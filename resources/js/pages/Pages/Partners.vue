<script setup lang="ts">
import { computed } from 'vue';
import { ArrowRight, ExternalLink, Handshake, Sparkles, Users } from 'lucide-vue-next';
import { Badge, Button, Card, EmptyState } from '@/components/ui';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: PublicLayout });

interface Partner {
    id: number;
    name: string;
    logo_url?: string | null;
    website?: string | null;
    description?: string | null;
}

const props = defineProps<{ partners: Partner[] }>();

const items = computed(() => props.partners ?? []);

const benefits = [
    {
        title: 'Reach motivated learners',
        body: 'Put your brand in front of thousands of students actively building new skills.',
    },
    {
        title: 'Co-create courses',
        body: 'Work with our instructors to turn your industry expertise into a structured curriculum.',
    },
    {
        title: 'Support local talent',
        body: 'Help close the skills gap in Myanmar by making quality training affordable.',
    },
];
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="relative overflow-hidden border-b border-border">
        <div class="hero-backdrop pointer-events-none absolute inset-0" />
        <div class="page-container relative py-16 sm:py-20">
            <div class="mx-auto max-w-3xl text-center">
                <Badge variant="brand" class="mb-5">
                    <Sparkles class="size-3" />
                    Partners
                </Badge>
                <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">
                    Organisations we build with
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                    We work with companies, universities and communities who share our goal of making
                    practical education accessible across Myanmar.
                </p>
            </div>
        </div>
    </section>

    <!-- ======================================================= Partners -->
    <section class="page-container py-16 sm:py-20">
        <EmptyState
            v-if="items.length === 0"
            :icon="Handshake"
            title="Partners coming soon"
            description="We are finalising new partnerships. Want to work with us? We would love to hear from you."
        >
            <Button :href="routes.contact()" variant="brand">Become a partner</Button>
        </EmptyState>

        <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="partner in items" :key="partner.id" class="flex h-full flex-col">
                <div
                    class="mb-5 flex h-20 items-center justify-center rounded-lg border border-border bg-muted/40 px-4"
                >
                    <img
                        v-if="partner.logo_url"
                        :src="partner.logo_url"
                        :alt="partner.name"
                        class="max-h-12 w-auto max-w-full object-contain"
                        loading="lazy"
                    />
                    <span v-else class="text-base font-bold tracking-tight text-muted-foreground">
                        {{ partner.name }}
                    </span>
                </div>

                <h2 class="text-base font-bold">{{ partner.name }}</h2>

                <p v-if="partner.description" class="mt-2 text-sm leading-relaxed text-muted-foreground">
                    {{ partner.description }}
                </p>

                <div v-if="partner.website" class="mt-auto pt-4">
                    <Button
                        :href="partner.website"
                        variant="outline"
                        size="sm"
                        external
                    >
                        Visit website
                        <ExternalLink />
                    </Button>
                </div>
            </Card>
        </div>
    </section>

    <!-- ============================================================= CTA -->
    <section class="page-container pb-20">
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 px-8 py-14 sm:px-14"
        >
            <div class="hero-backdrop pointer-events-none absolute inset-0 opacity-25" />
            <div class="relative grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <h2 class="text-balance text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        Become a partner
                    </h2>
                    <p class="mt-4 text-pretty text-sm leading-relaxed text-brand-100 sm:text-base">
                        Whether you want to sponsor scholarships, co-create a course or hire our graduates,
                        let us build something together.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <Button
                            :href="routes.contact()"
                            size="lg"
                            class="bg-white text-brand-800 hover:bg-brand-50"
                        >
                            Start a conversation
                            <ArrowRight />
                        </Button>
                    </div>
                </div>

                <ul class="grid gap-4">
                    <li
                        v-for="benefit in benefits"
                        :key="benefit.title"
                        class="rounded-xl border border-white/15 bg-white/5 p-5 backdrop-blur-sm"
                    >
                        <div class="flex items-start gap-3">
                            <span
                                class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white"
                            >
                                <Users class="size-4" />
                            </span>
                            <div>
                                <p class="font-semibold text-white">{{ benefit.title }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-brand-100">{{ benefit.body }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
