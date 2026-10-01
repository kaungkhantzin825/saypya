import { ref } from 'vue';
import type { Toast, ToastVariant } from '@/types/ui';

/**
 * Tiny toast store. Module-level state so any component (or plain module) can
 * raise a toast without prop-drilling. Rendered by <Toaster />.
 */
const toasts = ref<Toast[]>([]);
let counter = 0;

export function useToast() {
    function dismiss(id: number) {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }

    function push(options: {
        title?: string;
        description?: string;
        variant?: ToastVariant;
        duration?: number;
    }): number {
        const id = ++counter;
        const toast: Toast = {
            id,
            title: options.title,
            description: options.description,
            variant: options.variant ?? 'default',
            duration: options.duration ?? 4500,
        };

        toasts.value = [...toasts.value, toast];

        if (toast.duration > 0) {
            window.setTimeout(() => dismiss(id), toast.duration);
        }

        return id;
    }

    return {
        toasts,
        push,
        dismiss,
        success: (description: string, title = 'Success') => push({ title, description, variant: 'success' }),
        error: (description: string, title = 'Something went wrong') =>
            push({ title, description, variant: 'error', duration: 7000 }),
        warning: (description: string, title = 'Heads up') => push({ title, description, variant: 'warning' }),
        info: (description: string, title = 'Notice') => push({ title, description, variant: 'info' }),
    };
}
