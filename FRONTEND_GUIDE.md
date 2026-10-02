# Frontend Guide — Inertia + Vue 3 (public site, student area & admin/instructor panels)

Two surfaces coexist, and **migration of the admin/instructor panels is in progress**:

| Surface | Status | Where |
|---|---|---|
| Public site + student area | **Fully migrated** | `resources/js/pages` (34 pages) |
| Admin panel | **Partially migrated** — dashboard, users, courses | `resources/js/pages/Admin` |
| Instructor panel | **Not started** — still Blade | `resources/views/instructor` |

Everything not yet listed as migrated is still Blade/AdminLTE. See
[Admin & instructor panels](#admin--instructor-panels-in-progress) for the pattern to follow.

## Stack

| Piece | Choice |
|---|---|
| Glue | `inertiajs/inertia-laravel` v2 + `@inertiajs/vue3` v2 |
| Framework | Vue 3.5, `<script setup lang="ts">` |
| Language | TypeScript (strict) |
| Styling | Tailwind CSS v3 + CSS variables (shadcn-vue token set) |
| Primitives | `reka-ui` (accessible headless components) |
| Icons | `lucide-vue-next` |
| Build | Vite 4 + `@vitejs/plugin-vue` |

## Entry points

```
resources/js/
├── inertia.ts              # createInertiaApp() — the only Vite input for this surface
├── pages/                  # one .vue per Inertia page, path maps to the page name
├── layouts/                # PublicLayout, StudentLayout, AuthLayout
├── components/
│   ├── ui/                 # design-system primitives (+ index.ts barrel)
│   └── site/               # composed, domain-aware components
├── composables/            # useApp (shared/auth/formatting), useToast
├── lib/                    # utils (cn, formatters), routes (URL builders)
└── types/                  # index.ts (domain), ui.ts (component props)
```

- Root Blade template: `resources/views/app.blade.php`
- Shared props: `app/Http/Middleware/HandleInertiaRequests.php`

## Page conventions

```vue
<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Course } from '@/types';

// Persistent layout. Omit for a page with no chrome (e.g. the exam runner).
defineOptions({ layout: PublicLayout });

const props = defineProps<{ courses: Paginated<Course> }>();
</script>

<template>…</template>
```

- Page name = path under `resources/js/pages`, e.g. `Courses/Show.vue` → `Inertia::render('Courses/Show')`.
- **Always** import from the `@/` alias (maps to `resources/js`).
- Use `useForm()` from `@inertiajs/vue3` for every form — it handles CSRF, validation errors and
  the submitting state. Read errors with `form.errors.field`.
- Never `window.location` for internal links: use `<Link>` or `router.visit()`.
- Links to the **Blade-only** panels (`/admin/...`, `/instructor/...`) must be real page loads:
  use `<a href>` or `<Button href external>` / `MenuItem.external`.

## Layouts

| Layout | Use for |
|---|---|
| `PublicLayout` | Marketing + catalog: home, courses, categories, blog, static pages, checkout |
| `StudentLayout` | Authenticated learner area: dashboard, my courses, wishlist, exams, profile |
| `AuthLayout` | Split-screen login/register/password pages |

All three already render `<Toaster />`, which automatically turns Laravel session flash
messages (`success` / `error` / `warning` / `info`) into toasts. Don't add your own flash UI.

## UI primitives — `@/components/ui`

Import from the barrel: `import { Button, Card, Badge } from '@/components/ui'`.

| Component | Key props |
|---|---|
| `Button` | `variant` (`default`/`brand`/`destructive`/`outline`/`secondary`/`ghost`/`link`), `size` (`sm`/`default`/`lg`/`icon`/`icon-sm`), `href`, `external`, `loading`, `block`, `type` |
| `Card` | `padded`, `interactive`; slots `header`, `title`, `description`, default, `footer` |
| `Badge` | `variant` (`default`/`brand`/`secondary`/`success`/`warning`/`destructive`/`info`/`outline`/`muted`) |
| `Input` | `v-model`, `type`, `placeholder`, `icon`, `invalid` |
| `Textarea` | `v-model`, `rows`, `invalid` |
| `Label` | `for`, `required` |
| `Checkbox` | `v-model` (boolean), `id` |
| `RadioGroup` | `v-model` (string), `options: RadioOption[]` |
| `Select` | `v-model` (string), `options: SelectOption[]`, `placeholder` |
| `Dialog` | `v-model:open`, `title`, `description`, `size` (`sm`/`md`/`lg`/`xl`/`full`); slots `trigger`, default, `footer` |
| `Sheet` | `v-model:open`, `side` (`left`/`right`/`bottom`), `title` |
| `DropdownMenu` | `items: MenuItem[]`, `align`, `side` |
| `Tabs` | `v-model` (string), `tabs: TabItem[]`, `variant` (`default`/`underline`); one slot per tab value |
| `Accordion` | `items: AccordionEntry[]`, `type`, `defaultValue`; scoped slots `title`/`content` with `{ item }` |
| `Avatar` | `src`, `name`, `size` (`xs`…`2xl`) |
| `Progress` | `value`, `autoTone`, `size` |
| `Alert` | `variant` (`info`/`success`/`warning`/`destructive`), `title` |
| `Pagination` | `links` (Laravel paginator links), `from`, `to`, `total` |
| `EmptyState` | `icon`, `title`, `description`; default slot for the CTA |
| `Skeleton`, `Separator`, `Toaster` | — |

> `Select` and `RadioGroup` values must be **strings**. Reka reserves `''`, so use a real
> sentinel like `'all'` rather than an empty string.

## Site components — `@/components/site`

| Component | Notes |
|---|---|
| `CourseCard` | props `course: Course`, `progress?: number`. Renders thumbnail, badges, rating, price/progress, wishlist button |
| `StarRating` | `rating`, `count`, `size`, `showValue`, `interactive` (emits `update:rating`) |
| `PriceTag` | `price`, `discountPrice`, `size` — handles discount % and locale formatting |
| `SectionHeading` | `title`, `subtitle`, `eyebrow`, `align`; `action` slot |
| `Logo` | `compact`, `variant` (`default`/`light`) |
| `LocaleSwitcher`, `UserMenu` | already wired into the layouts |

## Helpers

```ts
import { routes } from '@/lib/routes';           // routes.course(slug), routes.checkout(slug), …
import { cn, formatMMK, formatDuration, clampPercent, timeAgo } from '@/lib/utils';
import { useShared, useAuth, useFormatting } from '@/composables/useApp';
import { useToast } from '@/composables/useToast';
```

`useFormatting()` returns `{ locale, isMyanmar, myanmarClass, formatPrice }` — `formatPrice`
automatically switches to Myanmar numerals when the locale is `my`. Prefer it over calling
`formatMMK` directly.

## Design tokens

Semantic colours live in `resources/css/inertia.css` and are wired into `tailwind.config.js`:

`bg-background`, `text-foreground`, `bg-card`, `bg-muted`, `text-muted-foreground`,
`bg-primary`, `bg-secondary`, `bg-accent`, `bg-destructive`, `bg-success`, `bg-warning`,
`border-border`, `border-input`, `ring-ring`.

The Sanpya teal scale is available as `brand-50` … `brand-950` (`brand-600` = `#0d9488`).

Dark mode is class-based (`darkMode: ['class']`) — the token set is already defined for it.
The legacy `primary-50 … primary-900` blue scale is still present **only** because the
un-migrated Blade views use it. Don't use `primary-<number>` in new Vue code.

## Admin & instructor panels (in progress)

The panels are being migrated off AdminLTE onto the same Inertia/Vue stack. The admin panel is
**fully migrated except** `users/show` and `users/create-lecturer`; the instructor panel is still
untouched and follows the same pattern when its turn comes.

### What exists

| File | Role |
|---|---|
| `resources/js/layouts/AdminLayout.vue` | Shared, **role-aware** layout for both panels |
| `resources/js/components/ui/DataTable.vue` | Generic table: sorting, selection, loading, slots |
| `resources/js/components/ui/ImageUpload.vue` | Drag/drop, preview, renders an `error` prop |
| `resources/js/components/charts/LineChart.vue` | Dependency-free SVG line/area chart |
| `resources/js/components/charts/DoughnutChart.vue` | Dependency-free SVG doughnut + legend |
| `resources/js/pages/Admin/**` | Dashboard, Users, Courses, Categories, HeroSlides, Enrollments, Reviews, ContactMessages, Blog, Exams, Reports, Settings |
| `resources/js/pages/Admin/Users/Form.vue` | **One component for create *and* edit** — the pattern every `Form.vue` follows |

Charts are hand-rolled SVG rather than Chart.js: this project deliberately avoids runtime
dependencies (`resources/js/lib/routes.ts` is hand-written rather than Ziggy, for the same reason).
The admin panel uses `AdminController` for all of it; there are no per-resource controllers.

`AdminLayout` picks its nav from `isAdmin`, so `InstructorController` can reuse it as-is.

### Partial migrations: mixed links

A page can be Inertia while the pages it links to are still Blade. Those links **must** be
plain anchors — `Button` renders a `<Link>` unless you pass `external`:

```vue
<Button :href="routes.admin.courseEdit(row.id)" external>Edit</Button>
```

`routes.ts` groups the admin URLs and marks which ones are still Blade. When you migrate a
page, remove `external: true` from its `AdminLayout` nav entry and from every link that
targets it — otherwise you get a needless full page reload (harmless) or, for a `<Link>`
pointing at a Blade route, a thrown error.

### Hard delete vs soft delete — get the copy right

`User` uses `SoftDeletes`; `Course` does **not**. So `usersDestroy` only sets `deleted_at`
(recoverable, and the old Blade panel wrongly said "Permanently delete"), while
`coursesDestroy` really does delete the row **and unlinks the thumbnail from disk**. Each
confirmation dialog must match its own model's behaviour.

### The rules that bite

**1. `DataTable`'s generic is `T extends object`, not `Record<string, unknown>`.**
A `Record<…>` constraint demands an index signature, which real interfaces
(`User`, `Enrollment`) do not have — every call site fails to type-check. Dynamic column
access goes through a single `valueOf()` cast inside the component instead.

**2. `min-w-0` is load-bearing on any ancestor of a `DataTable`.**
The table has `min-w-[42rem]`. Grid and flex items default to `min-width: auto`, so that
42rem minimum propagates up through the `Card` and stretches the **whole page** to ~740px
on a 390px viewport. Put `min-w-0` on the grid item / card wrapper. Symptom: a horizontal
scrollbar on mobile and `document.documentElement.scrollWidth > innerWidth`.

**3. `Button` with an `href` renders a `<Link>`, so its ARIA role is `link`.**
`getByRole('button', …)` will not find row actions that navigate. Only `@click`-driven
buttons ("Disable account", "Delete permanently") are real `<button>`s.

**4. A debounced search input swallows clicks.**
`Index.vue` debounces search by 350 ms and then issues an Inertia visit with
`preserveState`. Clicking a row action before that visit settles gets the click discarded.
Wait for the `search` query param *and* network idle before interacting.

**5. A `DataTable` needs ≥ 42rem of container, so don't split two of them across a grid.**
`min-w-0` stops the 42rem minimum stretching the *page*, but the table still overflows its own
card: the trailing columns disappear behind an `overflow-x-auto` scrollbar that has no visible
affordance on desktop, so a revenue column silently vanishes. Two tables side by side need
~1368px of content *plus* the sidebar — that is beyond `xl:` and `2xl:`. Keep wide tables
full-width and stacked (see `Reports.vue`'s leaderboards). Check with
`el.scrollWidth > el.clientWidth + 1` on every `.overflow-x-auto`, at 1440px.

**6. Budget width for a legend, not just the graphic, inside a narrow card.**
`DoughnutChart` in a `lg:col-span-1` card has ~300px of inner width. Placing the legend beside
the 144px ring leaves ~40px for the label while "Students" needs ~59px, so every label ellipsises
even though the DOM text is complete (asserting on text content will not catch it — measure
`scrollWidth` vs `clientWidth`). Stack the legend under the ring instead.

### Forms: one component, `_method` spoofing

`Users/Form.vue` serves both create and edit — the controller passes `user: null` or the
record. A file upload cannot be sent as a real `PUT`, so edit submits via
`form.transform((d) => ({ ...d, _method: 'put' })).post(...)`.

### Controller conventions

Follow the existing house style in `AdminController`: add methods to that one class rather
than creating per-resource controllers, and return `Inertia::render()` with `title` /
`description` props for the layout header.

**Whitelist anything interpolated into `orderBy()`.** `usersIndex()` accepts `sort` +
`direction` and validates `sort` against an explicit array:

```php
$sortable = ['id', 'name', 'email', 'role', 'status', 'created_at'];
$sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
$direction = $request->direction === 'asc' ? 'asc' : 'desc';
$users = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();
```

### Verification

`.workbuddy-ai/admin-pilot-check.cjs` (28 checks) drives the dashboard + users pages end to
end — component identity, SPA nav, server-side sort, filters, create → edit → toggle →
delete, plus mobile overflow and a page-error budget. It creates and deletes its own user;
aborted runs may leave rows, cleaned with
`DELETE FROM users WHERE email LIKE 'pilot.user.%@example.com';`.

`.workbuddy-ai/courses-check.cjs` (18 checks) covers the courses list: filters, sort,
approve, feature toggle (both directions) and delete. It inserts one throwaway draft course
plus a throwaway thumbnail file, then removes both — **no real course is touched**. The
fixture is cleaned up in a `finally` block even if the run fails.

```bash
SANPYA_SEED_PASSWORD=password node .workbuddy-ai/admin-pilot-check.cjs
SANPYA_SEED_PASSWORD=password node .workbuddy-ai/courses-check.cjs
```

`.workbuddy-ai/logo-check.cjs` (15 checks) covers the public site and all three role logins —
run it after any change to shared UI primitives.

### Deleting a migrated Blade view

A Blade view is safe to delete only once no `view('admin.…')` call references it. Verify,
back it up, then remove:

```bash
grep -rhoE "view\('admin\.[a-z0-9._-]+'" app/ | sort -u   # the live list
cp resources/views/admin/x.blade.php .workbuddy-ai/backup/blade-views-<date>/…
rm resources/views/admin/x.blade.php
```

Note that `route('admin.x.index')` references in *other* Blade files are fine and should stay —
they generate a URL, which now resolves to the Inertia page via a normal full page load.

**Never delete Blade wholesale.** `resources/views/app.blade.php` is the Inertia root — without
it every Vue page 404s. The instructor panel and ~27 admin pages still render Blade.

## Adding a page

1. Create `resources/js/pages/Path/Name.vue` with `defineOptions({ layout: … })`.
2. Return it from the controller: `return Inertia::render('Path/Name', [...props]);`
3. Add a URL builder to `resources/js/lib/routes.ts` if the page needs linking.

## Server-side contract — read before touching a controller

### Layout props must come from the controller

Inertia renders a page's layout as `h(layout, { ...page.props })`, so a page **cannot** pass
props into its own layout at render time. `StudentLayout` / `AuthLayout` therefore read
`title` / `subtitle` / `description` out of the page's own props — every controller that
returns a page using one of those layouts must send them explicitly.

### Accessors need `$appends`

Eloquent accessors are **not** serialised to JSON by default. Every value the Vue layer
reads that is computed rather than stored must be listed in the model's `$appends`:

| Model | Appended |
|---|---|
| `Course` | `thumbnail_url`, `preview_video_url`, `current_price`, `discount_percentage`, `average_rating`, `total_reviews`, `total_students`, `total_lessons`, `total_duration` |
| `Lesson` | `video_url_full`, `youtube_embed_url`, `formatted_duration` |
| `HeroSlide` | `image_url` |
| `BlogPost` | `image_url`, `featured_image_url`, `reading_time` |

`Course`'s aggregate accessors prefer an already-loaded relation (or a `lessons_count`
`withCount` alias) so `$appends` does not introduce N+1 queries. **Any new listing query
should keep `withCount('lessons')` and the relevant `with(...)` eager loads** — dropping
them silently turns one query into N.

### Field names that differ from the obvious

| Vue expects | DB column / reality |
|---|---|
| `Lesson.is_preview` | `is_preview` (not `is_free`) |
| `HeroSlide.button_text` / `button_link` | not `cta_text` / `cta_url` |
| `BlogPost.image_url` | accessor alias of `featured_image_url` |
| `Comment.comment`, `Discussion.content` | not `body` |
| `Course.status` | `'draft' \| 'published'` — there is **no** `courses.is_published` |
| `Exam.is_published` | real column (exams *do* have it) |
| `ExamAttempt.total_points` | snapshot taken at attempt creation |

### Exam answer encoding — easy to get wrong

`ExamQuestion::isCorrect()` does a strict `$answer === $this->correct_answer`:

- `multiple_choice` → `correct_answer` holds the **option index as a string** (`"0"`, `"1"`).
  The radio value must be `String(index)`, **not** the option text.
- `true_false` → lowercase `'true'` / `'false'`.
- `essay` → returns `null`, needs manual grading.

`Exams/Take.vue` (`optionsFor()`) and `Exams/Result.vue` (`displayAnswer()`) both encode this.

### Write endpoints must branch on the Inertia header

Inertia sends `X-Requested-With: XMLHttpRequest`, so `request()->ajax()` is **true** for
Inertia visits. Any endpoint that returns JSON for AJAX callers must check
`request()->header('X-Inertia')` *first* and return `back()->with(...)` for Inertia —
otherwise the JSON body lands in the page. See `CourseController::toggleWishlist`,
`WishlistController::toggle`, `LessonController::respond`.

Plain `back()->with('success', …)` needs no change: `HandleInertiaRequests` shares
`flash`, and `Toaster.vue` turns it into a toast.

### Still Blade (not migrated)

`DiscussionController::index` / `show` still reference `courses.discussions.index` /
`courses.discussions.show`, but those templates were already missing before cleanup.
There is no Vue discussion page. Direct access can fail with a missing-view error;
migrate these methods and implement their pages before linking to them with Inertia.
`InstructorController` also references pre-existing missing `instructor.exams.grade`
and `instructor.exams.results` templates; these remain separate follow-up work.

`InstructorController` (the AdminLTE panel) is intentionally still fully on Blade, as are
`AdminController`'s `users/show` and `users/create-lecturer` (see below).
`app.css`, `adminlte.min.css` and `app.js` stay in the Vite `input` array for them.

On 2026-10-01, 42 unused Blade templates were removed after backup to
`.workbuddy-ai/backup/blade-views-2026-10-01/` (with `MANIFEST.sha256`); the admin panel's
migrated pages followed on 2026-10-02 into `blade-views-2026-10-02/`.
**18 referenced templates remain: 2 admin, 12 instructor, 3 panel layouts, and
`resources/views/app.blade.php`.** Keep this Inertia root; it mounts the Vue application
and is not the old public UI.

### Never let an Inertia request land on a Blade route

The Inertia client cannot render a Blade response, and it does not follow a *second*
redirect. `AuthenticatedSessionController::store()` therefore sends lecturers and admins
to their panel with `Inertia::location(route('admin.dashboard'))` rather than
`redirect()->intended(route('dashboard'))` — the latter bounces them via `/dashboard`,
which is a redirect the client silently drops, leaving them stuck on the login screen.
Use `Inertia::location()` for any redirect that ends at a still-Blade route.

### Build asset URLs with `asset()`

`Course`, `HeroSlide`, `SiteSetting` and `User` used to build storage URLs from
`config('app.url')`, which pinned every image to `APP_URL` and broke previews served on any
other port. They now use `asset()`, which is request-relative. Do the same in new code.

### Verifying a change

There is no test coverage, so verify by hand:

```bash
npm run build                       # public/build is gitignored — always rebuild
php artisan route:list              # catches routes pointing at missing methods
php -l app/Http/Controllers/X.php   # cheap syntax check
```

Server-side props can be inspected without a browser by grepping the HTML-escaped
`data-page` attribute: `grep -o '&quot;component&quot;:&quot;[^&]*' page.html`.

> **`data-page` is a cold-load snapshot only.** `@inertiajs/core` *reads*
> `el.dataset.page` at boot and **never writes it back**, so after any SPA visit the
> attribute still describes the *first* page of the session. Assert component identity
> only after a full `page.goto`; for SPA navigation, assert on rendered DOM instead.
> (Verified against `@inertiajs/core` — the only `data-page` reads are in its boot path.)

To confirm the client actually mounts, render in headless Chrome (installed at
`C:\Program Files\Google\Chrome\Application\chrome.exe`):

```bash
chrome --headless=new --disable-gpu --no-sandbox --virtual-time-budget=8000 \
  --dump-dom "http://127.0.0.1:8899/courses" | grep -c '<header'
```

Authenticated pages need the session cookie, so drive Chrome over CDP
(`--remote-debugging-port`) and `Network.setCookie` the `sanpya_online_academy_session`
value. Node 22 has a global `WebSocket`, so no npm packages are required.

**Headless gotcha:** do **not** call `Emulation.setDeviceMetricsOverride` before the first
paint of a cookie-bearing session — the page loads but `#app` stays empty with no JS error.
`Page.captureScreenshot` with `captureBeyondViewport: true` already captures the full page,
so the override is unnecessary anyway.

Two more Windows/Git-Bash traps: route arguments starting with `/` get rewritten into
Windows paths (set `MSYS_NO_PATHCONV=1`), and Vite's default clean step can trip a
safe-delete guard. Use the Vite JS API with `build({build:{emptyOutDir:false}})` for a
non-destructive rebuild; it retains old hashed assets while updating the manifest.
Do not split deletions to bypass a guard.
