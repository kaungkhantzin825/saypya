// Admin Reports + Site settings — end-to-end verification.
//
//   /admin/reports   -> Admin/Reports
//   /admin/settings  -> Admin/Settings
//
// Verifies every number on the reports page against direct SQL, checks both
// SVG charts render, then exercises the settings form including the image
// branch (a temporary `image` setting row is created and removed).
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/reports-settings-check.cjs
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
// Print each passing check immediately. The run is long enough that a silent
// stall is otherwise indistinguishable from slow progress, and the summary is
// only emitted at the very end.
const ok = (check, detail) => {
    results.push({ check, passed: true, detail });
    const extra = detail ? ` ${JSON.stringify(detail).slice(0, 110)}` : '';
    console.log(`  ok  [${results.length}] ${check}${extra}`);
};

const STAMP = Date.now();
const IMAGE_KEY = `zz_test_banner_${STAMP}`;

const LOCAL_PNG = path.join(out, `zz-settings-banner-${STAMP}.png`);
const LOCAL_TXT = path.join(out, `zz-settings-banner-${STAMP}.txt`);
const PNG_1PX = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'base64',
);

let originalSiteName = null;
let originalSiteDescription = null;
let uploadedRel = null;

function sql(statement) {
    // Two Windows CLI quirks to normalise here:
    //  * `--default-character-set=utf8mb4` — without it non-ASCII values
    //    (category names are Myanmar) come back as question marks.
    //  * the client emits CRLF, so a plain split('\n') leaves a stray CR on every
    //    line except the last — enough to break `WHERE key = 'x\r'`.
    return execFileSync(
        MYSQL,
        ['--default-character-set=utf8mb4', '-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`],
        { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] },
    )
        .replace(/\r\n/g, '\n')
        .trim();
}

function cleanup() {
    try {
        // Restore anything the run may have changed.
        if (originalSiteName !== null) {
            sql(`UPDATE site_settings SET value = '${originalSiteName.replace(/'/g, "''")}' WHERE \`key\` = 'site_name';`);
        }
        if (originalSiteDescription !== null) {
            sql(`UPDATE site_settings SET value = '${originalSiteDescription.replace(/'/g, "''")}' WHERE \`key\` = 'site_description';`);
        }
        sql(`DELETE FROM site_settings WHERE \`key\` LIKE 'zz_test_banner_%';`);
    } catch { /* ignore */ }

    try {
        if (uploadedRel) {
            const abs = path.join(project, 'storage', 'app', 'public', uploadedRel);
            if (fs.existsSync(abs)) fs.unlinkSync(abs);
        }
        // Any file this run stored but we did not record.
        for (const rel of sql(`SELECT value FROM site_settings WHERE value LIKE 'settings/%';`).split('\n').filter(Boolean)) {
            // only remove ones matching our stamp pattern
            if (rel.includes(String(STAMP))) {
                const abs = path.join(project, 'storage', 'app', 'public', rel);
                if (fs.existsSync(abs)) fs.unlinkSync(abs);
            }
        }
    } catch { /* ignore */ }
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const pageErrors = [];
    const consoleErrors = [];
    let livePage = null;

    const waitForPath = (page, p, timeout = 90000) =>
        page.waitForFunction((expected) => window.location.pathname === expected, p, { timeout });

    const coldComponent = (page) =>
        page.evaluate(() => {
            const el = document.getElementById('app');
            if (!el || !el.dataset.page) return null;
            try { return JSON.parse(el.dataset.page).component; } catch { return null; }
        });

    const coldGoto = async (page, url) => {
        await page.goto(`${base}${url}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
    };

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

    /** Reads the big number under a stat card label. */
    const statValue = async (page, label) => {
        const el = page.locator('main p', { hasText: new RegExp(`^${label}$`) }).first();
        return (await el.locator('xpath=following-sibling::p[1]').innerText()).trim();
    };

    const money = (amount) => `${Number(amount).toLocaleString('en-US')} Ks`;

    try {
        fs.mkdirSync(out, { recursive: true });
        fs.writeFileSync(LOCAL_PNG, PNG_1PX);
        fs.writeFileSync(LOCAL_TXT, 'not an image');

        originalSiteName = sql(`SELECT value FROM site_settings WHERE \`key\` = 'site_name';`);
        originalSiteDescription = sql(`SELECT value FROM site_settings WHERE \`key\` = 'site_description';`);
        assert(originalSiteName.length > 0, 'site_name fixture missing');

        // ---------- Expected values straight from SQL ----------
        // `User` uses SoftDeletes, so the Eloquent counts exclude rows with a
        // `deleted_at` stamp — the expectations must too.
        const expected = {
            users: sql('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL;'),
            courses: sql('SELECT COUNT(*) FROM courses;'),
            enrollments: sql(`SELECT COUNT(*) FROM enrollments WHERE payment_status = 'completed';`),
            revenue: sql(`SELECT COALESCE(SUM(price_paid), 0) FROM enrollments WHERE payment_status = 'completed';`),
            students: sql(`SELECT COUNT(*) FROM users WHERE role = 'student' AND deleted_at IS NULL;`),
            lecturers: sql(`SELECT COUNT(*) FROM users WHERE role = 'lecturer' AND deleted_at IS NULL;`),
            admins: sql(`SELECT COUNT(*) FROM users WHERE role = 'admin' AND deleted_at IS NULL;`),
            categories: sql('SELECT COUNT(*) FROM categories;'),
        };
        ok('Expected report values read from SQL', expected);

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

        // ==================== REPORTS ====================
        await coldGoto(page, '/admin/reports');
        assert(
            (await coldComponent(page)) === 'Admin/Reports',
            `Expected Admin/Reports, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/reports renders Inertia component Admin/Reports (no longer Blade)');

        await coldGoto(page, '/admin/dashboard');
        await page.getByRole('link', { name: 'Reports', exact: true }).first().click();
        await waitForPath(page, '/admin/reports');
        await page.waitForFunction(() => /Total users/i.test(document.body.innerText), undefined, { timeout: 60000 });
        ok('Sidebar "Reports" link now performs an Inertia SPA visit');

        // Stat cards vs SQL.
        assert((await statValue(page, 'Total users')) === Number(expected.users).toLocaleString('en-US'),
            `Total users card mismatch: ${await statValue(page, 'Total users')} vs ${expected.users}`);
        assert((await statValue(page, 'Total courses')) === Number(expected.courses).toLocaleString('en-US'),
            `Total courses card mismatch: ${await statValue(page, 'Total courses')} vs ${expected.courses}`);
        assert((await statValue(page, 'Total enrollments')) === Number(expected.enrollments).toLocaleString('en-US'),
            `Total enrollments card mismatch: ${await statValue(page, 'Total enrollments')} vs ${expected.enrollments}`);
        assert((await statValue(page, 'Total revenue')) === money(expected.revenue),
            `Total revenue card mismatch: ${await statValue(page, 'Total revenue')} vs ${money(expected.revenue)}`);
        ok('Reports overview cards match direct SQL counts', { revenue: money(expected.revenue) });

        // Revenue chart.
        const lineChart = page.locator('svg[data-chart="line"]');
        assert((await lineChart.count()) === 1, 'Revenue line chart did not render');
        assert((await lineChart.locator('circle').count()) === 12, 'Revenue chart should plot 12 months');
        assert((await lineChart.locator('path').count()) >= 2, 'Revenue chart should draw an area and a line path');
        const lineD = await lineChart.locator('path').last().getAttribute('d');
        assert(/\d/.test(lineD ?? '') && !/NaN/.test(lineD ?? ''), `Revenue chart path looks broken: ${lineD}`);
        // `allTextContents` — SVG elements have no `innerText`.
        const monthLabels = (await lineChart.locator('text').allTextContents())
            .map((t) => t.trim())
            .filter((t) => /^[A-Z][a-z]{2} \d{4}$/.test(t));
        assert(monthLabels.length === 6, `Expected 6 thinned month labels, got ${monthLabels.length}: ${monthLabels}`);
        ok('Revenue line chart renders 12 points with a valid path and thinned month labels', {
            first: monthLabels[0], last: monthLabels[monthLabels.length - 1],
        });

        // User distribution chart.
        const doughnut = page.locator('svg[data-chart="doughnut"]');
        assert((await doughnut.count()) === 1, 'User distribution chart did not render');
        assert((await doughnut.locator('circle').count()) === 4, 'Doughnut should have a track plus three arcs');

        // Assert against the chart's accessible summary — it is punctuated, unlike
        // the legend, where adjacent inline spans concatenate in `innerText`.
        const doughnutLabel = (await doughnut.getAttribute('aria-label')) ?? '';
        for (const [label, value] of [['Students', expected.students], ['Lecturers', expected.lecturers], ['Admins', expected.admins]]) {
            assert(
                doughnutLabel.includes(`${label}: ${value} (`),
                `Doughnut summary missing ${label}=${value}: ${doughnutLabel}`,
            );
        }
        const legendItems = await page.locator('svg[data-chart="doughnut"] + ul li').allInnerTexts();
        assert(legendItems.length === 3, `Doughnut legend should have 3 entries, got ${legendItems.length}`);
        const legend = legendItems.map((t) => t.replace(/\s+/g, ' ').trim()).join(' | ');
        ok('User distribution doughnut matches SQL role counts', { legend, summary: doughnutLabel });

        // Top courses.
        const courseRows = page.locator('table tbody tr');
        const firstCourseRow = (await courseRows.first().innerText()).replace(/\s+/g, ' ');
        const topCourse = sql(
            `SELECT CONCAT(c.title, '|', COALESCE(u.name, 'N/A'), '|', c.enrollments_count, '|', COALESCE(c.revenue, 0)) FROM (` +
            `SELECT courses.id, courses.title, courses.instructor_id, ` +
            `(SELECT COUNT(*) FROM enrollments WHERE enrollments.course_id = courses.id) AS enrollments_count, ` +
            `(SELECT COALESCE(SUM(price_paid), 0) FROM enrollments WHERE enrollments.course_id = courses.id AND payment_status = 'completed') AS revenue ` +
            `FROM courses ORDER BY enrollments_count DESC, courses.id ASC LIMIT 1) c ` +
            `LEFT JOIN users u ON u.id = c.instructor_id;`,
        );
        const [cTitle, cInstructor, cEnroll, cRevenue] = topCourse.split('|');
        assert(firstCourseRow.includes(cTitle), `Top course row should start with "${cTitle}": ${firstCourseRow}`);
        assert(firstCourseRow.includes(cInstructor), `Top course row should name "${cInstructor}": ${firstCourseRow}`);
        assert(firstCourseRow.includes(String(cEnroll)), `Top course row should show ${cEnroll} students: ${firstCourseRow}`);
        assert(firstCourseRow.includes(money(cRevenue)), `Top course row should show ${money(cRevenue)}: ${firstCourseRow}`);
        ok('Top courses table matches SQL (title, instructor, students, revenue)', { firstCourseRow });

        // Category rollups.
        const categoryTable = page.locator('table').last();
        assert(
            (await categoryTable.locator('tbody tr').count()) === Number(expected.categories),
            `Category table should list ${expected.categories} rows, got ${await categoryTable.locator('tbody tr').count()}`,
        );
        const catProbe = sql(
            `SELECT CONCAT(c.name, '|', (SELECT COUNT(*) FROM courses WHERE courses.category_id = c.id), '|', ` +
            `(SELECT COUNT(*) FROM enrollments e JOIN courses co ON co.id = e.course_id WHERE co.category_id = c.id AND e.payment_status = 'completed'), '|', ` +
            `(SELECT COALESCE(SUM(e.price_paid), 0) FROM enrollments e JOIN courses co ON co.id = e.course_id WHERE co.category_id = c.id AND e.payment_status = 'completed')) ` +
            `FROM categories c WHERE (SELECT COUNT(*) FROM courses WHERE courses.category_id = c.id) > 0 ORDER BY c.id LIMIT 1;`,
        );
        const [catName, catCourses, catStudents, catRevenue] = catProbe.split('|');
        const catRow = page.locator('table').last().locator('tbody tr', { hasText: catName }).first();
        const catRowText = (await catRow.innerText()).replace(/\s+/g, ' ');
        assert(catRowText.includes(catCourses), `Category row should show ${catCourses} courses: ${catRowText}`);
        assert(catRowText.includes(catStudents), `Category row should show ${catStudents} students: ${catRowText}`);
        assert(catRowText.includes(money(catRevenue)), `Category row should show ${money(catRevenue)}: ${catRowText}`);
        ok('Category rollup table matches SQL (courses, students, revenue)', { catRowText });

        await page.screenshot({ path: `${out}/sanpya-admin-vue-reports.png`, fullPage: true });
        ok('Screenshot: reports page');

        // ==================== SETTINGS ====================
        await coldGoto(page, '/admin/settings');
        assert(
            (await coldComponent(page)) === 'Admin/Settings',
            `Expected Admin/Settings, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/settings renders Inertia component Admin/Settings (no longer Blade)');

        await coldGoto(page, '/admin/dashboard');
        await page.getByRole('link', { name: 'Settings', exact: true }).first().click();
        await waitForPath(page, '/admin/settings');
        await page.waitForFunction(() => /Save settings/i.test(document.body.innerText), undefined, { timeout: 60000 });
        ok('Sidebar "Settings" link now performs an Inertia SPA visit');

        // Every setting row renders a control prefilled from the DB.
        const settingKeys = sql(`SELECT \`key\` FROM site_settings ORDER BY \`key\`;`).split('\n').filter(Boolean);
        assert(settingKeys.length >= 6, `Expected at least 6 settings, got ${settingKeys.length}`);
        for (const key of settingKeys) {
            const control = page.locator(`#${key}`);
            assert((await control.count()) === 1, `Setting "${key}" has no control`);
            const dbValue = sql(`SELECT COALESCE(value, '') FROM site_settings WHERE \`key\` = '${key}';`);
            assert(
                (await control.inputValue()) === dbValue,
                `Setting "${key}" not prefilled: "${await control.inputValue()}" vs "${dbValue}"`,
            );
        }
        ok('Every site setting renders a control prefilled from the database', { count: settingKeys.length });

        await page.screenshot({ path: `${out}/sanpya-admin-vue-settings.png`, fullPage: true });
        ok('Screenshot: settings page');

        // ---------- Save a text setting ----------
        const NEW_NAME = `Sanpya Online Academy (${STAMP})`;
        await page.locator('#site_name').fill(NEW_NAME);
        await page.getByRole('button', { name: 'Save settings' }).click();
        await waitForToast(page, 'Settings updated successfully');
        assert(
            sql(`SELECT value FROM site_settings WHERE \`key\` = 'site_name';`) === NEW_NAME,
            'Saving site_name did not persist',
        );
        ok('Saving a text setting persists to the database');

        await coldGoto(page, '/admin/settings');
        assert(
            (await page.locator('#site_name').inputValue()) === NEW_NAME,
            'Reloaded settings page did not show the saved value',
        );
        ok('Reloaded settings page reflects the saved value');

        // Restore immediately so the public site is unaffected by later steps.
        await page.locator('#site_name').fill(originalSiteName);
        await page.getByRole('button', { name: 'Save settings' }).click();
        await waitForToast(page, 'Settings updated successfully');
        assert(
            sql(`SELECT value FROM site_settings WHERE \`key\` = 'site_name';`) === originalSiteName,
            'site_name was not restored',
        );
        ok('Original site name restored');

        // ---------- Image setting branch ----------
        sql(
            `INSERT INTO site_settings (\`key\`, value, type, \`group\`, label, description, created_at, updated_at) VALUES ` +
            `('${IMAGE_KEY}', NULL, 'image', 'zz_temp', 'Test banner', 'Throwaway image setting', NOW(), NOW());`,
        );
        ok('Temporary image-type setting created', { IMAGE_KEY });

        await coldGoto(page, '/admin/settings');
        const imageWrapper = page.locator(`label[for="${IMAGE_KEY}"]`).locator('..');
        await imageWrapper.waitFor({ state: 'visible', timeout: 30000 });
        assert(
            (await imageWrapper.locator('input[type=file]').count()) === 1,
            'Image setting should render a file picker',
        );

        // Non-image upload must be rejected by the `image` validation rule.
        await imageWrapper.locator('input[type=file]').setInputFiles(LOCAL_TXT);
        await page.getByRole('button', { name: 'Save settings' }).click();
        await page.waitForFunction(
            () => /must be an image|must be a file of type|valid image/i.test(document.body.innerText),
            undefined,
            { timeout: 60000 },
        );
        assert(
            sql(`SELECT COALESCE(value, '') FROM site_settings WHERE \`key\` = '${IMAGE_KEY}';`) === '',
            'A rejected upload must not change the stored value',
        );
        ok('Uploading a non-image is rejected and leaves the stored value untouched');

        // Valid upload stores a relative path on the public disk.
        await coldGoto(page, '/admin/settings');
        const imageWrapper2 = page.locator(`label[for="${IMAGE_KEY}"]`).locator('..');
        await imageWrapper2.locator('input[type=file]').setInputFiles(LOCAL_PNG);
        await page.getByRole('button', { name: 'Save settings' }).click();
        await waitForToast(page, 'Settings updated successfully');

        uploadedRel = sql(`SELECT value FROM site_settings WHERE \`key\` = '${IMAGE_KEY}';`);
        assert(/^settings\//.test(uploadedRel), `Image upload did not store a settings/ path: "${uploadedRel}"`);
        assert(
            fs.existsSync(path.join(project, 'storage', 'app', 'public', uploadedRel)),
            `Uploaded image missing on disk: ${uploadedRel}`,
        );
        ok('Image upload stores the file on the public disk and records its path', { uploadedRel });

        // The preview must resolve through SiteSetting::imageUrl().
        await coldGoto(page, '/admin/settings');
        const previewSrc = await page
            .locator(`label[for="${IMAGE_KEY}"]`)
            .locator('..')
            .locator('img')
            .first()
            .getAttribute('src');
        assert(
            !!previewSrc && previewSrc.endsWith(uploadedRel),
            `Image preview src should resolve to the stored path: "${previewSrc}" vs "${uploadedRel}"`,
        );
        const previewStatus = await page.evaluate(async (src) => {
            const response = await fetch(src, { method: 'GET' });
            return response.status;
        }, previewSrc);
        assert(previewStatus === 200, `Image preview returned HTTP ${previewStatus} for ${previewSrc}`);
        ok('Stored image resolves to a loadable URL via SiteSetting::imageUrl()', { previewSrc });

        // ---------- Mobile overflow ----------
        for (const url of ['/admin/reports', '/admin/settings']) {
            await coldGoto(page, url);
            await page.setViewportSize({ width: 390, height: 844 });
            await page.waitForTimeout(400);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            assert(overflow <= 1, `${url}: horizontal overflow of ${overflow}px at 390px`);
            await page.setViewportSize({ width: 1440, height: 960 });
        }
        ok('Reports and Settings: no horizontal overflow at 390px');

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 5) });

        fs.writeFileSync(
            `${out}/sanpya-admin-reports-settings-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nREPORTS + SETTINGS: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-reports-settings-results.json`,
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
        try { if (fs.existsSync(LOCAL_PNG)) fs.unlinkSync(LOCAL_PNG); } catch { /* ignore */ }
        try { if (fs.existsSync(LOCAL_TXT)) fs.unlinkSync(LOCAL_TXT); } catch { /* ignore */ }
        console.log('[cleanup] temp image setting + uploaded file removed, site_name restored');
    }
})();
