<script setup lang="ts" generic="T extends object">
import { computed } from 'vue';
import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-vue-next';
import Checkbox from '@/components/ui/Checkbox.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Skeleton from '@/components/ui/Skeleton.vue';
import { cn } from '@/lib/utils';
import type { DataTableColumn } from '@/types/ui';

const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: T[];
        /** Stable identity for each row — required for selection. */
        rowKey?: (row: T) => string | number;
        selectable?: boolean;
        /** Selected row keys (`v-model:selected`). */
        selected?: (string | number)[];
        /** `key:asc|desc`. Two-way with `v-model:sort`. */
        sort?: string;
        loading?: boolean;
        emptyTitle?: string;
        emptyDescription?: string;
        /** Renders the actions column when the slot is present. */
        hasActions?: boolean;
    }>(),
    {
        selectable: false,
        loading: false,
        emptyTitle: 'Nothing to show',
        hasActions: false,
    },
);

const emit = defineEmits<{
    'update:selected': [keys: (string | number)[]];
    'update:sort': [value: string];
}>();

const keyOf = (row: T, index: number): string | number =>
    props.rowKey ? props.rowKey(row) : index;

/**
 * Dynamic column lookup. `T` is a plain interface (no index signature), so a
 * direct `row[column.key]` is not type-safe — this cast is the single place
 * where we opt out, keeping the public generic ergonomic for callers.
 */
const valueOf = (row: T, key: string): unknown => (row as Record<string, unknown>)[key];

const selectableKeys = computed(() => props.rows.map((row, index) => keyOf(row, index)));

const allSelected = computed(
    () => selectableKeys.value.length > 0 && selectableKeys.value.every((key) => props.selected?.includes(key)),
);

const someSelected = computed(
    () => !allSelected.value && selectableKeys.value.some((key) => props.selected?.includes(key)),
);

function toggleAll(checked: boolean) {
    emit('update:selected', checked ? [...selectableKeys.value] : []);
}

function toggleRow(key: string | number, checked: boolean) {
    const current = new Set(props.selected ?? []);
    if (checked) {
        current.add(key);
    } else {
        current.delete(key);
    }
    emit('update:selected', [...current]);
}

/** Current direction for a column, or null when it is not the active sort. */
function sortDirection(column: DataTableColumn): 'asc' | 'desc' | null {
    if (!props.sort) return null;
    const [key, direction] = props.sort.split(':');
    if (key !== column.key) return null;
    return direction === 'desc' ? 'desc' : 'asc';
}

function toggleSort(column: DataTableColumn) {
    if (!column.sortable) return;
    const current = sortDirection(column);
    const next = current === 'asc' ? 'desc' : 'asc';
    emit('update:sort', `${column.key}:${next}`);
}

const alignClass = (align?: DataTableColumn['align']) =>
    align === 'right' ? 'text-right' : align === 'center' ? 'text-center' : 'text-left';
</script>

<template>
    <!--
        `min-w-0` is load-bearing: the table below has a 42rem minimum, and grid
        /flex items default to `min-width: auto`, which would let that minimum
        propagate up and stretch the whole page instead of scrolling inside the
        `overflow-x-auto` wrapper.
    -->
    <div class="min-w-0">
        <!-- Toolbar -->
        <div v-if="$slots.toolbar" class="mb-4 flex flex-wrap items-center gap-3">
            <slot name="toolbar" :selected="selected ?? []" />
        </div>

        <div class="min-w-0 overflow-x-auto rounded-xl border border-border bg-card">
            <table class="w-full min-w-[42rem] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th v-if="selectable" class="w-10 px-4 py-3">
                            <Checkbox
                                :model-value="allSelected"
                                :indeterminate="someSelected"
                                aria-label="Select all rows"
                                @update:model-value="toggleAll"
                            />
                        </th>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="
                                cn(
                                    'px-4 py-3 text-xs font-bold uppercase tracking-wider text-muted-foreground',
                                    alignClass(column.align),
                                    column.hideOnMobile && 'hidden md:table-cell',
                                    column.headerClass,
                                )
                            "
                            :aria-sort="
                                sortDirection(column) === 'asc'
                                    ? 'ascending'
                                    : sortDirection(column) === 'desc'
                                      ? 'descending'
                                      : undefined
                            "
                        >
                            <button
                                v-if="column.sortable"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30"
                                @click="toggleSort(column)"
                            >
                                {{ column.label }}
                                <ArrowUp v-if="sortDirection(column) === 'asc'" class="size-3.5" />
                                <ArrowDown v-else-if="sortDirection(column) === 'desc'" class="size-3.5" />
                                <ChevronsUpDown v-else class="size-3.5 opacity-40" />
                            </button>
                            <span v-else>{{ column.label }}</span>
                        </th>
                        <th v-if="hasActions" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-muted-foreground">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Loading -->
                    <template v-if="loading">
                        <tr v-for="n in 5" :key="`skeleton-${n}`" class="border-b border-border last:border-0">
                            <td v-if="selectable" class="px-4 py-3"><Skeleton class="size-4" /></td>
                            <td v-for="column in columns" :key="column.key" class="px-4 py-3">
                                <Skeleton class="h-4 w-24" />
                            </td>
                            <td v-if="hasActions" class="px-4 py-3"><Skeleton class="ml-auto h-4 w-16" /></td>
                        </tr>
                    </template>

                    <!-- Empty -->
                    <tr v-else-if="rows.length === 0">
                        <td :colspan="columns.length + (selectable ? 1 : 0) + (hasActions ? 1 : 0)" class="px-4 py-12">
                            <slot name="empty">
                                <EmptyState
                                    :title="emptyTitle"
                                    :description="emptyDescription"
                                    class="border-0 bg-transparent py-4"
                                />
                            </slot>
                        </td>
                    </tr>

                    <!-- Rows -->
                    <tr
                        v-else
                        v-for="(row, index) in rows"
                        :key="keyOf(row, index)"
                        class="border-b border-border transition-colors last:border-0 hover:bg-muted/40"
                    >
                        <td v-if="selectable" class="px-4 py-3">
                            <Checkbox
                                :model-value="(selected ?? []).includes(keyOf(row, index))"
                                :aria-label="`Select row ${index + 1}`"
                                @update:model-value="(checked: boolean) => toggleRow(keyOf(row, index), checked)"
                            />
                        </td>
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="cn('px-4 py-3 align-middle', alignClass(column.align), column.hideOnMobile && 'hidden md:table-cell', column.class)"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="valueOf(row, column.key)" :index="index">
                                {{ valueOf(row, column.key) }}
                            </slot>
                        </td>
                        <td v-if="hasActions" class="px-4 py-3 text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <slot name="actions" :row="row" :index="index" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer (pagination) -->
        <div v-if="$slots.footer" class="mt-4">
            <slot name="footer" />
        </div>
    </div>
</template>
