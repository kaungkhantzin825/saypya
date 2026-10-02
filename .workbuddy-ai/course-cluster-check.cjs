// Admin Courses cluster — end-to-end verification.
//
// Covers the four pages migrated in this batch:
//   /admin/courses/create   -> Admin/Courses/Form
//   /admin/courses/{id}/edit-> Admin/Courses/Form (prefilled)
//   /admin/courses/{id}     -> Admin/Courses/Show
//   /admin/courses/{id}/content -> Admin/Courses/Content
//
// Everything runs against ONE throwaway course created through the real create
// form (including a genuine multipart thumbnail upload), and the course, its
// sections/lessons, and the uploaded file are all removed in a `finally` block.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/course-cluster-check.cjs
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

const STAMP = Date.now();
const TITLE = `ZZ Cluster Course ${STAMP}`;
const EDITED_TITLE = `ZZ Cluster Course ${STAMP} (edited)`;
const SECTION_TITLE = `ZZ Section ${STAMP}`;
const EDITED_SECTION_TITLE = `ZZ Section ${STAMP} edited`;
const LESSON_TITLE = `ZZ Lesson ${STAMP}`;
const EDITED_LESSON_TITLE = `ZZ Lesson ${STAMP} edited`;

const LOCAL_THUMB = path.join(out, 'pilot-course-thumb.png');
// Smallest valid 1x1 PNG — Laravel's `image` rule sniffs the real header.
const PNG_1PX = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'base64',
);

let courseId = null;
let uploadedThumbRel = null;

function sql(statement) {
    return execFileSync(MYSQL, ['-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
}

function cleanup() {
    try {
        if (courseId) {
            // Sections/lessons cascade from the course, but be explicit so an
            // aborted run cannot leave orphans behind.
            const sections = sql(`SELECT id FROM sections WHERE course_id = ${courseId};`);
            if (sections) {
                sql(`DELETE FROM lessons WHERE section_id IN (${sections.split('\n').join(',')});`);
                sql(`DELETE FROM sections WHERE course_id = ${courseId};`);
            }
            sql(`DELETE FROM courses WHERE id = ${courseId};`);
        }
    } catch { /* ignore */ }
    try {
        if (uploadedThumbRel) {
            const abs = path.join(project, 'storage', 'app', 'public', uploadedThumbRel);
            if (fs.existsSync(abs)) fs.unlinkSync(abs);
        }
    } catch { /* ignore */ }
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

    /** reka-ui renders options in a portal; pick by visible label. */
    const pickSelect = async (page, triggerId, label) => {
        await page.locator(`#${triggerId}`).click();
        await page.getByRole('option', { name: label, exact: true }).first().click();
    };

    /** Pick the first option of a select (there is no placeholder row — the
     *  empty state is expressed with the Select's `placeholder` prop). */
    const pickFirstReal = async (page, triggerId) => {
        await page.locator(`#${triggerId}`).click();
        await page.getByRole('option').first().click();
    };

    const coldGoto = async (page, url) => {
        await page.goto(`${base}${url}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
    };

    try {
        // ---------- Fixture: thumbnail file ----------
        fs.mkdirSync(out, { recursive: true });
        fs.writeFileSync(LOCAL_THUMB, PNG_1PX);
        ok('Fixture: local PNG ready for the multipart upload');

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

        // ---------- CREATE ----------
        await coldGoto(page, '/admin/courses/create');
        assert(
            (await coldComponent(page)) === 'Admin/Courses/Form',
            `Expected Admin/Courses/Form on create, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/courses/create renders Inertia component Admin/Courses/Form (no longer Blade)');

        await page.locator('#title').fill(TITLE);
        await pickFirstReal(page, 'category_id');
        await pickFirstReal(page, 'instructor_id');
        await page.locator('#description').fill('Throwaway course created by the automated cluster check.');
        await page.locator('#short_description').fill('Automated check fixture.');
        await page.locator('#price').fill('25000');
        await page.locator('#discount_price').fill('19000');
        await pickSelect(page, 'level', 'Beginner');
        await pickSelect(page, 'status', 'Draft');

        // Repeatable list rows — the "what you'll learn" builder.
        await page.getByRole('button', { name: 'Add outcome' }).click();
        await page.locator('input[placeholder="Enter a learning outcome"]').first().fill('Build a real project');
        await page.getByRole('button', { name: 'Add requirement' }).click();
        await page.locator('input[placeholder="Enter a requirement"]').first().fill('A laptop');

        await page.locator('input[type=file]').setInputFiles(LOCAL_THUMB);

        // Read the state back before submitting — a Select that silently fails to
        // commit shows up here instead of as a confusing 422 from the server.
        const formState = {
            title: await page.locator('#title').inputValue(),
            category: (await page.locator('#category_id').innerText()).trim(),
            instructor: (await page.locator('#instructor_id').innerText()).trim(),
            description: await page.locator('#description').inputValue(),
            price: await page.locator('#price').inputValue(),
            level: (await page.locator('#level').innerText()).trim(),
            status: (await page.locator('#status').innerText()).trim(),
            outcomes: await page.locator('input[placeholder="Enter a learning outcome"]').count(),
        };
        assert(formState.title === TITLE, `Title not set: ${JSON.stringify(formState)}`);
        assert(
            formState.category && !/select/i.test(formState.category),
            `Category Select did not commit a value: ${JSON.stringify(formState)}`,
        );
        assert(
            formState.instructor && !/select/i.test(formState.instructor),
            `Instructor Select did not commit a value: ${JSON.stringify(formState)}`,
        );
        assert(formState.description.length > 10, `Description not set: ${JSON.stringify(formState)}`);
        assert(formState.price.startsWith('25000'), `Price not set: ${JSON.stringify(formState)}`);
        assert(formState.outcomes === 1, `Outcome row not added: ${JSON.stringify(formState)}`);
        ok('Create form: fields filled and a real image file attached', formState);

        await page.getByRole('button', { name: 'Create course' }).click();

        // Wait for the redirect into the content page, or for a validation alert.
        await page.waitForFunction(
            () =>
                /^\/admin\/courses\/\d+\/content$/.test(window.location.pathname) ||
                !!document.querySelector('[role="alert"]'),
            undefined,
            { timeout: 90000 },
        );

        if (!/^\/admin\/courses\/\d+\/content$/.test(new URL(page.url()).pathname)) {
            const alert = await page
                .locator('[role="alert"]')
                .first()
                .innerText()
                .catch(() => '(no alert)');
            const fieldErrors = await page
                .locator('p.text-destructive')
                .allInnerTexts()
                .catch(() => []);
            throw new Error(
                `Create did not redirect. Alert: ${alert.replace(/\s+/g, ' ')} | field errors: ${fieldErrors.join(' ; ')}`,
            );
        }
        await page.waitForLoadState('networkidle').catch(() => {});

        const pathAfterCreate = new URL(page.url()).pathname;
        courseId = Number(pathAfterCreate.match(/\/admin\/courses\/(\d+)\/content$/)[1]);
        assert(Number.isInteger(courseId), 'Could not parse the new course id from the redirect');

        const dbRow = sql(
            `SELECT CONCAT(status, '|', price, '|', level, '|', IF(thumbnail IS NULL OR thumbnail = '', 'none', 'set')) ` +
            `FROM courses WHERE id = ${courseId};`,
        );
        assert(dbRow === 'draft|25000.00|beginner|set', `Unexpected row after create: ${dbRow}`);
        uploadedThumbRel = sql(`SELECT thumbnail FROM courses WHERE id = ${courseId};`);
        ok('Create posts a multipart form and persists the course (status/price/level/thumbnail)', { id: courseId, thumbnail: uploadedThumbRel });

        const storedArrays = sql(
            `SELECT CONCAT(what_you_learn, '~', requirements) FROM courses WHERE id = ${courseId};`,
        );
        assert(/Build a real project/.test(storedArrays) && /A laptop/.test(storedArrays),
            `Repeatable list fields were not saved: ${storedArrays}`);
        ok('Repeatable "what you\'ll learn" / "requirements" rows persist as JSON');

        // ---------- CONTENT ----------
        await coldGoto(page, `/admin/courses/${courseId}/content`);
        assert(
            (await coldComponent(page)) === 'Admin/Courses/Content',
            `Expected Admin/Courses/Content, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/courses/{id}/content renders Admin/Courses/Content (no longer Blade)');

        // Add a section.
        await page.getByRole('button', { name: 'Add section' }).first().click();
        const sectionDialog = page.getByRole('dialog');
        await sectionDialog.waitFor({ state: 'visible' });
        await sectionDialog.locator('#section-title').fill(SECTION_TITLE);
        await sectionDialog.locator('#section-description').fill('Section created by the automated check.');
        await sectionDialog.getByRole('button', { name: 'Add section' }).click();
        const sectionToast = await waitForToast(page, 'Section created');
        await page.waitForFunction(
            (title) => document.body.innerText.includes(title),
            SECTION_TITLE,
            { timeout: 30000 },
        );
        const sectionId = Number(sql(`SELECT id FROM sections WHERE course_id = ${courseId} AND title = '${SECTION_TITLE}';`));
        assert(Number.isInteger(sectionId) && sectionId > 0, 'Section was not persisted');
        ok('Add section works (dialog -> DB, Inertia request not swallowed by the JSON branch)', { toast: sectionToast, sectionId });

        // Add a lesson.
        await page.getByRole('button', { name: 'Add lesson' }).first().click();
        const lessonDialog = page.getByRole('dialog');
        await lessonDialog.waitFor({ state: 'visible' });
        await lessonDialog.locator('#lesson-title').fill(LESSON_TITLE);
        await lessonDialog.locator('#lesson-duration').fill('615');
        await lessonDialog.locator('#lesson-video').fill('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        await lessonDialog.locator('#lesson-content').fill('Lesson body from the automated check.');
        await lessonDialog.locator('#lesson-preview').click();
        await lessonDialog.getByRole('button', { name: 'Add lesson' }).click();
        const lessonToast = await waitForToast(page, 'Lesson created');
        await page.waitForFunction((title) => document.body.innerText.includes(title), LESSON_TITLE, { timeout: 30000 });
        const lessonId = Number(sql(`SELECT id FROM lessons WHERE section_id = ${sectionId} AND title = '${LESSON_TITLE}';`));
        assert(Number.isInteger(lessonId) && lessonId > 0, 'Lesson was not persisted');
        const lessonRow = sql(`SELECT CONCAT(type, '|', video_duration, '|', is_preview) FROM lessons WHERE id = ${lessonId};`);
        assert(lessonRow === 'video|615|1', `Unexpected lesson row: ${lessonRow}`);
        ok('Add lesson works (type/duration/preview persisted)', { toast: lessonToast, lessonId });

        // Duration badge is rendered as m:ss by the Vue page.
        await page.waitForFunction(() => /10:15/.test(document.body.innerText), undefined, { timeout: 30000 });
        ok('Lesson duration renders as m:ss (10:15)');

        // Edit the lesson.
        await page.getByRole('button', { name: 'Edit lesson' }).first().click();
        const editLessonDialog = page.getByRole('dialog');
        await editLessonDialog.waitFor({ state: 'visible' });
        await editLessonDialog.locator('#lesson-title').fill(EDITED_LESSON_TITLE);
        await editLessonDialog.getByRole('button', { name: 'Save lesson' }).click();
        await waitForToast(page, 'Lesson updated');
        assert(
            sql(`SELECT title FROM lessons WHERE id = ${lessonId};`) === EDITED_LESSON_TITLE,
            'Lesson edit did not persist',
        );
        ok('Edit lesson works');

        // Edit the section.
        await page.getByRole('button', { name: 'Edit section' }).first().click();
        const editSectionDialog = page.getByRole('dialog');
        await editSectionDialog.waitFor({ state: 'visible' });
        await editSectionDialog.locator('#section-title').fill(EDITED_SECTION_TITLE);
        await editSectionDialog.getByRole('button', { name: 'Save section' }).click();
        await waitForToast(page, 'Section updated');
        assert(
            sql(`SELECT title FROM sections WHERE id = ${sectionId};`) === EDITED_SECTION_TITLE,
            'Section edit did not persist',
        );
        ok('Edit section works');

        // ---------- SHOW ----------
        await coldGoto(page, `/admin/courses/${courseId}`);
        assert(
            (await coldComponent(page)) === 'Admin/Courses/Show',
            `Expected Admin/Courses/Show, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/courses/{id} renders Admin/Courses/Show (no longer Blade)');
        await page.waitForFunction((t) => document.body.innerText.includes(t), EDITED_SECTION_TITLE, { timeout: 30000 });
        await page.waitForFunction((t) => document.body.innerText.includes(t), EDITED_LESSON_TITLE, { timeout: 30000 });
        ok('Show page renders the curriculum from the DB');

        // The Show page links to the (now Inertia) content page.
        await page.getByRole('link', { name: 'Manage content' }).click();
        await waitForPath(page, `/admin/courses/${courseId}/content`);
        ok('Show -> "Manage content" is an Inertia visit (link no longer marked external)');
        await page.goBack();
        await page.waitForLoadState('networkidle').catch(() => {});

        // ---------- EDIT ----------
        await coldGoto(page, `/admin/courses/${courseId}/edit`);
        assert(
            (await coldComponent(page)) === 'Admin/Courses/Form',
            `Expected Admin/Courses/Form on edit, got ${await coldComponent(page)}`,
        );
        const prefilled = await page.locator('#title').inputValue();
        assert(prefilled === TITLE, `Edit form not prefilled (got "${prefilled}")`);
        ok('GET /admin/courses/{id}/edit renders Admin/Courses/Form prefilled from the DB');

        const prefilledPrice = await page.locator('#price').inputValue();
        assert(prefilledPrice.startsWith('25000'), `Price not prefilled (got "${prefilledPrice}")`);
        const prefilledLearn = await page.locator('input[placeholder="Enter a learning outcome"]').first().inputValue();
        assert(prefilledLearn === 'Build a real project', `what_you_learn not prefilled (got "${prefilledLearn}")`);
        ok('Edit form prefills price and the repeatable list rows');

        await page.locator('#title').fill(EDITED_TITLE);
        await page.getByRole('button', { name: 'Save changes' }).click();
        await waitForPath(page, '/admin/courses', 90000);
        await waitForToast(page, 'Course updated successfully');
        assert(sql(`SELECT title FROM courses WHERE id = ${courseId};`) === EDITED_TITLE, 'Course edit did not persist');
        ok('Edit course works (PUT with _method spoofing, no new thumbnail required)');

        // The replacement thumbnail survived the edit (it was not cleared).
        const thumbAfterEdit = sql(`SELECT thumbnail FROM courses WHERE id = ${courseId};`);
        assert(thumbAfterEdit === uploadedThumbRel, `Thumbnail changed unexpectedly: ${thumbAfterEdit}`);
        ok('Editing without picking a new file keeps the stored thumbnail');

        // ---------- DELETE lesson + section ----------
        await coldGoto(page, `/admin/courses/${courseId}/content`);

        await page.getByRole('button', { name: 'Delete lesson' }).first().click();
        const delLesson = page.getByRole('dialog');
        await delLesson.waitFor({ state: 'visible' });
        await delLesson.getByRole('button', { name: 'Delete lesson' }).click();
        await waitForToast(page, 'Lesson deleted');
        assert(sql(`SELECT COUNT(*) FROM lessons WHERE id = ${lessonId};`) === '0', 'Lesson delete did not persist');
        ok('Delete lesson works');

        await page.getByRole('button', { name: 'Delete section' }).first().click();
        const delSection = page.getByRole('dialog');
        await delSection.waitFor({ state: 'visible' });
        const delSectionText = (await delSection.innerText()).replace(/\s+/g, ' ');
        assert(/cannot be undone/i.test(delSectionText), `Section delete dialog should warn: "${delSectionText}"`);
        await delSection.getByRole('button', { name: 'Delete section' }).click();
        await waitForToast(page, 'Section deleted');
        assert(sql(`SELECT COUNT(*) FROM sections WHERE id = ${sectionId};`) === '0', 'Section delete did not persist');
        ok('Delete section works (warns it also removes its lessons)');

        // ---------- Screenshots ----------
        await coldGoto(page, `/admin/courses/${courseId}/content`);
        await page.screenshot({ path: `${out}/sanpya-admin-vue-course-content.png`, fullPage: true });
        ok('Screenshot: course content page');

        await coldGoto(page, `/admin/courses/${courseId}`);
        await page.screenshot({ path: `${out}/sanpya-admin-vue-course-show.png`, fullPage: true });
        ok('Screenshot: course detail page');

        await coldGoto(page, `/admin/courses/${courseId}/edit`);
        await page.screenshot({ path: `${out}/sanpya-admin-vue-course-form.png`, fullPage: true });
        ok('Screenshot: course edit form');

        // ---------- Mobile overflow ----------
        for (const url of [`/admin/courses/${courseId}`, `/admin/courses/${courseId}/content`, `/admin/courses/${courseId}/edit`]) {
            await coldGoto(page, url);
            await page.setViewportSize({ width: 390, height: 844 });
            await page.waitForTimeout(400);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            assert(overflow <= 1, `${url}: horizontal overflow of ${overflow}px at 390px`);
            await page.setViewportSize({ width: 1440, height: 960 });
        }
        ok('Show / Content / Form pages: no horizontal overflow at 390px');

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 5) });

        fs.writeFileSync(
            `${out}/sanpya-admin-course-cluster-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), courseId, results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nCOURSE CLUSTER: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-course-cluster-results.json`,
            JSON.stringify({ base, courseId, results, pageErrors, consoleErrors }, null, 2),
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
        try { if (fs.existsSync(LOCAL_THUMB)) fs.unlinkSync(LOCAL_THUMB); } catch { /* ignore */ }
        console.log('[cleanup] throwaway course, its sections/lessons and the uploaded thumbnail removed');
    }
})();
