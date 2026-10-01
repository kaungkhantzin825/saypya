<script setup lang="ts">
import { computed } from 'vue';
import { Check, Languages } from 'lucide-vue-next';
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import { useShared } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import type { MenuItem } from '@/types/ui';

const { app } = useShared();

/**
 * Locale switching hits a server route that writes the session, so these are
 * real page loads (external: true) rather than Inertia visits.
 */
const items = computed<MenuItem[]>(() => {
    const entries = Object.entries(app.value.locales ?? {});

    return [
        { label: 'Language', heading: true },
        ...entries.map(([code, label]) => ({
            label: code === app.value.locale ? `${label} ✓` : label,
            href: routes.switchLanguage(code),
            external: true,
        })),
    ];
});

const currentLabel = computed(() => app.value.locales?.[app.value.locale] ?? app.value.locale);
</script>

<template>
    <DropdownMenu :items="items">
        <template #trigger>
            <button
                type="button"
                class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-background px-3 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30"
                aria-label="Change language"
            >
                <Languages class="size-4" />
                <span class="hidden sm:inline">{{ currentLabel }}</span>
            </button>
        </template>
    </DropdownMenu>
</template>
