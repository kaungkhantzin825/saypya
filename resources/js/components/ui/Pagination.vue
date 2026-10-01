<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    links: PaginationLink[];
    from?: number | null;
    to?: number | null;
    total?: number;
    class?: string;
}>();

/** Drop the redundant "&laquo; Previous" / "Next &raquo;" text for icon buttons. */
const isPrevious = (label: string) => /previous|&laquo;/i.test(label);
const isNext = (label: string) => /next|&raquo;/i.test(label);

const visible = computed(() => props.links.filter((link) => !isPrevious(link.label) && !isNext(link.label)));
const previous = computed(() => props.links.find((link) => isPrevious(link.label)));
const next = computed(() => props.links.find((link) => isNext(link.label)));

const showSummary = computed(() => props.total !== undefined && props.total > 0);

const cellClasses = (active: boolean, disabled: boolean) =>
    cn(
        'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition-colors',
        active
            ? 'border-primary bg-primary text-primary-foreground'
            : 'border-border bg-background text-foreground hover:bg-accent',
        disabled && 'pointer-events-none opacity-40',
    );
</script>

<template>
    <div :class="cn('flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between', props.class)">
        <p v-if="showSummary" class="text-sm text-muted-foreground">
            Showing <span class="font-medium text-foreground">{{ from ?? 0 }}</span>
            to <span class="font-medium text-foreground">{{ to ?? 0 }}</span>
            of <span class="font-medium text-foreground">{{ total }}</span> results
        </p>
        <span v-else />

        <nav v-if="links.length > 3" class="flex items-center gap-1" aria-label="Pagination">
            <Link
                v-if="previous"
                :href="previous.url ?? ''"
                :class="cellClasses(false, !previous.url)"
                preserve-scroll
                aria-label="Previous page"
            >
                <ChevronLeft class="size-4" />
            </Link>

            <template v-for="(link, index) in visible" :key="index">
                <span
                    v-if="link.label === '...'"
                    class="inline-flex h-9 w-9 items-center justify-center text-sm text-muted-foreground"
                >
                    …
                </span>
                <Link
                    v-else
                    :href="link.url ?? ''"
                    :class="cellClasses(link.active, !link.url)"
                    preserve-scroll
                >
                    {{ link.label }}
                </Link>
            </template>

            <Link
                v-if="next"
                :href="next.url ?? ''"
                :class="cellClasses(false, !next.url)"
                preserve-scroll
                aria-label="Next page"
            >
                <ChevronRight class="size-4" />
            </Link>
        </nav>
    </div>
</template>
