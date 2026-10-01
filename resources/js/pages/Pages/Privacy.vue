<script setup lang="ts">
import { Mail, ShieldCheck } from 'lucide-vue-next';
import { Badge, Button } from '@/components/ui';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: PublicLayout });

interface PolicySection {
    id: string;
    title: string;
    paragraphs?: string[];
    list?: string[];
}

const lastUpdated = 'September 2026';

const sections: PolicySection[] = [
    {
        id: 'introduction',
        title: 'Introduction',
        paragraphs: [
            'Sanpya Online Academy (“Sanpya”, “we”, “us”) is committed to protecting the privacy of everyone who uses our website, mobile experience and learning platform. This policy explains what information we collect, why we collect it, and the choices you have.',
            'By creating an account or using our services you agree to the practices described in this policy. If you do not agree, please do not use the platform.',
        ],
    },
    {
        id: 'information-we-collect',
        title: 'Information we collect',
        paragraphs: ['We collect information in three ways: what you give us, what we record automatically, and what we receive from partners.'],
        list: [
            'Account details such as your name, email address, password (stored as a secure hash) and chosen role.',
            'Profile information you add, including a profile photo, phone number and biography.',
            'Enrolment and learning data, such as the courses you take, lessons you complete and exam results.',
            'Payment confirmation details from our payment partners. We never store full card numbers on our servers.',
            'Technical data such as your IP address, browser type, device and pages visited, used to keep the service secure and reliable.',
        ],
    },
    {
        id: 'how-we-use-information',
        title: 'How we use your information',
        paragraphs: ['We use your information only where we have a lawful reason to do so. In practice this means to:'],
        list: [
            'Create and secure your account, and verify your email address.',
            'Deliver the courses, exams and certificates you have enrolled in.',
            'Process payments and provide receipts for your purchases.',
            'Send service messages such as password resets and important account notices.',
            'Send occasional product updates and offers, which you can unsubscribe from at any time.',
            'Understand how the platform is used so we can improve lessons, features and support.',
        ],
    },
    {
        id: 'cookies',
        title: 'Cookies and similar technologies',
        paragraphs: [
            'We use cookies and similar storage to keep you signed in, remember your language preference and protect forms against abuse. Essential cookies are required for the platform to function; analytics cookies help us understand which content is useful.',
            'You can control cookies through your browser settings. Disabling essential cookies may prevent parts of the platform from working correctly.',
        ],
    },
    {
        id: 'sharing',
        title: 'When we share information',
        paragraphs: [
            'We do not sell your personal information. We share it only with the service providers that help us run Sanpya, and only as much as they need:',
        ],
        list: [
            'Payment providers, to confirm and process your purchases.',
            'Email delivery services, to send verification links, password resets and receipts.',
            'Hosting and analytics providers, under strict confidentiality agreements.',
            'Authorities, where we are legally required to disclose information.',
        ],
    },
    {
        id: 'security',
        title: 'How we protect your data',
        paragraphs: [
            'We use encryption in transit, hashed passwords, access controls and regular reviews to protect your information. No system is completely secure, so we also ask you to choose a strong, unique password and keep it private.',
            'If we ever become aware of a breach that affects you, we will notify you and the relevant authorities without undue delay.',
        ],
    },
    {
        id: 'your-rights',
        title: 'Your rights and choices',
        paragraphs: ['Depending on where you live, you may have the right to:'],
        list: [
            'Access a copy of the personal information we hold about you.',
            'Correct information that is inaccurate or incomplete.',
            'Request deletion of your account and personal data.',
            'Object to or restrict certain types of processing, including marketing.',
            'Withdraw consent at any time, without affecting the lawfulness of past processing.',
        ],
    },
    {
        id: 'children',
        title: 'Children’s privacy',
        paragraphs: [
            'Sanpya is not intended for children under 13, and we do not knowingly collect their personal information. If you believe a child has provided us with data, please contact us and we will remove it.',
        ],
    },
    {
        id: 'changes',
        title: 'Changes to this policy',
        paragraphs: [
            'We may update this policy from time to time to reflect changes in our practices or the law. When we do, we will revise the date at the top of this page and, for significant changes, notify you by email or in the platform.',
        ],
    },
    {
        id: 'contact',
        title: 'Contact us',
        paragraphs: [
            'If you have any questions about this policy or how we handle your information, please reach out. We aim to respond to privacy requests within 30 days.',
        ],
    },
];
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="relative overflow-hidden border-b border-border">
        <div class="hero-backdrop pointer-events-none absolute inset-0" />
        <div class="page-container relative py-14 sm:py-20">
            <div class="max-w-3xl">
                <Badge variant="brand" class="mb-5">
                    <ShieldCheck class="size-3" />
                    Legal
                </Badge>
                <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">
                    Privacy policy
                </h1>
                <p class="mt-5 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                    How Sanpya Online Academy collects, uses and protects your personal information.
                </p>
                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    Last updated: {{ lastUpdated }}
                </p>
            </div>
        </div>
    </section>

    <!-- ========================================================== Body -->
    <section class="page-container py-16 sm:py-20">
        <div class="grid gap-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <!-- Table of contents -->
            <nav class="hidden lg:block" aria-label="On this page">
                <div class="sticky top-24">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-muted-foreground">
                        On this page
                    </p>
                    <ul class="mt-4 grid gap-1 border-l border-border">
                        <li v-for="section in sections" :key="section.id">
                            <a
                                :href="`#${section.id}`"
                                class="-ml-px block border-l-2 border-transparent py-1.5 pl-4 text-sm text-muted-foreground transition-colors hover:border-brand-500 hover:text-foreground"
                            >
                                {{ section.title }}
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Content -->
            <div class="grid max-w-3xl gap-10">
                <section
                    v-for="section in sections"
                    :id="section.id"
                    :key="section.id"
                    class="scroll-mt-24"
                >
                    <h2 class="text-xl font-extrabold tracking-tight sm:text-2xl">{{ section.title }}</h2>

                    <p
                        v-for="(paragraph, index) in section.paragraphs"
                        :key="index"
                        class="mt-4 text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base"
                    >
                        {{ paragraph }}
                    </p>

                    <ul v-if="section.list" class="mt-4 grid gap-2.5">
                        <li
                            v-for="(entry, index) in section.list"
                            :key="index"
                            class="flex items-start gap-3 text-sm leading-relaxed text-muted-foreground sm:text-base"
                        >
                            <span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500" />
                            <span>{{ entry }}</span>
                        </li>
                    </ul>
                </section>

                <div class="rounded-xl border border-border bg-muted/40 p-6">
                    <h2 class="flex items-center gap-2 text-base font-bold">
                        <Mail class="size-4 text-brand-600 dark:text-brand-400" />
                        Questions about your data?
                    </h2>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                        Email us at
                        <a href="mailto:webdeveloperkkz@gmail.com" class="font-medium text-brand-600 hover:underline">
                            webdeveloperkkz@gmail.com
                        </a>
                        or use our contact form.
                    </p>
                    <Button :href="routes.contact()" variant="brand" size="sm" class="mt-4">
                        Contact us
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>
