<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Check, RotateCcw, X } from 'lucide-vue-next';
import {
    Badge,
    Button,
    Card,
    DataTable,
    Input,
    Label,
    Pagination,
    Progress,
    Select,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import { formatMMK } from '@/lib/utils';
import type { DataTableColumn } from '@/types/ui';
import type { Enrollment, Paginated } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    enrollments: Paginated<Enrollment>;
    stats: {
        completed: number;
        pending: number;
        failed: number;
        refunded: number;
        revenue: number | string;
    };
    filters: {
        status?: string | null;
        from_date?: string | null;
        to_date?: string | null;
        sort?: string | null;
        direction?: string | null;
    };
    title?: string;
    description?: string;
}>();

/** `all` is a sentinel: reka-ui's Select cannot hold an empty-string value. */
const status = ref(props.filters.status ?? 'all');
const fromDate = ref(props.filters.from_date ?? '');
const toDate = ref(props.filters.to_date ?? '');
const sort = ref(`${props.filters.sort ?? 'enrolled_at'}:${props.filters.direction === 'asc' ? 'asc' : 'desc'}`);

const statusOptions = [
    { value: 'all', label: 'All statuses' },
    { value: 'completed', label: 'Completed' },
    { value: 'pending', label: 'Pending' },
    { value: 'failed', label: 'Failed' },
    { value: 'refunded', label: 'Refunded' },
];

const isFiltered = computed(
    () => status.value !== 'all' || fromDate.value !== '' || toDate.value !== '',
);

function applyFilters() {
    const [sortKey, sortDirection] = sort.value.split(':');

    router.get(
        routes.admin.enrollments({
            status: status.value === 'all' ? undefined : status.value,
            from_date: fromDate.value || undefined,
            to_date: toDate.value || undefined,
            sort: sortKey,
            direction: sortDirection,
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

// Status is a single-select: apply immediately. The date range keeps its
// explicit Apply button so half-typed dates don't fire a request.
watch(status, applyFilters);

function onSort(value: string) {
    sort.value = value;
    applyFilters();
}

function clearFilters() {
    status.value = 'all';
    fromDate.value = '';
    toDate.value = '';
    sort.value = 'enrolled_at:desc';
    applyFilters();
}

const statCards = computed(() => [
    { label: 'Completed', value: props.stats.completed, tone: 'text-success' },
    { label: 'Pending', value: props.stats.pending, tone: 'text-warning' },
    { label: 'Failed', value: props.stats.failed, tone: 'text-destructive' },
    { label: 'Refunded', value: props.stats.refunded, tone: 'text-muted-foreground' },
    { label: 'Total revenue', value: formatMMK(props.stats.revenue), tone: 'text-foreground' },
]);

const columns: DataTableColumn[] = [
    { key: 'id', label: 'ID', sortable: true, hideOnMobile: true },
    { key: 'student', label: 'Student' },
    { key: 'course', label: 'Course', hideOnMobile: true },
    { key: 'amount', label: 'Amount', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'progress', label: 'Progress', sortable: true, hideOnMobile: true },
    { key: 'enrolled_at', label: 'Enrolled', sortable: true, hideOnMobile: true },
];

const statusVariant = (value: string) =>
    value === 'completed' ? 'success' : value === 'pending' ? 'warning' : value === 'failed' ? 'destructive' : 'muted';

function approve(enrollment: Enrollment) {
    router.patch(routes.admin.enrollmentApprove(enrollment.id), {}, { preserveScroll: true });
}

function reject(enrollment: Enrollment) {
    router.patch(routes.admin.enrollmentReject(enrollment.id), {}, { preserveScroll: true });
}

function refund(enrollment: Enrollment) {
    router.patch(routes.admin.enrollmentRefund(enrollment.id), {}, { preserveScroll: true });
}
</script>

<template>
    <!-- Totals -->
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <Card v-for="stat in statCards" :key="stat.label" class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ stat.label }}</p>
            <p class="mt-1 truncate text-xl font-extrabold" :class="stat.tone">{{ stat.value }}</p>
        </Card>
    </div>

    <Card class="min-w-0" :padded="false">
        <template #header>
            <div>
                <h2 class="text-base font-bold">All enrollments</h2>
                <p class="text-sm text-muted-foreground">
                    {{ props.enrollments.total }}
                    {{ props.enrollments.total === 1 ? 'enrollment' : 'enrollments' }}
                </p>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <Label for="filter-status" class="mb-1.5 block">Payment status</Label>
                    <Select id="filter-status" v-model="status" :options="statusOptions" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-from" class="mb-1.5 block">Enrolled from</Label>
                    <Input id="filter-from" v-model="fromDate" type="date" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-to" class="mb-1.5 block">Enrolled to</Label>
                    <Input id="filter-to" v-model="toDate" type="date" />
                </div>
                <div class="flex items-end gap-2 lg:col-span-3">
                    <Button variant="outline" class="flex-1" @click="applyFilters">Apply</Button>
                    <Button v-if="isFiltered" variant="ghost" @click="clearFilters">Clear</Button>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="props.enrollments.data"
                :row-key="(row) => row.id"
                :sort="sort"
                has-actions
                empty-title="No enrollments found"
                empty-description="Try widening the date range or clearing the filters."
                @update:sort="onSort"
            >
                <template #cell-student="{ row }">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ row.user?.name ?? 'Unknown student' }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ row.user?.email }}</p>
                    </div>
                </template>

                <template #cell-course="{ row }">
                    <span class="text-muted-foreground">{{ row.course?.title ?? 'Deleted course' }}</span>
                </template>

                <template #cell-amount="{ row }">
                    <span class="whitespace-nowrap font-medium">{{ formatMMK(row.price_paid) }}</span>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="statusVariant(row.payment_status)" class="capitalize">
                        {{ row.payment_status }}
                    </Badge>
                </template>

                <template #cell-progress="{ row }">
                    <div class="flex items-center gap-2">
                        <Progress :value="row.progress_percentage" class="w-20" />
                        <span class="whitespace-nowrap text-xs text-muted-foreground">
                            {{ Math.round(row.progress_percentage) }}%
                        </span>
                    </div>
                </template>

                <template #cell-enrolled_at="{ row }">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ row.enrolled_at ? new Date(row.enrolled_at).toLocaleDateString() : '—' }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <template v-if="row.payment_status === 'pending'">
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            class="text-success hover:bg-success/10 hover:text-success"
                            aria-label="Approve enrollment"
                            @click="approve(row)"
                        >
                            <Check />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            aria-label="Reject enrollment"
                            @click="reject(row)"
                        >
                            <X />
                        </Button>
                    </template>

                    <Button
                        v-else-if="row.payment_status === 'completed'"
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Refund enrollment"
                        @click="refund(row)"
                    >
                        <RotateCcw />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.enrollments.links"
                        :from="props.enrollments.from"
                        :to="props.enrollments.to"
                        :total="props.enrollments.total"
                    />
                </template>
            </DataTable>
        </div>
    </Card>
</template>
