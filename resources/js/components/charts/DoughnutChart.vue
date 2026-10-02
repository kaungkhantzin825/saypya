<script setup lang="ts">
import { computed } from 'vue';

/**
 * Dependency-free doughnut chart, drawn as concentric `stroke-dasharray` arcs on
 * a single circle — no arc maths, no Chart.js, no CDN.
 */
const props = defineProps<{
    slices: { label: string; value: number; color: string }[];
}>();

const RADIUS = 42;
const STROKE = 14;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

const total = computed(() => props.slices.reduce((sum, slice) => sum + (Number(slice.value) || 0), 0));

const arcs = computed(() => {
    let consumed = 0;

    return props.slices.map((slice) => {
        const value = Number(slice.value) || 0;
        const fraction = total.value > 0 ? value / total.value : 0;
        const dash = fraction * CIRCUMFERENCE;
        const arc = {
            ...slice,
            value,
            fraction,
            dash,
            // Negative offset advances the dash start along the circle.
            offset: -consumed,
        };
        consumed += dash;
        return arc;
    });
});

const percent = (fraction: number) => `${Math.round(fraction * 100)}%`;

const summary = computed(() =>
    arcs.value.map((arc) => `${arc.label}: ${arc.value} (${percent(arc.fraction)})`).join(', '),
);
</script>

<template>
    <!--
        Stacked rather than doughnut-beside-legend: this chart lives in a 1/3-width
        card, where a side-by-side legend leaves only ~40px for the label while
        "Students" needs ~59px — every label ellipsised. Full width below the ring
        removes the constraint entirely.
    -->
    <div class="flex flex-col items-center gap-5">
        <svg viewBox="0 0 100 100" class="size-36 shrink-0 -rotate-90" role="img" data-chart="doughnut" :aria-label="summary">
            <circle cx="50" cy="50" :r="RADIUS" fill="none" stroke="hsl(var(--muted))" :stroke-width="STROKE" />
            <circle
                v-for="arc in arcs"
                :key="arc.label"
                cx="50"
                cy="50"
                :r="RADIUS"
                fill="none"
                :stroke="arc.color"
                :stroke-width="STROKE"
                :stroke-dasharray="`${arc.dash} ${CIRCUMFERENCE - arc.dash}`"
                :stroke-dashoffset="arc.offset"
            >
                <title>{{ arc.label }}: {{ arc.value }} ({{ percent(arc.fraction) }})</title>
            </circle>
        </svg>

        <ul class="grid w-full grid-cols-1 gap-2.5">
            <li
                v-for="arc in arcs"
                :key="arc.label"
                class="flex items-center gap-3 text-sm"
            >
                <span class="flex min-w-0 flex-1 items-center gap-2">
                    <span class="size-2.5 shrink-0 rounded-full" :style="{ backgroundColor: arc.color }" />
                    <span class="truncate text-muted-foreground">{{ arc.label }}</span>
                </span>
                <span class="shrink-0 font-semibold tabular-nums">{{ arc.value }}</span>
                <span class="w-10 shrink-0 text-right text-xs tabular-nums text-muted-foreground">
                    {{ percent(arc.fraction) }}
                </span>
            </li>
        </ul>
    </div>
</template>
