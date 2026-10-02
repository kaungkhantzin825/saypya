<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { BarChart3, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import {
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
import type { Course, Exam, Paginated } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    exams: Paginated<Exam>;
    courses: Course[];
    filters: {
        search?: string | null;
        course_id?: string | number | null;
        is_published?: string | null;
        sort?: string | null;
        direction?: string | null;
    };
    title?: string;
    description?: string;
}>();

/** `all` is a sentinel — reka-ui's Select cannot hold an empty-string value. */
const search = ref(props.filters.search ?? '');
const courseId = ref(
    props.filters.course_id != null && props.filters.course_id !== '' ? String(props.filters.course_id) : 'all',
);
const published = ref(
    props.filters.is_published != null && props.filters.is_published !== ''
        ? String(props.filters.is_published)
        : 'all',
);
/** `key:asc|desc` — matches the DataTable's v-model contract. */
const sort = ref(`${props.filters.sort ?? 'created_at'}:${props.filters.direction === 'asc' ? 'asc' : 'desc'}`);

const courseOptions = [
    { value: 'all', label: 'All courses' },
    ...props.courses.map((course) => ({ value: String(course.id), label: course.title })),
];

const publishedOptions = [
    { value: 'all', label: 'All statuses' },
    { value: '1', label: 'Published' },
    { value: '0', label: 'Draft' },
];

const isFiltered = computed(
    () => courseId.value !== 'all' || published.value !== 'all' || search.value.trim() !== '',
);

function applyFilters() {
    const [sortKey, sortDirection] = sort.value.split(':');

    router.get(
        routes.admin.exams({
            search: search.value.trim() || undefined,
            course_id: courseId.value === 'all' ? undefined : courseId.value,
            is_published: published.value === 'all' ? undefined : published.value,
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
    courseId.value = 'all';
    published.value = 'all';
    sort.value = 'created_at:desc';
    applyFilters();
}

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
});

watch([courseId, published], applyFilters);

const columns: DataTableColumn[] = [
    { key: 'title', label: 'Exam', sortable: true },
    { key: 'course', label: 'Course', hideOnMobile: true },
    { key: 'questions', label: 'Questions' },
    { key: 'duration', label: 'Duration', sortable: true, hideOnMobile: true },
    { key: 'attempts', label: 'Attempts', hideOnMobile: true },
    { key: 'is_published', label: 'Status', sortable: true },
];

// Exam has no SoftDeletes — the row, its questions and its attempts are all gone.
const deleteTarget = ref<Exam | null>(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(routes.admin.examDestroy(deleteTarget.value.id), {
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
                    <h2 class="text-base font-bold">All exams</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ props.exams.total }} {{ props.exams.total === 1 ? 'exam' : 'exams' }}
                    </p>
                </div>
                <Button :href="routes.admin.examCreate()" variant="brand">
                    <Plus />
                    Create exam
                </Button>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <Label for="filter-search" class="mb-1.5 block">Search</Label>
                    <Input id="filter-search" v-model="search" :icon="Search" placeholder="Exam title…" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-course" class="mb-1.5 block">Course</Label>
                    <Select id="filter-course" v-model="courseId" :options="courseOptions" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-status" class="mb-1.5 block">Status</Label>
                    <Select id="filter-status" v-model="published" :options="publishedOptions" />
                </div>
                <div class="flex items-end lg:col-span-2">
                    <Button v-if="isFiltered" variant="ghost" class="w-full" @click="clearFilters">Clear</Button>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="props.exams.data"
                :row-key="(row) => row.id"
                :sort="sort"
                has-actions
                empty-title="No exams found"
                empty-description="Adjust the filters, or create the first exam for a course."
                @update:sort="onSort"
            >
                <template #cell-title="{ row }">
                    <div class="min-w-0 max-w-xs">
                        <p class="truncate font-medium">{{ row.title }}</p>
                        <p v-if="row.description" class="truncate text-xs text-muted-foreground">
                            {{ row.description }}
                        </p>
                    </div>
                </template>

                <template #cell-course="{ row }">
                    <span class="text-muted-foreground">{{ row.course?.title ?? 'N/A' }}</span>
                </template>

                <template #cell-questions="{ row }">
                    <Badge :variant="(row.questions_count ?? 0) > 0 ? 'info' : 'warning'">
                        {{ row.questions_count ?? 0 }} {{ (row.questions_count ?? 0) === 1 ? 'question' : 'questions' }}
                    </Badge>
                </template>

                <template #cell-duration="{ row }">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ row.duration_minutes ? `${row.duration_minutes} min` : 'Unlimited' }}
                    </span>
                </template>

                <template #cell-attempts="{ row }">
                    <span class="tabular-nums text-muted-foreground">{{ row.attempts_count ?? 0 }}</span>
                </template>

                <template #cell-is_published="{ row }">
                    <Badge :variant="row.is_published ? 'success' : 'secondary'">
                        {{ row.is_published ? 'Published' : 'Draft' }}
                    </Badge>
                </template>

                <template #actions="{ row }">
                    <Button
                        :href="routes.admin.examEdit(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Edit exam"
                    >
                        <Pencil />
                    </Button>

                    <Button
                        :href="routes.admin.examResults(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        aria-label="View results"
                    >
                        <BarChart3 />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        aria-label="Delete exam"
                        @click="deleteTarget = row"
                    >
                        <Trash2 />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.exams.links"
                        :from="props.exams.from"
                        :to="props.exams.to"
                        :total="props.exams.total"
                    />
                </template>
            </DataTable>
        </div>
    </Card>

    <!-- Exam has no SoftDeletes — the row, questions and attempts are all removed. -->
    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete exam"
        :description="`Permanently delete “${deleteTarget?.title}”? Every question and student attempt on it is deleted too. This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete exam
            </Button>
        </template>
    </Dialog>
</template>
