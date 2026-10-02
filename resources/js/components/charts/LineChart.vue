<script setup lang="ts">
import { computed, useId } from 'vue';

/**
 * Dependency-free line/area chart.
 *
 * The old Blade reports page pulled Chart.js from a CDN. This codebase avoids
 * extra runtime dependencies (see `lib/routes.ts`), and a CDN chart silently
 * fails to render offline — so the one chart the admin panel actually needs is
 * drawn as plain SVG instead. Colours come from the design tokens, so it tracks
 * the light/dark theme for free.
 */
const props = withDefaults(
    defineProps<{
        labels: string[];
        values: number[];
        /** Formats the hover value in full (e.g. money). Ticks stay compact. */
        formatter?: (value: number) => string;
        height?: number;
    }>(),
    { height: 240 },
);

/**
 * Unique per instance so two charts on one page cannot share a gradient id.
 * (`useId` rather than a module counter — `<script setup>` bodies run per
 * instance, so a module-level counter is not available here.)
 */
const gradientId = `line-chart-gradient-${useId()}`;

const WIDTH = 640;
const PAD = { top: 16, right: 16, bottom: 34, left: 88 };

const plot = computed(() => ({
    width: WIDTH - PAD.left - PAD.right,
    height: props.height - PAD.top - PAD.bottom,
}));

/** Compact tick labels — "1.2M", "250k", "80". */
function formatTick(value: number): string {
    const abs = Math.abs(value);
    if (abs >= 1_000_000) return `${(value / 1_000_000).toFixed(1)}M`;
    if (abs >= 1_000) return `${Math.round(value / 1000)}k`;
    return String(Math.round(value * 100) / 100);
}

function formatValue(value: number): string {
    return props.formatter ? props.formatter(value) : value.toLocaleString('en-US');
}

/** Axis top rounded up to a friendly step so gridlines land on round numbers. */
const axis = computed(() => {
    const max = Math.max(0, ...props.values);
    if (!Number.isFinite(max) || max <= 0) return { max: 1, ticks: [0, 0.5, 1] };

    const rawStep = max / 4;
    const magnitude = 10 ** Math.floor(Math.log10(rawStep));
    const normalized = rawStep / magnitude;
    const niceSteps = [1, 1.5, 2, 2.5, 3, 4, 5, 7.5, 10];
    const step = (niceSteps.find((candidate) => normalized <= candidate) ?? 10) * magnitude;

    return { max: step * 4, ticks: [0, step, step * 2, step * 3, step * 4] };
});

const xAt = (index: number) =>
    PAD.left + (props.values.length <= 1 ? plot.value.width / 2 : (index / (props.values.length - 1)) * plot.value.width);

const yAt = (value: number) => PAD.top + plot.value.height - (value / axis.value.max) * plot.value.height;

const points = computed(() =>
    props.values.map((value, index) => ({
        key: `${props.labels[index] ?? index}-${index}`,
        label: props.labels[index] ?? '',
        value,
        x: xAt(index),
        y: yAt(value),
    })),
);

const linePath = computed(() =>
    points.value.map((point, index) => `${index === 0 ? 'M' : 'L'}${point.x.toFixed(2)},${point.y.toFixed(2)}`).join(' '),
);

const areaPath = computed(() => {
    if (points.value.length === 0) return '';
    const first = points.value[0];
    const last = points.value[points.value.length - 1];
    const baseline = (PAD.top + plot.value.height).toFixed(2);
    return `${linePath.value} L${last.x.toFixed(2)},${baseline} L${first.x.toFixed(2)},${baseline} Z`;
});

/** Thin out x labels on a crowded axis so they do not overlap. */
const labelStride = computed(() => (props.labels.length <= 6 ? 1 : props.labels.length <= 12 ? 2 : 3));

/**
 * Pre-filtered so the template can use a plain `v-for`. (`v-if` alongside `v-for`
 * on the same element is evaluated before the loop scope exists in Vue 3.)
 */
const xLabels = computed(() =>
    props.labels
        .map((label, index) => ({ label, index }))
        .filter((entry) => entry.index % labelStride.value === 0),
);

const summary = computed(() =>
    points.value.map((point) => `${point.label}: ${formatValue(point.value)}`).join(', '),
);
</script>

<template>
    <svg
        :viewBox="`0 0 ${WIDTH} ${height}`"
        class="w-full"
        role="img"
        data-chart="line"
        :aria-label="summary"
        preserveAspectRatio="xMidYMid meet"
    >
        <defs>
            <linearGradient :id="gradientId" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#0d9488" stop-opacity="0.28" />
                <stop offset="100%" stop-color="#0d9488" stop-opacity="0" />
            </linearGradient>
        </defs>

        <!-- Gridlines + y ticks -->
        <g>
            <template v-for="tick in axis.ticks" :key="tick">
                <line
                    :x1="PAD.left"
                    :x2="WIDTH - PAD.right"
                    :y1="yAt(tick)"
                    :y2="yAt(tick)"
                    stroke="hsl(var(--border))"
                    stroke-width="1"
                />
                <text
                    :x="PAD.left - 12"
                    :y="yAt(tick) + 4"
                    text-anchor="end"
                    class="fill-muted-foreground text-[11px] tabular-nums"
                >
                    {{ formatTick(tick) }}
                </text>
            </template>
        </g>

        <!-- Series -->
        <path v-if="areaPath" :d="areaPath" :fill="`url(#${gradientId})`" />
        <path
            v-if="linePath"
            :d="linePath"
            fill="none"
            stroke="#0d9488"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

        <circle v-for="point in points" :key="point.key" :cx="point.x" :cy="point.y" r="3.5" fill="#0d9488">
            <title>{{ point.label }}: {{ formatValue(point.value) }}</title>
        </circle>

        <!-- X labels — thinned so a 12-month axis stays readable -->
        <text
            v-for="entry in xLabels"
            :key="`${entry.label}-${entry.index}`"
            :x="xAt(entry.index)"
            :y="height - 12"
            text-anchor="middle"
            class="fill-muted-foreground text-[11px]"
        >
            {{ entry.label }}
        </text>
    </svg>
</template>
