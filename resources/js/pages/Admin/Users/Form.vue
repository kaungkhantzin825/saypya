<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff, ImagePlus, Lock, Mail, MapPin, Phone, User as UserIcon } from 'lucide-vue-next';
import { Alert, Button, Card, Checkbox, Input, Label, Select, Textarea } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { Role } from '@/types';

defineOptions({ layout: AdminLayout });

interface EditableUser {
    id: number;
    name: string;
    email: string;
    role: Role;
    phone?: string | null;
    country?: string | null;
    bio?: string | null;
    status?: string;
    is_active?: boolean;
    avatar_url?: string;
}

const props = defineProps<{
    /** `null` when creating. */
    user: EditableUser | null;
    title?: string;
    description?: string;
}>();

const isEdit = computed(() => props.user !== null);

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    role: (props.user?.role ?? 'student') as Role,
    phone: props.user?.phone ?? '',
    country: props.user?.country ?? '',
    bio: props.user?.bio ?? '',
    is_active: props.user?.is_active ?? true,
    avatar: null as File | null,
});

const roleOptions = [
    { value: 'student', label: 'Student' },
    { value: 'lecturer', label: 'Lecturer' },
    { value: 'admin', label: 'Administrator' },
];

const showPassword = ref(false);
const avatarPreview = ref<string | null>(null);

/** Show the picked file immediately; the server URL is the fallback when editing. */
const avatarSrc = computed(() => avatarPreview.value ?? props.user?.avatar_url ?? null);

function onAvatarChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.avatar = file;

    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
    avatarPreview.value = file ? URL.createObjectURL(file) : null;
}

function submit() {
    if (isEdit.value && props.user) {
        // A file upload cannot be sent as a real PUT, so post with _method spoofing.
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(routes.admin.userUpdate(props.user.id));
        return;
    }

    form.post(routes.admin.userStore());
}

const passwordHint = computed(() =>
    isEdit.value ? 'Leave blank to keep the current password.' : 'At least 8 characters.',
);
</script>

<template>
    <Link
        :href="routes.admin.users()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to users
    </Link>

    <form class="grid grid-cols-1 min-w-0 gap-6 lg:grid-cols-3" @submit.prevent="submit">
        <!-- Main details -->
        <Card class="min-w-0 lg:col-span-2">
            <template #header>
                <h2 class="text-base font-bold">Account details</h2>
                <p class="text-sm text-muted-foreground">
                    {{ isEdit ? 'Update the account information below.' : 'All fields marked required must be filled in.' }}
                </p>
            </template>

            <div class="grid grid-cols-1 gap-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="name" required>Full name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            :icon="UserIcon"
                            autocomplete="name"
                            placeholder="Jane Doe"
                            :invalid="!!form.errors.name"
                            :aria-invalid="!!form.errors.name"
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="email" required>Email address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            :icon="Mail"
                            autocomplete="email"
                            placeholder="jane@example.com"
                            :invalid="!!form.errors.email"
                            :aria-invalid="!!form.errors.email"
                        />
                        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="password" :required="!isEdit">Password</Label>
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                :icon="Lock"
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="pr-10"
                                :invalid="!!form.errors.password"
                                :aria-invalid="!!form.errors.password"
                            />
                            <button
                                type="button"
                                class="absolute right-1.5 top-1/2 inline-flex size-7 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="size-4" />
                                <Eye v-else class="size-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        <p v-else class="text-xs text-muted-foreground">{{ passwordHint }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="password_confirmation" :required="!isEdit">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            :icon="Lock"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            :invalid="!!form.errors.password_confirmation"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="role" required>Role</Label>
                        <Select id="role" v-model="form.role" :options="roleOptions" />
                        <p v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="phone">Phone</Label>
                        <Input id="phone" v-model="form.phone" :icon="Phone" placeholder="+95 …" />
                        <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="country">Country</Label>
                        <Input id="country" v-model="form.country" :icon="MapPin" placeholder="Myanmar" />
                        <p v-if="form.errors.country" class="text-sm text-destructive">{{ form.errors.country }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <Label for="bio">Bio</Label>
                    <Textarea id="bio" v-model="form.bio" :rows="4" placeholder="A short introduction…" />
                    <p v-if="form.errors.bio" class="text-sm text-destructive">{{ form.errors.bio }}</p>
                </div>
            </div>
        </Card>

        <!-- Sidebar -->
        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Avatar</h2>
                </template>

                <div class="grid grid-cols-1 gap-4">
                    <div class="flex items-center gap-4">
                        <img
                            v-if="avatarSrc"
                            :src="avatarSrc"
                            :alt="form.name || 'Avatar'"
                            class="size-16 rounded-full object-cover ring-1 ring-border"
                        />
                        <span
                            v-else
                            class="flex size-16 items-center justify-center rounded-full bg-muted text-muted-foreground"
                        >
                            <ImagePlus class="size-6" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <label
                                for="avatar"
                                class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-md border border-input bg-background px-3 text-xs font-semibold shadow-sm transition-colors hover:bg-accent"
                            >
                                <ImagePlus class="size-3.5" />
                                Choose image
                            </label>
                            <input
                                id="avatar"
                                type="file"
                                accept="image/png,image/jpeg"
                                class="sr-only"
                                @change="onAvatarChange"
                            />
                            <p class="mt-1.5 text-xs text-muted-foreground">PNG or JPG, up to 2 MB.</p>
                        </div>
                    </div>

                    <p v-if="form.errors.avatar" class="text-sm text-destructive">{{ form.errors.avatar }}</p>
                </div>
            </Card>

            <Card v-if="isEdit">
                <template #header>
                    <h2 class="text-base font-bold">Status</h2>
                </template>

                <div class="flex items-start gap-3">
                    <Checkbox id="is_active" v-model="form.is_active" class="mt-0.5" />
                    <div>
                        <Label for="is_active" class="cursor-pointer font-normal">Account is active</Label>
                        <p class="text-xs text-muted-foreground">
                            Inactive accounts cannot sign in.
                        </p>
                    </div>
                </div>
            </Card>

            <Alert v-if="form.hasErrors" variant="destructive" title="Please check the form">
                Some fields need your attention before this can be saved.
            </Alert>

            <div class="flex flex-col gap-2">
                <Button type="submit" variant="brand" size="lg" :loading="form.processing">
                    {{ isEdit ? 'Save changes' : 'Create user' }}
                </Button>
                <Button :href="routes.admin.users()" variant="ghost" :disabled="form.processing">
                    Cancel
                </Button>
            </div>
        </div>
    </form>
</template>
