<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowRight, Eye, EyeOff, Images, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { Badge, Button, Card, Dialog, EmptyState } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: AdminLayout });

interface AdminHeroSlide {
    id: number;
    title?: string | null;
    subtitle?: string | null;
    image_url: string;
    button_text?: string | null;
    button_link?: string | null;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<{
    slides: AdminHeroSlide[];
    title?: string;
    description?: string;
}>();

function toggle(slide: AdminHeroSlide) {
    router.patch(routes.admin.heroSlideToggle(slide.id), {}, { preserveScroll: true });
}

// --- Delete confirmation -------------------------------------------------
// HeroSlide has no SoftDeletes, and the controller unlinks local uploads.
const deleteTarget = ref<AdminHeroSlide | null>(null);
const deleting = ref(false);

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(routes.admin.heroSlideDestroy(deleteTarget.value.id), {
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
        <Button :href="routes.admin.heroSlideCreate()" variant="brand">
            <Plus />
            Add slide
        </Button>
    </div>

    <!-- The Blade panel used a card grid here; it suits image previews better than a table. -->
    <div v-if="props.slides.length === 0">
        <Card>
            <EmptyState
                :icon="Images"
                title="No slides yet"
                description="The homepage shows a default banner until you add one."
                class="border-0 bg-transparent py-6"
            />
        </Card>
    </div>

    <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <Card v-for="slide in props.slides" :key="slide.id" class="flex min-w-0 flex-col" :padded="false">
            <div class="relative">
                <img
                    :src="slide.image_url"
                    :alt="slide.title || 'Slide image'"
                    class="h-52 w-full rounded-t-xl object-cover"
                    loading="lazy"
                />
                <Badge
                    :variant="slide.is_active ? 'success' : 'muted'"
                    class="absolute right-2.5 top-2.5 shadow-sm"
                >
                    {{ slide.is_active ? 'Active' : 'Inactive' }}
                </Badge>
                <Badge variant="default" class="absolute left-2.5 top-2.5 bg-black/70 text-white shadow-sm">
                    Order: {{ slide.sort_order }}
                </Badge>
            </div>

            <div class="flex flex-1 flex-col gap-3 p-5">
                <div>
                    <h3 v-if="slide.title" class="text-base font-bold">{{ slide.title }}</h3>
                    <h3 v-else class="text-base font-bold italic text-muted-foreground">
                        Image only (no text)
                    </h3>
                    <p v-if="slide.subtitle" class="mt-1 line-clamp-3 text-sm text-muted-foreground">
                        {{ slide.subtitle }}
                    </p>
                </div>

                <div v-if="slide.button_text" class="flex flex-wrap items-center gap-2">
                    <Badge variant="info">
                        <span class="inline-flex items-center gap-1">
                            {{ slide.button_text }}
                            <ArrowRight class="size-3" />
                        </span>
                    </Badge>
                    <span v-if="slide.button_link" class="truncate text-xs text-muted-foreground">
                        {{ slide.button_link }}
                    </span>
                </div>

                <div class="mt-auto flex items-center justify-between gap-2 pt-2">
                    <Button
                        :href="routes.admin.heroSlideEdit(slide.id)"
                        variant="outline"
                        size="sm"
                        class="flex-1"
                    >
                        <Pencil />
                        Edit
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        :aria-label="slide.is_active ? 'Deactivate slide' : 'Activate slide'"
                        :title="slide.is_active ? 'Deactivate' : 'Activate'"
                        @click="toggle(slide)"
                    >
                        <EyeOff v-if="slide.is_active" />
                        <Eye v-else />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        aria-label="Delete slide"
                        @click="deleteTarget = slide"
                    >
                        <Trash2 />
                    </Button>
                </div>
            </div>
        </Card>
    </div>

    <!-- Delete confirmation -->
    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete slide"
        :description="`Permanently delete “${deleteTarget?.title || 'this image-only slide'}”? Any uploaded image file is removed too. This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete slide
            </Button>
        </template>
    </Dialog>
</template>
