<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cva, type VariantProps } from 'class-variance-authority';
import { Loader2 } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-semibold transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-4 [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground shadow-sm hover:bg-primary/90',
                brand: 'bg-gradient-to-r from-brand-600 to-brand-500 text-white shadow-sm hover:from-brand-700 hover:to-brand-600',
                destructive: 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
                outline: 'border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground',
                secondary: 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
                ghost: 'hover:bg-accent hover:text-accent-foreground',
                link: 'text-primary underline-offset-4 hover:underline',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-9 rounded-md px-3 text-xs',
                lg: 'h-12 rounded-xl px-6 text-base',
                icon: 'size-10',
                'icon-sm': 'size-8 rounded-md',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

type ButtonVariant = VariantProps<typeof buttonVariants>;

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariant['variant'];
        size?: ButtonVariant['size'];
        /** When set, renders an Inertia <Link> (or a plain <a> for external URLs). */
        href?: string;
        /** Force a plain <a> / full page load instead of an Inertia visit. */
        external?: boolean;
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        loading?: boolean;
        block?: boolean;
        class?: string;
    }>(),
    {
        variant: 'default',
        size: 'default',
        type: 'button',
    },
);

const isExternal = computed(
    () => !!props.href && (props.external === true || /^(https?:)?\/\//.test(props.href)),
);
const isLink = computed(() => !!props.href);

const tag = computed(() => {
    if (!props.href) return 'button';
    return isExternal.value ? 'a' : Link;
});

const isDisabled = computed(() => !isLink.value && (props.disabled || props.loading));

const classes = computed(() =>
    cn(buttonVariants({ variant: props.variant, size: props.size }), props.block && 'w-full', props.class),
);
</script>

<template>
    <component
        :is="tag"
        :href="href"
        :type="isLink ? undefined : type"
        :disabled="isDisabled ? true : undefined"
        :aria-disabled="isDisabled ? 'true' : undefined"
        :aria-busy="loading ? 'true' : undefined"
        :target="isExternal ? '_blank' : undefined"
        :rel="isExternal ? 'noopener noreferrer' : undefined"
        :class="classes"
    >
        <Loader2 v-if="loading" class="animate-spin" />
        <slot />
    </component>
</template>
