<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    Award,
    BookOpen,
    Clock,
    GraduationCap,
    Search,
    ShieldCheck,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import { Badge, Button, Card } from '@/components/ui';
import PublicLayout from '@/layouts/PublicLayout.vue';
import CourseCard from '@/components/site/CourseCard.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import StarRating from '@/components/site/StarRating.vue';
import Avatar from '@/components/ui/Avatar.vue';
import { useShared } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import type { Category, Course, HeroSlide, InstructorSummary } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    heroSlides: HeroSlide[];
    featuredCourses: Course[];
    popularCourses: Course[];
    categories: Category[];
    topInstructors: (InstructorSummary & { courses_count?: number })[];
    stats: {
        total_courses: number;
        total_students: number;
        total_instructors: number;
        total_enrollments: number;
    };
}>();

const { app } = useShared();

/* ---------------------------------------------------------------- hero --- */

const activeSlide = ref(0);
let timer: number | undefined;

const slides = computed(() => props.heroSlides ?? []);
const hasSlides = computed(() => slides.value.length > 0);

function goTo(index: number) {
    activeSlide.value = (index + slides.value.length) % slides.value.length;
}

onMounted(() => {
    if (slides.value.length > 1) {
        timer = window.setInterval(() => goTo(activeSlide.value + 1), 6000);
    }
});

onBeforeUnmount(() => {
    if (timer) window.clearInterval(timer);
});

const search = ref('');

function submitSearch() {
    router.get(routes.search(search.value.trim() || undefined));
}

/* --------------------------------------------------------------- stats --- */

const statCards = computed(() => [
    { label: 'Courses', value: props.stats?.total_courses ?? 0, icon: BookOpen },
    { label: 'Students', value: props.stats?.total_students ?? 0, icon: Users },
    { label: 'Instructors', value: props.stats?.total_instructors ?? 0, icon: GraduationCap },
    { label: 'Enrollments', value: props.stats?.total_enrollments ?? 0, icon: Award },
]);

const features = [
    {
        icon: BookOpen,
        title: 'Expert-led lessons',
        body: 'Structured video courses built by working professionals, split into short, focused lessons.',
    },
    {
        icon: ShieldCheck,
        title: 'Learn at your pace',
        body: 'Lifetime access to everything you buy. Pick up exactly where you left off on any device.',
    },
    {
        icon: Award,
        title: 'Certificates',
        body: 'Pass the course exam and receive a certificate you can share with employers.',
    },
    {
        icon: Clock,
        title: 'Bilingual',
        body: 'Study in English or Myanmar — the whole site and your course material follow your choice.',
    },
];
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="relative overflow-hidden border-b border-border">
        <div class="hero-backdrop pointer-events-none absolute inset-0" />

        <div class="page-container relative py-16 sm:py-24">
            <div v-if="hasSlides" class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <Badge variant="brand" class="mb-5">
                        <Sparkles class="size-3" />
                        Sanpya Online Academy
                    </Badge>

                    <Transition mode="out-in" name="fade">
                        <div :key="activeSlide">
                            <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">
                                {{ slides[activeSlide]?.title ?? app.name }}
                            </h1>
                            <p
                                v-if="slides[activeSlide]?.subtitle"
                                class="mt-5 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg"
                            >
                                {{ slides[activeSlide]?.subtitle }}
                            </p>
                        </div>
                    </Transition>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <Button
                            v-if="slides[activeSlide]?.button_text && slides[activeSlide]?.button_link"
                            :href="slides[activeSlide]?.button_link ?? routes.courses()"
                            size="lg"
                            variant="brand"
                        >
                            {{ slides[activeSlide]?.button_text }}
                            <ArrowRight />
                        </Button>
                        <Button v-else :href="routes.courses()" size="lg" variant="brand">
                            Browse courses
                            <ArrowRight />
                        </Button>
                        <Button :href="routes.about()" size="lg" variant="outline">Learn more</Button>
                    </div>

                    <!-- Slide dots -->
                    <div v-if="slides.length > 1" class="mt-8 flex items-center gap-2">
                        <button
                            v-for="(slide, index) in slides"
                            :key="slide.id"
                            type="button"
                            :aria-label="`Go to slide ${index + 1}`"
                            :class="[
                                'h-1.5 rounded-full transition-all',
                                index === activeSlide ? 'w-8 bg-brand-600' : 'w-4 bg-border hover:bg-brand-300',
                            ]"
                            @click="goTo(index)"
                        />
                    </div>
                </div>

                <div class="relative hidden lg:block">
                    <div class="overflow-hidden rounded-2xl border border-border shadow-lift">
                        <img
                            :src="slides[activeSlide]?.image_url"
                            :alt="slides[activeSlide]?.title ?? 'Featured'"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    </div>
                </div>
            </div>

            <!-- Fallback hero when no slides are configured -->
            <div v-else class="mx-auto max-w-3xl text-center">
                <Badge variant="brand" class="mb-5">
                    <Sparkles class="size-3" />
                    Sanpya Online Academy
                </Badge>
                <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-6xl">
                    Learn skills that move your career forward
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                    Expert-led courses in English and Myanmar, with lifetime access, progress tracking and
                    certificates on completion.
                </p>

                <form class="mx-auto mt-8 flex max-w-lg items-center gap-2" @submit.prevent="submitSearch">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="What do you want to learn?"
                            aria-label="Search courses"
                            class="h-12 w-full rounded-xl border border-input bg-background pl-10 pr-4 text-sm shadow-sm focus-visible:border-ring focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/25"
                        />
                    </div>
                    <Button type="submit" size="lg" variant="brand">Search</Button>
                </form>
            </div>
        </div>
    </section>

    <!-- =========================================================== Stats -->
    <section class="border-b border-border bg-muted/40">
        <div class="page-container grid grid-cols-2 gap-6 py-10 lg:grid-cols-4">
            <div v-for="stat in statCards" :key="stat.label" class="flex items-center gap-4">
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                >
                    <component :is="stat.icon" class="size-5" />
                </span>
                <div>
                    <p class="text-2xl font-extrabold leading-none tracking-tight">
                        {{ stat.value.toLocaleString('en-US') }}+
                    </p>
                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                        {{ stat.label }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== Featured -->
    <section v-if="featuredCourses?.length" class="page-container py-16 sm:py-20">
        <SectionHeading
            eyebrow="Handpicked"
            title="Featured courses"
            subtitle="Our most recommended courses, chosen for quality and outcomes."
        >
            <template #action>
                <Button :href="routes.courses()" variant="outline">
                    View all
                    <ArrowRight />
                </Button>
            </template>
        </SectionHeading>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <CourseCard v-for="course in featuredCourses" :key="course.id" :course="course" />
        </div>
    </section>

    <!-- ====================================================== Categories -->
    <section v-if="categories?.length" class="border-y border-border bg-muted/40 py-16 sm:py-20">
        <div class="page-container">
            <SectionHeading
                eyebrow="Explore"
                title="Browse by category"
                subtitle="Find the right course for where you are right now."
            />

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="routes.category(category.slug)"
                    class="group flex items-center justify-between gap-4 rounded-xl border border-border bg-card p-5 shadow-soft transition-all hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800"
                >
                    <div class="min-w-0">
                        <p class="truncate font-semibold transition-colors group-hover:text-brand-700 dark:group-hover:text-brand-400">
                            {{ category.name }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ category.courses_count ?? 0 }}
                            {{ (category.courses_count ?? 0) === 1 ? 'course' : 'courses' }}
                        </p>
                    </div>
                    <ArrowRight
                        class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-brand-600"
                    />
                </Link>
            </div>
        </div>
    </section>

    <!-- ========================================================= Popular -->
    <section v-if="popularCourses?.length" class="page-container py-16 sm:py-20">
        <SectionHeading
            eyebrow="Trending"
            title="Most popular right now"
            subtitle="What other learners are enrolling in this month."
        >
            <template #action>
                <Button :href="routes.courses({ sort: 'popular' })" variant="outline">
                    See more
                    <ArrowRight />
                </Button>
            </template>
        </SectionHeading>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <CourseCard v-for="course in popularCourses" :key="course.id" :course="course" />
        </div>
    </section>

    <!-- ========================================================= Why us -->
    <section class="border-y border-border bg-muted/40 py-16 sm:py-20">
        <div class="page-container">
            <SectionHeading
                align="center"
                eyebrow="Why Sanpya"
                title="Everything you need to actually finish"
                subtitle="No fluff, no expiring access — just a clear path from first lesson to certificate."
            />

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="feature in features" :key="feature.title" class="h-full">
                    <span
                        class="mb-4 flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                    >
                        <component :is="feature.icon" class="size-5" />
                    </span>
                    <h3 class="font-semibold">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ feature.body }}</p>
                </Card>
            </div>
        </div>
    </section>

    <!-- ==================================================== Instructors -->
    <section v-if="topInstructors?.length" class="page-container py-16 sm:py-20">
        <SectionHeading
            eyebrow="Meet the team"
            title="Learn from experienced instructors"
            subtitle="Practitioners who teach what they do every day."
        />

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="instructor in topInstructors"
                :key="instructor.id"
                :href="routes.instructors(instructor.id)"
                class="group flex items-center gap-4 rounded-xl border border-border bg-card p-5 shadow-soft transition-all hover:-translate-y-1 hover:shadow-lift"
            >
                <Avatar :src="instructor.avatar_url" :name="instructor.name" size="lg" />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold transition-colors group-hover:text-brand-700 dark:group-hover:text-brand-400">
                        {{ instructor.name }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ instructor.courses_count ?? 0 }}
                        {{ (instructor.courses_count ?? 0) === 1 ? 'course' : 'courses' }}
                    </p>
                    <StarRating :rating="instructor.average_rating ?? 0" class="mt-1.5" />
                </div>
            </Link>
        </div>
    </section>

    <!-- ============================================================= CTA -->
    <section class="page-container pb-20">
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 px-8 py-14 text-center sm:px-14"
        >
            <div class="hero-backdrop pointer-events-none absolute inset-0 opacity-25" />
            <div class="relative mx-auto max-w-2xl">
                <h2 class="text-balance text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    Start learning today
                </h2>
                <p class="mt-4 text-pretty text-sm leading-relaxed text-brand-100 sm:text-base">
                    Create a free account and get instant access to your dashboard, wishlist and progress
                    tracking.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <Button :href="routes.register()" size="lg" class="bg-white text-brand-800 hover:bg-brand-50">
                        Create free account
                    </Button>
                    <Button
                        :href="routes.courses()"
                        size="lg"
                        variant="outline"
                        class="border-white/40 bg-transparent text-white hover:bg-white/10 hover:text-white"
                    >
                        Browse courses
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.35s ease, transform 0.35s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
