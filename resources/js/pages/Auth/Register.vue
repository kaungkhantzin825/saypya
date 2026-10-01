<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail, User } from 'lucide-vue-next';
import { Alert, Button, Checkbox, Input, Label, Progress, RadioGroup } from '@/components/ui';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { routes } from '@/lib/routes';
import type { RadioOption } from '@/types/ui';

defineOptions({ layout: AuthLayout });

const props = defineProps<{
    registrationEnabled: boolean;
    title?: string;
    subtitle?: string;
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'student',
    terms: false,
});

const showPassword = ref(false);
const showOwnHeader = computed(() => !props.title);

const roleOptions: RadioOption[] = [
    {
        value: 'student',
        label: 'I want to learn',
        description: 'Enrol in courses, take exams and earn certificates.',
    },
    {
        value: 'lecturer',
        label: 'I want to teach',
        description: 'Publish your own courses and reach students across Myanmar.',
    },
];

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
    form.post(routes.register(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <div v-if="showOwnHeader" class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight">Create your account</h1>
        <p class="mt-2 text-sm text-muted-foreground">Start learning in minutes — it&rsquo;s free to join.</p>
    </div>

    <!-- Registration closed -->
    <template v-if="!props.registrationEnabled">
        <Alert variant="warning" title="Registration is currently closed">
            We have paused new sign-ups for a short while. Please check back soon, or
            <Link :href="routes.login()" class="font-semibold underline underline-offset-4">sign in</Link>
            if you already have an account.
        </Alert>

        <Button :href="routes.login()" variant="outline" size="lg" block class="mt-6">
            Back to sign in
        </Button>
    </template>

    <!-- Registration form -->
    <form v-else class="grid gap-5" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name" required>Full name</Label>
            <Input
                id="name"
                v-model="form.name"
                :icon="User"
                autocomplete="name"
                placeholder="Your full name"
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
                    autocomplete="new-password"
                    placeholder="Create a password"
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
            <Label for="password_confirmation" required>Confirm password</Label>
            <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                :icon="Lock"
                autocomplete="new-password"
                placeholder="Re-enter your password"
                :invalid="!!form.errors.password_confirmation"
                :aria-invalid="!!form.errors.password_confirmation"
                :aria-describedby="form.errors.password_confirmation ? 'password-confirmation-error' : undefined"
            />
            <p v-if="form.errors.password_confirmation" id="password-confirmation-error" class="text-sm text-destructive">
                {{ form.errors.password_confirmation }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label required>How do you want to use Sanpya?</Label>
            <RadioGroup v-model="form.role" :options="roleOptions" name="role" />
            <p v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</p>
        </div>

        <div class="grid gap-2">
            <div class="flex items-start gap-2">
                <Checkbox id="terms" v-model="form.terms" class="mt-0.5" />
                <Label for="terms" class="cursor-pointer font-normal leading-relaxed">
                    I agree to the
                    <Link :href="routes.terms()" class="font-medium text-brand-700 hover:underline dark:text-brand-400">
                        Terms of Service
                    </Link>
                    and
                    <Link :href="routes.privacy()" class="font-medium text-brand-700 hover:underline dark:text-brand-400">
                        Privacy Policy
                    </Link>
                    .
                </Label>
            </div>
            <p v-if="form.errors.terms" class="text-sm text-destructive">{{ form.errors.terms }}</p>
        </div>

        <Button type="submit" variant="brand" size="lg" block :loading="form.processing">
            Create account
        </Button>
    </form>

    <p v-if="props.registrationEnabled" class="mt-8 text-center text-sm text-muted-foreground">
        Already have an account?
        <Link :href="routes.login()" class="font-semibold text-brand-700 hover:underline dark:text-brand-400">
            Sign in
        </Link>
    </p>
</template>
