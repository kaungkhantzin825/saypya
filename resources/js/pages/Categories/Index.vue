<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Briefcase,
    Code,
    Cpu,
    GraduationCap,
    Languages,
    Layers,
    Palette,
    TrendingUp,
    type LucideIcon,
} from 'lucide-vue-next';
import { Button, EmptyState } from '@/components/ui';
import PageHero from '@/components/site/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';
import type { Category } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{ categories: Category[] }>();

/** Cycled accents so each card gets a distinct icon without a per-category config. */
const accents: LucideIcon[] = [
    BookOpen,
    GraduationCap,
    Code,
    Palette,
    Briefcase,
    Languages,
    TrendingUp,
    Cpu,
    Layers,
];

const categories = computed(() => props.categories ?? []);

function iconFor(index: number): LucideIcon {
    return accents[index % accents.length];
}
</script>

<template>
    <PageHero
        eyebrow="Explore"
        title="Browse by category"
        subtitle="Find the right course for where you are right now — from programming and design to business and languages."
        size="compact"
        image="/images/page-headers/categories.webp"
    />

    <div class="page-container py-10 sm:py-14">
        <div v-if="categories.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="(category, index) in categories"
                :key="category.id"
                :href="routes.category(category.slug)"
                class="group flex flex-col rounded-xl border border-border bg-card p-6 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800"
            >
                <span
                    class="mb-5 flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-colors group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950 dark:text-brand-400"
                >
                    <component :is="iconFor(index)" class="size-6" />
                </span>

                <h2
                    class="text-lg font-bold transition-colors group-hover:text-brand-700 dark:group-hover:text-brand-400"
                >
                    {{ category.name }}
                </h2>

                <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-muted-foreground">
                    {{ category.description || 'Courses in this category are being added all the time.' }}
                </p>

                <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
                    <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {{ category.courses_count ?? 0 }}
                        {{ (category.courses_count ?? 0) === 1 ? 'course' : 'courses' }}
                    </span>
                    <ArrowRight
                        class="size-4 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-brand-600"
                    />
                </div>
            </Link>
        </div>

        <EmptyState
            v-else
            :icon="Layers"
            title="No categories yet"
            description="Categories will appear here once courses are organised into them."
        >
            <Button :href="routes.courses()" variant="brand">
                Browse all courses
                <ArrowRight />
            </Button>
        </EmptyState>
    </div>
</template>
