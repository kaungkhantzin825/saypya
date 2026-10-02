<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Archive,
    ArrowLeft,
    BookOpen,
    Check,
    FileText,
    Layers,
    Pencil,
    PlayCircle,
    Star,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { Badge, Button, Card, Dialog, Separator } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import { formatMMK } from '@/lib/utils';
import type { Course } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    course: Course;
    title?: string;
    description?: string;
}>();

const statusVariant = (value: string) =>
    value === 'published' ? 'success' : value === 'draft' ? 'warning' : 'muted';

const lessonCount = computed(
    () => props.course.sections?.reduce((total, section) => total + section.lessons.length, 0) ?? 0,
);

const lessonIcon = (type?: string | null) => {
    if (type === 'video') return PlayCircle;
    if (type === 'quiz') return Star;
    return FileText;
};

function approve() {
    router.patch(routes.admin.courseApprove(props.course.id), {}, { preserveScroll: true });
}

function archive() {
    router.patch(routes.admin.courseArchive(props.course.id), {}, { preserveScroll: true });
}

// `Course` does NOT use SoftDeletes and the controller unlinks the thumbnail,
// so this really is permanent.
const deleteOpen = ref(false);
const deleting = ref(false);

function destroy() {
    deleting.value = true;
    router.delete(routes.admin.courseDestroy(props.course.id), {
        onFinish: () => {
            deleting.value = false;
            deleteOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a
            :href="routes.admin.courses()"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            Back to courses
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <Button :href="routes.admin.courseContent(course.id)" variant="outline">
                <Layers />
                Manage content
            </Button>
            <Button :href="routes.admin.courseEdit(course.id)" variant="brand">
                <Pencil />
                Edit course
            </Button>
        </div>
    </div>

    <div class="grid grid-cols-1 min-w-0 gap-6 lg:grid-cols-3">
        <div class="grid min-w-0 grid-cols-1 content-start gap-6 lg:col-span-2">
            <Card :padded="false" class="overflow-hidden">
                <img
                    v-if="course.thumbnail_url"
                    :src="course.thumbnail_url"
                    :alt="course.title"
                    class="h-56 w-full object-cover sm:h-72"
                />
                <div v-else class="grid grid-cols-1 h-40 place-items-center bg-muted text-muted-foreground">
                    <BookOpen class="size-8" />
                </div>

                <div class="grid grid-cols-1 gap-3 p-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge :variant="statusVariant(course.status)" class="capitalize">{{ course.status }}</Badge>
                        <Badge v-if="course.is_featured" variant="warning">Featured</Badge>
                        <Badge variant="muted" class="capitalize">{{ course.level }}</Badge>
                    </div>
                    <h2 class="text-xl font-extrabold tracking-tight">
                        {{ course.title || 'Untitled course' }}
                    </h2>
                    <p v-if="course.short_description" class="text-sm text-muted-foreground">
                        {{ course.short_description }}
                    </p>
                </div>
            </Card>

            <Card v-if="course.description">
                <template #header>
                    <h2 class="text-base font-bold">Description</h2>
                </template>
                <p class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground">
                    {{ course.description }}
                </p>
            </Card>

            <Card v-if="course.what_you_learn?.length || course.requirements?.length">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div v-if="course.what_you_learn?.length">
                        <h3 class="mb-3 text-sm font-bold">What you'll learn</h3>
                        <ul class="grid grid-cols-1 gap-2">
                            <li
                                v-for="item in course.what_you_learn"
                                :key="item"
                                class="flex items-start gap-2 text-sm text-muted-foreground"
                            >
                                <Check class="mt-0.5 size-4 shrink-0 text-success" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>

                    <div v-if="course.requirements?.length">
                        <h3 class="mb-3 text-sm font-bold">Requirements</h3>
                        <ul class="grid grid-cols-1 gap-2">
                            <li
                                v-for="item in course.requirements"
                                :key="item"
                                class="flex items-start gap-2 text-sm text-muted-foreground"
                            >
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-muted-foreground/50" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>
            </Card>

            <Card :padded="false">
                <template #header>
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold">Curriculum</h2>
                        <span class="text-sm text-muted-foreground">
                            {{ course.sections?.length ?? 0 }} sections · {{ lessonCount }} lessons
                        </span>
                    </div>
                </template>

                <div class="px-6 pb-6">
                    <p v-if="!course.sections?.length" class="py-6 text-center text-sm text-muted-foreground">
                        No content added yet.
                    </p>

                    <div v-else class="grid grid-cols-1 gap-4">
                        <div v-for="section in course.sections" :key="section.id" class="rounded-lg border border-border">
                            <div class="flex items-center justify-between gap-3 border-b border-border bg-muted/40 px-4 py-2.5">
                                <p class="font-semibold">{{ section.title }}</p>
                                <span class="text-xs text-muted-foreground">
                                    {{ section.lessons.length }} lessons
                                </span>
                            </div>
                            <ul class="divide-y divide-border">
                                <li
                                    v-for="lesson in section.lessons"
                                    :key="lesson.id"
                                    class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm"
                                >
                                    <span class="flex min-w-0 items-center gap-2">
                                        <component :is="lessonIcon(lesson.type)" class="size-4 shrink-0 text-muted-foreground" />
                                        <span class="truncate">{{ lesson.title }}</span>
                                        <Badge v-if="lesson.is_preview" variant="success">Preview</Badge>
                                    </span>
                                    <span class="shrink-0 text-xs text-muted-foreground">
                                        {{ lesson.formatted_duration ?? '—' }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Reviews ({{ course.reviews?.length ?? 0 }})</h2>
                </template>

                <p v-if="!course.reviews?.length" class="py-4 text-center text-sm text-muted-foreground">
                    No reviews yet.
                </p>

                <ul v-else class="grid grid-cols-1 gap-4">
                    <li v-for="review in course.reviews" :key="review.id" class="grid grid-cols-1 gap-1.5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-medium">{{ review.user?.name ?? 'Anonymous' }}</p>
                            <span class="flex items-center gap-0.5" :aria-label="`${review.rating} out of 5`">
                                <Star
                                    v-for="index in 5"
                                    :key="index"
                                    class="size-3.5"
                                    :class="index <= review.rating ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30'"
                                />
                            </span>
                        </div>
                        <p v-if="review.comment" class="text-sm text-muted-foreground">{{ review.comment }}</p>
                    </li>
                </ul>
            </Card>
        </div>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Actions</h2>
                </template>

                <div class="grid grid-cols-1 gap-2">
                    <Button v-if="course.status === 'draft'" variant="brand" block @click="approve">
                        <Check />
                        Approve &amp; publish
                    </Button>
                    <Button v-else-if="course.status === 'published'" variant="outline" block @click="archive">
                        <Archive />
                        Archive course
                    </Button>
                    <Button :href="routes.admin.courseEdit(course.id)" variant="outline" block>
                        <Pencil />
                        Edit course
                    </Button>
                    <Button
                        variant="ghost"
                        block
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        @click="deleteOpen = true"
                    >
                        <Trash2 />
                        Delete course
                    </Button>
                </div>
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Details</h2>
                </template>

                <dl class="grid grid-cols-1 gap-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Instructor</dt>
                        <dd class="text-right font-medium">{{ course.instructor?.name ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Category</dt>
                        <dd class="text-right font-medium">{{ course.category?.name ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Language</dt>
                        <dd class="text-right font-medium">{{ course.language ?? 'N/A' }}</dd>
                    </div>
                    <Separator />
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Price</dt>
                        <dd class="text-right font-medium">
                            <span v-if="course.discount_price" class="text-success">
                                {{ formatMMK(course.discount_price) }}
                            </span>
                            <span v-else>{{ formatMMK(course.price) }}</span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Students</dt>
                        <dd class="flex items-center gap-1.5 text-right font-medium">
                            <Users class="size-3.5" />
                            {{ course.enrollments?.length ?? course.enrollments_count ?? 0 }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Rating</dt>
                        <dd class="flex items-center gap-1.5 text-right font-medium">
                            <Star class="size-3.5 fill-amber-400 text-amber-400" />
                            {{ course.average_rating ? Number(course.average_rating).toFixed(1) : '—' }}
                        </dd>
                    </div>
                </dl>
            </Card>
        </div>
    </div>

    <Dialog
        :open="deleteOpen"
        size="sm"
        title="Delete course"
        :description="`Permanently delete “${course.title || 'this course'}”? Its thumbnail file is removed too. This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteOpen = false)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteOpen = false">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete course
            </Button>
        </template>
    </Dialog>
</template>
