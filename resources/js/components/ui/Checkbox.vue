<script setup lang="ts">
import { CheckboxIndicator, CheckboxRoot } from 'reka-ui';
import { Check } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue?: boolean;
    id?: string;
    name?: string;
    disabled?: boolean;
    class?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();
</script>

<template>
    <CheckboxRoot
        :id="id"
        :name="name"
        :model-value="modelValue ?? false"
        :disabled="disabled"
        :class="
            cn(
                'peer size-4 shrink-0 rounded border border-input shadow-sm transition-colors',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30 focus-visible:ring-offset-1',
                'disabled:cursor-not-allowed disabled:opacity-50',
                'data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground',
                props.class,
            )
        "
        @update:model-value="emit('update:modelValue', $event === true)"
    >
        <CheckboxIndicator class="flex items-center justify-center text-current">
            <Check class="size-3.5" stroke-width="3" />
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
