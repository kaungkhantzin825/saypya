// Post-deletion smoke check: /admin/reports and /admin/settings must still serve
// after their Blade views were removed from resources/views/admin/.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/smoke-reports-settings.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const base = 'http://127.0.0.1:8899';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    let failed = false;

    try {
        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        page.setDefaultNavigationTimeout(90000);

        const errors = [];
        page.on('pageerror', (e) => errors.push(e.message));

        await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('#email').fill('admin@learnhub.com');
        await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
        await page.getByRole('button', { name: 'Sign in', exact: true }).click();
        await page.waitForFunction(
            () => window.location.pathname === '/admin/dashboard',
            undefined,
            { timeout: 90000 },
        );

        const targets = [
            ['/admin/reports', 'Admin/Reports', /Total users/i],
            ['/admin/settings', 'Admin/Settings', /Save settings/i],
        ];

        for (const [url, expected, marker] of targets) {
            // Full cold load so #app's data-page reflects this response.
            const response = await page.goto(`${base}${url}`, { waitUntil: 'domcontentloaded' });
            const status = response.status();

            const html = await page.content();
            const match = html.match(/&quot;component&quot;:&quot;([^&]*)&quot;/);
            // Inertia's JSON escapes the path separator, so the raw capture is
            // `Admin\/Reports` — unescape before comparing.
            const component = match ? match[1].replace(/\\\//g, '/') : '(none)';

            let rendered = true;
            try {
                await page.waitForFunction(
                    (source) => new RegExp(source).test(document.body.innerText),
                    marker.source,
                    { timeout: 60000 },
                );
            } catch {
                rendered = false;
            }

            const pass = status === 200 && component === expected && rendered;
            if (!pass) failed = true;
            console.log(
                `${pass ? 'PASS' : 'FAIL'}  ${url}  HTTP ${status}  component=${component}  rendered=${rendered}`,
            );
        }

        // No table may be clipped at desktop width. DataTable's table carries
        // `min-w-[42rem]` (672px); if its card is narrower, trailing columns hide
        // behind a scrollbar with no visible affordance.
        await page.setViewportSize({ width: 1440, height: 960 });
        await page.goto(`${base}/admin/reports`, { waitUntil: 'domcontentloaded' });
        await page.waitForFunction(
            () => /Total users/i.test(document.body.innerText),
            undefined,
            { timeout: 60000 },
        );
        const clipped = await page.evaluate(() =>
            [...document.querySelectorAll('.overflow-x-auto')]
                .map((el) => ({
                    label: el.querySelector('th')?.textContent?.trim() ?? '(table)',
                    scrollWidth: el.scrollWidth,
                    clientWidth: el.clientWidth,
                }))
                .filter((t) => t.scrollWidth > t.clientWidth + 1),
        );
        if (clipped.length) failed = true;
        console.log(
            `${clipped.length ? 'FAIL' : 'PASS'}  no clipped tables at 1440px` +
                (clipped.length ? ` -> ${JSON.stringify(clipped)}` : ''),
        );

        // Doughnut legend labels must be fully readable, not ellipsised.
        const legend = await page.evaluate(() => {
            const chart = document.querySelector('[data-chart="doughnut"]');
            const card = chart?.closest('div')?.parentElement;
            return [...(card?.querySelectorAll('li span.truncate') ?? [])].map((el) => ({
                text: el.textContent.trim(),
                available: el.clientWidth,
                needed: el.scrollWidth,
                clipped: el.scrollWidth > el.clientWidth + 1,
            }));
        });
        const legendClipped = legend.filter((l) => l.clipped);
        if (legendClipped.length) failed = true;
        console.log(
            `${legendClipped.length ? 'FAIL' : 'PASS'}  doughnut legend labels not truncated` +
                ` -> ${JSON.stringify(legend)}`,
        );

        console.log(`pageErrors: ${errors.length ? errors.join(' | ') : 'none'}`);
    } finally {
        await browser.close();
    }

    process.exitCode = failed ? 1 : 0;
})();
