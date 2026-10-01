<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Circle,
    Clock,
    PlayCircle,
} from 'lucide-vue-next';
import { Accordion, Badge, Button, Card, Progress } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { clampPercent, formatClock } from '@/lib/utils';
import type { Course, Enrollment, Lesson, LessonProgress, Section } from '@/types';
import type { AccordionEntry } from '@/types/ui';

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    course: Course;
    lesson: Lesson;
    enrollment: Enrollment;
    progress?: LessonProgress | null;
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

/* ---------------------------------------------------------- curriculum --- */

const sections = computed<Section[]>(() => props.course.sections ?? []);

const lessons = computed<Lesson[]>(
    () => sections.value.flatMap((section) => section.lessons ?? []),
);

const lessonIndex = computed(() => lessons.value.findIndex((entry) => entry.id === props.lesson.id));
const previousLesson = computed<Lesson | null>(() =>
    lessonIndex.value > 0 ? lessons.value[lessonIndex.value - 1] : null,
);
const nextLesson = computed<Lesson | null>(() =>
    lessonIndex.value >= 0 && lessonIndex.value < lessons.value.length - 1
        ? lessons.value[lessonIndex.value + 1]
        : null,
);

const completedCount = computed(() => lessons.value.filter((entry) => entry.is_completed).length);
const progressPercent = computed(() => clampPercent(props.enrollment.progress_percentage));

const sectionEntries = computed<AccordionEntry[]>(() =>
    sections.value.map((section) => ({
        value: String(section.id),
        title: section.title,
        meta: `${section.lessons?.length ?? 0} ${(section.lessons?.length ?? 0) === 1 ? 'lesson' : 'lessons'}`,
    })),
);

const currentSection = computed(() =>
    sections.value.find((section) => section.lessons?.some((entry) => entry.id === props.lesson.id)),
);
const defaultSections = computed(() =>
    currentSection.value ? [String(currentSection.value.id)] : sections.value.length ? [String(sections.value[0].id)] : [],
);

function lessonsFor(value: string): Lesson[] {
    return sections.value.find((section) => String(section.id) === value)?.lessons ?? [];
}

/* ------------------------------------------------------- completion --- */

const isCompleted = computed(() => !!props.progress?.is_completed);

function toggleComplete() {
    const url = isCompleted.value
        ? routes.lessonUncomplete(props.lesson.id)
        : routes.lessonComplete(props.lesson.id);

    router.post(url, {}, { preserveScroll: true, preserveState: true });
}

/* --------------------------------------------------- video tracking --- */

const videoEl = ref<HTMLVideoElement | null>(null);

/** Last watch position (seconds) we reported to the server. */
let lastReported = 0;
let completedLocally = props.progress?.is_completed ?? false;

/**
 * Report watch progress at most once every ~15 seconds. Failures are swallowed
 * so a dropped request never interrupts playback.
 */
function reportProgress(force = false) {
    const video = videoEl.value;
    if (!video || !props.lesson.video_url) return;

    const current = Math.floor(video.currentTime || 0);
    if (!force && current - lastReported < 15) return;

    lastReported = current;

    const reachedEnd = video.duration > 0 && current >= video.duration - 1;
    const completed = completedLocally || reachedEnd;
    completedLocally = completed;

    router.post(
        routes.lessonProgress(props.lesson.id),
        { watch_time: current, is_completed: completed },
        {
            preserveScroll: true,
            preserveState: true,
            only: [],
            onError: () => {
                /* Ignore — progress pings are best-effort. */
            },
        },
    );
}

function onTimeUpdate() {
    reportProgress();
}

function onEnded() {
    reportProgress(true);
}
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <!-- ====================================================== Player -->
        <div class="min-w-0 space-y-6">
            <nav class="flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground" aria-label="Breadcrumb">
                <Link :href="routes.learn(course.slug)" class="transition-colors hover:text-foreground">
                    {{ course.title }}
                </Link>
                <ChevronRight class="size-3" />
                <span v-if="currentSection" class="truncate">{{ currentSection.title }}</span>
            </nav>

            <Card :padded="false" class="overflow-hidden">
                <div class="grid gap-5 p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <h2 class="min-w-0 text-xl font-bold tracking-tight">{{ lesson.title }}</h2>
                        <Badge v-if="isCompleted" variant="success">
                            <CheckCircle2 class="size-3" />
                            Completed
                        </Badge>
                    </div>

                    <div
                        v-if="lesson.youtube_embed_url || lesson.video_url_full"
                        class="overflow-hidden rounded-xl border border-border bg-black"
                    >
                        <div class="aspect-video w-full">
                            <iframe
                                v-if="lesson.youtube_embed_url"
                                :src="lesson.youtube_embed_url"
                                :title="lesson.title"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                class="h-full w-full"
                            />
                            <video
                                v-else
                                ref="videoEl"
                                :src="lesson.video_url_full ?? undefined"
                                controls
                                preload="metadata"
                                controlslist="nodownload"
                                class="h-full w-full"
                                @timeupdate="onTimeUpdate"
                                @ended="onEnded"
                            >
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex aspect-video w-full items-center justify-center gap-2 rounded-xl border border-dashed border-border bg-muted/40 text-sm text-muted-foreground"
                    >
                        <PlayCircle class="size-5" />
                        No video for this lesson
                    </div>

                    <p
                        v-if="lesson.description"
                        class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ lesson.description }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button v-if="!isCompleted" variant="brand" @click="toggleComplete">
                            <CheckCircle2 />
                            Mark complete
                        </Button>
                        <Button v-else variant="outline" @click="toggleComplete">
                            <Circle />
                            Mark incomplete
                        </Button>
                    </div>
                </div>
            </Card>

            <!-- Prev / next -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <Button
                    v-if="previousLesson"
                    :href="routes.learnLesson(course.slug, previousLesson.id)"
                    variant="outline"
                    class="justify-start"
                >
                    <ChevronLeft />
                    <span class="min-w-0 truncate">{{ previousLesson.title }}</span>
                </Button>
                <span v-else />

                <Button
                    v-if="nextLesson"
                    :href="routes.learnLesson(course.slug, nextLesson.id)"
                    variant="outline"
                    class="justify-end sm:ml-auto"
                >
                    <span class="min-w-0 truncate">{{ nextLesson.title }}</span>
                    <ChevronRight />
                </Button>
            </div>
        </div>

        <!-- ==================================================== Sidebar -->
        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <Card>
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">Your progress</h3>
                    <span class="text-sm font-bold text-brand-700 dark:text-brand-400">{{ progressPercent }}%</span>
                </div>
                <Progress :value="progressPercent" :auto-tone="true" class="mt-3" />
                <p class="mt-2 text-xs text-muted-foreground">
                    {{ completedCount }} of {{ lessons.length }}
                    {{ lessons.length === 1 ? 'lesson' : 'lessons' }} completed
                </p>
            </Card>

            <Card :padded="false">
                <div class="border-b border-border px-5 py-4">
                    <h3 class="text-sm font-semibold">Curriculum</h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ sections.length }} {{ sections.length === 1 ? 'section' : 'sections' }} ·
                        {{ lessons.length }} {{ lessons.length === 1 ? 'lesson' : 'lessons' }}
                    </p>
                </div>

                <div v-if="sectionEntries.length" class="p-3">
                    <Accordion type="multiple" :items="sectionEntries" :default-value="defaultSections">
                        <template #content="{ item }">
                            <ul class="grid gap-1">
                                <li v-for="entry in lessonsFor(item.value)" :key="entry.id">
                                    <Link
                                        :href="routes.learnLesson(course.slug, entry.id)"
                                        :aria-current="entry.id === lesson.id ? 'page' : undefined"
                                        :class="[
                                            'flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm transition-colors',
                                            entry.id === lesson.id
                                                ? 'bg-brand-50 font-semibold text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                        ]"
                                    >
                                        <CheckCircle2
                                            v-if="entry.is_completed"
                                            class="size-4 shrink-0 text-success"
                                        />
                                        <Circle v-else class="size-4 shrink-0 text-muted-foreground/60" />
                                        <span class="min-w-0 flex-1 truncate">{{ entry.title }}</span>
                                        <span
                                            v-if="entry.video_duration"
                                            class="inline-flex shrink-0 items-center gap-1 text-xs text-muted-foreground"
                                        >
                                            <Clock class="size-3" />
                                            {{ formatClock(entry.video_duration) }}
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </template>
                    </Accordion>
                </div>

                <p v-else class="px-5 py-6 text-sm text-muted-foreground">
                    The curriculum for this course will appear here once it's ready.
                </p>
            </Card>
        </aside>
    </div>
</template>
