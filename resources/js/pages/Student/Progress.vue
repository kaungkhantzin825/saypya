<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, Compass, PlayCircle, TrendingUp } from 'lucide-vue-next';
import { Badge, Button, Card, EmptyState, Progress } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { clampPercent, cn } from '@/lib/utils';

interface ProgressEnrollment {
    id: number;
    course: {
        id: number;
        title: string;
        slug: string;
        total_lessons?: number;
    };
    progress_percentage: number;
    completed_at?: string | null;
    enrolled_at?: string | null;
}

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    enrollments: ProgressEnrollment[];
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

const enrollments = computed<ProgressEnrollment[]>(() => props.enrollments ?? []);
const hasEnrollments = computed(() => enrollments.value.length > 0);

function isComplete(enrollment: ProgressEnrollment): boolean {
    return !!enrollment.completed_at || clampPercent(enrollment.progress_percentage) >= 100;
}

const completedCount = computed(() => enrollments.value.filter(isComplete).length);

const inProgressCount = computed(
    () =>
        enrollments.value.filter(
            (enrollment) => !isComplete(enrollment) && clampPercent(enrollment.progress_percentage) > 0,
        ).length,
);

const averageProgress = computed(() => {
    if (!enrollments.value.length) return 0;
    const sum = enrollments.value.reduce(
        (total, enrollment) => total + clampPercent(enrollment.progress_percentage),
        0,
    );
    return Math.round(sum / enrollments.value.length);
});

const summary = computed(() => [
    { key: 'progress', label: 'In progress', value: inProgressCount.value, suffix: '', icon: PlayCircle, tone: 'brand' },
    { key: 'completed', label: 'Completed', value: completedCount.value, suffix: '', icon: CheckCircle2, tone: 'success' },
    { key: 'average', label: 'Average completion', value: averageProgress.value, suffix: '%', icon: TrendingUp, tone: 'info' },
]);

const toneClasses: Record<string, string> = {
    brand: 'bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400',
    success: 'bg-success/12 text-success',
    info: 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
};

function statusOf(enrollment: ProgressEnrollment): 'completed' | 'in_progress' | 'not_started' {
    if (isComplete(enrollment)) return 'completed';
    if (clampPercent(enrollment.progress_percentage) > 0) return 'in_progress';
    return 'not_started';
}

const statusLabels: Record<string, string> = {
    completed: 'Completed',
    in_progress: 'In progress',
    not_started: 'Not started',
};

const statusVariants: Record<string, 'success' | 'brand' | 'muted'> = {
    completed: 'success',
    in_progress: 'brand',
    not_started: 'muted',
};

function lessonsCompleted(enrollment: ProgressEnrollment): number {
    const total = enrollment.course.total_lessons ?? 0;
    return Math.round((clampPercent(enrollment.progress_percentage) / 100) * total);
}
</script>

<template>
    <div class="grid gap-8">
        <!-- ===================================================== Summary -->
        <section aria-label="Progress summary">
            <div class="grid gap-4 sm:grid-cols-3">
                <div
                    v-for="tile in summary"
                    :key="tile.key"
                    class="rounded-xl border border-border bg-card p-5 shadow-soft"
                >
                    <span
                        :class="cn('flex size-10 items-center justify-center rounded-lg', toneClasses[tile.tone])"
                    >
                        <component :is="tile.icon" class="size-5" />
                    </span>
                    <p class="mt-4 text-2xl font-extrabold leading-none tracking-tight">
                        {{ Number(tile.value).toLocaleString('en-US') }}{{ tile.suffix }}
                    </p>
                    <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                        {{ tile.label }}
                    </p>
                </div>
            </div>
        </section>

        <!-- ======================================================== List -->
        <section v-if="hasEnrollments" aria-label="Course progress">
            <Card :padded="false">
                <ul class="divide-y divide-border">
                    <li
                        v-for="enrollment in enrollments"
                        :key="enrollment.id"
                        class="grid gap-4 p-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:gap-6"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="min-w-0 text-sm font-semibold">
                                    <Link
                                        :href="routes.course(enrollment.course.slug)"
                                        class="line-clamp-1 transition-colors hover:text-brand-700 dark:hover:text-brand-400"
                                    >
                                        {{ enrollment.course.title }}
                                    </Link>
                                </h2>
                                <Badge :variant="statusVariants[statusOf(enrollment)]">
                                    {{ statusLabels[statusOf(enrollment)] }}
                                </Badge>
                            </div>

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

                            <p class="mt-2 text-xs text-muted-foreground">
                                <template v-if="enrollment.course.total_lessons">
                                    {{ lessonsCompleted(enrollment) }} of
                                    {{ enrollment.course.total_lessons }}
                                    {{ enrollment.course.total_lessons === 1 ? 'lesson' : 'lessons' }} completed
                                </template>
                                <template v-else>Lesson count unavailable</template>
                            </p>
                        </div>

                        <Button
                            :href="routes.learn(enrollment.course.slug)"
                            :variant="statusOf(enrollment) === 'completed' ? 'outline' : 'brand'"
                            size="sm"
                            class="sm:shrink-0"
                        >
                            {{ statusOf(enrollment) === 'completed' ? 'Review' : 'Continue' }}
                        </Button>
                    </li>
                </ul>
            </Card>
        </section>

        <EmptyState
            v-else
            :icon="Compass"
            title="No progress to show yet"
            description="Enrol in a course and your completion will be tracked here lesson by lesson."
        >
            <Button :href="routes.courses()" variant="brand">
                Browse courses
                <PlayCircle />
            </Button>
        </EmptyState>
    </div>
</template>
