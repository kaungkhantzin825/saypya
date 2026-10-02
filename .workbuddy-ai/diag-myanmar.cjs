// Diagnose the Myanmar blog-post rendering on the live site.
//
// Reports, for the article body: computed font-size / line-height / font-family,
// the line box heights actually produced, and which of the candidate fonts the
// browser really used. Also screenshots the content area.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const fs = require('node:fs');

const url = process.argv[2] || 'https://sanpyalearning.com/blog/it-paing-laikkhyainta-baalokyamlete';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';

(async () => {
    fs.mkdirSync(out, { recursive: true });
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });

    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(2500);

        const report = await page.evaluate(async () => {
            // The article body is the v-html container: the deepest div with lots of text.
            const candidates = [...document.querySelectorAll('div')].filter(
                (d) => d.children.length < 40 && (d.innerText || '').length > 500,
            );
            const body = candidates.sort((a, b) => (b.innerText || '').length - (a.innerText || '').length)[0];
            if (!body) return { error: 'article body not found' };

            const cs = getComputedStyle(body);

            // Line boxes actually produced by the first paragraph-ish block.
            const block = [...body.children].find((c) => (c.innerText || '').length > 40) || body;
            const range = document.createRange();
            range.selectNodeContents(block);
            const rects = [...range.getClientRects()].map((r) => Math.round(r.height));
            const lineHeights = [...new Set(rects)].sort((a, b) => b - a);

            // Which font really renders Myanmar? Measure a probe string in each candidate.
            const probe = 'မြန်မာစာ စမ်းသပ်စာသား';
            const measurer = document.createElement('span');
            measurer.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;font-size:40px';
            document.body.appendChild(measurer);
            const widths = {};
            for (const f of ['Inter', 'Pyidaungsu', 'Noto Sans Myanmar', 'Padauk', 'Myanmar Text', 'sans-serif']) {
                measurer.style.fontFamily = `'${f}'`;
                widths[f] = Math.round(measurer.getBoundingClientRect().width);
            }
            measurer.style.fontFamily = cs.fontFamily;
            const bodyStackWidth = Math.round(measurer.getBoundingClientRect().width);
            measurer.remove();

            const loaded = {};
            for (const f of ['Inter', 'Noto Sans Myanmar', 'Padauk', 'Pyidaungsu']) {
                loaded[f] = document.fonts.check(`16px '${f}'`);
            }

            // Does any element on the page carry the Myanmar class / lang?
            const myanmarClassed = document.querySelectorAll('.myanmar-text').length;
            const htmlLang = document.documentElement.getAttribute('lang');

            return {
                textLength: (body.innerText || '').length,
                fontFamily: cs.fontFamily,
                fontSize: cs.fontSize,
                lineHeight: cs.lineHeight,
                fontWeight: cs.fontWeight,
                letterSpacing: cs.letterSpacing,
                textRendering: cs.textRendering,
                fontFeatureSettings: cs.fontFeatureSettings,
                lineHeights,
                widths,
                bodyStackWidth,
                loaded,
                myanmarClassed,
                htmlLang,
                fontsAvailable: [...document.fonts].map((f) => `${f.family}:${f.status}`).slice(0, 30),
            };
        });

        console.log(JSON.stringify(report, null, 2));

        const body = page.locator('div').filter({ hasText: /IT/ }).first();
        await page.screenshot({ path: `${out}/myanmar-post-live.png`, fullPage: false });
        console.log(`\nscreenshot -> ${out}/myanmar-post-live.png`);
    } catch (e) {
        console.error('FAILED:', e.message);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
