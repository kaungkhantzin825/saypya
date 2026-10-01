<script setup lang="ts">
import type { Component } from 'vue';
import {
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from 'reka-ui';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import type { MenuItem } from '@/types/ui';

const props = withDefaults(
    defineProps<{
        items: MenuItem[];
        align?: 'start' | 'center' | 'end';
        side?: 'top' | 'right' | 'bottom' | 'left';
        sideOffset?: number;
        class?: string;
    }>(),
    { align: 'end', side: 'bottom', sideOffset: 6 },
);

const itemClasses = (item: MenuItem) =>
    cn(
        'relative flex w-full cursor-pointer select-none items-center gap-2 rounded-md px-2 py-2 text-sm outline-none transition-colors',
        'data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground',
        'data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
        item.destructive && 'text-destructive data-[highlighted]:bg-destructive/10 data-[highlighted]:text-destructive',
    );
</script>

<template>
    <DropdownMenuRoot>
        <DropdownMenuTrigger as-child>
            <slot name="trigger" />
        </DropdownMenuTrigger>

        <DropdownMenuPortal>
            <DropdownMenuContent
                :align="align"
                :side="side"
                :side-offset="sideOffset"
                :class="
                    cn(
                        'z-50 min-w-[12rem] overflow-hidden rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-lift',
                        'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
                        props.class,
                    )
                "
            >
                <template v-for="(item, index) in items" :key="index">
                    <DropdownMenuSeparator v-if="item.separator" class="-mx-1 my-1 h-px bg-border" />

                    <DropdownMenuLabel
                        v-else-if="item.heading"
                        class="px-2 py-1.5 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        {{ item.label }}
                    </DropdownMenuLabel>

                    <DropdownMenuItem v-else-if="item.href" as-child :disabled="item.disabled">
                        <a v-if="item.external" :href="item.href" :class="itemClasses(item)">
                            <component :is="item.icon" v-if="item.icon" class="size-4 opacity-80" />
                            <span>{{ item.label }}</span>
                        </a>
                        <Link v-else :href="item.href" :class="itemClasses(item)">
                            <component :is="item.icon" v-if="item.icon" class="size-4 opacity-80" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuItem
                        v-else
                        :disabled="item.disabled"
                        :class="itemClasses(item)"
                        @select="item.onSelect?.()"
                    >
                        <component :is="item.icon" v-if="item.icon" class="size-4 opacity-80" />
                        <span>{{ item.label }}</span>
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>
