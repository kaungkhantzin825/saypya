<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Award, BookOpen, Calendar, GraduationCap, MessageSquare, Star, Users } from 'lucide-vue-next';
import { Avatar, Badge, Card, EmptyState, Pagination, Separator } from '@/components/ui';
import CourseCard from '@/components/site/CourseCard.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import StarRating from '@/components/site/StarRating.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';
import { formatDate, timeAgo } from '@/lib/utils';
import type { Course, InstructorSummary, Paginated, Review } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    instructor: InstructorSummary & { bio?: string | null; created_at?: string };
    courses: Paginated<Course>;
    stats: {
        total_courses: number;
        total_students: number;
        average_rating: number;
        total_reviews: number;
    };
    recentReviews: Review[];
}>();

const hasCourses = computed(() => (props.courses.data?.length ?? 0) > 0);
const reviews = computed(() => props.recentReviews ?? []);

const statTiles = computed(() => [
    { icon: BookOpen, label: 'Courses', value: props.stats?.total_courses ?? 0, raw: true },
    { icon: Users, label: 'Students', value: props.stats?.total_students ?? 0, raw: true },
    { icon: Star, label: 'Rating', value: props.stats?.average_rating ?? 0, raw: false },
    { icon: MessageSquare, label: 'Reviews', value: props.stats?.total_reviews ?? 0, raw: true },
]);

function displayValue(tile: { value: number; raw: boolean }): string {
    return tile.raw ? tile.value.toLocaleString('en-US') : tile.value.toFixed(1);
}
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="border-b border-border bg-muted/40">
        <div class="page-container py-10 sm:py-14">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center">
                    <Avatar :src="instructor.avatar_url" :name="instructor.name" size="2xl" />

                    <div class="min-w-0">
                        <Badge variant="brand" class="mb-2">
                            <GraduationCap class="size-3" />
                            Instructor
                        </Badge>
                        <h1 class="text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">
                            {{ instructor.name }}
                        </h1>

                        <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
                            <StarRating
                                :rating="stats?.average_rating ?? 0"
                                :count="stats?.total_reviews ?? 0"
                                size="md"
                                show-value
                            />
                            <span v-if="instructor.created_at" class="inline-flex items-center gap-1.5">
                                <Calendar class="size-4" />
                                Joined {{ formatDate(instructor.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stat tiles -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:shrink-0">
                    <div
                        v-for="tile in statTiles"
                        :key="tile.label"
                        class="rounded-xl border border-border bg-card px-4 py-3 text-center shadow-soft"
                    >
                        <span
                            class="mx-auto mb-2 flex size-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                        >
                            <component :is="tile.icon" class="size-4" />
                        </span>
                        <p class="text-xl font-extrabold leading-none tracking-tight">
                            {{ displayValue(tile) }}
                        </p>
                        <p class="mt-1 text-[11px] font-medium uppercase tracking-wide text-muted-foreground">
                            {{ tile.label }}
                        </p>
                    </div>
                </div>
            </div>

            <p
                v-if="instructor.bio"
                class="mt-8 max-w-3xl whitespace-pre-line text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base"
            >
                {{ instructor.bio }}
            </p>
        </div>
    </section>

    <div class="page-container py-10 sm:py-14">
        <!-- ===================================================== Courses -->
        <SectionHeading
            eyebrow="Teaching"
            :title="`Courses by ${instructor.name}`"
            :subtitle="`${stats?.total_courses ?? courses.total} ${(stats?.total_courses ?? courses.total) === 1 ? 'course' : 'courses'} available`"
        />

        <div v-if="hasCourses" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <CourseCard v-for="course in courses.data" :key="course.id" :course="course" />
        </div>

        <EmptyState
            v-else
            class="mt-8"
            :icon="BookOpen"
            title="No published courses yet"
            description="This instructor hasn't published any courses. Check back soon."
        />

        <Pagination
            v-if="hasCourses && courses.last_page > 1"
            class="mt-10"
            :links="courses.links"
            :from="courses.from"
            :to="courses.to"
            :total="courses.total"
        />

        <!-- ===================================================== Reviews -->
        <section v-if="reviews.length" class="mt-16 border-t border-border pt-12">
            <SectionHeading
                eyebrow="Feedback"
                title="What students are saying"
                subtitle="Recent reviews left across this instructor's courses."
            />

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="review in reviews" :key="review.id">
                    <div class="flex items-center gap-3">
                        <Avatar :src="review.user?.avatar_url" :name="review.user?.name" size="md" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ review.user?.name ?? 'Student' }}</p>
                            <div class="mt-0.5 flex items-center gap-2">
                                <StarRating :rating="review.rating" />
                                <span class="text-xs text-muted-foreground">{{ timeAgo(review.created_at) }}</span>
                            </div>
                        </div>
                        <Award class="size-4 shrink-0 text-brand-500" />
                    </div>

                    <Separator class="my-4" />

                    <p
                        v-if="review.comment"
                        class="line-clamp-4 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ review.comment }}
                    </p>
                    <p v-else class="text-sm italic text-muted-foreground">No written comment was left.</p>

                    <p v-if="review.course" class="mt-4 text-xs text-muted-foreground">
                        on
                        <Link
                            :href="routes.course(review.course.slug)"
                            class="font-medium text-brand-700 transition-colors hover:underline dark:text-brand-400"
                        >
                            {{ review.course.title }}
                        </Link>
                    </p>
                </Card>
            </div>
        </section>
    </div>
</template>
