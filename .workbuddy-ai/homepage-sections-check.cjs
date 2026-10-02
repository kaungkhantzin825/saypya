// Homepage section check.
//
//   1. "Most popular right now" must be gone (section removed).
//   2. "Everything you need to actually finish" must be gone (section hidden).
//   3. The rest of the homepage must still render, and `popularCourses` must no
//      longer be sent as an Inertia prop (its query was removed too).
//
// Run: node .workbuddy-ai/homepage-sections-check.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';

const results = [];
const ok = (check, detail) => {
    results.push({ check, passed: true, detail });
    console.log(`  ok  [${results.length}] ${check}${detail ? ` ${JSON.stringify(detail)}` : ''}`);
};
const assert = (value, message) => { if (!value) throw new Error(message); };

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });

    try {
        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        page.setDefaultNavigationTimeout(90000);

        const errors = [];
        page.on('pageerror', (e) => errors.push(e.message));

        await page.goto(`${base}/`, { waitUntil: 'domcontentloaded' });
        await page.waitForFunction(
            () => /Featured/i.test(document.body.innerText),
            undefined,
            { timeout: 60000 },
        );

        const body = await page.evaluate(() => document.body.innerText);

        // ---------- The two removals ----------
        assert(
            !/Most popular right now/i.test(body),
            'Removed section is still rendered: "Most popular right now"',
        );
        ok('"Most popular right now" no longer rendered');

        assert(
            !/Everything you need to actually finish/i.test(body),
            'Hidden section is still rendered: "Everything you need to actually finish"',
        );
        ok('"Everything you need to actually finish" no longer rendered');

        // The hidden block must produce no rendered elements. Note: asserting on
        // `innerHTML` is the WRONG test here — Vue preserves template comments in
        // dev mode (and strips them in production builds), so the commented-out
        // markup legitimately appears as a comment node while rendering nothing.
        const renderedHidden = await page.evaluate(() =>
            [...document.querySelectorAll('h1, h2, h3, h4, p, section, span')].filter((el) =>
                /Why Sanpya|Everything you need to actually finish/.test(el.textContent || ''),
            ).length,
        );
        assert(renderedHidden === 0, `Hidden section still renders ${renderedHidden} element(s)`);
        ok('Hidden section renders zero elements (only a dev-mode comment remains)');

        // ---------- The rest of the page must survive ----------
        const survivors = [
            ['Hero heading', /Start|Learn|Sanpya/i],
            ['Categories', /Browse by category|Categories/i],
            ['Featured courses', /Featured/i],
            ['Instructors', /Learn from experienced instructors/i],
            ['Final CTA', /Start learning today/i],
        ];
        for (const [label, pattern] of survivors) {
            assert(pattern.test(body), `Homepage section missing after edit: ${label}`);
        }
        ok('Every other homepage section still renders', {
            sections: survivors.map((s) => s[0]),
        });

        const cardCount = await page.locator('a[href^="/courses/"]').count();
        assert(cardCount > 0, 'No course cards rendered at all');
        ok('Course cards still render', { courseLinks: cardCount });

        // ---------- The prop must be gone from the payload ----------
        const props = await page.evaluate(() => {
            const raw = document.getElementById('app')?.dataset?.page;
            return raw ? Object.keys(JSON.parse(raw).props) : null;
        });
        assert(props !== null, 'Could not read Inertia props from #app');
        assert(
            !props.includes('popularCourses'),
            `popularCourses is still being sent: ${props.join(', ')}`,
        );
        ok('Inertia payload no longer carries popularCourses', { props });

        // ---------- Screenshot ----------
        await page.screenshot({ path: `${out}/sanpya-home-sections-updated.png`, fullPage: true });
        ok('Screenshot: homepage after section changes');

        assert(errors.length === 0, `Uncaught page errors: ${errors.join(' | ')}`);
        ok('No uncaught page errors');

        console.log(`\nHOMEPAGE SECTIONS: ${results.length}/${results.length} checks passed`);
    } catch (error) {
        results.push({ check: 'RUN', passed: false, error: error.message });
        console.error(`\nFAILED: ${error.message}`);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
