<script setup lang="ts">
import { computed } from 'vue';
import {
    CheckCircle2,
    ChevronLeft,
    CircleCheck,
    CircleX,
    Hourglass,
    Repeat,
    Target,
    XCircle,
} from 'lucide-vue-next';
import { Alert, Badge, Button, Card } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { cn } from '@/lib/utils';
import type { ExamAnswer, ExamAttempt, ExamQuestion } from '@/types';

defineOptions({ layout: StudentLayout });

/** Optional per-learner attempt counters the controller may attach to the exam. */
type ExamWithCounters = NonNullable<ExamAttempt['exam']> & {
    attempts_used?: number;
    attempts_remaining?: number;
};

const props = defineProps<{
    attempt: ExamAttempt;
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

const exam = computed<ExamWithCounters | null>(() => (props.attempt.exam as ExamWithCounters | undefined) ?? null);

const questions = computed<ExamQuestion[]>(() => exam.value?.questions ?? []);

const answersByQuestion = computed(() => {
    const map = new Map<number, ExamAnswer>();
    (props.attempt.answers ?? []).forEach((answer) => map.set(answer.question_id, answer));
    return map;
});

const score = computed(() => Number(props.attempt.score ?? 0));
const totalPoints = computed(() => Number(props.attempt.total_points ?? 0));
const percentage = computed(() =>
    totalPoints.value > 0 ? Math.round((score.value / totalPoints.value) * 100) : 0,
);
const passed = computed(() => props.attempt.passed === true);
const passingScore = computed(() => exam.value?.passing_score ?? 0);
const isPending = computed(() => props.attempt.status === 'submitted');
const showCorrectAnswers = computed(() => exam.value?.show_correct_answers === true);

const backHref = computed(() => {
    const slug = exam.value?.course?.slug;
    return slug ? routes.learn(slug) : routes.myExams();
});
const backLabel = computed(() => (exam.value?.course?.slug ? 'Back to course' : 'My exams'));

const canRetake = computed(() => {
    const current = exam.value;
    if (!current || passed.value) return false;

    if (typeof current.attempts_remaining === 'number') return current.attempts_remaining > 0;
    if (typeof current.attempts_used === 'number') return current.attempts_used < current.max_attempts;

    return current.max_attempts > 1;
});

/**
 * Multiple-choice answers are stored as the option **index** (as a string) and
 * true/false as the lowercase 'true' / 'false' — see `ExamQuestion::isCorrect()`.
 * Resolve both to readable text for display.
 */
function displayAnswer(question: ExamQuestion, raw: string | null | undefined): string {
    if (raw === null || raw === undefined || String(raw).trim() === '') return 'Not answered';

    const value = String(raw);

    if (question.type === 'true_false') {
        return value.toLowerCase() === 'true' ? 'True' : 'False';
    }

    if (question.type === 'multiple_choice') {
        const options = question.options ?? [];
        const index = Number(value);
        if (Number.isInteger(index) && options[index] !== undefined) return options[index];
        // Tolerate rows that already hold the option text.
        return value;
    }

    return value;
}

function answerText(question: ExamQuestion, answer: ExamAnswer | undefined): string {
    return displayAnswer(question, answer?.answer);
}

function correctAnswerText(question: ExamQuestion): string {
    return displayAnswer(question, question.correct_answer);
}

function isCorrect(question: ExamQuestion, answer: ExamAnswer | undefined): boolean | null {
    if (question.type === 'essay') return answer?.points_earned != null ? (answer.is_correct ?? null) : null;
    return answer?.is_correct ?? null;
}
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <!-- ========================================================= Hero -->
        <Card :padded="false" class="overflow-hidden">
            <div
                :class="
                    cn(
                        'flex flex-col gap-4 px-6 py-6 text-white sm:flex-row sm:items-center sm:justify-between',
                        passed ? 'bg-gradient-to-r from-emerald-600 to-emerald-500' : 'bg-gradient-to-r from-rose-600 to-rose-500',
                    )
                "
            >
                <div class="flex items-center gap-4">
                    <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-white/20">
                        <CheckCircle2 v-if="passed" class="size-8" />
                        <XCircle v-else class="size-8" />
                    </span>
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-white/80">
                            {{ passed ? 'Exam passed' : 'Exam not passed' }}
                        </p>
                        <h2 class="text-2xl font-extrabold tracking-tight">
                            {{ exam?.title ?? 'Exam result' }}
                        </h2>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <p class="text-4xl font-extrabold leading-none tabular-nums">{{ percentage }}%</p>
                    <p class="mt-1 text-sm text-white/80">
                        {{ score }} / {{ totalPoints }} points
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 p-6 sm:grid-cols-3">
                <div class="text-center">
                    <p class="text-2xl font-bold tabular-nums">{{ percentage }}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Your score</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold tabular-nums">{{ passingScore }}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Pass mark</p>
                </div>
                <div class="col-span-2 text-center sm:col-span-1">
                    <p class="text-2xl font-bold tabular-nums">{{ score }}/{{ totalPoints }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Points earned</p>
                </div>
            </div>
        </Card>

        <Alert v-if="isPending" variant="warning" title="Awaiting manual grading">
            Some questions need to be graded by your instructor. Your final score will update once grading is
            complete.
        </Alert>

        <!-- ================================================ Breakdown -->
        <section v-if="questions.length" class="space-y-4">
            <h3 class="text-base font-semibold">Answer breakdown</h3>

            <Card v-for="(question, index) in questions" :key="question.id">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h4 class="min-w-0 text-sm font-semibold leading-snug">
                        <span class="text-muted-foreground">Question {{ index + 1 }}</span>
                        <span class="mx-1.5 text-muted-foreground/50">·</span>
                        <span>{{ question.question }}</span>
                    </h4>

                    <Badge
                        v-if="question.type === 'essay' && isCorrect(question, answersByQuestion.get(question.id)) === null"
                        variant="warning"
                    >
                        <Hourglass class="size-3" />
                        Awaiting manual grading
                    </Badge>
                    <Badge
                        v-else-if="isCorrect(question, answersByQuestion.get(question.id)) === true"
                        variant="success"
                    >
                        <CircleCheck class="size-3" />
                        Correct · {{ answersByQuestion.get(question.id)?.points_earned ?? 0 }}/{{ question.points }}
                    </Badge>
                    <Badge v-else-if="isCorrect(question, answersByQuestion.get(question.id)) === false" variant="destructive">
                        <CircleX class="size-3" />
                        Incorrect · {{ answersByQuestion.get(question.id)?.points_earned ?? 0 }}/{{ question.points }}
                    </Badge>
                    <Badge v-else variant="muted">{{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}</Badge>
                </div>

                <div class="mt-4 grid gap-3">
                    <div class="rounded-lg border border-border bg-muted/40 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Your answer</p>
                        <p class="mt-1 whitespace-pre-line text-sm">{{ answerText(question, answersByQuestion.get(question.id)) }}</p>
                    </div>

                    <div
                        v-if="showCorrectAnswers && question.type !== 'essay' && question.correct_answer"
                        class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900 dark:bg-emerald-950/40"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                            Correct answer
                        </p>
                        <p class="mt-1 text-sm font-medium text-emerald-900 dark:text-emerald-100">
                            {{ correctAnswerText(question) }}
                        </p>
                    </div>
                </div>
            </Card>
        </section>

        <!-- ================================================== Actions -->
        <div class="flex flex-wrap items-center gap-3">
            <Button :href="backHref" variant="outline">
                <ChevronLeft />
                {{ backLabel }}
            </Button>
            <Button v-if="canRetake && exam" :href="routes.startExam(exam.id)" variant="brand">
                <Repeat />
                Retake exam
            </Button>
        </div>

        <p v-if="exam" class="text-xs text-muted-foreground">
            <Target class="mr-1 inline size-3" />
            You need {{ passingScore }}% to pass this exam.
            <template v-if="exam.max_attempts">
                Up to {{ exam.max_attempts }} {{ exam.max_attempts === 1 ? 'attempt' : 'attempts' }} allowed.
            </template>
        </p>
    </div>
</template>
