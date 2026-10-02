# LearningWeb — Project Memory

Laravel 10 LMS, "Sanpya Online Academy" (sanpyalearning.com). Dev on Windows
`d:\education\LearningWeb`; prod Linux `/var/www/html/sanpyalearning`.

**Read `CLAUDE.md` (backend) and `FRONTEND_GUIDE.md` (Inertia/Vue) first — both canonical.** This
file is a *short index*: state, environment traps, harness lessons. Frontend rule detail lives in
`FRONTEND_GUIDE.md`; do not duplicate it here.

| Surface | Stack | Status |
|---|---|---|
| public site + student area | Inertia v2 + Vue 3 + TS + Tailwind (shadcn-style) | done |
| `admin` panel | migrating to the same Vue stack | 2 pages left |
| `instructor` panel | Blade + AdminLTE 3 + Alpine | untouched (12 pages) |

## Migration state (started 2026-10-01)

**Migrated to Vue:** `layouts/AdminLayout.vue` (role-aware, shared by both panels),
`components/ui/{DataTable,ImageUpload,AppImage}.vue`, `components/charts/{LineChart,DoughnutChart}.vue`,
`pages/Admin/`: `Dashboard`, `Users/{Index,Form}`, `Courses/{Index,Form,Show,Content}`,
`Categories/{Index,Form}`, `HeroSlides/{Index,Form}`, `Enrollments/Index`, `Reviews/{Index,Form}`,
`ContactMessages/{Index,Show}`, `Blog/{Index,Form}`, `Exams/{Index,Form,Results,Grade}`,
`Reports`, `Settings`. Charts are hand-rolled SVG, not Chart.js — this project deliberately avoids
runtime deps (`lib/routes.ts` is hand-written rather than Ziggy).

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
- **MySQL CLI needs `--default-character-set=utf8mb4`** or Myanmar text comes back as `?`. It emits
  CRLF, so `split('\n')` leaves a stray `\r` — enough to break `WHERE key = 'x\r'`.
- **`public/hot` is legitimate iff a Vite dev server is really running** (Laravel reads it to pick
  dev-server asset URLs; a stale one breaks every CSS/JS request). While a real dev server runs the
  browser loads *source* modules, so `npm run build` changes nothing the tests see; with `public/hot`
  gone the browser serves **built** assets and a source edit needs a rebuild first.
- **`public/build/` is gitignored.** After ANY change under `resources/css|js`, rebuild. A new Vite
  entry must go in `vite.config.js`'s `input` array first or Blade `@vite([...])` throws "Unable to
  locate file in Vite manifest". Deploy: `git pull` **+ `composer install --no-dev
  --optimize-autoloader`** + `npm install && npm run build` + `php artisan optimize:clear`. Omitting
  the composer step broke production on 2026-10-02 (`Class "Inertia\Middleware" not found`, HTTP 500)
  — `/vendor` is gitignored and the server's `deploy.sh` had no composer line. Never commit a
  `deploy.sh`: the server's copy is untracked and a pull would then refuse to run. Confirm
  `APP_DEBUG=false` (it was leaking the Ignition page publicly).
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
  so it cannot detect an empty slide — the carousel instead drops any slide whose title *and*
  subtitle are unusable, where `usableText()` counts punctuation-only values (`-`, `—`, `N/A`) as
  empty. A contentless slide must never reach the DOM. `.fade-*` classes live in `inertia.css`.
- **Public page headers are one shared component** — `components/site/PageHero.vue` (photo background
  + direction-aware scrim), used by `/courses`, `/categories`, `/blog`, `/about`, `/contact`. Props
  `eyebrow/title/subtitle/image/align/size/icon` + `subtitle` and default slots. Backgrounds are WebP
  in `public/images/page-headers/`, referenced root-relative like `Logo.vue`. Convert heavy generated
  PNGs before committing — PHP GD `imagewebp` q82 took 8.8 MB of PNG to 356 KB.
- **Missing images fall back to `/images/SanPya-Logo.png`** via `components/ui/AppImage.vue` (renders
  the fallback when `src` is empty *and* on `@error`). Never emit a bare `<img :src>` for
  user-supplied or nullable media — an empty src renders a broken-image icon. Public cards use
  `AppImage`; admin tables keep `v-if` guards.
- **A named Tailwind `text-*` sets a line-height, and `sm:`/`lg:` beats `leading-*`.** Responsive
  variants are emitted after base utilities, so `sm:text-base` silently overrode a sibling
  `leading-7`, and `lg:text-5xl` overrode `leading-[1.55]` — CSS order decides, so class order in the
  attribute cannot fix it. **Use arbitrary sizes (`text-[16px]`, `sm:text-[36px]`), which set
  font-size only**, or the `text-base/8` slash syntax. This is why the Myanmar blog body rendered at
  16px/24px and its h1 at 48px/48px (ratio 1.0).
- **Myanmar typography.** `tailwind.config.js`'s `sans` stack is `['Inter', 'Noto Sans Myanmar',
  'Padauk', 'Pyidaungsu', …]` — fallback is **per character**, so Latin stays Inter and Myanmar drops
  through to the webfont, which Google serves with a Myanmar-only `unicode-range` (free on English
  pages). Line-height cannot be per character, so blocks are detected with `hasMyanmar()` and opted
  into `leading-[1.95]`; headings need ≥1.5 and **no negative tracking**. **`Pyidaungsu` is NOT
  bundled** — an old comment claimed it was.
- **Blog bodies are plain text, not HTML** — one line per paragraph, no markup. `v-html` collapses
  those newlines, so render `post.content_html` (`BlogPost::getContentHtmlAttribute()`, appended only
  in `BlogController::show` to avoid doubling list payloads), never `post.content`.
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

## Verification

**→ Read `.workbuddy-ai/memory/TESTING.md` before writing or running any Playwright suite.** It holds
the toolbox paths, the harness lessons that cost real time (stale `data-page`, Inertia login polling,
SVG `innerText`, `ERR_NETWORK_IO_SUSPENDED`, geometry-not-presence) and the suite inventory.

Two rules that matter even when you are not testing:

- **Bash kills commands at 120s** — long suites need `run_in_background: true`.
- **Re-run the regression suites after any layout-wide nav change.**
