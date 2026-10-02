<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    BarChart3,
    Check,
    CircleHelp,
    Info,
    Pencil,
    Plus,
    Save,
    Trash2,
    X,
} from 'lucide-vue-next';
import {
    Alert,
    Badge,
    Button,
    Card,
    Checkbox,
    Dialog,
    EmptyState,
    Input,
    Label,
    Select,
    Textarea,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { Course, Exam, ExamQuestion, ExamQuestionType } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    /** `null` when creating — questions can only be added once the exam exists. */
    exam: Exam | null;
    courses: Course[];
    title?: string;
    description?: string;
}>();

const isEdit = computed(() => props.exam !== null);

const courseOptions = props.courses.map((course) => ({
    value: String(course.id),
    label: course.title,
}));

// --- Exam settings --------------------------------------------------------

/**
 * Numeric inputs are held as strings: `Input` emits `$event.target.value`, and a
 * field initialised as a number would fail to type-check on the first keystroke.
 * Laravel's `integer` rule accepts numeric strings, so nothing is lost.
 */
const settingsForm = useForm({
    course_id: props.exam ? String(props.exam.course_id) : '',
    title: props.exam?.title ?? '',
    description: props.exam?.description ?? '',
    duration_minutes: props.exam?.duration_minutes != null ? String(props.exam.duration_minutes) : '',
    passing_score: String(props.exam?.passing_score ?? 70),
    max_attempts: String(props.exam?.max_attempts ?? 1),
    show_results: props.exam?.show_results ?? true,
    show_correct_answers: props.exam?.show_correct_answers ?? true,
    is_published: props.exam?.is_published ?? false,
});

const totalPoints = computed(() =>
    (props.exam?.questions ?? []).reduce((sum, question) => sum + Number(question.points || 0), 0),
);

function submitSettings() {
    if (isEdit.value && props.exam) {
        settingsForm.put(routes.admin.examUpdate(props.exam.id), { preserveScroll: true });
        return;
    }

    settingsForm.post(routes.admin.examStore());
}

// --- Question add / edit --------------------------------------------------

const questionTypeOptions = [
    { value: 'multiple_choice', label: 'Multiple choice' },
    { value: 'true_false', label: 'True / false' },
    { value: 'essay', label: 'Essay (graded manually)' },
];

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

const questionDialog = ref<{ open: boolean; question: ExamQuestion | null }>({ open: false, question: null });

const questionForm = useForm({
    question: '',
    type: 'multiple_choice' as ExamQuestionType,
    points: '1',
    options: ['', ''] as string[],
    correct_answer: null as string | null,
});

function openCreateQuestion() {
    questionForm.reset();
    questionForm.clearErrors();
    questionForm.type = 'multiple_choice';
    questionForm.points = '1';
    questionForm.options = ['', ''];
    questionForm.correct_answer = null;
    questionDialog.value = { open: true, question: null };
}

function openEditQuestion(question: ExamQuestion) {
    questionForm.clearErrors();
    questionForm.question = question.question;
    questionForm.type = question.type;
    questionForm.points = String(question.points);
    questionForm.options = question.options?.length ? [...question.options] : ['', ''];
    questionForm.correct_answer = question.correct_answer ?? null;
    questionDialog.value = { open: true, question };
}

/** Selecting a type resets the answer fields so a stale value cannot leak through. */
function onTypeChange(value: string) {
    questionForm.type = value as ExamQuestionType;
    questionForm.clearErrors();

    if (value === 'multiple_choice') {
        if (questionForm.options.length < 2) questionForm.options = ['', ''];
        questionForm.correct_answer = null;
    } else if (value === 'true_false') {
        questionForm.correct_answer = 'true';
    } else {
        questionForm.correct_answer = null;
    }
}

function addOption() {
    questionForm.options.push('');
}

function removeOption(index: number) {
    const current = questionForm.correct_answer;
    questionForm.options.splice(index, 1);

    if (current === null) return;

    // Keep the marked answer pointing at the same option after the shift.
    const currentIndex = Number(current);
    if (currentIndex === index) {
        questionForm.correct_answer = null;
    } else if (currentIndex > index) {
        questionForm.correct_answer = String(currentIndex - 1);
    }
}

/** Client-side guard so the server never has to reindex a gappy option list. */
const questionError = ref<string | null>(null);

function submitQuestion() {
    questionError.value = null;

    if (questionForm.type === 'multiple_choice') {
        const filled = questionForm.options.filter((option) => option.trim() !== '');
        if (filled.length < 2) {
            questionError.value = 'Fill in at least two options.';
            return;
        }
        if (questionForm.options.some((option) => option.trim() === '')) {
            questionError.value = 'Remove empty options — a blank row would shift the correct answer.';
            return;
        }
        if (questionForm.correct_answer === null || questionForm.correct_answer === '') {
            questionError.value = 'Mark which option is correct.';
            return;
        }
    }

    if (!props.exam) return;

    const target = questionDialog.value.question;
    const options = { preserveScroll: true, onSuccess: () => (questionDialog.value.open = false) };

    // Only multiple choice carries options. Sending `[]` for the other types
    // keeps the request honest and relies on the server's `exclude_unless` rule.
    const payload = questionForm.transform((data) => ({
        ...data,
        options: data.type === 'multiple_choice' ? data.options : [],
    }));

    if (target) {
        payload.put(routes.admin.examQuestionUpdate(props.exam.id, target.id), options);
        return;
    }

    payload.post(routes.admin.examQuestionStore(props.exam.id), options);
}

// --- Question delete ------------------------------------------------------

const deleteQuestion = ref<ExamQuestion | null>(null);

function destroyQuestion() {
    if (!props.exam || !deleteQuestion.value) return;
    const id = deleteQuestion.value.id;

    // Close on finish, not on click: the dialog stays up until the server
    // confirms, so a failure is visible instead of silently vanishing.
    router.delete(routes.admin.examQuestionDestroy(props.exam.id, id), {
        preserveScroll: true,
        onFinish: () => (deleteQuestion.value = null),
    });
}
</script>

<template>
    <Link
        :href="routes.admin.exams()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to exams
    </Link>

    <div v-if="isEdit && exam" class="grid grid-cols-1 gap-4">
        <Alert v-if="(exam.questions?.length ?? 0) === 0" variant="warning" title="No questions yet">
            Students cannot take this exam until you add at least one question.
        </Alert>
        <Alert v-else-if="!exam.is_published" variant="info" title="This exam is not published">
            Tick “Published” below and save to make it visible to enrolled students.
        </Alert>
    </div>

    <div class="mt-4 grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-3">
        <form class="grid min-w-0 grid-cols-1 content-start gap-6 lg:col-span-2" @submit.prevent="submitSettings">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Exam settings</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ isEdit ? 'Update the title, timing and scoring rules.' : 'Questions are added after the exam is created.' }}
                    </p>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="course_id" required>Course</Label>
                        <Select
                            id="course_id"
                            v-model="settingsForm.course_id"
                            :options="courseOptions"
                            placeholder="Select a course"
                        />
                        <p v-if="settingsForm.errors.course_id" class="text-sm text-destructive">
                            {{ settingsForm.errors.course_id }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="title" required>Exam title</Label>
                        <Input
                            id="title"
                            v-model="settingsForm.title"
                            placeholder="Final assessment"
                            :invalid="!!settingsForm.errors.title"
                        />
                        <p v-if="settingsForm.errors.title" class="text-sm text-destructive">
                            {{ settingsForm.errors.title }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="settingsForm.description"
                            :rows="3"
                            placeholder="What does this exam cover?"
                        />
                        <p v-if="settingsForm.errors.description" class="text-sm text-destructive">
                            {{ settingsForm.errors.description }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="grid grid-cols-1 gap-2">
                            <Label for="duration_minutes">Duration (minutes)</Label>
                            <Input
                                id="duration_minutes"
                                v-model="settingsForm.duration_minutes"
                                type="number"
                                placeholder="Unlimited"
                                :invalid="!!settingsForm.errors.duration_minutes"
                            />
                            <p class="text-xs text-muted-foreground">Leave empty for no time limit.</p>
                            <p v-if="settingsForm.errors.duration_minutes" class="text-sm text-destructive">
                                {{ settingsForm.errors.duration_minutes }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <Label for="passing_score" required>Passing score (%)</Label>
                            <Input
                                id="passing_score"
                                v-model="settingsForm.passing_score"
                                type="number"
                                :invalid="!!settingsForm.errors.passing_score"
                            />
                            <p v-if="settingsForm.errors.passing_score" class="text-sm text-destructive">
                                {{ settingsForm.errors.passing_score }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <Label for="max_attempts" required>Max attempts</Label>
                            <Input
                                id="max_attempts"
                                v-model="settingsForm.max_attempts"
                                type="number"
                                :invalid="!!settingsForm.errors.max_attempts"
                            />
                            <p v-if="settingsForm.errors.max_attempts" class="text-sm text-destructive">
                                {{ settingsForm.errors.max_attempts }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 border-t border-border pt-5 sm:grid-cols-3">
                        <label for="show_results" class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <Checkbox id="show_results" v-model="settingsForm.show_results" />
                            Show results immediately
                        </label>

                        <label for="show_correct_answers" class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <Checkbox id="show_correct_answers" v-model="settingsForm.show_correct_answers" />
                            Show correct answers
                        </label>

                        <label for="is_published" class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <Checkbox id="is_published" v-model="settingsForm.is_published" />
                            Published
                        </label>
                    </div>
                </div>
            </Card>

            <Alert v-if="settingsForm.hasErrors" variant="destructive" title="Please check the form">
                Some fields need your attention before this can be saved.
            </Alert>

            <div class="flex flex-wrap gap-2">
                <Button type="submit" variant="brand" size="lg" :loading="settingsForm.processing">
                    <Save />
                    {{ isEdit ? 'Update settings' : 'Create exam' }}
                </Button>
                <Button :href="routes.admin.exams()" variant="ghost" size="lg" :disabled="settingsForm.processing">
                    Cancel
                </Button>
            </div>
        </form>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card v-if="isEdit && exam">
                <template #header>
                    <h2 class="text-base font-bold">At a glance</h2>
                </template>

                <dl class="grid grid-cols-1 gap-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Status</dt>
                        <dd>
                            <Badge :variant="exam.is_published ? 'success' : 'secondary'">
                                {{ exam.is_published ? 'Published' : 'Draft' }}
                            </Badge>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Questions</dt>
                        <dd class="font-semibold tabular-nums">{{ exam.questions?.length ?? 0 }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Total points</dt>
                        <dd class="font-semibold tabular-nums">{{ totalPoints }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Attempts</dt>
                        <dd class="font-semibold tabular-nums">{{ exam.attempts_count ?? 0 }}</dd>
                    </div>
                </dl>

                <div class="mt-5 border-t border-border pt-5">
                    <Button :href="routes.admin.examResults(exam.id)" variant="outline" block>
                        <BarChart3 />
                        View results
                    </Button>
                </div>
            </Card>

            <Card v-else>
                <template #header>
                    <h2 class="flex items-center gap-2 text-base font-bold">
                        <Info class="size-4" />
                        How exams work
                    </h2>
                </template>

                <ul class="grid grid-cols-1 gap-3 text-sm text-muted-foreground">
                    <li><strong class="text-foreground">Duration</strong> — time limit for a single attempt; empty means unlimited.</li>
                    <li><strong class="text-foreground">Passing score</strong> — the minimum percentage needed to pass.</li>
                    <li><strong class="text-foreground">Max attempts</strong> — how many times one student may submit.</li>
                    <li><strong class="text-foreground">Show results</strong> — reveal the score right after submitting.</li>
                    <li><strong class="text-foreground">Show correct answers</strong> — let students review what was right.</li>
                    <li><strong class="text-foreground">Published</strong> — make the exam visible to enrolled students.</li>
                </ul>

                <p class="mt-4 text-xs text-muted-foreground">
                    An exam only becomes visible once it is published, has at least one question, and the
                    student's enrollment is complete.
                </p>
            </Card>
        </div>
    </div>

    <!-- Questions (edit only — a question needs a parent exam) -->
    <Card v-if="isEdit && exam" class="mt-6 min-w-0">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">Questions</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ exam.questions?.length ?? 0 }}
                        {{ (exam.questions?.length ?? 0) === 1 ? 'question' : 'questions' }} ·
                        {{ totalPoints }} {{ totalPoints === 1 ? 'point' : 'points' }}
                    </p>
                </div>
                <Button variant="brand" @click="openCreateQuestion">
                    <Plus />
                    Add question
                </Button>
            </div>
        </template>

        <EmptyState
            v-if="(exam.questions?.length ?? 0) === 0"
            :icon="CircleHelp"
            title="No questions yet"
            description="Add at least one question before publishing this exam."
        >
            <Button variant="outline" @click="openCreateQuestion">
                <Plus />
                Add the first question
            </Button>
        </EmptyState>

        <ol v-else class="grid grid-cols-1 gap-4">
            <li
                v-for="(question, index) in exam.questions"
                :key="question.id"
                class="rounded-xl border border-border p-4"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                Question {{ index + 1 }}
                            </span>
                            <Badge :variant="typeVariants[question.type] ?? 'secondary'">
                                {{ typeLabels[question.type] ?? question.type }}
                            </Badge>
                            <Badge variant="muted">
                                {{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}
                            </Badge>
                        </div>

                        <p class="whitespace-pre-wrap font-medium">{{ question.question }}</p>

                        <ul v-if="question.type === 'multiple_choice'" class="mt-3 grid grid-cols-1 gap-1.5">
                            <li
                                v-for="(option, optionIndex) in question.options ?? []"
                                :key="optionIndex"
                                class="flex items-start gap-2 text-sm"
                                :class="String(optionIndex) === question.correct_answer ? 'font-semibold text-success' : 'text-muted-foreground'"
                            >
                                <Check
                                    v-if="String(optionIndex) === question.correct_answer"
                                    class="mt-0.5 size-3.5 shrink-0"
                                />
                                <span v-else class="mt-0.5 size-3.5 shrink-0 text-center text-xs leading-none">
                                    {{ String.fromCharCode(65 + optionIndex) }}
                                </span>
                                <span>{{ option }}</span>
                            </li>
                        </ul>

                        <p v-else-if="question.type === 'true_false'" class="mt-3 text-sm text-muted-foreground">
                            Correct answer:
                            <strong class="text-success">
                                {{ question.correct_answer === 'true' ? 'True' : 'False' }}
                            </strong>
                        </p>

                        <p v-else class="mt-3 text-sm text-muted-foreground">
                            Graded manually — students submit free text.
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            aria-label="Edit question"
                            @click="openEditQuestion(question)"
                        >
                            <Pencil />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            aria-label="Delete question"
                            @click="deleteQuestion = question"
                        >
                            <Trash2 />
                        </Button>
                    </div>
                </div>
            </li>
        </ol>
    </Card>

    <!-- Add / edit question -->
    <Dialog
        :open="questionDialog.open"
        size="lg"
        :title="questionDialog.question ? 'Edit question' : 'Add question'"
        @update:open="(value: boolean) => !value && (questionDialog.open = false)"
    >
        <div class="grid grid-cols-1 gap-5">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="grid grid-cols-1 gap-2">
                    <Label for="question_type" required>Question type</Label>
                    <Select
                        id="question_type"
                        :model-value="questionForm.type"
                        :options="questionTypeOptions"
                        @update:model-value="onTypeChange"
                    />
                    <p v-if="questionForm.errors.type" class="text-sm text-destructive">
                        {{ questionForm.errors.type }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <Label for="question_points" required>Points</Label>
                    <Input
                        id="question_points"
                        v-model="questionForm.points"
                        type="number"
                        :invalid="!!questionForm.errors.points"
                    />
                    <p v-if="questionForm.errors.points" class="text-sm text-destructive">
                        {{ questionForm.errors.points }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <Label for="question_text" required>Question</Label>
                <Textarea
                    id="question_text"
                    v-model="questionForm.question"
                    :rows="3"
                    placeholder="What does the acronym MVC stand for?"
                    :invalid="!!questionForm.errors.question"
                />
                <p v-if="questionForm.errors.question" class="text-sm text-destructive">
                    {{ questionForm.errors.question }}
                </p>
            </div>

            <!-- Multiple choice -->
            <div v-if="questionForm.type === 'multiple_choice'" class="grid grid-cols-1 gap-3">
                <Label required>Options</Label>

                <div
                    v-for="(_, index) in questionForm.options"
                    :key="index"
                    class="flex items-center gap-2"
                >
                    <input
                        type="radio"
                        class="size-4 shrink-0 accent-brand-600"
                        :name="'correct_answer'"
                        :value="String(index)"
                        :checked="questionForm.correct_answer === String(index)"
                        :aria-label="`Mark option ${index + 1} as correct`"
                        @change="questionForm.correct_answer = String(index)"
                    />
                    <Input
                        v-model="questionForm.options[index]"
                        :placeholder="`Option ${index + 1}`"
                    />
                    <Button
                        v-if="questionForm.options.length > 2"
                        variant="ghost"
                        size="icon-sm"
                        :aria-label="`Remove option ${index + 1}`"
                        @click="removeOption(index)"
                    >
                        <X />
                    </Button>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button variant="outline" size="sm" @click="addOption">
                        <Plus />
                        Add option
                    </Button>
                    <p class="text-xs text-muted-foreground">Select the radio button beside the correct option.</p>
                </div>

                <p v-if="questionForm.errors.options" class="text-sm text-destructive">
                    {{ questionForm.errors.options }}
                </p>
                <p v-if="questionForm.errors.correct_answer" class="text-sm text-destructive">
                    {{ questionForm.errors.correct_answer }}
                </p>
            </div>

            <!-- True / false -->
            <div v-else-if="questionForm.type === 'true_false'" class="grid grid-cols-1 gap-2">
                <Label for="correct_answer_tf" required>Correct answer</Label>
                <Select
                    id="correct_answer_tf"
                    v-model="questionForm.correct_answer"
                    :options="[
                        { value: 'true', label: 'True' },
                        { value: 'false', label: 'False' },
                    ]"
                />
                <p v-if="questionForm.errors.correct_answer" class="text-sm text-destructive">
                    {{ questionForm.errors.correct_answer }}
                </p>
            </div>

            <Alert v-else variant="info" title="Essay questions are graded by hand">
                Students submit free text. After they submit, open their attempt from the results page to
                award points and leave feedback.
            </Alert>

            <Alert v-if="questionError" variant="destructive" :title="questionError ?? undefined" hide-icon />
        </div>

        <template #footer>
            <Button variant="outline" :disabled="questionForm.processing" @click="questionDialog.open = false">
                Cancel
            </Button>
            <Button variant="brand" :loading="questionForm.processing" @click="submitQuestion">
                <Save />
                {{ questionDialog.question ? 'Save question' : 'Add question' }}
            </Button>
        </template>
    </Dialog>

    <!-- Delete question -->
    <Dialog
        :open="deleteQuestion !== null"
        size="sm"
        title="Delete question"
        description="This removes the question and every student answer recorded against it. This cannot be undone."
        @update:open="(value: boolean) => !value && (deleteQuestion = null)"
    >
        <p v-if="deleteQuestion" class="flex gap-2 rounded-lg bg-muted p-3 text-sm">
            <AlertTriangle class="mt-0.5 size-4 shrink-0 text-warning" />
            <span class="line-clamp-3">{{ deleteQuestion.question }}</span>
        </p>

        <template #footer>
            <Button variant="outline" @click="deleteQuestion = null">Cancel</Button>
            <Button variant="destructive" @click="destroyQuestion">
                <Trash2 />
                Delete question
            </Button>
        </template>
    </Dialog>
</template>
