<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, CheckCircle2, ClipboardList, Eye, Pencil, TrendingUp, XCircle } from 'lucide-vue-next';
import { Avatar, Badge, Button, Card, DataTable, EmptyState, Pagination } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { DataTableColumn } from '@/types/ui';
import type { Exam, ExamAttempt, Paginated } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    exam: Exam;
    attempts: Paginated<ExamAttempt>;
    /** Aggregated over every attempt, not just the current page. */
    stats: { total: number; passed: number; failed: number; average: number };
    title?: string;
    description?: string;
}>();

const passRate = computed(() =>
    props.stats.total > 0 ? Math.round((props.stats.passed / props.stats.total) * 100) : 0,
);

const failRate = computed(() =>
    props.stats.total > 0 ? Math.round((props.stats.failed / props.stats.total) * 100) : 0,
);

const columns: DataTableColumn[] = [
    { key: 'student', label: 'Student' },
    { key: 'score', label: 'Score' },
    { key: 'percentage', label: 'Percentage' },
    { key: 'passed', label: 'Status' },
    { key: 'submitted_at', label: 'Submitted', hideOnMobile: true },
];

function percentage(attempt: ExamAttempt): number {
    return Math.round(attempt.percentage ?? 0);
}
</script>

<template>
    <Link
        :href="routes.admin.exams()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to exams
    </Link>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total attempts</p>
                    <p class="mt-1 text-3xl font-extrabold tabular-nums">{{ props.stats.total }}</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400">
                    <ClipboardList class="size-5" />
                </span>
            </div>
        </Card>

        <Card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Passed</p>
                    <p class="mt-1 text-3xl font-extrabold tabular-nums text-success">{{ props.stats.passed }}</p>
                    <p v-if="props.stats.total > 0" class="mt-1 text-xs text-muted-foreground">
                        {{ passRate }}% pass rate
                    </p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-full bg-success/12 text-success">
                    <CheckCircle2 class="size-5" />
                </span>
            </div>
        </Card>

        <Card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Failed</p>
                    <p class="mt-1 text-3xl font-extrabold tabular-nums text-destructive">{{ props.stats.failed }}</p>
                    <p v-if="props.stats.total > 0" class="mt-1 text-xs text-muted-foreground">
                        {{ failRate }}% fail rate
                    </p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-full bg-destructive/12 text-destructive">
                    <XCircle class="size-5" />
                </span>
            </div>
        </Card>

        <Card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Average score</p>
                    <p class="mt-1 text-3xl font-extrabold tabular-nums text-warning">{{ props.stats.average }}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Passing: {{ props.exam.passing_score }}%</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-full bg-warning/15 text-warning">
                    <TrendingUp class="size-5" />
                </span>
            </div>
        </Card>
    </div>

    <Card class="mt-6 min-w-0" :padded="false">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">All attempts</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ props.exam.title }}
                        <span v-if="props.exam.course"> · {{ props.exam.course.title }}</span>
                    </p>
                </div>
                <Badge variant="muted">{{ props.attempts.total }} total</Badge>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <DataTable
                :columns="columns"
                :rows="props.attempts.data"
                :row-key="(row) => row.id"
                has-actions
            >
                <template #empty>
                    <EmptyState
                        :icon="ClipboardList"
                        title="No attempts yet for this exam"
                        description="Student results will appear here once they take the exam."
                    />
                </template>

                <template #cell-student="{ row }">
                    <div class="flex items-center gap-2">
                        <Avatar :src="row.user?.avatar_url" :name="row.user?.name" size="sm" />
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ row.user?.name ?? 'Unknown student' }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ row.user?.email }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-score="{ row }">
                    <span class="whitespace-nowrap font-semibold tabular-nums">{{ row.score ?? 0 }}</span>
                    <span class="text-muted-foreground">/{{ row.total_points }}</span>
                </template>

                <template #cell-percentage="{ row }">
                    <span
                        class="text-base font-bold tabular-nums"
                        :class="row.passed ? 'text-success' : 'text-destructive'"
                    >
                        {{ percentage(row) }}%
                    </span>
                </template>

                <template #cell-passed="{ row }">
                    <Badge :variant="row.passed ? 'success' : 'destructive'">
                        {{ row.passed ? 'Passed' : 'Failed' }}
                    </Badge>
                </template>

                <template #cell-submitted_at="{ row }">
                    <span v-if="row.submitted_at" class="whitespace-nowrap text-muted-foreground">
                        {{ new Date(row.submitted_at).toLocaleDateString() }}
                        <span class="block text-xs">
                            {{ new Date(row.submitted_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                        </span>
                    </span>
                    <span v-else class="text-muted-foreground">In progress</span>
                </template>

                <template #actions="{ row }">
                    <!-- Student-facing result page is still Blade — needs a full load. -->
                    <Button
                        :href="routes.admin.examResultView(row.id)"
                        external
                        variant="ghost"
                        size="icon-sm"
                        aria-label="View result"
                    >
                        <Eye />
                    </Button>

                    <Button
                        v-if="row.status === 'submitted'"
                        :href="routes.admin.examGrade(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        class="text-warning hover:bg-warning/15 hover:text-warning"
                        aria-label="Grade attempt"
                    >
                        <Pencil />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.attempts.links"
                        :from="props.attempts.from"
                        :to="props.attempts.to"
                        :total="props.attempts.total"
                    />
                </template>
            </DataTable>
        </div>
    </Card>

    <p class="mt-4 flex items-center gap-1.5 text-xs text-muted-foreground">
        <ArrowUpRight class="size-3.5" />
        The eye icon opens the student-facing result page in a new tab.
    </p>
</template>
