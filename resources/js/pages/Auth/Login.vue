<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail } from 'lucide-vue-next';
import { Alert, Button, Checkbox, Input, Label } from '@/components/ui';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: AuthLayout });

const props = withDefaults(
    defineProps<{
        status?: string | null;
        canResetPassword?: boolean;
        title?: string;
        subtitle?: string;
    }>(),
    { canResetPassword: true },
);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

// The layout renders a title when the server supplies one; otherwise show our own.
const showOwnHeader = computed(() => !props.title);

function submit() {
    form.post(routes.login(), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <div v-if="showOwnHeader" class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight">Welcome back</h1>
        <p class="mt-2 text-sm text-muted-foreground">Sign in to continue your learning.</p>
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

        <div class="grid gap-2">
            <Label for="password" required>Password</Label>
            <div class="relative">
                <Input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    :icon="Lock"
                    autocomplete="current-password"
                    placeholder="Your password"
                    class="pr-10"
                    :invalid="!!form.errors.password"
                    :aria-invalid="!!form.errors.password"
                    :aria-describedby="form.errors.password ? 'password-error' : undefined"
                />
                <button
                    type="button"
                    class="absolute right-1.5 top-1/2 inline-flex size-7 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    :aria-pressed="showPassword"
                    @click="showPassword = !showPassword"
                >
                    <EyeOff v-if="showPassword" class="size-4" />
                    <Eye v-else class="size-4" />
                </button>
            </div>
            <p v-if="form.errors.password" id="password-error" class="text-sm text-destructive">
                {{ form.errors.password }}
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <Checkbox id="remember" v-model="form.remember" />
                <Label for="remember" class="cursor-pointer font-normal">Remember me</Label>
            </div>

            <Link
                v-if="props.canResetPassword"
                :href="routes.forgotPassword()"
                class="text-sm font-medium text-brand-700 hover:underline dark:text-brand-400"
            >
                Forgot password?
            </Link>
        </div>

        <Button type="submit" variant="brand" size="lg" block :loading="form.processing">
            Sign in
        </Button>
    </form>

    <p class="mt-8 text-center text-sm text-muted-foreground">
        Don&rsquo;t have an account?
        <Link :href="routes.register()" class="font-semibold text-brand-700 hover:underline dark:text-brand-400">
            Create one
        </Link>
    </p>
</template>
