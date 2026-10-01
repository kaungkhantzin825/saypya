<script setup lang="ts">
import { computed } from 'vue';
import { Separator } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        orientation?: 'horizontal' | 'vertical';
        decorative?: boolean;
        label?: string;
        class?: string;
    }>(),
    { orientation: 'horizontal', decorative: true },
);

const classes = computed(() =>
    cn(
        'shrink-0 bg-border',
        props.orientation === 'horizontal' ? 'h-px w-full' : 'h-full w-px',
        props.class,
    ),
);
</script>

<template>
    <div v-if="label" class="flex items-center gap-3">
        <Separator :orientation="orientation" :decorative="decorative" :class="classes" />
        <span class="shrink-0 text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ label }}</span>
        <Separator :orientation="orientation" :decorative="decorative" :class="classes" />
    </div>
    <Separator v-else :orientation="orientation" :decorative="decorative" :class="classes" />
</template>
