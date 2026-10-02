<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Calendar, PlayCircle, Sparkles } from 'lucide-vue-next';
import { AppImage, Button, EmptyState, Pagination, Progress } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { clampPercent, formatDate } from '@/lib/utils';
import type { Course, Enrollment, Paginated } from '@/types';

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    enrollments: Paginated<Enrollment>;
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

const items = computed(() =>
    (props.enrollments?.data ?? []).filter(
        (enrollment): enrollment is Enrollment & { course: Course } => !!enrollment.course,
    ),
);

const hasItems = computed(() => items.value.length > 0);

function started(progress: number | null | undefined): boolean {
    return clampPercent(progress) > 0;
}
</script>

<template>
    <div class="grid gap-8">
        <template v-if="hasItems">
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="enrollment in items"
                    :key="enrollment.id"
                    class="flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800"
                >
                    <Link
                        :href="routes.course(enrollment.course.slug)"
                        class="relative block aspect-video overflow-hidden bg-muted"
                    >
                        <AppImage
                            :src="enrollment.course.thumbnail_url"
                            :alt="enrollment.course.title"
                            loading="lazy"
                            class="size-full object-cover transition-transform duration-500 hover:scale-105"
                        />
                    </Link>

                    <div class="flex flex-1 flex-col gap-3 p-5">
                        <h2 class="text-base font-bold leading-snug">
                            <Link
                                :href="routes.course(enrollment.course.slug)"
                                class="line-clamp-2 transition-colors hover:text-brand-700 dark:hover:text-brand-400"
                            >
                                {{ enrollment.course.title }}
                            </Link>
                        </h2>

                        <p class="text-xs text-muted-foreground">
                            {{ enrollment.course.instructor?.name ?? 'Sanpya Academy' }}
                        </p>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between text-xs font-medium">
                                <span class="text-muted-foreground">Progress</span>
                                <span class="tabular-nums text-foreground">
                                    {{ clampPercent(enrollment.progress_percentage) }}%
                                </span>
                            </div>
                            <Progress :value="enrollment.progress_percentage" auto-tone size="sm" />
                        </div>

                        <div class="mt-auto grid gap-4 pt-2">
                            <p
                                v-if="enrollment.enrolled_at || enrollment.created_at"
                                class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
                            >
                                <Calendar class="size-3.5" />
                                Enrolled {{ formatDate(enrollment.enrolled_at ?? enrollment.created_at) }}
                            </p>

                            <Button
                                :href="routes.learn(enrollment.course.slug)"
                                :variant="started(enrollment.progress_percentage) ? 'brand' : 'outline'"
                                block
                            >
                                <PlayCircle v-if="started(enrollment.progress_percentage)" />
                                <Sparkles v-else />
                                {{ started(enrollment.progress_percentage) ? 'Continue' : 'Start learning' }}
                            </Button>
                        </div>
                    </div>
                </article>
            </div>

            <Pagination
                v-if="enrollments.last_page > 1"
                :links="enrollments.links"
                :from="enrollments.from"
                :to="enrollments.to"
                :total="enrollments.total"
            />
        </template>

        <EmptyState
            v-else
            :icon="BookOpen"
            title="No courses yet"
            description="When you enrol in a course it will appear here, along with your progress and next lesson."
        >
            <Button :href="routes.courses()" variant="brand">
                Browse courses
                <PlayCircle />
            </Button>
        </EmptyState>
    </div>
</template>
