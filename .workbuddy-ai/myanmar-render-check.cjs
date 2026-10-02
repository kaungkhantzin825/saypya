// Myanmar blog-post typography check.
//
// Guards the three things that made the Myanmar post render as an unreadable
// block of text:
//   1. no Myanmar-capable font in the resolved stack (fell back to an OS font),
//   2. line-height 1.5 — the `sm:text-base` clobber — far too tight for marks
//      that stack above *and* below the base character,
//   3. plain-text bodies piped straight into `v-html`, so every newline collapsed
//      and 65 authored lines became one paragraph-less wall.
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/myanmar-render-check.cjs [label]
// `label` only names the screenshots; assertions always run.
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const fs = require('node:fs');

const base = 'http://127.0.0.1:8899';
const slug = 'it-paing-laikkhyainta-baalokyamlete';
const label = process.argv[2] || 'after';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';

const results = [];
const assert = (v, m) => { if (!v) throw new Error(m); };
const ok = (check, detail) => {
    results.push({ check, passed: true });
    console.log(`  ok  [${results.length}] ${check}${detail ? ` ${JSON.stringify(detail)}` : ''}`);
};

(async () => {
    fs.mkdirSync(out, { recursive: true });
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 1100 } });

    try {
        await page.goto(`${base}/blog/${slug}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.waitForLoadState('networkidle').catch(() => {});

        const report = await page.evaluate(async () => {
            // Make sure any webfont the layout references is actually resolved
            // before measuring — otherwise every probe reports the fallback width.
            await document.fonts.ready;
            for (const f of ['16px "Noto Sans Myanmar"', '16px "Padauk"', '16px "Inter"']) {
                await document.fonts.load(f).catch(() => {});
            }
            await document.fonts.ready;

            const body = document.querySelector('[data-article-body]');
            if (!body) return { error: 'article body [data-article-body] not found' };

            const cs = getComputedStyle(body);
            const h1 = document.querySelector('h1');
            const h1cs = h1 ? getComputedStyle(h1) : null;

            // Real rendered line count: one rect per line box.
            const range = document.createRange();
            range.selectNodeContents(body);
            const lineCount = range.getClientRects().length;

            const check = {};
            for (const f of ['Inter', 'Noto Sans Myanmar', 'Padauk']) {
                check[f] = document.fonts.check(`16px "${f}"`);
            }

            return {
                fontFamily: cs.fontFamily,
                hasMyanmarFontInStack: /noto sans myanmar|padauk|pyidaungsu|myanmar text/i.test(cs.fontFamily),
                fontSize: cs.fontSize,
                lineHeight: cs.lineHeight,
                ratio: +(parseFloat(cs.lineHeight) / parseFloat(cs.fontSize)).toFixed(2),
                paragraphCount: body.querySelectorAll('p').length,
                brCount: body.querySelectorAll('br').length,
                bodyTextLength: (body.innerText || '').length,
                lineCount,
                fontsCheck: check,
                h1: h1cs
                    ? {
                          text: h1.textContent.trim().slice(0, 34),
                          fontSize: h1cs.fontSize,
                          lineHeight: h1cs.lineHeight,
                          ratio: +(parseFloat(h1cs.lineHeight) / parseFloat(h1cs.fontSize)).toFixed(2),
                          letterSpacing: h1cs.letterSpacing,
                      }
                    : null,
            };
        });

        if (report.error) throw new Error(report.error);
        console.log(JSON.stringify(report, null, 2) + '\n');

        // ---------------------------------------------------------- assertions
        assert(
            report.hasMyanmarFontInStack,
            `Resolved stack has no Myanmar font -> ${report.fontFamily}`,
        );
        ok('A Myanmar-capable font is present in the resolved stack');

        assert(
            report.fontsCheck['Noto Sans Myanmar'],
            'Noto Sans Myanmar did not load — the webfont is not being applied',
        );
        ok('Noto Sans Myanmar webfont is loaded and usable');

        assert(
            report.ratio >= 1.85,
            `Body line-height ratio is ${report.ratio}, needs >= 1.85 for Myanmar`,
        );
        ok(`Body leading is ${report.lineHeight} on ${report.fontSize} (ratio ${report.ratio})`);

        assert(
            report.paragraphCount >= 50,
            `Only ${report.paragraphCount} <p> in the body — the plain-text newlines are collapsing again`,
        );
        ok(`Plain-text body is split into ${report.paragraphCount} paragraphs`);

        assert(
            report.lineCount >= 50,
            `Only ${report.lineCount} rendered lines — the body looks collapsed`,
        );
        ok(`Body renders ${report.lineCount} line boxes`);

        assert(report.h1, 'no <h1> found');
        assert(
            report.h1.ratio >= 1.45,
            `H1 line-height ratio is ${report.h1.ratio}, too tight for a Myanmar heading`,
        );
        assert(
            report.h1.letterSpacing === 'normal' || parseFloat(report.h1.letterSpacing) >= 0,
            `H1 has negative tracking (${report.h1.letterSpacing}) which collides Myanmar marks`,
        );
        ok(`H1 leading is ${report.h1.lineHeight} on ${report.h1.fontSize} (ratio ${report.h1.ratio})`, {
            letterSpacing: report.h1.letterSpacing,
        });

        // ------------------------------------------------- blog index card
        // The same post is the featured card on /blog, which uses its own type scale.
        await page.goto(`${base}/blog`, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(1200);
        const card = await page.evaluate(() => {
            const h = [...document.querySelectorAll('h2')].find((el) => /[\u1000-\u109F]/.test(el.textContent || ''));
            if (!h) return null;
            const cs = getComputedStyle(h);
            return {
                text: h.textContent.trim().slice(0, 30),
                fontSize: cs.fontSize,
                lineHeight: cs.lineHeight,
                ratio: +(parseFloat(cs.lineHeight) / parseFloat(cs.fontSize)).toFixed(2),
                letterSpacing: cs.letterSpacing,
            };
        });
        assert(card, '/blog: no Myanmar-script card heading found');
        assert(card.ratio >= 1.4, `/blog: featured card heading leading is ${card.ratio}, too tight`);
        ok(`/blog featured card heading leading is ${card.lineHeight} on ${card.fontSize} (ratio ${card.ratio})`, {
            letterSpacing: card.letterSpacing,
        });
        await page.screenshot({ path: `${out}/myanmar-${label}-index.png`, fullPage: false });
        ok(`Screenshot -> myanmar-${label}-index.png`);

        // Back to the post for the body screenshots.
        await page.goto(`${base}/blog/${slug}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.waitForLoadState('networkidle').catch(() => {});
        await page.waitForTimeout(1200);

        await page.screenshot({ path: `${out}/myanmar-${label}.png`, fullPage: false });        await page.evaluate(() => {
            const el = document.querySelector('[data-article-body]');
            el?.scrollIntoView({ block: 'start' });
            window.scrollBy(0, -90);
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: `${out}/myanmar-${label}-body.png`, fullPage: false });
        ok(`Screenshots -> myanmar-${label}.png / myanmar-${label}-body.png`);

        // Dark mode sanity: the marks must still be legible.
        //
        // `emulateMedia({ colorScheme: 'dark' })` does nothing here — this project
        // uses class-based dark mode (`darkMode: ['class']`), so the `.dark` class
        // has to be toggled on <html> directly.
        await page.evaluate(() => document.documentElement.classList.add('dark'));
        await page.waitForTimeout(400);
        const darkApplied = await page.evaluate(() => ({
            hasClass: document.documentElement.classList.contains('dark'),
            bodyBg: getComputedStyle(document.body).backgroundColor,
        }));
        assert(darkApplied.hasClass, 'dark class was not applied');
        await page.screenshot({ path: `${out}/myanmar-${label}-dark.png`, fullPage: false });
        ok('Dark-mode screenshot captured', darkApplied);

        console.log(`\nPASS ${results.length}/${results.length}`);
    } catch (e) {
        console.error(`\nFAIL after ${results.length} passing checks`);
        console.error(e.message);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
