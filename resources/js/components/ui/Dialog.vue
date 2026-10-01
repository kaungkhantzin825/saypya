<script setup lang="ts">
import { computed } from 'vue';
import {
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
    DialogTrigger,
    VisuallyHidden,
} from 'reka-ui';
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        open?: boolean;
        title?: string;
        description?: string;
        size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
        hideClose?: boolean;
        /** Prevent closing via overlay click / Escape. */
        persistent?: boolean;
        class?: string;
    }>(),
    { size: 'md' },
);

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const sizeClasses: Record<string, string> = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    full: 'max-w-6xl',
};

const contentClasses = computed(() =>
    cn(
        'fixed left-1/2 top-1/2 z-50 grid w-[calc(100vw-2rem)] -translate-x-1/2 -translate-y-1/2 gap-4',
        'rounded-xl border border-border bg-background p-6 shadow-lift',
        'max-h-[calc(100vh-4rem)] overflow-y-auto scrollbar-slim',
        'data-[state=open]:animate-in data-[state=closed]:animate-out',
        'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
        'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
        sizeClasses[props.size],
        props.class,
    ),
);

function onOpenChange(value: boolean) {
    if (!value && props.persistent) return;
    emit('update:open', value);
}
</script>

<template>
    <DialogRoot :open="open" @update:open="onOpenChange">
        <DialogTrigger v-if="$slots.trigger" as-child>
            <slot name="trigger" />
        </DialogTrigger>

        <DialogPortal>
            <DialogOverlay
                class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
            />

            <DialogContent :class="contentClasses">
                <div class="grid gap-1.5 pr-8">
                    <DialogTitle v-if="title" class="text-lg font-semibold leading-none tracking-tight">
                        {{ title }}
                    </DialogTitle>
                    <VisuallyHidden v-else>
                        <DialogTitle>{{ title ?? 'Dialog' }}</DialogTitle>
                    </VisuallyHidden>

                    <DialogDescription v-if="description" class="text-sm text-muted-foreground">
                        {{ description }}
                    </DialogDescription>
                    <VisuallyHidden v-else>
                        <DialogDescription />
                    </VisuallyHidden>
                </div>

                <slot />

                <div v-if="$slots.footer" class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <slot name="footer" />
                </div>

                <DialogClose
                    v-if="!hideClose"
                    class="absolute right-4 top-4 rounded-md p-1 text-muted-foreground opacity-70 transition-opacity hover:bg-accent hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30"
                    aria-label="Close"
                >
                    <X class="size-4" />
                </DialogClose>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
