<script setup lang="ts">
import { computed } from 'vue';
import { Compass, Heart } from 'lucide-vue-next';
import { Button, EmptyState, Pagination } from '@/components/ui';
import CourseCard from '@/components/site/CourseCard.vue';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import type { Course, Paginated } from '@/types';

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    wishlist: Paginated<Course>;
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

const items = computed<Course[]>(() => props.wishlist?.data ?? []);
const hasItems = computed(() => items.value.length > 0);
</script>

<template>
    <div class="grid gap-8">
        <template v-if="hasItems">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <CourseCard v-for="course in items" :key="course.id" :course="course" />
            </div>

            <Pagination
                v-if="wishlist.last_page > 1"
                :links="wishlist.links"
                :from="wishlist.from"
                :to="wishlist.to"
                :total="wishlist.total"
            />
        </template>

        <EmptyState
            v-else
            :icon="Heart"
            title="Your wishlist is empty"
            description="Tap the heart on any course to save it here and come back to it whenever you are ready."
        >
            <Button :href="routes.courses()" variant="brand">
                Browse courses
                <Compass />
            </Button>
        </EmptyState>
    </div>
</template>
