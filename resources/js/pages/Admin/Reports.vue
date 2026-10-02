<script setup lang="ts">
import { computed } from 'vue';
import { BarChart3, BookOpen, GraduationCap, TrendingUp, Users } from 'lucide-vue-next';
import { Card, DataTable } from '@/components/ui';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatMMK } from '@/lib/utils';
import type { DataTableColumn } from '@/types/ui';
import type {
    CategoryStat,
    MonthlyRevenue,
    ReportStats,
    TopCourse,
    TopInstructor,
} from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    stats: ReportStats;
    userDistribution: { students: number; lecturers: number; admins: number };
    monthlyRevenue: MonthlyRevenue;
    topCourses: TopCourse[];
    topInstructors: TopInstructor[];
    categoryStats: CategoryStat[];
    title?: string;
    description?: string;
}>();

/**
 * `formatMMK` renders a zero amount as "Free" by default, which reads oddly on a
 * revenue report — the old Blade printed "0 Ks", so keep that wording.
 */
const money = (amount: number | string | null | undefined) => formatMMK(amount, '0 Ks');

const statCards = computed(() => [
    { label: 'Total users', value: props.stats.total_users.toLocaleString('en-US'), icon: Users, tone: 'brand' },
    { label: 'Total courses', value: props.stats.total_courses.toLocaleString('en-US'), icon: BookOpen, tone: 'success' },
    {
        label: 'Total enrollments',
        value: props.stats.total_enrollments.toLocaleString('en-US'),
        icon: GraduationCap,
        tone: 'warning',
    },
    { label: 'Total revenue', value: money(props.stats.total_revenue), icon: TrendingUp, tone: 'destructive' },
]);

const toneClasses: Record<string, string> = {
    brand: 'bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400',
    success: 'bg-success/12 text-success',
    warning: 'bg-warning/15 text-warning',
    destructive: 'bg-destructive/12 text-destructive',
};

const distributionSlices = computed(() => [
    { label: 'Students', value: props.userDistribution.students, color: 'hsl(var(--success))' },
    { label: 'Lecturers', value: props.userDistribution.lecturers, color: '#0d9488' },
    { label: 'Admins', value: props.userDistribution.admins, color: 'hsl(var(--destructive))' },
]);

const revenueTotal = computed(() => props.monthlyRevenue.data.reduce((sum, value) => sum + Number(value || 0), 0));

const courseColumns: DataTableColumn[] = [
    { key: 'index', label: '#', class: 'w-12', hideOnMobile: true },
    { key: 'title', label: 'Course' },
    { key: 'instructor', label: 'Instructor', hideOnMobile: true },
    { key: 'enrollments_count', label: 'Students', align: 'right' },
    { key: 'revenue', label: 'Revenue', align: 'right' },
];

const instructorColumns: DataTableColumn[] = [
    { key: 'index', label: '#', class: 'w-12', hideOnMobile: true },
    { key: 'name', label: 'Instructor' },
    { key: 'courses_count', label: 'Courses', align: 'right', hideOnMobile: true },
    { key: 'students_count', label: 'Students', align: 'right' },
    { key: 'revenue', label: 'Revenue', align: 'right' },
];

const categoryColumns: DataTableColumn[] = [
    { key: 'name', label: 'Category' },
    { key: 'courses_count', label: 'Courses', align: 'right', hideOnMobile: true },
    { key: 'students_count', label: 'Students', align: 'right', hideOnMobile: true },
    { key: 'revenue', label: 'Revenue', align: 'right' },
    { key: 'avg_rating', label: 'Avg rating', align: 'right' },
];
</script>

<template>
    <!-- Overview -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Card v-for="card in statCards" :key="card.label">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-muted-foreground">{{ card.label }}</p>
                    <p class="mt-1 truncate text-2xl font-extrabold tabular-nums">{{ card.value }}</p>
                </div>
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-full"
                    :class="toneClasses[card.tone]"
                >
                    <component :is="card.icon" class="size-5" />
                </span>
            </div>
        </Card>
    </div>

    <!-- Charts -->
    <div class="mt-6 grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-3">
        <Card class="min-w-0 lg:col-span-2">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-bold">
                            <TrendingUp class="size-4 text-brand-600" />
                            Revenue by month
                        </h2>
                        <p class="text-sm text-muted-foreground">Completed enrollments over the last 12 months.</p>
                    </div>
                    <span class="text-sm font-semibold tabular-nums">{{ money(revenueTotal) }}</span>
                </div>
            </template>

            <LineChart
                :labels="props.monthlyRevenue.labels"
                :values="props.monthlyRevenue.data"
                :formatter="money"
            />
        </Card>

        <Card class="min-w-0">
            <template #header>
                <h2 class="flex items-center gap-2 text-base font-bold">
                    <Users class="size-4 text-brand-600" />
                    User distribution
                </h2>
            </template>

            <DoughnutChart :slices="distributionSlices" />
        </Card>
    </div>

    <!-- Leaderboards -->
    <!--
        Stacked, not side-by-side: DataTable's table carries `min-w-[42rem]` (672px),
        so two of them need ~1368px of content plus the sidebar. Splitting at `xl:`
        (or even `2xl:`) leaves each card ~614px wide, and the trailing
        students/revenue columns get clipped behind a scrollbar that has no visible
        affordance on desktop. Full width for both is the honest layout.
    -->
    <div class="mt-6 grid min-w-0 grid-cols-1 gap-6">
        <Card class="min-w-0" :padded="false">
            <template #header>
                <h2 class="flex items-center gap-2 text-base font-bold">
                    <BarChart3 class="size-4 text-brand-600" />
                    Top courses by enrollments
                </h2>
            </template>

            <div class="min-w-0 px-6 pb-6">
                <DataTable
                    :columns="courseColumns"
                    :rows="props.topCourses"
                    :row-key="(row) => row.id"
                    empty-title="No courses yet"
                    empty-description="Course performance appears here once students enroll."
                >
                    <template #cell-index="{ index }">
                        <span class="tabular-nums text-muted-foreground">{{ index + 1 }}</span>
                    </template>
                    <template #cell-title="{ row }">
                        <span class="font-medium">{{ row.title }}</span>
                    </template>
                    <template #cell-instructor="{ row }">
                        <span class="text-muted-foreground">{{ row.instructor?.name ?? 'N/A' }}</span>
                    </template>
                    <template #cell-enrollments_count="{ row }">
                        <span class="tabular-nums">{{ row.enrollments_count }}</span>
                    </template>
                    <template #cell-revenue="{ row }">
                        <span class="whitespace-nowrap tabular-nums">{{ money(row.revenue) }}</span>
                    </template>
                </DataTable>
            </div>
        </Card>

        <Card class="min-w-0" :padded="false">
            <template #header>
                <h2 class="flex items-center gap-2 text-base font-bold">
                    <GraduationCap class="size-4 text-brand-600" />
                    Top instructors
                </h2>
            </template>

            <div class="min-w-0 px-6 pb-6">
                <DataTable
                    :columns="instructorColumns"
                    :rows="props.topInstructors"
                    :row-key="(row) => row.id"
                    empty-title="No instructors yet"
                    empty-description="Instructor performance appears here once courses are sold."
                >
                    <template #cell-index="{ index }">
                        <span class="tabular-nums text-muted-foreground">{{ index + 1 }}</span>
                    </template>
                    <template #cell-name="{ row }">
                        <span class="font-medium">{{ row.name }}</span>
                    </template>
                    <template #cell-courses_count="{ row }">
                        <span class="tabular-nums">{{ row.courses_count }}</span>
                    </template>
                    <template #cell-students_count="{ row }">
                        <span class="tabular-nums">{{ row.students_count }}</span>
                    </template>
                    <template #cell-revenue="{ row }">
                        <span class="whitespace-nowrap tabular-nums">{{ money(row.revenue) }}</span>
                    </template>
                </DataTable>
            </div>
        </Card>
    </div>

    <!-- Categories -->
    <Card class="mt-6 min-w-0" :padded="false">
        <template #header>
            <h2 class="flex items-center gap-2 text-base font-bold">
                <BookOpen class="size-4 text-brand-600" />
                Courses by category
            </h2>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <DataTable
                :columns="categoryColumns"
                :rows="props.categoryStats"
                :row-key="(row) => row.id"
                empty-title="No categories yet"
                empty-description="Category rollups appear once courses are assigned to them."
            >
                <template #cell-name="{ row }">
                    <span class="font-medium">{{ row.name }}</span>
                </template>
                <template #cell-courses_count="{ row }">
                    <span class="tabular-nums">{{ row.courses_count }}</span>
                </template>
                <template #cell-students_count="{ row }">
                    <span class="tabular-nums">{{ row.students_count }}</span>
                </template>
                <template #cell-revenue="{ row }">
                    <span class="whitespace-nowrap tabular-nums">{{ money(row.revenue) }}</span>
                </template>
                <template #cell-avg_rating="{ row }">
                    <span class="whitespace-nowrap tabular-nums">
                        {{ Number(row.avg_rating ?? 0).toFixed(1) }}
                        <span class="text-muted-foreground">/ 5</span>
                    </span>
                </template>
            </DataTable>
        </div>
    </Card>
</template>
