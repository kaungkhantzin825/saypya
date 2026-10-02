<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Eye, Mail, Trash2 } from 'lucide-vue-next';
import {
    Badge,
    Button,
    Card,
    Checkbox,
    DataTable,
    Dialog,
    Label,
    Pagination,
    Select,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { DataTableColumn } from '@/types/ui';
import type { ContactMessage, Paginated } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    messages: Paginated<ContactMessage>;
    stats: { new: number; read: number; replied: number };
    filters: { status?: string | null; sort?: string | null; direction?: string | null };
    title?: string;
    description?: string;
}>();

/** `all` is a sentinel: reka-ui's Select cannot hold an empty-string value. */
const status = ref(props.filters.status ?? 'all');
const sort = ref(`${props.filters.sort ?? 'created_at'}:${props.filters.direction === 'asc' ? 'asc' : 'desc'}`);

const statusOptions = [
    { value: 'all', label: 'All statuses' },
    { value: 'new', label: 'New' },
    { value: 'read', label: 'Read' },
    { value: 'replied', label: 'Replied' },
];

const statCards = computed(() => [
    { label: 'New', value: props.stats.new, tone: 'text-brand-600' },
    { label: 'Read', value: props.stats.read, tone: 'text-muted-foreground' },
    { label: 'Replied', value: props.stats.replied, tone: 'text-success' },
]);

function applyFilters() {
    const [sortKey, sortDirection] = sort.value.split(':');

    router.get(
        routes.admin.contactMessages({
            status: status.value === 'all' ? undefined : status.value,
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

watch(status, applyFilters);

const columns: DataTableColumn[] = [
    { key: 'select', label: '', align: 'left' },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', hideOnMobile: true },
    { key: 'subject', label: 'Subject', hideOnMobile: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true, hideOnMobile: true },
];

// --- Selection ------------------------------------------------------------
const selected = ref<number[]>([]);

const allSelected = computed(
    () => props.messages.data.length > 0 && selected.value.length === props.messages.data.length,
);

function toggleAll(checked: boolean) {
    selected.value = checked ? props.messages.data.map((message) => message.id) : [];
}

function toggleOne(id: number, checked: boolean) {
    selected.value = checked ? [...selected.value, id] : selected.value.filter((value) => value !== id);
}

// --- Bulk delete ----------------------------------------------------------
const bulkOpen = ref(false);
const bulkDeleting = ref(false);

function destroyBulk() {
    bulkDeleting.value = true;
    router.delete(routes.admin.contactMessageBulkDelete(), {
        data: { ids: selected.value },
        preserveScroll: true,
        onFinish: () => {
            bulkDeleting.value = false;
            bulkOpen.value = false;
            selected.value = [];
        },
    });
}

// --- Single delete --------------------------------------------------------
// `ContactMessage` has no SoftDeletes — the row is really removed.
const deleteTarget = ref<ContactMessage | null>(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(routes.admin.contactMessageDestroy(deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}

const statusVariant = (value: string) =>
    value === 'new' ? 'brand' : value === 'replied' ? 'success' : 'muted';
</script>

<template>
    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <Card v-for="stat in statCards" :key="stat.label" class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ stat.label }}</p>
            <p class="mt-1 text-xl font-extrabold" :class="stat.tone">{{ stat.value }}</p>
        </Card>
    </div>

    <Card class="min-w-0" :padded="false">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">Inbox</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ props.messages.total }}
                        {{ props.messages.total === 1 ? 'message' : 'messages' }}
                    </p>
                </div>

                <Button
                    v-if="selected.length"
                    variant="destructive"
                    size="sm"
                    @click="bulkOpen = true"
                >
                    <Trash2 />
                    Delete {{ selected.length }} selected
                </Button>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <Label for="filter-status" class="mb-1.5 block">Status</Label>
                    <Select id="filter-status" v-model="status" :options="statusOptions" />
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="props.messages.data"
                :row-key="(row) => row.id"
                :sort="sort"
                has-actions
                empty-title="No messages found"
                empty-description="Nothing matches the current filter."
                @update:sort="onSort"
            >
                <template #cell-select="{ row }">
                    <Checkbox
                        :model-value="selected.includes(row.id)"
                        :aria-label="`Select message from ${row.name}`"
                        @update:model-value="(value: boolean) => toggleOne(row.id, value)"
                    />
                </template>

                <template #cell-name="{ row }">
                    <a
                        :href="routes.admin.contactMessage(row.id)"
                        class="font-medium hover:text-brand-600 hover:underline"
                    >
                        {{ row.name }}
                    </a>
                    <p class="mt-0.5 truncate text-xs text-muted-foreground md:hidden">{{ row.email }}</p>
                </template>

                <template #cell-email="{ row }">
                    <a :href="`mailto:${row.email}`" class="text-muted-foreground hover:text-foreground hover:underline">
                        {{ row.email }}
                    </a>
                </template>

                <template #cell-subject="{ row }">
                    <span class="text-muted-foreground">{{ row.subject || '—' }}</span>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="statusVariant(row.status)" class="capitalize">{{ row.status }}</Badge>
                </template>

                <template #cell-created_at="{ row }">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ row.created_at ? new Date(row.created_at).toLocaleDateString() : '—' }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <Button
                        :href="routes.admin.contactMessage(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        :aria-label="row.status === 'new' ? 'Read message' : 'View message'"
                    >
                        <Mail v-if="row.status === 'new'" />
                        <Eye v-else />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        aria-label="Delete message"
                        @click="deleteTarget = row"
                    >
                        <Trash2 />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.messages.links"
                        :from="props.messages.from"
                        :to="props.messages.to"
                        :total="props.messages.total"
                    />
                </template>
            </DataTable>

            <!-- Select-all lives outside the table header so it keeps its own state. -->
            <div v-if="props.messages.data.length" class="mt-3 flex items-center gap-2">
                <Checkbox
                    id="select-all"
                    :model-value="allSelected"
                    @update:model-value="(value: boolean) => toggleAll(value)"
                />
                <Label for="select-all" class="cursor-pointer text-sm font-normal text-muted-foreground">
                    Select all on this page
                </Label>
            </div>
        </div>
    </Card>

    <Dialog
        :open="bulkOpen"
        size="sm"
        title="Delete messages"
        :description="`Permanently delete ${selected.length} selected message(s)? This cannot be undone.`"
        @update:open="(value: boolean) => !value && (bulkOpen = false)"
    >
        <template #footer>
            <Button variant="outline" :disabled="bulkDeleting" @click="bulkOpen = false">Cancel</Button>
            <Button variant="destructive" :loading="bulkDeleting" @click="destroyBulk">
                <Trash2 />
                Delete messages
            </Button>
        </template>
    </Dialog>

    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete message"
        :description="`Permanently delete the message from “${deleteTarget?.name ?? ''}”? This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete message
            </Button>
        </template>
    </Dialog>
</template>
