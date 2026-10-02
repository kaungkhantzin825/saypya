# LearningWeb — Testing & Harness Notes

Companion to `MEMORY.md`. Read this **before writing or running any Playwright suite** for this
project. Environment traps and domain rules stay in `MEMORY.md`.

## Toolbox

- Chrome: `/c/Program Files/Google/Chrome/Application/chrome.exe`
- Playwright: `C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright`
- Managed Node: `C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/versions/22.22.2-3/node.exe`
- Launch with `chromium.launch({ channel: 'chrome', headless: true })`.
- Run suites as `SANPYA_SEED_PASSWORD=password <managed-node> .workbuddy-ai/<script>.cjs`.
- **Bash kills commands at 120s** — long suites need `run_in_background: true`.

## Lessons that cost real time

- **Log every check as it passes, not only in a final summary** — otherwise a stall is
  indistinguishable from slow progress.
- **A very long silent run is usually environmental.** `net::ERR_NETWORK_IO_SUSPENDED` / `Network
  Error` means Windows suspended Chrome's network I/O (machine slept) and the POST never completed.
  Re-run before hunting a product bug — and confirm the fixture cleanup actually ran.
- **`#app`'s `data-page` is stale after any SPA visit** (`@inertiajs/core` reads it only at boot).
  Assert component identity only after a full `page.goto`; for SPA nav assert on the rendered DOM.
  Inertia pushes the URL *before* swapping the component, so gate on the DOM, not `location.pathname`.
  The attribute is JSON-escaped, so a raw regex capture gives `Admin\/Reports` — unescape (`\\/` → `/`)
  or `JSON.parse` before comparing, or every component check falsely fails.
- **Inertia login forms can't use `page.waitForURL`** — poll `window.location.pathname` via
  `waitForFunction`; expect a 409 for non-student logins (`Inertia::location`). The CSRF token goes
  stale after login regenerates the session — log out with `context.clearCookies()`.
- **A debounced search input swallows clicks** — wait for the query param + network idle first.
- **Toasts live ~7s, so identical repeats can't be told apart** — wait for the dialog to hide
  (`onSuccess`) instead, and only assert a toast whose text differs from the previous one.
- **reka-ui `Checkbox` is a `<button role="checkbox" data-state>`**; the hidden `<input>` renders only
  when `name` is set. Click when `data-state` differs rather than trusting `locator.check()`.
- **SVG has no `innerText`** — use `allTextContents()`. Adjacent inline `<span>`s concatenate
  (`Students 1673%`), so assert discrete cells or the chart's `aria-label` summary.
- **Never assert a hidden section via `innerHTML`** — Vue's dev build keeps comment nodes, so the
  markup string still matches. Assert on rendered *elements* (`TreeWalker` + `SHOW_COMMENT` is a good
  cross-check).
- **Assert geometry, not just presence.** Two real layout defects passed every textual assertion and
  were caught only by looking at screenshots: `DataTable`s clipped inside `xl:grid-cols-2` (each needs
  ≥42rem, got ~614px) and an ellipsised doughnut legend. Add width/overflow assertions.
- Git Bash mangles `/route` args into Windows paths — set `MSYS_NO_PATHCONV=1`; `curl` needs
  `--noproxy '*'`.
- **Verify generated assets are byte-distinct and count them** — two image generations once collided
  on the same auto-filename and one silently overwrote the other (4 files, not 5).
- **After a scrim/style edit, rebuild before re-screenshotting** — with `public/hot` absent the
  browser serves built assets, so identical screenshots mean the build never ran.

## Suites (`.workbuddy-ai/`)

Per-page, all passing: `logo-check` (15) · `admin-pilot-check` (28; cleanup `DELETE FROM users WHERE
email LIKE 'pilot.user.%@example.com'`) · `courses-check` (18) · `categories-check` (17) ·
`hero-slides-check` (14) · `course-cluster-check` (26) · `reviews-enrollments-check` (24) ·
`contact-blog-check` (26) · `exams-check` (31) · `reports-settings-check` (23) ·
`homepage-sections-check` (8) · `hero-slider-check` (12) · `page-headers-check` (10) ·
`image-fallback-check`.

Also: `smoke-reports-settings` (login + both pages serve after the Blade deletion), `shots.cjs`
(screenshots), `diag-*.cjs` (redirect chains / broken images / overflow / SPA traces),
`build-safe.mjs` (non-destructive rebuild).

**Re-run the regression suites after any layout-wide nav change.**
