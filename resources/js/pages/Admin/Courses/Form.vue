<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, Trash2, Type } from 'lucide-vue-next';
import {
    Alert,
    Button,
    Card,
    Checkbox,
    ImageUpload,
    Input,
    Label,
    Select,
    Textarea,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { Category, Course, InstructorSummary } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    /** `null` when creating. */
    course: Course | null;
    categories: Category[];
    instructors: InstructorSummary[];
    title?: string;
    description?: string;
}>();

const isEdit = computed(() => props.course !== null);

const form = useForm({
    title: props.course?.title ?? '',
    category_id: props.course?.category?.id ? String(props.course.category.id) : '',
    instructor_id: props.course?.instructor?.id ? String(props.course.instructor.id) : '',
    short_description: props.course?.short_description ?? '',
    description: props.course?.description ?? '',
    what_you_learn: [...(props.course?.what_you_learn ?? [])] as string[],
    requirements: [...(props.course?.requirements ?? [])] as string[],
    thumbnail: null as File | null,
    price: props.course?.price != null ? String(props.course.price) : '0',
    discount_price: props.course?.discount_price != null ? String(props.course.discount_price) : '',
    level: props.course?.level ?? 'beginner',
    language: props.course?.language ?? 'Myanmar',
    status: props.course?.status ?? 'draft',
    preview_video: props.course?.preview_video_url ?? '',
    is_featured: props.course?.is_featured ?? false,
});

const currentThumbnail = computed(() => props.course?.thumbnail_url ?? null);

// reka-ui throws if a SelectItem carries an empty-string value (it reserves ""
// to mean "cleared"), so the empty row is expressed with the `placeholder`
// prop on <Select> instead of as a real option.
const categoryOptions = computed(() =>
    props.categories.map((item) => ({ value: String(item.id), label: item.name })),
);

const instructorOptions = computed(() =>
    props.instructors.map((item) => ({ value: String(item.id), label: item.name })),
);

const levelOptions = [
    { value: 'beginner', label: 'Beginner' },
    { value: 'intermediate', label: 'Intermediate' },
    { value: 'advanced', label: 'Advanced' },
];

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' },
];

// --- Repeatable text rows (what you'll learn / requirements) ---------------
function addRow(list: string[]) {
    list.push('');
}

function removeRow(list: string[], index: number) {
    list.splice(index, 1);
}

function submit() {
    if (isEdit.value && props.course) {
        // A file upload cannot be sent as a real PUT, so post with _method spoofing.
        form
            .transform((data) => ({
                ...data,
                _method: 'put',
                // Strip blank rows so the backend's array_filter() is not the only guard.
                what_you_learn: data.what_you_learn.filter((item) => item.trim() !== ''),
                requirements: data.requirements.filter((item) => item.trim() !== ''),
            }))
            .post(routes.admin.courseUpdate(props.course.id));
        return;
    }

    form
        .transform((data) => ({
            ...data,
            what_you_learn: data.what_you_learn.filter((item) => item.trim() !== ''),
            requirements: data.requirements.filter((item) => item.trim() !== ''),
        }))
        .post(routes.admin.courseStore());
}
</script>

<template>
    <Link
        :href="routes.admin.courses()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to courses
    </Link>

    <form class="grid grid-cols-1 min-w-0 gap-6 lg:grid-cols-3" @submit.prevent="submit">
        <Card class="min-w-0 lg:col-span-2">
            <template #header>
                <h2 class="text-base font-bold">Course information</h2>
                <p class="text-sm text-muted-foreground">
                    The public URL slug is generated automatically from the title.
                </p>
            </template>

            <div class="grid grid-cols-1 gap-5">
                <div class="grid grid-cols-1 gap-2">
                    <Label for="title" required>Course title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        :icon="Type"
                        placeholder="Complete Web Development Bootcamp"
                        :invalid="!!form.errors.title"
                    />
                    <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="category_id" required>Category</Label>
                        <Select
                            id="category_id"
                            v-model="form.category_id"
                            :options="categoryOptions"
                            placeholder="Select a category"
                        />
                        <p v-if="form.errors.category_id" class="text-sm text-destructive">
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="instructor_id" required>Instructor</Label>
                        <Select
                            id="instructor_id"
                            v-model="form.instructor_id"
                            :options="instructorOptions"
                            placeholder="Select an instructor"
                        />
                        <p v-if="form.errors.instructor_id" class="text-sm text-destructive">
                            {{ form.errors.instructor_id }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <Label for="short_description">Short description</Label>
                    <Textarea
                        id="short_description"
                        v-model="form.short_description"
                        :rows="2"
                        maxlength="500"
                        placeholder="One or two lines shown on the course card."
                    />
                    <p v-if="form.errors.short_description" class="text-sm text-destructive">
                        {{ form.errors.short_description }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <Label for="description" required>Full description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        :rows="6"
                        placeholder="What the course covers, who it is for, and what students will build."
                        :invalid="!!form.errors.description"
                    />
                    <p v-if="form.errors.description" class="text-sm text-destructive">
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- What you'll learn -->
                <div class="grid grid-cols-1 gap-2">
                    <Label>What you'll learn</Label>
                    <div class="grid grid-cols-1 gap-2">
                        <div v-for="(_, index) in form.what_you_learn" :key="`learn-${index}`" class="flex gap-2">
                            <Input
                                v-model="form.what_you_learn[index]"
                                placeholder="Enter a learning outcome"
                                class="flex-1"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="shrink-0 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                aria-label="Remove outcome"
                                @click="removeRow(form.what_you_learn, index)"
                            >
                                <Trash2 />
                            </Button>
                        </div>
                        <p v-if="form.what_you_learn.length === 0" class="text-sm text-muted-foreground">
                            No outcomes added yet.
                        </p>
                    </div>
                    <Button type="button" variant="outline" size="sm" class="justify-self-start" @click="addRow(form.what_you_learn)">
                        <Plus />
                        Add outcome
                    </Button>
                </div>

                <!-- Requirements -->
                <div class="grid grid-cols-1 gap-2">
                    <Label>Requirements</Label>
                    <div class="grid grid-cols-1 gap-2">
                        <div v-for="(_, index) in form.requirements" :key="`req-${index}`" class="flex gap-2">
                            <Input
                                v-model="form.requirements[index]"
                                placeholder="Enter a requirement"
                                class="flex-1"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="shrink-0 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                aria-label="Remove requirement"
                                @click="removeRow(form.requirements, index)"
                            >
                                <Trash2 />
                            </Button>
                        </div>
                        <p v-if="form.requirements.length === 0" class="text-sm text-muted-foreground">
                            No requirements added yet.
                        </p>
                    </div>
                    <Button type="button" variant="outline" size="sm" class="justify-self-start" @click="addRow(form.requirements)">
                        <Plus />
                        Add requirement
                    </Button>
                </div>
            </div>
        </Card>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Thumbnail</h2>
                </template>

                <ImageUpload
                    :current-url="currentThumbnail"
                    hint="PNG or JPG, up to 2 MB. Shown on course cards."
                    aspect="video"
                    :error="form.errors.thumbnail"
                    @update:file="(file) => (form.thumbnail = file)"
                />
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Pricing &amp; level</h2>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="grid grid-cols-1 gap-2">
                            <Label for="price" required>Price (MMK)</Label>
                            <Input
                                id="price"
                                v-model="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                :invalid="!!form.errors.price"
                            />
                            <p v-if="form.errors.price" class="text-sm text-destructive">{{ form.errors.price }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <Label for="discount_price">Discount price</Label>
                            <Input
                                id="discount_price"
                                v-model="form.discount_price"
                                type="number"
                                min="0"
                                step="0.01"
                                :invalid="!!form.errors.discount_price"
                            />
                            <p v-if="form.errors.discount_price" class="text-sm text-destructive">
                                {{ form.errors.discount_price }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="level" required>Level</Label>
                        <Select id="level" v-model="form.level" :options="levelOptions" />
                        <p v-if="form.errors.level" class="text-sm text-destructive">{{ form.errors.level }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="language" required>Language</Label>
                        <Input id="language" v-model="form.language" :invalid="!!form.errors.language" />
                        <p v-if="form.errors.language" class="text-sm text-destructive">{{ form.errors.language }}</p>
                    </div>
                </div>
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Publishing</h2>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="status" required>Status</Label>
                        <Select id="status" v-model="form.status" :options="statusOptions" />
                        <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="preview_video">Preview video URL</Label>
                        <Input
                            id="preview_video"
                            v-model="form.preview_video"
                            type="url"
                            placeholder="https://youtube.com/..."
                            :invalid="!!form.errors.preview_video"
                        />
                        <p v-if="form.errors.preview_video" class="text-sm text-destructive">
                            {{ form.errors.preview_video }}
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <Checkbox id="is_featured" v-model="form.is_featured" class="mt-0.5" />
                        <div>
                            <Label for="is_featured" class="cursor-pointer font-normal">Featured course</Label>
                            <p class="text-xs text-muted-foreground">
                                Featured courses are promoted on the home page.
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
                    {{ isEdit ? 'Save changes' : 'Create course' }}
                </Button>
                <Button :href="routes.admin.courses()" variant="ghost" :disabled="form.processing">
                    Cancel
                </Button>
            </div>
        </div>
    </form>
</template>
