// Debug: how does the Courses create form behave under automation?
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
    const page = await context.newPage();
    page.setDefaultTimeout(20000);

    await page.goto('http://127.0.0.1:8899/login', { waitUntil: 'domcontentloaded' });
    await page.locator('#email').fill('admin@learnhub.com');
    await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.waitForFunction(() => window.location.pathname === '/admin/dashboard', undefined, { timeout: 60000 });

    await page.goto('http://127.0.0.1:8899/admin/courses/create', { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(500);

    console.log('--- element presence ---');
    console.log('  #description      :', await page.locator('#description').count());
    console.log('  #title            :', await page.locator('#title').count());
    console.log('  #category_id      :', await page.locator('#category_id').count());
    console.log('  textarea count    :', await page.locator('textarea').count());
    console.log('  #app textarea ids :', await page.evaluate(() =>
        [...document.querySelectorAll('textarea')].map((t) => t.id || '(no id)').join(', ')));

    // Fill description and confirm the value sticks.
    await page.locator('#description').fill('HELLO-DESCRIPTION');
    console.log('  after fill, value :', JSON.stringify(await page.locator('#description').inputValue()));
    await page.locator('#short_description').fill('HELLO-SHORT');
    console.log('  short after fill  :', JSON.stringify(await page.locator('#short_description').inputValue()));

    console.log('\n--- options in the DOM (before opening anything) ---');
    const before = await page.evaluate(() =>
        [...document.querySelectorAll('[role="option"], option')].map((o) => ({
            tag: o.tagName.toLowerCase(),
            role: o.getAttribute('role'),
            text: (o.textContent || '').trim().slice(0, 30),
            hidden: o.closest('[aria-hidden="true"]') !== null,
        })));
    console.log('  count:', before.length);
    console.log('  ', JSON.stringify(before.slice(0, 8)));

    console.log('\n--- open #category_id ---');
    await page.locator('#category_id').click();
    await page.waitForTimeout(400);
    const after = await page.evaluate(() =>
        [...document.querySelectorAll('[role="option"], option')].map((o) => ({
            tag: o.tagName.toLowerCase(),
            role: o.getAttribute('role'),
            text: (o.textContent || '').trim().slice(0, 30),
            inListbox: o.closest('[role="listbox"]') !== null,
            ariaHidden: o.closest('[aria-hidden="true"]') !== null,
            disabled: o.getAttribute('aria-disabled'),
        })));
    console.log('  count:', after.length);
    console.log('  ', JSON.stringify(after, null, 1));

    // Click the FIRST option the old way, then read the trigger text.
    await page.getByRole('option').first().click();
    await page.waitForTimeout(300);
    console.log('  trigger text after .first() click:', JSON.stringify(await page.locator('#category_id').innerText()));

    // Now do it properly: scope to the visible listbox.
    await page.locator('#category_id').click();
    await page.waitForTimeout(300);
    const visible = page.locator('[role="listbox"] [role="option"]');
    console.log('  visible listbox options:', await visible.count());
    if (await visible.count()) {
        console.log('  first visible text:', JSON.stringify((await visible.first().innerText()).trim()));
        await visible.first().click();
        await page.waitForTimeout(300);
        console.log('  trigger text after scoped click:', JSON.stringify(await page.locator('#category_id').innerText()));
    }

    await browser.close();
})();
