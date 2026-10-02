// Trace what the Inertia SPA visit to /admin/users actually returns.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);

    page.on('response', async (res) => {
        const url = res.url();
        if (!url.includes('127.0.0.1:8899')) return;
        if (/\.(js|css|png|jpg|svg|woff2?|ico)(\?|$)/.test(url)) return;
        const headers = res.headers();
        console.log(
            `[resp] ${res.status()} ${res.request().method()} ${url.replace('http://127.0.0.1:8899', '')}` +
                (headers['x-inertia'] ? ' (inertia)' : '') +
                (headers['x-inertia-location'] ? ` -> ${headers['x-inertia-location']}` : ''),
        );
    });
    page.on('pageerror', (e) => console.log('[pageerror]', e.message));
    page.on('console', (m) => { if (m.type() === 'error') console.log('[console]', m.text().slice(0, 200)); });

    await page.goto('http://127.0.0.1:8899/login', { waitUntil: 'domcontentloaded' });
    await page.locator('#email').fill('admin@learnhub.com');
    await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
    console.log('--- submitting login ---');
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.waitForFunction(() => window.location.pathname === '/admin/dashboard', undefined, { timeout: 60000 });
    await page.waitForLoadState('networkidle').catch(() => {});
    console.log('--- on dashboard, now clicking Users ---');
    console.log('url:', page.url());

    await page.getByRole('link', { name: 'Users', exact: true }).first().click();
    await page.waitForTimeout(6000);

    console.log('url after click:', page.url());
    const comp = await page.evaluate(() => {
        const el = document.getElementById('app');
        try { return JSON.parse(el.dataset.page).component; } catch { return 'PARSE_FAIL'; }
    });
    console.log('component:', comp);

    const bodyHead = await page.evaluate(() => document.body.innerText.slice(0, 400));
    console.log('--- body text ---');
    console.log(bodyHead);

    await browser.close();
})();
