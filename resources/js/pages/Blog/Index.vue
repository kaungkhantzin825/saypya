<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, Clock, Newspaper } from 'lucide-vue-next';
import { AppImage, Avatar, Badge, Button, EmptyState, Pagination } from '@/components/ui';
import PageHero from '@/components/site/PageHero.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { cn, formatDate, hasMyanmar } from '@/lib/utils';
import { routes } from '@/lib/routes';
import type { BlogPost, Paginated } from '@/types';

defineOptions({ layout: PublicLayout });

const props = defineProps<{ posts: Paginated<BlogPost> }>();

const featured = computed<BlogPost | null>(() => props.posts.data[0] ?? null);
const rest = computed<BlogPost[]>(() => props.posts.data.slice(1));
const isEmpty = computed(() => props.posts.data.length === 0);

/**
 * Myanmar stacks diacritics above *and* below the base character, so a card title
 * needs far more leading than the `leading-snug`/`leading-tight` that suits Latin.
 * Line-height can't be set per character, so each post is tested individually.
 */
const isMyanmarPost = (post: BlogPost | null) =>
    !!post && (hasMyanmar(post.title) || hasMyanmar(post.excerpt));

const authorName = (post: BlogPost) => post.author?.name ?? 'Sanpya Academy';
const readingTime = (post: BlogPost) => `${post.reading_time ?? 1} min read`;
</script>

<template>
    <PageHero
        eyebrow="Blog"
        :icon="Newspaper"
        title="Insights, guides and stories from Sanpya"
        subtitle="Study tips, career advice and product updates — written for learners in Myanmar and beyond."
        image="/images/page-headers/blog.webp"
    />

    <!-- ========================================================== Empty -->
    <section v-if="isEmpty" class="page-container py-16 sm:py-20">
        <EmptyState
            :icon="Newspaper"
            title="No articles yet"
            description="We are busy writing our first posts. Check back soon — or start learning straight away."
        >
            <Button :href="routes.courses()" variant="brand">
                Browse courses
                <ArrowRight />
            </Button>
        </EmptyState>
    </section>

    <template v-else>
        <!-- ===================================================== Featured -->
        <section v-if="featured" class="page-container py-12 sm:py-16">
            <Link
                :href="routes.blogPost(featured.slug)"
                class="group grid overflow-hidden rounded-2xl border border-border bg-card shadow-soft transition-all hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800 lg:grid-cols-2"
            >
                <div class="relative aspect-[16/10] overflow-hidden lg:aspect-auto">
                    <AppImage
                        :src="featured.image_url"
                        :alt="featured.title"
                        class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="eager"
                    />
                    <Badge variant="brand" class="absolute left-4 top-4 shadow-soft">Latest</Badge>
                </div>

                <div class="flex flex-col justify-center gap-4 p-6 sm:p-9">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                        <Badge v-if="featured.category" variant="muted">{{ featured.category }}</Badge>
                        <span class="inline-flex items-center gap-1.5">
                            <CalendarDays class="size-3.5" />
                            {{ formatDate(featured.published_at) }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <Clock class="size-3.5" />
                            {{ readingTime(featured) }}
                        </span>
                    </div>

                    <h2
                        :class="
                            cn(
                                'text-balance font-extrabold transition-colors group-hover:text-brand-700 dark:group-hover:text-brand-400',
                                // Arbitrary sizes so `sm:` cannot smuggle in a
                                // line-height and clobber the `leading-*` below.
                                'text-[24px] sm:text-[30px]',
                                isMyanmarPost(featured)
                                    ? 'leading-[1.6] tracking-normal'
                                    : 'leading-[1.15] tracking-tight',
                            )
                        "
                    >
                        {{ featured.title }}
                    </h2>

                    <p
                        v-if="featured.excerpt"
                        :class="
                            cn(
                                'line-clamp-3 text-pretty text-muted-foreground',
                                'text-[14px]',
                                isMyanmarPost(featured) ? 'leading-[1.85]' : 'leading-[1.65]',
                            )
                        "
                    >
                        {{ featured.excerpt }}
                    </p>

                    <div class="mt-1 flex items-center gap-3">
                        <Avatar :src="featured.author?.avatar_url" :name="authorName(featured)" size="sm" />
                        <span class="text-sm font-medium">{{ authorName(featured) }}</span>
                    </div>

                    <span class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 dark:text-brand-400">
                        Read article
                        <ArrowRight class="size-4 transition-transform group-hover:translate-x-1" />
                    </span>
                </div>
            </Link>
        </section>

        <!-- ========================================================= Grid -->
        <section v-if="rest.length" class="page-container pb-16 sm:pb-20">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="post in rest"
                    :key="post.id"
                    :href="routes.blogPost(post.slug)"
                    class="group flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-soft transition-all hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift dark:hover:border-brand-800"
                >
                    <div class="relative aspect-[16/9] overflow-hidden">
                        <AppImage
                            :src="post.image_url"
                            :alt="post.title"
                            class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    </div>

                    <div class="flex flex-1 flex-col gap-3 p-5">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                            <Badge v-if="post.category" variant="muted">{{ post.category }}</Badge>
                            <span>{{ formatDate(post.published_at) }}</span>
                        </div>

                        <h3
                            :class="
                                cn(
                                    'line-clamp-2 font-bold transition-colors group-hover:text-brand-700 dark:group-hover:text-brand-400',
                                    'text-[18px]',
                                    isMyanmarPost(post) ? 'leading-[1.6]' : 'leading-[1.375]',
                                )
                            "
                        >
                            {{ post.title }}
                        </h3>

                        <p
                            v-if="post.excerpt"
                            :class="
                                cn(
                                    'line-clamp-3 text-muted-foreground',
                                    'text-[14px]',
                                    isMyanmarPost(post) ? 'leading-[1.85]' : 'leading-[1.65]',
                                )
                            "
                        >
                            {{ post.excerpt }}
                        </p>

                        <div class="mt-auto flex items-center gap-3 border-t border-border pt-4">
                            <Avatar :src="post.author?.avatar_url" :name="authorName(post)" size="xs" />
                            <span class="truncate text-xs font-medium">{{ authorName(post) }}</span>
                            <span class="ml-auto inline-flex shrink-0 items-center gap-1.5 text-xs text-muted-foreground">
                                <Clock class="size-3.5" />
                                {{ post.reading_time ?? 1 }} min
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <Pagination
                v-if="props.posts.last_page > 1"
                class="mt-12"
                :links="props.posts.links"
                :from="props.posts.from"
                :to="props.posts.to"
                :total="props.posts.total"
            />
        </section>
    </template>
</template>
