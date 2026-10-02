const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await (await browser.newContext()).newPage();
    page.setDefaultTimeout(45000);
    const failed = [];
    page.on('requestfailed', r => failed.push(`${r.url()} -> ${r.failure()?.errorText}`));
    page.on('response', r => { if (r.status() >= 400) failed.push(`HTTP ${r.status()} ${r.url()}`); });

    for (const path of ['/', '/courses', '/login', '/contact']) {
        failed.length = 0;
        await page.goto('http://127.0.0.1:8899' + path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(6000);
        const imgs = await page.evaluate(() => {
            const list = [...document.images].map(i => ({ src: i.currentSrc || i.src, ok: i.complete && i.naturalWidth > 0 }));
            return { total: list.length, broken: list.filter(i => !i.ok).map(i => i.src) };
        });
        const hostIssues = failed.filter(f => f.includes('localhost:8000') || f.includes('127.0.0.1:8000'));
        console.log(`--- ${path} ---`);
        console.log(`  images: ${imgs.total - imgs.broken.length}/${imgs.total} loaded`);
        if (imgs.broken.length) console.log('  broken: ' + imgs.broken.slice(0, 4).join('\n           '));
        console.log('  wrong-port refs: ' + (hostIssues.length ? hostIssues.join('; ') : 'none'));
        const other = failed.filter(f => !hostIssues.includes(f));
        console.log('  other failures: ' + (other.length ? other.slice(0, 4).join('; ') : 'none'));
    }
    await browser.close();
})().catch(e => { console.error(e); process.exitCode = 1; });
