<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Type } from 'lucide-vue-next';
import { Alert, Button, Card, Checkbox, ImageUpload, Input, Label, Textarea } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: AdminLayout });

interface EditableCategory {
    id: number;
    name: string;
    description?: string | null;
    icon?: string | null;
    sort_order: number;
    is_active: boolean;
    image_url?: string | null;
}

const props = defineProps<{
    /** `null` when creating. */
    category: EditableCategory | null;
    title?: string;
    description?: string;
}>();

const isEdit = computed(() => props.category !== null);

const form = useForm({
    name: props.category?.name ?? '',
    description: props.category?.description ?? '',
    icon: props.category?.icon ?? '',
    sort_order: props.category?.sort_order ?? 0,
    is_active: props.category?.is_active ?? true,
    image: null as File | null,
});

/** Preview the stored image, or the freshly picked file (ImageUpload handles both). */
const currentImage = computed(() => props.category?.image_url ?? null);

function submit() {
    if (isEdit.value && props.category) {
        // A file upload cannot be sent as a real PUT, so post with _method spoofing.
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(routes.admin.categoryUpdate(props.category.id));
        return;
    }

    form.post(routes.admin.categoryStore());
}
</script>

<template>
    <Link
        :href="routes.admin.categories()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to categories
    </Link>

    <form class="grid grid-cols-1 min-w-0 gap-6 lg:grid-cols-3" @submit.prevent="submit">
        <Card class="min-w-0 lg:col-span-2">
            <template #header>
                <h2 class="text-base font-bold">Category details</h2>
                <p class="text-sm text-muted-foreground">
                    The slug is generated automatically from the name.
                </p>
            </template>

            <div class="grid grid-cols-1 gap-5">
                <div class="grid grid-cols-1 gap-2">
                    <Label for="name" required>Category name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        :icon="Type"
                        placeholder="Web Development"
                        :invalid="!!form.errors.name"
                        :aria-invalid="!!form.errors.name"
                    />
                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        :rows="3"
                        placeholder="What kind of courses belong in this category?"
                    />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="icon">Icon class</Label>
                        <Input
                            id="icon"
                            v-model="form.icon"
                            placeholder="fas fa-laptop-code"
                            :invalid="!!form.errors.icon"
                        />
                        <p v-if="form.errors.icon" class="text-sm text-destructive">{{ form.errors.icon }}</p>
                        <p v-else class="text-xs text-muted-foreground">
                            FontAwesome class, e.g. <code>fas fa-laptop-code</code>. Optional.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="sort_order">Sort order</Label>
                        <Input
                            id="sort_order"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            :invalid="!!form.errors.sort_order"
                        />
                        <p v-if="form.errors.sort_order" class="text-sm text-destructive">
                            {{ form.errors.sort_order }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">Lower numbers appear first.</p>
                    </div>
                </div>
            </div>
        </Card>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Image</h2>
                </template>

                <ImageUpload
                    :current-url="currentImage"
                    hint="PNG or JPG, up to 2 MB."
                    aspect="square"
                    :error="form.errors.image"
                    @update:file="(file) => (form.image = file)"
                />
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Visibility</h2>
                </template>

                <div class="flex items-start gap-3">
                    <Checkbox id="is_active" v-model="form.is_active" class="mt-0.5" />
                    <div>
                        <Label for="is_active" class="cursor-pointer font-normal">Active</Label>
                        <p class="text-xs text-muted-foreground">
                            Inactive categories are hidden from the public site.
                        </p>
                    </div>
                </div>
            </Card>

            <Alert v-if="form.hasErrors" variant="destructive" title="Please check the form">
                Some fields need your attention before this can be saved.
            </Alert>

            <div class="flex flex-col gap-2">
                <Button type="submit" variant="brand" size="lg" :loading="form.processing">
                    <Save />
                    {{ isEdit ? 'Save changes' : 'Create category' }}
                </Button>
                <Button :href="routes.admin.categories()" variant="ghost" :disabled="form.processing">
                    Cancel
                </Button>
            </div>
        </div>
    </form>
</template>
