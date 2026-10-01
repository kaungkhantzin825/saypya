# Frontend Guide — Inertia + Vue 3 (public site & student area)

This document describes the **new** frontend. The **admin** (`resources/views/admin`) and
**instructor** (`resources/views/instructor`) panels are still Blade/AdminLTE and are **not**
part of this system yet.

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

`AdminController` and `InstructorController` (the AdminLTE panels) are intentionally left
on Blade. `app.css`, `adminlte.min.css` and `app.js` stay in the Vite `input` array for them.

On 2026-10-01, 42 unused Blade templates were removed after backup to
`.workbuddy-ai/backup/blade-views-2026-10-01/` (with `MANIFEST.sha256`).
48 referenced templates remain: 32 admin, 12 instructor, 3 panel layouts, and
`resources/views/app.blade.php`. Keep this Inertia root; it mounts the Vue application
and is not the old public UI.

### Verifying a change

There is no test coverage, so verify by hand:

```bash
npm run build                       # public/build is gitignored — always rebuild
php artisan route:list              # catches routes pointing at missing methods
php -l app/Http/Controllers/X.php   # cheap syntax check
```

Server-side props can be inspected without a browser by grepping the HTML-escaped
`data-page` attribute: `grep -o '&quot;component&quot;:&quot;[^&]*' page.html`.

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
