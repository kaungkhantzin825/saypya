const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const base = 'http://127.0.0.1:8899';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
    const page = await context.newPage();
    page.setDefaultTimeout(45000);
    const log = [];
    page.on('framenavigated', f => { if (f === page.mainFrame()) log.push('NAV  -> ' + new URL(f.url()).pathname); });
    page.on('response', r => {
        if (r.request().method() === 'POST' || r.status() >= 300) {
            log.push(`HTTP ${r.status()} ${r.request().method()} ${new URL(r.url()).pathname}`);
        }
    });

    for (const [role, email] of [['lecturer', 'sarah@learnhub.com'], ['admin', 'admin@learnhub.com']]) {
        log.length = 0;
        await context.clearCookies();
        await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('#email').fill(email);
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: 'Sign in', exact: true }).click();
        await page.waitForTimeout(12000);
        const info = await page.evaluate(() => ({
            path: window.location.pathname,
            appChildren: document.getElementById('app')?.children.length ?? -1,
            h1: document.querySelector('h1')?.textContent?.trim() ?? '',
            component: (() => {
                const el = document.querySelector('#app');
                const page = el?.getAttribute('data-page');
                if (!page) return '';
                try { return JSON.parse(page).component; } catch { return 'unparsed'; }
            })(),
        }));
        console.log(`\n=== ${role} ===`);
        console.log(JSON.stringify(info));
        console.log(log.join('\n'));
    }
    await browser.close();
})().catch(e => { console.error(e); process.exitCode = 1; });
