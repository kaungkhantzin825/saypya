<script setup lang="ts">
import { watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Clock, Mail, MapPin, MessageSquare, Phone, Send, ShieldCheck, Sparkles } from 'lucide-vue-next';
import { Alert, Button, Card, Input, Label, Textarea } from '@/components/ui';
import PageHero from '@/components/site/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    captcha: { expected: number; question: string };
}>();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
    captcha_answer: '',
    captcha_expected: String(props.captcha?.expected ?? ''),
    website: '',
});

// The controller issues a fresh challenge on every successful submission.
watch(
    () => props.captcha?.expected,
    (expected) => {
        form.captcha_expected = String(expected ?? '');
    },
);

function submit() {
    form.post(routes.contactSubmit(), {
        preserveScroll: true,
        onSuccess: () =>
            form.reset('name', 'email', 'phone', 'subject', 'message', 'captcha_answer'),
    });
}

const contactChannels = [
    {
        icon: Mail,
        label: 'Email',
        value: 'webdeveloperkkz@gmail.com',
        href: 'mailto:webdeveloperkkz@gmail.com',
    },
    {
        icon: Phone,
        label: 'Viber',
        value: '+95 9695238273',
        href: 'viber://chat?number=%2B959695238273',
    },
    {
        icon: MapPin,
        label: 'Location',
        value: 'Yangon, Myanmar',
        href: null,
    },
];

const responseTime = [
    { icon: Clock, label: 'Support hours', value: 'Mon–Sat, 9:00–18:00 (MMT)' },
    { icon: MessageSquare, label: 'Typical reply', value: 'Within 1 business day' },
    { icon: ShieldCheck, label: 'Your data', value: 'Never shared or sold' },
];
</script>

<template>
    <PageHero
        eyebrow="Contact"
        :icon="Sparkles"
        title="We would love to hear from you"
        subtitle="Questions about a course, payments or partnerships? Send us a message and our team will get back to you."
        align="center"
        image="/images/page-headers/contact.webp"
    />

    <!-- ========================================================== Body -->
    <section class="page-container py-16 sm:py-20">
        <div class="grid gap-8 lg:grid-cols-[1fr_1.35fr]">
            <!-- Contact details -->
            <div class="grid content-start gap-6">
                <div class="grid gap-4">
                    <component
                        :is="channel.href ? 'a' : 'div'"
                        v-for="channel in contactChannels"
                        :key="channel.label"
                        :href="channel.href ?? undefined"
                        :class="[
                            'flex items-start gap-4 rounded-xl border border-border bg-card p-5 shadow-soft transition-all',
                            channel.href
                                ? 'hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800'
                                : '',
                        ]"
                    >
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                        >
                            <component :is="channel.icon" class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-muted-foreground">
                                {{ channel.label }}
                            </p>
                            <p class="mt-1 break-words font-semibold">{{ channel.value }}</p>
                        </div>
                    </component>
                </div>

                <Card>
                    <h2 class="text-base font-bold">What to expect</h2>
                    <ul class="mt-4 grid gap-4">
                        <li v-for="item in responseTime" :key="item.label" class="flex items-start gap-3">
                            <component :is="item.icon" class="mt-0.5 size-4 shrink-0 text-brand-600 dark:text-brand-400" />
                            <div>
                                <p class="text-sm font-medium">{{ item.label }}</p>
                                <p class="text-sm text-muted-foreground">{{ item.value }}</p>
                            </div>
                        </li>
                    </ul>
                </Card>
            </div>

            <!-- Form -->
            <Card>
                <h2 class="text-lg font-bold">Send a message</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Fill in the form below and we will reply as soon as we can.
                </p>

                <form class="relative mt-6 grid gap-5" novalidate @submit.prevent="submit">
                    <!-- Honeypot: off-screen, non-focusable, ignored by real users -->
                    <div class="absolute left-[-9999px] top-0 h-px w-px overflow-hidden" aria-hidden="true">
                        <Label for="website">Website</Label>
                        <Input
                            id="website"
                            v-model="form.website"
                            type="text"
                            tabindex="-1"
                            autocomplete="off"
                            aria-hidden="true"
                        />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name" required>Full name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                autocomplete="name"
                                placeholder="Your name"
                                :invalid="!!form.errors.name"
                                :aria-invalid="!!form.errors.name"
                                :aria-describedby="form.errors.name ? 'name-error' : undefined"
                            />
                            <p v-if="form.errors.name" id="name-error" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" required>Email address</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="you@example.com"
                                :invalid="!!form.errors.email"
                                :aria-invalid="!!form.errors.email"
                                :aria-describedby="form.errors.email ? 'email-error' : undefined"
                            />
                            <p v-if="form.errors.email" id="email-error" class="text-sm text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="phone">Phone (optional)</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                autocomplete="tel"
                                placeholder="+95 ..."
                                :invalid="!!form.errors.phone"
                                :aria-invalid="!!form.errors.phone"
                                :aria-describedby="form.errors.phone ? 'phone-error' : undefined"
                            />
                            <p v-if="form.errors.phone" id="phone-error" class="text-sm text-destructive">
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="subject" required>Subject</Label>
                            <Input
                                id="subject"
                                v-model="form.subject"
                                placeholder="How can we help?"
                                :invalid="!!form.errors.subject"
                                :aria-invalid="!!form.errors.subject"
                                :aria-describedby="form.errors.subject ? 'subject-error' : undefined"
                            />
                            <p v-if="form.errors.subject" id="subject-error" class="text-sm text-destructive">
                                {{ form.errors.subject }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="message" required>Message</Label>
                        <Textarea
                            id="message"
                            v-model="form.message"
                            :rows="6"
                            placeholder="Tell us a little about what you need…"
                            :invalid="!!form.errors.message"
                            :aria-invalid="!!form.errors.message"
                            :aria-describedby="form.errors.message ? 'message-error' : undefined"
                        />
                        <p v-if="form.errors.message" id="message-error" class="text-sm text-destructive">
                            {{ form.errors.message }}
                        </p>
                    </div>

                    <!-- Captcha -->
                    <div class="grid gap-2">
                        <Label for="captcha_answer" required>{{ props.captcha?.question ?? 'Verification' }}</Label>
                        <Input
                            id="captcha_answer"
                            v-model="form.captcha_answer"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="Your answer"
                            :invalid="!!form.errors.captcha_answer"
                            :aria-invalid="!!form.errors.captcha_answer"
                            :aria-describedby="form.errors.captcha_answer ? 'captcha-error' : undefined"
                        />
                        <p v-if="form.errors.captcha_answer" id="captcha-error" class="text-sm text-destructive">
                            {{ form.errors.captcha_answer }}
                        </p>
                        <input
                            v-model="form.captcha_expected"
                            type="hidden"
                            name="captcha_expected"
                        />
                    </div>

                    <Alert v-if="form.hasErrors" variant="destructive" title="Please check the form">
                        Some details need your attention. Review the highlighted fields and try again.
                    </Alert>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button type="submit" variant="brand" size="lg" :loading="form.processing">
                            <Send />
                            Send message
                        </Button>
                        <p class="text-xs text-muted-foreground">
                            By sending this form you agree to our
                            <Link :href="routes.privacy()" class="font-medium text-brand-600 hover:underline">
                                privacy policy
                            </Link>.
                        </p>
                    </div>
                </form>
            </Card>
        </div>
    </section>
</template>
