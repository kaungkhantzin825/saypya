<script setup lang="ts">
import { computed } from 'vue';
import { BadgeCheck, Sparkles, UserRoundPlus, Users } from 'lucide-vue-next';
import { Avatar, Badge, Button, Card, EmptyState } from '@/components/ui';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: PublicLayout });

interface TeamMember {
    id: number;
    name: string;
    role?: string | null;
    bio?: string | null;
    avatar_url: string;
}

const props = defineProps<{ team: TeamMember[] }>();

const members = computed(() => props.team ?? []);
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="relative overflow-hidden border-b border-border">
        <div class="hero-backdrop pointer-events-none absolute inset-0" />
        <div class="page-container relative py-16 sm:py-20">
            <div class="mx-auto max-w-3xl text-center">
                <Badge variant="brand" class="mb-5">
                    <Sparkles class="size-3" />
                    Our team
                </Badge>
                <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">
                    The people behind Sanpya
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                    A small, dedicated team of educators, engineers and creators building a better way to
                    learn in Myanmar.
                </p>
            </div>
        </div>
    </section>

    <!-- ========================================================== Team -->
    <section class="page-container py-16 sm:py-20">
        <EmptyState
            v-if="members.length === 0"
            :icon="Users"
            title="Team profiles coming soon"
            description="We are putting the finishing touches on our team page. In the meantime, get in touch — we would love to hear from you."
        >
            <Button :href="routes.contact()" variant="brand">Contact us</Button>
        </EmptyState>

        <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Card
                v-for="member in members"
                :key="member.id"
                class="flex h-full flex-col items-center text-center"
            >
                <Avatar :src="member.avatar_url" :name="member.name" size="2xl" class="mx-auto mb-5" />

                <h2 class="text-lg font-bold">{{ member.name }}</h2>

                <p
                    v-if="member.role"
                    class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 dark:text-brand-400"
                >
                    <BadgeCheck class="size-4" />
                    {{ member.role }}
                </p>

                <p v-if="member.bio" class="mt-3 text-sm leading-relaxed text-muted-foreground">
                    {{ member.bio }}
                </p>
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
                    Want to teach with us?
                </h2>
                <p class="mt-4 text-pretty text-sm leading-relaxed text-brand-100 sm:text-base">
                    Share your expertise with thousands of learners. Apply as an instructor and publish your
                    first course.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <Button
                        :href="routes.register()"
                        size="lg"
                        class="bg-white text-brand-800 hover:bg-brand-50"
                    >
                        <UserRoundPlus />
                        Become an instructor
                    </Button>
                    <Button
                        :href="routes.contact()"
                        size="lg"
                        variant="outline"
                        class="border-white/40 bg-transparent text-white hover:bg-white/10 hover:text-white"
                    >
                        Contact us
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>
