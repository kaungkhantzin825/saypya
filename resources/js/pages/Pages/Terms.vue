<script setup lang="ts">
import { FileText, Mail } from 'lucide-vue-next';
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
        id: 'acceptance',
        title: 'Acceptance of these terms',
        paragraphs: [
            'These Terms of Service govern your use of Sanpya Online Academy (“Sanpya”, “we”, “us”) and any course, exam or service offered through the platform. By creating an account or using the platform you agree to be bound by these terms.',
            'If you are using Sanpya on behalf of an organisation, you confirm that you have authority to accept these terms on its behalf.',
        ],
    },
    {
        id: 'accounts',
        title: 'Your account',
        paragraphs: ['You are responsible for everything that happens under your account. To keep it safe:'],
        list: [
            'Provide accurate, current information when you register and keep it up to date.',
            'Choose a strong password and keep it confidential.',
            'Verify your email address so we can reach you about your account.',
            'Tell us immediately if you suspect unauthorised access.',
            'Do not share your login with anyone else or create accounts for others.',
        ],
    },
    {
        id: 'courses-and-access',
        title: 'Courses and access',
        paragraphs: [
            'When you enrol in a course you receive a personal, non-transferable licence to access its lessons and materials for as long as the course remains available on the platform. Access is for your own learning and may not be shared, resold or used to deliver training to others without our written permission.',
            'We may update, reorganise or retire lessons over time to keep content accurate. We will make reasonable efforts to preserve access to courses you have purchased.',
        ],
    },
    {
        id: 'payments-and-refunds',
        title: 'Payments and refunds',
        paragraphs: [
            'Prices are shown in Myanmar Kyat at checkout and may change for future purchases. Payment is processed by our payment partners, and access is granted once payment is confirmed.',
            'If a course is not what you expected, contact us within seven days of purchase and before completing a significant portion of the lessons. We will review refund requests fairly, but refunds are not guaranteed where substantial content has already been consumed.',
        ],
    },
    {
        id: 'acceptable-use',
        title: 'Acceptable use',
        paragraphs: ['To keep Sanpya a safe and useful place to learn, you agree not to:'],
        list: [
            'Copy, record, download or redistribute course content without permission.',
            'Share your account credentials or resell access to courses.',
            'Attempt to disrupt, overload or gain unauthorised access to the platform.',
            'Post unlawful, abusive, misleading or infringing content in discussions or reviews.',
            'Use automated tools to scrape content or harvest other users’ information.',
        ],
    },
    {
        id: 'intellectual-property',
        title: 'Intellectual property',
        paragraphs: [
            'All course content, branding, software and design on the platform are owned by Sanpya or its instructors and are protected by intellectual property laws. Your purchase grants you a licence to learn from the content — it does not transfer ownership.',
            'Content you create, such as discussion posts and reviews, remains yours, but you grant us a licence to display and moderate it on the platform.',
        ],
    },
    {
        id: 'certificates',
        title: 'Exams and certificates',
        paragraphs: [
            'Certificates are issued when you pass a course exam, where the course offers one. You must complete exams yourself; cheating, impersonation or misrepresentation may result in the certificate being revoked and your account being suspended.',
            'Certificates confirm completion of our course content and are not a substitute for any professional licence or accreditation.',
        ],
    },
    {
        id: 'disclaimers',
        title: 'Disclaimers',
        paragraphs: [
            'The platform and its content are provided “as is”. While we work hard to keep lessons accurate and up to date, we do not warrant that content is error-free or that completing a course will guarantee a particular job, grade or business result.',
        ],
    },
    {
        id: 'liability',
        title: 'Limitation of liability',
        paragraphs: [
            'To the maximum extent permitted by law, Sanpya is not liable for indirect, incidental or consequential losses arising from your use of the platform. Where liability cannot be excluded, our total liability is limited to the amount you paid us for the course giving rise to the claim.',
        ],
    },
    {
        id: 'changes',
        title: 'Changes to these terms',
        paragraphs: [
            'We may update these terms from time to time. When we do, we will revise the date at the top of this page and, for significant changes, notify you by email or in the platform. Continuing to use Sanpya after a change means you accept the updated terms.',
        ],
    },
    {
        id: 'contact',
        title: 'Contact us',
        paragraphs: [
            'If you have any questions about these terms, please get in touch and we will be happy to help.',
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
                    <FileText class="size-3" />
                    Legal
                </Badge>
                <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">
                    Terms of service
                </h1>
                <p class="mt-5 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                    The rules that apply when you create an account and learn with Sanpya Online Academy.
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
                        Need to clarify something?
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
