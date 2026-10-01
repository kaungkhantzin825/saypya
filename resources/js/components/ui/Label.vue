<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
    for?: string;
    required?: boolean;
    class?: string;
}>();
</script>

<template>
    <label
        :for="props.for"
        :class="cn('text-sm font-medium leading-none text-foreground peer-disabled:cursor-not-allowed peer-disabled:opacity-70', props.class)"
    >
        <slot />
        <span v-if="required" class="ml-0.5 text-destructive" aria-hidden="true">*</span>
    </label>
</template>
