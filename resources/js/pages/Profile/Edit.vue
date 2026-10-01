<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Alert, Avatar, Button, Card, Dialog, Input, Label, Textarea } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import type { User } from '@/types';

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    user: User;
    status?: string | null;
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

/* ----------------------------------------------------- profile form --- */

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
    country: props.user.country ?? '',
    bio: props.user.bio ?? '',
    avatar: null as File | null,
});

const preview = ref<string | null>(null);

function onAvatarChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = file ? URL.createObjectURL(file) : null;
    profileForm.avatar = file;
}

onBeforeUnmount(() => {
    if (preview.value) URL.revokeObjectURL(preview.value);
});

function submitProfile() {
    profileForm.patch(routes.profileUpdate(), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            if (preview.value) URL.revokeObjectURL(preview.value);
            preview.value = null;
        },
    });
}

/* ---------------------------------------------------- password form --- */

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submitPassword() {
    passwordForm.patch(routes.profileUpdate(), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => passwordForm.reset('password', 'password_confirmation'),
    });
}

/* ------------------------------------------------------ delete form --- */

const deleteOpen = ref(false);
const deleteForm = useForm({ password: '' });

function confirmDelete() {
    deleteForm.delete(routes.profile(), {
        errorBag: 'userDeletion',
        preserveScroll: true,
        onError: () => deleteForm.reset(),
    });
}
</script>

<template>
    <div class="grid gap-8 lg:max-w-3xl">
        <Alert v-if="status === 'profile-updated'" variant="success" title="Saved">
            Your profile information has been updated.
        </Alert>

        <!-- =========================================== Profile information -->
        <Card>
            <template #header>
                <h2 class="text-base font-semibold leading-none tracking-tight">Profile information</h2>
                <p class="text-sm text-muted-foreground">Update your account details and public profile.</p>
            </template>

            <form class="grid gap-6" @submit.prevent="submitProfile">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <Avatar :src="preview ?? user.avatar_url" :name="user.name" size="2xl" />

                    <div class="grid flex-1 gap-2">
                        <Label for="avatar">Profile photo</Label>
                        <Input
                            id="avatar"
                            type="file"
                            accept="image/*"
                            aria-describedby="avatar-hint"
                            @change="onAvatarChange"
                        />
                        <p id="avatar-hint" class="text-xs text-muted-foreground">
                            PNG or JPG. A square image works best.
                        </p>
                        <p v-if="profileForm.errors.avatar" class="text-xs font-medium text-destructive">
                            {{ profileForm.errors.avatar }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="name" required>Name</Label>
                    <Input
                        id="name"
                        v-model="profileForm.name"
                        autocomplete="name"
                        :invalid="!!profileForm.errors.name"
                    />
                    <p v-if="profileForm.errors.name" class="text-xs font-medium text-destructive">
                        {{ profileForm.errors.name }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="email" required>Email address</Label>
                    <Input
                        id="email"
                        v-model="profileForm.email"
                        type="email"
                        autocomplete="email"
                        :invalid="!!profileForm.errors.email"
                    />
                    <p v-if="profileForm.errors.email" class="text-xs font-medium text-destructive">
                        {{ profileForm.errors.email }}
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            v-model="profileForm.phone"
                            type="tel"
                            autocomplete="tel"
                            placeholder="+95 9 000 000 000"
                            :invalid="!!profileForm.errors.phone"
                        />
                        <p v-if="profileForm.errors.phone" class="text-xs font-medium text-destructive">
                            {{ profileForm.errors.phone }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="country">Country</Label>
                        <Input
                            id="country"
                            v-model="profileForm.country"
                            autocomplete="country-name"
                            placeholder="Myanmar"
                            :invalid="!!profileForm.errors.country"
                        />
                        <p v-if="profileForm.errors.country" class="text-xs font-medium text-destructive">
                            {{ profileForm.errors.country }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="bio">Bio</Label>
                    <Textarea
                        id="bio"
                        v-model="profileForm.bio"
                        :rows="4"
                        placeholder="Tell other learners a little about yourself."
                        :invalid="!!profileForm.errors.bio"
                    />
                    <p v-if="profileForm.errors.bio" class="text-xs font-medium text-destructive">
                        {{ profileForm.errors.bio }}
                    </p>
                </div>

                <div>
                    <Button type="submit" variant="brand" :loading="profileForm.processing">
                        Save changes
                    </Button>
                </div>
            </form>
        </Card>

        <!-- =========================================== Update password -->
        <Card>
            <template #header>
                <h2 class="text-base font-semibold leading-none tracking-tight">Update password</h2>
                <p class="text-sm text-muted-foreground">
                    Use a long, random password to keep your account secure.
                </p>
            </template>

            <form class="grid gap-6" @submit.prevent="submitPassword">
                <div class="grid gap-2">
                    <Label for="current_password" required>Current password</Label>
                    <Input
                        id="current_password"
                        v-model="passwordForm.current_password"
                        type="password"
                        autocomplete="current-password"
                        :invalid="!!passwordForm.errors.current_password"
                    />
                    <p v-if="passwordForm.errors.current_password" class="text-xs font-medium text-destructive">
                        {{ passwordForm.errors.current_password }}
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="password" required>New password</Label>
                        <Input
                            id="password"
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            :invalid="!!passwordForm.errors.password"
                        />
                        <p v-if="passwordForm.errors.password" class="text-xs font-medium text-destructive">
                            {{ passwordForm.errors.password }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" required>Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            :invalid="!!passwordForm.errors.password_confirmation"
                        />
                        <p
                            v-if="passwordForm.errors.password_confirmation"
                            class="text-xs font-medium text-destructive"
                        >
                            {{ passwordForm.errors.password_confirmation }}
                        </p>
                    </div>
                </div>

                <div>
                    <Button type="submit" variant="brand" :loading="passwordForm.processing">
                        Update password
                    </Button>
                </div>
            </form>
        </Card>

        <!-- =============================================== Danger zone -->
        <Card class="border-destructive/40">
            <template #header>
                <h2 class="text-base font-semibold leading-none tracking-tight text-destructive">
                    Delete account
                </h2>
                <p class="text-sm text-muted-foreground">
                    Permanently delete your account and all of its data. This cannot be undone.
                </p>
            </template>

            <Dialog
                v-model:open="deleteOpen"
                title="Delete your account"
                description="Enter your password to confirm. Your account and all associated data will be permanently removed."
            >
                <template #trigger>
                    <Button variant="destructive">Delete account</Button>
                </template>

                <form class="grid gap-5" @submit.prevent="confirmDelete">
                    <div class="grid gap-2">
                        <Label for="delete_password" required>Password</Label>
                        <Input
                            id="delete_password"
                            v-model="deleteForm.password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Your current password"
                            :invalid="!!deleteForm.errors.password"
                        />
                        <p v-if="deleteForm.errors.password" class="text-xs font-medium text-destructive">
                            {{ deleteForm.errors.password }}
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" @click="deleteOpen = false">
                            Cancel
                        </Button>
                        <Button type="submit" variant="destructive" :loading="deleteForm.processing">
                            Delete account
                        </Button>
                    </div>
                </form>
            </Dialog>
        </Card>
    </div>
</template>
