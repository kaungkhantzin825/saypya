<script setup lang="ts">
/**
 * Image with a branded fallback.
 *
 * Renders `/images/SanPya-Logo.png` whenever `src` is empty *or* the file fails
 * to load. A bare `<img :src>` shows the browser's broken-image icon in both
 * cases, which is what we are removing here.
 *
 * Drop-in replacement for a plain `<img>`: it is still a single element, so put
 * sizing / aspect / rounding in `class` exactly as before.
 *
 * The fallback is drawn on a white plate (matching `Logo.vue`) because the logo
 * is full-colour and unreadable on a dark surface.
 */
import { computed, ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const FALLBACK_SRC = '/images/SanPya-Logo.png';
/** Filename only, so an absolute URL from the API still matches. */
const FALLBACK_FILE = FALLBACK_SRC.split('/').pop() as string;

const props = withDefaults(
    defineProps<{
        src?: string | null;
        alt?: string | null;
        /** Override the logo shown when `src` is unusable. */
        fallback?: string;
        class?: string;
        loading?: 'lazy' | 'eager';
        decoding?: 'sync' | 'async' | 'auto';
    }>(),
    { fallback: FALLBACK_SRC, loading: 'lazy', decoding: 'async' },
);

/** Reset the error flag whenever the caller points at a new file. */
const failed = ref(false);
watch(
    () => props.src,
    () => {
        failed.value = false;
    },
);

const usableSrc = computed(() => (props.src ?? '').trim());
const usingFallback = computed(() => failed.value || usableSrc.value === '');
const resolvedSrc = computed(() => (usingFallback.value ? props.fallback : usableSrc.value));

/**
 * True whenever the brand mark is on screen rather than a real photo — either
 * because we substituted it, or because the caller passed it (the API sends an
 * absolute logo URL for courses that have no thumbnail).
 */
const showingLogo = computed(() => usingFallback.value || resolvedSrc.value.includes(FALLBACK_FILE));
</script>

<template>
    <img
        :src="resolvedSrc"
        :alt="showingLogo ? '' : (alt ?? '')"
        :aria-hidden="showingLogo ? 'true' : undefined"
        :loading="loading"
        :decoding="decoding"
        :class="
            cn(
                props.class,
                // Merged *after* `class` so `object-contain` beats an
                // `object-cover` the caller passed for the real photo.
                //
                // `max-h-72` caps the plate so a full-bleed 16:9 hero does not turn
                // the logo into a 600px-tall slab. It only binds on boxes taller
                // than 288px, so card thumbnails are unaffected.
                showingLogo && 'max-h-72 bg-white object-contain p-4 ring-1 ring-inset ring-black/5',
            )
        "
        @error="failed = true"
    />
</template>
