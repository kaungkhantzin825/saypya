<script setup lang="ts">
import type { Component } from 'vue';
import { Badge } from '@/components/ui';

/**
 * Page-header band with a photographic background.
 *
 * Replaces the five hand-rolled copies of this markup (courses, categories, blog,
 * about, contact) so the treatment stays consistent and the photo/scrim rules live
 * in one place.
 *
 * The scrim direction follows the copy: a left-to-right fade only works while the
 * text sits in a left column, so below `lg` (where the copy spans the full width)
 * it runs top-to-bottom instead. Centred copy gets a centred radial scrim, which
 * protects the middle and lets the photo show at the edges.
 */
withDefaults(
    defineProps<{
        /** Small pill above the title, e.g. "Course catalogue". */
        eyebrow: string;
        title: string;
        /** Plain-text subtitle. Use the `subtitle` slot for rich content. */
        subtitle?: string;
        /** Background photo URL. */
        image: string;
        align?: 'left' | 'center';
        /** `compact` is the shorter band used by the listing pages. */
        size?: 'compact' | 'large';
        /** Optional icon shown inside the eyebrow pill. */
        icon?: Component;
    }>(),
    {
        subtitle: '',
        align: 'left',
        size: 'large',
        icon: undefined,
    },
);
</script>

<template>
    <section class="relative isolate overflow-hidden border-b border-border">
        <img
            v-if="image"
            :src="image"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 -z-10 size-full object-cover"
        />

        <!--
            Scrim. Kept as light as legibility allows so the photo still reads as a
            photo rather than a white block, and built from theme tokens so it flips
            correctly in dark mode.
        -->
        <div
            class="absolute inset-0 -z-10"
            :class="
                align === 'center'
                    ? 'bg-[radial-gradient(ellipse_78%_135%_at_50%_45%,hsl(var(--background)/0.9)_0%,hsl(var(--background)/0.72)_48%,hsl(var(--background)/0.28)_100%)]'
                    : 'bg-[linear-gradient(180deg,hsl(var(--background)/0.92)_0%,hsl(var(--background)/0.8)_62%,hsl(var(--background)/0.45)_100%)] lg:bg-[linear-gradient(90deg,hsl(var(--background)/0.98)_0%,hsl(var(--background)/0.9)_34%,hsl(var(--background)/0.68)_54%,hsl(var(--background)/0.26)_70%,transparent_84%)]'
            "
            aria-hidden="true"
        />

        <div
            class="page-container relative"
            :class="size === 'compact' ? 'py-12 sm:py-16' : 'py-16 sm:py-24'"
        >
            <div :class="align === 'center' ? 'mx-auto max-w-3xl text-center' : ''">
                <Badge variant="brand" :class="size === 'compact' ? 'mb-4' : 'mb-5'">
                    <component :is="icon" v-if="icon" class="size-3" />
                    {{ eyebrow }}
                </Badge>

                <h1
                    class="text-balance font-extrabold leading-[1.1] tracking-tight"
                    :class="size === 'compact' ? 'text-3xl sm:text-4xl' : 'max-w-3xl text-4xl sm:text-5xl'"
                >
                    {{ title }}
                </h1>

                <p
                    class="text-pretty leading-relaxed text-muted-foreground"
                    :class="[
                        size === 'compact' ? 'mt-3 max-w-2xl text-sm sm:text-base' : 'mt-5 max-w-2xl text-base sm:text-lg',
                        align === 'center' ? 'mx-auto' : '',
                    ]"
                >
                    <slot name="subtitle">{{ subtitle }}</slot>
                </p>

                <!-- Extra content, e.g. the About page's call-to-action buttons. -->
                <slot />
            </div>
        </div>
    </section>
</template>
