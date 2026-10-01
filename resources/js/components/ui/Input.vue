<script setup lang="ts">
import { computed, useAttrs, type Component } from 'vue';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        type?: string;
        placeholder?: string;
        disabled?: boolean;
        invalid?: boolean;
        class?: string;
        /** Renders an icon inside the field on the left. */
        icon?: Component;
    }>(),
    { type: 'text' },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const attrs = useAttrs();

const classes = computed(() =>
    cn(
        'flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors',
        'placeholder:text-muted-foreground',
        'focus-visible:outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/25',
        'disabled:cursor-not-allowed disabled:opacity-50',
        'file:border-0 file:bg-transparent file:text-sm file:font-medium',
        props.icon && 'pl-10',
        props.invalid && 'border-destructive focus-visible:border-destructive focus-visible:ring-destructive/25',
        props.class,
    ),
);
</script>

<template>
    <div class="relative">
        <component
            :is="icon"
            v-if="icon"
            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <input
            v-bind="attrs"
            :type="type"
            :value="modelValue ?? ''"
            :placeholder="placeholder"
            :disabled="disabled"
            :class="classes"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
    </div>
</template>
