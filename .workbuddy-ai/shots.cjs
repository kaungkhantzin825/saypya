// Capture clean screenshots of the migrated admin pages.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);

    await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
    await page.locator('#email').fill('admin@learnhub.com');
    await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.waitForFunction(() => window.location.pathname === '/admin/dashboard', undefined, { timeout: 60000 });
    await page.waitForLoadState('networkidle').catch(() => {});

    const shots = [
        ['/admin/courses', 'sanpya-admin-vue-courses.png'],
        ['/admin/courses?status=published&featured=1&sort=price&direction=desc', 'sanpya-admin-vue-courses-filtered.png'],
    ];

    for (const [url, file] of shots) {
        await page.goto(`${base}${url}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForFunction(
            () => !/No courses found/i.test(document.querySelector('table tbody')?.innerText || 'No courses found'),
            undefined,
            { timeout: 60000 },
        ).catch(() => {});
        await page.waitForTimeout(600);
        await page.screenshot({ path: `${out}/${file}`, fullPage: true });
        console.log('captured', file);
    }

    await browser.close();
})();
