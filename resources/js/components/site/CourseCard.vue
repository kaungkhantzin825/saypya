<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BookOpen, Clock, Heart, PlayCircle, Users } from 'lucide-vue-next';
import { Badge, Progress } from '@/components/ui';
import StarRating from './StarRating.vue';
import PriceTag from './PriceTag.vue';
import { useAuth } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import { clampPercent, cn, formatDuration } from '@/lib/utils';
import type { Course } from '@/types';

const props = defineProps<{
    course: Course;
    /** When set, the card renders as "in progress" for an enrolled student. */
    progress?: number | null;
    class?: string;
}>();

const { isAuthenticated } = useAuth();

const levelLabels: Record<string, string> = {
    beginner: 'Beginner',
    intermediate: 'Intermediate',
    advanced: 'Advanced',
    all_levels: 'All levels',
};

const levelLabel = computed(() => levelLabels[props.course.level] ?? props.course.level);
const enrolled = computed(() => props.progress !== null && props.progress !== undefined);
const inWishlist = computed(() => props.course.is_in_wishlist === true);

const wishlistBusy = ref(false);

function toggleWishlist() {
    if (!isAuthenticated.value) {
        router.visit(routes.login());
        return;
    }

    wishlistBusy.value = true;

    router.post(
        routes.toggleWishlist(props.course.id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                wishlistBusy.value = false;
            },
        },
    );
}

const lessonsLabel = computed(() => {
    const count = props.course.total_lessons ?? 0;
    return count ? `${count} ${count === 1 ? 'lesson' : 'lessons'}` : null;
});

const durationLabel = computed(() => {
    const hours = Number(props.course.duration_hours ?? 0);
    if (hours > 0) return `${hours}h`;
    const minutes = Number(props.course.total_duration ?? 0);
    return minutes > 0 ? formatDuration(minutes) : null;
});
</script>

<template>
    <article
        :class="
            cn(
                'group relative flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-soft transition-all duration-300',
                'hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800',
                props.class,
            )
        "
    >
        <!-- Thumbnail -->
        <a :href="routes.course(course.slug)" class="relative block aspect-video overflow-hidden bg-muted">
            <img
                :src="course.thumbnail_url"
                :alt="course.title"
                loading="lazy"
                decoding="async"
                class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            <span
                v-if="course.is_featured"
                class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-brand-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm"
            >
                Featured
            </span>

            <span
                v-if="course.preview_video_url"
                class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            >
                <PlayCircle class="size-12 text-white drop-shadow-lg" />
            </span>
        </a>

        <!-- Wishlist -->
        <button
            v-if="!enrolled"
            type="button"
            :disabled="wishlistBusy"
            :aria-label="inWishlist ? 'Remove from wishlist' : 'Save to wishlist'"
            class="absolute right-3 top-3 flex size-9 items-center justify-center rounded-full bg-white/90 text-muted-foreground shadow-sm backdrop-blur transition-colors hover:bg-white hover:text-destructive disabled:opacity-60 dark:bg-slate-900/80"
            @click.prevent="toggleWishlist"
        >
            <Heart :class="cn('size-4', inWishlist && 'fill-destructive text-destructive')" />
        </button>

        <div class="flex flex-1 flex-col gap-3 p-5">
            <div class="flex flex-wrap items-center gap-2">
                <Badge v-if="course.category" variant="brand">{{ course.category.name }}</Badge>
                <Badge variant="muted">{{ levelLabel }}</Badge>
            </div>

            <h3 class="text-base font-bold leading-snug">
                <a
                    :href="routes.course(course.slug)"
                    class="line-clamp-2 transition-colors hover:text-brand-700 dark:hover:text-brand-400"
                >
                    {{ course.title }}
                </a>
            </h3>

            <p v-if="course.short_description" class="line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                {{ course.short_description }}
            </p>

            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <img
                    v-if="course.instructor"
                    :src="course.instructor.avatar_url"
                    :alt="course.instructor.name"
                    loading="lazy"
                    class="size-5 rounded-full object-cover ring-1 ring-border"
                />
                <span class="truncate">{{ course.instructor?.name ?? 'Sanpya Academy' }}</span>
            </div>

            <StarRating
                :rating="course.average_rating ?? 0"
                :count="course.total_reviews ?? 0"
                show-value
            />

            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                <span v-if="lessonsLabel" class="inline-flex items-center gap-1.5">
                    <BookOpen class="size-3.5" />{{ lessonsLabel }}
                </span>
                <span v-if="durationLabel" class="inline-flex items-center gap-1.5">
                    <Clock class="size-3.5" />{{ durationLabel }}
                </span>
                <span v-if="course.total_students" class="inline-flex items-center gap-1.5">
                    <Users class="size-3.5" />{{ course.total_students }}
                </span>
            </div>

            <!-- Progress (enrolled view) -->
            <div v-if="enrolled" class="mt-auto grid gap-2 pt-2">
                <div class="flex items-center justify-between text-xs font-medium">
                    <span class="text-muted-foreground">Progress</span>
                    <span class="text-foreground">{{ clampPercent(progress) }}%</span>
                </div>
                <Progress :value="progress" auto-tone size="sm" />
            </div>

            <!-- Price / CTA -->
            <div v-else class="mt-auto flex items-center justify-between gap-3 border-t border-border pt-4">
                <PriceTag
                    :price="course.price"
                    :discount-price="course.discount_price"
                    size="md"
                />
                <a
                    :href="routes.course(course.slug)"
                    class="inline-flex h-9 shrink-0 items-center rounded-lg bg-brand-600 px-3.5 text-xs font-semibold text-white transition-colors hover:bg-brand-700"
                >
                    View
                </a>
            </div>
        </div>
    </article>
</template>
