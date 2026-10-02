# LearningWeb — Project Memory

Laravel 10 LMS, "Sanpya Online Academy" (sanpyalearning.com). Dev on Windows
`d:\education\LearningWeb`; prod Linux `/var/www/html/sanpyalearning`.

**Read `CLAUDE.md` (backend) and `FRONTEND_GUIDE.md` (Inertia/Vue) first.** Both are canonical.
This file is deliberately a *short index* — it records state, environment traps and harness
lessons. The frontend rules live in `FRONTEND_GUIDE.md` (§"The rules that bite", §"Deleting a
migrated Blade view", §"Server-side contract"); do not duplicate them here.

| Surface | Stack | Status |
|---|---|---|
| public site + student area | Inertia v2 + Vue 3 + TS + Tailwind (shadcn-style) | done |
| `admin` panel | migrating to the same Vue stack | 2 pages left |
| `instructor` panel | Blade + AdminLTE 3 + Alpine | untouched (12 pages) |

## Migration state (started 2026-10-01)

**Migrated to Vue:** `layouts/AdminLayout.vue` (role-aware, shared by both panels),
`components/ui/{DataTable,ImageUpload}.vue`, `components/charts/{LineChart,DoughnutChart}.vue`,
`pages/Admin/`: `Dashboard`, `Users/{Index,Form}`, `Courses/{Index,Form,Show,Content}`,
`Categories/{Index,Form}`, `HeroSlides/{Index,Form}`, `Enrollments/Index`, `Reviews/{Index,Form}`,
`ContactMessages/{Index,Show}`, `Blog/{Index,Form}`, `Exams/{Index,Form,Results,Grade}`,
`Reports`, `Settings`.

Charts are hand-rolled SVG rather than Chart.js — this project deliberately avoids runtime deps
(`lib/routes.ts` is hand-written rather than Ziggy).

**Remaining admin Blade (2):** `users/{show,create-lecturer}` — then the instructor panel, which can
reuse `AdminLayout`. Admin Blade went 48 → 2. `app.blade.php` is the Inertia root — never delete.

**Migration loop (proven, non-negotiable):** controller → `Inertia::render` page → build page →
`vue-tsc --noEmit` → **non-destructive** rebuild (`.workbuddy-ai/build-safe.mjs`) → E2E test with a
throwaway fixture → **only then** back up (`.workbuddy-ai/backup/blade-views-<date>/` + SHA-256
manifest) + delete the orphaned Blade view.

## Environment traps

- **Local MySQL must be running.** XAMPP's MariaDB is not a Windows service and `sc.exe` is
  blacklisted here, so start it directly:
  `/c/xampp/mysql/bin/mysqld.exe --defaults-file="C:/xampp/mysql/bin/my.ini" --standalone`.
  `[2002] connection refused` on every page means MySQL is down, not that the app is broken.
- **MySQL CLI needs `--default-character-set=utf8mb4`** or Myanmar text comes back as `?`. It also
  emits CRLF, so `split('\n')` leaves a stray `\r` — enough to break `WHERE key = 'x\r'`.
- **`public/hot` is legitimate iff a Vite dev server is really running** (Laravel reads it to choose
  dev-server asset URLs; a stale one breaks every CSS/JS request). While a real dev server runs, the
  browser loads *source* modules — a `npm run build` changes nothing the tests see.
- **`public/build/` is gitignored.** After ANY change under `resources/css|js`, rebuild. A new Vite
  entry must go in `vite.config.js`'s `input` array first or Blade `@vite([...])` throws "Unable to
  locate file in Vite manifest". Deploy: `git pull && npm install && npm run build`.
- **Vite's clean step can hit `SAFE_DELETE_BULK_CONFIRM_REQUIRED`.** Do not work around deletion
  protection by splitting deletes — use `.workbuddy-ai/build-safe.mjs`
  (`build({build:{emptyOutDir:false}})`, retains old hashed assets, updates the manifest).
- **The Laravel Vite plugin full-reloads on `.blade.php`/asset changes**, aborting an in-flight
  `page.goto` with `net::ERR_ABORTED` — never edit `resources/` mid-run.
- **A git merge may be left half-finished.** Unresolved `<<<<<<<` in `routes/web.php` is a ParseError
  that kills the app. Check `grep -rn '^<<<<<<<' routes/ app/` and `ls .git/MERGE_HEAD`.
  `package-lock.json` has 24 conflict blocks (runtime-irrelevant); `routes/web.php` is still git-`UU`.
- **Bash kills commands at 120s** — long Playwright suites need `run_in_background: true`.

## ⚠️ Two copies of this project exist on disk

`D:\education\LearningWeb` (**this workspace / migrated**, ~104 built assets) vs
`D:\Education Web\LearningWeb` (older, **still Blade**, 2 built assets). Names differ only by a space
and case, and **both share `DB_DATABASE=Learningweb`**, so they render identical content. If the user
says "it's still Blade", first check which directory `php artisan serve` runs from. Fingerprint:
`curl -s <url>/build/manifest.json | wc -c` → ~53 KB = migrated, ~255 B = old.

## Domain rules the guides don't state

- **Never derive page statistics from a paginator** — `$paginator->count()` only sees the current
  page (the old exam-results blade reported wrong totals past 20 attempts). Run a separate aggregate.
- **Aggregate aliases must not collide with `$casts`** — `selectRaw('SUM(passed = 1) as passed')` on
  `ExamAttempt` returns a *boolean*. Alias to `passed_count`.
- **`required_if:x,v|array|min:2` is a trap** — `required_if` only drops the *required* check, so
  `min:2` still fails a present-but-empty array. Use `exclude_unless:x,v|required|array|min:2`.
- **Whitelist any column name interpolated into `orderBy()`** (`usersIndex`/`coursesIndex` do).
- **`sortByDesc(...)->take(...)` yields non-sequential keys**, so JSON serialises it as an object, not
  an array. `->values()` is load-bearing.
- **`User` uses `SoftDeletes`** (delete = `deleted_at`); `Course`, `Category`, `Review`,
  `ContactMessage`, `BlogPost`, `HeroSlide`, `Exam` do **not**.
- **Unique constraints that break fixtures:** `reviews` and `enrollments` both have
  `UNIQUE (user_id, course_id)`; `blog_posts.slug` is unique. `enrollments.enrolled_at` is
  `ON UPDATE CURRENT_TIMESTAMP` — approving an enrollment resets it and breaks date filters.
- **Two key/value stores:** `Setting` (`settings` table, feature flags e.g. `registration_enabled`)
  and `SiteSetting` (`site_settings`, typed/grouped/labelled, admin-editable). Both cache 1h under
  `setting_{key}` and forget on write. Use `SiteSetting` for Settings-page items.
- **The homepage hero is full-bleed** (`Home.vue`): the active slide's photo is an absolutely
  positioned `object-cover` background with the copy on a themed gradient scrim, and the dotted
  `hero-backdrop` texture renders only when there is no photo. `hero_slides.image` is **NOT NULL**,
  so it cannot be used to detect an empty slide — the carousel instead drops any slide whose title
  *and* subtitle are unusable, where `usableText()` counts punctuation-only values (`-`, `—`, `N/A`)
  as empty. A contentless slide must never reach the DOM. `.fade-*` transition classes live in
  `inertia.css`.
- **Public page headers are one shared component** — `components/site/PageHero.vue` (photo background
  + direction-aware scrim), used by `/courses`, `/categories`, `/blog`, `/about`, `/contact`. Props:
  `eyebrow/title/subtitle/image/align/size/icon`, plus `subtitle` and default slots. Backgrounds are
  WebP in `public/images/page-headers/`, referenced root-relative like `Logo.vue`. Heavy generated
  PNGs must be converted before committing — PHP GD `imagewebp` at q82 took 8.8 MB of PNG to 356 KB.
- **`courses` has no `is_published`** — it is `status='published'` (`Course::scopePublished`).
  Exams *do* have `is_published`.
- **Exam visibility needs ALL of:** exam `is_published`, ≥1 `ExamQuestion`, enrollment
  `payment_status = completed`, `progress_percentage >= 100` (`EXAM_TROUBLESHOOTING_GUIDE.md`).
- **Registration auto-activates** (`status='active'`); docs saying "pending + admin approval" are
  stale. `users.status` still gates login for `pending`/`inactive`.
- **FK deletion is intentionally asymmetric** (`CASCADE_DELETE_FIX.md`): `Category→Course` and
  `User(instructor)→Course` are `onDelete('restrict')`. Do NOT revert to cascade without asking.
- **Role gates have no hierarchy** — `role:admin` does not satisfy `role:lecturer`. Alias lives in
  `app/Http/Kernel.php` (Laravel 10), not `bootstrap/app.php`.
- `AdminController.php` is one big controller (~1350 lines). **Convention: add admin features as
  methods there + routes in the `admin.` group — no per-resource controllers.** Settings routes are
  `admin.settings` (GET) / `admin.settings.update` (PUT → `updateSettings`).
- **Pre-existing missing views (unresolved):** `DiscussionController::index`/`show` reference
  `courses.discussions.*`; instructor exam `grade`/`results` are missing too.
- `tests/` holds only stock examples — effectively no coverage; verify by hand.

## Verification toolbox

Chrome `/c/Program Files/Google/Chrome/Application/chrome.exe`; Playwright in the managed Node
workspace `C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright`; use
managed Node + `chromium.launch({channel:'chrome',headless:true})`. Run suites as
`SANPYA_SEED_PASSWORD=password <managed-node> .workbuddy-ai/<script>.cjs`.

- **Log every check as it passes, not only in a final summary** — otherwise a stall is
  indistinguishable from slow progress.
- **A very long silent run is usually environmental.** `net::ERR_NETWORK_IO_SUSPENDED` / `Network
  Error` means Windows suspended Chrome's network I/O (machine slept) and the POST never completed.
  Re-run before hunting a product bug — and confirm the fixture cleanup actually ran.
- **`#app`'s `data-page` is stale after any SPA visit** (`@inertiajs/core` only reads it at boot).
  Assert component identity only after a full `page.goto`; for SPA nav assert on the rendered DOM.
  Inertia pushes the URL *before* swapping the component, so gate on the DOM, not `location.pathname`.
  The attribute is JSON-escaped, so a raw regex capture gives `Admin\/Reports` — unescape (`\\/` → `/`)
  or `JSON.parse` the attribute before comparing, or every component check falsely fails.
- **Inertia login forms can't use `page.waitForURL`** — poll `window.location.pathname` via
  `waitForFunction`; expect a 409 for non-student logins (`Inertia::location`). The CSRF token goes
  stale after login regenerates the session — log out with `context.clearCookies()`.
- **A debounced search input swallows clicks** — wait for the query param + network idle first.
- **Toasts live ~7s, so identical repeats can't be told apart** — wait for the dialog to hide
  (`onSuccess`) instead, and only assert a toast whose text differs from the previous one.
- **reka-ui `Checkbox` is a `<button role="checkbox" data-state>`**; the hidden `<input>` only renders
  when `name` is set. Click when `data-state` differs rather than trusting `locator.check()`.
- **SVG has no `innerText`** — use `allTextContents()`. Adjacent inline `<span>`s concatenate
  (`Students 1673%`), so assert discrete cells or the chart's `aria-label` summary.
- Git Bash mangles `/route` args into Windows paths — set `MSYS_NO_PATHCONV=1`; `curl` needs `--noproxy '*'`.

### Scripts (`.workbuddy-ai/`)

`logo-check.cjs` (15) · `admin-pilot-check.cjs` (28, cleanup `DELETE FROM users WHERE email LIKE
'pilot.user.%@example.com'`) · `courses-check.cjs` (18) · `categories-check.cjs` (17) ·
`hero-slides-check.cjs` (14) · `course-cluster-check.cjs` (26) · `reviews-enrollments-check.cjs` (24) ·
`contact-blog-check.cjs` (26) · `exams-check.cjs` (31) · `reports-settings-check.cjs` (23) ·
`smoke-reports-settings.cjs` (login + assert both pages still serve after the Blade deletion).
`shots.cjs` = screenshots; `diag-*.cjs` = redirect chains / broken images / overflow / SPA traces;
`build-safe.mjs` = non-destructive rebuild.

**Re-run the regression suites after any layout-wide nav change.**
