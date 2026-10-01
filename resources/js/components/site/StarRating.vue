<script setup lang="ts">
import { computed } from 'vue';
import { Star } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        rating?: number | string | null;
        /** Number of reviews, rendered in parentheses. */
        count?: number | null;
        size?: 'sm' | 'md' | 'lg';
        /** Renders clickable stars for a review form. */
        interactive?: boolean;
        showValue?: boolean;
        class?: string;
    }>(),
    { rating: 0, size: 'sm' },
);

const emit = defineEmits<{
    (e: 'update:rating', value: number): void;
}>();

const stars = [1, 2, 3, 4, 5];

const sizeClasses: Record<string, string> = {
    sm: 'size-3.5',
    md: 'size-4',
    lg: 'size-6',
};

const value = computed(() => Number(props.rating ?? 0));
</script>

<template>
    <div :class="cn('inline-flex items-center gap-1.5', props.class)">
        <div class="inline-flex items-center gap-0.5">
            <component
                :is="interactive ? 'button' : 'span'"
                v-for="star in stars"
                :key="star"
                :type="interactive ? 'button' : undefined"
                :aria-label="interactive ? `Rate ${star} out of 5` : undefined"
                :class="cn(interactive && 'cursor-pointer transition-transform hover:scale-110')"
                @click="interactive && emit('update:rating', star)"
            >
                <Star
                    :class="
                        cn(
                            sizeClasses[size],
                            star <= Math.round(value)
                                ? 'fill-amber-400 text-amber-400'
                                : 'fill-transparent text-muted-foreground/40',
                        )
                    "
                />
            </component>
        </div>

        <span v-if="showValue && value > 0" class="text-xs font-semibold text-foreground">
            {{ value.toFixed(1) }}
        </span>

        <span v-if="count !== undefined && count !== null" class="text-xs text-muted-foreground">
            ({{ count }})
        </span>
    </div>
</template>
