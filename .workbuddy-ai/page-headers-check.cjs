// Page-header background verification for the five public pages.
//
// Asserts each page's header band renders its background photo full-bleed with the
// copy intact, and that nothing overflows. Also captures desktop / mobile / dark
// screenshots for each page.
//
// Run: node .workbuddy-ai/page-headers-check.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');

const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';

const PAGES = [
    { url: '/courses', eyebrow: 'Course catalogue', title: 'Browse all courses', image: 'courses.webp' },
    { url: '/categories', eyebrow: 'Explore', title: 'Browse by category', image: 'categories.webp' },
    { url: '/blog', eyebrow: 'Blog', title: 'Insights, guides and stories from Sanpya', image: 'blog.webp' },
    { url: '/about', eyebrow: 'About us', title: 'Education that moves careers forward', image: 'about.webp' },
    { url: '/contact', eyebrow: 'Contact', title: 'We would love to hear from you', image: 'contact.webp' },
];

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

        const load = async (url) => {
            await page.goto(`${base}${url}`, { waitUntil: 'domcontentloaded' });
            // The header photo is above the fold; wait for it to actually decode.
            await page.waitForFunction(
                () => {
                    const img = [...document.querySelectorAll('section img[aria-hidden="true"]')][0];
                    return img && img.complete && img.naturalWidth > 0;
                },
                undefined,
                { timeout: 60000 },
            );
        };

        const header = () =>
            page.evaluate(() => {
                const section = [...document.querySelectorAll('section')].find((s) =>
                    s.querySelector('img[aria-hidden="true"]'),
                );
                const img = section?.querySelector('img[aria-hidden="true"]');
                const h1 = section?.querySelector('h1');
                const box = section?.getBoundingClientRect();
                const imgBox = img?.getBoundingClientRect();

                return {
                    text: section?.innerText ?? '',
                    h1: h1?.textContent?.trim() ?? '',
                    imgSrc: img?.getAttribute('src') ?? '',
                    imgNatural: `${img?.naturalWidth}x${img?.naturalHeight}`,
                    sectionWidth: Math.round(box?.width ?? 0),
                    sectionLeft: Math.round(box?.left ?? 0),
                    sectionHeight: Math.round(box?.height ?? 0),
                    imgWidth: Math.round(imgBox?.width ?? 0),
                    imgLeft: Math.round(imgBox?.left ?? 0),
                };
            });

        for (const spec of PAGES) {
            await load(spec.url);
            const state = await header();
            const viewport = page.viewportSize().width;

            assert(
                state.imgSrc.endsWith(spec.image),
                `${spec.url}: expected background ${spec.image}, got "${state.imgSrc}"`,
            );
            assert(
                state.imgWidth >= viewport - 1 && state.imgLeft === 0,
                `${spec.url}: photo is not full-bleed — ${state.imgWidth}px at x=${state.imgLeft}, viewport ${viewport}`,
            );
            assert(
                state.sectionWidth >= viewport - 1 && state.sectionLeft === 0,
                `${spec.url}: band is not full-width — ${state.sectionWidth}px at x=${state.sectionLeft}`,
            );
            assert(
                state.h1 === spec.title,
                `${spec.url}: title mismatch — "${state.h1}" vs "${spec.title}"`,
            );
            assert(
                state.text.includes(spec.eyebrow),
                `${spec.url}: eyebrow "${spec.eyebrow}" missing from the band`,
            );

            ok(`${spec.url} — photo full-bleed, copy intact`, {
                image: spec.image,
                natural: state.imgNatural,
                band: `${state.sectionWidth}x${state.sectionHeight}`,
                title: state.h1.slice(0, 34),
            });
        }

        // ---------- Overflow at three widths ----------
        for (const width of [390, 768, 1440]) {
            await page.setViewportSize({ width, height: 900 });
            for (const spec of PAGES) {
                await load(spec.url);
                const overflow = await page.evaluate(
                    () => document.documentElement.scrollWidth - window.innerWidth,
                );
                assert(overflow <= 1, `${spec.url}: ${overflow}px horizontal overflow at ${width}px`);
            }
        }
        ok('No horizontal overflow on any of the five pages at 390 / 768 / 1440px');

        // ---------- Screenshots ----------
        await page.setViewportSize({ width: 1440, height: 960 });
        for (const spec of PAGES) {
            await load(spec.url);
            await page.waitForTimeout(400);
            const name = spec.url.replace('/', '');
            await page.screenshot({
                path: `${out}/page-header-${name}.png`,
                clip: { x: 0, y: 0, width: 1440, height: 520 },
            });
        }
        ok('Screenshots captured for all five pages (desktop)');

        await page.setViewportSize({ width: 390, height: 844 });
        for (const spec of PAGES) {
            await load(spec.url);
            await page.waitForTimeout(400);
            const name = spec.url.replace('/', '');
            await page.screenshot({
                path: `${out}/page-header-${name}-mobile.png`,
                clip: { x: 0, y: 0, width: 390, height: 480 },
            });
        }
        ok('Screenshots captured for all five pages (mobile)');

        await page.setViewportSize({ width: 1440, height: 960 });
        for (const spec of PAGES) {
            await load(spec.url);
            await page.evaluate(() => document.documentElement.classList.add('dark'));
            await page.waitForTimeout(400);
            const name = spec.url.replace('/', '');
            await page.screenshot({
                path: `${out}/page-header-${name}-dark.png`,
                clip: { x: 0, y: 0, width: 1440, height: 520 },
            });
        }
        ok('Screenshots captured for all five pages (dark mode)');

        assert(errors.length === 0, `Uncaught page errors: ${errors.join(' | ')}`);
        ok('No uncaught page errors');

        console.log(`\nPAGE HEADERS: ${results.length}/${results.length} checks passed`);
    } catch (error) {
        results.push({ check: 'RUN', passed: false, error: error.message });
        console.error(`\nFAILED: ${error.message}`);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
