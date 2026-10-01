<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    Compass,
    GraduationCap,
    Heart,
    LayoutDashboard,
    Menu,
    TrendingUp,
    User as UserIcon,
} from 'lucide-vue-next';
import { Badge, Toaster } from '@/components/ui';
import Sheet from '@/components/ui/Sheet.vue';
import Logo from '@/components/site/Logo.vue';
import LocaleSwitcher from '@/components/site/LocaleSwitcher.vue';
import UserMenu from '@/components/site/UserMenu.vue';
import { useAuth, useShared } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import { cn } from '@/lib/utils';

defineProps<{
    title?: string;
    description?: string;
}>();

const { auth } = useShared();
const { user } = useAuth();
const page = usePage();

const mobileOpen = ref(false);

const navItems = [
    { label: 'Dashboard', href: routes.dashboard(), icon: LayoutDashboard },
    { label: 'My courses', href: routes.myCourses(), icon: BookOpen },
    { label: 'Wishlist', href: routes.myWishlist(), icon: Heart },
    { label: 'My exams', href: routes.myExams(), icon: GraduationCap },
    { label: 'Certificates', href: routes.myCertificates(), icon: Award },
    { label: 'Progress', href: routes.myProgress(), icon: TrendingUp },
];

const accountItems = [
    { label: 'Profile', href: routes.profile(), icon: UserIcon },
    { label: 'Browse courses', href: routes.courses(), icon: Compass },
];

const currentUrl = computed(() => page.url.split('?')[0]);

function isActive(href: string): boolean {
    return currentUrl.value === href || currentUrl.value.startsWith(`${href}/`);
}

const wishlistCount = computed(() => auth.value.wishlistCount ?? 0);
</script>

<template>
    <div class="min-h-screen bg-muted/40">
        <Toaster />

        <!-- Sidebar (desktop) -->
        <aside
            class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-border bg-background lg:flex"
        >
            <div class="flex h-16 shrink-0 items-center border-b border-border px-5">
                <Link :href="routes.home()" aria-label="Sanpya Online Academy home">
                    <Logo />
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto scrollbar-slim p-3" aria-label="Student">
                <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-muted-foreground">
                    Learning
                </p>
                <ul class="grid gap-0.5">
                    <li v-for="item in navItems" :key="item.href">
                        <Link
                            :href="item.href"
                            :class="
                                cn(
                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                    isActive(item.href)
                                        ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                        : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                )
                            "
                        >
                            <component :is="item.icon" class="size-4 shrink-0" />
                            <span class="flex-1">{{ item.label }}</span>
                            <Badge v-if="item.label === 'Wishlist' && wishlistCount" variant="muted">
                                {{ wishlistCount }}
                            </Badge>
                        </Link>
                    </li>
                </ul>

                <p class="mt-6 px-3 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-muted-foreground">
                    Account
                </p>
                <ul class="grid gap-0.5">
                    <li v-for="item in accountItems" :key="item.href">
                        <Link
                            :href="item.href"
                            :class="
                                cn(
                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                    isActive(item.href)
                                        ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                        : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                )
                            "
                        >
                            <component :is="item.icon" class="size-4 shrink-0" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-border p-3">
                <div class="flex items-center gap-3 rounded-lg bg-muted/60 p-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ user?.name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ user?.email }}</p>
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

                <Link :href="routes.home()" class="lg:hidden" aria-label="Home">
                    <Logo compact />
                </Link>

                <div class="ml-auto flex items-center gap-2">
                    <div class="hidden sm:block">
                        <LocaleSwitcher />
                    </div>
                    <UserMenu />
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <div v-if="title" class="mb-6">
                    <h1 class="text-2xl font-extrabold tracking-tight">{{ title }}</h1>
                    <p v-if="description" class="mt-1 text-sm text-muted-foreground">{{ description }}</p>
                </div>

                <slot />
            </main>
        </div>

        <!-- Mobile navigation -->
        <Sheet v-model:open="mobileOpen" side="left" title="Menu">
            <nav class="grid gap-1" aria-label="Mobile">
                <Link
                    v-for="item in [...navItems, ...accountItems]"
                    :key="item.href"
                    :href="item.href"
                    :class="
                        cn(
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                            isActive(item.href)
                                ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                        )
                    "
                    @click="mobileOpen = false"
                >
                    <component :is="item.icon" class="size-4 shrink-0" />
                    <span class="flex-1">{{ item.label }}</span>
                    <Badge v-if="item.label === 'Wishlist' && wishlistCount" variant="muted">
                        {{ wishlistCount }}
                    </Badge>
                </Link>
            </nav>

            <div class="mt-6 border-t border-border pt-4">
                <LocaleSwitcher />
            </div>
        </Sheet>
    </div>
</template>
