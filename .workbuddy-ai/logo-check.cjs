const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';
const results = [];
const assert = (value, message) => { if (!value) throw new Error(message); };

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const pageErrors = [];
    const consoleErrors = [];
    try {
        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        page.setDefaultTimeout(60000);
        page.setDefaultNavigationTimeout(90000);
        page.on('pageerror', error => pageErrors.push(error.message));
        page.on('console', message => { if (message.type() === 'error') consoleErrors.push(message.text()); });

        async function checkLogo(label) {
            const logo = page.locator('img[src="/images/SanPya-Logo.png"]:visible').first();
            await logo.waitFor({ state: 'visible', timeout: 60000 });
            await page.waitForFunction(() => [...document.images].filter(img => img.getAttribute('src') === '/images/SanPya-Logo.png').every(img => img.complete && img.naturalWidth > 0));
            const size = await logo.evaluate(img => ({ naturalWidth: img.naturalWidth, naturalHeight: img.naturalHeight, width: Math.round(img.getBoundingClientRect().width), height: Math.round(img.getBoundingClientRect().height) }));
            assert(size.naturalWidth > 0, `${label}: logo failed to load`);
            assert(Math.abs(size.width / size.height - size.naturalWidth / size.naturalHeight) < 0.03, `${label}: logo aspect ratio distorted`);
            assert(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1), `${label}: horizontal overflow`);
            results.push({ check: label, passed: true, rendered: size });
        }

        // ---- Guest pages: logo + responsive ----
        await page.goto(base, { waitUntil: 'domcontentloaded' });
        await checkLogo('Desktop homepage header');
        await page.screenshot({ path: `${out}/sanpya-home-desktop.png` });

        await page.setViewportSize({ width: 390, height: 844 });
        await checkLogo('Mobile homepage header');
        await page.screenshot({ path: `${out}/sanpya-home-mobile.png` });

        await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
        await checkLogo('Mobile login page');
        await page.setViewportSize({ width: 1440, height: 960 });
        await checkLogo('Desktop login page');
        await page.screenshot({ path: `${out}/sanpya-login.png` });

        await page.goto(`${base}/contact`, { waitUntil: 'domcontentloaded' });
        const captchaQuestion = await page.locator('label[for="captcha_answer"]').innerText();
        assert(/what is \d+ \+ \d+/i.test(captchaQuestion), `Contact captcha question missing, got: ${captchaQuestion}`);
        results.push({ check: 'Contact page captcha renders', passed: true, question: captchaQuestion.trim() });

        // ---- Role logins ----
        const accounts = [
            { role: 'student', email: 'john.doe@example.com', expect: '/dashboard' },
            { role: 'lecturer', email: 'sarah@learnhub.com', expect: '/instructor/dashboard' },
            { role: 'admin', email: 'admin@learnhub.com', expect: '/admin/dashboard' },
        ];
        for (const account of accounts) {
            await context.clearCookies();
            await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
            await page.locator('#email').fill(account.email);
            await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
            await page.getByRole('button', { name: 'Sign in', exact: true }).click();

            try {
                await page.waitForFunction(
                    expected => window.location.pathname === expected,
                    account.expect,
                    { timeout: 60000 },
                );
            } catch {
                const alert = await page.locator('[role="alert"], .text-destructive').first().innerText().catch(() => '');
                const path = new URL(page.url()).pathname;
                throw new Error(`${account.role}: login did not reach ${account.expect} (at ${path}) ${alert ? '| ' + alert.trim() : ''}`);
            }
            await page.waitForLoadState('networkidle').catch(() => {});

            if (account.role === 'student') {
                await checkLogo('Student dashboard sidebar');
                await page.screenshot({ path: `${out}/sanpya-student-dashboard.png` });
                await page.setViewportSize({ width: 390, height: 844 });
                await checkLogo('Mobile student dashboard header');
                await page.setViewportSize({ width: 1440, height: 960 });
                for (const path of ['/my/courses', '/my/wishlist', '/my/exams', '/profile']) {
                    const response = await page.goto(`${base}${path}`, { waitUntil: 'domcontentloaded' });
                    assert(response.status() === 200, `${path}: expected HTTP 200, got ${response.status()}`);
                    await page.locator('#app').waitFor();
                    results.push({ check: `Student ${path}`, passed: true });
                }
                const forbidden = await page.goto(`${base}/admin/dashboard`, { waitUntil: 'domcontentloaded' });
                assert(forbidden.status() === 403, `Student must be denied /admin/dashboard, got ${forbidden.status()}`);
                results.push({ check: 'Student denied access to admin dashboard', passed: true });
            } else {
                await page.screenshot({ path: `${out}/sanpya-${account.role}-dashboard.png` });
            }
            results.push({ check: `${account.role} logged in via form -> ${account.expect}`, passed: true });
        }

        assert(pageErrors.length === 0, 'Uncaught browser errors: ' + pageErrors.join('; '));
        const report = { base, results, pageErrors, consoleErrors };
        fs.writeFileSync(`${out}/sanpya-logo-test-results.json`, JSON.stringify(report, null, 2));
        console.log(JSON.stringify(report, null, 2));
        await context.close();
    } finally {
        await browser.close();
    }
})().catch(error => { console.error('FAILED: ' + error.message); process.exitCode = 1; });
