<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock } from 'lucide-vue-next';
import { Button, Input, Label, Progress } from '@/components/ui';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { routes } from '@/lib/routes';

defineOptions({ layout: AuthLayout });

const props = defineProps<{
    title?: string;
    subtitle?: string;
}>();

const form = useForm({
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmation = ref(false);
const showOwnHeader = computed(() => !props.title);

const strength = computed(() => {
    const value = form.password ?? '';
    let score = 0;
    if (value.length >= 8) score += 1;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score += 1;
    if (/\d/.test(value)) score += 1;
    if (/[^A-Za-z0-9]/.test(value)) score += 1;

    const labels = ['Too short', 'Weak', 'Fair', 'Good', 'Strong'];
    const tones = ['bg-muted-foreground/40', 'bg-destructive', 'bg-warning', 'bg-brand-500', 'bg-success'];

    return {
        label: labels[score],
        tone: tones[score],
        percent: (score / 4) * 100,
    };
});

function submit() {
    form.post(routes.resetPassword(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <div v-if="showOwnHeader" class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight">Set a new password</h1>
        <p class="mt-2 text-sm text-muted-foreground">
            Choose a strong password you haven&rsquo;t used before.
        </p>
    </div>

    <form class="grid gap-5" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="password" required>New password</Label>
            <div class="relative">
                <Input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    :icon="Lock"
                    autocomplete="new-password"
                    placeholder="New password"
                    class="pr-10"
                    :invalid="!!form.errors.password"
                    :aria-invalid="!!form.errors.password"
                    :aria-describedby="form.errors.password ? 'password-error' : 'password-hint'"
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

            <div v-if="form.password" class="mt-1 grid gap-1.5">
                <Progress :value="strength.percent" size="sm" :indicator-class="strength.tone" />
                <p class="text-xs text-muted-foreground">
                    Password strength: <span class="font-semibold text-foreground">{{ strength.label }}</span>
                </p>
            </div>

            <p v-if="!form.password" id="password-hint" class="text-xs text-muted-foreground">
                Use at least 8 characters with a mix of letters, numbers and symbols.
            </p>
            <p v-if="form.errors.password" id="password-error" class="text-sm text-destructive">
                {{ form.errors.password }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="password_confirmation" required>Confirm new password</Label>
            <div class="relative">
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="showConfirmation ? 'text' : 'password'"
                    :icon="Lock"
                    autocomplete="new-password"
                    placeholder="Re-enter your new password"
                    class="pr-10"
                    :invalid="!!form.errors.password_confirmation"
                    :aria-invalid="!!form.errors.password_confirmation"
                    :aria-describedby="form.errors.password_confirmation ? 'password-confirmation-error' : undefined"
                />
                <button
                    type="button"
                    class="absolute right-1.5 top-1/2 inline-flex size-7 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30"
                    :aria-label="showConfirmation ? 'Hide password' : 'Show password'"
                    :aria-pressed="showConfirmation"
                    @click="showConfirmation = !showConfirmation"
                >
                    <EyeOff v-if="showConfirmation" class="size-4" />
                    <Eye v-else class="size-4" />
                </button>
            </div>
            <p v-if="form.errors.password_confirmation" id="password-confirmation-error" class="text-sm text-destructive">
                {{ form.errors.password_confirmation }}
            </p>
        </div>

        <Button type="submit" variant="brand" size="lg" block :loading="form.processing">
            Reset password
        </Button>
    </form>

    <div class="mt-8 border-t border-border pt-6">
        <Link
            :href="routes.login()"
            class="text-sm font-medium text-brand-700 hover:underline dark:text-brand-400"
        >
            Back to sign in
        </Link>
    </div>
</template>
