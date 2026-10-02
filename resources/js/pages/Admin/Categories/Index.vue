<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, ImageOff } from 'lucide-vue-next';
import { Badge, Button, Card, DataTable, Dialog } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { DataTableColumn } from '@/types/ui';

defineOptions({ layout: AdminLayout });

/**
 * `courses_count` is the *published* course count (Category accessor), which is
 * what the previous Blade panel displayed.
 */
interface AdminCategory {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    icon?: string | null;
    image_url?: string | null;
    is_active: boolean;
    sort_order: number;
    courses_count?: number;
}

const props = defineProps<{
    categories: AdminCategory[];
    title?: string;
    description?: string;
}>();

const columns: DataTableColumn[] = [
    { key: 'sort_order', label: 'Order', align: 'center', hideOnMobile: true },
    { key: 'image', label: 'Image' },
    { key: 'name', label: 'Name' },
    { key: 'slug', label: 'Slug', hideOnMobile: true },
    { key: 'courses', label: 'Courses', align: 'center' },
    { key: 'status', label: 'Status' },
];

// --- Delete confirmation -------------------------------------------------
// Category has no SoftDeletes; the controller refuses when courses exist.
const deleteTarget = ref<AdminCategory | null>(null);
const deleting = ref(false);

const blocked = (category: Pick<AdminCategory, 'courses_count'> | null) =>
    (category?.courses_count ?? 0) > 0;

function confirmDelete(category: AdminCategory) {
    deleteTarget.value = category;
}

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(routes.admin.categoryDestroy(deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <div class="mb-5 flex flex-wrap items-center justify-end gap-2">
        <Button :href="routes.admin.categoryCreate()" variant="brand">
            <Plus />
            Add category
        </Button>
    </div>

    <Card class="min-w-0" :padded="false">
        <template #header>
            <div>
                <h2 class="text-base font-bold">All categories</h2>
                <p class="text-sm text-muted-foreground">
                    {{ props.categories.length }} {{ props.categories.length === 1 ? 'category' : 'categories' }}
                </p>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <DataTable
                :columns="columns"
                :rows="props.categories"
                :row-key="(row) => row.id"
                has-actions
                empty-title="No categories yet"
                empty-description="Create a category to group courses under."
            >
                <template #cell-sort_order="{ row }">
                    <span class="text-muted-foreground">{{ row.sort_order }}</span>
                </template>

                <template #cell-image="{ row }">
                    <img
                        v-if="row.image_url"
                        :src="row.image_url"
                        :alt="row.name"
                        class="h-12 w-12 shrink-0 rounded-md object-cover ring-1 ring-border"
                        loading="lazy"
                    />
                    <span
                        v-else
                        class="flex size-12 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
                        title="No image"
                    >
                        <ImageOff class="size-4" />
                    </span>
                </template>

                <template #cell-name="{ row }">
                    <div class="flex items-center gap-2">
                        <!-- `icon` is a FontAwesome class from the legacy panel. -->
                        <i v-if="row.icon" :class="row.icon" class="shrink-0 text-muted-foreground" aria-hidden="true" />
                        <span class="font-medium">{{ row.name }}</span>
                    </div>
                </template>

                <template #cell-slug="{ row }">
                    <code class="rounded bg-muted px-1.5 py-0.5 text-xs text-muted-foreground">{{ row.slug }}</code>
                </template>

                <template #cell-courses="{ row }">
                    <Badge variant="info">
                        {{ row.courses_count ?? 0 }}
                        {{ (row.courses_count ?? 0) === 1 ? 'course' : 'courses' }}
                    </Badge>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="row.is_active ? 'success' : 'muted'">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>

                <template #actions="{ row }">
                    <Button
                        :href="routes.admin.categoryEdit(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Edit category"
                    >
                        <Pencil />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        :aria-label="blocked(row) ? 'Cannot delete — category has courses' : 'Delete category'"
                        :title="blocked(row) ? 'Cannot delete: this category still has courses' : 'Delete'"
                        @click="confirmDelete(row)"
                    >
                        <Trash2 />
                    </Button>
                </template>
            </DataTable>
        </div>
    </Card>

    <!-- Delete confirmation -->
    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete category"
        :description="
            blocked(deleteTarget)
                ? `“${deleteTarget?.name}” still has ${deleteTarget?.courses_count} published course(s). The server will refuse to delete it — move or unpublish those courses first.`
                : `Permanently delete “${deleteTarget?.name}”? This cannot be undone.`
        "
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button
                variant="destructive"
                :loading="deleting"
                :disabled="blocked(deleteTarget)"
                @click="destroy"
            >
                <Trash2 />
                Delete category
            </Button>
        </template>
    </Dialog>
</template>
