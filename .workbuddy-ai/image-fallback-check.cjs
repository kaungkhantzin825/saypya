// Missing-image fallback verification.
//
// Rule under test: a missing image must never render the browser's broken-image
// icon. Public content images fall back to `/images/SanPya-Logo.png` via
// `AppImage.vue`; avatars fall back to the initials avatar (`Avatar.vue`).
//
// Covers both branches of the component:
//   * `src` empty/null      -> the model accessors now return null
//   * `src` set but broken  -> forced at runtime by pointing an <img> at a 404
//
// Run: SANPYA_SEED_PASSWORD=password node .workbuddy-ai/image-fallback-check.cjs
const { chromium } = require('C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright');
const fs = require('node:fs');

const base = 'http://127.0.0.1:8899';
const out = 'D:/education/LearningWeb/.workbuddy-ai/outputs';
const LOGO = '/images/SanPya-Logo.png';

const results = [];
const assert = (value, message) => { if (!value) throw new Error(message); };
const ok = (check, detail) => {
    results.push({ check, passed: true, detail });
    console.log(`  ok  [${results.length}] ${check}${detail ? ` ${JSON.stringify(detail)}` : ''}`);
};

/** Every <img> that finished loading but decoded to nothing = a broken image. */
const BROKEN_PROBE = () => {
    const images = [...document.querySelectorAll('img')];
    const broken = images
        .filter((img) => img.complete && img.naturalWidth === 0)
        .map((img) => ({
            src: img.getAttribute('src') ?? '(no src)',
            alt: img.getAttribute('alt') ?? '',
            cls: img.className,
        }));
    const emptySrc = images
        .filter((img) => {
            const src = (img.getAttribute('src') ?? '').trim();
            return src === '' || src === 'null' || src === 'undefined';
        })
        .map((img) => img.className);

    return { total: images.length, broken, emptySrc };
};

(async () => {
    fs.mkdirSync(out, { recursive: true });
    const browser = await chromium.launch({ channel: 'chrome', headless: true });

    try {
        const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
        const page = await context.newPage();
        page.setDefaultTimeout(60000);
        page.setDefaultNavigationTimeout(90000);

        const pageErrors = [];
        page.on('pageerror', (e) => pageErrors.push(e.message));

        /** Load a URL and settle lazy images by scrolling the full page. */
        const load = async (url, colorScheme = 'light') => {
            await page.emulateMedia({ colorScheme });
            await page.goto(`${base}${url}`, { waitUntil: 'domcontentloaded' });
            await page.waitForLoadState('networkidle').catch(() => {});
            await page.evaluate(async () => {
                const step = window.innerHeight;
                for (let y = 0; y < document.body.scrollHeight; y += step) {
                    window.scrollTo(0, y);
                    await new Promise((r) => setTimeout(r, 60));
                }
                window.scrollTo(0, 0);
            });
            // Give every in-flight image a chance to settle.
            await page
                .waitForFunction(
                    () => [...document.querySelectorAll('img')].every((i) => i.complete),
                    undefined,
                    { timeout: 30000 },
                )
                .catch(() => {});
        };

        const assertNoBrokenImages = async (label) => {
            const state = await page.evaluate(BROKEN_PROBE);
            assert(
                state.emptySrc.length === 0,
                `${label}: ${state.emptySrc.length} <img> with an empty src -> ${JSON.stringify(state.emptySrc)}`,
            );
            assert(
                state.broken.length === 0,
                `${label}: ${state.broken.length} broken image(s) -> ${JSON.stringify(state.broken)}`,
            );
            ok(`${label}: ${state.total} images, none broken and none with an empty src`);
            return state;
        };

        /**
         * Details of the first brand-mark *fallback* on the page.
         *
         * Must discriminate from the navbar `Logo.vue`, which uses the same file.
         * `AppImage`'s fallback is the only one that paints its own white plate,
         * so `bg-white` on the <img> itself is the unique signature.
         */
        const logoProbe = () =>
            page.evaluate((logoFile) => {
                const img = [...document.querySelectorAll('img')].find(
                    (i) =>
                        (i.getAttribute('src') ?? '').includes(logoFile) && i.className.includes('bg-white'),
                );
                if (!img) return null;
                return {
                    src: img.getAttribute('src'),
                    natural: `${img.naturalWidth}x${img.naturalHeight}`,
                    alt: img.getAttribute('alt'),
                    ariaHidden: img.getAttribute('aria-hidden'),
                    cls: img.className,
                };
            }, LOGO.split('/').pop());

        // ---------------------------------------------------------------- 1
        // /blog — the single seeded post has `featured_image = NULL`, so the
        // featured card must render the brand mark rather than a broken icon.
        await load('/blog');
        const blogLogo = await logoProbe();
        assert(blogLogo, '/blog: no image is using the brand-mark fallback');
        assert(blogLogo.natural !== '0x0', `/blog: fallback did not decode (${blogLogo.natural})`);
        ok('/blog: featured card falls back to the brand mark', {
            src: blogLogo.src,
            natural: blogLogo.natural,
        });

        // The fallback is drawn on a white plate with `object-contain`, otherwise
        // the full-colour logo would be cropped and unreadable on dark surfaces.
        assert(
            blogLogo.cls.includes('object-contain') && blogLogo.cls.includes('bg-white'),
            `/blog: fallback missing its plate classes -> ${blogLogo.cls}`,
        );
        ok('/blog: fallback uses object-contain on a white plate');

        assert(
            blogLogo.alt === '' && blogLogo.ariaHidden === 'true',
            `/blog: fallback should be decorative (alt="${blogLogo.alt}", aria-hidden="${blogLogo.ariaHidden}")`,
        );
        ok('/blog: fallback is decorative for assistive tech');

        await assertNoBrokenImages('/blog');

        // ---------------------------------------------------------------- 2
        // Blog detail — the hero image is now always rendered.
        const slug = await page.evaluate(() => {
            const link = document.querySelector('a[href^="/blog/"]');
            return link ? new URL(link.href).pathname : null;
        });
        assert(slug, '/blog: could not find a post link to follow');
        await load(slug);
        const postLogo = await logoProbe();
        assert(postLogo, `${slug}: post hero did not fall back to the brand mark`);
        assert(postLogo.natural !== '0x0', `${slug}: post hero fallback did not decode`);
        ok(`${slug}: post hero falls back to the brand mark`, { natural: postLogo.natural });
        await assertNoBrokenImages(slug);

        // ---------------------------------------------------------------- 3
        // Public pages — no broken images anywhere, including the course cards
        // whose thumbnails are external Unsplash URLs.
        for (const url of ['/', '/courses', '/categories', '/about', '/contact']) {
            await load(url);
            await assertNoBrokenImages(url);
        }

        // ---------------------------------------------------------------- 4
        // Instructor avatars: all 26 users have `avatar = NULL`, so every card
        // must show the initials avatar instead of an <img> pointing at nothing.
        await load('/courses');
        const avatarState = await page.evaluate(() => {
            const fallbacks = [...document.querySelectorAll('[class*="rounded-full"]')].filter((el) =>
                el.className.includes('bg-brand-50'),
            );
            return {
                count: fallbacks.length,
                sample: fallbacks[0]?.innerText?.trim() ?? '',
            };
        });
        assert(
            avatarState.count > 0,
            '/courses: no initials-avatar fallback found — instructor avatars may still be raw <img>',
        );
        ok('/courses: instructor avatars render the initials fallback', avatarState);

        // ---------------------------------------------------------------- 5
        // Dark mode — the plate must stay white or the logo becomes unreadable.
        await load('/blog', 'dark');
        const darkLogo = await logoProbe();
        assert(darkLogo, '/blog (dark): brand-mark fallback missing');
        assert(
            darkLogo.cls.includes('bg-white'),
            `/blog (dark): fallback plate is not white -> ${darkLogo.cls}`,
        );
        const plateLuma = await page.evaluate(() => {
            const img = [...document.querySelectorAll('img')].find(
                (i) =>
                    (i.getAttribute('src') ?? '').includes('SanPya-Logo') && i.className.includes('bg-white'),
            );
            if (!img) return null;
            const bg = getComputedStyle(img).backgroundColor;
            const m = bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
            if (!m) return { bg, luma: null };
            const [r, g, b] = [Number(m[1]), Number(m[2]), Number(m[3])];
            return { bg, luma: Math.round(0.2126 * r + 0.7152 * g + 0.0722 * b) };
        });
        assert(plateLuma && plateLuma.luma > 200, `/blog (dark): plate is not light -> ${JSON.stringify(plateLuma)}`);
        ok('/blog (dark): fallback plate stays light so the logo reads', plateLuma);
        await assertNoBrokenImages('/blog (dark)');

        // ---------------------------------------------------------------- 6
        // The @error branch: point a live, AppImage-managed <img> at a 404 and
        // confirm the component swaps itself for the brand mark.
        await load('/courses');
        const forced = await page.evaluate(async (logoFile) => {
            // A course-card thumbnail: managed by AppImage, currently a real photo.
            const img = [...document.querySelectorAll('img')].find((i) => {
                const src = i.getAttribute('src') ?? '';
                return src.includes('/storage/') || src.startsWith('http');
            });
            if (!img) return { found: false };
            const before = img.getAttribute('src');
            img.setAttribute('src', '/definitely-missing-image-404.jpg');
            await new Promise((r) => setTimeout(r, 2500));
            const after = img.getAttribute('src');
            return {
                found: true,
                before,
                after,
                natural: `${img.naturalWidth}x${img.naturalHeight}`,
                cls: img.className,
                swapped: (after ?? '').includes(logoFile),
            };
        }, LOGO.split('/').pop());

        assert(forced.found, '/courses: no AppImage-managed photo found to break');
        assert(
            forced.swapped,
            `/courses: a 404 image did not fall back to the brand mark -> ${JSON.stringify(forced)}`,
        );
        assert(forced.natural !== '0x0', `/courses: swapped fallback did not decode (${forced.natural})`);
        ok('/courses: a 404 image swaps itself for the brand mark', {
            before: forced.before,
            after: forced.after,
            natural: forced.natural,
        });
        await assertNoBrokenImages('/courses (after forced 404)');

        // ---------------------------------------------------------------- 7
        // No third-party placeholder host may appear anywhere in the markup.
        await load('/courses');
        const deadHosts = await page.evaluate(() =>
            ['placehold.co', 'via.placeholder.com', 'ui-avatars.com', 'dummyimage.com'].filter((host) =>
                document.documentElement.innerHTML.includes(host),
            ),
        );
        assert(deadHosts.length === 0, `External placeholder host(s) still referenced: ${deadHosts.join(', ')}`);
        ok('No external placeholder host is referenced in the rendered markup');

        // ---------------------------------------------------------------- 8
        // Admin tables — the users table had an unguarded raw <img> avatar and
        // the courses table an unguarded raw <img> thumbnail.
        await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('#email').fill('admin@learnhub.com');
        await page.locator('#password').fill(process.env.SANPYA_SEED_PASSWORD);
        await page.getByRole('button', { name: 'Sign in', exact: true }).click();
        await page.waitForFunction(() => window.location.pathname === '/admin/dashboard', undefined, {
            timeout: 60000,
        });
        ok('Admin login reaches /admin/dashboard');

        await load('/admin/courses');
        await assertNoBrokenImages('/admin/courses');
        const adminThumbs = await page.evaluate(() =>
            [...document.querySelectorAll('img')].filter((i) => i.className.includes('h-12 w-20')).length,
        );
        assert(adminThumbs > 0, '/admin/courses: thumbnail cells not found');
        ok(`/admin/courses: ${adminThumbs} thumbnail cells render through AppImage`);

        await load('/admin/users');
        await assertNoBrokenImages('/admin/users');
        const adminAvatars = await page.evaluate(() =>
            [...document.querySelectorAll('span[class*="rounded-full"]')].filter((el) =>
                el.className.includes('bg-brand-50'),
            ).length,
        );
        assert(adminAvatars > 0, '/admin/users: initials avatars not found');
        ok(`/admin/users: ${adminAvatars} rows use the initials avatar`);

        // ---------------------------------------------------------------- 9
        // Screenshots for eyeballing.
        for (const [url, name, scheme] of [
            ['/blog', 'fallback-blog-light', 'light'],
            ['/blog', 'fallback-blog-dark', 'dark'],
            ['/courses', 'fallback-courses-light', 'light'],
        ]) {
            await load(url, scheme);
            await page.screenshot({ path: `${out}/${name}.png`, fullPage: false });
        }
        ok('Screenshots written to .workbuddy-ai/outputs');

        assert(pageErrors.length === 0, `Page errors: ${JSON.stringify(pageErrors)}`);
        ok('No uncaught page errors');

        console.log(`\nPASS ${results.length}/${results.length}`);
    } catch (error) {
        console.error(`\nFAIL after ${results.length} passing checks`);
        console.error(error.message);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
