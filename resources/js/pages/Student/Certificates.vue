<script setup lang="ts">
import { computed } from 'vue';
import { Award, CalendarCheck, Compass, GraduationCap, User as UserIcon } from 'lucide-vue-next';
import { Badge, Button, EmptyState } from '@/components/ui';
import StudentLayout from '@/layouts/StudentLayout.vue';
import { routes } from '@/lib/routes';
import { formatDate } from '@/lib/utils';

interface Certificate {
    id: number;
    course: {
        id: number;
        title: string;
        slug: string;
        instructor?: { name: string } | null;
    };
    completed_at: string;
    progress_percentage: number;
}

defineOptions({ layout: StudentLayout });

const props = defineProps<{
    certificates: Certificate[];
    /** Supplied by the controller for the layout header. */
    title?: string;
    description?: string;
}>();

const certificates = computed<Certificate[]>(() => props.certificates ?? []);
const hasCertificates = computed(() => certificates.value.length > 0);
</script>

<template>
    <div class="grid gap-8">
        <div v-if="hasCertificates" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="certificate in certificates"
                :key="certificate.id"
                class="flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-lift"
            >
                <!-- Decorative certificate visual (CSS only) -->
                <div
                    class="relative flex h-44 items-center justify-center overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800"
                >
                    <div
                        class="pointer-events-none absolute inset-4 rounded-lg border-2 border-dashed border-white/40"
                    />
                    <div
                        class="pointer-events-none absolute -right-6 -top-6 size-24 rounded-full bg-white/10"
                        aria-hidden="true"
                    />
                    <div
                        class="pointer-events-none absolute -bottom-8 -left-8 size-28 rounded-full bg-white/10"
                        aria-hidden="true"
                    />

                    <div class="relative flex flex-col items-center gap-2 px-6 text-center text-white">
                        <span
                            class="flex size-14 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/40 backdrop-blur"
                        >
                            <Award class="size-7" />
                        </span>
                        <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/85">
                            Certificate of completion
                        </p>
                    </div>
                </div>

                <div class="flex flex-1 flex-col gap-3 p-5">
                    <Badge variant="success" class="w-fit">
                        <GraduationCap class="size-3" />
                        Completed
                    </Badge>

                    <h2 class="line-clamp-2 text-base font-bold leading-snug">
                        {{ certificate.course.title }}
                    </h2>

                    <p class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                        <UserIcon class="size-3.5 shrink-0" />
                        {{ certificate.course.instructor?.name ?? 'Sanpya Academy' }}
                    </p>

                    <div class="mt-auto grid gap-4 pt-2">
                        <p class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                            <CalendarCheck class="size-3.5 shrink-0" />
                            Completed {{ formatDate(certificate.completed_at) }}
                        </p>

                        <Button :href="routes.course(certificate.course.slug)" variant="outline" block>
                            View course
                        </Button>
                    </div>
                </div>
            </article>
        </div>

        <EmptyState
            v-else
            :icon="Award"
            title="No certificates yet"
            description="Certificates are earned by completing a course and passing its final exam. Finish a course to see yours here."
        >
            <Button :href="routes.courses()" variant="brand">
                Browse courses
                <Compass />
            </Button>
        </EmptyState>
    </div>
</template>
