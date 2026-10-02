<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Info, RotateCcw, Save } from 'lucide-vue-next';
import { Alert, Button, Card, ImageUpload, Input, Label, Textarea } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { routes } from '@/lib/routes';
import type { SiteSetting, SiteSettingGroup } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    groups: SiteSettingGroup[];
    title?: string;
    description?: string;
}>();

/** Flat, keyed state so each control binds by setting key. */
const initialSettings: Record<string, string> = {};
const initialImages: Record<string, File | null> = {};

for (const group of props.groups) {
    for (const setting of group.settings) {
        initialSettings[setting.key] = setting.value ?? '';
        initialImages[setting.key] = null;
    }
}

const form = useForm({ settings: initialSettings, images: initialImages });

/** Textareas and image pickers get the full width of the group grid. */
const isWide = (setting: SiteSetting) => setting.type === 'textarea' || setting.type === 'image';

function submit() {
    // Only send images the user actually picked. A `null` entry would be
    // serialised as an empty string and Laravel would still try to validate it
    // against the `image` rule.
    const images: Record<string, File> = {};
    for (const [key, file] of Object.entries(form.images)) {
        if (file) images[key] = file;
    }

    form
        // A file upload cannot be sent as a real PUT, so spoof the method.
        .transform((data) => ({ ...data, images, _method: 'put' }))
        .post(routes.admin.settingsUpdate(), { preserveScroll: true });
}
</script>

<template>
    <form class="grid grid-cols-1 gap-6" @submit.prevent="submit">
        <Card v-for="group in props.groups" :key="group.key" class="min-w-0">
            <template #header>
                <h2 class="text-base font-bold capitalize">{{ group.label }}</h2>
                <p class="text-sm text-muted-foreground">
                    {{ group.settings.length }}
                    {{ group.settings.length === 1 ? 'setting' : 'settings' }}
                </p>
            </template>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div
                    v-for="setting in group.settings"
                    :key="setting.key"
                    class="grid grid-cols-1 content-start gap-2"
                    :class="isWide(setting) && 'sm:col-span-2'"
                >
                    <Label :for="setting.key" :required="setting.type === 'number'">
                        {{ setting.label }}
                    </Label>

                    <p v-if="setting.description" class="text-xs text-muted-foreground">
                        {{ setting.description }}
                    </p>

                    <!-- Image: URL field plus an upload that overrides it -->
                    <template v-if="setting.type === 'image'">
                        <Input
                            :id="setting.key"
                            v-model="form.settings[setting.key]"
                            placeholder="https://example.com/image.jpg"
                            :invalid="!!form.errors[`settings.${setting.key}`]"
                        />
                        <p class="text-xs text-muted-foreground">
                            Paste an image URL, or upload a file below — uploading overrides the URL.
                        </p>
                        <ImageUpload
                            :current-url="setting.image_url"
                            hint="PNG, JPG or WebP, up to 4 MB."
                            aspect="video"
                            :error="form.errors[`images.${setting.key}`]"
                            @update:file="(file) => (form.images[setting.key] = file)"
                        />
                    </template>

                    <Textarea
                        v-else-if="setting.type === 'textarea'"
                        :id="setting.key"
                        v-model="form.settings[setting.key]"
                        :rows="4"
                        :invalid="!!form.errors[`settings.${setting.key}`]"
                    />

                    <Input
                        v-else-if="setting.type === 'number'"
                        :id="setting.key"
                        v-model="form.settings[setting.key]"
                        type="number"
                        :invalid="!!form.errors[`settings.${setting.key}`]"
                    />

                    <Input
                        v-else
                        :id="setting.key"
                        v-model="form.settings[setting.key]"
                        :invalid="!!form.errors[`settings.${setting.key}`]"
                    />

                    <p v-if="form.errors[`settings.${setting.key}`]" class="text-sm text-destructive">
                        {{ form.errors[`settings.${setting.key}`] }}
                    </p>
                </div>
            </div>
        </Card>

        <Alert v-if="props.groups.length === 0" variant="info" title="No settings defined">
            There are no rows in the <code>site_settings</code> table yet.
        </Alert>

        <Alert v-if="form.hasErrors" variant="destructive" title="Please check the form">
            Some settings could not be saved. Fix the highlighted fields and try again.
        </Alert>

        <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-start gap-1.5 text-xs text-muted-foreground">
                <Info class="mt-0.5 size-3.5 shrink-0" />
                Saved values are cached for an hour; saving clears that cache immediately.
            </p>

            <div class="flex shrink-0 gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    :disabled="form.processing"
                    @click="form.reset()"
                >
                    <RotateCcw />
                    Discard changes
                </Button>
                <Button type="submit" variant="brand" :loading="form.processing">
                    <Save />
                    Save settings
                </Button>
            </div>
        </div>
    </form>
</template>
