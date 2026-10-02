<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Type } from 'lucide-vue-next';
import {
    Alert,
    Button,
    Card,
    ImageUpload,
    Input,
    Label,
    Select,
    Textarea,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { BlogPost } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    /** `null` when creating. */
    post: BlogPost | null;
    title?: string;
    description?: string;
}>();

const isEdit = computed(() => props.post !== null);

const form = useForm({
    title: props.post?.title ?? '',
    excerpt: props.post?.excerpt ?? '',
    content: props.post?.content ?? '',
    status: props.post?.status ?? 'draft',
    featured_image: null as File | null,
});

const currentImage = computed(() => props.post?.image_url ?? null);

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

function submit() {
    if (isEdit.value && props.post) {
        // A file upload cannot be sent as a real PUT, so post with _method spoofing.
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(routes.admin.blogUpdate(props.post.id));
        return;
    }

    form.post(routes.admin.blogStore());
}
</script>

<template>
    <Link
        :href="routes.admin.blog()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to blog
    </Link>

    <form class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-3" @submit.prevent="submit">
        <div class="grid min-w-0 grid-cols-1 content-start gap-6 lg:col-span-2">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Post content</h2>
                    <p class="text-sm text-muted-foreground">
                        The public URL slug is generated automatically from the title.
                    </p>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="title" required>Title</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            :icon="Type"
                            placeholder="How to get started with Laravel"
                            :invalid="!!form.errors.title"
                        />
                        <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="excerpt">Excerpt</Label>
                        <Textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            :rows="3"
                            placeholder="A short summary shown on the blog index."
                        />
                        <p v-if="form.errors.excerpt" class="text-sm text-destructive">{{ form.errors.excerpt }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="content" required>Content</Label>
                        <Textarea
                            id="content"
                            v-model="form.content"
                            :rows="14"
                            placeholder="Write the post…"
                            :invalid="!!form.errors.content"
                        />
                        <p v-if="form.errors.content" class="text-sm text-destructive">{{ form.errors.content }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Publishing</h2>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="status" required>Status</Label>
                        <Select id="status" v-model="form.status" :options="statusOptions" />
                        <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                        <p class="text-xs text-muted-foreground">
                            Publishing sets the published date the first time.
                        </p>
                    </div>

                    <div v-if="post?.published_at" class="rounded-lg bg-muted px-3 py-2 text-xs text-muted-foreground">
                        Published {{ new Date(post.published_at).toLocaleString() }}
                    </div>
                </div>
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Featured image</h2>
                </template>

                <ImageUpload
                    :current-url="currentImage"
                    hint="PNG or JPG, up to 2 MB."
                    aspect="video"
                    :error="form.errors.featured_image"
                    @update:file="(file) => (form.featured_image = file)"
                />
            </Card>

            <Alert v-if="form.hasErrors" variant="destructive" title="Please check the form">
                Some fields need your attention before this can be saved.
            </Alert>

            <div class="flex flex-col gap-2">
                <Button type="submit" variant="brand" size="lg" :loading="form.processing">
                    <Save />
                    {{ isEdit ? 'Save changes' : 'Create post' }}
                </Button>
                <Button :href="routes.admin.blog()" variant="ghost" :disabled="form.processing">Cancel</Button>
            </div>
        </div>
    </form>
</template>
