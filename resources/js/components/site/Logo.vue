<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        compact?: boolean;
        /** Keep the original full-color logo readable on dark backgrounds. */
        variant?: 'default' | 'light';
        class?: string;
    }>(),
    { variant: 'default' },
);
</script>

<template>
    <span
        :class="cn(
            'inline-flex shrink-0 items-center justify-center rounded-lg bg-white p-1.5',
            variant === 'light' && 'shadow-sm ring-1 ring-white/20',
            props.class,
        )"
    >
        <img
            src="/images/SanPya-Logo.png"
            alt="SanPya Learning logo"
            width="1019"
            height="478"
            :class="cn('block w-auto object-contain', compact ? 'h-8' : 'h-10 sm:h-11')"
            decoding="async"
        />
    </span>
</template>
