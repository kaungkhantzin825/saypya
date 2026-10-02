<script setup lang="ts">
import { computed } from 'vue';
import {
    Award,
    BookOpen,
    Clock,
    Eye,
    GraduationCap,
    Handshake,
    ShieldCheck,
    Sparkles,
    Target,
    Users,
} from 'lucide-vue-next';
import { Button, Card } from '@/components/ui';
import SectionHeading from '@/components/site/SectionHeading.vue';
import PageHero from '@/components/site/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    stats: {
        students: number;
        courses: number;
        instructors: number;
        partners: number;
    };
    siteName: string;
}>();

const statCards = computed(() => [
    { label: 'Students', value: props.stats?.students ?? 0, icon: Users },
    { label: 'Courses', value: props.stats?.courses ?? 0, icon: BookOpen },
    { label: 'Instructors', value: props.stats?.instructors ?? 0, icon: GraduationCap },
    { label: 'Partners', value: props.stats?.partners ?? 0, icon: Handshake },
]);

const pillars = [
    {
        icon: Target,
        title: 'Our mission',
        body: 'Make practical, job-ready education affordable and accessible to every learner in Myanmar — in the language they are most comfortable with.',
    },
    {
        icon: Eye,
        title: 'Our vision',
        body: 'A generation of skilled professionals who can compete for opportunities anywhere, without having to leave home to find them.',
    },
];

const values = [
    {
        icon: BookOpen,
        title: 'Practical curriculum',
        body: 'Every course is built around real projects and outcomes, not theory you will never use.',
    },
    {
        icon: Users,
        title: 'Expert instructors',
        body: 'Learn from working professionals who teach what they practise every single day.',
    },
    {
        icon: Clock,
        title: 'Learn at your pace',
        body: 'Lifetime access to everything you buy, on any device, with progress tracking built in.',
    },
    {
        icon: Award,
        title: 'Recognised certificates',
        body: 'Pass the course exam and earn a certificate you can share with employers and partners.',
    },
    {
        icon: ShieldCheck,
        title: 'Secure payments',
        body: 'Local payment methods with clear pricing — no hidden fees and no surprise renewals.',
    },
    {
        icon: Sparkles,
        title: 'Bilingual by default',
        body: 'Study in English or Myanmar. The whole site and your materials follow your choice.',
    },
];
</script>

<template>
    <PageHero
        eyebrow="About us"
        :icon="Sparkles"
        title="Education that moves careers forward"
        align="center"
        image="/images/page-headers/about.webp"
    >
        <template #subtitle>
            {{ props.siteName }} is a Myanmar online academy built to help people learn the skills
            employers actually ask for — at a price that makes sense locally.
        </template>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <Button :href="routes.courses()" size="lg" variant="brand">Explore courses</Button>
            <Button :href="routes.contact()" size="lg" variant="outline">Talk to us</Button>
        </div>
    </PageHero>

    <!-- ================================================= Mission/Vision -->
    <section class="page-container py-16 sm:py-20">
        <div class="grid gap-6 lg:grid-cols-2">
            <Card v-for="pillar in pillars" :key="pillar.title" class="h-full">
                <span
                    class="mb-4 flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                >
                    <component :is="pillar.icon" class="size-5" />
                </span>
                <h2 class="text-lg font-bold">{{ pillar.title }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ pillar.body }}</p>
            </Card>
        </div>
    </section>

    <!-- =========================================================== Stats -->
    <section class="border-y border-border bg-muted/40">
        <div class="page-container grid grid-cols-2 gap-6 py-12 lg:grid-cols-4">
            <div v-for="stat in statCards" :key="stat.label" class="flex items-center gap-4">
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                >
                    <component :is="stat.icon" class="size-5" />
                </span>
                <div>
                    <p class="text-2xl font-extrabold leading-none tracking-tight">
                        {{ stat.value.toLocaleString('en-US') }}+
                    </p>
                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                        {{ stat.label }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================================================== Why choose -->
    <section class="page-container py-16 sm:py-20">
        <SectionHeading
            align="center"
            eyebrow="Why choose us"
            title="Everything you need to actually finish"
            subtitle="No fluff and no expiring access — just a clear path from your first lesson to a certificate."
        />

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="value in values" :key="value.title" class="h-full">
                <span
                    class="mb-4 flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                >
                    <component :is="value.icon" class="size-5" />
                </span>
                <h3 class="font-semibold">{{ value.title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ value.body }}</p>
            </Card>
        </div>
    </section>

    <!-- ============================================================= CTA -->
    <section class="page-container pb-20">
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 px-8 py-14 text-center sm:px-14"
        >
            <div class="hero-backdrop pointer-events-none absolute inset-0 opacity-25" />
            <div class="relative mx-auto max-w-2xl">
                <h2 class="text-balance text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    Ready to start learning?
                </h2>
                <p class="mt-4 text-pretty text-sm leading-relaxed text-brand-100 sm:text-base">
                    Create a free account and get instant access to your dashboard, wishlist and progress
                    tracking.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <Button :href="routes.register()" size="lg" class="bg-white text-brand-800 hover:bg-brand-50">
                        Create free account
                    </Button>
                    <Button
                        :href="routes.team()"
                        size="lg"
                        variant="outline"
                        class="border-white/40 bg-transparent text-white hover:bg-white/10 hover:text-white"
                    >
                        Meet the team
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>
