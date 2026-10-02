// Admin Hero slides page — end-to-end verification.
//
// Creates ONE throwaway slide (with an uploaded image), exercises toggle / edit /
// delete, and removes it again.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/hero-slides-check.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');

const base = 'http://127.0.0.1:8899';
const project = 'D:/education/LearningWeb';
const out = `${project}/.workbuddy-ai/outputs`;
const MYSQL = 'C:/xampp/mysql/bin/mysql.exe';
const DB = 'Learningweb';

const results = [];
const assert = (value, message) => { if (!value) throw new Error(message); };
const ok = (check, detail) => { results.push({ check, passed: true, detail }); };

const stamp = Date.now();
const TITLE = `ZZ Pilot Slide ${stamp}`;
const RENAMED = `ZZ Pilot Slide ${stamp} (renamed)`;
const FIXTURE_PNG = path.join(project, '.workbuddy-ai', `slide-fixture-${stamp}.png`);

const PNG_1PX = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==',
    'base64',
);

let uploadedRel = null;

function sql(statement) {
    return execFileSync(MYSQL, ['-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
}

function cleanup() {
    try { sql(`DELETE FROM hero_slides WHERE title LIKE 'ZZ Pilot Slide %';`); } catch { /* ignore */ }
    if (uploadedRel) {
        try {
            const abs = path.join(project, 'storage', 'app', 'public', uploadedRel);
            if (fs.existsSync(abs)) fs.unlinkSync(abs);
        } catch { /* ignore */ }
    }
    try { if (fs.existsSync(FIXTURE_PNG)) fs.unlinkSync(FIXTURE_PNG); } catch { /* ignore */ }
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const pageErrors = [];
    const consoleErrors = [];
    let livePage = null;

    const waitForPath = (p, timeout = 60000) =>
        livePage.waitForFunction((expected) => window.location.pathname === expected, p, { timeout });

    const coldComponent = () =>
        livePage.evaluate(() => {
            const el = document.getElementById('app');
            if (!el || !el.dataset.page) return null;
            try { return JSON.parse(el.dataset.page).component; } catch { return null; }
        });

    const waitForToast = async (pattern, timeout = 30000) => {
        await livePage.waitForFunction(
            (source) => {
                const nodes = document.querySelectorAll('[role="status"]');
                const re = new RegExp(source, 'i');
                return [...nodes].some((node) => re.test(node.innerText || ''));
            },
            pattern,
            { timeout },
        );
        const texts = await livePage.locator('[role="status"]').allInnerTexts();
        return (texts.find((t) => new RegExp(pattern, 'i').test(t)) ?? texts[0] ?? '').replace(/\s+/g, ' ').trim();
    };

    try {
        fs.writeFileSync(FIXTURE_PNG, PNG_1PX);

        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        livePage = page;
        page.setDefaultTimeout(60000);
        page.setDefaultNavigationTimeout(90000);
        page.on('pageerror', (e) => pageErrors.push(e.message));
        page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(m.text()); });

        // ---------- Login ----------
        await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('#email').fill('admin@learnhub.com');
        await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
        await page.getByRole('button', { name: 'Sign in', exact: true }).click();
        await waitForPath('/admin/dashboard');
        await page.waitForLoadState('networkidle').catch(() => {});

        // ---------- Cold load ----------
        await page.goto(`${base}/admin/hero-slides`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        const component = await coldComponent();
        assert(component === 'Admin/HeroSlides/Index', `Expected Admin/HeroSlides/Index, got ${component}`);
        ok('GET /admin/hero-slides renders Inertia component Admin/HeroSlides/Index (no longer Blade)');

        const cardCount = await page.locator('img[src*="hero-slides"], img[src^="http"]').count();
        assert(cardCount >= 2, `Expected at least 2 slide images, got ${cardCount}`);
        ok('Existing slides render as image cards', { cards: cardCount });

        await page.screenshot({ path: `${out}/sanpya-admin-vue-hero-slides.png`, fullPage: true });
        ok('Screenshot: hero slides (desktop)');

        // ---------- Create ----------
        await page.getByRole('link', { name: 'Add slide' }).click();
        await waitForPath('/admin/hero-slides/create');
        await page.locator('#title').waitFor({ state: 'visible', timeout: 60000 });
        await page.goto(`${base}/admin/hero-slides/create`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        assert((await coldComponent()) === 'Admin/HeroSlides/Form', 'Create form component mismatch');
        ok('GET /admin/hero-slides/create renders Admin/HeroSlides/Form');

        // The "needs an image" warning should be visible before anything is picked.
        assert(
            /needs an image/i.test(await page.locator('body').innerText()),
            'Create form should warn that a slide needs an image',
        );
        ok('Form warns when no image is set yet');

        await page.locator('#title').fill(TITLE);
        await page.locator('#subtitle').fill('Throwaway slide created by the automated check.');
        await page.locator('#button_text').fill('Learn more');
        await page.locator('#button_link').fill('/courses');
        await page.locator('#sort_order').fill('42');
        await page.locator('input[type="file"]').setInputFiles(FIXTURE_PNG);
        await page.waitForFunction(() => !!document.querySelector('img[src^="blob:"]'), undefined, { timeout: 30000 });
        ok('ImageUpload previews the picked file');

        await page.getByRole('button', { name: 'Create slide' }).click();
        await waitForPath('/admin/hero-slides');
        const createToast = await waitForToast('Slide created successfully');
        const stored = sql(
            `SELECT CONCAT(IFNULL(image,''),'|',is_active,'|',sort_order,'|',IFNULL(button_text,'')) FROM hero_slides WHERE title='${TITLE}';`,
        );
        const [storedImage, storedActive, storedOrder, storedButton] = stored.split('|');
        uploadedRel = storedImage;
        assert(/^hero-slides\/[A-Za-z0-9]+\.png$/.test(storedImage), `Upload path wrong: ${storedImage}`);
        assert(storedActive === '1', 'is_active should default to true');
        assert(storedOrder === '42', `sort_order not saved: ${storedOrder}`);
        assert(storedButton === 'Learn more', `button_text not saved: ${storedButton}`);
        ok('Slide created with upload, checkbox and number fields', { toast: createToast, image: storedImage });

        // ---------- Toggle active ----------
        const slideCard = page.locator('img[alt="' + TITLE + '"]').locator('xpath=ancestor::div[contains(@class,"rounded-xl")][1]');
        await slideCard.waitFor({ state: 'visible', timeout: 30000 });
        await slideCard.getByRole('button', { name: 'Deactivate slide' }).click();
        const toggleToast = await waitForToast('Slide status updated');
        assert(sql(`SELECT is_active FROM hero_slides WHERE title='${TITLE}';`) === '0', 'Toggle did not persist');
        ok('Toggle active works', { toast: toggleToast });

        // ---------- Edit ----------
        await page.goto(`${base}/admin/hero-slides`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        const editCard = page.locator('img[alt="' + TITLE + '"]').locator('xpath=ancestor::div[contains(@class,"rounded-xl")][1]');
        await editCard.getByRole('link', { name: 'Edit' }).click();
        await page.waitForFunction(
            () => /\/admin\/hero-slides\/\d+\/edit$/.test(window.location.pathname),
            undefined,
            { timeout: 60000 },
        );
        await page.locator('#title').waitFor({ state: 'visible', timeout: 60000 });
        assert((await page.locator('#title').inputValue()) === TITLE, 'Edit: title not prefilled');
        assert((await page.locator('#button_text').inputValue()) === 'Learn more', 'Edit: button_text not prefilled');
        assert((await page.locator('#sort_order').inputValue()) === '42', 'Edit: sort_order not prefilled');
        assert(
            (await page.locator('#image_url').inputValue()) === '',
            'Edit: image_url must stay empty for a local upload (else it posts back as a URL)',
        );
        ok('Edit form prefills, and keeps image_url empty for local uploads');

        await page.locator('#title').fill(RENAMED);
        await page.getByRole('button', { name: 'Save changes' }).click();
        await waitForPath('/admin/hero-slides');
        const updateToast = await waitForToast('Slide updated successfully');
        assert(sql(`SELECT COUNT(*) FROM hero_slides WHERE title='${RENAMED}';`) === '1', 'Rename did not persist');
        ok('Slide updated', { toast: updateToast });

        // ---------- Mobile ----------
        await page.setViewportSize({ width: 390, height: 844 });
        await page.waitForTimeout(500);
        assert(
            await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1),
            'Hero slides page: horizontal overflow on mobile',
        );
        await page.screenshot({ path: `${out}/sanpya-admin-vue-hero-slides-mobile.png`, fullPage: true });
        ok('Hero slides page: no horizontal overflow at 390px');
        await page.setViewportSize({ width: 1440, height: 960 });

        // ---------- Delete ----------
        const delCard = page.locator('img[alt="' + RENAMED + '"]').locator('xpath=ancestor::div[contains(@class,"rounded-xl")][1]');
        await delCard.waitFor({ state: 'visible', timeout: 30000 });
        await delCard.getByRole('button', { name: 'Delete slide' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.waitFor({ state: 'visible', timeout: 30000 });
        await dialog.getByRole('button', { name: 'Delete slide' }).click();
        const deleteToast = await waitForToast('Slide deleted successfully');
        assert(sql(`SELECT COUNT(*) FROM hero_slides WHERE title LIKE 'ZZ Pilot Slide %';`) === '0', 'Delete failed');
        assert(
            !fs.existsSync(path.join(project, 'storage', 'app', 'public', uploadedRel)),
            'Delete did not remove the uploaded image from storage',
        );
        ok('Delete removed the row and its uploaded image', { toast: deleteToast });

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 6) });

        fs.writeFileSync(
            `${out}/sanpya-admin-hero-slides-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nHERO SLIDES PAGE: ${results.length}/${results.length} checks passed`);
        for (const r of results) console.log(`  PASS  ${r.check}`);
        if (consoleErrors.length) {
            console.log(`\nConsole errors (${consoleErrors.length}):`);
            consoleErrors.slice(0, 8).forEach((e) => console.log(`  - ${e}`));
        }
    } catch (error) {
        const lastGood = results.length ? results[results.length - 1].check : '(none)';
        const where = livePage ? livePage.url() : '(no page)';
        results.push({ check: 'RUN', passed: false, error: error.message, lastGood, where });
        fs.writeFileSync(
            `${out}/sanpya-admin-hero-slides-results.json`,
            JSON.stringify({ base, results, pageErrors, consoleErrors }, null, 2),
        );
        console.error(`\nFAILED: ${error.message}`);
        console.error(`  last successful check: ${lastGood}`);
        console.error(`  url at failure: ${where}`);
        if (pageErrors.length) console.error(`Page errors: ${pageErrors.join(' | ')}`);
        if (consoleErrors.length) console.error(`Console errors: ${consoleErrors.slice(0, 6).join(' | ')}`);
        process.exitCode = 1;
    } finally {
        await browser.close();
        cleanup();
        console.log('[cleanup] throwaway slide + uploaded file removed');
    }
})();
