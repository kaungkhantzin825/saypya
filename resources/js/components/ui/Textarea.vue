<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        rows?: number;
        placeholder?: string;
        disabled?: boolean;
        invalid?: boolean;
        class?: string;
    }>(),
    { rows: 4 },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const classes = computed(() =>
    cn(
        'flex w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors',
        'placeholder:text-muted-foreground',
        'focus-visible:outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/25',
        'disabled:cursor-not-allowed disabled:opacity-50',
        props.invalid && 'border-destructive focus-visible:border-destructive focus-visible:ring-destructive/25',
        props.class,
    ),
);
</script>

<template>
    <textarea
        :value="modelValue ?? ''"
        :rows="rows"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="classes"
        @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
</template>
