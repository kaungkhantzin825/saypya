<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    BookOpen,
    GraduationCap,
    Heart,
    LayoutDashboard,
    LogOut,
    Shield,
    User as UserIcon,
} from 'lucide-vue-next';
import Avatar from '@/components/ui/Avatar.vue';
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import { useAuth } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import type { MenuItem } from '@/types/ui';

const { user, isAdmin, isLecturer } = useAuth();

function logout() {
    router.post(routes.logout());
}

const items = computed<MenuItem[]>(() => {
    const list: MenuItem[] = [];

    // The admin and instructor panels are still Blade, so link them as full loads.
    if (isAdmin.value) {
        list.push({ label: 'Admin panel', href: '/admin/dashboard', external: true, icon: Shield });
    }
    if (isLecturer.value) {
        list.push({
            label: 'Instructor panel',
            href: '/instructor/dashboard',
            external: true,
            icon: GraduationCap,
        });
    }

    list.push({ label: 'Dashboard', href: routes.dashboard(), icon: LayoutDashboard });
    list.push({ label: 'My courses', href: routes.myCourses(), icon: BookOpen });
    list.push({ label: 'Wishlist', href: routes.myWishlist(), icon: Heart });
    list.push({ label: 'My exams', href: routes.myExams(), icon: GraduationCap });
    list.push({ separator: true });
    list.push({ label: 'Profile', href: routes.profile(), icon: UserIcon });
    list.push({ label: 'Sign out', onSelect: logout, icon: LogOut, destructive: true });

    return list;
});
</script>

<template>
    <DropdownMenu :items="items" align="end">
        <template #trigger>
            <button
                type="button"
                class="flex items-center gap-2 rounded-full p-0.5 pr-2 transition-colors hover:bg-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/30"
                aria-label="Account menu"
            >
                <Avatar :src="user?.avatar_url" :name="user?.name" size="sm" />
                <span class="hidden max-w-[8rem] truncate text-sm font-medium sm:inline">
                    {{ user?.name }}
                </span>
            </button>
        </template>
    </DropdownMenu>
</template>
