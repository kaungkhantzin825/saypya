const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const fs = require('node:fs');
const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';
const results = [];
const assert = (value, message) => { if (!value) throw new Error(message); };

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    try {
        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        const pageErrors = [];
        const consoleErrors = [];
        page.on('pageerror', error => pageErrors.push(error.message));
        page.on('console', message => { if (message.type() === 'error') consoleErrors.push(message.text()); });
        async function checkLogo(label) {
            const logo = page.locator('img[src="/images/SanPya-Logo.png"]:visible').first();
            await logo.waitFor({ state: 'visible', timeout: 30000 });
            await page.waitForFunction(() => [...document.images].filter(img => img.getAttribute('src') === '/images/SanPya-Logo.png').every(img => img.complete && img.naturalWidth > 0));
            const size = await logo.evaluate(img => ({ naturalWidth: img.naturalWidth, naturalHeight: img.naturalHeight, width: img.getBoundingClientRect().width, height: img.getBoundingClientRect().height }));
            assert(Math.abs(size.width / size.height - size.naturalWidth / size.naturalHeight) < 0.03, `${label}: logo is distorted`);
            assert(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1), `${label}: horizontal overflow`);
            results.push({ check: label, passed: true, logo: size });
        }
        await page.goto(base, { waitUntil: 'networkidle' });
        await checkLogo('Desktop homepage');
        await page.screenshot({ path: `${out}/sanpya-home-desktop.png`, fullPage: false });
        await page.setViewportSize({ width: 390, height: 844 });
        await checkLogo('Mobile homepage');
        await page.screenshot({ path: `${out}/sanpya-home-mobile.png`, fullPage: false });
        await page.goto(`${base}/login`, { waitUntil: 'networkidle' });
        await checkLogo('Mobile login');
        await page.setViewportSize({ width: 1440, height: 960 });
        await checkLogo('Desktop login');
        await page.screenshot({ path: `${out}/sanpya-login.png`, fullPage: false });

        const accounts = [
            { role: 'student', email: 'john.doe@example.com', path: '/dashboard' },
            { role: 'lecturer', email: 'sarah@learnhub.com', path: '/instructor/dashboard' },
            { role: 'admin', email: 'admin@learnhub.com', path: '/admin/dashboard' },
        ];
        for (const account of accounts) {
            await context.clearCookies();
            await page.goto(`${base}/login`, { waitUntil: 'networkidle' });
            await page.locator('#email').fill(account.email);
            await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
            await page.getByRole('button', { name: 'Sign in', exact: true }).click();
            await page.waitForURL(url => url.pathname === account.path, { timeout: 30000 });
            await page.waitForLoadState('networkidle');
            const response = await page.goto(`${base}${account.path}`, { waitUntil: 'networkidle' });
            assert(response.status() === 200, `${account.role}: dashboard did not return 200`);
            if (account.role === 'student') {
                await checkLogo('Student dashboard');
                await page.screenshot({ path: `${out}/sanpya-student-dashboard.png`, fullPage: false });
                await page.setViewportSize({ width: 390, height: 844 });
                await checkLogo('Mobile student dashboard');
                await page.setViewportSize({ width: 1440, height: 960 });
                for (const path of ['/my/courses', '/my/wishlist', '/my/exams', '/profile']) {
                    const response = await page.goto(`${base}${path}`, { waitUntil: 'networkidle' });
                    assert(response.status() === 200, `${path}: expected HTTP 200`);
                    await page.locator('#app main').waitFor();
                    results.push({ check: `Student ${path}`, passed: true });
                }
                const forbidden = await page.goto(`${base}/admin/dashboard`, { waitUntil: 'domcontentloaded' });
                assert(forbidden.status() === 403, 'Student must not access admin dashboard');
                results.push({ check: 'Student denied access to admin dashboard', passed: true });
            }
            results.push({ check: `${account.role} form login and dashboard`, passed: true });
            await page.evaluate(async () => {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'text/html' }, credentials: 'same-origin' });
                if (!response.ok) throw new Error('Logout failed: ' + response.status);
            });
        }
        assert(pageErrors.length === 0, 'Uncaught browser errors: ' + pageErrors.join('; '));
        const report = { results, pageErrors, consoleErrors };
        fs.writeFileSync(`${out}/sanpya-logo-test-results.json`, JSON.stringify(report, null, 2));
        console.log(JSON.stringify(report, null, 2));
        await context.close();
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
