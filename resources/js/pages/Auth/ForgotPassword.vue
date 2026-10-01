<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Mail } from 'lucide-vue-next';
import { Alert, Button, Input, Label } from '@/components/ui';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: AuthLayout });

const props = defineProps<{
    status?: string | null;
    title?: string;
    subtitle?: string;
}>();

const form = useForm({ email: '' });

const showOwnHeader = computed(() => !props.title);

function submit() {
    form.post(routes.forgotPassword());
}
</script>

<template>
    <div v-if="showOwnHeader" class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight">Forgot your password?</h1>
        <p class="mt-2 text-sm text-muted-foreground">
            Enter your email address and we&rsquo;ll send you a secure link to reset it.
        </p>
    </div>

    <Alert v-if="props.status" variant="success" class="mb-6">
        {{ props.status }}
    </Alert>

    <form class="grid gap-5" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="email" required>Email address</Label>
            <Input
                id="email"
                v-model="form.email"
                type="email"
                :icon="Mail"
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

        <Button type="submit" variant="brand" size="lg" block :loading="form.processing">
            Email reset link
        </Button>
    </form>

    <div class="mt-8 border-t border-border pt-6">
        <Link
            :href="routes.login()"
            class="inline-flex items-center gap-2 text-sm font-medium text-brand-700 hover:underline dark:text-brand-400"
        >
            <ArrowLeft class="size-4" />
            Back to sign in
        </Link>
    </div>
</template>
