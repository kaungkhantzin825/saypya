<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    Check,
    Clock,
    Facebook,
    Link2,
    Linkedin,
    Newspaper,
    Twitter,
} from 'lucide-vue-next';
import { Avatar, Badge, Button } from '@/components/ui';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { formatDate } from '@/lib/utils';
import { routes } from '@/lib/routes';
import type { BlogPost } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    post: BlogPost;
    relatedPosts: BlogPost[];
}>();

const authorName = computed(() => props.post.author?.name ?? 'Sanpya Academy');
const related = computed(() => props.relatedPosts ?? []);

/* --------------------------------------------------------------- share --- */

const shareUrl = computed(() => {
    const path = routes.blogPost(props.post.slug);
    if (typeof document === 'undefined') return path;
    return new URL(path, document.baseURI).toString();
});
const shareText = computed(() => encodeURIComponent(props.post.title));
const encodedUrl = computed(() => encodeURIComponent(shareUrl.value));

const copied = ref(false);

async function copyLink() {
    try {
        await navigator.clipboard.writeText(shareUrl.value);
        copied.value = true;
        window.setTimeout(() => (copied.value = false), 2000);
    } catch {
        copied.value = false;
    }
}

/* --------------------------------------------------------------- prose --- */

const proseClasses = [
    'text-[15px] leading-7 text-foreground/90 sm:text-base',
    '[&_h2]:mt-10 [&_h2]:mb-4 [&_h2]:text-2xl [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-foreground',
    '[&_h3]:mt-8 [&_h3]:mb-3 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-foreground',
    '[&_h4]:mt-6 [&_h4]:mb-2 [&_h4]:text-lg [&_h4]:font-semibold [&_h4]:text-foreground',
    '[&_p]:my-4 [&_p]:text-pretty',
    '[&_a]:font-medium [&_a]:text-brand-600 [&_a]:underline [&_a]:underline-offset-4 [&_a:hover]:text-brand-700 dark:[&_a]:text-brand-400',
    '[&_strong]:font-semibold [&_strong]:text-foreground',
    '[&_ul]:my-4 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6',
    '[&_ol]:my-4 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6',
    '[&_li]:pl-1 [&_li::marker]:text-brand-500',
    '[&_blockquote]:my-6 [&_blockquote]:rounded-r-lg [&_blockquote]:border-l-4 [&_blockquote]:border-brand-400 [&_blockquote]:bg-muted/60 [&_blockquote]:py-3 [&_blockquote]:pl-5 [&_blockquote]:pr-4 [&_blockquote]:italic [&_blockquote]:text-muted-foreground',
    '[&_code]:rounded [&_code]:bg-muted [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.85em]',
    '[&_pre]:my-6 [&_pre]:overflow-x-auto [&_pre]:rounded-xl [&_pre]:bg-slate-900 [&_pre]:p-5 [&_pre]:text-sm [&_pre]:leading-relaxed [&_pre]:text-slate-100',
    '[&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-inherit',
    '[&_img]:my-8 [&_img]:w-full [&_img]:rounded-xl [&_img]:border [&_img]:border-border',
    '[&_hr]:my-10 [&_hr]:border-border',
    '[&_table]:my-6 [&_table]:w-full [&_table]:border-collapse [&_table]:text-sm',
    '[&_th]:border [&_th]:border-border [&_th]:bg-muted [&_th]:px-3 [&_th]:py-2 [&_th]:text-left [&_th]:font-semibold',
    '[&_td]:border [&_td]:border-border [&_td]:px-3 [&_td]:py-2',
].join(' ');
</script>

<template>
    <article class="pb-16 sm:pb-24">
        <!-- ======================================================= Header -->
        <header class="relative overflow-hidden border-b border-border">
            <div class="hero-backdrop pointer-events-none absolute inset-0" />
            <div class="page-container relative py-10 sm:py-14">
                <Link
                    :href="routes.blog()"
                    class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="size-4" />
                    Back to blog
                </Link>

                <div class="mt-6 max-w-3xl">
                    <Badge v-if="post.category" variant="brand" class="mb-4">{{ post.category }}</Badge>

                    <h1 class="text-balance text-3xl font-extrabold leading-[1.15] tracking-tight sm:text-4xl lg:text-5xl">
                        {{ post.title }}
                    </h1>

                    <p v-if="post.excerpt" class="mt-5 text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                        {{ post.excerpt }}
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-3">
                        <div class="flex items-center gap-3">
                            <Avatar :src="post.author?.avatar_url" :name="authorName" size="md" />
                            <div>
                                <p class="text-sm font-semibold leading-tight">{{ authorName }}</p>
                                <p class="text-xs text-muted-foreground">Author</p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-2 text-sm text-muted-foreground">
                            <CalendarDays class="size-4" />
                            {{ formatDate(post.published_at) }}
                        </span>

                        <span class="inline-flex items-center gap-2 text-sm text-muted-foreground">
                            <Clock class="size-4" />
                            {{ post.reading_time ?? 1 }} min read
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- ======================================================== Image -->
        <div v-if="post.image_url" class="page-container pt-10">
            <img
                :src="post.image_url"
                :alt="post.title"
                class="aspect-[16/9] w-full rounded-2xl border border-border object-cover shadow-soft"
            />
        </div>

        <!-- ====================================================== Content -->
        <div class="page-container pt-10 sm:pt-14">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_15rem]">
                <div class="min-w-0">
                    <div :class="proseClasses" v-html="post.content" />

                    <!-- Share -->
                    <div
                        class="mt-12 flex flex-wrap items-center gap-3 border-t border-border pt-6"
                        aria-label="Share this article"
                    >
                        <span class="text-sm font-semibold">Share</span>

                        <a
                            :href="`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex size-9 items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:border-brand-200 hover:text-brand-600 dark:hover:border-brand-800"
                            aria-label="Share on Facebook"
                        >
                            <Facebook class="size-4" />
                        </a>
                        <a
                            :href="`https://twitter.com/intent/tweet?url=${encodedUrl}&text=${shareText}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex size-9 items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:border-brand-200 hover:text-brand-600 dark:hover:border-brand-800"
                            aria-label="Share on X"
                        >
                            <Twitter class="size-4" />
                        </a>
                        <a
                            :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex size-9 items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:border-brand-200 hover:text-brand-600 dark:hover:border-brand-800"
                            aria-label="Share on LinkedIn"
                        >
                            <Linkedin class="size-4" />
                        </a>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:border-brand-200 hover:text-brand-600 dark:hover:border-brand-800"
                            @click="copyLink"
                        >
                            <Check v-if="copied" class="size-4 text-success" />
                            <Link2 v-else class="size-4" />
                            {{ copied ? 'Link copied' : 'Copy link' }}
                        </button>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="hidden lg:block">
                    <div class="sticky top-24 grid gap-4">
                        <div class="rounded-xl border border-border bg-card p-5 shadow-soft">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-muted-foreground">
                                Written by
                            </p>
                            <div class="mt-4 flex items-center gap-3">
                                <Avatar :src="post.author?.avatar_url" :name="authorName" size="lg" />
                                <div class="min-w-0">
                                    <p class="truncate font-semibold">{{ authorName }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(post.published_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-border bg-muted/40 p-5">
                            <p class="text-sm font-semibold">Keep learning</p>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                                Turn what you read into a skill with a hands-on course.
                            </p>
                            <Button :href="routes.courses()" variant="brand" size="sm" block class="mt-4">
                                Browse courses
                            </Button>
                        </div>
                    </div>
                </aside>
            </div>

            <Link
                :href="routes.blog()"
                class="mt-12 inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" />
                Back to all articles
            </Link>
        </div>
    </article>

    <!-- =================================================== Related posts -->
    <section v-if="related.length" class="border-t border-border bg-muted/40 py-16 sm:py-20">
        <div class="page-container">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-brand-600 dark:text-brand-400">
                        Keep reading
                    </p>
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Related articles</h2>
                </div>
                <Button :href="routes.blog()" variant="outline">
                    View all
                    <ArrowRight />
                </Button>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="item in related"
                    :key="item.id"
                    :href="routes.blogPost(item.slug)"
                    class="group flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-soft transition-all hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800"
                >
                    <div class="relative aspect-[16/9] overflow-hidden">
                        <img
                            v-if="item.image_url"
                            :src="item.image_url"
                            :alt="item.title"
                            class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="flex size-full items-center justify-center bg-gradient-to-br from-brand-600 to-brand-800"
                        >
                            <Newspaper class="size-9 text-white/70" />
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col gap-3 p-5">
                        <span class="text-xs text-muted-foreground">{{ formatDate(item.published_at) }}</span>
                        <h3
                            class="line-clamp-2 text-base font-bold leading-snug transition-colors group-hover:text-brand-700 dark:group-hover:text-brand-400"
                        >
                            {{ item.title }}
                        </h3>
                        <p v-if="item.excerpt" class="line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                            {{ item.excerpt }}
                        </p>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
