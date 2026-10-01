<script setup lang="ts">
import { computed } from 'vue';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors [&_svg]:size-3',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground',
                brand: 'border-transparent bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300',
                secondary: 'border-transparent bg-secondary text-secondary-foreground',
                success: 'border-transparent bg-success/12 text-success',
                warning: 'border-transparent bg-warning/15 text-warning',
                destructive: 'border-transparent bg-destructive/12 text-destructive',
                info: 'border-transparent bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                outline: 'border-border text-foreground',
                muted: 'border-transparent bg-muted text-muted-foreground',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

type BadgeVariant = VariantProps<typeof badgeVariants>;

const props = withDefaults(
    defineProps<{
        variant?: BadgeVariant['variant'];
        class?: string;
    }>(),
    { variant: 'default' },
);

const classes = computed(() => cn(badgeVariants({ variant: props.variant }), props.class));
</script>

<template>
    <span :class="classes"><slot /></span>
</template>
