// Homepage hero slider verification.
//
//   1. The hero photo is full-bleed (covers the whole band, edge to edge).
//   2. The carousel never blanks: the headline is always real copy, on every slide.
//   3. A contentless slide (title "-", no subtitle) is dropped from the carousel
//      rather than rendering an empty panel — proven with a throwaway fixture row.
//   4. No horizontal overflow at 390px or 1440px.
//
// Run: node .workbuddy-ai/hero-slider-check.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');

const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';
const MYSQL = 'C:/xampp/mysql/bin/mysql.exe';
const DB = 'Learningweb';
const JUNK_ID_LABEL = 'zz_hero_guard_probe';

const results = [];
const ok = (check, detail) => {
    results.push({ check, passed: true, detail });
    console.log(`  ok  [${results.length}] ${check}${detail ? ` ${JSON.stringify(detail)}` : ''}`);
};
const assert = (value, message) => { if (!value) throw new Error(message); };

function sql(statement) {
    return execFileSync(
        MYSQL,
        ['--default-character-set=utf8mb4', '-u', 'root', '-N', '-B', '-e', `USE ${DB}; ${statement}`],
        { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] },
    )
        .replace(/\r\n/g, '\n')
        .trim();
}

/** Punctuation-only text is a placeholder, not copy. Mirrors Home.vue's usableText(). */
const usable = (value) => ((value ?? '').trim().replace(/[\s\-–—_.·•|/\\]+/g, '').length > 0 ? value.trim() : '');

let junkId = null;

function cleanup() {
    if (junkId !== null) {
        try { sql(`DELETE FROM hero_slides WHERE id = ${junkId};`); } catch { /* ignore */ }
        junkId = null;
    }
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });

    try {
        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        page.setDefaultNavigationTimeout(90000);

        const errors = [];
        page.on('pageerror', (e) => errors.push(e.message));

        const loadHome = async () => {
            await page.goto(`${base}/`, { waitUntil: 'domcontentloaded' });
            await page.waitForFunction(
                () => /Featured/i.test(document.body.innerText),
                undefined,
                { timeout: 60000 },
            );
        };

        const heroState = () =>
            page.evaluate(() => {
                const section = document.querySelector('section');
                const bg = section?.querySelector('img[aria-hidden="true"]');
                const h1 = section?.querySelector('h1');
                const sub = h1?.nextElementSibling?.tagName === 'P' ? h1.nextElementSibling : null;

                return {
                    h1: h1?.textContent?.trim() ?? '',
                    h1Height: Math.round(h1?.getBoundingClientRect().height ?? 0),
                    subtitle: sub?.textContent?.trim() ?? '',
                    bgSrc: bg?.getAttribute('src') ?? '',
                    bgWidth: Math.round(bg?.getBoundingClientRect().width ?? 0),
                    sectionWidth: Math.round(section?.getBoundingClientRect().width ?? 0),
                    sectionLeft: Math.round(section?.getBoundingClientRect().left ?? 0),
                    bgLeft: Math.round(bg?.getBoundingClientRect().left ?? 0),
                    dots: document.querySelectorAll('button[aria-label^="Go to slide"]').length,
                };
            });

        await loadHome();

        // ---------- 1. Full-bleed background ----------
        const first = await heroState();
        const viewport = page.viewportSize().width;

        assert(!!first.bgSrc, 'Hero has no background photo');
        ok('Hero renders a background photo', { src: first.bgSrc.split('/').pop() });

        assert(
            first.sectionWidth >= viewport - 1 && first.sectionLeft === 0,
            `Hero band is not full-width: section ${first.sectionWidth}px at x=${first.sectionLeft}, viewport ${viewport}`,
        );
        assert(
            first.bgWidth >= viewport - 1 && first.bgLeft === 0,
            `Background photo is not full-bleed: ${first.bgWidth}px at x=${first.bgLeft}, viewport ${viewport}`,
        );
        ok('Background photo covers the full band edge to edge', {
            viewport,
            section: first.sectionWidth,
            image: first.bgWidth,
        });

        // ---------- 2. Carousel never blanks ----------
        const seen = new Map();
        const blanks = [];

        for (let i = 0; i < 9; i++) {
            const state = await heroState();
            if (!usable(state.h1) || state.h1Height < 40) {
                blanks.push({ h1: state.h1, height: state.h1Height });
            }
            if (!usable(state.subtitle)) {
                blanks.push({ h1: state.h1, subtitle: '(empty)' });
            }
            seen.set(state.h1, { subtitle: state.subtitle.slice(0, 40), height: state.h1Height });
            await page.waitForTimeout(2000);
        }

        assert(blanks.length === 0, `Hero blanked during rotation: ${JSON.stringify(blanks)}`);
        ok('Hero headline and subtitle present on every sample over ~18s', { samples: 9 });

        assert(seen.size >= 2, `Expected both slides to rotate through, saw: ${[...seen.keys()].join(' | ')}`);
        ok('Both slides rotate through the carousel', { slides: [...seen.keys()] });

        // ---------- 3. The empty-slide guard ----------
        // `image` is NOT NULL, so borrow a real one: this models the realistic bad
        // row — a slide with a picture but no copy at all.
        sql(
            `INSERT INTO hero_slides (title, subtitle, button_text, button_link, image, is_active, sort_order, created_at, updated_at) ` +
            `SELECT '-', NULL, 'Browse Courses', '/courses', image, 1, 99, NOW(), NOW() FROM hero_slides ORDER BY id LIMIT 1;`,
        );
        junkId = Number(sql(`SELECT id FROM hero_slides WHERE sort_order = 99 ORDER BY id DESC LIMIT 1;`));
        assert(Number.isFinite(junkId), 'Could not create the guard fixture');
        ok('Inserted a contentless slide fixture', { id: junkId, title: '-' });

        await loadHome();
        const guarded = await heroState();
        assert(
            guarded.dots === 2,
            `Contentless slide was not dropped: ${guarded.dots} dots (expected 2)`,
        );
        assert(usable(guarded.h1), `Hero headline blank with the junk slide present: "${guarded.h1}"`);
        ok('Contentless slide is dropped from the carousel', { dots: guarded.dots });

        // It must also be absent from the rendered slide list entirely.
        const rendered = await page.evaluate(() =>
            [...document.querySelectorAll('h1')].map((h) => h.textContent.trim()),
        );
        assert(
            !rendered.some((t) => t.replace(/[\s\-–—_.·•|/\\]+/g, '') === ''),
            `A placeholder headline reached the DOM: ${JSON.stringify(rendered)}`,
        );
        ok('No placeholder headline reaches the DOM', { headings: rendered.length });

        // Rotating with the junk slide present must still never blank.
        const blanksWithJunk = [];
        for (let i = 0; i < 6; i++) {
            const state = await heroState();
            if (!usable(state.h1)) blanksWithJunk.push(state.h1);
            await page.waitForTimeout(2000);
        }
        assert(blanksWithJunk.length === 0, `Hero blanked with junk slide present: ${JSON.stringify(blanksWithJunk)}`);
        ok('Hero stays populated while rotating with a junk slide in the table');

        cleanup();
        ok('Guard fixture removed', { id: junkId });

        // ---------- 4. Overflow ----------
        for (const width of [390, 768, 1440]) {
            await page.setViewportSize({ width, height: 900 });
            await loadHome();
            const overflow = await page.evaluate(
                () => document.documentElement.scrollWidth - window.innerWidth,
            );
            assert(overflow <= 1, `Horizontal overflow of ${overflow}px at ${width}px`);
        }
        ok('No horizontal overflow at 390 / 768 / 1440px');

        // ---------- Screenshot ----------
        await page.setViewportSize({ width: 1440, height: 960 });
        await loadHome();
        await page.waitForTimeout(700);
        await page.screenshot({ path: `${out}/sanpya-home-hero-slider.png`, clip: { x: 0, y: 0, width: 1440, height: 640 } });
        ok('Screenshot: full-bleed hero');

        assert(errors.length === 0, `Uncaught page errors: ${errors.join(' | ')}`);
        ok('No uncaught page errors');

        fs.writeFileSync(
            `${out}/sanpya-hero-slider-results.json`,
            JSON.stringify({ results, slides: [...seen.entries()] }, null, 2),
        );

        console.log(`\nHERO SLIDER: ${results.length}/${results.length} checks passed`);
    } catch (error) {
        results.push({ check: 'RUN', passed: false, error: error.message });
        console.error(`\nFAILED: ${error.message}`);
        process.exitCode = 1;
    } finally {
        cleanup();
        await browser.close();
    }
})();
