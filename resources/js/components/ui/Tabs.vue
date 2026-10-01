<script setup lang="ts">
import type { Component } from 'vue';
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
import { cn } from '@/lib/utils';
import type { TabItem } from '@/types/ui';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        tabs: TabItem[];
        variant?: 'default' | 'underline';
        class?: string;
    }>(),
    { variant: 'default' },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <TabsRoot
        :model-value="modelValue"
        :class="cn('flex flex-col gap-4', props.class)"
        @update:model-value="emit('update:modelValue', String($event))"
    >
        <TabsList
            :class="
                cn(
                    'inline-flex items-center justify-start gap-1 overflow-x-auto scrollbar-slim',
                    variant === 'default' && 'rounded-lg bg-muted p-1',
                    variant === 'underline' && 'w-full justify-start gap-6 border-b border-border',
                )
            "
        >
            <TabsTrigger
                v-for="tab in tabs"
                :key="tab.value"
                :value="tab.value"
                :disabled="tab.disabled"
                :class="
                    cn(
                        'inline-flex items-center gap-2 whitespace-nowrap text-sm font-medium transition-all',
                        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30',
                        'disabled:pointer-events-none disabled:opacity-50',
                        variant === 'default' &&
                            'rounded-md px-3 py-1.5 text-muted-foreground hover:text-foreground data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm',
                        variant === 'underline' &&
                            '-mb-px border-b-2 border-transparent px-1 pb-3 text-muted-foreground hover:text-foreground data-[state=active]:border-brand-600 data-[state=active]:text-brand-700 dark:data-[state=active]:text-brand-400',
                    )
                "
            >
                <component :is="tab.icon" v-if="tab.icon" class="size-4" />
                {{ tab.label }}
                <span
                    v-if="tab.badge !== undefined"
                    class="rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-semibold text-muted-foreground"
                >
                    {{ tab.badge }}
                </span>
            </TabsTrigger>
        </TabsList>

        <TabsContent
            v-for="tab in tabs"
            :key="tab.value"
            :value="tab.value"
            class="focus-visible:outline-none data-[state=active]:animate-in data-[state=active]:fade-in-0"
        >
            <slot :name="tab.value" />
        </TabsContent>
    </TabsRoot>
</template>
