<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui';
import { useFormatting } from '@/composables/useApp';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        price?: number | string | null;
        discountPrice?: number | string | null;
        size?: 'sm' | 'md' | 'lg' | 'xl';
        showBadge?: boolean;
        class?: string;
    }>(),
    { size: 'md', showBadge: true },
);

const { formatPrice } = useFormatting();

const basePrice = computed(() => Number(props.price ?? 0));
const discounted = computed(() => Number(props.discountPrice ?? 0));

const hasDiscount = computed(
    () => discounted.value > 0 && basePrice.value > 0 && discounted.value < basePrice.value,
);

const current = computed(() => (hasDiscount.value ? discounted.value : basePrice.value));

const percent = computed(() =>
    hasDiscount.value ? Math.round(((basePrice.value - discounted.value) / basePrice.value) * 100) : 0,
);

const sizeClasses: Record<string, string> = {
    sm: 'text-sm',
    md: 'text-lg',
    lg: 'text-2xl',
    xl: 'text-3xl',
};
</script>

<template>
    <div :class="cn('flex flex-wrap items-baseline gap-x-2 gap-y-1', props.class)">
        <span :class="cn('font-extrabold tracking-tight text-foreground', sizeClasses[size])">
            {{ formatPrice(current) }}
        </span>

        <span v-if="hasDiscount" class="text-sm font-medium text-muted-foreground line-through">
            {{ formatPrice(basePrice) }}
        </span>

        <Badge v-if="hasDiscount && showBadge" variant="destructive">-{{ percent }}%</Badge>
    </div>
</template>
