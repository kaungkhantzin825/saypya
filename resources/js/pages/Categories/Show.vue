<script setup lang="ts">
import { computed, reactive } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, GraduationCap, SlidersHorizontal, Users } from 'lucide-vue-next';
import { Badge, Button, EmptyState, Label, Pagination, Select } from '@/components/ui';
import CourseCard from '@/components/site/CourseCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';
import type { Category, Course, Paginated } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    category: Category;
    courses: Paginated<Course>;
    totalStudents: number;
    otherCategories: Category[];
}>();

/** Reka reserves `''` as a Select value, so an explicit sentinel means "no filter". */
const ALL = 'all';

const page = usePage();
const initialParams = new URLSearchParams(page.url.split('?')[1] ?? '');

const filters = reactive({
    level: initialParams.get('level') ?? ALL,
    sort: initialParams.get('sort') ?? 'newest',
});

const levelOptions = [
    { value: ALL, label: 'All levels' },
    { value: 'beginner', label: 'Beginner' },
    { value: 'intermediate', label: 'Intermediate' },
    { value: 'advanced', label: 'Advanced' },
    { value: 'all_levels', label: 'Suitable for all levels' },
];

const sortOptions = [
    { value: 'newest', label: 'Newest' },
    { value: 'oldest', label: 'Oldest' },
    { value: 'price_low', label: 'Price: low to high' },
    { value: 'price_high', label: 'Price: high to low' },
    { value: 'rating', label: 'Highest rated' },
    { value: 'popular', label: 'Most popular' },
];

const hasResults = computed(() => (props.courses.data?.length ?? 0) > 0);
const courseCount = computed(() => props.category.courses_count ?? props.courses.total ?? 0);

function apply() {
    const params = new URLSearchParams();

    if (filters.level !== ALL) params.set('level', filters.level);
    if (filters.sort && filters.sort !== 'newest') params.set('sort', filters.sort);

    const qs = params.toString();
    const url = qs ? `${routes.category(props.category.slug)}?${qs}` : routes.category(props.category.slug);

    router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true });
}

const statTiles = computed(() => [
    { icon: BookOpen, label: 'Courses', value: courseCount.value },
    { icon: Users, label: 'Students', value: props.totalStudents ?? 0 },
]);
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="border-b border-border bg-muted/40">
        <div class="page-container py-10 sm:py-14">
            <nav class="flex items-center gap-1.5 text-xs text-muted-foreground" aria-label="Breadcrumb">
                <Link :href="routes.home()" class="transition-colors hover:text-foreground">Home</Link>
                <ArrowRight class="size-3" />
                <Link :href="routes.categories()" class="transition-colors hover:text-foreground">
                    Categories
                </Link>
            </nav>

            <div class="mt-6 flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-2xl">
                    <Badge variant="brand" class="mb-3">
                        <GraduationCap class="size-3" />
                        Category
                    </Badge>
                    <h1 class="text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">
                        {{ category.name }}
                    </h1>
                    <p
                        v-if="category.description"
                        class="mt-3 text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base"
                    >
                        {{ category.description }}
                    </p>
                </div>

                <div class="flex gap-4">
                    <div
                        v-for="tile in statTiles"
                        :key="tile.label"
                        class="flex items-center gap-3 rounded-xl border border-border bg-card px-4 py-3 shadow-soft"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                        >
                            <component :is="tile.icon" class="size-5" />
                        </span>
                        <div>
                            <p class="text-lg font-extrabold leading-none tracking-tight">
                                {{ tile.value.toLocaleString('en-US') }}
                            </p>
                            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                {{ tile.label }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="page-container py-10 sm:py-14">
        <!-- ================================================== Filter bar -->
        <div class="flex flex-col gap-4 rounded-xl border border-border bg-card p-4 shadow-soft sm:flex-row sm:items-end">
            <span
                class="hidden size-10 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground sm:flex"
            >
                <SlidersHorizontal class="size-4" />
            </span>

            <div class="grid flex-1 gap-2 sm:max-w-48">
                <Label for="category-level">Level</Label>
                <Select id="category-level" v-model="filters.level" :options="levelOptions" />
            </div>

            <div class="grid flex-1 gap-2 sm:max-w-56">
                <Label for="category-sort">Sort by</Label>
                <Select id="category-sort" v-model="filters.sort" :options="sortOptions" />
            </div>

            <Button variant="brand" class="sm:mb-0" @click="apply">Apply</Button>
        </div>

        <!-- ===================================================== Results -->
        <div class="mt-8">
            <div v-if="hasResults" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <CourseCard v-for="course in courses.data" :key="course.id" :course="course" />
            </div>

            <EmptyState
                v-else
                :icon="BookOpen"
                title="No courses in this category yet"
                description="Nothing matches the filters you've chosen. Try a different level or sort order."
            >
                <Button :href="routes.courses()" variant="brand">
                    Browse all courses
                    <ArrowRight />
                </Button>
            </EmptyState>

            <Pagination
                v-if="hasResults && courses.last_page > 1"
                class="mt-10"
                :links="courses.links"
                :from="courses.from"
                :to="courses.to"
                :total="courses.total"
            />
        </div>

        <!-- ============================================ Other categories -->
        <section v-if="otherCategories?.length" class="mt-16 border-t border-border pt-10">
            <h2 class="text-sm font-bold uppercase tracking-[0.16em] text-muted-foreground">
                Explore other categories
            </h2>
            <div class="mt-5 flex flex-wrap gap-2">
                <Link
                    v-for="other in otherCategories"
                    :key="other.id"
                    :href="routes.category(other.slug)"
                    class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-2 text-sm font-medium shadow-soft transition-all hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700 dark:hover:border-brand-800 dark:hover:text-brand-400"
                >
                    {{ other.name }}
                    <span class="text-xs text-muted-foreground">{{ other.courses_count ?? 0 }}</span>
                </Link>
            </div>
        </section>
    </div>
</template>
