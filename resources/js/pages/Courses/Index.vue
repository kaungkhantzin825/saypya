<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { BookOpen, RotateCcw, Search, SlidersHorizontal } from 'lucide-vue-next';
import { Badge, Button, Card, EmptyState, Input, Label, Pagination, Select } from '@/components/ui';
import CourseCard from '@/components/site/CourseCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';
import type { Category, Course, Paginated } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    courses: Paginated<Course>;
    categories: Category[];
    category?: string | null;
    level?: string | null;
    sort?: string;
}>();

/** Reka reserves `''` as a Select value, so an explicit sentinel means "no filter". */
const ALL = 'all';

const page = usePage();
const initialParams = new URLSearchParams(page.url.split('?')[1] ?? '');

const filters = reactive({
    search: initialParams.get('search') ?? '',
    category: props.category ?? ALL,
    level: props.level ?? ALL,
    sort: props.sort ?? 'newest',
});

const showFilters = ref(false);

const categoryOptions = computed(() => [
    { value: ALL, label: 'All categories' },
    ...props.categories.map((category) => ({ value: String(category.id), label: category.name })),
]);

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

const total = computed(() => props.courses.total ?? 0);
const hasResults = computed(() => (props.courses.data?.length ?? 0) > 0);

const hasActiveFilters = computed(
    () =>
        filters.search.trim() !== '' ||
        filters.category !== ALL ||
        filters.level !== ALL ||
        filters.sort !== 'newest',
);

function apply() {
    const params: Record<string, string> = {};

    if (filters.search.trim()) params.search = filters.search.trim();
    if (filters.category !== ALL) params.category = filters.category;
    if (filters.level !== ALL) params.level = filters.level;
    if (filters.sort && filters.sort !== 'newest') params.sort = filters.sort;

    router.get(routes.courses(params), {}, { preserveState: true, preserveScroll: true, replace: true });
    showFilters.value = false;
}

function reset() {
    filters.search = '';
    filters.category = ALL;
    filters.level = ALL;
    filters.sort = 'newest';
    apply();
}
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="border-b border-border bg-muted/40">
        <div class="page-container py-10 sm:py-14">
            <Badge variant="brand" class="mb-4">Course catalogue</Badge>
            <h1 class="text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">
                Browse all courses
            </h1>
            <p class="mt-3 max-w-2xl text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base">
                <template v-if="total > 0">
                    <span class="font-semibold text-foreground">{{ total }}</span>
                    {{ total === 1 ? 'course' : 'courses' }} ready to explore. Filter by category, level or
                    price to find your next step.
                </template>
                <template v-else>
                    New courses are added regularly — check back soon or browse every category.
                </template>
            </p>
        </div>
    </section>

    <div class="page-container py-10 sm:py-14">
        <div class="grid gap-8 lg:grid-cols-[18rem_1fr]">
            <!-- ================================================ Filters -->
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <Button
                    variant="outline"
                    block
                    class="lg:hidden"
                    @click="showFilters = !showFilters"
                >
                    <SlidersHorizontal />
                    {{ showFilters ? 'Hide filters' : 'Show filters' }}
                </Button>

                <Card :class="showFilters ? 'mt-4 block' : 'mt-4 hidden lg:block'" :padded="false">
                    <form class="grid gap-5 p-5" @submit.prevent="apply">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold">Refine results</h2>
                            <button
                                v-if="hasActiveFilters"
                                type="button"
                                class="inline-flex items-center gap-1 text-xs font-medium text-brand-700 transition-colors hover:text-brand-800 dark:text-brand-400"
                                @click="reset"
                            >
                                <RotateCcw class="size-3" />
                                Reset
                            </button>
                        </div>

                        <div class="grid gap-2">
                            <Label for="course-search">Search</Label>
                            <Input
                                id="course-search"
                                v-model="filters.search"
                                type="search"
                                :icon="Search"
                                placeholder="Course title or topic"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="course-category">Category</Label>
                            <Select
                                id="course-category"
                                v-model="filters.category"
                                :options="categoryOptions"
                                placeholder="All categories"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="course-level">Level</Label>
                            <Select
                                id="course-level"
                                v-model="filters.level"
                                :options="levelOptions"
                                placeholder="All levels"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="course-sort">Sort by</Label>
                            <Select
                                id="course-sort"
                                v-model="filters.sort"
                                :options="sortOptions"
                                placeholder="Newest"
                            />
                        </div>

                        <Button type="submit" variant="brand" block>Apply filters</Button>
                    </form>
                </Card>
            </aside>

            <!-- ================================================= Results -->
            <section>
                <div v-if="hasResults" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <CourseCard v-for="course in courses.data" :key="course.id" :course="course" />
                </div>

                <EmptyState
                    v-else
                    :icon="BookOpen"
                    title="No courses match your filters"
                    description="Try widening your search — remove a filter or pick a different category."
                >
                    <Button variant="outline" @click="reset">
                        <RotateCcw />
                        Clear all filters
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
            </section>
        </div>
    </div>
</template>
