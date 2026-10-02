<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Save, Star, Trash2 } from 'lucide-vue-next';
import {
    Alert,
    Avatar,
    Button,
    Card,
    Checkbox,
    Dialog,
    Label,
    Select,
    Textarea,
} from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { Review } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    review: Review;
    title?: string;
    description?: string;
}>();

const ratingOptions = [1, 2, 3, 4, 5].map((value) => ({
    value: String(value),
    label: `${value} star${value > 1 ? 's' : ''}`,
}));

const form = useForm({
    rating: String(props.review.rating),
    comment: props.review.comment ?? '',
    is_approved: props.review.is_approved,
});

function submit() {
    form.put(routes.admin.reviewUpdate(props.review.id));
}

// `Review` has no SoftDeletes — the row is really removed.
const deleteOpen = ref(false);
const deleting = ref(false);

function destroy() {
    deleting.value = true;
    form.delete(routes.admin.reviewDestroy(props.review.id), {
        onFinish: () => {
            deleting.value = false;
            deleteOpen.value = false;
        },
    });
}
</script>

<template>
    <Link
        :href="routes.admin.reviews()"
        class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
    >
        <ArrowLeft class="size-4" />
        Back to reviews
    </Link>

    <form class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-3" @submit.prevent="submit">
        <div class="grid min-w-0 grid-cols-1 content-start gap-6 lg:col-span-2">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Review details</h2>
                    <p class="text-sm text-muted-foreground">
                        Posted by {{ review.user?.name ?? 'Anonymous' }} on
                        {{ review.course?.title ?? 'a deleted course' }}.
                    </p>
                </template>

                <div class="grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 gap-2">
                        <Label for="rating" required>Rating</Label>
                        <Select id="rating" v-model="form.rating" :options="ratingOptions" />
                        <p v-if="form.errors.rating" class="text-sm text-destructive">{{ form.errors.rating }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <Label for="comment">Comment</Label>
                        <Textarea
                            id="comment"
                            v-model="form.comment"
                            :rows="6"
                            maxlength="1000"
                            placeholder="Review comment (optional)"
                            :invalid="!!form.errors.comment"
                        />
                        <p v-if="form.errors.comment" class="text-sm text-destructive">{{ form.errors.comment }}</p>
                        <p v-else class="text-xs text-muted-foreground">Maximum 1000 characters.</p>
                    </div>

                    <div class="flex items-start gap-3">
                        <Checkbox id="is_approved" v-model="form.is_approved" class="mt-0.5" />
                        <div>
                            <Label for="is_approved" class="cursor-pointer font-normal">Approved</Label>
                            <p class="text-xs text-muted-foreground">
                                Approved reviews are visible on the public course page.
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <Alert v-if="form.hasErrors" variant="destructive" title="Please check the form">
                Some fields need your attention before this can be saved.
            </Alert>

            <div class="flex flex-col gap-2">
                <Button type="submit" variant="brand" size="lg" :loading="form.processing">
                    <Save />
                    Save changes
                </Button>
                <Button :href="routes.admin.reviews()" variant="ghost" :disabled="form.processing">Cancel</Button>
            </div>
        </div>

        <div class="grid min-w-0 grid-cols-1 content-start gap-6">
            <Card>
                <template #header>
                    <h2 class="text-base font-bold">Preview</h2>
                </template>

                <div class="flex items-start gap-3">
                    <Avatar :src="review.user?.avatar_url" :name="review.user?.name" size="lg" />
                    <div class="min-w-0">
                        <p class="font-semibold">{{ review.user?.name ?? 'Anonymous' }}</p>
                        <span class="mt-1 flex items-center gap-0.5" :aria-label="`${form.rating} out of 5`">
                            <Star
                                v-for="index in 5"
                                :key="index"
                                class="size-4"
                                :class="index <= Number(form.rating) ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30'"
                            />
                        </span>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ review.created_at ? new Date(review.created_at).toLocaleString() : '' }}
                        </p>
                    </div>
                </div>

                <p v-if="form.comment" class="mt-4 text-sm leading-relaxed">{{ form.comment }}</p>
                <p v-else class="mt-4 text-sm italic text-muted-foreground">No comment provided</p>
            </Card>

            <Card v-if="review.course">
                <template #header>
                    <h2 class="text-base font-bold">Course</h2>
                </template>

                <img
                    v-if="review.course.thumbnail_url"
                    :src="review.course.thumbnail_url"
                    :alt="review.course.title"
                    class="mb-3 aspect-video w-full rounded-lg object-cover ring-1 ring-border"
                />
                <p class="font-semibold">{{ review.course.title }}</p>
                <p class="mb-3 text-sm text-muted-foreground">
                    by {{ review.course.instructor?.name ?? 'N/A' }}
                </p>

                <Button :href="routes.course(review.course.slug)" external variant="outline" block>
                    <ExternalLink />
                    View course
                </Button>
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
                    Delete review
                </Button>
            </Card>
        </div>
    </form>

    <Dialog
        :open="deleteOpen"
        size="sm"
        title="Delete review"
        :description="`Permanently delete this review by “${review.user?.name ?? 'this student'}”? This cannot be undone.`"
        @update:open="(value: boolean) => !value && (deleteOpen = false)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteOpen = false">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete review
            </Button>
        </template>
    </Dialog>
</template>
