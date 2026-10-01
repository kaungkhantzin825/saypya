<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Clock,
    FileQuestion,
    GraduationCap,
    ListChecks,
    Repeat,
    Target,
} from 'lucide-vue-next';
import { Badge, Button, Card, EmptyState } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import type { Course, Exam } from '@/types';

defineOptions({ layout: StudentLayout });

/** Optional per-learner state the controller may attach to each exam. */
type ExamCard = Exam & { passed?: boolean | null };

const props = defineProps<{
    course: Course;
    exams: Exam[];
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

const exams = computed<ExamCard[]>(() => props.exams ?? []);

function questionCount(exam: Exam): number {
    return exam.questions_count ?? exam.questions?.length ?? 0;
}

function hasQuestions(exam: Exam): boolean {
    return questionCount(exam) > 0;
}
</script>

<template>
    <div class="mx-auto max-w-4xl">
        <nav class="mb-6 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground" aria-label="Breadcrumb">
            <Link :href="routes.learn(course.slug)" class="inline-flex items-center gap-1 transition-colors hover:text-foreground">
                <ChevronLeft class="size-3" />
                Back to course
            </Link>
        </nav>

        <div v-if="exams.length" class="grid gap-5">
            <Card v-for="exam in exams" :key="exam.id">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold tracking-tight">{{ exam.title }}</h2>
                            <Badge v-if="exam.passed === true" variant="success">Passed</Badge>
                            <Badge v-else-if="exam.passed === false" variant="destructive">Not passed</Badge>
                        </div>

                        <p v-if="exam.description" class="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {{ exam.description }}
                        </p>

                        <ul class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted-foreground">
                            <li class="inline-flex items-center gap-1.5">
                                <Clock class="size-3.5" />
                                <template v-if="exam.duration_minutes">
                                    {{ exam.duration_minutes }} minutes
                                </template>
                                <template v-else>No time limit</template>
                            </li>
                            <li class="inline-flex items-center gap-1.5">
                                <Target class="size-3.5" />
                                Pass mark {{ exam.passing_score }}%
                            </li>
                            <li class="inline-flex items-center gap-1.5">
                                <Repeat class="size-3.5" />
                                {{ exam.max_attempts }}
                                {{ exam.max_attempts === 1 ? 'attempt' : 'attempts' }} allowed
                            </li>
                            <li class="inline-flex items-center gap-1.5">
                                <ListChecks class="size-3.5" />
                                {{ questionCount(exam) }}
                                {{ questionCount(exam) === 1 ? 'question' : 'questions' }}
                            </li>
                        </ul>

                        <p v-if="!hasQuestions(exam)" class="mt-3 text-xs font-medium text-warning">
                            This exam has no questions yet, so it can't be started.
                        </p>
                    </div>

                    <div class="shrink-0 sm:pt-1">
                        <Button
                            v-if="hasQuestions(exam)"
                            :href="routes.startExam(exam.id)"
                            variant="brand"
                            class="w-full sm:w-auto"
                        >
                            <GraduationCap />
                            Start exam
                        </Button>
                        <Button v-else variant="secondary" disabled class="w-full sm:w-auto">
                            <FileQuestion />
                            Not available
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <EmptyState
            v-else
            :icon="GraduationCap"
            title="No exams available"
            description="There are no published exams for this course yet. Check back once your instructor adds one."
        >
            <Button :href="routes.learn(course.slug)" variant="outline">
                <ChevronLeft />
                Back to course
            </Button>
        </EmptyState>
    </div>
</template>
