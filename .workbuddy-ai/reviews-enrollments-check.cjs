// Admin Reviews + Enrollments — end-to-end verification.
//
//   /admin/reviews        -> Admin/Reviews/Index
//   /admin/reviews/{id}/edit -> Admin/Reviews/Form
//   /admin/enrollments    -> Admin/Enrollments/Index
//
// Uses two throwaway fixtures (one pending review, one pending enrollment) that
// are inserted directly and removed in a `finally` block, so no real student's
// payment status or review is touched.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/reviews-enrollments-check.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');

const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';
const MYSQL = 'C:/xampp/mysql/bin/mysql.exe';
const DB = 'Learningweb';

const results = [];
const assert = (value, message) => { if (!value) throw new Error(message); };
const ok = (check, detail) => { results.push({ check, passed: true, detail }); };

const STAMP = Date.now();
const COMMENT = `ZZ automated review ${STAMP}`;
const EDITED_COMMENT = `ZZ automated review ${STAMP} (edited)`;
// Pinned to a day no real enrollment will ever occupy, so the date filter
// isolates the fixture row. `enrollments.enrolled_at` is declared
// `ON UPDATE CURRENT_TIMESTAMP`, so any UPDATE (e.g. approving the payment)
// silently resets it — we re-pin it before every UI lookup.
const PINNED_DATE = '2020-06-15';
const PINNED_TS = '2020-06-15 12:00:00';

let reviewId = null;
let enrollmentId = null;
let courseId = null;
let studentId = null;

function sql(statement) {
    return execFileSync(MYSQL, ['-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
}

function cleanup() {
    try { if (reviewId) sql(`DELETE FROM reviews WHERE id = ${reviewId};`); } catch { /* ignore */ }
    try { if (enrollmentId) sql(`DELETE FROM enrollments WHERE id = ${enrollmentId};`); } catch { /* ignore */ }
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

    const waitForQuery = (page, key, value, timeout = 60000) =>
        page.waitForFunction(
            ([k, v]) => new URLSearchParams(window.location.search).get(k) === v,
            [key, value],
            { timeout },
        );

    const pickOption = async (page, triggerId, label) => {
        await page.locator(`#${triggerId}`).click();
        await page.locator('[role="listbox"] [role="option"]', { hasText: label }).first().click();
    };

    try {
        // ---------- Fixtures ----------
        // Both `reviews` and `enrollments` carry a UNIQUE (user_id, course_id),
        // so the fixtures must use a pair that is still free.
        //
        // The reviews page searches only student/course NAMES (not the comment),
        // so the review fixture needs a student with no other reviews — that way
        // searching their name returns the fixture and nothing else.
        const reviewUser = sql(
            `SELECT u.id FROM users u WHERE u.role = 'student' AND NOT EXISTS (` +
            `  SELECT 1 FROM reviews r WHERE r.user_id = u.id) ORDER BY u.id LIMIT 1;`,
        );
        assert(/^\d+$/.test(reviewUser), `No review-free student available: "${reviewUser}"`);
        const reviewStudentName = sql(`SELECT name FROM users WHERE id = ${reviewUser};`);
        const reviewCourse = sql(`SELECT id FROM courses ORDER BY id LIMIT 1;`);
        assert(/^\d+$/.test(reviewCourse), 'No course available for the review fixture');

        const freeEnrollmentPair = sql(
            `SELECT CONCAT(u.id, '|', c.id) FROM users u JOIN courses c ` +
            `WHERE u.role = 'student' AND NOT EXISTS (` +
            `  SELECT 1 FROM enrollments e WHERE e.user_id = u.id AND e.course_id = c.id) LIMIT 1;`,
        );
        assert(/^\d+\|\d+$/.test(freeEnrollmentPair), `No free (user, course) pair for an enrollment: "${freeEnrollmentPair}"`);

        const [enrollUser, enrollCourse] = freeEnrollmentPair.split('|');
        studentId = reviewUser;
        courseId = reviewCourse;

        sql(
            `INSERT INTO reviews (course_id, user_id, rating, comment, is_approved, created_at, updated_at) VALUES ` +
            `(${reviewCourse}, ${reviewUser}, 3, '${COMMENT}', 0, NOW(), NOW());`,
        );
        reviewId = sql(`SELECT id FROM reviews WHERE comment = '${COMMENT}' ORDER BY id DESC LIMIT 1;`);
        assert(/^\d+$/.test(reviewId), `Review fixture not created (got "${reviewId}")`);
        const fixtureReviewId = reviewId;

        sql(
            `INSERT INTO enrollments (user_id, course_id, price_paid, payment_status, progress_percentage, enrolled_at, created_at, updated_at) VALUES ` +
            `(${enrollUser}, ${enrollCourse}, 12345, 'pending', 40, '${PINNED_TS}', '${PINNED_TS}', '${PINNED_TS}');`,
        );
        enrollmentId = sql(
            `SELECT id FROM enrollments WHERE price_paid = 12345 AND payment_status = 'pending' AND user_id = ${enrollUser} ORDER BY id DESC LIMIT 1;`,
        );
        assert(/^\d+$/.test(enrollmentId), `Enrollment fixture not created (got "${enrollmentId}")`);
        ok('Fixtures: throwaway pending review + pending enrollment created', {
            reviewId, enrollmentId, reviewStudentName, enrollmentPair: freeEnrollmentPair,
        });

        /** `enrolled_at` is reset by MySQL on every UPDATE — put it back. */
        const repinEnrollment = () => {
            if (!enrollmentId) return;
            sql(`UPDATE enrollments SET enrolled_at = '${PINNED_TS}' WHERE id = ${enrollmentId};`);
        };

        /** Narrow the list to just the fixture via the status + date filters. */
        const showFixtureEnrollment = async (statusLabel) => {
            await pickOption(page, 'filter-status', statusLabel);
            await waitForQuery(page, 'status', statusLabel.toLowerCase());
            await page.locator('#filter-from').fill(PINNED_DATE);
            await page.locator('#filter-to').fill(PINNED_DATE);
            await page.getByRole('button', { name: 'Apply' }).click();
            await waitForQuery(page, 'from_date', PINNED_DATE);
            await page.waitForLoadState('networkidle').catch(() => {});
            await page.waitForTimeout(300);
        };

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

        // ---------- ENROLLMENTS ----------
        await coldGoto(page, '/admin/enrollments');
        assert(
            (await coldComponent(page)) === 'Admin/Enrollments/Index',
            `Expected Admin/Enrollments/Index, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/enrollments renders Inertia component Admin/Enrollments/Index (no longer Blade)');

        // The five summary cards are rendered from the grouped SQL totals.
        await page.waitForFunction(() => /Total revenue/i.test(document.body.innerText), undefined, { timeout: 30000 });
        const statText = (await page.locator('main').innerText()).replace(/\s+/g, ' ');
        assert(/Completed/i.test(statText) && /Refunded/i.test(statText) && /Ks/.test(statText),
            `Enrollment stat cards missing: ${statText.slice(0, 200)}`);
        ok('Enrollment totals render (completed/pending/failed/refunded + revenue)');

        // Sidebar SPA visit.
        await coldGoto(page, '/admin/dashboard');
        await page.getByRole('link', { name: 'Enrollments', exact: true }).first().click();
        await waitForPath(page, '/admin/enrollments');
        await page.waitForFunction(() => /All enrollments/i.test(document.body.innerText), undefined, { timeout: 60000 });
        ok('Sidebar "Enrollments" link now performs an Inertia SPA visit');

        // Status filter (applies immediately) + date range (explicit Apply) narrow
        // the list down to just the fixture row.
        await showFixtureEnrollment('Pending');
        await page.waitForFunction(
            () => {
                const cells = [...document.querySelectorAll('table tbody tr td:nth-child(5)')];
                return cells.length > 0 && cells.every((c) => (c.innerText || '').trim().toLowerCase() === 'pending');
            },
            undefined,
            { timeout: 60000 },
        );
        ok('Enrollment status filter round-trips to the server');

        const rowCount = await page.locator('table tbody tr').count();
        assert(rowCount === 1, `Date filter should isolate the fixture, found ${rowCount} rows`);
        ok('Date range filter isolates the fixture row');

        const row = page.locator('table tbody tr', { hasText: '12,345' }).first();
        await row.waitFor({ state: 'visible', timeout: 30000 });
        await row.getByRole('button', { name: 'Approve enrollment' }).click();
        const approveToast = await waitForToast(page, 'Enrollment approved');
        assert(
            sql(`SELECT payment_status FROM enrollments WHERE id = ${enrollmentId};`) === 'completed',
            'Approve did not persist',
        );
        ok('Approve enrollment works (pending -> completed, verified in SQL)', { toast: approveToast });

        // After approving, the row offers Refund instead of Approve/Reject.
        // Approving reset `enrolled_at`, so re-pin it before looking again.
        repinEnrollment();
        await showFixtureEnrollment('Completed');
        const completedRow = page.locator('table tbody tr', { hasText: '12,345' }).first();
        await completedRow.waitFor({ state: 'visible', timeout: 30000 });
        assert(
            (await completedRow.getByRole('button', { name: 'Refund enrollment' }).count()) === 1,
            'Completed enrollment should offer a refund action',
        );
        await completedRow.getByRole('button', { name: 'Refund enrollment' }).click();
        const refundToast = await waitForToast(page, 'Enrollment refunded');
        assert(
            sql(`SELECT payment_status FROM enrollments WHERE id = ${enrollmentId};`) === 'refunded',
            'Refund did not persist',
        );
        ok('Refund enrollment works (completed -> refunded, verified in SQL)', { toast: refundToast });

        await coldGoto(page, '/admin/enrollments');
        await page.screenshot({ path: `${out}/sanpya-admin-vue-enrollments.png`, fullPage: true });
        ok('Screenshot: enrollments list');

        // ---------- REVIEWS ----------
        await coldGoto(page, '/admin/reviews');
        assert(
            (await coldComponent(page)) === 'Admin/Reviews/Index',
            `Expected Admin/Reviews/Index, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/reviews renders Inertia component Admin/Reviews/Index (no longer Blade)');

        // Search narrows to the fixture. The backend matches student/course
        // NAMES, so search by the fixture student's name.
        await page.locator('#filter-search').fill(reviewStudentName);
        await waitForQuery(page, 'search', reviewStudentName);
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(300);
        const reviewRow = page.locator('table tbody tr', { hasText: COMMENT }).first();
        await reviewRow.waitFor({ state: 'visible', timeout: 30000 });
        ok('Review search round-trips to the server and finds the fixture');

        // The fixture is unapproved, so it shows Pending + an approve action.
        assert(
            (await reviewRow.getByText('Pending', { exact: true }).count()) === 1,
            'Unapproved review should render a Pending badge',
        );
        await reviewRow.getByRole('button', { name: 'Approve review' }).click();
        const reviewToast = await waitForToast(page, 'Review approved');
        assert(sql(`SELECT is_approved FROM reviews WHERE id = ${reviewId};`) === '1', 'Approve did not persist');
        ok('Approve review works (verified in SQL)', { toast: reviewToast });

        // Approved rows no longer offer the approve button.
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(300);
        const approvedRow = page.locator('table tbody tr', { hasText: COMMENT }).first();
        await approvedRow.waitFor({ state: 'visible', timeout: 30000 });
        assert(
            (await approvedRow.getByRole('button', { name: 'Approve review' }).count()) === 0,
            'Approved review should no longer offer the approve action',
        );
        ok('Approved review hides the approve action');

        // Rating filter must AND with the search, not OR (regression guard for
        // the grouped whereHas fix).
        await pickOption(page, 'filter-rating', '5 stars');
        await waitForQuery(page, 'rating', '5');
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(400);
        const stillVisible = await page.locator('table tbody tr', { hasText: COMMENT }).count();
        assert(stillVisible === 0, 'Rating filter was bypassed by the search OR — regression in reviewsIndex');
        ok('Rating filter ANDs with search (grouped whereHas — regression guard)');

        await page.getByRole('button', { name: 'Clear' }).click();
        await page.waitForFunction(
            () => !new URLSearchParams(window.location.search).get('rating'),
            undefined,
            { timeout: 60000 },
        );
        await page.waitForLoadState('networkidle').catch(() => {});
        ok('Clear filters resets the query string');

        // ---------- REVIEW EDIT ----------
        await coldGoto(page, `/admin/reviews/${reviewId}/edit`);
        assert(
            (await coldComponent(page)) === 'Admin/Reviews/Form',
            `Expected Admin/Reviews/Form, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/reviews/{id}/edit renders Admin/Reviews/Form (no longer Blade)');

        const prefilledComment = await page.locator('#comment').inputValue();
        assert(prefilledComment === COMMENT, `Comment not prefilled (got "${prefilledComment}")`);
        assert(
            (await page.locator('#rating').innerText()).trim().startsWith('3'),
            'Rating Select not prefilled with 3 stars',
        );
        ok('Review edit form prefills rating, comment and approval state');

        await page.locator('#comment').fill(EDITED_COMMENT);
        await pickOption(page, 'rating', '4 stars');
        await page.getByRole('button', { name: 'Save changes' }).click();
        await waitForPath(page, '/admin/reviews', 90000);
        await waitForToast(page, 'Review updated');
        const saved = sql(`SELECT CONCAT(rating, '|', comment) FROM reviews WHERE id = ${reviewId};`);
        assert(saved === `4|${EDITED_COMMENT}`, `Review edit did not persist: ${saved}`);
        ok('Edit review works (PUT persists rating + comment)');

        await coldGoto(page, `/admin/reviews/${reviewId}/edit`);
        await page.screenshot({ path: `${out}/sanpya-admin-vue-review-edit.png`, fullPage: true });
        ok('Screenshot: review edit form');

        await coldGoto(page, '/admin/reviews');
        await page.screenshot({ path: `${out}/sanpya-admin-vue-reviews.png`, fullPage: true });
        ok('Screenshot: reviews list');

        // ---------- Delete the review through the UI ----------
        await page.locator('#filter-search').fill(reviewStudentName);
        await waitForQuery(page, 'search', reviewStudentName);
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(300);
        const delRow = page.locator('table tbody tr', { hasText: EDITED_COMMENT }).first();
        await delRow.waitFor({ state: 'visible', timeout: 30000 });
        await delRow.getByRole('button', { name: 'Delete review' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.waitFor({ state: 'visible', timeout: 30000 });
        const dialogText = (await dialog.innerText()).replace(/\s+/g, ' ');
        assert(/cannot be undone/i.test(dialogText), `Delete dialog should warn: "${dialogText}"`);
        await dialog.getByRole('button', { name: 'Delete review' }).click();
        await waitForToast(page, 'Review deleted');
        assert(sql(`SELECT COUNT(*) FROM reviews WHERE id = ${reviewId};`) === '0', 'Delete did not persist');
        reviewId = null; // already gone; skip the cleanup delete
        ok('Delete review works (row really removed — no SoftDeletes)');

        // ---------- Mobile overflow ----------
        for (const url of ['/admin/reviews', '/admin/enrollments', `/admin/reviews/${fixtureReviewId}/edit`]) {
            await coldGoto(page, url);
            await page.setViewportSize({ width: 390, height: 844 });
            await page.waitForTimeout(400);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            assert(overflow <= 1, `${url}: horizontal overflow of ${overflow}px at 390px`);
            await page.setViewportSize({ width: 1440, height: 960 });
        }
        ok('Reviews / Enrollments pages: no horizontal overflow at 390px');

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 5) });

        fs.writeFileSync(
            `${out}/sanpya-admin-reviews-enrollments-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nREVIEWS + ENROLLMENTS: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-reviews-enrollments-results.json`,
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
        console.log('[cleanup] throwaway review + enrollment removed');
    }
})();
