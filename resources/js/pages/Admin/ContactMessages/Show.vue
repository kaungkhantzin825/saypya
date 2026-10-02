<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Mail, Phone, Reply, Trash2, User } from 'lucide-vue-next';
import {
    Alert,
    Badge,
    Button,
    Card,
    Dialog,
    Label,
    Textarea,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { ContactMessage } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    message: ContactMessage;
    title?: string;
    description?: string;
}>();

const statusVariant = (value: string) =>
    value === 'new' ? 'brand' : value === 'replied' ? 'success' : 'muted';

const form = useForm({
    reply: props.message.admin_reply ?? '',
});

function submitReply() {
    form.post(routes.admin.contactMessageReply(props.message.id), { preserveScroll: true });
}

// `ContactMessage` has no SoftDeletes — the row is really removed.
const deleteOpen = ref(false);
const deleting = ref(false);

function destroy() {
    deleting.value = true;
    form.delete(routes.admin.contactMessageDestroy(props.message.id), {
        onFinish: () => {
            deleting.value = false;
            deleteOpen.value = false;
        },
    });
}

const mailtoReply = () =>
    `mailto:${props.message.email}?subject=${encodeURIComponent(`Re: ${props.message.subject ?? ''}`)}`;
</script>

<template>
    <Link
        :href="routes.admin.contactMessages()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to inbox
    </Link>

    <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="grid min-w-0 grid-cols-1 content-start gap-6 lg:col-span-2">
            <Card>
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate text-base font-bold">{{ message.subject || 'No subject' }}</h2>
                            <p class="text-sm text-muted-foreground">
                                {{ message.created_at ? new Date(message.created_at).toLocaleString() : '' }}
                            </p>
                        </div>
                        <Badge :variant="statusVariant(message.status)" class="capitalize">
                            {{ message.status }}
                        </Badge>
                    </div>
                </template>

                <p class="whitespace-pre-line text-sm leading-relaxed">{{ message.message }}</p>
            </Card>

            <Card v-if="message.admin_reply">
                <template #header>
                    <h2 class="text-base font-bold">Your reply</h2>
                    <p v-if="message.replied_at" class="text-sm text-muted-foreground">
                        Sent {{ new Date(message.replied_at).toLocaleString() }}
                    </p>
                </template>

                <p class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground">
                    {{ message.admin_reply }}
                </p>
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">
                        {{ message.admin_reply ? 'Update the reply' : 'Reply' }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Saving marks the message as replied. Sending an email is not wired up yet.
                    </p>
                </template>

                <form class="grid grid-cols-1 gap-4" @submit.prevent="submitReply">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="reply" required>Reply</Label>
                        <Textarea
                            id="reply"
                            v-model="form.reply"
                            :rows="6"
                            placeholder="Type your reply…"
                            :invalid="!!form.errors.reply"
                        />
                        <p v-if="form.errors.reply" class="text-sm text-destructive">{{ form.errors.reply }}</p>
                    </div>

                    <Alert v-if="form.hasErrors && !form.errors.reply" variant="destructive" title="Please check the form">
                        Some fields need your attention before this can be saved.
                    </Alert>

                    <div class="flex flex-wrap gap-2">
                        <Button type="submit" variant="brand" :loading="form.processing">
                            <Reply />
                            {{ message.admin_reply ? 'Update reply' : 'Send reply' }}
                        </Button>
                        <Button :href="mailtoReply()" external variant="outline">
                            <Mail />
                            Open in mail client
                        </Button>
                    </div>
                </form>
            </Card>
        </div>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Sender</h2>
                </template>

                <dl class="grid grid-cols-1 gap-3 text-sm">
                    <div class="flex items-start gap-2">
                        <User class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                        <div class="min-w-0">
                            <dt class="text-muted-foreground">Name</dt>
                            <dd class="truncate font-medium">{{ message.name }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <Mail class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                        <div class="min-w-0">
                            <dt class="text-muted-foreground">Email</dt>
                            <dd class="truncate">
                                <a :href="`mailto:${message.email}`" class="font-medium hover:underline">
                                    {{ message.email }}
                                </a>
                            </dd>
                        </div>
                    </div>
                    <div v-if="message.phone" class="flex items-start gap-2">
                        <Phone class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                        <div class="min-w-0">
                            <dt class="text-muted-foreground">Phone</dt>
                            <dd class="truncate font-medium">{{ message.phone }}</dd>
                        </div>
                    </div>
                </dl>
            </Card>

            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Danger zone</h2>
                </template>

                <Button
                    variant="outline"
                    block
                    class="border-destructive/40 text-destructive hover:bg-destructive/10 hover:text-destructive"
                    @click="deleteOpen = true"
                >
                    <Trash2 />
                    Delete message
                </Button>
            </Card>
        </div>
    </div>

    <Dialog
        :open="deleteOpen"
        size="sm"
        title="Delete message"
        :description="`Permanently delete the message from “${message.name}”? This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteOpen = false)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteOpen = false">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete message
            </Button>
        </template>
    </Dialog>
</template>
