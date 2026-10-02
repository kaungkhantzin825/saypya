<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Check, Pencil, Search, Star, Trash2 } from 'lucide-vue-next';
import {
    Avatar,
    Badge,
    Button,
    Card,
    DataTable,
    Dialog,
    Input,
    Label,
    Pagination,
    Select,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { DataTableColumn } from '@/types/ui';
import type { Paginated, Review } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    reviews: Paginated<Review>;
    filters: {
        search?: string | null;
        rating?: string | number | null;
        approved?: string | null;
        sort?: string | null;
        direction?: string | null;
    };
    title?: string;
    description?: string;
}>();

/** `all` is a sentinel: reka-ui's Select cannot hold an empty-string value. */
const search = ref(props.filters.search ?? '');
const rating = ref(props.filters.rating != null && props.filters.rating !== '' ? String(props.filters.rating) : 'all');
const approved = ref(
    props.filters.approved != null && props.filters.approved !== '' ? String(props.filters.approved) : 'all',
);
/** `key:asc|desc` — matches the DataTable's v-model contract. */
const sort = ref(`${props.filters.sort ?? 'created_at'}:${props.filters.direction === 'asc' ? 'asc' : 'desc'}`);

const ratingOptions = [
    { value: 'all', label: 'All ratings' },
    ...[5, 4, 3, 2, 1].map((value) => ({ value: String(value), label: `${value} star${value > 1 ? 's' : ''}` })),
];

const approvedOptions = [
    { value: 'all', label: 'All reviews' },
    { value: '1', label: 'Approved' },
    { value: '0', label: 'Pending' },
];

const isFiltered = computed(
    () => rating.value !== 'all' || approved.value !== 'all' || search.value.trim() !== '',
);

function applyFilters() {
    const [sortKey, sortDirection] = sort.value.split(':');

    router.get(
        routes.admin.reviews({
            search: search.value.trim() || undefined,
            rating: rating.value === 'all' ? undefined : rating.value,
            approved: approved.value === 'all' ? undefined : approved.value,
            sort: sortKey,
            direction: sortDirection,
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function onSort(value: string) {
    sort.value = value;
    applyFilters();
}

function clearFilters() {
    search.value = '';
    rating.value = 'all';
    approved.value = 'all';
    sort.value = 'created_at:desc';
    applyFilters();
}

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
});

watch([rating, approved], applyFilters);

const columns: DataTableColumn[] = [
    { key: 'id', label: 'ID', sortable: true, hideOnMobile: true },
    { key: 'student', label: 'Student' },
    { key: 'course', label: 'Course', hideOnMobile: true },
    { key: 'rating', label: 'Rating', sortable: true },
    { key: 'comment', label: 'Comment', hideOnMobile: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true, hideOnMobile: true },
];

function approve(review: Review) {
    router.patch(routes.admin.reviewApprove(review.id), {}, { preserveScroll: true });
}

// `Review` does not use SoftDeletes — the row is really gone.
const deleteTarget = ref<Review | null>(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(routes.admin.reviewDestroy(deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <Card class="min-w-0" :padded="false">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">All reviews</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ props.reviews.total }} {{ props.reviews.total === 1 ? 'review' : 'reviews' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <Label for="filter-search" class="mb-1.5 block">Search</Label>
                    <Input id="filter-search" v-model="search" :icon="Search" placeholder="Student or course name…" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-rating" class="mb-1.5 block">Rating</Label>
                    <Select id="filter-rating" v-model="rating" :options="ratingOptions" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-approved" class="mb-1.5 block">Status</Label>
                    <Select id="filter-approved" v-model="approved" :options="approvedOptions" />
                </div>
                <div class="flex items-end lg:col-span-2">
                    <Button v-if="isFiltered" variant="ghost" class="w-full" @click="clearFilters">Clear</Button>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="props.reviews.data"
                :row-key="(row) => row.id"
                :sort="sort"
                has-actions
                empty-title="No reviews found"
                empty-description="Try adjusting the filters above."
                @update:sort="onSort"
            >
                <template #cell-student="{ row }">
                    <div class="flex items-center gap-2">
                        <Avatar :src="row.user?.avatar_url" :name="row.user?.name" size="sm" />
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ row.user?.name ?? 'Anonymous' }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ row.user?.email }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-course="{ row }">
                    <span class="text-muted-foreground">{{ row.course?.title ?? 'N/A' }}</span>
                </template>

                <template #cell-rating="{ row }">
                    <span class="flex items-center gap-0.5" :aria-label="`${row.rating} out of 5`">
                        <Star
                            v-for="index in 5"
                            :key="index"
                            class="size-3.5"
                            :class="index <= row.rating ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30'"
                        />
                    </span>
                </template>

                <template #cell-comment="{ row }">
                    <p class="line-clamp-2 max-w-xs text-muted-foreground">
                        {{ row.comment || '—' }}
                    </p>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="row.is_approved ? 'success' : 'warning'">
                        {{ row.is_approved ? 'Approved' : 'Pending' }}
                    </Badge>
                </template>

                <template #cell-created_at="{ row }">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ row.created_at ? new Date(row.created_at).toLocaleDateString() : '—' }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <Button
                        v-if="!row.is_approved"
                        variant="ghost"
                        size="icon-sm"
                        class="text-success hover:bg-success/10 hover:text-success"
                        aria-label="Approve review"
                        @click="approve(row)"
                    >
                        <Check />
                    </Button>

                    <Button
                        :href="routes.admin.reviewEdit(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Edit review"
                    >
                        <Pencil />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        aria-label="Delete review"
                        @click="deleteTarget = row"
                    >
                        <Trash2 />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.reviews.links"
                        :from="props.reviews.from"
                        :to="props.reviews.to"
                        :total="props.reviews.total"
                    />
                </template>
            </DataTable>
        </div>
    </Card>

    <!-- Review has no SoftDeletes — the row is really removed. -->
    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete review"
        :description="`Permanently delete this review by “${deleteTarget?.user?.name ?? 'this student'}”? This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete review
            </Button>
        </template>
    </Dialog>
</template>
