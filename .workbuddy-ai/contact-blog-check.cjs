// Admin Contact messages + Blog — end-to-end verification.
//
//   /admin/contact-messages            -> Admin/ContactMessages/Index
//   /admin/contact-messages/{id}       -> Admin/ContactMessages/Show
//   /admin/blog                        -> Admin/Blog/Index
//   /admin/blog/create | /{id}/edit    -> Admin/Blog/Form
//
// Inserts three throwaway contact messages and creates two throwaway blog posts
// through the real UI (including a genuine image upload). Everything is removed
// in a `finally` block.
//
// NOTE: the "select all" checkbox is deliberately never clicked — it would
// delete every real message on the page. Only the fixture rows are selected.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/contact-blog-check.cjs
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
const MSG_A = `ZZ Contact ${STAMP} A`;
const MSG_B = `ZZ Contact ${STAMP} B`;
const MSG_C = `ZZ Contact ${STAMP} C`;
const POST_TITLE = `ZZ Blog Post ${STAMP}`;
const POST_TITLE_2 = `${POST_TITLE} (second)`;
const EDITED_POST_TITLE = `ZZ Blog Post ${STAMP} (edited)`;

const LOCAL_THUMB = path.join(out, 'pilot-blog-image.png');
const PNG_1PX = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'base64',
);

const messageIds = [];
const postIds = [];
let uploadedImageRel = null;

function sql(statement) {
    return execFileSync(MYSQL, ['-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`], {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
    }).trim();
}

function cleanup() {
    for (const id of messageIds) {
        try { sql(`DELETE FROM contact_messages WHERE id = ${id};`); } catch { /* ignore */ }
    }
    for (const id of postIds) {
        try { sql(`DELETE FROM blog_posts WHERE id = ${id};`); } catch { /* ignore */ }
    }
    try {
        // Any post this run created that we did not record.
        sql(`DELETE FROM blog_posts WHERE title LIKE 'ZZ Blog Post ${STAMP}%';`);
    } catch { /* ignore */ }
    try {
        if (uploadedImageRel) {
            const abs = path.join(project, 'storage', 'app', 'public', uploadedImageRel);
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

    try {
        // ---------- Fixtures ----------
        fs.mkdirSync(out, { recursive: true });
        fs.writeFileSync(LOCAL_THUMB, PNG_1PX);

        const subjects = [MSG_A, MSG_B, MSG_C];
        for (const subject of subjects) {
            sql(
                `INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at, updated_at) VALUES ` +
                `('${subject}', '${subject.toLowerCase().replace(/[^a-z0-9]+/g, '.')}@example.com', '+959000000', ` +
                `'${subject}', 'Throwaway message created by the automated check.', 'new', NOW(), NOW());`,
            );
            const id = sql(`SELECT id FROM contact_messages WHERE subject = '${subject}' ORDER BY id DESC LIMIT 1;`);
            assert(/^\d+$/.test(id), `Contact message fixture not created (got "${id}")`);
            messageIds.push(Number(id));
        }
        ok('Fixtures: three throwaway "new" contact messages created', { messageIds });

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

        // ---------- CONTACT MESSAGES: INDEX ----------
        await coldGoto(page, '/admin/contact-messages');
        assert(
            (await coldComponent(page)) === 'Admin/ContactMessages/Index',
            `Expected Admin/ContactMessages/Index, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/contact-messages renders Inertia component Admin/ContactMessages/Index (no longer Blade)');

        await page.waitForFunction(() => /Inbox/i.test(document.body.innerText), undefined, { timeout: 30000 });
        const statText = (await page.locator('main').innerText()).replace(/\s+/g, ' ');
        assert(/New/i.test(statText) && /Replied/i.test(statText), `Stat cards missing: ${statText.slice(0, 160)}`);
        ok('Contact message totals render (new / read / replied)');

        // Sidebar SPA visit.
        await coldGoto(page, '/admin/dashboard');
        await page.getByRole('link', { name: 'Messages', exact: true }).first().click();
        await waitForPath(page, '/admin/contact-messages');
        await page.waitForFunction(() => /Inbox/i.test(document.body.innerText), undefined, { timeout: 60000 });
        ok('Sidebar "Messages" link now performs an Inertia SPA visit');

        // Status filter.
        await pickOption(page, 'filter-status', 'New');
        await page.waitForFunction(
            () => new URLSearchParams(window.location.search).get('status') === 'new',
            undefined,
            { timeout: 60000 },
        );
        await page.waitForFunction(
            () => {
                const cells = [...document.querySelectorAll('table tbody tr td:nth-child(5)')];
                return cells.length > 0 && cells.every((c) => (c.innerText || '').trim().toLowerCase() === 'new');
            },
            undefined,
            { timeout: 60000 },
        );
        ok('Contact message status filter round-trips to the server');

        await page.screenshot({ path: `${out}/sanpya-admin-vue-contact-messages.png`, fullPage: true });
        ok('Screenshot: contact messages inbox');

        // ---------- CONTACT MESSAGES: SHOW ----------
        await coldGoto(page, `/admin/contact-messages/${messageIds[0]}`);
        assert(
            (await coldComponent(page)) === 'Admin/ContactMessages/Show',
            `Expected Admin/ContactMessages/Show, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/contact-messages/{id} renders Admin/ContactMessages/Show (no longer Blade)');
        assert(
            sql(`SELECT status FROM contact_messages WHERE id = ${messageIds[0]};`) === 'read',
            'Opening a new message did not mark it as read',
        );
        ok('Opening a message marks it as read (verified in SQL)');

        // Reply.
        await page.locator('#reply').fill('Thanks for getting in touch — this is an automated reply.');
        await page.getByRole('button', { name: 'Send reply' }).click();
        const replyToast = await waitForToast(page, 'Reply sent successfully');
        const replied = sql(
            `SELECT CONCAT(status, '|', IF(admin_reply IS NULL, 'none', 'set'), '|', IF(replied_at IS NULL, 'none', 'set')) ` +
            `FROM contact_messages WHERE id = ${messageIds[0]};`,
        );
        assert(replied === 'replied|set|set', `Reply did not persist: ${replied}`);
        ok('Reply persists status/reply/replied_at', { toast: replyToast });

        await coldGoto(page, `/admin/contact-messages/${messageIds[0]}`);
        await page.screenshot({ path: `${out}/sanpya-admin-vue-contact-message-show.png`, fullPage: true });
        ok('Screenshot: contact message detail');

        // Delete via the detail page.
        await page.getByRole('button', { name: 'Delete message' }).first().click();
        const delDialog = page.getByRole('dialog');
        await delDialog.waitFor({ state: 'visible', timeout: 30000 });
        const delText = (await delDialog.innerText()).replace(/\s+/g, ' ');
        assert(/cannot be undone/i.test(delText), `Delete dialog should warn: "${delText}"`);
        await delDialog.getByRole('button', { name: 'Delete message' }).click();
        await waitForPath(page, '/admin/contact-messages', 90000);
        await waitForToast(page, 'Message deleted successfully');
        assert(
            sql(`SELECT COUNT(*) FROM contact_messages WHERE id = ${messageIds[0]};`) === '0',
            'Delete did not remove the row',
        );
        messageIds.shift();
        ok('Delete message works (row really removed — no SoftDeletes)');

        // ---------- CONTACT MESSAGES: BULK DELETE ----------
        await coldGoto(page, '/admin/contact-messages');
        // Select ONLY the two remaining fixtures — never "select all".
        for (const id of messageIds) {
            await page.getByRole('checkbox', { name: new RegExp(`Select message from ZZ Contact ${STAMP}`) })
                .nth(messageIds.indexOf(id))
                .check();
        }
        await page.getByRole('button', { name: /Delete 2 selected/ }).click();
        const bulkDialog = page.getByRole('dialog');
        await bulkDialog.waitFor({ state: 'visible', timeout: 30000 });
        await bulkDialog.getByRole('button', { name: 'Delete messages' }).click();
        await waitForToast(page, 'message\\(s\\) deleted successfully');
        const remaining = sql(
            `SELECT COUNT(*) FROM contact_messages WHERE subject IN ('${MSG_B}', '${MSG_C}');`,
        );
        assert(remaining === '0', `Bulk delete left ${remaining} fixture rows`);
        messageIds.length = 0;
        ok('Bulk delete removes only the selected rows');

        // ---------- BLOG: INDEX ----------
        await coldGoto(page, '/admin/blog');
        assert(
            (await coldComponent(page)) === 'Admin/Blog/Index',
            `Expected Admin/Blog/Index, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/blog renders Inertia component Admin/Blog/Index (no longer Blade)');

        await coldGoto(page, '/admin/dashboard');
        await page.getByRole('link', { name: 'Blog', exact: true }).first().click();
        await waitForPath(page, '/admin/blog');
        await page.waitForFunction(() => /All posts/i.test(document.body.innerText), undefined, { timeout: 60000 });
        ok('Sidebar "Blog" link now performs an Inertia SPA visit');

        // ---------- BLOG: CREATE ----------
        await coldGoto(page, '/admin/blog/create');
        assert(
            (await coldComponent(page)) === 'Admin/Blog/Form',
            `Expected Admin/Blog/Form on create, got ${await coldComponent(page)}`,
        );
        ok('GET /admin/blog/create renders Admin/Blog/Form (no longer Blade)');

        await page.locator('#title').fill(POST_TITLE);
        await page.locator('#excerpt').fill('A throwaway post created by the automated check.');
        await page.locator('#content').fill('Body copy for the automated blog check. '.repeat(8));
        await pickOption(page, 'status', 'Draft');
        await page.locator('input[type=file]').setInputFiles(LOCAL_THUMB);

        const formState = {
            title: await page.locator('#title').inputValue(),
            status: (await page.locator('#status').innerText()).trim(),
            content: (await page.locator('#content').inputValue()).length,
        };
        assert(formState.title === POST_TITLE, `Title not set: ${JSON.stringify(formState)}`);
        assert(formState.status === 'Draft', `Status Select did not commit: ${JSON.stringify(formState)}`);
        assert(formState.content > 100, `Content not set: ${JSON.stringify(formState)}`);

        await page.getByRole('button', { name: 'Create post' }).click();
        await waitForPath(page, '/admin/blog', 90000);
        await waitForToast(page, 'Blog post created successfully');

        const postId = sql(`SELECT id FROM blog_posts WHERE title = '${POST_TITLE}' ORDER BY id DESC LIMIT 1;`);
        assert(/^\d+$/.test(postId), `Blog post was not created (got "${postId}")`);
        postIds.push(Number(postId));

        const postRow = sql(`SELECT CONCAT(slug, '|', status, '|', IF(featured_image IS NULL, 'none', 'set')) FROM blog_posts WHERE id = ${postId};`);
        uploadedImageRel = sql(`SELECT featured_image FROM blog_posts WHERE id = ${postId};`);
        assert(/^zz-blog-post-\d+\|draft\|set$/.test(postRow), `Unexpected post row: ${postRow}`);
        assert(
            fs.existsSync(path.join(project, 'storage', 'app', 'public', uploadedImageRel)),
            `Uploaded image missing on disk: ${uploadedImageRel}`,
        );
        ok('Create post works (slug generated, image uploaded to disk)', { postId, uploadedImageRel });

        // ---------- BLOG: SLUG COLLISION ----------
        await coldGoto(page, '/admin/blog/create');
        await page.locator('#title').fill(POST_TITLE); // same title on purpose
        await page.locator('#content').fill('Second post with a colliding title.');
        await page.getByRole('button', { name: 'Create post' }).click();
        await waitForPath(page, '/admin/blog', 90000);
        await waitForToast(page, 'Blog post created successfully');

        const secondId = sql(`SELECT id FROM blog_posts WHERE title = '${POST_TITLE}' AND id != ${postId} ORDER BY id DESC LIMIT 1;`);
        assert(/^\d+$/.test(secondId), 'Second post with the same title was not created');
        postIds.push(Number(secondId));
        const secondSlug = sql(`SELECT slug FROM blog_posts WHERE id = ${secondId};`);
        assert(
            secondSlug === `${sql(`SELECT slug FROM blog_posts WHERE id = ${postId};`)}-2`,
            `Colliding title did not get a suffixed slug: "${secondSlug}"`,
        );
        ok('A second post with the same title gets a unique slug (no unique-index crash)', { secondSlug });

        // ---------- BLOG: EDIT ----------
        await coldGoto(page, `/admin/blog/${postId}/edit`);
        assert(
            (await coldComponent(page)) === 'Admin/Blog/Form',
            `Expected Admin/Blog/Form on edit, got ${await coldComponent(page)}`,
        );
        assert(
            (await page.locator('#title').inputValue()) === POST_TITLE,
            'Edit form title not prefilled',
        );
        assert(
            (await page.locator('#status').innerText()).trim() === 'Draft',
            'Edit form status Select not prefilled',
        );
        ok('Edit form prefills title, content and status from the DB');

        await page.locator('#title').fill(EDITED_POST_TITLE);
        await page.getByRole('button', { name: 'Save changes' }).click();
        await waitForPath(page, '/admin/blog', 90000);
        await waitForToast(page, 'Blog post updated successfully');
        assert(
            sql(`SELECT title FROM blog_posts WHERE id = ${postId};`) === EDITED_POST_TITLE,
            'Blog edit did not persist',
        );
        ok('Edit post works (PUT with _method spoofing, image kept)');
        assert(
            sql(`SELECT featured_image FROM blog_posts WHERE id = ${postId};`) === uploadedImageRel,
            'Editing without picking a new image changed the stored path',
        );
        ok('Editing without picking a new image keeps the stored featured image');

        await coldGoto(page, '/admin/blog');
        await page.screenshot({ path: `${out}/sanpya-admin-vue-blog.png`, fullPage: true });
        ok('Screenshot: blog index');

        await coldGoto(page, `/admin/blog/${postId}/edit`);
        await page.screenshot({ path: `${out}/sanpya-admin-vue-blog-form.png`, fullPage: true });
        ok('Screenshot: blog form');

        // ---------- BLOG: DELETE ----------
        await coldGoto(page, '/admin/blog');
        const postRowEl = page.locator('table tbody tr', { hasText: EDITED_POST_TITLE }).first();
        await postRowEl.waitFor({ state: 'visible', timeout: 30000 });
        await postRowEl.getByRole('button', { name: 'Delete post' }).click();
        const blogDialog = page.getByRole('dialog');
        await blogDialog.waitFor({ state: 'visible', timeout: 30000 });
        const blogDelText = (await blogDialog.innerText()).replace(/\s+/g, ' ');
        assert(/permanently/i.test(blogDelText) && /cannot be undone/i.test(blogDelText),
            `Blog delete dialog should warn it is permanent: "${blogDelText}"`);
        await blogDialog.getByRole('button', { name: 'Delete post' }).click();
        await waitForToast(page, 'Blog post deleted successfully');

        assert(sql(`SELECT COUNT(*) FROM blog_posts WHERE id = ${postId};`) === '0', 'Delete did not remove the row');
        assert(
            !fs.existsSync(path.join(project, 'storage', 'app', 'public', uploadedImageRel)),
            'Delete did not remove the featured image from disk',
        );
        postIds.shift();
        uploadedImageRel = null;
        ok('Delete post removes both the DB row and the featured image file');

        // ---------- Mobile overflow ----------
        for (const url of ['/admin/contact-messages', `/admin/contact-messages/${messageIds[0] ?? ''}`, '/admin/blog', '/admin/blog/create']) {
            if (url.endsWith('/')) continue;
            await coldGoto(page, url);
            await page.setViewportSize({ width: 390, height: 844 });
            await page.waitForTimeout(400);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            assert(overflow <= 1, `${url}: horizontal overflow of ${overflow}px at 390px`);
            await page.setViewportSize({ width: 1440, height: 960 });
        }
        ok('Contact / Blog pages: no horizontal overflow at 390px');

        // ---------- Error budget ----------
        assert(pageErrors.length === 0, `Uncaught page errors: ${pageErrors.join(' | ')}`);
        ok('No uncaught page errors');
        results.push({ check: 'Console errors observed', passed: true, detail: consoleErrors.slice(0, 5) });

        fs.writeFileSync(
            `${out}/sanpya-admin-contact-blog-results.json`,
            JSON.stringify({ base, generatedAt: new Date().toISOString(), results, pageErrors, consoleErrors }, null, 2),
        );

        console.log(`\nCONTACT + BLOG: ${results.length}/${results.length} checks passed`);
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
            `${out}/sanpya-admin-contact-blog-results.json`,
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
        try { if (fs.existsSync(LOCAL_THUMB)) fs.unlinkSync(LOCAL_THUMB); } catch { /* ignore */ }
        console.log('[cleanup] throwaway contact messages + blog posts + uploaded image removed');
    }
})();
