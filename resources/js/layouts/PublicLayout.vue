<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Facebook, Mail, MapPin, Menu, Phone, Search, Youtube } from 'lucide-vue-next';
import { Button, Toaster } from '@/components/ui';
import Sheet from '@/components/ui/Sheet.vue';
import Logo from '@/components/site/Logo.vue';
import LocaleSwitcher from '@/components/site/LocaleSwitcher.vue';
import UserMenu from '@/components/site/UserMenu.vue';
import { useAuth, useShared } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import { cn } from '@/lib/utils';

const { app } = useShared();
const { isAuthenticated } = useAuth();
const page = usePage();

const mobileOpen = ref(false);
const search = ref('');

const navItems = [
    { label: 'Home', href: routes.home() },
    { label: 'Courses', href: routes.courses() },
    { label: 'Categories', href: routes.categories() },
    { label: 'Blog', href: routes.blog() },
    { label: 'About', href: routes.about() },
    { label: 'Contact', href: routes.contact() },
];

const currentUrl = computed(() => page.url.split('?')[0]);

function isActive(href: string): boolean {
    const url = currentUrl.value;
    if (href === '/') return url === '/';
    return url === href || url.startsWith(`${href}/`);
}

function submitSearch() {
    const term = search.value.trim();
    router.get(routes.search(term || undefined), {}, { preserveState: false });
    mobileOpen.value = false;
}

const footerLinks = [
    {
        heading: 'Learn',
        links: [
            { label: 'All courses', href: routes.courses() },
            { label: 'Categories', href: routes.categories() },
            { label: 'Blog', href: routes.blog() },
            { label: 'Search', href: routes.search() },
        ],
    },
    {
        heading: 'Company',
        links: [
            { label: 'About us', href: routes.about() },
            { label: 'Our team', href: routes.team() },
            { label: 'Partners', href: routes.partners() },
            { label: 'Contact', href: routes.contact() },
        ],
    },
    {
        heading: 'Support',
        links: [
            { label: 'Help centre', href: routes.help() },
            { label: 'Privacy policy', href: routes.privacy() },
            { label: 'Terms of service', href: routes.terms() },
        ],
    },
];

const year = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <Toaster />

        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-border bg-background/85 backdrop-blur-lg">
            <div class="page-container flex h-16 items-center gap-4">
                <Link :href="routes.home()" class="shrink-0" aria-label="Sanpya Online Academy home">
                    <Logo />
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="
                            cn(
                                'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                isActive(item.href)
                                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                    : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                            )
                        "
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <!-- Desktop search -->
                <form class="ml-auto hidden max-w-xs flex-1 items-center md:flex" @submit.prevent="submitSearch">
                    <div class="relative w-full">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="search"
                            :placeholder="'Search courses…'"
                            aria-label="Search courses"
                            class="h-9 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/25"
                        />
                    </div>
                </form>

                <div class="ml-auto flex items-center gap-2 md:ml-0">
                    <div class="hidden sm:block">
                        <LocaleSwitcher />
                    </div>

                    <template v-if="isAuthenticated">
                        <UserMenu />
                    </template>
                    <template v-else>
                        <Button :href="routes.login()" variant="ghost" size="sm" class="hidden sm:inline-flex">
                            Sign in
                        </Button>
                        <Button :href="routes.register()" variant="brand" size="sm">Get started</Button>
                    </template>

                    <!-- Mobile menu trigger -->
                    <button
                        type="button"
                        class="inline-flex size-9 items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:bg-accent lg:hidden"
                        aria-label="Open menu"
                        @click="mobileOpen = true"
                    >
                        <Menu class="size-4" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile navigation -->
        <Sheet v-model:open="mobileOpen" side="right" title="Menu">
            <div class="grid gap-6">
                <form class="flex items-center gap-2" @submit.prevent="submitSearch">
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Search courses…"
                            aria-label="Search courses"
                            class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/25"
                        />
                    </div>
                    <Button type="submit" size="icon" aria-label="Search">
                        <Search />
                    </Button>
                </form>

                <nav class="grid gap-1" aria-label="Mobile">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="
                            cn(
                                'rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                                isActive(item.href)
                                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                    : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                            )
                        "
                        @click="mobileOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="border-t border-border pt-4">
                    <LocaleSwitcher />
                </div>

                <div v-if="!isAuthenticated" class="grid gap-2 border-t border-border pt-4">
                    <Button :href="routes.login()" variant="outline" block @click="mobileOpen = false">
                        Sign in
                    </Button>
                    <Button :href="routes.register()" variant="brand" block @click="mobileOpen = false">
                        Get started
                    </Button>
                </div>
            </div>
        </Sheet>

        <!-- Page content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-slate-950 text-slate-300">
            <div class="page-container grid gap-10 py-14 lg:grid-cols-[1.4fr_repeat(3,1fr)]">
                <div class="grid gap-4">
                    <Logo variant="light" />
                    <p class="max-w-sm text-sm leading-relaxed text-slate-400">
                        {{ app.name }} — learn from expert instructors, at your own pace, in English and Myanmar.
                    </p>

                    <ul class="mt-1 grid gap-2 text-sm text-slate-400">
                        <li class="flex items-center gap-2">
                            <Mail class="size-4 shrink-0" />
                            <a href="mailto:webdeveloperkkz@gmail.com" class="hover:text-white">
                                webdeveloperkkz@gmail.com
                            </a>
                        </li>
                        <li class="flex items-center gap-2">
                            <Phone class="size-4 shrink-0" />
                            <span>Viber: +95 9695238273</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <MapPin class="size-4 shrink-0" />
                            <span>Yangon, Myanmar</span>
                        </li>
                    </ul>

                    <div class="flex items-center gap-2 pt-1">
                        <a
                            href="#"
                            class="inline-flex size-9 items-center justify-center rounded-lg bg-white/5 transition-colors hover:bg-white/10 hover:text-white"
                            aria-label="Facebook"
                        >
                            <Facebook class="size-4" />
                        </a>
                        <a
                            href="#"
                            class="inline-flex size-9 items-center justify-center rounded-lg bg-white/5 transition-colors hover:bg-white/10 hover:text-white"
                            aria-label="YouTube"
                        >
                            <Youtube class="size-4" />
                        </a>
                    </div>
                </div>

                <div v-for="group in footerLinks" :key="group.heading" class="grid content-start gap-3">
                    <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-white">{{ group.heading }}</h3>
                    <ul class="grid gap-2 text-sm">
                        <li v-for="link in group.links" :key="link.label">
                            <Link :href="link.href" class="text-slate-400 transition-colors hover:text-white">
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10">
                <div class="page-container flex flex-col items-center justify-between gap-3 py-6 text-xs text-slate-500 sm:flex-row">
                    <p>© {{ year }} {{ app.name }}. All rights reserved.</p>
                    <p>Built with Laravel, Inertia &amp; Vue.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
