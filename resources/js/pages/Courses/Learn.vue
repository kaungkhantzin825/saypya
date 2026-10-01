<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle2,
    Circle,
    Clock,
    Lock,
    PlayCircle,
    Trophy,
} from 'lucide-vue-next';
import { Accordion, Badge, Button, Card, Dialog, EmptyState, Progress } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { clampPercent, formatClock } from '@/lib/utils';
import type { Course, Enrollment, Lesson, Section } from '@/types';
import type { AccordionEntry } from '@/types/ui';

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    course: Course;
    enrollment: Enrollment;
    currentLesson?: Lesson | null;
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

/* ---------------------------------------------------------- curriculum --- */

const sections = computed<Section[]>(() => props.course.sections ?? []);

const lessons = computed<Lesson[]>(
    () => sections.value.flatMap((section) => section.lessons ?? []),
);

const completedCount = computed(() => lessons.value.filter((lesson) => lesson.is_completed).length);

const progressPercent = computed(() => clampPercent(props.enrollment.progress_percentage));
const isCourseComplete = computed(() => progressPercent.value >= 100);

/** The lesson currently playing — falls back to the first lesson of the course. */
const activeLesson = computed<Lesson | null>(() => props.currentLesson ?? lessons.value[0] ?? null);
const isActiveCompleted = computed(() => !!activeLesson.value?.is_completed);

const currentSectionTitle = computed(() => {
    if (!activeLesson.value) return '';
    return sections.value.find((section) => section.lessons?.some((lesson) => lesson.id === activeLesson.value?.id))?.title ?? '';
});

const sectionEntries = computed<AccordionEntry[]>(() =>
    sections.value.map((section) => ({
        value: String(section.id),
        title: section.title,
        meta: `${section.lessons?.length ?? 0} ${(section.lessons?.length ?? 0) === 1 ? 'lesson' : 'lessons'}`,
    })),
);

const defaultSections = computed(() => {
    if (activeLesson.value) {
        const section = sections.value.find((entry) =>
            entry.lessons?.some((lesson) => lesson.id === activeLesson.value?.id),
        );
        if (section) return [String(section.id)];
    }
    return sections.value.length ? [String(sections.value[0].id)] : [];
});

function lessonsFor(value: string): Lesson[] {
    return sections.value.find((section) => String(section.id) === value)?.lessons ?? [];
}

/* -------------------------------------------------------------- actions --- */

function markComplete() {
    if (!activeLesson.value) return;
    router.post(routes.lessonComplete(activeLesson.value.id), {}, { preserveScroll: true, preserveState: true });
}

function markIncomplete() {
    if (!activeLesson.value) return;
    router.post(routes.lessonUncomplete(activeLesson.value.id), {}, { preserveScroll: true, preserveState: true });
}

/* ------------------------------------------------------------ exam gate --- */

const publishedExams = computed(() => (props.course.exams ?? []).filter((exam) => exam.is_published));

/** Exams unlock only once every lesson is complete. */
const examUnlocked = computed(() => publishedExams.value.length > 0 && isCourseComplete.value);

/* -------------------------------------------------------- celebration --- */

const celebrateOpen = ref(false);
let wasComplete = isCourseComplete.value;

watch(progressPercent, (value) => {
    if (value >= 100 && !wasComplete) {
        celebrateOpen.value = true;
    }
    wasComplete = value >= 100;
});
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <!-- ====================================================== Player -->
        <div class="min-w-0 space-y-6">
            <Card v-if="activeLesson" :padded="false" class="overflow-hidden">
                <div class="grid gap-5 p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p
                                v-if="currentSectionTitle"
                                class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                            >
                                {{ currentSectionTitle }}
                            </p>
                            <h2 class="mt-1 text-xl font-bold tracking-tight">{{ activeLesson.title }}</h2>
                        </div>
                        <Badge v-if="isActiveCompleted" variant="success">
                            <CheckCircle2 class="size-3" />
                            Completed
                        </Badge>
                    </div>

                    <div
                        v-if="activeLesson.youtube_embed_url || activeLesson.video_url_full"
                        class="overflow-hidden rounded-xl border border-border bg-black"
                    >
                        <div class="aspect-video w-full">
                            <iframe
                                v-if="activeLesson.youtube_embed_url"
                                :src="activeLesson.youtube_embed_url"
                                :title="activeLesson.title"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                class="h-full w-full"
                            />
                            <video
                                v-else
                                :src="activeLesson.video_url_full ?? undefined"
                                controls
                                preload="metadata"
                                controlslist="nodownload"
                                class="h-full w-full"
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
                        v-if="activeLesson.description"
                        class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ activeLesson.description }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button v-if="!isActiveCompleted" variant="brand" @click="markComplete">
                            <CheckCircle2 />
                            Mark complete
                        </Button>
                        <Button v-else variant="outline" @click="markIncomplete">
                            <Circle />
                            Mark incomplete
                        </Button>
                        <Button
                            v-if="currentLesson"
                            :href="routes.learnLesson(course.slug, currentLesson.id)"
                            variant="ghost"
                        >
                            Open lesson page
                        </Button>
                    </div>
                </div>
            </Card>

            <EmptyState
                v-else
                :icon="BookOpen"
                title="No lessons published yet"
                description="This course doesn't have any lessons to play right now. Check back soon."
            />
        </div>

        <!-- ==================================================== Sidebar -->
        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <!-- Progress -->
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

            <!-- Curriculum -->
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
                                <li v-for="lesson in lessonsFor(item.value)" :key="lesson.id">
                                    <Link
                                        :href="routes.learnLesson(course.slug, lesson.id)"
                                        :aria-current="lesson.id === currentLesson?.id ? 'page' : undefined"
                                        :class="[
                                            'flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm transition-colors',
                                            lesson.id === currentLesson?.id
                                                ? 'bg-brand-50 font-semibold text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                        ]"
                                    >
                                        <CheckCircle2
                                            v-if="lesson.is_completed"
                                            class="size-4 shrink-0 text-success"
                                        />
                                        <Circle v-else class="size-4 shrink-0 text-muted-foreground/60" />
                                        <span class="min-w-0 flex-1 truncate">{{ lesson.title }}</span>
                                        <span
                                            v-if="lesson.video_duration"
                                            class="inline-flex shrink-0 items-center gap-1 text-xs text-muted-foreground"
                                        >
                                            <Clock class="size-3" />
                                            {{ formatClock(lesson.video_duration) }}
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

            <!-- Exam gate — only shown when the course actually has published exams
                 (mirrors the @elseif($course->exams->count() > 0) guard in the old Blade view). -->
            <template v-if="publishedExams.length > 0">
                <Card v-if="examUnlocked" class="border-brand-200 dark:border-brand-800">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                        >
                            <Trophy class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold leading-tight">Exam available</h3>
                            <p class="text-xs text-muted-foreground">
                                {{ publishedExams.length }}
                                {{ publishedExams.length === 1 ? 'published exam' : 'published exams' }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-muted-foreground">
                        You've completed every lesson. Take the course exam to finish up.
                    </p>
                    <Button :href="routes.courseExams(course.id)" variant="brand" block class="mt-4">
                        Go to exams
                    </Button>
                </Card>

                <Card v-else>
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-muted text-muted-foreground"
                        >
                            <Lock class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-semibold leading-tight">Exam locked</h3>
                            <p class="text-xs text-muted-foreground">Complete all lessons to unlock</p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-muted-foreground">
                        Finish every lesson to unlock the course exam. You're at
                        <span class="font-semibold text-foreground">{{ progressPercent }}%</span>.
                    </p>
                    <Progress :value="progressPercent" :auto-tone="true" size="sm" class="mt-3" />
                </Card>
            </template>
        </aside>
    </div>

    <!-- ==================================================== Celebration -->
    <Dialog v-model:open="celebrateOpen" size="sm" title="Congratulations!">
        <div class="flex flex-col items-center gap-3 py-2 text-center">
            <span
                class="flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
            >
                <Trophy class="size-7" />
            </span>
            <p class="text-sm text-muted-foreground">
                You've completed every lesson in
                <span class="font-semibold text-foreground">{{ course.title }}</span>. The course exam is now
                unlocked.
            </p>
        </div>
        <template #footer>
            <Button variant="outline" @click="celebrateOpen = false">Keep learning</Button>
            <Button :href="routes.courseExams(course.id)" variant="brand" @click="celebrateOpen = false">
                Go to exams
            </Button>
        </template>
    </Dialog>
</template>
