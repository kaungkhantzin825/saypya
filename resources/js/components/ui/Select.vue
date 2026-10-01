<script setup lang="ts">
import {
    SelectContent,
    SelectIcon,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectPortal,
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui';
import { Check, ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import type { SelectOption } from '@/types/ui';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        options: SelectOption[];
        placeholder?: string;
        disabled?: boolean;
        class?: string;
        id?: string;
    }>(),
    { placeholder: 'Select…' },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const triggerClasses = cn(
    'flex h-10 w-full items-center justify-between gap-2 rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors',
    'data-[placeholder]:text-muted-foreground',
    'focus-visible:outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/25',
    'disabled:cursor-not-allowed disabled:opacity-50',
    props.class,
);
</script>

<template>
    <SelectRoot
        :model-value="modelValue ?? undefined"
        :disabled="disabled"
        @update:model-value="emit('update:modelValue', String($event))"
    >
        <SelectTrigger :id="id" :class="triggerClasses" aria-label="Select an option">
            <SelectValue :placeholder="placeholder" />
            <SelectIcon>
                <ChevronDown class="size-4 opacity-60" />
            </SelectIcon>
        </SelectTrigger>

        <SelectPortal>
            <SelectContent
                position="popper"
                :side-offset="4"
                class="relative z-50 max-h-96 min-w-[10rem] overflow-hidden rounded-lg border border-border bg-popover text-popover-foreground shadow-lift data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95"
            >
                <SelectViewport class="p-1">
                    <SelectItem
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                        :disabled="option.disabled"
                        class="relative flex w-full cursor-pointer select-none items-center rounded-md py-2 pl-8 pr-2 text-sm outline-none data-[disabled]:pointer-events-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[disabled]:opacity-50"
                    >
                        <SelectItemIndicator class="absolute left-2 flex size-4 items-center justify-center">
                            <Check class="size-3.5" />
                        </SelectItemIndicator>
                        <SelectItemText>{{ option.label }}</SelectItemText>
                    </SelectItem>
                </SelectViewport>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>
