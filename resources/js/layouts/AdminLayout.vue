<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    BookOpen,
    ClipboardList,
    ExternalLink,
    FolderTree,
    GraduationCap,
    Images,
    LayoutDashboard,
    Mail,
    Menu,
    Newspaper,
    Settings,
    Star,
    TrendingUp,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { Toaster } from '@/components/ui';
import Sheet from '@/components/ui/Sheet.vue';
import Logo from '@/components/site/Logo.vue';
import UserMenu from '@/components/site/UserMenu.vue';
import { useAuth, useShared } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import { cn } from '@/lib/utils';
import type { Component } from 'vue';

defineProps<{
    title?: string;
    description?: string;
    /** Optional right-aligned actions rendered in the page header. */
    breadcrumb?: { label: string; href?: string }[];
}>();

interface NavItem {
    label: string;
    href: string;
    icon: Component;
    /** Blade route — must be a full page load, not an Inertia visit. */
    external?: boolean;
    badge?: number;
}

/**
 * Legacy rows store a FontAwesome class in `categories.icon`, which the old
 * AdminLTE layout loaded from a CDN. The Inertia app doesn't bundle it, so pull
 * it in lazily — and only for the admin panel, so public pages pay nothing.
 * If it fails to load the icon simply doesn't render; the name beside it does.
 */
const FA_HREF = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';

onMounted(() => {
    if (document.querySelector(`link[href="${FA_HREF}"]`)) return;

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = FA_HREF;
    link.crossOrigin = 'anonymous';
    document.head.appendChild(link);
});

interface NavGroup {
    heading: string;
    items: NavItem[];
}

const { auth } = useShared();
const { user, isAdmin } = useAuth();
const page = usePage();

const mobileOpen = ref(false);

const adminNav: NavGroup[] = [
    {
        heading: 'Overview',
        items: [{ label: 'Dashboard', href: routes.admin.dashboard(), icon: LayoutDashboard }],
    },
    {
        heading: 'Manage',
        items: [
            { label: 'Users', href: routes.admin.users(), icon: Users },
            { label: 'Courses', href: routes.admin.courses(), icon: BookOpen },
            { label: 'Categories', href: routes.admin.categories(), icon: FolderTree },
            { label: 'Enrollments', href: routes.admin.enrollments(), icon: GraduationCap },
            { label: 'Exams', href: routes.admin.exams(), icon: ClipboardList },
            { label: 'Reviews', href: routes.admin.reviews(), icon: Star },
        ],
    },
    {
        heading: 'Content',
        items: [
            { label: 'Blog', href: routes.admin.blog(), icon: Newspaper },
            { label: 'Hero slides', href: routes.admin.heroSlides(), icon: Images },
            { label: 'Messages', href: routes.admin.contactMessages(), icon: Mail },
        ],
    },
    {
        heading: 'System',
        items: [
            { label: 'Reports', href: routes.admin.reports(), icon: BarChart3 },
            { label: 'Settings', href: routes.admin.settings(), icon: Settings },
        ],
    },
];

// The instructor panel is entirely Blade for now, so every item is a full load.
const instructorNav: NavGroup[] = [
    {
        heading: 'Overview',
        items: [{ label: 'Dashboard', href: routes.instructor.dashboard(), icon: LayoutDashboard, external: true }],
    },
    {
        heading: 'Teaching',
        items: [
            { label: 'My courses', href: routes.instructor.courses(), icon: BookOpen, external: true },
            { label: 'Students', href: routes.instructor.students(), icon: Users, external: true },
            { label: 'Exams', href: routes.instructor.exams(), icon: ClipboardList, external: true },
            { label: 'Reviews', href: routes.instructor.reviews(), icon: Star, external: true },
        ],
    },
    {
        heading: 'Insights',
        items: [
            { label: 'Earnings', href: routes.instructor.earnings(), icon: Wallet, external: true },
            { label: 'Analytics', href: routes.instructor.analytics(), icon: TrendingUp, external: true },
        ],
    },
];

const navGroups = computed<NavGroup[]>(() => (isAdmin.value ? adminNav : instructorNav));

const panelHome = computed(() => (isAdmin.value ? routes.admin.dashboard() : routes.instructor.dashboard()));

const currentUrl = computed(() => page.url.split('?')[0]);

/**
 * A nav item is active on its own URL and any nested URL, but `/admin/users`
 * must not light up while on `/admin/users/create`.
 */
function isActive(item: NavItem): boolean {
    const href = item.href;
    if (currentUrl.value === href) return true;
    if (href === panelHome.value) return false;
    return currentUrl.value.startsWith(`${href}/`);
}

const wishlistCount = computed(() => auth.value.wishlistCount ?? 0);
</script>

<template>
    <div class="min-h-screen bg-muted/40">
        <Toaster />

        <!-- Sidebar (desktop) -->
        <aside
            class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-brand-950 text-brand-100 lg:flex"
        >
            <div class="flex h-16 shrink-0 items-center border-b border-white/10 px-5">
                <Link :href="panelHome" aria-label="Panel home">
                    <Logo />
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto scrollbar-slim px-3 py-4" aria-label="Panel">
                <template v-for="group in navGroups" :key="group.heading">
                    <p class="px-3 pb-2 pt-4 text-[10px] font-bold uppercase tracking-[0.14em] text-brand-400 first:pt-0">
                        {{ group.heading }}
                    </p>
                    <ul class="grid grid-cols-1 gap-0.5">
                        <li v-for="item in group.items" :key="item.href">
                            <component
                                :is="item.external ? 'a' : Link"
                                :href="item.href"
                                :class="
                                    cn(
                                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                        isActive(item)
                                            ? 'bg-white/10 text-white'
                                            : 'text-brand-200 hover:bg-white/5 hover:text-white',
                                    )
                                "
                            >
                                <component :is="item.icon" class="size-4 shrink-0" />
                                <span class="flex-1">{{ item.label }}</span>
                                <ExternalLink v-if="item.external" class="size-3 opacity-40" />
                            </component>
                        </li>
                    </ul>
                </template>
            </nav>

            <div class="border-t border-white/10 p-3">
                <div class="flex items-center gap-3 rounded-lg bg-white/5 p-3">
                    <img
                        :src="user?.avatar_url"
                        :alt="user?.name"
                        class="size-9 shrink-0 rounded-full object-cover ring-1 ring-white/20"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ user?.name }}</p>
                        <p class="truncate text-xs capitalize text-brand-300">{{ user?.role }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main column -->
        <div class="lg:pl-64">
            <header
                class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-border bg-background/85 px-4 backdrop-blur-lg sm:px-6"
            >
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:bg-accent lg:hidden"
                    aria-label="Open menu"
                    @click="mobileOpen = true"
                >
                    <Menu class="size-4" />
                </button>

                <Link :href="panelHome" class="lg:hidden" aria-label="Panel home">
                    <Logo compact />
                </Link>

                <div class="ml-auto flex items-center gap-2">
                    <a
                        :href="routes.home()"
                        target="_blank"
                        rel="noopener"
                        class="hidden items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground sm:inline-flex"
                    >
                        <ExternalLink class="size-3.5" />
                        View site
                    </a>
                    <UserMenu />
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <nav v-if="breadcrumb?.length" class="mb-3 flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground" aria-label="Breadcrumb">
                    <template v-for="(crumb, index) in breadcrumb" :key="crumb.label">
                        <span v-if="index > 0" class="text-muted-foreground/50">/</span>
                        <Link v-if="crumb.href" :href="crumb.href" class="hover:text-foreground hover:underline">
                            {{ crumb.label }}
                        </Link>
                        <span v-else class="font-medium text-foreground">{{ crumb.label }}</span>
                    </template>
                </nav>

                <div v-if="title" class="mb-6">
                    <h1 class="text-2xl font-extrabold tracking-tight">{{ title }}</h1>
                    <p v-if="description" class="mt-1 text-sm text-muted-foreground">{{ description }}</p>
                </div>

                <slot />
            </main>
        </div>

        <!-- Mobile navigation -->
        <Sheet v-model:open="mobileOpen" side="left" title="Menu">
            <nav class="grid grid-cols-1 gap-1" aria-label="Mobile panel">
                <template v-for="group in navGroups" :key="group.heading">
                    <p class="px-3 pb-1 pt-3 text-[10px] font-bold uppercase tracking-[0.14em] text-muted-foreground first:pt-0">
                        {{ group.heading }}
                    </p>
                    <component
                        :is="item.external ? 'a' : Link"
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        :class="
                            cn(
                                'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                                isActive(item)
                                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                    : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                            )
                        "
                        @click="mobileOpen = false"
                    >
                        <component :is="item.icon" class="size-4 shrink-0" />
                        <span class="flex-1">{{ item.label }}</span>
                        <ExternalLink v-if="item.external" class="size-3 opacity-40" />
                    </component>
                </template>
            </nav>
        </Sheet>
    </div>
</template>
