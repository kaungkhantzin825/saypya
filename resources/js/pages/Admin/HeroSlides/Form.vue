<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Link2, Save, Type } from 'lucide-vue-next';
import { Alert, Button, Card, Checkbox, ImageUpload, Input, Label, Textarea } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: AdminLayout });

interface EditableSlide {
    id: number;
    title?: string | null;
    subtitle?: string | null;
    button_text?: string | null;
    button_link?: string | null;
    sort_order: number;
    is_active: boolean;
    image_url: string;
    /** True when `image` holds an external URL rather than a storage path. */
    is_external?: boolean;
}

const props = defineProps<{
    /** `null` when creating. */
    slide: EditableSlide | null;
    title?: string;
    description?: string;
}>();

const isEdit = computed(() => props.slide !== null);

const form = useForm({
    title: props.slide?.title ?? '',
    subtitle: props.slide?.subtitle ?? '',
    // Only pre-fill the URL box when the stored image really is an external URL —
    // otherwise it would post the storage path back as `image_url`.
    image_url: props.slide?.is_external ? (props.slide?.image_url ?? '') : '',
    button_text: props.slide?.button_text ?? '',
    button_link: props.slide?.button_link ?? '',
    sort_order: props.slide?.sort_order ?? 0,
    is_active: props.slide?.is_active ?? true,
    image: null as File | null,
});

/** Show whichever image is current: a freshly picked file, or the stored one. */
const currentImage = computed(() => props.slide?.image_url ?? null);

function submit() {
    if (isEdit.value && props.slide) {
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(routes.admin.heroSlideUpdate(props.slide.id));
        return;
    }

    form.post(routes.admin.heroSlideStore());
}
</script>

<template>
    <Link
        :href="routes.admin.heroSlides()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to hero slides
    </Link>

    <form class="grid grid-cols-1 min-w-0 gap-6 lg:grid-cols-3" @submit.prevent="submit">
        <Card class="min-w-0 lg:col-span-2">
            <template #header>
                <h2 class="text-base font-bold">Slide content</h2>
                <p class="text-sm text-muted-foreground">
                    All text is optional — a slide can be an image on its own.
                </p>
            </template>

            <div class="grid grid-cols-1 gap-5">
                <div class="grid grid-cols-1 gap-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        :icon="Type"
                        placeholder="Learn Anytime, Anywhere"
                        :invalid="!!form.errors.title"
                    />
                    <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <Label for="subtitle">Subtitle</Label>
                    <Textarea
                        id="subtitle"
                        v-model="form.subtitle"
                        :rows="2"
                        placeholder="A short supporting line shown under the title."
                    />
                    <p v-if="form.errors.subtitle" class="text-sm text-destructive">{{ form.errors.subtitle }}</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="button_text">Button text</Label>
                        <Input
                            id="button_text"
                            v-model="form.button_text"
                            placeholder="Browse courses"
                            :invalid="!!form.errors.button_text"
                        />
                        <p v-if="form.errors.button_text" class="text-sm text-destructive">
                            {{ form.errors.button_text }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="button_link">Button link</Label>
                        <Input
                            id="button_link"
                            v-model="form.button_link"
                            :icon="Link2"
                            placeholder="/courses"
                            :invalid="!!form.errors.button_link"
                        />
                        <p v-if="form.errors.button_link" class="text-sm text-destructive">
                            {{ form.errors.button_link }}
                        </p>
                    </div>
                </div>
            </div>
        </Card>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Image</h2>
                    <p class="text-sm text-muted-foreground">
                        Upload a file, or paste an external URL below.
                    </p>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <ImageUpload
                        :current-url="currentImage"
                        hint="PNG, JPG or WebP, up to 4 MB. Landscape works best."
                        aspect="video"
                        :error="form.errors.image"
                        @update:file="(file) => (form.image = file)"
                    />

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="image_url">…or an image URL</Label>
                        <Input
                            id="image_url"
                            v-model="form.image_url"
                            :icon="Link2"
                            placeholder="https://example.com/banner.jpg"
                            :invalid="!!form.errors.image_url"
                        />
                        <p v-if="form.errors.image_url" class="text-sm text-destructive">
                            {{ form.errors.image_url }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            An uploaded file takes precedence over this URL.
                        </p>
                    </div>

                    <Alert v-if="!form.image && !form.image_url && !currentImage" variant="warning">
                        <span class="flex items-start gap-2">
                            <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                            A slide needs an image — upload a file or paste a URL.
                        </span>
                    </Alert>
                </div>
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Placement</h2>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="sort_order">Sort order</Label>
                        <Input id="sort_order" v-model="form.sort_order" type="number" min="0" />
                        <p class="text-xs text-muted-foreground">Lower numbers appear first.</p>
                    </div>

                    <div class="flex items-start gap-3">
                        <Checkbox id="is_active" v-model="form.is_active" class="mt-0.5" />
                        <div>
                            <Label for="is_active" class="cursor-pointer font-normal">Active</Label>
                            <p class="text-xs text-muted-foreground">
                                Inactive slides are not shown in the carousel.
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <Alert v-if="form.hasErrors" variant="destructive" title="Please check the form">
                Some fields need your attention before this can be saved.
            </Alert>

            <div class="flex flex-col gap-2">
                <Button type="submit" variant="brand" size="lg" :loading="form.processing">
                    <Save />
                    {{ isEdit ? 'Save changes' : 'Create slide' }}
                </Button>
                <Button :href="routes.admin.heroSlides()" variant="ghost" :disabled="form.processing">
                    Cancel
                </Button>
            </div>
        </div>
    </form>
</template>
