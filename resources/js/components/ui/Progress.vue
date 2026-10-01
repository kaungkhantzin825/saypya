<script setup lang="ts">
import { computed } from 'vue';
import { ProgressIndicator, ProgressRoot } from 'reka-ui';
import { cn, clampPercent } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        value?: number | string | null;
        max?: number;
        class?: string;
        indicatorClass?: string;
        /** Colour the bar by completion instead of the flat brand colour. */
        autoTone?: boolean;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { value: 0, max: 100, size: 'md' },
);

const percent = computed(() => clampPercent((Number(props.value ?? 0) / props.max) * 100));

const heightClasses: Record<string, string> = {
    sm: 'h-1.5',
    md: 'h-2',
    lg: 'h-3',
};

const tone = computed(() => {
    if (!props.autoTone) return 'bg-brand-600';
    if (percent.value >= 100) return 'bg-success';
    if (percent.value >= 50) return 'bg-brand-600';
    if (percent.value > 0) return 'bg-warning';
    return 'bg-muted-foreground/40';
});
</script>

<template>
    <ProgressRoot
        :model-value="percent"
        :max="100"
        :class="cn('relative w-full overflow-hidden rounded-full bg-muted', heightClasses[size], props.class)"
    >
        <ProgressIndicator
            :class="cn('h-full w-full flex-1 rounded-full transition-transform duration-500 ease-out', tone, indicatorClass)"
            :style="`transform: translateX(-${100 - percent}%)`"
        />
    </ProgressRoot>
</template>
