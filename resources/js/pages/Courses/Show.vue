<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    ChartColumn,
    Check,
    ChevronRight,
    Clock,
    Heart,
    Lock,
    PlayCircle,
    Users,
    type LucideIcon,
} from 'lucide-vue-next';
import { Accordion, Alert, AppImage, Avatar, Badge, Button, Card, EmptyState, Label, Separator, Tabs, Textarea } from '@/components/ui';
import CourseCard from '@/components/site/CourseCard.vue';
import PriceTag from '@/components/site/PriceTag.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import StarRating from '@/components/site/StarRating.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';
import { formatClock, formatDuration, timeAgo } from '@/lib/utils';
import type { Course, Enrollment, Lesson, Review, Section } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    course: Course;
    isEnrolled: boolean;
    isInWishlist: boolean;
    userReview?: Review | null;
    relatedCourses: Course[];
    previewLessons: Lesson[];
    enrollment?: Enrollment | null;
}>();

/* --------------------------------------------------------------- meta --- */

const levelLabels: Record<string, string> = {
    beginner: 'Beginner',
    intermediate: 'Intermediate',
    advanced: 'Advanced',
    all_levels: 'All levels',
};

const levelLabel = computed(() => levelLabels[props.course.level] ?? props.course.level);

const durationLabel = computed(() => {
    const hours = Number(props.course.duration_hours ?? 0);
    if (hours > 0) return `${hours} hours`;
    const minutes = Number(props.course.total_duration ?? 0);
    return minutes > 0 ? formatDuration(minutes) : null;
});

const lessonCount = computed(() => props.course.total_lessons ?? 0);
const studentCount = computed(() => props.course.total_students ?? 0);

const meta = computed(() =>
    [
        lessonCount.value ? { icon: BookOpen, label: `${lessonCount.value} ${lessonCount.value === 1 ? 'lesson' : 'lessons'}` } : null,
        durationLabel.value ? { icon: Clock, label: durationLabel.value } : null,
        { icon: ChartColumn, label: levelLabel.value },
        studentCount.value ? { icon: Users, label: `${studentCount.value.toLocaleString('en-US')} students` } : null,
    ].filter((entry): entry is { icon: LucideIcon; label: string } => entry !== null),
);

/* -------------------------------------------------------- wishlist --- */

const inWishlist = ref(props.isInWishlist);
const wishlistBusy = ref(false);

function toggleWishlist() {
    const next = !inWishlist.value;
    wishlistBusy.value = true;

    router.post(
        routes.toggleWishlist(props.course.id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                inWishlist.value = next;
            },
            onFinish: () => {
                wishlistBusy.value = false;
            },
        },
    );
}

/* --------------------------------------------------------- curriculum --- */

const sections = computed<Section[]>(() => props.course.sections ?? []);

const curriculum = computed(() =>
    sections.value.map((section) => ({
        value: String(section.id),
        title: section.title,
        meta: `${section.lessons?.length ?? 0} ${(section.lessons?.length ?? 0) === 1 ? 'lesson' : 'lessons'}`,
    })),
);

const defaultSection = computed(() => (sections.value.length ? [String(sections.value[0].id)] : []));

function lessonsFor(value: string): Lesson[] {
    return sections.value.find((section) => String(section.id) === value)?.lessons ?? [];
}

/** Lessons flagged as free previews come from the controller as `previewLessons`. */
const previewIds = computed(() => new Set((props.previewLessons ?? []).map((lesson) => lesson.id)));

function isPreview(lessonId: number): boolean {
    return previewIds.value.has(lessonId);
}

/* -------------------------------------------------------------- tabs --- */

const activeTab = ref('overview');

const tabs = computed(() => [
    { value: 'overview', label: 'Overview' },
    { value: 'curriculum', label: 'Curriculum', badge: lessonCount.value || undefined },
    { value: 'instructor', label: 'Instructor' },
    { value: 'reviews', label: 'Reviews', badge: props.course.total_reviews || undefined },
]);

const learningOutcomes = computed(() => props.course.what_you_learn ?? []);
const requirements = computed(() => props.course.requirements ?? []);
const reviews = computed<Review[]>(() => props.course.reviews ?? []);

/* ------------------------------------------------------ review form --- */

const reviewForm = useForm({
    rating: 5,
    comment: '',
});

function submitReview() {
    reviewForm.post(routes.storeReview(props.course.id), {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset(),
    });
}
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="border-b border-border bg-muted/40">
        <div class="page-container py-8 sm:py-12">
            <!-- Breadcrumb -->
            <nav class="flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground" aria-label="Breadcrumb">
                <Link :href="routes.home()" class="transition-colors hover:text-foreground">Home</Link>
                <ChevronRight class="size-3" />
                <Link :href="routes.courses()" class="transition-colors hover:text-foreground">Courses</Link>
                <template v-if="course.category">
                    <ChevronRight class="size-3" />
                    <Link
                        :href="routes.category(course.category.slug)"
                        class="transition-colors hover:text-foreground"
                    >
                        {{ course.category.name }}
                    </Link>
                </template>
            </nav>

            <div class="mt-6 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <Badge v-if="course.category" variant="brand">{{ course.category.name }}</Badge>
                    <Badge variant="muted">{{ levelLabel }}</Badge>
                    <Badge v-if="course.is_featured" variant="warning">
                        <Award class="size-3" />
                        Featured
                    </Badge>
                </div>

                <h1 class="mt-4 text-balance text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">
                    {{ course.title }}
                </h1>

                <p
                    v-if="course.short_description"
                    class="mt-4 text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base"
                >
                    {{ course.short_description }}
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <StarRating
                        :rating="course.average_rating ?? 0"
                        :count="course.total_reviews ?? 0"
                        size="md"
                        show-value
                    />

                    <Link
                        v-if="course.instructor"
                        :href="routes.instructors(course.instructor.id)"
                        class="group inline-flex items-center gap-2"
                    >
                        <Avatar :src="course.instructor.avatar_url" :name="course.instructor.name" size="sm" />
                        <span
                            class="text-sm font-medium text-muted-foreground transition-colors group-hover:text-foreground"
                        >
                            {{ course.instructor.name }}
                        </span>
                    </Link>
                </div>

                <ul class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
                    <li v-for="entry in meta" :key="entry.label" class="inline-flex items-center gap-1.5">
                        <component :is="entry.icon" class="size-4" />
                        {{ entry.label }}
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <div class="page-container py-10 sm:py-14">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <!-- ================================================= Sidebar -->
            <aside class="lg:order-2 lg:sticky lg:top-24 lg:self-start">
                <Card :padded="false" class="overflow-hidden">
                    <AppImage
                        :src="course.thumbnail_url"
                        :alt="course.title"
                        class="aspect-video w-full object-cover"
                    />

                    <div class="grid gap-5 p-6">
                        <PriceTag
                            :price="course.price"
                            :discount-price="course.discount_price"
                            size="lg"
                        />

                        <Button
                            v-if="isEnrolled"
                            :href="routes.learn(course.slug)"
                            variant="brand"
                            size="lg"
                            block
                        >
                            <PlayCircle />
                            Continue learning
                        </Button>
                        <Button
                            v-else
                            :href="routes.checkout(course.slug)"
                            variant="brand"
                            size="lg"
                            block
                        >
                            Enrol now
                        </Button>

                        <Button
                            variant="outline"
                            block
                            :disabled="wishlistBusy"
                            :aria-pressed="inWishlist"
                            @click="toggleWishlist"
                        >
                            <Heart :class="inWishlist && 'fill-destructive text-destructive'" />
                            {{ inWishlist ? 'Saved to wishlist' : 'Add to wishlist' }}
                        </Button>

                        <Alert
                            v-if="enrollment && enrollment.payment_status === 'pending'"
                            variant="warning"
                            title="Enrolment pending approval"
                        >
                            Your enrolment is awaiting admin confirmation. You'll get full access as soon as your
                            payment is verified.
                        </Alert>

                        <ul class="grid gap-2 text-sm text-muted-foreground">
                            <li v-if="lessonCount" class="flex items-center gap-2">
                                <BookOpen class="size-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                {{ lessonCount }} {{ lessonCount === 1 ? 'lesson' : 'lessons' }}
                            </li>
                            <li v-if="durationLabel" class="flex items-center gap-2">
                                <Clock class="size-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                {{ durationLabel }} of content
                            </li>
                            <li v-if="studentCount" class="flex items-center gap-2">
                                <Users class="size-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                {{ studentCount.toLocaleString('en-US') }} students enrolled
                            </li>
                        </ul>
                    </div>
                </Card>

                <!-- Free previews -->
                <Card v-if="previewLessons?.length" class="mt-5">
                    <template #header>
                        <h2 class="text-base font-semibold leading-none tracking-tight">Free preview</h2>
                        <p class="text-sm text-muted-foreground">Try a few lessons before you enrol.</p>
                    </template>

                    <ul class="grid gap-3">
                        <li
                            v-for="lesson in previewLessons"
                            :key="lesson.id"
                            class="flex items-center gap-3 text-sm"
                        >
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                            >
                                <PlayCircle class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1 truncate">{{ lesson.title }}</span>
                            <span v-if="lesson.video_duration" class="shrink-0 text-xs text-muted-foreground">
                                {{ formatClock(lesson.video_duration) }}
                            </span>
                        </li>
                    </ul>
                </Card>
            </aside>

            <!-- ================================================== Content -->
            <div class="min-w-0 lg:order-1">
                <Tabs v-model="activeTab" :tabs="tabs" variant="underline">
                    <!-- ------------------------------------------------ Overview -->
                    <template #overview>
                        <div class="grid gap-8">
                            <div v-if="course.description">
                                <h2 class="text-lg font-bold">About this course</h2>
                                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">
                                    {{ course.description }}
                                </p>
                            </div>

                            <div v-if="learningOutcomes.length">
                                <h2 class="text-lg font-bold">What you'll learn</h2>
                                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <li
                                        v-for="(item, index) in learningOutcomes"
                                        :key="index"
                                        class="flex items-start gap-2.5 text-sm text-muted-foreground"
                                    >
                                        <Check class="mt-0.5 size-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="requirements.length">
                                <h2 class="text-lg font-bold">Requirements</h2>
                                <ul class="mt-4 grid gap-2.5">
                                    <li
                                        v-for="(item, index) in requirements"
                                        :key="index"
                                        class="flex items-start gap-2.5 text-sm text-muted-foreground"
                                    >
                                        <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-brand-500" />
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                            </div>

                            <EmptyState
                                v-if="!course.description && !learningOutcomes.length && !requirements.length"
                                :icon="BookOpen"
                                title="Course details coming soon"
                                description="The instructor hasn't published the full description yet."
                            />
                        </div>
                    </template>

                    <!-- ---------------------------------------------- Curriculum -->
                    <template #curriculum>
                        <div v-if="curriculum.length" class="grid gap-4">
                            <p class="text-sm text-muted-foreground">
                                {{ sections.length }} {{ sections.length === 1 ? 'section' : 'sections' }} ·
                                {{ lessonCount }} {{ lessonCount === 1 ? 'lesson' : 'lessons' }}
                            </p>

                            <Accordion :items="curriculum" type="multiple" :default-value="defaultSection">
                                <template #content="{ item }">
                                    <ul class="grid gap-1">
                                        <li
                                            v-for="lesson in lessonsFor(item.value)"
                                            :key="lesson.id"
                                            class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm"
                                        >
                                            <span
                                                class="flex size-7 shrink-0 items-center justify-center rounded-md"
                                                :class="
                                                    isPreview(lesson.id)
                                                        ? 'bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400'
                                                        : 'bg-muted text-muted-foreground'
                                                "
                                            >
                                                <PlayCircle v-if="isPreview(lesson.id)" class="size-3.5" />
                                                <Lock v-else class="size-3.5" />
                                            </span>
                                            <span class="min-w-0 flex-1 truncate">{{ lesson.title }}</span>
                                            <Badge v-if="isPreview(lesson.id)" variant="brand" class="shrink-0">
                                                Preview
                                            </Badge>
                                            <span
                                                v-if="lesson.video_duration"
                                                class="shrink-0 text-xs text-muted-foreground"
                                            >
                                                {{ formatClock(lesson.video_duration) }}
                                            </span>
                                        </li>
                                    </ul>
                                </template>
                            </Accordion>
                        </div>

                        <EmptyState
                            v-else
                            :icon="BookOpen"
                            title="No lessons published yet"
                            description="The curriculum for this course will appear here once it's ready."
                        />
                    </template>

                    <!-- ---------------------------------------------- Instructor -->
                    <template #instructor>
                        <div v-if="course.instructor" class="grid gap-5">
                            <div class="flex items-center gap-4">
                                <Avatar
                                    :src="course.instructor.avatar_url"
                                    :name="course.instructor.name"
                                    size="xl"
                                />
                                <div class="min-w-0">
                                    <h2 class="text-lg font-bold">{{ course.instructor.name }}</h2>
                                    <p class="text-sm text-muted-foreground">Instructor</p>
                                </div>
                            </div>

                            <p
                                v-if="course.instructor.bio"
                                class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ course.instructor.bio }}
                            </p>
                            <p v-else class="text-sm text-muted-foreground">
                                This instructor hasn't added a biography yet.
                            </p>

                            <Button
                                :href="routes.instructors(course.instructor.id)"
                                variant="outline"
                                class="w-fit"
                            >
                                View full profile
                                <ChevronRight />
                            </Button>
                        </div>

                        <EmptyState
                            v-else
                            :icon="Users"
                            title="Instructor not listed"
                            description="No instructor has been assigned to this course yet."
                        />
                    </template>

                    <!-- ------------------------------------------------ Reviews -->
                    <template #reviews>
                        <div class="grid gap-8">
                            <div v-if="reviews.length" class="flex flex-wrap items-center gap-6">
                                <div class="text-center">
                                    <p class="text-4xl font-extrabold tracking-tight">
                                        {{ (course.average_rating ?? 0).toFixed(1) }}
                                    </p>
                                    <StarRating :rating="course.average_rating ?? 0" size="md" class="mt-1" />
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ course.total_reviews ?? reviews.length }}
                                        {{ (course.total_reviews ?? reviews.length) === 1 ? 'review' : 'reviews' }}
                                    </p>
                                </div>
                                <Separator orientation="vertical" class="hidden h-16 sm:block" />
                                <p class="max-w-md text-sm text-muted-foreground">
                                    Ratings come from students who completed the course.
                                </p>
                            </div>

                            <!-- Review form -->
                            <Card v-if="isEnrolled && !userReview">
                                <template #header>
                                    <h2 class="text-base font-semibold leading-none tracking-tight">
                                        Leave a review
                                    </h2>
                                    <p class="text-sm text-muted-foreground">
                                        Share what worked for you to help other learners.
                                    </p>
                                </template>

                                <form class="grid gap-5" @submit.prevent="submitReview">
                                    <div class="grid gap-2">
                                        <Label required>Your rating</Label>
                                        <StarRating
                                            v-model:rating="reviewForm.rating"
                                            size="lg"
                                            interactive
                                        />
                                        <p v-if="reviewForm.errors.rating" class="text-xs font-medium text-destructive">
                                            {{ reviewForm.errors.rating }}
                                        </p>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="review-comment">Your review</Label>
                                        <Textarea
                                            id="review-comment"
                                            v-model="reviewForm.comment"
                                            :rows="4"
                                            placeholder="What did you think of the course?"
                                            :invalid="!!reviewForm.errors.comment"
                                        />
                                        <p
                                            v-if="reviewForm.errors.comment"
                                            class="text-xs font-medium text-destructive"
                                        >
                                            {{ reviewForm.errors.comment }}
                                        </p>
                                    </div>

                                    <Button
                                        type="submit"
                                        variant="brand"
                                        class="w-fit"
                                        :loading="reviewForm.processing"
                                    >
                                        Submit review
                                    </Button>
                                </form>
                            </Card>

                            <Alert
                                v-else-if="userReview"
                                variant="success"
                                title="Thanks for your review"
                            >
                                You rated this course
                                <StarRating :rating="userReview.rating" class="mx-1 inline-flex align-middle" />
                                on {{ timeAgo(userReview.created_at) }}.
                            </Alert>

                            <!-- Review list -->
                            <div v-if="reviews.length" class="grid gap-5">
                                <article
                                    v-for="review in reviews"
                                    :key="review.id"
                                    class="rounded-xl border border-border bg-card p-5 shadow-soft"
                                >
                                    <div class="flex items-center gap-3">
                                        <Avatar :src="review.user?.avatar_url" :name="review.user?.name" size="md" />
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold">
                                                {{ review.user?.name ?? 'Student' }}
                                            </p>
                                            <div class="mt-0.5 flex items-center gap-2">
                                                <StarRating :rating="review.rating" />
                                                <span class="text-xs text-muted-foreground">
                                                    {{ timeAgo(review.created_at) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <p
                                        v-if="review.comment"
                                        class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted-foreground"
                                    >
                                        {{ review.comment }}
                                    </p>
                                </article>
                            </div>

                            <EmptyState
                                v-else-if="!isEnrolled"
                                :icon="Award"
                                title="No reviews yet"
                                description="Enrol in this course to be the first to share your experience."
                            />
                        </div>
                    </template>
                </Tabs>
            </div>
        </div>

        <!-- ==================================================== Related -->
        <section v-if="relatedCourses?.length" class="mt-16 sm:mt-20">
            <SectionHeading
                eyebrow="Keep going"
                title="Students also enrolled in"
                subtitle="More courses that pair well with this one."
            />
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <CourseCard v-for="related in relatedCourses" :key="related.id" :course="related" />
            </div>
        </section>
    </div>
</template>
