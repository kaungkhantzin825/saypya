# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Laravel 10 LMS ("Sanpya Online Academy" / "Sanpya Academy") — students browse/enroll in courses, lecturers manage their own course content and exams, admins manage the whole platform. Blade + Bootstrap/AdminLTE 3 admin panel, Alpine.js + Tailwind on the public site, Vite for asset bundling. Deployed at sanpyalearning.com on a Linux server (`/var/www/html/sanpyalearning`), developed locally on Windows at `d:\education\LearningWeb`.

## Commands

- `npm run dev` — Vite dev server (hot reload for `resources/css`/`resources/js`)
- `npm run build` — production asset build; **must be run after any change under `resources/css` or `resources/js`** (or after adding a new Vite input) because `public/build/` is gitignored and not committed — it only exists from a local/server build
- `php artisan serve` — local app server (default `http://127.0.0.1:8000`)
- `php artisan migrate` — run migrations
- `php artisan test` or `vendor/bin/phpunit` — run test suite (Feature/Unit dirs under `tests/`; currently only example tests, no real coverage)
- `vendor/bin/phpunit --filter TestName` — run a single test
- `php artisan tinker` — inspect/mutate data directly (commonly used here for one-off admin fixes, e.g. flipping `is_super_admin`)

### Deploying to production

`public/build/` is not committed to git. After pulling changes on the server, asset changes require: `git pull`, then `npm install && npm run build`. Vite inputs are declared in `vite.config.js` (`laravel-vite-plugin`) — any new top-level CSS/JS file referenced via `@vite([...])` in a Blade view must be added to that `input` array first or the build will throw "Unable to locate file in Vite manifest."

## Architecture

### Roles and access control

Three roles live on `users.role`: `student`, `lecturer`, `admin`. Route groups are gated with the `role:<name>` middleware alias (`App\Http\Middleware\RoleMiddleware`, registered in `bootstrap/app.php`), which does a strict `$request->user()->role !== $role` check (no hierarchy — admin does not automatically pass a `role:lecturer` gate).

On top of `role`, `users.is_super_admin` (boolean) is a separate, narrower permission used only for the most destructive admin action (permanently deleting a user account — see `AdminController::usersDestroy`). Regular admins can manage/disable users but cannot delete them; only super admins can. There is no UI to grant this — it's set directly via `php artisan tinker` (`User::where('email', ...)->update(['is_super_admin' => true])`).

Separately, `users.status` (`pending` / `active` / `inactive`) gates login itself, independent of role — see auth flow below.

### Three separate UI surfaces

- **Public site** (`resources/views/pages/*`, `layouts/app.blade.php`) — Tailwind/Alpine.js, course catalog, checkout, learning player.
- **Admin panel** (`resources/views/admin/*`, `layouts/admin.blade.php` / `layouts/adminlte.blade.php`) — AdminLTE 3, routes under `admin.*` name prefix / `/admin` path prefix, all behind `role:admin`.
- **Lecturer/instructor panel** (`resources/views/instructor/*`, `layouts/lecturer.blade.php`) — routes under `instructor.*` / `/instructor`, behind `role:lecturer`. Its exam-management routes largely mirror the admin exam routes but are scoped to the lecturer's own courses (`InstructorController` vs `AdminController`).

`AdminController` is a single large controller handling users, categories, courses (+ sections/lessons), enrollments, reviews, exams, contact messages, blog, and site settings — it is intentionally not split into per-resource controllers. When adding admin features, the existing convention is to add methods to `AdminController` and routes to the `admin.` group in `routes/web.php`, not to create a new controller.

### Domain model

`Course` → `Section` (ordered) → `Lesson` (`hasManyThrough` for `Course::lessons()`). `Enrollment` is the join between `User` and `Course` with `payment_status` (`pending`/`completed`/`failed`/`refunded`) and `progress_percentage`; `Enrollment::updateProgress()` recomputes progress from `LessonProgress` rows and stamps `completed_at` at 100%. `Exam` belongs to a `Course` and only becomes visible to a student once: the exam `is_published`, it has ≥1 `ExamQuestion`, and the student's enrollment has `progress_percentage` at 100% with `payment_status = completed` (see `EXAM_TROUBLESHOOTING_GUIDE.md` for the full checklist and DB queries used to debug this).

**Foreign key deletion behavior is intentionally asymmetric** (see `CASCADE_DELETE_FIX.md`): `Category`→`Course` and `User`(instructor)→`Course` are `onDelete('restrict')` — deleting a category/instructor with existing courses fails loudly rather than silently wiping courses. Downstream of a course, deletion *does* cascade: `Course` → `Section` → `Lesson`, and `Course` → `Enrollment`/`Review`/`Exam`. Don't change a `restrict` back to `cascade` without confirming with the user — that was a deliberate data-loss fix.

`SiteSetting` is a simple key/value store (`SiteSetting::get($key, $default)` / `::set(...)`) used for editable About-page stats and site name/description, cached for 1 hour and invalidated on write.

### Auth / registration flow

Registration and password reset use emailed verification **links** (not OTP codes despite the `Otp` model name and `otps` table) — a 40-char single-use token valid 24h, generated/verified via `App\Models\Otp`. New self-registered users are created with `status = pending` and cannot log in until an admin approves them (flips status to `active`) from the admin Users page; rejecting sets `inactive`. See `OTP_FLOW_GUIDE.md` for the full route list and file map if working on this flow.

### Localization

`SetLocale` middleware (global) reads the locale from session (`en`/`my`, default `en`) and calls `App::setLocale()`; `lang/en` and `lang/my` hold translations. The public-facing site has first-class Myanmar-language support (Pyidaungsu/Noto Sans Myanmar fonts, `myanmar-text` CSS class) — keep this in mind when touching public-facing auth/course views.
