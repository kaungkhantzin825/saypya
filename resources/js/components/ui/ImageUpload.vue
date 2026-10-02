<script setup lang="ts">
/**
 * Image picker with an instant local preview.
 *
 * The chosen `File` is emitted to the parent so it can be posted with Inertia's
 * `useForm`. When editing, pass `currentUrl` to show the stored image until the
 * user picks a replacement.
 */
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { ImagePlus, X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        /** Currently stored image URL (edit screens). */
        currentUrl?: string | null;
        accept?: string;
        /** Shown under the picker, e.g. "PNG or JPG, up to 2 MB." */
        hint?: string;
        /** Constrains the preview box. */
        aspect?: 'square' | 'video' | 'wide';
        label?: string;
        error?: string;
        disabled?: boolean;
    }>(),
    {
        accept: 'image/png,image/jpeg,image/webp',
        aspect: 'video',
    },
);

const emit = defineEmits<{
    (e: 'update:file', file: File | null): void;
}>();

const input = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const fileName = ref<string | null>(null);

const src = computed(() => previewUrl.value ?? props.currentUrl ?? null);

const aspectClass = computed(
    () =>
        ({
            square: 'size-24',
            video: 'h-24 w-40',
            wide: 'h-20 w-56',
        })[props.aspect],
);

/** Object URLs must be revoked or the blob leaks for the life of the page. */
function releasePreview() {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
}

function onPick(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    releasePreview();
    fileName.value = file?.name ?? null;
    previewUrl.value = file ? URL.createObjectURL(file) : null;

    emit('update:file', file);
}

function clear() {
    releasePreview();
    fileName.value = null;
    if (input.value) input.value.value = '';
    emit('update:file', null);
}

// A parent that swaps `currentUrl` (e.g. navigating between records) should drop
// any pending preview rather than show a stale one.
watch(
    () => props.currentUrl,
    () => clear(),
);

onBeforeUnmount(releasePreview);
</script>

<template>
    <div class="grid gap-3">
        <span v-if="label" class="text-sm font-medium leading-none">{{ label }}</span>

        <div class="flex items-start gap-4">
            <div
                :class="
                    cn(
                        'relative shrink-0 overflow-hidden rounded-lg border border-border bg-muted',
                        aspectClass,
                    )
                "
            >
                <img
                    v-if="src"
                    :src="src"
                    alt=""
                    class="size-full object-cover"
                />
                <span v-else class="flex size-full items-center justify-center text-muted-foreground">
                    <ImagePlus class="size-6" />
                </span>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        :disabled="disabled"
                        class="inline-flex h-9 items-center gap-2 rounded-md border border-input bg-background px-3 text-xs font-semibold shadow-sm transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                        @click="input?.click()"
                    >
                        <ImagePlus class="size-3.5" />
                        {{ src ? 'Replace image' : 'Choose image' }}
                    </button>

                    <button
                        v-if="previewUrl"
                        type="button"
                        :disabled="disabled"
                        class="inline-flex h-9 items-center gap-1.5 rounded-md px-2 text-xs font-semibold text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                        @click="clear"
                    >
                        <X class="size-3.5" />
                        Undo
                    </button>
                </div>

                <p v-if="fileName" class="mt-1.5 truncate text-xs font-medium text-foreground">
                    {{ fileName }}
                </p>
                <p v-if="hint" class="mt-1.5 text-xs text-muted-foreground">{{ hint }}</p>
                <p v-if="error" class="mt-1.5 text-sm text-destructive">{{ error }}</p>
            </div>
        </div>

        <input
            ref="input"
            type="file"
            :accept="accept"
            :disabled="disabled"
            class="sr-only"
            @change="onPick"
        />
    </div>
</template>
