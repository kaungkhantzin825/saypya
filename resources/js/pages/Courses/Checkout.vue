<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    BookOpen,
    ChartColumn,
    ChevronLeft,
    Clock,
    Lock,
    ShieldCheck,
    User,
    type LucideIcon,
} from 'lucide-vue-next';
import { Alert, Badge, Button, Card, Checkbox, Label, Select, Separator } from '@/components/ui';
import PriceTag from '@/components/site/PriceTag.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useFormatting } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import { formatDuration } from '@/lib/utils';
import type { Course } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{ course: Course }>();

const { formatPrice } = useFormatting();

const levelLabels: Record<string, string> = {
    beginner: 'Beginner',
    intermediate: 'Intermediate',
    advanced: 'Advanced',
    all_levels: 'All levels',
};

const levelLabel = computed(() => levelLabels[props.course.level] ?? props.course.level);

const durationLabel = computed(() => {
    const hours = Number(props.course.duration_hours ?? 0);
    if (hours > 0) return `${hours} hours`;
    const minutes = Number(props.course.total_duration ?? 0);
    return minutes > 0 ? formatDuration(minutes) : null;
});

const subtotal = computed(() => Number(props.course.price ?? 0));
const total = computed(() => Number(props.course.current_price ?? props.course.price ?? 0));
const discount = computed(() => Math.max(0, subtotal.value - total.value));

const summaryItems = computed(() =>
    [
        { icon: User, label: props.course.instructor?.name ?? 'Sanpya Academy' },
        { icon: ChartColumn, label: levelLabel.value },
        durationLabel.value ? { icon: Clock, label: durationLabel.value } : null,
        props.course.total_lessons
            ? {
                  icon: BookOpen,
                  label: `${props.course.total_lessons} ${props.course.total_lessons === 1 ? 'lesson' : 'lessons'}`,
              }
            : null,
    ].filter((entry): entry is { icon: LucideIcon; label: string } => entry !== null),
);

const paymentMethods = [
    { value: 'kbz_pay', label: 'KBZ Pay' },
    { value: 'wave_money', label: 'Wave Money' },
    { value: 'ayar', label: 'AYA Pay' },
    { value: 'bank_transfer', label: 'Bank transfer' },
    { value: 'cash', label: 'Cash at office' },
];

const form = useForm({
    payment_method: '',
    terms: false,
});

const canSubmit = computed(() => form.payment_method !== '' && form.terms && !form.processing);

function submit() {
    form.post(routes.enroll(props.course.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="page-container py-10 sm:py-14">
        <Link
            :href="routes.course(course.slug)"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
        >
            <ChevronLeft class="size-4" />
            Back to course
        </Link>

        <div class="mt-6">
            <Badge variant="brand" class="mb-3">
                <Lock class="size-3" />
                Secure checkout
            </Badge>
            <h1 class="text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">Complete your enrolment</h1>
            <p class="mt-3 max-w-2xl text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base">
                Review your order and choose how you'd like to pay. You'll get full access to the course once
                your payment is confirmed.
            </p>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
            <!-- ================================================== Summary -->
            <section class="grid content-start gap-6">
                <Card :padded="false" class="overflow-hidden">
                    <div class="flex flex-col gap-5 p-5 sm:flex-row">
                        <img
                            :src="course.thumbnail_url"
                            :alt="course.title"
                            class="aspect-video w-full rounded-lg object-cover sm:w-56"
                        />
                        <div class="min-w-0 flex-1">
                            <Badge v-if="course.category" variant="brand">{{ course.category.name }}</Badge>
                            <h2 class="mt-2 text-lg font-bold leading-snug">
                                <Link
                                    :href="routes.course(course.slug)"
                                    class="transition-colors hover:text-brand-700 dark:hover:text-brand-400"
                                >
                                    {{ course.title }}
                                </Link>
                            </h2>
                            <p
                                v-if="course.short_description"
                                class="mt-2 line-clamp-2 text-sm text-muted-foreground"
                            >
                                {{ course.short_description }}
                            </p>

                            <ul class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-muted-foreground">
                                <li v-for="item in summaryItems" :key="item.label" class="inline-flex items-center gap-1.5">
                                    <component :is="item.icon" class="size-3.5" />
                                    {{ item.label }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </Card>

                <!-- Totals -->
                <Card>
                    <template #header>
                        <h2 class="text-base font-semibold leading-none tracking-tight">Order summary</h2>
                    </template>

                    <dl class="grid gap-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Subtotal</dt>
                            <dd class="font-medium">{{ formatPrice(subtotal) }}</dd>
                        </div>

                        <div v-if="discount > 0" class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Discount</dt>
                            <dd class="font-medium text-success">−{{ formatPrice(discount) }}</dd>
                        </div>

                        <Separator />

                        <div class="flex items-center justify-between">
                            <dt class="font-semibold">Total due</dt>
                            <dd>
                                <PriceTag :price="course.price" :discount-price="course.discount_price" size="lg" />
                            </dd>
                        </div>
                    </dl>
                </Card>
            </section>

            <!-- ================================================== Payment -->
            <section class="grid content-start gap-6 lg:sticky lg:top-24 lg:self-start">
                <Card>
                    <template #header>
                        <h2 class="text-base font-semibold leading-none tracking-tight">Payment method</h2>
                        <p class="text-sm text-muted-foreground">Choose how you'd like to pay.</p>
                    </template>

                    <form class="grid gap-5" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="payment-method" required>Payment method</Label>
                            <Select
                                id="payment-method"
                                v-model="form.payment_method"
                                :options="paymentMethods"
                                placeholder="Select a payment method"
                            />
                            <p
                                v-if="form.errors.payment_method"
                                class="text-xs font-medium text-destructive"
                            >
                                {{ form.errors.payment_method }}
                            </p>
                        </div>

                        <Alert variant="info" title="Manual payment confirmation">
                            Payments are confirmed manually by an admin. After submitting, send your transfer
                            receipt and we'll unlock the course as soon as it's verified.
                        </Alert>

                        <div class="grid gap-2">
                            <div class="flex items-start gap-3">
                                <Checkbox id="terms" v-model="form.terms" class="mt-0.5" />
                                <Label for="terms" class="cursor-pointer font-normal leading-relaxed text-muted-foreground">
                                    I agree to the
                                    <Link
                                        :href="routes.terms()"
                                        class="font-medium text-brand-700 underline-offset-4 hover:underline dark:text-brand-400"
                                    >
                                        terms of service
                                    </Link>
                                    and
                                    <Link
                                        :href="routes.privacy()"
                                        class="font-medium text-brand-700 underline-offset-4 hover:underline dark:text-brand-400"
                                    >
                                        privacy policy
                                    </Link>.
                                </Label>
                            </div>
                            <p v-if="form.errors.terms" class="text-xs font-medium text-destructive">
                                {{ form.errors.terms }}
                            </p>
                        </div>

                        <Button
                            type="submit"
                            variant="brand"
                            size="lg"
                            block
                            :loading="form.processing"
                            :disabled="!canSubmit"
                        >
                            <ShieldCheck />
                            Confirm enrolment
                        </Button>
                    </form>
                </Card>

                <p class="flex items-center justify-center gap-2 text-xs text-muted-foreground">
                    <Lock class="size-3.5" />
                    Your details are only used to process this enrolment.
                </p>
            </section>
        </div>
    </div>
</template>
