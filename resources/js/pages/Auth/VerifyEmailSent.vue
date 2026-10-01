<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, MailCheck, RefreshCw } from 'lucide-vue-next';
import { Button } from '@/components/ui';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: AuthLayout });

const props = defineProps<{
    email?: string | null;
    type: 'registration' | 'password_reset';
    title?: string;
    subtitle?: string;
}>();

const isRegistration = computed(() => props.type === 'registration');
const showOwnHeader = computed(() => !props.title);

const resendForm = useForm({ email: props.email ?? '' });

function resend() {
    resendForm.post(routes.resendVerification(), { preserveScroll: true });
}
</script>

<template>
    <div v-if="showOwnHeader" class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight">
            {{ isRegistration ? 'Verify your email' : 'Check your email' }}
        </h1>
        <p class="mt-2 text-sm text-muted-foreground">
            {{ isRegistration ? 'One quick step to activate your account.' : 'Your password reset link is on its way.' }}
        </p>
    </div>

    <div
        class="mb-6 flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400"
    >
        <MailCheck class="size-7" />
    </div>

    <template v-if="isRegistration">
        <p class="text-sm leading-relaxed text-muted-foreground">
            We&rsquo;ve sent a verification link to
            <span v-if="props.email" class="font-semibold text-foreground">{{ props.email }}</span>
            <span v-else class="font-semibold text-foreground">your email address</span>.
            Open the link to confirm your address and activate your account.
        </p>
    </template>
    <template v-else>
        <p class="text-sm leading-relaxed text-muted-foreground">
            We&rsquo;ve emailed a password reset link to
            <span v-if="props.email" class="font-semibold text-foreground">{{ props.email }}</span>
            <span v-else class="font-semibold text-foreground">your email address</span>.
            Follow the link to choose a new password.
        </p>
    </template>

    <p class="mt-4 rounded-lg border border-border bg-muted/40 p-4 text-sm leading-relaxed text-muted-foreground">
        Can&rsquo;t find it? Check your spam or promotions folder first — verification emails sometimes land
        there. The link expires after a short time for your security.
    </p>

    <div v-if="isRegistration" class="mt-6 grid gap-3">
        <Button
            type="button"
            variant="brand"
            size="lg"
            block
            :loading="resendForm.processing"
            @click="resend"
        >
            <RefreshCw />
            Resend verification link
        </Button>
        <p v-if="resendForm.errors.email" class="text-sm text-destructive">{{ resendForm.errors.email }}</p>
    </div>

    <div class="mt-6 border-t border-border pt-6">
        <Link
            :href="routes.login()"
            class="inline-flex items-center gap-2 text-sm font-medium text-brand-700 hover:underline dark:text-brand-400"
        >
            <ArrowLeft class="size-4" />
            Back to sign in
        </Link>
    </div>
</template>
