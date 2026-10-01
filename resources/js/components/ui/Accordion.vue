<script setup lang="ts">
import {
    AccordionContent,
    AccordionHeader,
    AccordionItem,
    AccordionRoot,
    AccordionTrigger,
} from 'reka-ui';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import type { AccordionEntry } from '@/types/ui';

const props = withDefaults(
    defineProps<{
        items: AccordionEntry[];
        /** `single` collapses the others; `multiple` allows several open. */
        type?: 'single' | 'multiple';
        defaultValue?: string | string[];
        collapsible?: boolean;
        class?: string;
    }>(),
    { type: 'single', collapsible: true },
);

const classes = cn('divide-y divide-border overflow-hidden rounded-xl border border-border bg-card', props.class);
</script>

<template>
    <AccordionRoot
        :type="type"
        :default-value="defaultValue"
        :collapsible="collapsible"
        :class="classes"
    >
        <AccordionItem
            v-for="item in items"
            :key="item.value"
            :value="item.value"
            :disabled="item.disabled"
            class="group"
        >
            <AccordionHeader>
                <AccordionTrigger
                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold transition-colors hover:bg-accent/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-ring/30 disabled:opacity-50"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <slot name="title" :item="item">
                            <span class="truncate">{{ item.title }}</span>
                        </slot>
                    </span>
                    <span class="flex shrink-0 items-center gap-3">
                        <span v-if="item.meta" class="text-xs font-normal text-muted-foreground">{{ item.meta }}</span>
                        <ChevronDown class="size-4 shrink-0 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
                    </span>
                </AccordionTrigger>
            </AccordionHeader>

            <AccordionContent
                class="overflow-hidden data-[state=open]:animate-accordion-down data-[state=closed]:animate-accordion-up"
            >
                <div class="px-5 pb-4">
                    <slot name="content" :item="item" />
                </div>
            </AccordionContent>
        </AccordionItem>
    </AccordionRoot>
</template>
