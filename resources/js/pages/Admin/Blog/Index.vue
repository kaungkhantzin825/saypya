<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ExternalLink, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import {
    Avatar,
    Badge,
    Button,
    Card,
    DataTable,
    Dialog,
    Pagination,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { DataTableColumn } from '@/types/ui';
import type { BlogPost, Paginated } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    posts: Paginated<BlogPost>;
    title?: string;
    description?: string;
}>();

const columns: DataTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'author', label: 'Author', hideOnMobile: true },
    { key: 'status', label: 'Status' },
    { key: 'views_count', label: 'Views', align: 'center', hideOnMobile: true },
    { key: 'published_at', label: 'Published', hideOnMobile: true },
];

const statusVariant = (status?: string) => (status === 'published' ? 'success' : 'warning');

const formatDate = (value?: string | null) =>
    value ? new Date(value).toLocaleDateString() : '—';

// `BlogPost` has no SoftDeletes, and the controller also unlinks the featured
// image from disk, so this really is permanent.
const deleteTarget = ref<BlogPost | null>(null);
const deleting = ref(false);

function confirmDelete(post: BlogPost) {
    deleteTarget.value = post;
}

function destroy() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(routes.admin.blogDestroy(deleteTarget.value.id), {
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
        <Button :href="routes.admin.blogCreate()" variant="brand">
            <Plus />
            New post
        </Button>
    </div>

    <Card class="min-w-0" :padded="false">
        <template #header>
            <div>
                <h2 class="text-base font-bold">All posts</h2>
                <p class="text-sm text-muted-foreground">
                    {{ props.posts.total }} {{ props.posts.total === 1 ? 'post' : 'posts' }}
                </p>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <DataTable
                :columns="columns"
                :rows="props.posts.data"
                :row-key="(row) => row.id"
                has-actions
                empty-title="No blog posts yet"
                empty-description="Create your first post to get started."
            >
                <template #cell-title="{ row }">
                    <div class="flex min-w-0 items-center gap-3">
                        <img
                            v-if="row.image_url"
                            :src="row.image_url"
                            :alt="row.title"
                            class="hidden h-10 w-16 shrink-0 rounded-md object-cover ring-1 ring-border sm:block"
                            loading="lazy"
                        />
                        <div class="min-w-0">
                            <a
                                :href="routes.admin.blogEdit(row.id)"
                                class="line-clamp-2 font-medium hover:text-brand-600 hover:underline"
                            >
                                {{ row.title || 'Untitled post' }}
                            </a>
                            <p class="mt-0.5 text-xs text-muted-foreground sm:hidden">
                                {{ formatDate(row.published_at) }}
                            </p>
                        </div>
                    </div>
                </template>

                <template #cell-author="{ row }">
                    <div class="flex items-center gap-2">
                        <Avatar :src="row.author?.avatar_url" :name="row.author?.name" size="xs" />
                        <span class="truncate text-muted-foreground">{{ row.author?.name ?? 'Unknown' }}</span>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="statusVariant(row.status)" class="capitalize">{{ row.status ?? 'draft' }}</Badge>
                </template>

                <template #cell-views_count="{ row }">
                    <span class="text-muted-foreground">{{ row.views_count ?? 0 }}</span>
                </template>

                <template #cell-published_at="{ row }">
                    <span class="whitespace-nowrap text-muted-foreground">{{ formatDate(row.published_at) }}</span>
                </template>

                <template #actions="{ row }">
                    <Button
                        v-if="row.status === 'published'"
                        :href="routes.blogPost(row.slug)"
                        external
                        variant="ghost"
                        size="icon-sm"
                        aria-label="View post"
                    >
                        <ExternalLink />
                    </Button>

                    <Button
                        :href="routes.admin.blogEdit(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Edit post"
                    >
                        <Pencil />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        aria-label="Delete post"
                        @click="confirmDelete(row)"
                    >
                        <Trash2 />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.posts.links"
                        :from="props.posts.from"
                        :to="props.posts.to"
                        :total="props.posts.total"
                    />
                </template>
            </DataTable>
        </div>
    </Card>

    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete post"
        :description="`Permanently delete “${deleteTarget?.title ?? 'this post'}”? Its featured image is removed too. This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete post
            </Button>
        </template>
    </Dialog>
</template>
