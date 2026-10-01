<script setup lang="ts">
import { computed } from 'vue';
import { AlertCircle, CheckCircle2, Info, TriangleAlert } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        variant?: 'info' | 'success' | 'warning' | 'destructive';
        title?: string;
        /** Hide the leading status icon. */
        hideIcon?: boolean;
        class?: string;
    }>(),
    { variant: 'info' },
);

const icons = {
    info: Info,
    success: CheckCircle2,
    warning: TriangleAlert,
    destructive: AlertCircle,
};

const variantClasses: Record<string, string> = {
    info: 'border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-100',
    success:
        'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-100',
    warning:
        'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-100',
    destructive:
        'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950/50 dark:text-red-100',
};

const classes = computed(() =>
    cn('relative flex gap-3 rounded-lg border p-4 text-sm', variantClasses[props.variant], props.class),
);

const Icon = computed(() => icons[props.variant]);
</script>

<template>
    <div :class="classes" role="alert">
        <component :is="Icon" v-if="!hideIcon" class="mt-0.5 size-4 shrink-0" />
        <div class="grid gap-1">
            <p v-if="title" class="font-semibold leading-none">{{ title }}</p>
            <div v-if="$slots.default" class="leading-relaxed opacity-90">
                <slot />
            </div>
        </div>
    </div>
</template>
