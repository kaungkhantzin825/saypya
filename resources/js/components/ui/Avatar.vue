<script setup lang="ts">
import { computed } from 'vue';
import { AvatarFallback, AvatarImage, AvatarRoot } from 'reka-ui';
import { cn, initials } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        src?: string | null;
        name?: string | null;
        size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';
        class?: string;
    }>(),
    { size: 'md' },
);

const sizeClasses: Record<string, string> = {
    xs: 'size-6 text-[10px]',
    sm: 'size-8 text-xs',
    md: 'size-10 text-sm',
    lg: 'size-12 text-base',
    xl: 'size-16 text-lg',
    '2xl': 'size-24 text-2xl',
};

const classes = computed(() =>
    cn(
        'relative flex shrink-0 overflow-hidden rounded-full ring-1 ring-border',
        sizeClasses[props.size],
        props.class,
    ),
);

const fallback = computed(() => initials(props.name));
</script>

<template>
    <AvatarRoot :class="classes">
        <AvatarImage v-if="src" :src="src" :alt="name ?? 'Avatar'" class="aspect-square size-full object-cover" />
        <AvatarFallback
            class="flex size-full items-center justify-center bg-brand-50 font-semibold text-brand-700 dark:bg-brand-950 dark:text-brand-300"
            :delay-ms="src ? 300 : 0"
        >
            {{ fallback }}
        </AvatarFallback>
    </AvatarRoot>
</template>
