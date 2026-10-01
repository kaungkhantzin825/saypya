<script setup lang="ts">
import { computed, useSlots } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        /** Set false to control all padding yourself. */
        padded?: boolean;
        /** Adds a hover lift + border highlight, used by course cards. */
        interactive?: boolean;
        class?: string;
        headerClass?: string;
        contentClass?: string;
        footerClass?: string;
    }>(),
    { padded: true },
);

const slots = useSlots();

const hasHeader = computed(() => !!slots.header);
const hasFooter = computed(() => !!slots.footer);

const outer = computed(() =>
    cn(
        'rounded-xl border border-border bg-card text-card-foreground shadow-soft',
        props.interactive &&
            'transition-all duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800',
        props.class,
    ),
);

const headerClasses = computed(() => cn('flex flex-col space-y-1.5', props.padded && 'px-6 pt-6 pb-4', props.headerClass));

const contentClasses = computed(() =>
    cn(props.padded && (hasHeader.value ? 'px-6 pb-6' : 'p-6'), props.contentClass),
);

const footerClasses = computed(() =>
    cn('flex items-center', props.padded && 'px-6 pb-6', hasHeader.value ? 'pt-0' : 'pt-0', props.footerClass),
);
</script>

<template>
    <div :class="outer">
        <div v-if="hasHeader || $slots.title" :class="headerClasses">
            <slot name="header">
                <h3 v-if="$slots.title" class="text-base font-semibold leading-none tracking-tight">
                    <slot name="title" />
                </h3>
                <p v-if="$slots.description" class="text-sm text-muted-foreground">
                    <slot name="description" />
                </p>
            </slot>
        </div>

        <div :class="contentClasses">
            <slot />
        </div>

        <div v-if="hasFooter" :class="footerClasses">
            <slot name="footer" />
        </div>
    </div>
</template>
