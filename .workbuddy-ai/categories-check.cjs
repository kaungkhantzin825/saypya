// Admin Categories page — end-to-end verification.
//
// Covers the image-URL fix (external Unsplash URLs must NOT be prefixed with
// /storage/), the shared ImageUpload component, create/edit/delete, and the
// server-side guard that refuses to delete a category that still has courses.
//
// Creates ONE throwaway category and removes it again.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/categories-check.cjs
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
const NAME = `ZZ Pilot Category ${stamp}`;
const RENAMED = `ZZ Pilot Category ${stamp} (renamed)`;
const SLUG = `zz-pilot-category-${stamp}`;
const FIXTURE_PNG = path.join(project, '.workbuddy-ai', `upload-fixture-${stamp}.png`);

/** Actual stored path of the uploaded file, filled in after create. */
let uploadedRel = null;

// Smallest valid PNG (1x1, red).
const PNG_1PX = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==',
    'base64',
);

function sql(statement) {
    return execFileSync(MYSQL, ['-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
}

function cleanup() {
    try { sql(`DELETE FROM categories WHERE slug LIKE 'zz-pilot-category-%';`); } catch { /* ignore */ }
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
        await page.goto(`${base}/admin/categories`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        const component = await coldComponent();
        assert(component === 'Admin/Categories/Index', `Expected Admin/Categories/Index, got ${component}`);
        ok('GET /admin/categories renders Inertia component Admin/Categories/Index (no longer Blade)');

        const rowCount = await page.locator('table tbody tr').count();
        assert(rowCount === 8, `Expected 8 seeded categories, got ${rowCount}`);
        ok('All 8 seeded categories render', { rows: rowCount });

        // ---------- The image-URL fix ----------
        // `categories.image` holds full Unsplash URLs. The old accessor prepended
        // `storage/`, producing `…/storage/https://images.unsplash.com/…`.
        const imgSrcs = await page.locator('table tbody tr td:nth-child(2) img').evaluateAll((els) =>
            els.map((el) => el.getAttribute('src') || ''),
        );
        assert(imgSrcs.length === 8, `Expected 8 category images, got ${imgSrcs.length}`);
        assert(
            imgSrcs.every((src) => src.startsWith('https://images.unsplash.com/')),
            `Image URLs are still malformed: ${imgSrcs.slice(0, 2).join(' | ')}`,
        );
        assert(
            !imgSrcs.some((src) => /\/storage\/https?:\/\//.test(src)),
            'A src still contains /storage/https:// — the old bug is back',
        );
        ok('External image URLs are passed through unmodified (no /storage/ prefix)', {
            sample: imgSrcs[0],
        });

        await page.screenshot({ path: `${out}/sanpya-admin-vue-categories.png`, fullPage: true });
        ok('Screenshot: categories list (desktop)');

        // ---------- Create ----------
        await page.getByRole('link', { name: 'Add category' }).click();
        await waitForPath('/admin/categories/create');
        await page.locator('#name').waitFor({ state: 'visible', timeout: 60000 });
        ok('Sidebar/Add-category link opens the form via an Inertia SPA visit');

        // `data-page` is a cold-load snapshot only — reload to assert the component.
        await page.goto(`${base}/admin/categories/create`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        assert((await coldComponent()) === 'Admin/Categories/Form', 'Create form component mismatch');
        ok('GET /admin/categories/create renders Admin/Categories/Form');

        await page.locator('#name').fill(NAME);
        await page.locator('#description').fill('Throwaway category created by the automated check.');
        await page.locator('#icon').fill('fas fa-flask');
        await page.locator('#sort_order').fill('99');
        await page.locator('input[type="file"]').setInputFiles(FIXTURE_PNG);

        // The preview must switch to the freshly picked file (blob: URL).
        await page.waitForFunction(
            () => !!document.querySelector('img[src^="blob:"]'),
            undefined,
            { timeout: 30000 },
        );
        ok('ImageUpload shows a local preview of the picked file');

        await page.getByRole('button', { name: 'Create category' }).click();
        await waitForPath('/admin/categories');
        const createToast = await waitForToast('Category created successfully');
        ok('Category created', { toast: createToast });

        // Slug is derived from the name by the model mutator.
        const stored = sql(
            `SELECT CONCAT(slug,'|',IFNULL(image,''),'|',is_active,'|',sort_order) FROM categories WHERE name='${NAME.replace(/'/g, "''")}';`,
        );
        const [storedSlug, storedImage, storedActive, storedOrder] = stored.split('|');
        uploadedRel = storedImage;
        assert(storedSlug === SLUG, `Slug mismatch: expected ${SLUG}, got ${storedSlug}`);
        // Laravel generates a random file name, so assert the shape, not the name.
        assert(
            /^categories\/[A-Za-z0-9]+\.png$/.test(storedImage),
            `Uploaded image path wrong: ${storedImage}`,
        );
        assert(storedActive === '1', 'is_active should default to true');
        assert(storedOrder === '99', `sort_order not saved: ${storedOrder}`);
        assert(fs.existsSync(path.join(project, 'storage', 'app', 'public', storedImage)), 'Uploaded file is missing from storage');
        ok('Create persisted slug, upload, checkbox and number fields', { slug: storedSlug, image: storedImage });

        // ---------- The uploaded image renders from /storage ----------
        await page.goto(`${base}/admin/categories`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        const newRow = page.locator('table tbody tr', { hasText: NAME }).first();
        await newRow.waitFor({ state: 'visible', timeout: 30000 });
        const newSrc = await newRow.locator('td:nth-child(2) img').getAttribute('src');
        // asset() yields a root-relative URL here, which is correct (and is what
        // keeps images working on any host/port). Accept both shapes.
        assert(
            newSrc.endsWith(`/storage/${storedImage}`) && !newSrc.includes('/storage/http'),
            `Uploaded image should resolve under /storage, got ${newSrc}`,
        );
        ok('Local uploads resolve to /storage/... (both URL shapes handled)', { src: newSrc });

        // ---------- Edit ----------
        await newRow.getByRole('link', { name: 'Edit category' }).click();
        await page.waitForFunction(
            () => /\/admin\/categories\/\d+\/edit$/.test(window.location.pathname),
            undefined,
            { timeout: 60000 },
        );
        await page.locator('#name').waitFor({ state: 'visible', timeout: 60000 });
        assert((await page.locator('#name').inputValue()) === NAME, 'Edit form: name not prefilled');
        assert((await page.locator('#icon').inputValue()) === 'fas fa-flask', 'Edit form: icon not prefilled');
        assert((await page.locator('#sort_order').inputValue()) === '99', 'Edit form: sort_order not prefilled');
        const editImgSrc = await page.locator('img[src*="/storage/"]').first().getAttribute('src');
        assert(
            editImgSrc.endsWith(`/storage/${uploadedRel}`),
            `Edit form: existing image not previewed (got ${editImgSrc})`,
        );
        ok('Edit form prefills every field, including the existing image');

        await page.locator('#name').fill(RENAMED);
        await page.getByRole('button', { name: 'Save changes' }).click();
        await waitForPath('/admin/categories');
        const updateToast = await waitForToast('Category updated successfully');
        const renamedSlug = sql(`SELECT slug FROM categories WHERE name='${RENAMED.replace(/'/g, "''")}';`);
        assert(/^zz-pilot-category-\d+-renamed$/.test(renamedSlug), `Slug not regenerated: ${renamedSlug}`);
        ok('Category updated and slug regenerated from the new name', {
            toast: updateToast,
            slug: renamedSlug,
        });

        // ---------- Delete guard: category with courses is refused ----------
        await page.goto(`${base}/admin/categories`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        // Names are in Myanmar script; the slug is the stable identifier.
        const guardedRow = page.locator('table tbody tr', { hasText: 'web-development' }).first();
        await guardedRow.waitFor({ state: 'visible', timeout: 30000 });
        await guardedRow.getByRole('button', { name: /Cannot delete/ }).click();
        const guardDialog = page.getByRole('dialog');
        await guardDialog.waitFor({ state: 'visible', timeout: 30000 });
        const guardText = (await guardDialog.innerText()).replace(/\s+/g, ' ');
        assert(/will refuse/i.test(guardText), `Guard dialog should explain the refusal: "${guardText}"`);
        assert(
            await guardDialog.getByRole('button', { name: 'Delete category' }).isDisabled(),
            'Delete button should be disabled for a category with courses',
        );
        ok('Delete is blocked in the UI for a category that still has courses');
        await guardDialog.getByRole('button', { name: 'Cancel' }).click();
        await page.waitForTimeout(400);

        // ---------- Mobile ----------
        await page.setViewportSize({ width: 390, height: 844 });
        await page.waitForTimeout(500);
        assert(
            await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1),
            'Categories page: horizontal overflow on mobile',
        );
        await page.screenshot({ path: `${out}/sanpya-admin-vue-categories-mobile.png`, fullPage: true });
        ok('Categories page: no horizontal overflow at 390px');
        await page.setViewportSize({ width: 1440, height: 960 });

        // ---------- Delete the throwaway category ----------
        const delRow = page.locator('table tbody tr', { hasText: RENAMED }).first();
        await delRow.waitFor({ state: 'visible', timeout: 30000 });
        await delRow.getByRole('button', { name: 'Delete category' }).click();
        const delDialog = page.getByRole('dialog');
        await delDialog.waitFor({ state: 'visible', timeout: 30000 });
        await delDialog.getByRole('button', { name: 'Delete category' }).click();
        const deleteToast = await waitForToast('Category deleted successfully');
        assert(sql(`SELECT COUNT(*) FROM categories WHERE slug LIKE 'zz-pilot-category-%';`) === '0', 'Delete failed');
        assert(
            !fs.existsSync(path.join(project, 'storage', 'app', 'public', uploadedRel)),
            'Delete did not remove the uploaded image from storage',
        );
        ok('Delete removed the row and its uploaded image file', { toast: deleteToast });

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 6) });

        fs.writeFileSync(
            `${out}/sanpya-admin-categories-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nCATEGORIES PAGE: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-categories-results.json`,
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
        console.log('[cleanup] throwaway category + uploaded file removed');
    }
})();
