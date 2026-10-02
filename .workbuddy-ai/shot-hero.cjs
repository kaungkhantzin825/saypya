// Quick hero screenshots for visual iteration: desktop, mobile and dark mode.
// Run: node .workbuddy-ai/shot-hero.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });

    try {
        const shoot = async (name, viewport, { dark = false, height = 620 } = {}) => {
            const page = await browser.newPage({ viewport });
            await page.goto('http://127.0.0.1:8899/', { waitUntil: 'domcontentloaded' });
            await page.waitForFunction(() => /Featured/i.test(document.body.innerText), undefined, { timeout: 60000 });
            // `darkMode: ['class']` — set it before measuring so tokens resolve.
            if (dark) await page.evaluate(() => document.documentElement.classList.add('dark'));
            await page.waitForTimeout(700);
            await page.screenshot({ path: `${out}/${name}.png`, clip: { x: 0, y: 0, width: viewport.width, height } });
            console.log(`saved ${name}.png`);
            await page.close();
        };

        await shoot('hero-preview', { width: 1440, height: 900 });
        await shoot('hero-preview-mobile', { width: 390, height: 844 }, { height: 560 });
        await shoot('hero-preview-dark', { width: 1440, height: 900 }, { dark: true });
    } finally {
        await browser.close();
    }
})();
