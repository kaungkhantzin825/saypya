<script setup lang="ts">
import { computed, ref } from 'vue';
import {
    CircleQuestionMark,
    CreditCard,
    GraduationCap,
    LifeBuoy,
    Mail,
    Rocket,
    Sparkles,
    UserCog,
} from 'lucide-vue-next';
import { Accordion, Badge, Button, Card, Tabs } from '@/components/ui';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { routes } from '@/lib/routes';
import type { AccordionEntry, TabItem } from '@/types/ui';

defineOptions({ layout: PublicLayout });

interface Faq {
    value: string;
    question: string;
    answer: string;
}

const gettingStarted: Faq[] = [
    {
        value: 'gs-account',
        question: 'How do I create an account?',
        answer: 'Click “Get started” in the header, enter your name, email and a password, then choose whether you want to learn or teach. We will email you a verification link to confirm your address.',
    },
    {
        value: 'gs-verify',
        question: 'Why do I need to verify my email?',
        answer: 'Verification keeps your account secure and makes sure password resets and course notifications reach the right inbox. Open the link in the email we send you — if it is not in your inbox, check your spam or promotions folder.',
    },
    {
        value: 'gs-enrol',
        question: 'How do I enrol in a course?',
        answer: 'Open any course page and click “Enrol now”. Free courses unlock immediately; paid courses take you to checkout, and access is granted as soon as payment is confirmed.',
    },
    {
        value: 'gs-devices',
        question: 'Can I learn on my phone?',
        answer: 'Yes. Sanpya works in any modern mobile browser and remembers your progress, so you can start on a laptop and continue on your phone later.',
    },
];

const payments: Faq[] = [
    {
        value: 'pay-methods',
        question: 'Which payment methods do you accept?',
        answer: 'We support the local payment methods available at checkout, including mobile wallet transfers. The exact options are shown on the checkout page for each course.',
    },
    {
        value: 'pay-access',
        question: 'When do I get access after paying?',
        answer: 'Access is unlocked automatically once your payment is confirmed. If your payment is still showing as pending after a few minutes, contact us with the reference number.',
    },
    {
        value: 'pay-invoice',
        question: 'Can I get a receipt or invoice?',
        answer: 'Yes. Your purchase confirmation email acts as a receipt. If you need a formal invoice for a company or school, send us your details and we will prepare one.',
    },
    {
        value: 'pay-refund',
        question: 'What is your refund policy?',
        answer: 'If a course is not what you expected, contact us within seven days of purchase and before completing a significant portion of the lessons, and we will review your request.',
    },
];

const coursesExams: Faq[] = [
    {
        value: 'course-access',
        question: 'How long do I keep access to a course?',
        answer: 'Once you enrol, the course stays in your library with lifetime access — including future updates to the lessons and materials.',
    },
    {
        value: 'course-exam',
        question: 'How do exams and certificates work?',
        answer: 'Many courses end with a graded exam. Pass the exam and a certificate is added to your account, ready to download and share with employers.',
    },
    {
        value: 'course-retake',
        question: 'Can I retake an exam if I fail?',
        answer: 'Yes, as long as the course allows multiple attempts. Your best passing score is the one that counts towards your certificate.',
    },
    {
        value: 'course-notes',
        question: 'Can I download the lessons?',
        answer: 'Course materials are streamed inside the platform to protect our instructors’ work. Notes and resources that are marked as downloadable are available on the lesson page.',
    },
];

const account: Faq[] = [
    {
        value: 'acc-reset',
        question: 'I forgot my password — what now?',
        answer: 'Use the “Forgot password” link on the sign-in page. We will email you a secure link to set a new password; the link expires after a short time for your safety.',
    },
    {
        value: 'acc-profile',
        question: 'How do I update my profile?',
        answer: 'Open your account menu and choose “Profile”. From there you can change your name, photo and contact details at any time.',
    },
    {
        value: 'acc-role',
        question: 'Can I switch between learning and teaching?',
        answer: 'Your account can hold either role. If you would like to start teaching as well, contact us and we will help you set up an instructor profile.',
    },
    {
        value: 'acc-delete',
        question: 'How do I close my account?',
        answer: 'Send us a request from the email address on your account and we will delete your personal data in line with our privacy policy.',
    },
];

const categories = [
    { value: 'getting-started', label: 'Getting started', icon: Rocket, faqs: gettingStarted },
    { value: 'payments', label: 'Payments', icon: CreditCard, faqs: payments },
    { value: 'courses-exams', label: 'Courses & exams', icon: GraduationCap, faqs: coursesExams },
    { value: 'account', label: 'Account', icon: UserCog, faqs: account },
];

const tabs: TabItem[] = categories.map((category) => ({
    value: category.value,
    label: category.label,
    icon: category.icon,
    badge: category.faqs.length,
}));

const answers: Record<string, string> = Object.fromEntries(
    categories.flatMap((category) => category.faqs.map((faq) => [faq.value, faq.answer])),
);

const toEntries = (faqs: Faq[]): AccordionEntry[] =>
    faqs.map((faq) => ({ value: faq.value, title: faq.question }));

const activeTab = ref(categories[0]?.value ?? 'getting-started');

const totalQuestions = computed(() => categories.reduce((sum, category) => sum + category.faqs.length, 0));
</script>

<template>
    <!-- ============================================================ Hero -->
    <section class="relative overflow-hidden border-b border-border">
        <div class="hero-backdrop pointer-events-none absolute inset-0" />
        <div class="page-container relative py-14 sm:py-20">
            <div class="mx-auto max-w-3xl text-center">
                <Badge variant="brand" class="mb-5">
                    <Sparkles class="size-3" />
                    Help centre
                </Badge>
                <h1 class="text-balance text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">
                    How can we help?
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                    Answers to the questions we hear most. Browse by topic, or reach out if you cannot find
                    what you need.
                </p>
                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    {{ totalQuestions }} questions answered
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================= FAQ -->
    <section class="page-container py-16 sm:py-20">
        <div class="mx-auto max-w-4xl">
            <Tabs v-model="activeTab" :tabs="tabs" variant="underline">
                <template #getting-started>
                    <Accordion type="multiple" :items="toEntries(gettingStarted)">
                        <template #content="{ item }">
                            <p class="text-sm leading-relaxed text-muted-foreground">{{ answers[item.value] }}</p>
                        </template>
                    </Accordion>
                </template>

                <template #payments>
                    <Accordion type="multiple" :items="toEntries(payments)">
                        <template #content="{ item }">
                            <p class="text-sm leading-relaxed text-muted-foreground">{{ answers[item.value] }}</p>
                        </template>
                    </Accordion>
                </template>

                <template #courses-exams>
                    <Accordion type="multiple" :items="toEntries(coursesExams)">
                        <template #content="{ item }">
                            <p class="text-sm leading-relaxed text-muted-foreground">{{ answers[item.value] }}</p>
                        </template>
                    </Accordion>
                </template>

                <template #account>
                    <Accordion type="multiple" :items="toEntries(account)">
                        <template #content="{ item }">
                            <p class="text-sm leading-relaxed text-muted-foreground">{{ answers[item.value] }}</p>
                        </template>
                    </Accordion>
                </template>
            </Tabs>
        </div>
    </section>

    <!-- ============================================================= CTA -->
    <section class="page-container pb-20">
        <div class="mx-auto max-w-4xl">
            <Card class="flex flex-col items-center gap-5 text-center sm:p-10">
                <span
                    class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
                >
                    <LifeBuoy class="size-6" />
                </span>
                <div>
                    <h2 class="text-xl font-bold">Still need help?</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-muted-foreground">
                        Our support team is happy to answer anything that is not covered here. Send us a
                        message and we will get back to you within one business day.
                    </p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <Button :href="routes.contact()" variant="brand" size="lg">
                        <Mail />
                        Contact support
                    </Button>
                    <Button :href="routes.courses()" variant="outline" size="lg">
                        <CircleQuestionMark />
                        Browse courses
                    </Button>
                </div>
            </Card>
        </div>
    </section>
</template>
