<script setup lang="ts">
import { computed } from 'vue';
import { GraduationCap, PlayCircle } from 'lucide-vue-next';
import { Badge, Button, EmptyState, Pagination } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { formatDate } from '@/lib/utils';
import type { ExamAttempt, Paginated } from '@/types';

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    attempts: Paginated<ExamAttempt>;
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

const rows = computed(() => props.attempts?.data ?? []);
const hasAttempts = computed(() => rows.value.length > 0);

function dateLabel(attempt: ExamAttempt): string {
    return formatDate(attempt.submitted_at ?? attempt.started_at);
}

function scoreLabel(attempt: ExamAttempt): string {
    if (attempt.status === 'in_progress') return '—';
    return `${attempt.score ?? 0}/${attempt.total_points}`;
}

function resultLabel(attempt: ExamAttempt): string {
    if (attempt.passed === true) return 'Passed';
    if (attempt.passed === false) return 'Failed';
    return 'In progress';
}

function resultVariant(attempt: ExamAttempt): 'success' | 'destructive' | 'muted' {
    if (attempt.passed === true) return 'success';
    if (attempt.passed === false) return 'destructive';
    return 'muted';
}

function statusLabel(attempt: ExamAttempt): string {
    if (attempt.status === 'in_progress') return 'In progress';
    if (attempt.status === 'submitted') return 'Awaiting grading';
    return 'Graded';
}

function statusVariant(attempt: ExamAttempt): 'warning' | 'info' | 'secondary' {
    if (attempt.status === 'in_progress') return 'warning';
    if (attempt.status === 'submitted') return 'info';
    return 'secondary';
}
</script>

<template>
    <div>
        <div v-if="hasAttempts" class="overflow-hidden rounded-xl border border-border bg-card">
            <!-- Column headers (desktop) -->
            <div
                class="hidden grid-cols-[minmax(0,1fr)_7rem_6rem_11rem_auto] gap-4 border-b border-border bg-muted/50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground sm:grid"
            >
                <span>Exam</span>
                <span>Date</span>
                <span>Score</span>
                <span>Result</span>
                <span class="sr-only">Actions</span>
            </div>

            <ul class="divide-y divide-border">
                <li
                    v-for="attempt in rows"
                    :key="attempt.id"
                    class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_7rem_6rem_11rem_auto] sm:items-center sm:gap-4"
                >
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ attempt.exam?.title ?? 'Exam' }}</p>
                        <p v-if="attempt.exam?.course" class="truncate text-xs text-muted-foreground">
                            {{ attempt.exam.course.title }}
                        </p>
                    </div>

                    <div class="text-sm text-muted-foreground">{{ dateLabel(attempt) }}</div>

                    <div class="text-sm font-semibold tabular-nums">{{ scoreLabel(attempt) }}</div>

                    <div class="flex flex-wrap items-center gap-1.5">
                        <Badge :variant="resultVariant(attempt)">{{ resultLabel(attempt) }}</Badge>
                        <Badge :variant="statusVariant(attempt)">{{ statusLabel(attempt) }}</Badge>
                    </div>

                    <div class="flex items-center gap-2 sm:justify-end">
                        <Button
                            v-if="attempt.status === 'in_progress' && attempt.exam"
                            :href="routes.startExam(attempt.exam.id)"
                            size="sm"
                            variant="brand"
                        >
                            <PlayCircle />
                            Resume
                        </Button>
                        <Button :href="routes.examResult(attempt.id)" size="sm" variant="outline">
                            View result
                        </Button>
                    </div>
                </li>
            </ul>
        </div>

        <EmptyState
            v-else
            :icon="GraduationCap"
            title="No exam attempts yet"
            description="Once you take an exam, your score and result history will show up here."
        >
            <Button :href="routes.myCourses()" variant="outline">Go to my courses</Button>
        </EmptyState>

        <Pagination
            v-if="hasAttempts && attempts.last_page > 1"
            class="mt-6"
            :links="attempts.links"
            :from="attempts.from"
            :to="attempts.to"
            :total="attempts.total"
        />
    </div>
</template>
