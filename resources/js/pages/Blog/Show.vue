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
    Twitter,
} from 'lucide-vue-next';
import { AppImage, Avatar, Badge, Button } from '@/components/ui';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { cn, formatDate, hasMyanmar } from '@/lib/utils';
import { routes } from '@/lib/routes';
import type { BlogPost } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    post: BlogPost;
    relatedPosts: BlogPost[];
}>();

const authorName = computed(() => props.post.author?.name ?? 'Sanpya Academy');
const related = computed(() => props.relatedPosts ?? []);

/**
 * Myanmar stacks diacritics above *and* below the base character, so it needs
 * much more leading than Latin — at the 1.5 that suits English, the marks of
 * adjacent lines collide. Line-height can't be set per character the way
 * font-family can, so the post has to be detected and the block opted in.
 */
const isMyanmar = computed(() => hasMyanmar(props.post.title) || hasMyanmar(props.post.content));

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

const proseClasses = computed(() =>
    [
        /**
         * Sizes are written as arbitrary values (`text-[16px]`) rather than the
         * named scale on purpose: `text-base` also sets a line-height, and because
         * the responsive variant is emitted after the base utilities, `sm:text-base`
         * silently overrode the `leading-*` next to it (16px/24px instead of 28px).
         * Arbitrary font sizes carry no line-height, so the two stop fighting.
         */
        'text-[15px] text-foreground/90 sm:text-[16px]',
        isMyanmar.value ? 'leading-[1.95]' : 'leading-[1.75]',
        '[&_h2]:mt-10 [&_h2]:mb-4 [&_h2]:text-2xl [&_h2]:font-extrabold [&_h2]:text-foreground',
        '[&_h3]:mt-8 [&_h3]:mb-3 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-foreground',
        '[&_h4]:mt-6 [&_h4]:mb-2 [&_h4]:text-lg [&_h4]:font-semibold [&_h4]:text-foreground',
        // Myanmar marks extend below the baseline, so the gap between paragraphs
        // has to grow with the script too.
        isMyanmar.value ? '[&_p]:my-5' : '[&_p]:my-4',
        '[&_p]:text-pretty',
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
    ].join(' '),
);
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

                    <h1
                        :class="
                            cn(
                                /**
                                 * Arbitrary sizes, not `text-3xl/4xl/5xl`: the named
                                 * scale also sets a line-height, and because `lg:` is
                                 * emitted after the base utilities, `lg:text-5xl` was
                                 * overriding the `leading-*` below and pinning the
                                 * heading to line-height 1 — which collides every
                                 * stacked Myanmar mark.
                                 */
                                'text-balance text-[30px] font-extrabold sm:text-[36px] lg:text-[48px]',
                                // A Myanmar heading needs room for its stacked marks;
                                // negative tracking also collides them horizontally.
                                isMyanmar ? 'leading-[1.55] tracking-normal' : 'leading-[1.15] tracking-tight',
                            )
                        "
                    >
                        {{ post.title }}
                    </h1>

                    <p
                        v-if="post.excerpt"
                        :class="
                            cn(
                                'mt-5 text-pretty text-muted-foreground',
                                'text-[16px] sm:text-[18px]',
                                isMyanmar ? 'leading-[1.9]' : 'leading-[1.65]',
                            )
                        "
                    >
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
        <div class="page-container pt-10">
            <AppImage
                :src="post.image_url"
                :alt="post.title"
                loading="eager"
                class="aspect-[16/9] w-full rounded-2xl border border-border object-cover shadow-soft"
            />
        </div>

        <!-- ====================================================== Content -->
        <div class="page-container pt-10 sm:pt-14">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_15rem]">
                <div class="min-w-0">
                    <!-- `content_html` is the plain-text body wrapped into <p>s by the
                         model; `content` is the raw source and collapses in v-html, so
                         it is only a last-resort fallback. -->
                    <div
                        data-article-body
                        :class="proseClasses"
                        v-html="post.content_html || post.content"
                    />

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
                        <AppImage
                            :src="item.image_url"
                            :alt="item.title"
                            class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
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
