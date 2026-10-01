<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BookOpen, Search as SearchIcon } from 'lucide-vue-next';
import { Badge, Button, Card, EmptyState, Label, Pagination, Select } from '@/components/ui';
import CourseCard from '@/components/site/CourseCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';
import type { Category, Course, Paginated } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    courses: Paginated<Course>;
    categories: Category[];
    query?: string | null;
    category?: string | null;
    level?: string | null;
    sort?: string;
}>();

/** Reka reserves `''` as a Select value, so an explicit sentinel means "no filter". */
const ALL = 'all';

const term = ref(props.query ?? '');

const filters = reactive({
    category: props.category ?? ALL,
    level: props.level ?? ALL,
    sort: props.sort ?? 'newest',
});

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

const results = computed(() => props.courses.data ?? []);
const hasResults = computed(() => results.value.length > 0);
const hasQuery = computed(() => !!(props.query && props.query.trim()));

function submit() {
    const data: Record<string, string> = {};

    if (filters.category !== ALL) data.category = filters.category;
    if (filters.level !== ALL) data.level = filters.level;
    if (filters.sort && filters.sort !== 'newest') data.sort = filters.sort;

    router.get(routes.search(term.value.trim() || undefined), data, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="border-b border-border bg-muted/40">
        <div class="page-container py-10 sm:py-14">
            <Badge variant="brand" class="mb-4">
                <SearchIcon class="size-3" />
                Search
            </Badge>
            <h1 class="text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">
                Find your next course
            </h1>
            <p class="mt-3 max-w-2xl text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base">
                Search across every course in the academy, then narrow things down by category, level and price.
            </p>

            <form class="mt-8 grid gap-4" @submit.prevent="submit">
                <div class="relative">
                    <SearchIcon
                        class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-muted-foreground"
                    />
                    <input
                        v-model="term"
                        type="search"
                        placeholder="What do you want to learn?"
                        aria-label="Search courses"
                        class="h-14 w-full rounded-xl border border-input bg-background pl-12 pr-4 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/25"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-2">
                        <Label for="search-category">Category</Label>
                        <Select id="search-category" v-model="filters.category" :options="categoryOptions" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="search-level">Level</Label>
                        <Select id="search-level" v-model="filters.level" :options="levelOptions" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="search-sort">Sort by</Label>
                        <Select id="search-sort" v-model="filters.sort" :options="sortOptions" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button type="submit" variant="brand" size="lg">
                        <SearchIcon />
                        Search
                    </Button>
                    <Button v-if="hasQuery" :href="routes.courses()" variant="outline" size="lg">
                        Browse all courses
                    </Button>
                </div>
            </form>
        </div>
    </section>

    <div class="page-container py-10 sm:py-14">
        <!-- ===================================================== Results -->
        <div class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="text-lg font-bold">
                <template v-if="hasQuery">
                    {{ courses.total }}
                    {{ courses.total === 1 ? 'result' : 'results' }} for
                    <span class="text-brand-700 dark:text-brand-400">“{{ query }}”</span>
                </template>
                <template v-else>
                    {{ courses.total }} {{ courses.total === 1 ? 'course' : 'courses' }} available
                </template>
            </h2>
        </div>

        <div v-if="hasResults" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <CourseCard v-for="course in results" :key="course.id" :course="course" />
        </div>

        <Card v-else class="mt-8" :padded="false">
            <EmptyState
                :icon="BookOpen"
                title="No courses matched your search"
                :description="
                    hasQuery
                        ? 'Try a different keyword, or loosen the filters to see more results.'
                        : 'Adjust your filters or browse the full catalogue to find something that fits.'
                "
            >
                <Button :href="routes.courses()" variant="brand">
                    Browse all courses
                </Button>
            </EmptyState>
        </Card>

        <Pagination
            v-if="hasResults && courses.last_page > 1"
            class="mt-10"
            :links="courses.links"
            :from="courses.from"
            :to="courses.to"
            :total="courses.total"
        />
    </div>
</template>
