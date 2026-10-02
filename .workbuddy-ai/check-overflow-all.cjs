// Check every admin URL for horizontal overflow at 390px, in one session.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const urls = process.argv.slice(2);

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

    let failures = 0;
    for (const url of urls) {
        await page.goto(`http://127.0.0.1:8899${url}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(500);

        const info = await page.evaluate(() => {
            const vw = window.innerWidth;
            const sw = document.documentElement.scrollWidth;
            let worst = null, worstRight = -Infinity;
            for (const el of document.querySelectorAll('body *')) {
                const r = el.getBoundingClientRect();
                if (r.width === 0 || r.height === 0) continue;
                if (r.right > worstRight) { worstRight = r.right; worst = el; }
            }
            return {
                vw, sw,
                worstCls: worst ? (worst.getAttribute('class') || worst.tagName).slice(0, 80) : '',
                worstText: worst ? (worst.innerText || '').slice(0, 30).replace(/\s+/g, ' ') : '',
            };
        });

        const okFlag = info.sw <= info.vw + 1;
        if (!okFlag) failures++;
        console.log(
            `${okFlag ? 'OK  ' : 'FAIL'} ${url.padEnd(34)} scrollWidth=${info.sw}` +
            (okFlag ? '' : `  <- "${info.worstText}" .${info.worstCls}`),
        );
    }

    await browser.close();
    console.log(failures ? `\n${failures} page(s) overflow` : '\nAll pages fit 390px');
    process.exitCode = failures ? 1 : 0;
})();
