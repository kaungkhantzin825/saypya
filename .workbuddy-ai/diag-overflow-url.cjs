// One-off: locate the element(s) causing horizontal overflow at 390px on a
// specific admin URL. Pass the path as argv[2], e.g. /admin/courses/1/content
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const target = process.argv[2] || '/admin/dashboard';

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

    await page.goto(`http://127.0.0.1:8899${target}`, { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.waitForTimeout(800);

    const report = await page.evaluate(() => {
        const vw = window.innerWidth;
        const out = { url: window.location.pathname, viewport: vw, scrollWidth: document.documentElement.scrollWidth, offenders: [] };
        for (const el of document.querySelectorAll('body *')) {
            const rect = el.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) continue;
            const overflowRight = Math.round(rect.right - vw);
            if (overflowRight > 0) {
                const style = getComputedStyle(el);
                out.offenders.push({
                    tag: el.tagName.toLowerCase(),
                    cls: (el.getAttribute('class') || '').slice(0, 150),
                    text: (el.innerText || '').slice(0, 40).replace(/\s+/g, ' '),
                    right: Math.round(rect.right),
                    width: Math.round(rect.width),
                    overflowRight,
                    position: style.position,
                    minWidth: style.minWidth,
                    parentTag: el.parentElement?.tagName.toLowerCase(),
                    parentCls: (el.parentElement?.getAttribute('class') || '').slice(0, 120),
                });
            }
        }
        out.offenders.sort((a, b) => b.overflowRight - a.overflowRight);
        out.offenders = out.offenders.slice(0, 14);
        return out;
    });

    console.log(JSON.stringify(report, null, 2));
    await browser.close();
})();
