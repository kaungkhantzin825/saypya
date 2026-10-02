<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Info, Save, X } from 'lucide-vue-next';
import { Alert, Avatar, Badge, Button, Card, Input, Label, Textarea } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { ExamAnswer, ExamAttempt, ExamQuestion } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    attempt: ExamAttempt;
    title?: string;
    description?: string;
}>();

const answers = computed(() => props.attempt.answers ?? []);

/** Only essays need a human — everything else was auto-graded on submit. */
const essayAnswers = computed(() => answers.value.filter((answer) => answer.question?.type === 'essay'));

/**
 * Pairs each answer with its slot in `form.grades`. The grades array only holds
 * essays, so the answer index cannot be used to address it directly — any
 * auto-graded question before an essay would shift every field by one.
 */
const rows = computed(() => {
    let gradeIndex = -1;

    return answers.value.map((answer) => {
        const isEssay = answer.question?.type === 'essay';
        if (isEssay) gradeIndex++;

        return { answer, gradeIndex };
    });
});

const typeLabels: Record<string, string> = {
    multiple_choice: 'Multiple choice',
    true_false: 'True / false',
    essay: 'Essay',
};

const typeVariants: Record<string, 'info' | 'secondary' | 'warning'> = {
    multiple_choice: 'info',
    true_false: 'secondary',
    essay: 'warning',
};

/** Points already banked by the auto-grader. */
const autoGradedScore = computed(() =>
    answers.value
        .filter((answer) => answer.points_earned !== null)
        .reduce((sum, answer) => sum + Number(answer.points_earned ?? 0), 0),
);

const form = useForm({
    grades: essayAnswers.value.map((answer) => ({
        answer_id: answer.id,
        // Held as a string: `Input` emits strings and a numeric default would not type-check.
        points: answer.points_earned !== null ? String(answer.points_earned) : '',
        feedback: answer.feedback ?? '',
    })),
});

function optionText(answer: ExamAnswer): string {
    const options = answer.question?.options ?? [];
    const index = Number(answer.answer);
    if (!Number.isInteger(index) || !options[index]) return '(No answer)';
    return `${String.fromCharCode(65 + index)}) ${options[index]}`;
}

function correctOptionText(question?: ExamQuestion): string {
    const options = question?.options ?? [];
    const index = Number(question?.correct_answer ?? -1);
    if (!Number.isInteger(index) || !options[index]) return '—';
    return `${String.fromCharCode(65 + index)}) ${options[index]}`;
}

function submit() {
    form.post(routes.admin.examSubmitGrade(props.attempt.id));
}
</script>

<template>
    <Link
        :href="routes.admin.examResults(props.attempt.exam_id)"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to results
    </Link>

    <Card class="mb-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Exam</p>
                <p class="mt-1 font-semibold">{{ props.attempt.exam?.title }}</p>
                <p class="mt-0.5 text-sm text-muted-foreground">{{ props.attempt.exam?.course?.title }}</p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Student</p>
                <div class="mt-1 flex items-center gap-2">
                    <Avatar :src="props.attempt.user?.avatar_url" :name="props.attempt.user?.name" size="sm" />
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ props.attempt.user?.name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ props.attempt.user?.email }}</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Submitted</p>
                <p class="mt-1 font-semibold">
                    {{ props.attempt.submitted_at ? new Date(props.attempt.submitted_at).toLocaleString() : '—' }}
                </p>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Auto-graded: {{ autoGradedScore }} / {{ props.attempt.total_points }} pts
                </p>
            </div>
        </div>
    </Card>

    <form class="grid grid-cols-1 gap-4" @submit.prevent="submit">
        <Card
            v-for="(row, index) in rows"
            :key="row.answer.id"
            :class="row.answer.question?.type === 'essay' ? 'border-warning/50' : undefined"
        >
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold">Q{{ index + 1 }}.</span>
                        <Badge :variant="typeVariants[row.answer.question?.type ?? ''] ?? 'secondary'">
                            {{ typeLabels[row.answer.question?.type ?? ''] ?? row.answer.question?.type }}
                        </Badge>
                    </div>
                    <span class="text-sm text-muted-foreground">
                        {{ row.answer.question?.points ?? 0 }}
                        {{ (row.answer.question?.points ?? 0) === 1 ? 'point' : 'points' }}
                    </span>
                </div>
            </template>

            <p class="whitespace-pre-wrap font-semibold">{{ row.answer.question?.question }}</p>

            <div class="mt-4">
                <p class="mb-1.5 text-xs font-bold uppercase tracking-wider text-muted-foreground">Student answer</p>

                <!-- Multiple choice -->
                <div
                    v-if="row.answer.question?.type === 'multiple_choice'"
                    class="flex flex-wrap items-center gap-2 rounded-lg px-3 py-2 text-sm"
                    :class="row.answer.is_correct ? 'bg-success/12 text-success' : 'bg-destructive/12 text-destructive'"
                >
                    <component :is="row.answer.is_correct ? Check : X" class="size-4 shrink-0" />
                    <span>{{ optionText(row.answer) }}</span>
                    <span class="text-muted-foreground">·</span>
                    <span v-if="row.answer.is_correct" class="font-semibold">
                        Correct — {{ row.answer.points_earned }} pts
                    </span>
                    <span v-else class="font-semibold">
                        Wrong — 0 pts (correct: {{ correctOptionText(row.answer.question) }})
                    </span>
                </div>

                <!-- True / false -->
                <div
                    v-else-if="row.answer.question?.type === 'true_false'"
                    class="flex flex-wrap items-center gap-2 rounded-lg px-3 py-2 text-sm"
                    :class="row.answer.is_correct ? 'bg-success/12 text-success' : 'bg-destructive/12 text-destructive'"
                >
                    <component :is="row.answer.is_correct ? Check : X" class="size-4 shrink-0" />
                    <span class="capitalize">{{ row.answer.answer || '(No answer)' }}</span>
                    <span class="text-muted-foreground">·</span>
                    <span v-if="row.answer.is_correct" class="font-semibold">
                        Correct — {{ row.answer.points_earned }} pts
                    </span>
                    <span v-else class="font-semibold">
                        Wrong — 0 pts (correct: {{ row.answer.question?.correct_answer === 'true' ? 'True' : 'False' }})
                    </span>
                </div>

                <!-- Essay -->
                <div
                    v-else
                    class="min-h-[60px] whitespace-pre-wrap rounded-lg border border-border bg-muted/50 p-3 text-sm"
                >{{ row.answer.answer || '(No answer provided)' }}</div>
            </div>

            <!-- Manual grading — essays only -->
            <div
                v-if="row.answer.question?.type === 'essay'"
                class="mt-4 grid grid-cols-1 gap-4 border-t border-border pt-4 sm:grid-cols-3"
            >
                <div class="grid grid-cols-1 gap-2">
                    <Label :for="`points-${row.answer.id}`" required>
                        Points
                        <span class="font-normal text-muted-foreground">(max {{ row.answer.question?.points }})</span>
                    </Label>
                    <Input
                        :id="`points-${row.answer.id}`"
                        v-model="form.grades[row.gradeIndex].points"
                        type="number"
                        :invalid="!!form.errors[`grades.${row.gradeIndex}.points`]"
                    />
                    <p v-if="form.errors[`grades.${row.gradeIndex}.points`]" class="text-sm text-destructive">
                        {{ form.errors[`grades.${row.gradeIndex}.points`] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-2 sm:col-span-2">
                    <Label :for="`feedback-${row.answer.id}`">Feedback</Label>
                    <Textarea
                        :id="`feedback-${row.answer.id}`"
                        v-model="form.grades[row.gradeIndex].feedback"
                        :rows="2"
                        placeholder="Write feedback for the student…"
                    />
                </div>
            </div>
        </Card>

        <Alert v-if="essayAnswers.length === 0" variant="info" title="Nothing to grade manually">
            This attempt has no essay questions — every answer was auto-graded on submit.
        </Alert>

        <Alert v-if="form.hasErrors" variant="destructive" title="Please check the grades">
            Every essay needs a point value between 0 and the question's maximum.
        </Alert>

        <div class="flex flex-wrap items-center gap-2">
            <template v-if="essayAnswers.length > 0">
                <Button type="submit" variant="brand" size="lg" :loading="form.processing">
                    <Save />
                    Save grades
                </Button>
                <Button
                    :href="routes.admin.examResults(props.attempt.exam_id)"
                    variant="ghost"
                    size="lg"
                    :disabled="form.processing"
                >
                    Cancel
                </Button>
            </template>
            <Button v-else :href="routes.admin.examResults(props.attempt.exam_id)" variant="outline" size="lg">
                <ArrowLeft />
                Back to results
            </Button>
        </div>

        <p class="flex items-start gap-1.5 text-xs text-muted-foreground">
            <Info class="mt-0.5 size-3.5 shrink-0" />
            Saving re-sums every answer, marks the attempt graded and sets pass/fail against the exam's
            {{ props.attempt.exam?.passing_score }}% threshold.
        </p>
    </form>
</template>
