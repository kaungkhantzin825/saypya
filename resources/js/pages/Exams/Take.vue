<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Clock, Send } from 'lucide-vue-next';
import { Alert, Badge, Button, Card, Dialog, RadioGroup, Textarea } from '@/components/ui';
import { routes } from '@/lib/routes';
import { cn, formatClock } from '@/lib/utils';
import type { Exam, ExamAttempt, ExamQuestion } from '@/types';
import type { RadioOption } from '@/types/ui';

/**
 * Focused, distraction-free exam runner — deliberately rendered without a layout.
 */
const props = defineProps<{
    exam: Exam;
    attempt: ExamAttempt;
}>();

const questions = computed<ExamQuestion[]>(() => props.exam.questions ?? []);

const fieldKey = (questionId: number) => `question_${questionId}`;

/* --------------------------------------------------------------- form --- */

const initialData: Record<string, string> = {};
questions.value.forEach((question) => {
    initialData[fieldKey(question.id)] = '';
});

const form = useForm<Record<string, string>>(initialData);

let submitted = false;

function isAnswered(question: ExamQuestion): boolean {
    return (form[fieldKey(question.id)] ?? '').trim() !== '';
}

const unanswered = computed(() => questions.value.filter((question) => !isAnswered(question)));

/**
 * Answer values must match exactly what `ExamQuestion::isCorrect()` compares
 * against (`$answer === $this->correct_answer`, a strict string comparison):
 *
 *  - multiple_choice: `correct_answer` holds the option **index** as a string
 *    (the admin question form submits `value="0"`, `value="1"`, …), so the
 *    radio value is the index — not the option text.
 *  - true_false: `correct_answer` holds the lowercase `'true'` / `'false'`.
 */
function optionsFor(question: ExamQuestion): RadioOption[] {
    if (question.type === 'true_false') {
        return [
            { value: 'true', label: 'True' },
            { value: 'false', label: 'False' },
        ];
    }

    return (question.options ?? []).map((option, index) => ({
        value: String(index),
        label: option,
    }));
}

/* -------------------------------------------------------------- submit --- */

const confirmOpen = ref(false);

function doSubmit() {
    if (submitted) return;
    submitted = true;
    confirmOpen.value = false;

    form.post(routes.submitExam(props.attempt.id), {
        onError: () => {
            submitted = false;
        },
    });
}

function openConfirm() {
    confirmOpen.value = true;
}

/* --------------------------------------------------------------- timer --- */

const totalSeconds = (props.exam.duration_minutes ?? 0) * 60;
const remaining = ref(totalSeconds);

let timerId: number | undefined;

const timerTone = computed<'destructive' | 'warning' | 'secondary'>(() => {
    if (remaining.value <= 60) return 'destructive';
    if (remaining.value <= 300) return 'warning';
    return 'secondary';
});

onMounted(() => {
    if (totalSeconds > 0) {
        timerId = window.setInterval(() => {
            if (remaining.value <= 0) return;
            remaining.value -= 1;
            if (remaining.value <= 0) {
                doSubmit();
            }
        }, 1000);
    }

    window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
    if (timerId) window.clearInterval(timerId);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

/* ------------------------------------------------------- leave warning --- */

function handleBeforeUnload(event: BeforeUnloadEvent) {
    if (submitted || !form.isDirty) return;
    event.preventDefault();
    event.returnValue = '';
}

/* ---------------------------------------------------------- navigator --- */

function scrollToQuestion(questionId: number) {
    document.getElementById(`question-${questionId}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<template>
    <div class="min-h-screen bg-muted/40">
        <!-- ======================================================= Top bar -->
        <header class="sticky top-0 z-30 border-b border-border bg-background/90 backdrop-blur-lg">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-3 px-4 py-3 sm:px-6">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold leading-tight">{{ exam.title }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ questions.length }} {{ questions.length === 1 ? 'question' : 'questions' }} ·
                        {{ attempt.total_points }} points
                    </p>
                </div>

                <Badge v-if="totalSeconds > 0" :variant="timerTone" class="shrink-0 tabular-nums">
                    <Clock class="size-3" />
                    {{ formatClock(remaining) }}
                </Badge>
                <Badge v-else variant="muted" class="shrink-0">No time limit</Badge>

                <Button variant="brand" :loading="form.processing" @click="openConfirm">
                    <Send />
                    Submit
                </Button>
            </div>
        </header>

        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_16rem]">
                <!-- ============================================== Questions -->
                <div class="min-w-0 space-y-5">
                    <Alert v-if="totalSeconds > 0 && remaining <= 60" variant="destructive" title="Time is almost up">
                        Your exam will be submitted automatically when the timer reaches zero.
                    </Alert>

                    <Card
                        v-for="(question, index) in questions"
                        :id="`question-${question.id}`"
                        :key="question.id"
                        class="scroll-mt-24"
                    >
                        <template #header>
                            <div class="flex items-start justify-between gap-3">
                                <h2 class="text-base font-semibold leading-snug">
                                    <span class="text-muted-foreground">Question {{ index + 1 }}</span>
                                    <span class="mx-1.5 text-muted-foreground/50">·</span>
                                    <span>{{ question.question }}</span>
                                </h2>
                                <div class="flex shrink-0 items-center gap-2">
                                    <Badge variant="muted">
                                        {{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}
                                    </Badge>
                                    <Badge v-if="isAnswered(question)" variant="success">
                                        <CheckCircle2 class="size-3" />
                                        Answered
                                    </Badge>
                                </div>
                            </div>
                        </template>

                        <div class="grid gap-3">
                            <RadioGroup
                                v-if="question.type === 'multiple_choice' || question.type === 'true_false'"
                                v-model="form[fieldKey(question.id)]"
                                :name="fieldKey(question.id)"
                                :options="optionsFor(question)"
                            />

                            <Textarea
                                v-else
                                v-model="form[fieldKey(question.id)]"
                                :rows="6"
                                placeholder="Type your answer here…"
                                :invalid="!!form.errors[fieldKey(question.id)]"
                            />

                            <p
                                v-if="form.errors[fieldKey(question.id)]"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors[fieldKey(question.id)] }}
                            </p>
                        </div>
                    </Card>

                    <Card v-if="!questions.length">
                        <p class="text-sm text-muted-foreground">
                            This exam has no questions. You can submit to record an empty attempt.
                        </p>
                    </Card>

                    <div class="flex justify-end pb-4">
                        <Button variant="brand" :loading="form.processing" @click="openConfirm">
                            <Send />
                            Submit exam
                        </Button>
                    </div>
                </div>

                <!-- ============================================== Navigator -->
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <Card>
                        <h3 class="text-sm font-semibold">Questions</h3>
                        <div class="mt-3 grid grid-cols-6 gap-1.5 sm:grid-cols-8 lg:grid-cols-5">
                            <button
                                v-for="(question, index) in questions"
                                :key="question.id"
                                type="button"
                                :aria-label="`Go to question ${index + 1}`"
                                :class="
                                    cn(
                                        'flex size-8 items-center justify-center rounded-md border text-xs font-semibold transition-colors',
                                        isAnswered(question)
                                            ? 'border-transparent bg-brand-600 text-white hover:bg-brand-700'
                                            : 'border-border bg-background text-muted-foreground hover:bg-accent',
                                    )
                                "
                                @click="scrollToQuestion(question.id)"
                            >
                                {{ index + 1 }}
                            </button>
                        </div>

                        <dl class="mt-4 grid gap-1.5 text-xs text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <span class="size-3 rounded-sm bg-brand-600" />
                                Answered ({{ questions.length - unanswered.length }})
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="size-3 rounded-sm border border-border bg-background" />
                                Unanswered ({{ unanswered.length }})
                            </div>
                        </dl>
                    </Card>
                </aside>
            </div>
        </div>

        <!-- =================================================== Confirmation -->
        <Dialog
            v-model:open="confirmOpen"
            title="Submit your exam?"
            description="You won't be able to change your answers afterwards."
        >
            <Alert
                v-if="unanswered.length"
                variant="warning"
                :title="`${unanswered.length} unanswered ${unanswered.length === 1 ? 'question' : 'questions'}`"
            >
                <p class="mb-2">These questions will be marked as incorrect:</p>
                <ul class="flex flex-wrap gap-1.5">
                    <li v-for="question in unanswered" :key="question.id">
                        <Badge variant="warning">
                            <AlertTriangle class="size-3" />
                            Question {{ questions.findIndex((entry) => entry.id === question.id) + 1 }}
                        </Badge>
                    </li>
                </ul>
            </Alert>
            <Alert v-else variant="success" title="All questions answered">
                You're good to go — submit when you're ready.
            </Alert>

            <template #footer>
                <Button variant="outline" @click="confirmOpen = false">Keep working</Button>
                <Button variant="brand" :loading="form.processing" @click="doSubmit">Submit exam</Button>
            </template>
        </Dialog>
    </div>
</template>
