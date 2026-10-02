<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronDown,
    FileText,
    FolderOpen,
    Layers,
    Pencil,
    PlayCircle,
    Plus,
    Star,
    Trash2,
} from 'lucide-vue-next';
import {
    Badge,
    Button,
    Card,
    Checkbox,
    Dialog,
    Input,
    Label,
    Select,
    Textarea,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { Course, Lesson, Section } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    course: Course;
    title?: string;
    description?: string;
}>();

const lessonTypeOptions = [
    { value: 'video', label: 'Video' },
    { value: 'text', label: 'Text' },
    { value: 'quiz', label: 'Quiz' },
    { value: 'assignment', label: 'Assignment' },
];

const lessonIcon = (type?: string | null) => {
    if (type === 'video') return PlayCircle;
    if (type === 'quiz') return Star;
    return FileText;
};

const totalLessons = computed(
    () => props.course.sections?.reduce((total, section) => total + section.lessons.length, 0) ?? 0,
);

/** Sections start collapsed on a long curriculum; the first one stays open. */
const collapsed = ref<Set<number>>(new Set());
function toggleSection(id: number) {
    const next = new Set(collapsed.value);
    next.has(id) ? next.delete(id) : next.add(id);
    collapsed.value = next;
}

// --- Section create / edit ------------------------------------------------
const sectionDialog = ref<{ open: boolean; section: Section | null }>({ open: false, section: null });

const sectionForm = useForm({ title: '', description: '' });

function openCreateSection() {
    sectionForm.reset();
    sectionForm.clearErrors();
    sectionDialog.value = { open: true, section: null };
}

function openEditSection(section: Section) {
    sectionForm.clearErrors();
    sectionForm.title = section.title;
    sectionForm.description = section.description ?? '';
    sectionDialog.value = { open: true, section };
}

function submitSection() {
    const target = sectionDialog.value.section;

    if (target) {
        sectionForm.put(routes.admin.courseSectionUpdate(props.course.id, target.id), {
            preserveScroll: true,
            onSuccess: () => (sectionDialog.value.open = false),
        });
        return;
    }

    sectionForm.post(routes.admin.courseSectionsStore(props.course.id), {
        preserveScroll: true,
        onSuccess: () => (sectionDialog.value.open = false),
    });
}

// --- Section delete -------------------------------------------------------
const deleteSection = ref<Section | null>(null);
const deletingSection = ref(false);

function destroySection() {
    if (!deleteSection.value) return;
    deletingSection.value = true;
    sectionForm.delete(routes.admin.courseSectionDestroy(props.course.id, deleteSection.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deletingSection.value = false;
            deleteSection.value = null;
        },
    });
}

// --- Lesson create / edit -------------------------------------------------
const lessonDialog = ref<{ open: boolean; section: Section | null; lesson: Lesson | null }>({
    open: false,
    section: null,
    lesson: null,
});

const lessonForm = useForm({
    title: '',
    type: 'video',
    video_url: '',
    video_duration: '' as string | number,
    is_preview: false,
    content: '',
});

function openCreateLesson(section: Section) {
    lessonForm.reset();
    lessonForm.clearErrors();
    lessonDialog.value = { open: true, section, lesson: null };
}

function openEditLesson(section: Section, lesson: Lesson) {
    lessonForm.clearErrors();
    lessonForm.title = lesson.title;
    lessonForm.type = lesson.type ?? 'video';
    lessonForm.video_url = lesson.video_url ?? '';
    lessonForm.video_duration = lesson.video_duration ?? '';
    lessonForm.is_preview = lesson.is_preview ?? false;
    lessonForm.content = lesson.content ?? '';
    lessonDialog.value = { open: true, section, lesson };
}

function submitLesson() {
    const { section, lesson } = lessonDialog.value;
    if (!section) return;

    if (lesson) {
        lessonForm.put(routes.admin.courseLessonUpdate(props.course.id, section.id, lesson.id), {
            preserveScroll: true,
            onSuccess: () => (lessonDialog.value.open = false),
        });
        return;
    }

    lessonForm.post(routes.admin.courseLessonsStore(props.course.id, section.id), {
        preserveScroll: true,
        onSuccess: () => (lessonDialog.value.open = false),
    });
}

// --- Lesson delete --------------------------------------------------------
const deleteLesson = ref<{ section: Section; lesson: Lesson } | null>(null);
const deletingLesson = ref(false);

function destroyLesson() {
    if (!deleteLesson.value) return;
    const { section, lesson } = deleteLesson.value;
    deletingLesson.value = true;
    lessonForm.delete(routes.admin.courseLessonDestroy(props.course.id, section.id, lesson.id), {
        preserveScroll: true,
        onFinish: () => {
            deletingLesson.value = false;
            deleteLesson.value = null;
        },
    });
}
</script>

<template>
    <a
        :href="routes.admin.course(course.id)"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to course
    </a>

    <div class="grid grid-cols-1 min-w-0 gap-6 lg:grid-cols-3">
        <div class="grid min-w-0 grid-cols-1 content-start gap-6 lg:col-span-2">
            <Card :padded="false">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold">Sections &amp; lessons</h2>
                            <p class="text-sm text-muted-foreground">
                                {{ course.sections?.length ?? 0 }} sections · {{ totalLessons }} lessons
                            </p>
                        </div>
                        <Button variant="brand" size="sm" @click="openCreateSection">
                            <Plus />
                            Add section
                        </Button>
                    </div>
                </template>

                <div class="px-6 pb-6">
                    <div v-if="!course.sections?.length" class="grid grid-cols-1 place-items-center gap-3 py-12 text-center">
                        <FolderOpen class="size-10 text-muted-foreground/50" />
                        <div>
                            <p class="font-medium">No sections yet</p>
                            <p class="text-sm text-muted-foreground">
                                Add your first section to start building the curriculum.
                            </p>
                        </div>
                        <Button variant="outline" size="sm" @click="openCreateSection">
                            <Plus />
                            Add section
                        </Button>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-4">
                        <div v-for="section in course.sections" :key="section.id" class="rounded-lg border border-border">
                            <div class="flex flex-wrap items-center gap-2 border-b border-border bg-muted/40 px-4 py-2.5">
                                <button
                                    type="button"
                                    class="flex min-w-0 flex-1 items-center gap-2 text-left"
                                    :aria-expanded="!collapsed.has(section.id)"
                                    @click="toggleSection(section.id)"
                                >
                                    <ChevronDown
                                        class="size-4 shrink-0 text-muted-foreground transition-transform"
                                        :class="collapsed.has(section.id) ? '-rotate-90' : ''"
                                    />
                                    <span class="truncate font-semibold">{{ section.title }}</span>
                                    <Badge variant="muted">{{ section.lessons.length }} lessons</Badge>
                                </button>

                                <div class="flex shrink-0 items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        aria-label="Add lesson"
                                        @click="openCreateLesson(section)"
                                    >
                                        <Plus />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        aria-label="Edit section"
                                        @click="openEditSection(section)"
                                    >
                                        <Pencil />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                        aria-label="Delete section"
                                        @click="deleteSection = section"
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </div>

                            <div v-show="!collapsed.has(section.id)">
                                <p
                                    v-if="section.description"
                                    class="border-b border-border px-4 py-2 text-sm text-muted-foreground"
                                >
                                    {{ section.description }}
                                </p>

                                <ul v-if="section.lessons.length" class="divide-y divide-border">
                                    <li
                                        v-for="lesson in section.lessons"
                                        :key="lesson.id"
                                        class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm"
                                    >
                                        <span class="flex min-w-0 items-center gap-2">
                                            <component
                                                :is="lessonIcon(lesson.type)"
                                                class="size-4 shrink-0 text-muted-foreground"
                                            />
                                            <span class="truncate">{{ lesson.title }}</span>
                                            <Badge v-if="lesson.is_preview" variant="success">Preview</Badge>
                                            <Badge v-if="lesson.video_duration" variant="muted">
                                                {{ Math.floor(lesson.video_duration / 60) }}:{{
                                                    String(lesson.video_duration % 60).padStart(2, '0')
                                                }}
                                            </Badge>
                                        </span>

                                        <span class="flex shrink-0 items-center gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                aria-label="Edit lesson"
                                                @click="openEditLesson(section, lesson)"
                                            >
                                                <Pencil />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                aria-label="Delete lesson"
                                                @click="deleteLesson = { section, lesson }"
                                            >
                                                <Trash2 />
                                            </Button>
                                        </span>
                                    </li>
                                </ul>

                                <p v-else class="px-4 py-3 text-sm text-muted-foreground">
                                    No lessons in this section yet.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Course info</h2>
                </template>

                <div class="grid grid-cols-1 gap-3">
                    <img
                        v-if="course.thumbnail_url"
                        :src="course.thumbnail_url"
                        :alt="course.title"
                        class="aspect-video w-full rounded-lg object-cover ring-1 ring-border"
                    />

                    <dl class="grid grid-cols-1 gap-2.5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted-foreground">Title</dt>
                            <dd class="truncate text-right font-medium">{{ course.title || 'Untitled' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted-foreground">Status</dt>
                            <dd class="text-right font-medium capitalize">{{ course.status }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted-foreground">Instructor</dt>
                            <dd class="text-right font-medium">{{ course.instructor?.name ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted-foreground">Category</dt>
                            <dd class="text-right font-medium">{{ course.category?.name ?? 'N/A' }}</dd>
                        </div>
                    </dl>

                    <Button :href="routes.admin.courseEdit(course.id)" variant="outline" block>
                        <Pencil />
                        Edit course
                    </Button>
                    <Button :href="routes.admin.courses()" variant="ghost" block>
                        <Layers />
                        All courses
                    </Button>
                </div>
            </Card>
        </div>
    </div>

    <!-- Section dialog (create + edit) -->
    <Dialog
        :open="sectionDialog.open"
        size="sm"
        :title="sectionDialog.section ? 'Edit section' : 'Add section'"
        @update:open="(value: boolean) => !value && (sectionDialog.open = false)"
    >
        <form class="grid grid-cols-1 gap-4" @submit.prevent="submitSection">
            <div class="grid grid-cols-1 gap-2">
                <Label for="section-title" required>Section title</Label>
                <Input
                    id="section-title"
                    v-model="sectionForm.title"
                    placeholder="e.g. Introduction"
                    :invalid="!!sectionForm.errors.title"
                />
                <p v-if="sectionForm.errors.title" class="text-sm text-destructive">{{ sectionForm.errors.title }}</p>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <Label for="section-description">Description</Label>
                <Textarea
                    id="section-description"
                    v-model="sectionForm.description"
                    :rows="3"
                    placeholder="Brief description of this section (optional)"
                />
                <p v-if="sectionForm.errors.description" class="text-sm text-destructive">
                    {{ sectionForm.errors.description }}
                </p>
            </div>
        </form>

        <template #footer>
            <Button variant="outline" :disabled="sectionForm.processing" @click="sectionDialog.open = false">
                Cancel
            </Button>
            <Button variant="brand" :loading="sectionForm.processing" @click="submitSection">
                {{ sectionDialog.section ? 'Save section' : 'Add section' }}
            </Button>
        </template>
    </Dialog>

    <!-- Lesson dialog (create + edit) -->
    <Dialog
        :open="lessonDialog.open"
        :title="lessonDialog.lesson ? 'Edit lesson' : `Add lesson${lessonDialog.section ? ` to “${lessonDialog.section.title}”` : ''}`"
        @update:open="(value: boolean) => !value && (lessonDialog.open = false)"
    >
        <form class="grid grid-cols-1 gap-4" @submit.prevent="submitLesson">
            <div class="grid grid-cols-1 gap-2">
                <Label for="lesson-title" required>Lesson title</Label>
                <Input
                    id="lesson-title"
                    v-model="lessonForm.title"
                    :invalid="!!lessonForm.errors.title"
                />
                <p v-if="lessonForm.errors.title" class="text-sm text-destructive">{{ lessonForm.errors.title }}</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid grid-cols-1 gap-2">
                    <Label for="lesson-type" required>Type</Label>
                    <Select id="lesson-type" v-model="lessonForm.type" :options="lessonTypeOptions" />
                    <p v-if="lessonForm.errors.type" class="text-sm text-destructive">{{ lessonForm.errors.type }}</p>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <Label for="lesson-duration">Duration (seconds)</Label>
                    <Input
                        id="lesson-duration"
                        v-model="lessonForm.video_duration"
                        type="number"
                        min="0"
                        :invalid="!!lessonForm.errors.video_duration"
                    />
                    <p v-if="lessonForm.errors.video_duration" class="text-sm text-destructive">
                        {{ lessonForm.errors.video_duration }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <Label for="lesson-video">Video URL</Label>
                <Input
                    id="lesson-video"
                    v-model="lessonForm.video_url"
                    placeholder="YouTube or Vimeo URL"
                    :invalid="!!lessonForm.errors.video_url"
                />
                <p v-if="lessonForm.errors.video_url" class="text-sm text-destructive">
                    {{ lessonForm.errors.video_url }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <Label for="lesson-content">Content</Label>
                <Textarea
                    id="lesson-content"
                    v-model="lessonForm.content"
                    :rows="4"
                    placeholder="Lesson content or description"
                />
                <p v-if="lessonForm.errors.content" class="text-sm text-destructive">{{ lessonForm.errors.content }}</p>
            </div>

            <div class="flex items-start gap-3">
                <Checkbox id="lesson-preview" v-model="lessonForm.is_preview" class="mt-0.5" />
                <div>
                    <Label for="lesson-preview" class="cursor-pointer font-normal">Free preview</Label>
                    <p class="text-xs text-muted-foreground">Visible to visitors before they enrol.</p>
                </div>
            </div>
        </form>

        <template #footer>
            <Button variant="outline" :disabled="lessonForm.processing" @click="lessonDialog.open = false">
                Cancel
            </Button>
            <Button variant="brand" :loading="lessonForm.processing" @click="submitLesson">
                {{ lessonDialog.lesson ? 'Save lesson' : 'Add lesson' }}
            </Button>
        </template>
    </Dialog>

    <!-- Delete section -->
    <Dialog
        :open="deleteSection !== null"
        size="sm"
        title="Delete section"
        :description="`Delete “${deleteSection?.title ?? ''}” and all ${deleteSection?.lessons.length ?? 0} of its lessons? This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteSection = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deletingSection" @click="deleteSection = null">Cancel</Button>
            <Button variant="destructive" :loading="deletingSection" @click="destroySection">
                <Trash2 />
                Delete section
            </Button>
        </template>
    </Dialog>

    <!-- Delete lesson -->
    <Dialog
        :open="deleteLesson !== null"
        size="sm"
        title="Delete lesson"
        :description="`Delete “${deleteLesson?.lesson.title ?? ''}”? This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteLesson = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deletingLesson" @click="deleteLesson = null">Cancel</Button>
            <Button variant="destructive" :loading="deletingLesson" @click="destroyLesson">
                <Trash2 />
                Delete lesson
            </Button>
        </template>
    </Dialog>
</template>
