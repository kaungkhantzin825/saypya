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
        side?: 'left' | 'right' | 'bottom';
        class?: string;
    }>(),
    { side: 'right' },
);

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const sideClasses: Record<string, string> = {
    right: 'inset-y-0 right-0 h-full w-[min(22rem,90vw)] border-l data-[state=closed]:slide-out-to-right data-[state=open]:slide-in-from-right',
    left: 'inset-y-0 left-0 h-full w-[min(22rem,90vw)] border-r data-[state=closed]:slide-out-to-left data-[state=open]:slide-in-from-left',
    bottom: 'inset-x-0 bottom-0 max-h-[85vh] rounded-t-2xl border-t data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom',
};

const contentClasses = computed(() =>
    cn(
        'fixed z-50 flex flex-col gap-4 border-border bg-background p-6 shadow-lift transition ease-in-out',
        'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:duration-200 data-[state=open]:duration-300',
        sideClasses[props.side],
        props.class,
    ),
);
</script>

<template>
    <DialogRoot :open="open" @update:open="emit('update:open', $event)">
        <DialogTrigger v-if="$slots.trigger" as-child>
            <slot name="trigger" />
        </DialogTrigger>

        <DialogPortal>
            <DialogOverlay
                class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
            />

            <DialogContent :class="contentClasses">
                <div class="flex items-start justify-between gap-4">
                    <div class="grid gap-1">
                        <DialogTitle v-if="title" class="text-lg font-semibold tracking-tight">
                            {{ title }}
                        </DialogTitle>
                        <VisuallyHidden v-else>
                            <DialogTitle>Panel</DialogTitle>
                        </VisuallyHidden>

                        <DialogDescription v-if="description" class="text-sm text-muted-foreground">
                            {{ description }}
                        </DialogDescription>
                        <VisuallyHidden v-else>
                            <DialogDescription />
                        </VisuallyHidden>
                    </div>

                    <DialogClose
                        class="rounded-md p-1 text-muted-foreground opacity-70 transition-opacity hover:bg-accent hover:opacity-100"
                        aria-label="Close"
                    >
                        <X class="size-4" />
                    </DialogClose>
                </div>

                <div class="flex-1 overflow-y-auto scrollbar-slim">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="border-t border-border pt-4">
                    <slot name="footer" />
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
