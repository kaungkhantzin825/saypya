<script setup lang="ts">
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, Info, TriangleAlert, X } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import { cn } from '@/lib/utils';
import type { SharedPageProps } from '@/types';
import type { ToastVariant } from '@/types/ui';

const { toasts, dismiss, push } = useToast();
const page = usePage<SharedPageProps>();

/**
 * Bridge Laravel's session flash messages into toasts. The watcher fires on every
 * Inertia visit because the shared `flash` object is replaced each response.
 */
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) push({ title: 'Success', description: flash.success, variant: 'success' });
        if (flash.error) push({ title: 'Something went wrong', description: flash.error, variant: 'error', duration: 7000 });
        if (flash.warning) push({ title: 'Heads up', description: flash.warning, variant: 'warning' });
        if (flash.info) push({ title: 'Notice', description: flash.info, variant: 'info' });
    },
    { immediate: true },
);

const icons: Record<ToastVariant, unknown> = {
    default: Info,
    info: Info,
    success: CheckCircle2,
    error: AlertCircle,
    warning: TriangleAlert,
};

const tones: Record<ToastVariant, string> = {
    default: 'border-border bg-popover text-popover-foreground',
    info: 'border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-100',
    success:
        'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100',
    error: 'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100',
    warning:
        'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100',
};

const visible = computed(() => toasts.value);
</script>

<template>
    <div
        class="pointer-events-none fixed inset-x-0 top-0 z-[100] flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
        aria-live="polite"
        aria-atomic="false"
    >
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-[-8px] opacity-0 sm:translate-x-4 sm:translate-y-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in absolute"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
            move-class="transition duration-200"
        >
            <div
                v-for="toast in visible"
                :key="toast.id"
                :class="
                    cn(
                        'pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border p-4 shadow-lift',
                        tones[toast.variant],
                    )
                "
                role="status"
            >
                <component :is="icons[toast.variant]" class="mt-0.5 size-4 shrink-0" />

                <div class="min-w-0 flex-1">
                    <p v-if="toast.title" class="text-sm font-semibold leading-none">{{ toast.title }}</p>
                    <p v-if="toast.description" class="mt-1.5 text-sm leading-relaxed opacity-90">
                        {{ toast.description }}
                    </p>
                </div>

                <button
                    type="button"
                    class="-mr-1 -mt-1 rounded-md p-1 opacity-60 transition-opacity hover:opacity-100"
                    aria-label="Dismiss notification"
                    @click="dismiss(toast.id)"
                >
                    <X class="size-3.5" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
