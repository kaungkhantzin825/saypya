// Locate the element(s) causing horizontal overflow at 390px.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);

    await page.goto('http://127.0.0.1:8899/login', { waitUntil: 'domcontentloaded' });
    await page.locator('#email').fill('admin@learnhub.com');
    await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.waitForFunction(() => window.location.pathname === '/admin/dashboard', undefined, { timeout: 60000 });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(600);

    const report = await page.evaluate(() => {
        const vw = window.innerWidth;
        const out = { viewport: vw, scrollWidth: document.documentElement.scrollWidth, offenders: [] };
        for (const el of document.querySelectorAll('body *')) {
            const rect = el.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) continue;
            const overflowRight = Math.round(rect.right - vw);
            if (overflowRight > 1) {
                const style = getComputedStyle(el);
                out.offenders.push({
                    tag: el.tagName.toLowerCase(),
                    cls: (el.getAttribute('class') || '').slice(0, 130),
                    right: Math.round(rect.right),
                    width: Math.round(rect.width),
                    overflowRight,
                    position: style.position,
                    overflowX: style.overflowX,
                    minWidth: style.minWidth,
                    parentCls: (el.parentElement?.getAttribute('class') || '').slice(0, 110),
                });
            }
        }
        out.offenders.sort((a, b) => b.overflowRight - a.overflowRight);
        out.offenders = out.offenders.slice(0, 12);
        return out;
    });

    console.log(JSON.stringify(report, null, 2));
    await browser.close();
})();
