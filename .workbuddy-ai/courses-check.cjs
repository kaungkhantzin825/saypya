// Admin Courses page — end-to-end verification.
//
// Exercises the Inertia courses list: filters, server-side sort, approve,
// feature toggle, and delete. To do that safely it inserts ONE throwaway draft
// course (with a throwaway thumbnail file on disk) and removes it again, so no
// real course is ever modified or deleted.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/courses-check.cjs
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

const TITLE = `ZZ Pilot Test Course ${Date.now()}`;
const SLUG = `zz-pilot-test-course-${Date.now()}`;
const THUMB_REL = 'courses/thumbnails/pilot-test-thumb.png';
const THUMB_ABS = path.join(project, 'storage', 'app', 'public', THUMB_REL);

/** Run a SQL statement and return trimmed stdout. */
function sql(statement) {
    return execFileSync(MYSQL, ['-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
}

function cleanup() {
    try { sql(`DELETE FROM courses WHERE slug = '${SLUG}';`); } catch { /* ignore */ }
    try { if (fs.existsSync(THUMB_ABS)) fs.unlinkSync(THUMB_ABS); } catch { /* ignore */ }
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const pageErrors = [];
    const consoleErrors = [];
    let livePage = null;

    const waitForPath = (page, p, timeout = 60000) =>
        page.waitForFunction((expected) => window.location.pathname === expected, p, { timeout });

    const coldComponent = (page) =>
        page.evaluate(() => {
            const el = document.getElementById('app');
            if (!el || !el.dataset.page) return null;
            try { return JSON.parse(el.dataset.page).component; } catch { return null; }
        });

    const waitForToast = async (page, pattern, timeout = 30000) => {
        await page.waitForFunction(
            (source) => {
                const nodes = document.querySelectorAll('[role="status"]');
                const re = new RegExp(source, 'i');
                return [...nodes].some((node) => re.test(node.innerText || ''));
            },
            pattern,
            { timeout },
        );
        const texts = await page.locator('[role="status"]').allInnerTexts();
        return (texts.find((t) => new RegExp(pattern, 'i').test(t)) ?? texts[0] ?? '').replace(/\s+/g, ' ').trim();
    };

    /** Wait for the debounced search visit to settle before clicking anything. */
    const searchCourses = async (page, term) => {
        await page.locator('#filter-search').fill(term);
        await page.waitForFunction(
            (expected) => new URLSearchParams(window.location.search).get('search') === expected,
            term,
            { timeout: 60000 },
        );
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(250);
    };

    try {
        // ---------- Fixture ----------
        fs.mkdirSync(path.dirname(THUMB_ABS), { recursive: true });
        fs.writeFileSync(THUMB_ABS, Buffer.from('89504e470d0a1a0a', 'hex'));
        sql(
            `INSERT INTO courses (title, slug, description, thumbnail, level, category_id, instructor_id, ` +
            `price, status, is_featured, language, created_at, updated_at) VALUES (` +
            `'${TITLE}', '${SLUG}', 'Throwaway course created by the automated check.', ` +
            `'${THUMB_REL}', 'beginner', 1, 2, 0, 'draft', 0, 'English', NOW(), NOW());`,
        );
        const fixtureId = sql(`SELECT id FROM courses WHERE slug = '${SLUG}';`);
        assert(/^\d+$/.test(fixtureId), `Fixture course was not created (id="${fixtureId}")`);
        assert(fs.existsSync(THUMB_ABS), 'Fixture thumbnail file missing');
        ok('Fixture: throwaway draft course + thumbnail created', { id: fixtureId });

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
        await waitForPath(page, '/admin/dashboard');
        await page.waitForLoadState('networkidle').catch(() => {});

        // ---------- Cold load: component identity ----------
        await page.goto(`${base}/admin/courses`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        const component = await coldComponent(page);
        assert(component === 'Admin/Courses/Index', `Expected Admin/Courses/Index, got ${component}`);
        ok('GET /admin/courses renders Inertia component Admin/Courses/Index (no longer Blade)');

        const totalCopy = await page.locator('text=/^\\d+ courses?$/').first().innerText();
        ok('Courses list reports a total', { total: totalCopy.trim() });

        // ---------- SPA nav from the sidebar ----------
        await page.goto(`${base}/admin/dashboard`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.getByRole('link', { name: 'Courses', exact: true }).first().click();
        await waitForPath(page, '/admin/courses');
        await page.waitForFunction(
            () => /All courses/i.test(document.body.innerText),
            undefined,
            { timeout: 60000 },
        );
        ok('Sidebar "Courses" link now performs an Inertia SPA visit');

        // ---------- Sorting ----------
        await page.getByRole('button', { name: /^Title$/ }).first().click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('sort') === 'title',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForFunction(
            () => {
                const cells = [...document.querySelectorAll('table tbody tr td:nth-child(2)')].map((c) =>
                    (c.innerText || '').trim().toLowerCase(),
                );
                return cells.length > 1 && cells.every((v, i) => i === 0 || cells[i - 1] <= v);
            },
            undefined,
            { timeout: 60000 },
        );
        ok('Server-side sorting works (title asc)');

        // ---------- Status filter ----------
        await page.locator('#filter-status').click();
        await page.getByRole('option', { name: 'Published', exact: true }).click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('status') === 'published',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForFunction(
            () => {
                const cells = [...document.querySelectorAll('table tbody tr td:nth-child(7)')];
                return cells.length > 0 && cells.every((c) => (c.innerText || '').trim().toLowerCase() === 'published');
            },
            undefined,
            { timeout: 60000 },
        );
        ok('Status filter round-trips to the server', { status: 'published' });

        // ---------- Featured filter ----------
        await page.locator('#filter-featured').click();
        await page.getByRole('option', { name: 'Featured', exact: true }).click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('featured') === '1',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(400);
        ok('Featured filter round-trips to the server', { featured: '1' });

        // ---------- Clear ----------
        await page.getByRole('button', { name: 'Clear' }).click();
        await page.waitForFunction(
            () => {
                const q = new URLSearchParams(window.location.search);
                return !q.get('status') && !q.get('featured');
            },
            undefined,
            { timeout: 60000 },
        );
        await page.waitForLoadState('networkidle').catch(() => {});
        ok('Clear filters resets the query string');

        await page.screenshot({ path: `${out}/sanpya-admin-vue-courses.png`, fullPage: true });
        ok('Screenshot: courses list (desktop)');

        // ---------- Find the fixture, exercise approve / feature ----------
        await searchCourses(page, TITLE);
        const row = page.locator('table tbody tr', { hasText: TITLE }).first();
        await row.waitFor({ state: 'visible', timeout: 30000 });

        // Approve (draft only) — the button must be present for this row.
        await row.getByRole('button', { name: 'Approve and publish' }).click();
        const approveToast = await waitForToast(page, 'Course approved and published');
        ok('Approve action works (draft -> published)', { toast: approveToast });
        assert(sql(`SELECT status FROM courses WHERE slug='${SLUG}';`) === 'published', 'Approve did not persist');

        // Feature toggle, both directions.
        const rowAfter = page.locator('table tbody tr', { hasText: TITLE }).first();
        await rowAfter.waitFor({ state: 'visible', timeout: 30000 });
        await rowAfter.getByRole('button', { name: 'Mark as featured' }).click();
        const featureToast = await waitForToast(page, 'Course featured');
        assert(sql(`SELECT is_featured FROM courses WHERE slug='${SLUG}';`) === '1', 'Feature did not persist');
        ok('Feature toggle on works', { toast: featureToast });

        const rowFeatured = page.locator('table tbody tr', { hasText: TITLE }).first();
        await rowFeatured.waitFor({ state: 'visible', timeout: 30000 });
        await rowFeatured.getByRole('button', { name: 'Remove from featured' }).click();
        const unfeatureToast = await waitForToast(page, 'Course unfeatured');
        assert(sql(`SELECT is_featured FROM courses WHERE slug='${SLUG}';`) === '0', 'Unfeature did not persist');
        ok('Feature toggle off works', { toast: unfeatureToast });

        // ---------- Mobile ----------
        await page.setViewportSize({ width: 390, height: 844 });
        await page.waitForTimeout(500);
        assert(
            await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1),
            'Courses page: horizontal overflow on mobile',
        );
        await page.screenshot({ path: `${out}/sanpya-admin-vue-courses-mobile.png`, fullPage: true });
        ok('Courses page: no horizontal overflow at 390px');
        await page.setViewportSize({ width: 1440, height: 960 });

        // ---------- Delete ----------
        const deleteRow = page.locator('table tbody tr', { hasText: TITLE }).first();
        await deleteRow.waitFor({ state: 'visible', timeout: 30000 });
        await deleteRow.getByRole('button', { name: 'Delete course' }).click();

        const dialog = page.getByRole('dialog');
        await dialog.waitFor({ state: 'visible', timeout: 30000 });
        const dialogText = (await dialog.innerText()).replace(/\s+/g, ' ');
        // Course is hard-deleted (no SoftDeletes), so "permanently" is correct here.
        assert(
            /permanently/i.test(dialogText) && /cannot be undone/i.test(dialogText),
            `Courses delete dialog should warn it is permanent: "${dialogText}"`,
        );
        ok('Courses delete dialog correctly warns the action is permanent');

        await dialog.getByRole('button', { name: 'Delete course' }).click();
        const deleteToast = await waitForToast(page, 'Course deleted successfully');
        ok('Delete action works', { toast: deleteToast });

        assert(sql(`SELECT COUNT(*) FROM courses WHERE slug='${SLUG}';`) === '0', 'Delete did not remove the row');
        assert(!fs.existsSync(THUMB_ABS), 'Delete did not remove the thumbnail file from disk');
        ok('Delete removed both the DB row and the thumbnail file');

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 5) });

        fs.writeFileSync(
            `${out}/sanpya-admin-courses-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nCOURSES PAGE: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-courses-results.json`,
            JSON.stringify({ base, results, pageErrors, consoleErrors }, null, 2),
        );
        console.error(`\nFAILED: ${error.message}`);
        console.error(`  last successful check: ${lastGood}`);
        console.error(`  url at failure: ${where}`);
        if (pageErrors.length) console.error(`Page errors: ${pageErrors.join(' | ')}`);
        if (consoleErrors.length) console.error(`Console errors: ${consoleErrors.slice(0, 5).join(' | ')}`);
        process.exitCode = 1;
    } finally {
        await browser.close();
        cleanup();
        console.log('[cleanup] fixture course + thumbnail removed');
    }
})();
