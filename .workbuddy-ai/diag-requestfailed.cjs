// Log every failed request (network-level) while visiting the given URLs.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const urls = process.argv.slice(2);

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);

    const failures = [];
    page.on('requestfailed', (req) => {
        failures.push(`${req.failure()?.errorText} ${req.method()} ${req.url()}`);
    });

    await page.goto('http://127.0.0.1:8899/login', { waitUntil: 'domcontentloaded' });
    await page.locator('#email').fill('admin@learnhub.com');
    await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.waitForFunction(() => window.location.pathname === '/admin/dashboard', undefined, { timeout: 60000 });

    failures.length = 0; // login noise (the 409) is expected

    for (const url of urls) {
        await page.goto(`http://127.0.0.1:8899${url}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(1000);
    }

    console.log(`\nfailed requests: ${failures.length}`);
    [...new Set(failures)].forEach((f) => console.log('  ', f));
    await browser.close();
})();
