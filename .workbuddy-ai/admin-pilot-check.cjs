// Admin panel pilot — end-to-end verification.
//
// Proves the three Inertia pages converted in the pilot (Admin/Dashboard,
// Admin/Users/Index, Admin/Users/Form) render, and that the full user CRUD
// round-trip works through the real HTTP stack with a real admin login.
//
// NOTE ON COMPONENT ASSERTIONS
// ---------------------------
// Inertia v2 only *reads* the root element's `data-page` attribute at boot
// (`@inertiajs/core` never writes it back). After an SPA visit the attribute
// still describes the FIRST page of the session. So:
//   * component identity is asserted after a cold `page.goto` (the attribute is
//     valid there, and it also proves the server picks the right component);
//   * SPA navigation is asserted against rendered DOM, which is what the user
//     actually sees.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/admin-pilot-check.cjs
//
// The script creates and deletes its own user on the happy path. `User` uses
// SoftDeletes, so "deleted" rows remain with `deleted_at` set. Clean up leftovers
// (including soft-deleted ones) with:
//   DELETE FROM users WHERE email LIKE 'pilot.user.%@example.com';
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const fs = require('node:fs');

const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';
const results = [];
const assert = (value, message) => { if (!value) throw new Error(message); };
const ok = (check, detail) => { results.push({ check, passed: true, detail }); };

const stamp = Date.now();
const TEST_EMAIL = `pilot.user.${stamp}@example.com`;
const TEST_NAME = 'Pilot Test User';
const RENAMED = 'Pilot Test User (renamed)';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const pageErrors = [];
    const consoleErrors = [];
    let livePage = null;

    /** Component name from the root element — only valid on a cold load. */
    const coldComponent = (page) =>
        page.evaluate(() => {
            const el = document.getElementById('app');
            if (!el || !el.dataset.page) return null;
            try { return JSON.parse(el.dataset.page).component; } catch { return null; }
        });

    const waitForPath = (page, path, timeout = 60000) =>
        page.waitForFunction((expected) => window.location.pathname === expected, path, { timeout });

    /** Cold-load `url` and assert the server rendered `expected` component. */
    const expectColdComponent = async (page, url, expected) => {
        await page.goto(`${base}${url}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        const actual = await coldComponent(page);
        assert(actual === expected, `Cold load ${url}: expected ${expected}, got ${actual}`);
        return actual;
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
        const match = texts.find((text) => new RegExp(pattern, 'i').test(text)) ?? texts[0] ?? '';
        return match.replace(/\s+/g, ' ').trim();
    };

    const waitForBody = (page, pattern, timeout = 60000) =>
        page.waitForFunction(
            (source) => new RegExp(source, 'i').test(document.body.innerText || ''),
            pattern,
            { timeout },
        );

    /**
     * Type into the users search box and wait for the debounced visit to land.
     *
     * The search box debounces 350ms and then issues an Inertia visit with
     * preserveState. Clicking anything before that visit settles gets the click
     * swallowed (the pending request supersedes it), so always wait for the
     * `search` query param AND a quiet network before interacting further.
     */
    const searchUsers = async (page, term) => {
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
        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        livePage = page;
        page.setDefaultTimeout(60000);
        page.setDefaultNavigationTimeout(90000);
        page.on('pageerror', (error) => pageErrors.push(error.message));
        page.on('console', (message) => {
            if (message.type() === 'error') consoleErrors.push(message.text());
        });

        // ---------- Login as admin ----------
        await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('#email').fill('admin@learnhub.com');
        await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
        await page.getByRole('button', { name: 'Sign in', exact: true }).click();
        await waitForPath(page, '/admin/dashboard');
        await page.waitForLoadState('networkidle').catch(() => {});
        ok('Admin login reaches /admin/dashboard');

        // ---------- 1. Dashboard (cold load) ----------
        await expectColdComponent(page, '/admin/dashboard', 'Admin/Dashboard');
        ok('GET /admin/dashboard renders Inertia component Admin/Dashboard');

        assert(await page.locator('aside').first().isVisible(), 'Dashboard: sidebar missing');
        for (const label of ['Dashboard', 'Users', 'Courses', 'Enrollments', 'Settings']) {
            assert(
                await page.getByRole('link', { name: label, exact: true }).first().isVisible(),
                `Dashboard: sidebar link "${label}" missing`,
            );
        }
        ok('AdminLayout sidebar renders the role-aware nav');

        const coursesHref = await page
            .getByRole('link', { name: 'Courses', exact: true })
            .first()
            .getAttribute('href');
        assert(coursesHref === '/admin/courses', `Dashboard: Courses link href wrong (${coursesHref})`);
        ok('Still-Blade nav items point at real admin routes (plain <a>, not <Link>)');

        const dashProps = await page.evaluate(() => JSON.parse(document.getElementById('app').dataset.page).props);
        assert(typeof dashProps.stats.total_users === 'number', 'Dashboard: stats.total_users not numeric');
        assert(Array.isArray(dashProps.recentEnrollments), 'Dashboard: recentEnrollments is not an array');
        assert(Array.isArray(dashProps.topCourses), 'Dashboard: topCourses is not an array');
        ok('Dashboard receives real KPI + table data', {
            total_users: dashProps.stats.total_users,
            total_courses: dashProps.stats.total_courses,
            total_enrollments: dashProps.stats.total_enrollments,
            recent_enrollments: dashProps.recentEnrollments.length,
            top_courses: dashProps.topCourses.length,
        });

        await page.screenshot({ path: `${out}/sanpya-admin-vue-dashboard.png`, fullPage: true });
        ok('Screenshot: admin dashboard (desktop)');

        await page.setViewportSize({ width: 390, height: 844 });
        await page.waitForTimeout(500);
        assert(
            await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1),
            'Dashboard: horizontal overflow on mobile',
        );
        await page.screenshot({ path: `${out}/sanpya-admin-vue-dashboard-mobile.png`, fullPage: true });
        ok('Dashboard: no horizontal overflow at 390px');
        await page.setViewportSize({ width: 1440, height: 960 });

        // ---------- 2. Users index ----------
        // SPA navigation: click the sidebar link and assert the DOM swapped.
        await page.getByRole('link', { name: 'Users', exact: true }).first().click();
        await waitForPath(page, '/admin/users');
        await waitForBody(page, 'All users');
        assert(
            (await page.locator('h1').first().innerText()).trim() === 'Users',
            'Users index: heading did not update after SPA nav',
        );
        ok('Sidebar link performs an Inertia SPA visit (no full reload)');

        // Cold load: assert the server component identity.
        await expectColdComponent(page, '/admin/users', 'Admin/Users/Index');
        ok('GET /admin/users renders Inertia component Admin/Users/Index');

        const rowCount = await page.locator('table tbody tr').count();
        assert(rowCount > 0, 'Users index: no table rows rendered');
        const totalCopy = await page.locator('text=/^\\d+ accounts?$/').first().innerText();
        ok('Users DataTable renders rows', { rows: rowCount, total: totalCopy.trim() });

        // Sorting is server-side: the header emits `key:asc|desc`, the page
        // forwards it as query params, and the database orders the result.
        await page.getByRole('button', { name: /^Name$/ }).first().click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('sort') === 'name',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('direction') === 'asc',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForFunction(
            () => {
                const cells = [...document.querySelectorAll('table tbody tr td:nth-child(2)')].map((c) =>
                    (c.innerText || '').trim().toLowerCase(),
                );
                if (cells.length < 2) return false;
                return cells.every((value, i) => i === 0 || cells[i - 1] <= value);
            },
            undefined,
            { timeout: 60000 },
        );
        const sortedNames = await page.locator('table tbody tr td:nth-child(2)').allInnerTexts();
        ok('Server-side sorting works (name asc)', {
            sort: 'name',
            direction: 'asc',
            first: sortedNames[0]?.trim(),
        });

        // Clicking the same header again flips the direction.
        await page.getByRole('button', { name: /^Name$/ }).first().click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('direction') === 'desc',
            undefined,
            { timeout: 60000 },
        );
        ok('Clicking the active sort header flips asc -> desc');

        // Role filter through the reka-ui Select. The visit uses preserveState,
        // so wait on rendered rows rather than the URL.
        await page.locator('#filter-role').click();
        await page.getByRole('option', { name: 'Lecturer', exact: true }).click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('role') === 'lecturer',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForFunction(
            () => {
                const cells = [...document.querySelectorAll('table tbody tr td:nth-child(4)')];
                return cells.length > 0 && cells.every((c) => (c.innerText || '').trim().toLowerCase() === 'lecturer');
            },
            undefined,
            { timeout: 60000 },
        );
        const lectCount = await page.locator('table tbody tr').count();
        ok('Role filter round-trips to the server', { role: 'lecturer', rows: lectCount });

        await page.getByRole('button', { name: 'Clear' }).click();
        await page.waitForFunction(
            () => !new URLSearchParams(window.location.search).get('role'),
            undefined,
            { timeout: 60000 },
        );
        await page.waitForFunction(
            () => {
                const cells = [...document.querySelectorAll('table tbody tr td:nth-child(4)')];
                return cells.length > 0 && cells.some((c) => (c.innerText || '').trim().toLowerCase() !== 'lecturer');
            },
            undefined,
            { timeout: 60000 },
        );
        ok('Clear filters resets the query string');

        await page.screenshot({ path: `${out}/sanpya-admin-vue-users.png`, fullPage: true });
        ok('Screenshot: users index (desktop)');

        // ---------- 3. Create user ----------
        await page.getByRole('link', { name: 'Add user' }).click();
        await waitForPath(page, '/admin/users/create');
        await page.locator('#name').waitFor({ state: 'visible', timeout: 60000 });
        await expectColdComponent(page, '/admin/users/create', 'Admin/Users/Form');
        ok('GET /admin/users/create renders Inertia component Admin/Users/Form');

        await page.locator('#name').fill(TEST_NAME);
        await page.locator('#email').fill(TEST_EMAIL);
        await page.locator('#password').fill('pilot-password-123');
        await page.locator('#password_confirmation').fill('pilot-password-123');
        await page.locator('#phone').fill('+95 9 123 456');
        await page.locator('#country').fill('Myanmar');
        await page.locator('#bio').fill('Created by the automated pilot check.');
        await page.screenshot({ path: `${out}/sanpya-admin-vue-user-form.png`, fullPage: true });
        ok('Screenshot: user create form (desktop)');

        await page.getByRole('button', { name: 'Create user' }).click();
        await waitForPath(page, '/admin/users');
        const createToast = await waitForToast(page, 'User created successfully');
        ok('User created; flash toast shown', { toast: createToast });

        // ---------- 4. Find it, then edit ----------
        await searchUsers(page, TEST_EMAIL);
        const createdRow = page.locator('table tbody tr', { hasText: TEST_EMAIL }).first();
        await createdRow.waitFor({ state: 'visible', timeout: 30000 });
        const createdId = (await createdRow.locator('td').first().innerText()).replace('#', '').trim();
        ok('Newly created user appears in the filtered list', { id: createdId, email: TEST_EMAIL });

        // `Button` with an `href` renders a <Link>/<a>, so this is a link, not a button.
        await createdRow.getByRole('link', { name: 'Edit user' }).click();
        await waitForPath(page, `/admin/users/${createdId}/edit`);
        await page.locator('#name').waitFor({ state: 'visible', timeout: 60000 });
        await expectColdComponent(page, `/admin/users/${createdId}/edit`, 'Admin/Users/Form');
        ok('GET /admin/users/{id}/edit renders Inertia component Admin/Users/Form');

        await page.goto(`${base}/admin/users/${createdId}/edit`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        assert((await page.locator('#name').inputValue()) === TEST_NAME, 'Edit form: name not prefilled');
        assert((await page.locator('#email').inputValue()) === TEST_EMAIL, 'Edit form: email not prefilled');
        assert((await page.locator('#phone').inputValue()).includes('123'), 'Edit form: phone not prefilled');
        ok('Edit form prefills from the existing record');

        await page.locator('#name').fill(RENAMED);
        await page.getByRole('button', { name: 'Save changes' }).click();
        await waitForPath(page, '/admin/users');
        const updateToast = await waitForToast(page, 'User updated successfully');
        ok('User updated via _method=put spoofing', { toast: updateToast });

        // ---------- 5. Toggle status ----------
        await searchUsers(page, TEST_EMAIL);
        const toggleRow = page.locator('table tbody tr', { hasText: TEST_EMAIL }).first();
        await toggleRow.waitFor({ state: 'visible', timeout: 30000 });
        await toggleRow.getByRole('button', { name: 'Disable account' }).click();
        const toggleToast = await waitForToast(page, 'deactivated');
        ok('Toggle status works', { toast: toggleToast });

        // ---------- 6. Delete user (super-admin only) ----------
        const deleteRow = page.locator('table tbody tr', { hasText: TEST_EMAIL }).first();
        await deleteRow.waitFor({ state: 'visible', timeout: 30000 });
        await deleteRow.getByRole('button', { name: 'Delete user' }).click();
        // Scope to the dialog: the row action and the confirm button share a name.
        const dialog = page.getByRole('dialog');
        await dialog.waitFor({ state: 'visible', timeout: 30000 });

        // User uses SoftDeletes — the row survives, so the copy must not claim
        // the action is permanent (the old Blade panel got this wrong).
        const dialogText = (await dialog.innerText()).replace(/\s+/g, ' ');
        assert(
            !/permanently|cannot be undone/i.test(dialogText),
            `Delete dialog wrongly claims permanence: "${dialogText}"`,
        );
        assert(
            /can be restored/i.test(dialogText),
            `Delete dialog should say the record is retained: "${dialogText}"`,
        );
        ok('Delete dialog copy is accurate about the soft delete');

        await dialog.getByRole('button', { name: 'Delete user' }).click();
        const deleteToast = await waitForToast(page, 'User deleted successfully');
        ok('Delete confirmation dialog + destroy works', { toast: deleteToast });

        // Deleting redirects to the bare index (the filter is dropped), so
        // verify against the server directly instead of racing the debounce.
        await page.goto(`${base}/admin/users?search=${encodeURIComponent(TEST_EMAIL)}`, {
            waitUntil: 'domcontentloaded',
        });
        await page.waitForLoadState('networkidle').catch(() => {});
        const remaining = await page.locator('table tbody tr', { hasText: TEST_EMAIL }).count();
        assert(remaining === 0, 'Delete: user still returned by the server');
        const emptyCopy = await page.locator('table tbody').innerText();
        assert(/No users found/i.test(emptyCopy), `Delete: expected empty state, got "${emptyCopy.slice(0, 120)}"`);
        ok('Deleted user is gone from the server (search returns the empty state)');

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 5) });

        fs.writeFileSync(
            `${out}/sanpya-admin-pilot-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nADMIN PILOT: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-pilot-results.json`,
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
    }
})();
