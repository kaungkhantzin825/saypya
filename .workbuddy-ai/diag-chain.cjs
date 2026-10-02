// Dump the ancestor chain (tag/class/width) of the widest element at 390px.
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
        // Widest element on the page.
        let worst = null;
        let worstRight = -Infinity;
        for (const el of document.querySelectorAll('body *')) {
            const r = el.getBoundingClientRect();
            if (r.width === 0 || r.height === 0) continue;
            if (r.right > worstRight) { worstRight = r.right; worst = el; }
        }
        const chain = [];
        let node = worst;
        while (node && node !== document.documentElement) {
            const r = node.getBoundingClientRect();
            const cs = getComputedStyle(node);
            chain.push({
                tag: node.tagName.toLowerCase(),
                cls: (node.getAttribute('class') || '').slice(0, 120),
                width: Math.round(r.width),
                left: Math.round(r.left),
                right: Math.round(r.right),
                display: cs.display,
                minWidth: cs.minWidth,
                flex: cs.flex,
                gridTemplateColumns: cs.gridTemplateColumns,
                overflowX: cs.overflowX,
                text: (node.innerText || '').slice(0, 30).replace(/\s+/g, ' '),
            });
            node = node.parentElement;
        }
        return { viewport: vw, scrollWidth: document.documentElement.scrollWidth, chain: chain.reverse() };
    });

    console.log(JSON.stringify(report, null, 2));
    await browser.close();
})();
