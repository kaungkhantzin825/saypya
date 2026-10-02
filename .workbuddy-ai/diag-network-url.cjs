// Report any non-2xx/3xx responses for the given admin URLs.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const urls = process.argv.slice(2);

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

    for (const url of urls) {
        const bad = [];
        const onResponse = (res) => {
            if (res.status() >= 400 && res.status() !== 409) bad.push(`${res.status()} ${res.url()}`);
        };
        page.on('response', onResponse);
        await page.goto(`http://127.0.0.1:8899${url}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(800);
        page.off('response', onResponse);

        // Also list images that failed to load in the DOM.
        const brokenImages = await page.evaluate(() =>
            [...document.querySelectorAll('img')]
                .filter((img) => img.complete && img.naturalWidth === 0)
                .map((img) => img.getAttribute('src')));

        console.log(`\n${url}`);
        if (!bad.length && !brokenImages.length) console.log('  (no failures)');
        [...new Set(bad)].forEach((b) => console.log('  HTTP', b));
        brokenImages.forEach((b) => console.log('  BROKEN IMG', b));
    }

    await browser.close();
})();
