<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Check, Pencil, Plus, Search, Star, Trash2, Eye } from 'lucide-vue-next';
import {
    AppImage,
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
import { formatMMK } from '@/lib/utils';
import type { DataTableColumn } from '@/types/ui';
import type { Category, Course, Paginated } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    courses: Paginated<Course>;
    categories: Category[];
    filters: {
        status?: string | null;
        category?: string | number | null;
        featured?: string | null;
        search?: string | null;
        sort?: string | null;
        direction?: string | null;
    };
    title?: string;
    description?: string;
}>();

/** `all` is a sentinel: reka-ui's Select cannot hold an empty-string value. */
const status = ref(props.filters.status ?? 'all');
const category = ref(props.filters.category != null ? String(props.filters.category) : 'all');
const featured = ref(props.filters.featured != null && props.filters.featured !== '' ? String(props.filters.featured) : 'all');
const search = ref(props.filters.search ?? '');
/** `key:asc|desc` — matches the DataTable's v-model contract. */
const sort = ref(`${props.filters.sort ?? 'created_at'}:${props.filters.direction === 'asc' ? 'asc' : 'desc'}`);

const statusOptions = [
    { value: 'all', label: 'All statuses' },
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' },
];

const featuredOptions = [
    { value: 'all', label: 'All courses' },
    { value: '1', label: 'Featured' },
    { value: '0', label: 'Not featured' },
];

const categoryOptions = computed(() => [
    { value: 'all', label: 'All categories' },
    ...props.categories.map((item) => ({ value: String(item.id), label: item.name })),
]);

const isFiltered = computed(
    () =>
        status.value !== 'all' ||
        category.value !== 'all' ||
        featured.value !== 'all' ||
        search.value.trim() !== '',
);

function applyFilters() {
    const [sortKey, sortDirection] = sort.value.split(':');

    router.get(
        routes.admin.courses({
            status: status.value === 'all' ? undefined : status.value,
            category: category.value === 'all' ? undefined : category.value,
            featured: featured.value === 'all' ? undefined : featured.value,
            search: search.value.trim() || undefined,
            sort: sortKey,
            direction: sortDirection,
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

/** DataTable emits `key:asc|desc`; sorting is done by the database. */
function onSort(value: string) {
    sort.value = value;
    applyFilters();
}

function clearFilters() {
    status.value = 'all';
    category.value = 'all';
    featured.value = 'all';
    search.value = '';
    sort.value = 'created_at:desc';
    applyFilters();
}

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
});

watch([status, category, featured], applyFilters);

const columns: DataTableColumn[] = [
    { key: 'thumbnail', label: 'Thumbnail', align: 'left' },
    { key: 'title', label: 'Title', sortable: true },
    { key: 'instructor', label: 'Instructor', hideOnMobile: true },
    { key: 'category', label: 'Category', hideOnMobile: true },
    { key: 'price', label: 'Price', sortable: true },
    { key: 'students', label: 'Students', align: 'center', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
];

const statusVariant = (value: string) =>
    value === 'published' ? 'success' : value === 'draft' ? 'warning' : 'muted';

const studentCount = (course: Course) => course.enrollments_count ?? course.total_students ?? 0;

function approve(course: Course) {
    router.patch(routes.admin.courseApprove(course.id), {}, { preserveScroll: true });
}

function toggleFeatured(course: Course) {
    router.patch(routes.admin.courseFeature(course.id), {}, { preserveScroll: true });
}

// --- Delete confirmation -------------------------------------------------
// `Course` does NOT use SoftDeletes, and the controller also unlinks the
// thumbnail from disk, so this really is permanent.
const deleteTarget = ref<Course | null>(null);
const deleting = ref(false);

function confirmDelete(course: Course) {
    deleteTarget.value = course;
}

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(routes.admin.courseDestroy(deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <!-- Quick actions -->
    <div class="mb-5 flex flex-wrap items-center justify-end gap-2">
        <Button :href="routes.admin.courseCreate()" external variant="brand">
            <Plus />
            Create course
        </Button>
    </div>

    <Card class="min-w-0" :padded="false">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">All courses</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ props.courses.total }} {{ props.courses.total === 1 ? 'course' : 'courses' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <!-- Filters -->
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <Label for="filter-status" class="mb-1.5 block">Status</Label>
                    <Select id="filter-status" v-model="status" :options="statusOptions" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-category" class="mb-1.5 block">Category</Label>
                    <Select id="filter-category" v-model="category" :options="categoryOptions" />
                </div>
                <div class="lg:col-span-2">
                    <Label for="filter-featured" class="mb-1.5 block">Featured</Label>
                    <Select id="filter-featured" v-model="featured" :options="featuredOptions" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-search" class="mb-1.5 block">Search</Label>
                    <Input id="filter-search" v-model="search" :icon="Search" placeholder="Course title…" />
                </div>
                <div class="flex items-end lg:col-span-2">
                    <Button v-if="isFiltered" variant="ghost" class="w-full" @click="clearFilters">
                        Clear
                    </Button>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="props.courses.data"
                :row-key="(row) => row.id"
                :sort="sort"
                has-actions
                empty-title="No courses found"
                empty-description="Try adjusting the filters above."
                @update:sort="onSort"
            >
                <template #cell-thumbnail="{ row }">
                    <AppImage
                        :src="row.thumbnail_url"
                        :alt="row.title"
                        class="h-12 w-20 shrink-0 rounded-md object-cover ring-1 ring-border"
                        loading="lazy"
                    />
                </template>

                <template #cell-title="{ row }">
                    <div class="flex items-start gap-2">
                        <div class="min-w-0">
                            <a
                                :href="routes.admin.course(row.id)"
                                class="line-clamp-2 font-medium hover:text-brand-600 hover:underline"
                            >
                                {{ row.title || 'Untitled course' }}
                            </a>
                            <p class="mt-0.5 text-xs capitalize text-muted-foreground md:hidden">
                                {{ row.instructor?.name ?? 'N/A' }}
                            </p>
                        </div>
                        <Star
                            v-if="row.is_featured"
                            class="mt-0.5 size-4 shrink-0 fill-amber-400 text-amber-400"
                            aria-label="Featured"
                        />
                    </div>
                </template>

                <template #cell-instructor="{ row }">
                    <span class="text-muted-foreground">{{ row.instructor?.name ?? 'N/A' }}</span>
                </template>

                <template #cell-category="{ row }">
                    <span class="text-muted-foreground">{{ row.category?.name ?? 'N/A' }}</span>
                </template>

                <template #cell-price="{ row }">
                    <div v-if="row.discount_price" class="whitespace-nowrap">
                        <span class="block text-xs text-muted-foreground line-through">
                            {{ formatMMK(row.price) }}
                        </span>
                        <span class="font-semibold text-success">{{ formatMMK(row.discount_price) }}</span>
                    </div>
                    <span v-else class="whitespace-nowrap">{{ formatMMK(row.price) }}</span>
                </template>

                <template #cell-students="{ row }">
                    <span class="text-muted-foreground">{{ studentCount(row) }}</span>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="statusVariant(row.status)" class="capitalize">{{ row.status }}</Badge>
                </template>

                <template #actions="{ row }">
                    <Button
                        :href="routes.admin.course(row.id)"
                        external
                        variant="ghost"
                        size="icon-sm"
                        aria-label="View course"
                    >
                        <Eye />
                    </Button>

                    <Button
                        :href="routes.admin.courseEdit(row.id)"
                        external
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Edit course"
                    >
                        <Pencil />
                    </Button>

                    <Button
                        v-if="row.status === 'draft'"
                        variant="ghost"
                        size="icon-sm"
                        class="text-success hover:bg-success/10 hover:text-success"
                        aria-label="Approve and publish"
                        @click="approve(row)"
                    >
                        <Check />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        :class="row.is_featured ? 'text-amber-500 hover:bg-amber-500/10 hover:text-amber-500' : ''"
                        :aria-label="row.is_featured ? 'Remove from featured' : 'Mark as featured'"
                        @click="toggleFeatured(row)"
                    >
                        <Star :class="row.is_featured ? 'fill-current' : ''" />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        aria-label="Delete course"
                        @click="confirmDelete(row)"
                    >
                        <Trash2 />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.courses.links"
                        :from="props.courses.from"
                        :to="props.courses.to"
                        :total="props.courses.total"
                    />
                </template>
            </DataTable>
        </div>
    </Card>

    <!-- Delete confirmation -->
    <!-- Course is hard-deleted and its thumbnail unlinked, so warn accordingly. -->
    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete course"
        :description="`Permanently delete “${deleteTarget?.title ?? ''}”? Its thumbnail file is removed too. This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete course
            </Button>
        </template>
    </Dialog>
</template>
