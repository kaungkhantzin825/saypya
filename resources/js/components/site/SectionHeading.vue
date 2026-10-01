<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        /** Small uppercase text above the title. */
        eyebrow?: string;
        align?: 'left' | 'center';
        class?: string;
    }>(),
    { align: 'left' },
);
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between',
                props.align === 'center' && 'sm:flex-col sm:items-center sm:text-center',
                props.class,
            )
        "
    >
        <div :class="cn('max-w-2xl', props.align === 'center' && 'mx-auto text-center')">
            <p
                v-if="eyebrow"
                class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-brand-600 dark:text-brand-400"
            >
                {{ eyebrow }}
            </p>
            <h2 class="text-balance text-2xl font-extrabold tracking-tight sm:text-3xl">
                {{ title }}
            </h2>
            <p v-if="subtitle" class="mt-2 text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base">
                {{ subtitle }}
            </p>
        </div>

        <div v-if="$slots.action" class="shrink-0">
            <slot name="action" />
        </div>
    </div>
</template>
