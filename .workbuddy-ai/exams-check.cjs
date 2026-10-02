// Admin Exams cluster — end-to-end verification.
//
//   /admin/exams                      -> Admin/Exams/Index
//   /admin/exams/create               -> Admin/Exams/Form   (create)
//   /admin/exams/{id}/edit            -> Admin/Exams/Form   (edit + question CRUD)
//   /admin/exams/{id}/results         -> Admin/Exams/Results
//   /admin/exam-attempts/{id}/grade   -> Admin/Exams/Grade
//
// Creates one throwaway exam through the real UI, adds one question of each type
// through the dialog, edits and deletes a question, updates the settings, then
// seeds an exam attempt so the results + grading pages can be exercised. The
// exam is removed in a `finally` block; the FK cascade takes questions,
// attempts and answers with it.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/exams-check.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');

const base = 'http://127.0.0.1:8899';
const project = 'D:/education/LearningWeb';
const out = `${project}/.workbuddy-ai/outputs`;
const MYSQL = 'C:/xampp/mysql/bin/mysql.exe';
const DB = 'Learningweb';

const results = [];
const assert = (value, message) => { if (!value) throw new Error(message); };
const ok = (check, detail) => { results.push({ check, passed: true, detail }); };

const STAMP = Date.now();
const EXAM_TITLE = `ZZ Exam ${STAMP}`;
const EDITED_EXAM_TITLE = `ZZ Exam ${STAMP} (edited)`;
const Q_MCQ = `ZZ MCQ ${STAMP}: which option is the correct one?`;
const Q_MCQ_EDITED = `ZZ MCQ ${STAMP}: which option is the correct one (edited)?`;
const Q_TF = `ZZ TF ${STAMP}: Laravel is a PHP framework.`;
const Q_ESSAY = `ZZ Essay ${STAMP}: explain the MVC pattern.`;
const ESSAY_ANSWER = 'ZZ essay answer body — throwaway.';

function sql(statement) {
    return execFileSync(MYSQL, ['-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
}

function cleanup() {
    try {
        // FK cascade removes exam_questions / exam_attempts / exam_answers.
        sql(`DELETE FROM exams WHERE title LIKE 'ZZ Exam ${STAMP}%';`);
    } catch { /* ignore */ }
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const pageErrors = [];
    const consoleErrors = [];
    let livePage = null;

    const waitForPath = (page, path, timeout = 90000) =>
        page.waitForFunction((expected) => window.location.pathname === expected, path, { timeout });

    const waitForPathMatch = (page, source, timeout = 90000) =>
        page.waitForFunction((src) => new RegExp(src).test(window.location.pathname), source, { timeout });

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

    const pickOption = async (page, triggerId, label) => {
        await page.locator(`#${triggerId}`).click();
        await page.locator('[role="listbox"] [role="option"]', { hasText: label }).first().click();
    };

    /** reka-ui Checkbox is a <button role="checkbox">, so drive it by data-state. */
    const setChecked = async (page, selector, want) => {
        const el = page.locator(selector);
        await el.waitFor({ state: 'visible', timeout: 30000 });
        const current = await el.getAttribute('data-state');
        if ((current === 'checked') !== want) {
            await el.click();
            await page.waitForFunction(
                ([sel, expected]) => document.querySelector(sel)?.getAttribute('data-state') === expected,
                [selector, want ? 'checked' : 'unchecked'],
                { timeout: 15000 },
            );
        }
        assert(
            (await el.getAttribute('data-state')) === (want ? 'checked' : 'unchecked'),
            `Checkbox ${selector} did not reach ${want ? 'checked' : 'unchecked'}`,
        );
    };

    /** Reads the big number under a stat card label on the results page. */
    const statValue = async (page, label) => {
        const el = page.locator('main p', { hasText: new RegExp(`^${label}$`) }).first();
        return (await el.locator('xpath=following-sibling::p[1]').innerText()).trim();
    };

    const dialog = (page) => page.getByRole('dialog');

    try {
        // ---------- Fixture prerequisites ----------
        const courseTitle = sql('SELECT title FROM courses WHERE id = 1;');
        assert(courseTitle.length > 0, 'Course id 1 not found — cannot build an exam fixture');
        const studentId = sql(`SELECT id FROM users WHERE role = 'student' ORDER BY id LIMIT 1;`);
        assert(/^\d+$/.test(studentId), 'No student user found for the attempt fixture');
        ok('Fixture prerequisites resolved', { courseTitle, studentId });

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

        // ---------- EXAMS: INDEX ----------
        await coldGoto(page, '/admin/exams');
        assert(
            (await coldComponent(page)) === 'Admin/Exams/Index',
            `Expected Admin/Exams/Index, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/exams renders Inertia component Admin/Exams/Index (no longer Blade)');

        // Sidebar SPA visit.
        await coldGoto(page, '/admin/dashboard');
        await page.getByRole('link', { name: 'Exams', exact: true }).first().click();
        await waitForPath(page, '/admin/exams');
        await page.waitForFunction(() => /All exams/i.test(document.body.innerText), undefined, { timeout: 60000 });
        ok('Sidebar "Exams" link now performs an Inertia SPA visit');

        // Server-side sort.
        const firstTitle = () => page.locator('table tbody tr td:nth-child(1)').first().innerText();
        await page.locator('th button', { hasText: 'Exam' }).first().click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('sort') === 'title',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForLoadState('networkidle').catch(() => {});
        const ascFirst = (await firstTitle()).trim();
        await page.locator('th button', { hasText: 'Exam' }).first().click();
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('direction') === 'desc',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForLoadState('networkidle').catch(() => {});
        const descFirst = (await firstTitle()).trim();
        assert(ascFirst !== descFirst, `Sorting by title did not reorder rows (both "${ascFirst}")`);
        ok('Exam title column sorts server-side (asc/desc round-trip)', { ascFirst, descFirst });

        // Status filter.
        await pickOption(page, 'filter-status', 'Published');
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('is_published') === '1',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForLoadState('networkidle').catch(() => {});
        ok('Exam status filter round-trips to the server');

        await page.getByRole('button', { name: 'Clear' }).click();
        await page.waitForFunction(
            () => !new URLSearchParams(window.location.search).has('is_published'),
            undefined,
            { timeout: 60000 },
        );
        ok('Clear resets the exam filters');

        await coldGoto(page, '/admin/exams');
        await page.screenshot({ path: `${out}/sanpya-admin-vue-exams.png`, fullPage: true });
        ok('Screenshot: exams index');

        // ---------- EXAMS: CREATE ----------
        await coldGoto(page, '/admin/exams/create');
        assert(
            (await coldComponent(page)) === 'Admin/Exams/Form',
            `Expected Admin/Exams/Form on create, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/exams/create renders Admin/Exams/Form (no longer Blade)');

        await pickOption(page, 'course_id', courseTitle);
        await page.locator('#title').fill(EXAM_TITLE);
        await page.locator('#description').fill('Throwaway exam created by the automated check.');
        await page.locator('#duration_minutes').fill('45');
        await page.locator('#passing_score').fill('70');
        await page.locator('#max_attempts').fill('3');
        await setChecked(page, '#is_published', false);

        const createState = {
            course: (await page.locator('#course_id').innerText()).trim(),
            title: await page.locator('#title').inputValue(),
            duration: await page.locator('#duration_minutes').inputValue(),
        };
        assert(createState.course === courseTitle, `Course Select did not commit: ${JSON.stringify(createState)}`);
        assert(createState.title === EXAM_TITLE, `Title not set: ${JSON.stringify(createState)}`);

        await page.getByRole('button', { name: 'Create exam' }).click();
        await waitForPathMatch(page, '^/admin/exams/\\d+/edit$');
        await waitForToast(page, 'Exam created');

        const examId = sql(`SELECT id FROM exams WHERE title = '${EXAM_TITLE}' ORDER BY id DESC LIMIT 1;`);
        assert(/^\d+$/.test(examId), `Exam was not created (got "${examId}")`);
        const examRow = sql(
            `SELECT CONCAT(course_id, '|', duration_minutes, '|', passing_score, '|', max_attempts, '|', is_published, '|', show_results, '|', show_correct_answers, '|', IF(created_by = 1, 'admin', 'other')) ` +
            `FROM exams WHERE id = ${examId};`,
        );
        assert(
            examRow === `1|45|70|3|0|1|1|admin`,
            `Unexpected exam row: ${examRow}`,
        );
        ok('Create exam works (settings persisted, created_by attributed to the admin)', { examId, examRow });

        // ---------- EXAMS: EDIT — form prefills ----------
        await coldGoto(page, `/admin/exams/${examId}/edit`);
        assert(
            (await coldComponent(page)) === 'Admin/Exams/Form',
            `Expected Admin/Exams/Form on edit, got ${await coldComponent(page)}`,
        );
        assert(
            (await page.locator('#title').inputValue()) === EXAM_TITLE,
            'Edit form title not prefilled',
        );
        assert(
            (await page.locator('#passing_score').inputValue()) === '70',
            'Edit form passing score not prefilled',
        );
        assert(
            (await page.locator('#course_id').innerText()).trim() === courseTitle,
            'Edit form course Select not prefilled',
        );
        await page.waitForFunction(
            () => /No questions yet/i.test(document.body.innerText),
            undefined,
            { timeout: 30000 },
        );
        ok('Edit form prefills every field and warns that the exam has no questions');

        // ---------- QUESTIONS: ADD (one of each type) ----------
        const openAddDialog = async () => {
            await page.getByRole('button', { name: 'Add question' }).first().click();
            await dialog(page).waitFor({ state: 'visible', timeout: 30000 });
        };

        /**
         * Submits a dialog form and waits for it to close. Toasts live for ~7s, so
         * three identical "Question added successfully" toasts in a row cannot be
         * told apart — the dialog closing (driven by `onSuccess`) is the reliable
         * success signal.
         */
        const submitDialog = async (label) => {
            await dialog(page).getByRole('button', { name: label }).click();
            await dialog(page).waitFor({ state: 'hidden', timeout: 60000 });
            await page.waitForLoadState('networkidle').catch(() => {});
        };

        // -- multiple choice: 3 options, the SECOND one correct --
        await openAddDialog();
        await pickOption(page, 'question_type', 'Multiple choice');
        await page.locator('#question_text').fill(Q_MCQ);
        await page.locator('#question_points').fill('4');
        await dialog(page).getByRole('button', { name: 'Add option' }).click();
        await page.locator('[role="dialog"] input[placeholder="Option 1"]').fill('The first option');
        await page.locator('[role="dialog"] input[placeholder="Option 2"]').fill('The second option');
        await page.locator('[role="dialog"] input[placeholder="Option 3"]').fill('The third option');
        await page.locator('[role="dialog"] input[type=radio]').nth(1).check();
        await submitDialog('Add question');
        await waitForToast(page, 'Question added successfully');

        const mcq = sql(
            `SELECT CONCAT(id, '|', type, '|', points, '|', correct_answer, '|', \`order\`, '|', JSON_LENGTH(options), '|', JSON_UNQUOTE(JSON_EXTRACT(options, '$[1]'))) ` +
            `FROM exam_questions WHERE exam_id = ${examId} AND question = '${Q_MCQ}';`,
        );
        assert(
            mcq.endsWith('|multiple_choice|4|1|1|3|The second option'),
            `MCQ question row unexpected: ${mcq}`,
        );
        const mcqId = mcq.split('|')[0];
        ok('Add multiple-choice question works (options stored, correct_answer is the option index as a string)', { mcq });

        // -- true / false --
        await openAddDialog();
        await pickOption(page, 'question_type', 'True / false');
        await page.locator('#question_text').fill(Q_TF);
        await page.locator('#question_points').fill('2');
        await submitDialog('Add question');

        const tf = sql(
            `SELECT CONCAT(id, '|', type, '|', points, '|', correct_answer, '|', \`order\`, '|', IF(options IS NULL, 'null', options)) ` +
            `FROM exam_questions WHERE exam_id = ${examId} AND question = '${Q_TF}';`,
        );
        assert(tf.endsWith('|true_false|2|true|2|null'), `True/false question row unexpected: ${tf}`);
        const tfId = tf.split('|')[0];
        ok('Add true/false question works (correct_answer is the lowercase string "true", options stay NULL)', { tf });

        // -- essay (no options at all — exercises the exclude_unless rule) --
        await openAddDialog();
        await pickOption(page, 'question_type', 'Essay (graded manually)');
        await page.locator('#question_text').fill(Q_ESSAY);
        await page.locator('#question_points').fill('5');
        await submitDialog('Add question');

        const essay = sql(
            `SELECT CONCAT(id, '|', type, '|', points, '|', IF(correct_answer IS NULL, 'null', correct_answer), '|', \`order\`, '|', IF(options IS NULL, 'null', options)) ` +
            `FROM exam_questions WHERE exam_id = ${examId} AND question = '${Q_ESSAY}';`,
        );
        assert(essay.endsWith('|essay|5|null|3|null'), `Essay question row unexpected: ${essay}`);
        const essayId = essay.split('|')[0];
        ok('Add essay question works with an empty option list (exclude_unless validation rule)');

        // Total points surfaces in the summary card: 4 + 2 + 5 = 11.
        await page.waitForFunction(() => /Total points/i.test(document.body.innerText), undefined, { timeout: 30000 });
        const summary = (await page.locator('main').innerText()).replace(/\s+/g, ' ');
        assert(/Total points\s*11/i.test(summary), `Total points card should read 11: ${summary.slice(0, 300)}`);
        ok('Question list and total-points summary render after adding all three types');

        // ---------- QUESTIONS: EDIT ----------
        const mcqCard = page.locator('li', { hasText: Q_MCQ }).first();
        await mcqCard.getByRole('button', { name: 'Edit question' }).click();
        await dialog(page).waitFor({ state: 'visible', timeout: 30000 });
        assert(
            (await page.locator('#question_points').inputValue()) === '4',
            'Edit-question dialog did not prefill points',
        );
        assert(
            (await page.locator('[role="dialog"] input[placeholder="Option 2"]').inputValue()) === 'The second option',
            'Edit-question dialog did not prefill the options',
        );
        await page.locator('#question_text').fill(Q_MCQ_EDITED);
        await page.locator('#question_points').fill('6');
        await submitDialog('Save question');

        const mcqEdited = sql(
            `SELECT CONCAT(points, '|', correct_answer, '|', question) FROM exam_questions WHERE id = ${mcqId};`,
        );
        assert(
            mcqEdited === `6|1|${Q_MCQ_EDITED}`,
            `Question edit did not persist (correct answer should be untouched): ${mcqEdited}`,
        );
        ok('Edit question works (points and text updated, correct answer preserved)', { mcqEdited });

        // ---------- QUESTIONS: DELETE ----------
        const tfCard = page.locator('li', { hasText: Q_TF }).first();
        await tfCard.getByRole('button', { name: 'Delete question' }).click();
        await dialog(page).waitFor({ state: 'visible', timeout: 30000 });
        await submitDialog('Delete question');
        assert(
            sql(`SELECT COUNT(*) FROM exam_questions WHERE id = ${tfId};`) === '0',
            'Delete question did not remove the row',
        );
        ok('Delete question works (row really removed)');

        // ---------- EXAMS: UPDATE SETTINGS ----------
        await page.locator('#title').fill(EDITED_EXAM_TITLE);
        await page.locator('#passing_score').fill('80');
        await setChecked(page, '#is_published', true);
        await page.getByRole('button', { name: 'Update settings' }).click();
        await waitForToast(page, 'Exam updated successfully');

        const examAfterUpdate = sql(
            `SELECT CONCAT(title, '|', passing_score, '|', is_published) FROM exams WHERE id = ${examId};`,
        );
        assert(
            examAfterUpdate === `${EDITED_EXAM_TITLE}|80|1`,
            `Exam update did not persist: ${examAfterUpdate}`,
        );
        ok('Update exam settings works (title, passing score and publish flag persisted)', { examAfterUpdate });

        // ---------- RESULTS + GRADE ----------
        // Seed one submitted attempt: MCQ correct (6 pts), essay pending manual grading.
        sql(
            `INSERT INTO exam_attempts (exam_id, user_id, score, total_points, passed, status, started_at, submitted_at, created_at, updated_at) ` +
            `VALUES (${examId}, ${studentId}, 6, 11, NULL, 'submitted', NOW(), NOW(), NOW(), NOW());`,
        );
        const attemptId = sql(`SELECT id FROM exam_attempts WHERE exam_id = ${examId} ORDER BY id DESC LIMIT 1;`);
        assert(/^\d+$/.test(attemptId), 'Attempt fixture was not created');
        sql(
            `INSERT INTO exam_answers (attempt_id, question_id, answer, points_earned, is_correct, created_at, updated_at) VALUES ` +
            `(${attemptId}, ${mcqId}, '1', 6, 1, NOW(), NOW()), ` +
            `(${attemptId}, ${essayId}, '${ESSAY_ANSWER}', NULL, NULL, NOW(), NOW());`,
        );
        const essayAnswerId = sql(
            `SELECT id FROM exam_answers WHERE attempt_id = ${attemptId} AND question_id = ${essayId};`,
        );
        ok('Attempt fixture seeded (one auto-graded MCQ answer, one essay awaiting a human)', { attemptId });

        await coldGoto(page, `/admin/exams/${examId}/results`);
        assert(
            (await coldComponent(page)) === 'Admin/Exams/Results',
            `Expected Admin/Exams/Results, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/exams/{id}/results renders Admin/Exams/Results (no longer Blade)');

        await page.waitForFunction(() => /Total attempts/i.test(document.body.innerText), undefined, { timeout: 30000 });
        assert((await statValue(page, 'Total attempts')) === '1', 'Total attempts stat should be 1');
        assert((await statValue(page, 'Passed')) === '0', 'Passed stat should be 0 before grading');
        assert((await statValue(page, 'Failed')) === '0', 'Failed stat should be 0 before grading');
        assert(
            (await page.locator('table tbody tr').count()) === 1,
            'Results table should show the single seeded attempt',
        );
        assert(
            /Passing: 80%/.test(await page.locator('main').innerText()),
            'Average-score card should show the exam passing threshold',
        );
        ok('Results page renders attempt stats and the seeded student');

        // Grade the essay. `Button` + `href` renders an Inertia <Link>, so its
        // ARIA role is `link`, not `button`.
        await page.locator('table tbody tr').first().getByRole('link', { name: 'Grade attempt' }).click();
        await waitForPathMatch(page, '^/admin/exam-attempts/\\d+/grade$');
        ok('Results table links through to the grade page');

        // Cold load before asserting component identity: `#app`'s data-page is a
        // boot-time snapshot and stays stale after an SPA visit.
        await coldGoto(page, `/admin/exam-attempts/${attemptId}/grade`);
        assert(
            (await coldComponent(page)) === 'Admin/Exams/Grade',
            `Expected Admin/Exams/Grade, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/exam-attempts/{id}/grade renders Admin/Exams/Grade (no longer Blade)');

        await page.waitForFunction(
            () => /Student answer/i.test(document.body.innerText),
            undefined,
            { timeout: 30000 },
        );
        assert(
            /Auto-graded: 6 \/ 11 pts/.test(await page.locator('main').innerText()),
            'Grade page should show the auto-graded subtotal',
        );
        assert(
            (await page.locator(`#points-${essayAnswerId}`).count()) === 1,
            'The essay question should expose a manual points field',
        );
        assert(
            (await page.locator('main input[type=number]').count()) === 1,
            'Only the essay answer should be manually gradeable',
        );
        ok('Grade page shows every answer but only essays get manual fields');

        await page.locator(`#points-${essayAnswerId}`).fill('3');
        await page.locator(`#feedback-${essayAnswerId}`).fill('Good structure, tighten the conclusion.');
        await page.getByRole('button', { name: 'Save grades' }).click();
        await waitForPathMatch(page, '^/admin/exams/\\d+/results$');
        await waitForToast(page, 'Exam graded successfully');

        // 6 (MCQ) + 3 (essay) = 9 of 11 = 81.8% >= 80% -> passed.
        const graded = sql(
            `SELECT CONCAT(score, '|', passed, '|', status) FROM exam_attempts WHERE id = ${attemptId};`,
        );
        assert(graded === '9|1|graded', `Grading did not persist: ${graded}`);
        const gradedAnswer = sql(
            `SELECT CONCAT(points_earned, '|', IF(feedback IS NULL, 'none', 'set')) FROM exam_answers WHERE id = ${essayAnswerId};`,
        );
        assert(gradedAnswer === '3|set', `Essay grade did not persist: ${gradedAnswer}`);
        ok('Saving grades re-sums every answer, marks the attempt graded and sets pass/fail', { graded });

        await page.waitForFunction(() => /Total attempts/i.test(document.body.innerText), undefined, { timeout: 30000 });
        assert((await statValue(page, 'Passed')) === '1', 'Passed stat should be 1 after grading');
        assert((await statValue(page, 'Average score')) === '82%', `Average should be 82%, got ${await statValue(page, 'Average score')}`);
        assert(
            (await page.locator('table tbody tr').first().innerText()).includes('Passed'),
            'The graded attempt row should now read Passed',
        );
        ok('Results stats refresh after grading (pass rate, average, row status)');

        await coldGoto(page, `/admin/exams/${examId}/results`);
        await page.screenshot({ path: `${out}/sanpya-admin-vue-exam-results.png`, fullPage: true });
        ok('Screenshot: exam results');

        // ---------- STATS COVER ALL ATTEMPTS, NOT JUST THE PAGE ----------
        // 20 more graded attempts push the exam past one page (per_page = 20).
        const values = Array.from(
            { length: 20 },
            () => `(${examId}, ${studentId}, 11, 11, 1, 'graded', NOW(), NOW(), NOW(), NOW())`,
        ).join(',');
        sql(
            `INSERT INTO exam_attempts (exam_id, user_id, score, total_points, passed, status, started_at, submitted_at, created_at, updated_at) ` +
            `VALUES ${values};`,
        );

        await coldGoto(page, `/admin/exams/${examId}/results`);
        await page.waitForFunction(() => /Total attempts/i.test(document.body.innerText), undefined, { timeout: 30000 });
        assert((await statValue(page, 'Total attempts')) === '21', `Total attempts should be 21, got ${await statValue(page, 'Total attempts')}`);
        assert((await statValue(page, 'Passed')) === '21', `Passed should be 21, got ${await statValue(page, 'Passed')}`);
        assert((await statValue(page, 'Average score')) === '99%', `Average should be 99%, got ${await statValue(page, 'Average score')}`);
        assert(
            (await page.locator('table tbody tr').count()) === 20,
            'The table should paginate at 20 rows while the stats count all 21',
        );
        ok('Results stats aggregate over every attempt, not just the current page', {
            total: 21, pageRows: 20,
        });

        // ---------- Mobile overflow ----------
        const urls = [
            '/admin/exams',
            '/admin/exams/create',
            `/admin/exams/${examId}/edit`,
            `/admin/exams/${examId}/results`,
            `/admin/exam-attempts/${attemptId}/grade`,
        ];
        for (const url of urls) {
            await coldGoto(page, url);
            await page.setViewportSize({ width: 390, height: 844 });
            await page.waitForTimeout(400);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            assert(overflow <= 1, `${url}: horizontal overflow of ${overflow}px at 390px`);
            await page.setViewportSize({ width: 1440, height: 960 });
        }
        ok('Exams cluster: no horizontal overflow at 390px on any of the five pages');

        // ---------- EXAMS: DELETE (cascade) ----------
        await coldGoto(page, '/admin/exams');
        const examRowEl = page.locator('table tbody tr', { hasText: EDITED_EXAM_TITLE }).first();
        await examRowEl.waitFor({ state: 'visible', timeout: 30000 });
        await examRowEl.getByRole('button', { name: 'Delete exam' }).click();
        const delDialog = dialog(page);
        await delDialog.waitFor({ state: 'visible', timeout: 30000 });
        const delText = (await delDialog.innerText()).replace(/\s+/g, ' ');
        assert(/permanently/i.test(delText) && /cannot be undone/i.test(delText),
            `Exam delete dialog should warn it is permanent: "${delText}"`);
        await delDialog.getByRole('button', { name: 'Delete exam' }).click();
        await waitForToast(page, 'Exam deleted successfully');

        assert(sql(`SELECT COUNT(*) FROM exams WHERE id = ${examId};`) === '0', 'Delete did not remove the exam');
        assert(
            sql(`SELECT COUNT(*) FROM exam_questions WHERE exam_id = ${examId};`) === '0',
            'Deleting the exam left its questions behind',
        );
        assert(
            sql(`SELECT COUNT(*) FROM exam_attempts WHERE exam_id = ${examId};`) === '0',
            'Deleting the exam left its attempts behind',
        );
        assert(
            sql(`SELECT COUNT(*) FROM exam_answers WHERE attempt_id = ${attemptId};`) === '0',
            'Deleting the exam left its answers behind',
        );
        ok('Delete exam cascades to questions, attempts and answers');

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 5) });

        fs.writeFileSync(
            `${out}/sanpya-admin-exams-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nEXAMS: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-exams-results.json`,
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
        console.log('[cleanup] throwaway exam (with questions, attempts and answers) removed');
    }
})();
