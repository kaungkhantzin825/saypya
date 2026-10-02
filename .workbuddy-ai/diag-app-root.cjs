// Why is #app's data-page stale after an Inertia SPA visit?
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);

    await page.goto('http://127.0.0.1:8899/login', { waitUntil: 'domcontentloaded' });
    await page.locator('#email').fill('admin@learnhub.com');
    await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.waitForFunction(() => window.location.pathname === '/admin/dashboard', undefined, { timeout: 60000 });
    await page.waitForLoadState('networkidle').catch(() => {});

    const before = await page.evaluate(() => ({
        idAppCount: document.querySelectorAll('#app').length,
        dataPageCount: document.querySelectorAll('[data-page]').length,
        appComponent: (() => { try { return JSON.parse(document.getElementById('app').dataset.page).component; } catch { return 'ERR'; } })(),
        inertiakeys: Object.keys(window).filter((k) => /inertia/i.test(k)),
    }));
    console.log('BEFORE click:', JSON.stringify(before, null, 2));

    await page.getByRole('link', { name: 'Users', exact: true }).first().click();
    await page.waitForFunction(() => window.location.pathname === '/admin/users', undefined, { timeout: 60000 });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(1500);

    const after = await page.evaluate(() => {
        const els = [...document.querySelectorAll('#app')];
        return {
            idAppCount: els.length,
            dataPageCount: document.querySelectorAll('[data-page]').length,
            perEl: els.map((el) => {
                try { return JSON.parse(el.dataset.page).component; } catch { return 'ERR'; }
            }),
            attrLen: els.map((el) => (el.getAttribute('data-page') || '').length),
            bodyHasUsersPage: /All users/.test(document.body.innerText),
            h1: document.querySelector('h1')?.innerText ?? null,
        };
    });
    console.log('AFTER click:', JSON.stringify(after, null, 2));

    await browser.close();
})();
