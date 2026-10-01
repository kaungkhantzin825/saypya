<script setup lang="ts">
import type { Component } from 'vue';
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        icon?: Component;
        title: string;
        description?: string;
        class?: string;
    }>(),
    {},
);
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-border bg-card/50 px-6 py-16 text-center',
                $props.class,
            )
        "
    >
        <div v-if="icon" class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400">
            <component :is="icon" class="size-6" />
        </div>

        <div class="grid gap-1.5">
            <h3 class="text-base font-semibold">{{ title }}</h3>
            <p v-if="description" class="mx-auto max-w-sm text-sm text-muted-foreground">{{ description }}</p>
        </div>

        <div v-if="$slots.default" class="mt-1">
            <slot />
        </div>
    </div>
</template>
