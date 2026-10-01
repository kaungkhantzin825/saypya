<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Award,
    BookOpen,
    CheckCircle2,
    Clock,
    Compass,
    PlayCircle,
} from 'lucide-vue-next';
import { Button, Card, EmptyState, Progress } from '@/components/ui';
import CourseCard from '@/components/site/CourseCard.vue';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { clampPercent, cn, timeAgo } from '@/lib/utils';
import type { Course, Enrollment, Lesson, LessonProgress, Section } from '@/types';

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    enrollments: Enrollment[];
    wishlist: Course[];
    recentActivity: LessonProgress[];
    stats: {
        enrolled_courses: number;
        completed_courses: number;
        certificates: number;
        total_hours: number;
    };
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

/* ---------------------------------------------------------------- stats --- */

const toneClasses: Record<string, string> = {
    brand: 'bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400',
    success: 'bg-success/12 text-success',
    warning: 'bg-warning/15 text-warning',
    info: 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
};

const statTiles = computed(() => [
    {
        key: 'enrolled',
        label: 'Enrolled courses',
        value: props.stats?.enrolled_courses ?? 0,
        icon: BookOpen,
        tone: 'brand',
    },
    {
        key: 'completed',
        label: 'Completed',
        value: props.stats?.completed_courses ?? 0,
        icon: CheckCircle2,
        tone: 'success',
    },
    {
        key: 'certificates',
        label: 'Certificates',
        value: props.stats?.certificates ?? 0,
        icon: Award,
        tone: 'warning',
    },
    {
        key: 'hours',
        label: 'Hours learned',
        value: props.stats?.total_hours ?? 0,
        icon: Clock,
        tone: 'info',
    },
]);

/* ------------------------------------------------------------ continue --- */

const continueLearning = computed(() =>
    (props.enrollments ?? [])
        .filter((enrollment): enrollment is Enrollment & { course: Course } => !!enrollment.course)
        .slice(0, 3),
);

/* ------------------------------------------------------------- activity --- */

type ActivityLesson = Lesson & { section?: (Section & { course?: Course | null }) | null };
type ActivityRow = LessonProgress & { created_at?: string | null; lesson?: ActivityLesson | null };

interface ActivityItem {
    id: number;
    lessonTitle: string;
    course: Course | null;
    createdAt: string | null;
}

const activity = computed<ActivityItem[]>(() =>
    (props.recentActivity ?? [])
        .slice(0, 6)
        .map((row): ActivityItem => {
            const entry = row as ActivityRow;
            return {
                id: entry.id,
                lessonTitle: entry.lesson?.title ?? 'Lesson',
                course: entry.lesson?.section?.course ?? null,
                createdAt: entry.created_at ?? null,
            };
        }),
);

/* ------------------------------------------------------------- wishlist --- */

const wishlistStrip = computed(() => (props.wishlist ?? []).slice(0, 4));

const hasNothing = computed(
    () => (props.enrollments?.length ?? 0) === 0 && (props.wishlist?.length ?? 0) === 0,
);
</script>

<template>
    <div class="grid gap-8">
        <!-- ======================================================= Stats -->
        <section aria-label="Learning summary">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    v-for="tile in statTiles"
                    :key="tile.key"
                    class="rounded-xl border border-border bg-card p-5 shadow-soft"
                >
                    <span
                        :class="cn('flex size-10 items-center justify-center rounded-lg', toneClasses[tile.tone])"
                    >
                        <component :is="tile.icon" class="size-5" />
                    </span>
                    <p class="mt-4 text-2xl font-extrabold leading-none tracking-tight">
                        {{ Number(tile.value).toLocaleString('en-US') }}
                    </p>
                    <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                        {{ tile.label }}
                    </p>
                </div>
            </div>
        </section>

        <!-- ================================================== Empty state -->
        <EmptyState
            v-if="hasNothing"
            :icon="Compass"
            title="Your learning starts here"
            description="Enrol in your first course to track progress, earn certificates and pick up right where you left off."
        >
            <Button :href="routes.courses()" variant="brand">
                Browse courses
                <ArrowRight />
            </Button>
        </EmptyState>

        <template v-else>
            <!-- ======================================== Continue learning -->
            <section v-if="continueLearning.length" aria-labelledby="continue-heading">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 id="continue-heading" class="text-lg font-bold tracking-tight">Continue learning</h2>
                    <Link
                        :href="routes.myCourses()"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 transition-colors hover:text-brand-800 dark:text-brand-400"
                    >
                        All courses
                        <ArrowRight class="size-3.5" />
                    </Link>
                </div>

                <div class="grid gap-4">
                    <article
                        v-for="enrollment in continueLearning"
                        :key="enrollment.id"
                        class="flex flex-col gap-4 rounded-xl border border-border bg-card p-4 shadow-soft sm:flex-row sm:items-center sm:gap-5 sm:p-5"
                    >
                        <img
                            :src="enrollment.course.thumbnail_url"
                            :alt="enrollment.course.title"
                            loading="lazy"
                            decoding="async"
                            class="aspect-video w-full shrink-0 rounded-lg object-cover sm:w-40"
                        />

                        <div class="min-w-0 flex-1">
                            <h3 class="line-clamp-1 font-semibold">
                                <Link
                                    :href="routes.course(enrollment.course.slug)"
                                    class="transition-colors hover:text-brand-700 dark:hover:text-brand-400"
                                >
                                    {{ enrollment.course.title }}
                                </Link>
                            </h3>
                            <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                {{ enrollment.course.instructor?.name ?? 'Sanpya Academy' }}
                            </p>
                            <div class="mt-3 flex items-center gap-3">
                                <Progress
                                    :value="enrollment.progress_percentage"
                                    auto-tone
                                    size="sm"
                                    class="flex-1"
                                />
                                <span class="shrink-0 text-xs font-semibold tabular-nums text-muted-foreground">
                                    {{ clampPercent(enrollment.progress_percentage) }}%
                                </span>
                            </div>
                        </div>

                        <Button
                            :href="routes.learn(enrollment.course.slug)"
                            variant="brand"
                            class="sm:shrink-0"
                        >
                            Continue
                            <ArrowRight />
                        </Button>
                    </article>
                </div>
            </section>

            <!-- ====================================== Activity + wishlist -->
            <div class="grid gap-8 lg:grid-cols-2">
                <section v-if="activity.length" aria-labelledby="activity-heading">
                    <h2 id="activity-heading" class="mb-4 text-lg font-bold tracking-tight">Recent activity</h2>
                    <Card :padded="false">
                        <ul class="divide-y divide-border">
                            <li v-for="item in activity" :key="item.id" class="flex items-start gap-3 p-4">
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                                >
                                    <PlayCircle class="size-4" />
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ item.lessonTitle }}</p>
                                    <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                        <Link
                                            v-if="item.course"
                                            :href="routes.course(item.course.slug)"
                                            class="transition-colors hover:text-foreground"
                                        >
                                            {{ item.course.title }}
                                        </Link>
                                        <span v-else>Course unavailable</span>
                                    </p>
                                </div>

                                <time
                                    v-if="item.createdAt"
                                    :datetime="item.createdAt"
                                    class="shrink-0 text-xs text-muted-foreground"
                                >
                                    {{ timeAgo(item.createdAt) }}
                                </time>
                            </li>
                        </ul>
                    </Card>
                </section>

                <section v-if="wishlistStrip.length" aria-labelledby="wishlist-heading">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 id="wishlist-heading" class="text-lg font-bold tracking-tight">Saved for later</h2>
                        <Link
                            :href="routes.myWishlist()"
                            class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 transition-colors hover:text-brand-800 dark:text-brand-400"
                        >
                            Wishlist
                            <ArrowRight class="size-3.5" />
                        </Link>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <CourseCard v-for="course in wishlistStrip" :key="course.id" :course="course" />
                    </div>
                </section>
            </div>
        </template>
    </div>
</template>
