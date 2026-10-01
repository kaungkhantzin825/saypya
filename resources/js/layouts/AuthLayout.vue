<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Toaster } from '@/components/ui';
import Logo from '@/components/site/Logo.vue';
import { useShared } from '@/composables/useApp';
import { routes } from '@/lib/routes';

defineProps<{
    title?: string;
    subtitle?: string;
}>();

const { app } = useShared();
</script>

<template>
    <div class="grid min-h-screen lg:grid-cols-2">
        <Toaster />

        <!-- Brand panel -->
        <div class="relative hidden overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="hero-backdrop pointer-events-none absolute inset-0 opacity-30" />

            <Link :href="routes.home()" class="relative" aria-label="Home">
                <Logo variant="light" />
            </Link>

            <div class="relative max-w-md">
                <h2 class="text-3xl font-extrabold leading-tight text-white">
                    Learn skills that move your career forward.
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-brand-100">
                    Expert-led courses in English and Myanmar, with lifetime access, progress tracking and
                    certificates on completion.
                </p>

                <ul class="mt-8 grid gap-3 text-sm text-brand-50">
                    <li class="flex items-center gap-3">
                        <span class="flex size-6 items-center justify-center rounded-full bg-white/15">✓</span>
                        Self-paced video lessons
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-6 items-center justify-center rounded-full bg-white/15">✓</span>
                        Graded exams and certificates
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-6 items-center justify-center rounded-full bg-white/15">✓</span>
                        Learn in English or Myanmar
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-brand-200">© {{ new Date().getFullYear() }} {{ app.name }}</p>
        </div>

        <!-- Form panel -->
        <div class="flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16">
            <div class="mx-auto w-full max-w-sm">
                <Link :href="routes.home()" class="mb-8 inline-flex lg:hidden" aria-label="Home">
                    <Logo />
                </Link>

                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold tracking-tight">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-2 text-sm text-muted-foreground">{{ subtitle }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
