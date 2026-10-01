<script setup lang="ts">
import { RadioGroupIndicator, RadioGroupItem, RadioGroupRoot } from 'reka-ui';
import { cn } from '@/lib/utils';
import type { RadioOption } from '@/types/ui';

const props = defineProps<{
    modelValue?: string | null;
    options: RadioOption[];
    name?: string;
    disabled?: boolean;
    class?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const isDisabled = (option: RadioOption) => props.disabled;
</script>

<template>
    <RadioGroupRoot
        :model-value="modelValue ?? undefined"
        :disabled="disabled"
        :class="cn('grid gap-3', props.class)"
        @update:model-value="emit('update:modelValue', String($event))"
    >
        <label
            v-for="option in options"
            :key="option.value"
            :for="name ? `${name}-${option.value}` : undefined"
            :class="
                cn(
                    'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors',
                    'hover:bg-accent/60',
                    modelValue === option.value
                        ? 'border-primary bg-primary/5 ring-1 ring-primary/30'
                        : 'border-border',
                    isDisabled(option) && 'cursor-not-allowed opacity-60',
                )
            "
        >
            <RadioGroupItem
                :id="name ? `${name}-${option.value}` : undefined"
                :value="option.value"
                :disabled="isDisabled(option)"
                class="mt-0.5 size-4 shrink-0 rounded-full border border-input shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30 data-[state=checked]:border-primary disabled:cursor-not-allowed"
            >
                <RadioGroupIndicator class="flex size-full items-center justify-center after:block after:size-2 after:rounded-full after:bg-primary" />
            </RadioGroupItem>

            <span class="grid gap-1 leading-snug">
                <span class="text-sm font-medium">{{ option.label }}</span>
                <span v-if="option.description" class="text-xs text-muted-foreground">
                    {{ option.description }}
                </span>
            </span>
        </label>
    </RadioGroupRoot>
</template>
