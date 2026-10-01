# LearningWeb — Project Memory

Laravel 10 LMS, "Sanpya Online Academy" (sanpyalearning.com). **Two frontends coexist:**
the public site + student area are Laravel + Vue 3 + Inertia v2 + TypeScript + Tailwind
(shadcn-style); the admin and instructor panels are still Blade + AdminLTE 3 + Alpine.
Vite. Dev on Windows at `d:\education\LearningWeb`, prod on Linux at
`/var/www/html/sanpyalearning`. **Read `CLAUDE.md` (backend) and `FRONTEND_GUIDE.md`
(Inertia/Vue) first** — both are canonical and kept up to date.

## Layout cheat-sheet

- `app/Http/Controllers/AdminController.php` — one big controller (~1250 lines) covering
  users, categories, courses, sections/lessons, enrollments, reviews, exams, contact
  messages, blog, hero slides, reports, settings. **Convention: add new admin features as
  methods here + routes in the `admin.` group — do NOT create per-resource controllers.**
- `InstructorController.php` (~730 lines) mirrors the admin exam/course-content routes but
  scoped to the lecturer's own courses (enforced with `$this->authorize('update', $course)`
  against `CoursePolicy`).
- Public site: `HomeController`, `CourseController`, `CategoryController`, `BlogController`,
  `ContactController`, `StudentController`, `ExamController`, `LessonController`,
  `ReviewController`, `CommentController`, `DiscussionController`, `WishlistController`.
- API (`routes/api.php`, Sanctum): `Api/AuthApiController`, `Api/StudentApiController`.
- Helpers autoloaded via composer `files`: `CurrencyHelper` (MMK, Myanmar numerals),
  `LanguageHelper`.
- 3 UI surfaces: `resources/js/pages` (Vue public/student), `resources/views/admin`
  (AdminLTE), `resources/views/instructor` (lecturer panel).
- Public layouts: `resources/js/layouts`, with `resources/views/app.blade.php` as the
  required Inertia root. Blade panel layouts: `layouts/adminlte`, `layouts/admin`,
  `layouts/lecturer`. The old `layouts/app.blade.php` was removed.

## Frontend — Inertia + Vue 3 (public + student only)

Entry `resources/js/inertia.ts` → `resources/views/app.blade.php`; tokens in
`resources/css/inertia.css` (**not** `app.css`, which the Blade panels still use).
`resources/js/{pages,layouts,components/{ui,site},lib,types,composables}`.
34 pages. `@` aliases to `resources/js`. Details + component props: `FRONTEND_GUIDE.md`.

The four rules that break things most often:

1. **`$appends` is mandatory** for any accessor the Vue layer reads (`thumbnail_url`,
   `current_price`, `image_url`, …) — Eloquent does not serialise accessors by default.
   Already added on Course/Lesson/HeroSlide/BlogPost.
2. **Inertia sends `X-Requested-With: XMLHttpRequest`**, so `request()->ajax()` is `true`
   for Inertia visits. JSON-returning endpoints must check `request()->header('X-Inertia')`
   first. Plain `back()->with('success', …)` works as-is (flash → `Toaster.vue`).
3. **Layouts receive `page.props`**, so `title`/`subtitle` must be sent by the controller.
4. **Exam answers**: MCQ `correct_answer` = option **index as string** (`"0"`), true/false =
   lowercase `'true'`/`'false'`. `ExamQuestion::isCorrect()` compares strictly.

Still Blade on purpose: `AdminController` and `InstructorController`.
`DiscussionController::index`/`show` reference missing `courses.discussions.index`/`show`
templates; these were missing before cleanup. Migrate those methods and implement pages
before adding Inertia links. Instructor exam `grade`/`results` templates are also
pre-existing missing views and remain unresolved.

## ⚠️ Two copies of this project exist on disk

| | `D:\education\LearningWeb` (**the workspace / migrated**) | `D:\Education Web\LearningWeb` (older) |
|---|---|---|
| Last commit | `41709c8` 2026-08-09 | `89d41e9` 2026-06-24 |
| Frontend | Inertia + Vue 3 (this work) | **still Blade** |
| `public/build/assets` | ~104 files | 2 files |
| `DB_DATABASE` | `Learningweb` | `Learningweb` — **same database** |

Folder names differ only by a space and case (`education` vs `Education Web`), and they share
the database, so both render the same content and are very easy to confuse. If the user
reports "it's still Blade", check which directory their `php artisan serve` is running from
first. Fingerprint: `curl -s <url>/build/manifest.json | wc -c` → ~53 KB = migrated copy,
~255 bytes = old copy. The migrated copy also serves an Inertia component
(`grep -o '&quot;component&quot;:&quot;[^&]*' page.html`).

## Hard-won rules

- **`public/build/` is gitignored.** After ANY change under `resources/css` or
  `resources/js` you must run `npm run build`. Adding a new Vite entry requires adding it to
  the `input` array in `vite.config.js` first, or Blade `@vite([...])` throws
  "Unable to locate file in Vite manifest." Server deploy: `git pull && npm install && npm run build`.
- **Vite's default clean step can hit `SAFE_DELETE_BULK_CONFIRM_REQUIRED`.** Do not
  work around deletion protection by splitting deletes. Use the Vite JS API with
  `build({build:{emptyOutDir:false}})` to rebuild without removing existing files;
  this deliberately retains old hashed assets and updates the manifest.
- **FK deletion is intentionally asymmetric** (`CASCADE_DELETE_FIX.md`). `Category→Course`
  and `User(instructor)→Course` are `onDelete('restrict')` — do NOT change back to cascade
  without asking; it was a deliberate data-loss fix. Below a course it cascades normally.
- **Role gates have no hierarchy.** `role:admin` does not satisfy `role:lecturer`. Middleware
  alias lives in `app/Http/Kernel.php` (Laravel 10), not `bootstrap/app.php`.
- **`CoursePolicy` works by convention** (empty `AuthServiceProvider::$policies`) and is only
  used in `InstructorController`; the admin panel does its own checks.
- **Exam visibility needs ALL of:** exam `is_published`, ≥1 `ExamQuestion`, enrollment
  `payment_status = completed`, and `progress_percentage >= 100`. Enforced in
  `resources/js/pages/Courses/Learn.vue`; domain checklist in `EXAM_TROUBLESHOOTING_GUIDE.md`.
- **Registration auto-activates** (`status = 'active'`, auto-login after email verification).
  Older docs saying "pending + admin approval" are stale. Global toggle:
  `registration_enabled` setting. `users.status` still gates login for `pending`/`inactive`.
- **Two key/value stores exist:** `Setting` (simple, `settings` table, used for feature
  flags like `registration_enabled`) and `SiteSetting` (`site_settings` table, typed +
  grouped + labelled, used for editable About-page stats / site name). Both cache 1h under
  `setting_{key}` and forget on write. Pick `SiteSetting` for anything an admin edits in the
  Settings page.
- **Deployment webhook** at `POST /github-webhook` verifies `X-Hub-Signature-256` against
  `GITHUB_WEBHOOK_SECRET` then fires `/var/www/html/sanpyalearning/deploy.sh`.
- Locale: `SetLocale` (global, in the `web` group) reads session `en`/`my`. Public/auth views
  have first-class Myanmar support (Pyidaungsu/Noto Sans Myanmar, `myanmar-text` class) —
  error strings in auth code are hardcoded Myanmar, not `lang/` keys.

## Gotchas noticed

- `tests/` has only the stock example tests — there is effectively no test coverage, so
  verify changes by hand.
- **`courses` has no `is_published` column** — it is `status = 'published'` (`Course::scopePublished`).
  Exams *do* have `is_published`. `EnrollmentController::store` still reads
  `$course->is_published` but that controller is dead code (routes use `CourseController::enroll`).
- `php artisan tinker` is the standard escape hatch for one-off admin data fixes
  (e.g. flipping `is_super_admin`).
- `routes/web.php` has a dev-only `GET /api/test-image-url` debug endpoint.
- Blade cleanup complete (2026-10-01): 42 unused templates removed, including `home_old`
  and `test-lang`; 48 referenced templates remain. Backups and SHA-256 manifest are in
  `.workbuddy-ai/backup/blade-views-2026-10-01/`. Do not delete the Inertia root or panel
  templates without replacing their dependencies first.

## Handy verification tricks

Chrome is at `/c/Program Files/Google/Chrome/Application/chrome.exe`.
Playwright is installed in the managed Node workspace:
`C:/Users/Ko Kaung/.workbuddy-ai/binaries/node/workspace/node_modules/playwright`.
Use managed Node and `chromium.launch({channel:'chrome',headless:true})`.
Fallback checks:

- Server-side props without a browser: `grep -o '&quot;component&quot;:&quot;[^&]*' page.html`
  on the HTML-escaped `data-page` attribute.
- Real browser render: `chrome --headless=new --dump-dom <url>` for guest pages; for
  authenticated pages drive Chrome over CDP (`--remote-debugging-port`) and
  `Network.setCookie` the `sanpya_online_academy_session` value. Node 22 has a global
  `WebSocket`, so no npm packages are needed.
- Git Bash mangles `/route` args into Windows paths — set `MSYS_NO_PATHCONV=1`.
- Respect bulk-delete guards; prefer non-destructive build options or obtain approval.

