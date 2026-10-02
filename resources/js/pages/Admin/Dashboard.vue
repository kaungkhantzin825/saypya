<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    BookOpen,
    ClipboardList,
    FolderTree,
    GraduationCap,
    Star,
    TrendingUp,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { Alert, Badge, Card, DataTable } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import { formatMMK } from '@/lib/utils';
import type { DataTableColumn } from '@/types/ui';
import type { Course, Enrollment } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    stats: {
        total_users: number;
        total_courses: number;
        total_enrollments: number;
        total_revenue: number | string;
        pending_courses: number;
        active_instructors: number;
        total_categories: number;
        total_reviews: number;
    };
    recentEnrollments: Enrollment[];
    topCourses: Course[];
    pendingUsers: number;
    title?: string;
}>();

/** Primary KPIs — the four numbers an admin checks first. */
const primaryStats = computed(() => [
    {
        label: 'Total users',
        value: String(props.stats.total_users ?? 0),
        icon: Users,
        href: routes.admin.users(),
        external: false,
    },
    {
        label: 'Total courses',
        value: String(props.stats.total_courses ?? 0),
        icon: BookOpen,
        href: routes.admin.courses(),
        external: true,
    },
    {
        label: 'Enrollments',
        value: String(props.stats.total_enrollments ?? 0),
        icon: GraduationCap,
        href: routes.admin.enrollments(),
        external: true,
    },
    {
        label: 'Revenue',
        value: formatMMK(props.stats.total_revenue ?? 0),
        icon: Wallet,
        href: routes.admin.reports(),
        external: true,
    },
]);

const secondaryStats = computed(() => [
    { label: 'Active lecturers', value: props.stats.active_instructors ?? 0, icon: Users },
    { label: 'Draft courses', value: props.stats.pending_courses ?? 0, icon: ClipboardList },
    { label: 'Categories', value: props.stats.total_categories ?? 0, icon: FolderTree },
    { label: 'Reviews', value: props.stats.total_reviews ?? 0, icon: Star },
]);

const columns: DataTableColumn[] = [
    { key: 'student', label: 'Student' },
    { key: 'course', label: 'Course' },
    { key: 'amount', label: 'Amount' },
    { key: 'status', label: 'Status' },
    { key: 'date', label: 'Date', align: 'right' },
];

const paymentVariant = (status: string) =>
    status === 'completed' ? 'success' : status === 'pending' ? 'warning' : 'destructive';
</script>

<template>
    <!-- Pending approvals -->
    <Alert v-if="props.pendingUsers > 0" variant="warning" class="mb-6">
        <div class="flex flex-wrap items-center gap-2">
            <AlertTriangle class="size-4 shrink-0" />
            <span>
                <strong>{{ props.pendingUsers }}</strong>
                {{ props.pendingUsers === 1 ? 'account is' : 'accounts are' }} waiting for approval.
            </span>
            <Link
                :href="routes.admin.users({ status: 'pending' })"
                class="font-semibold underline underline-offset-2"
            >
                Review now
            </Link>
        </div>
    </Alert>

    <!-- Primary KPIs -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <component
            :is="stat.external ? 'a' : Link"
            v-for="stat in primaryStats"
            :key="stat.label"
            :href="stat.href"
            class="group rounded-xl border border-border bg-card p-5 shadow-soft transition-all hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
                        {{ stat.label }}
                    </p>
                    <p class="mt-2 truncate text-2xl font-extrabold tracking-tight">{{ stat.value }}</p>
                </div>
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-colors group-hover:bg-brand-100 dark:bg-brand-950 dark:text-brand-400"
                >
                    <component :is="stat.icon" class="size-5" />
                </span>
            </div>
            <span class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-muted-foreground">
                More info
                <ArrowRight class="size-3 transition-transform group-hover:translate-x-0.5" />
            </span>
        </component>
    </div>

    <!-- Secondary stats -->
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Card v-for="stat in secondaryStats" :key="stat.label" class="flex items-center gap-4">
            <span
                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-muted text-muted-foreground"
            >
                <component :is="stat.icon" class="size-5" />
            </span>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    {{ stat.label }}
                </p>
                <p class="text-xl font-extrabold">{{ stat.value }}</p>
            </div>
        </Card>
    </div>

    <!-- Recent activity -->
    <div class="mt-6 grid grid-cols-1 min-w-0 gap-6 xl:grid-cols-3">
        <Card class="min-w-0 xl:col-span-2" :padded="false">
            <template #header>
                <div class="flex items-center gap-2">
                    <TrendingUp class="size-4 text-brand-600 dark:text-brand-400" />
                    <h2 class="text-base font-bold">Recent enrollments</h2>
                </div>
            </template>

            <div class="min-w-0 px-6 pb-6">
                <DataTable
                    :columns="columns"
                    :rows="props.recentEnrollments"
                    :row-key="(row) => row.id"
                    empty-title="No enrollments yet"
                    empty-description="Paid enrollments will appear here."
                >
                    <template #cell-student="{ row }">
                        <span class="font-medium">{{ row.user?.name ?? 'N/A' }}</span>
                    </template>

                    <template #cell-course="{ row }">
                        <span class="line-clamp-1">{{ row.course?.title ?? 'N/A' }}</span>
                    </template>

                    <template #cell-amount="{ row }">
                        <span class="font-medium">{{ formatMMK(row.price_paid) }}</span>
                    </template>

                    <template #cell-status="{ row }">
                        <Badge :variant="paymentVariant(row.payment_status)">
                            {{ row.payment_status }}
                        </Badge>
                    </template>

                    <template #cell-date="{ row }">
                        <span class="text-muted-foreground">
                            {{ row.enrolled_at ? new Date(row.enrolled_at).toLocaleDateString() : '—' }}
                        </span>
                    </template>
                </DataTable>
            </div>
        </Card>

        <Card class="min-w-0" :padded="false">
            <template #header>
                <div class="flex items-center gap-2">
                    <Star class="size-4 text-brand-600 dark:text-brand-400" />
                    <h2 class="text-base font-bold">Top courses</h2>
                </div>
            </template>

            <ul class="divide-y divide-border">
                <li
                    v-for="course in props.topCourses"
                    :key="course.id"
                    class="flex items-center justify-between gap-3 px-6 py-3"
                >
                    <div class="min-w-0">
                        <p class="line-clamp-1 text-sm font-semibold">{{ course.title }}</p>
                        <p class="line-clamp-1 text-xs text-muted-foreground">
                            {{ course.instructor?.name ?? 'N/A' }}
                        </p>
                    </div>
                    <Badge variant="brand" class="shrink-0">
                        {{ course.total_students ?? 0 }} students
                    </Badge>
                </li>
                <li v-if="props.topCourses.length === 0" class="px-6 py-8 text-center text-sm text-muted-foreground">
                    No courses yet
                </li>
            </ul>
        </Card>
    </div>
</template>
