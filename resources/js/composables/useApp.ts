import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { formatMMK, formatMMKMyanmar } from '@/lib/utils';
import type { AppContext, AuthContext, SharedPageProps, User } from '@/types';

/** Typed access to the props shared by HandleInertiaRequests. */
export function useShared() {
    const page = usePage<SharedPageProps>();

    const app = computed<AppContext>(
        () => page.props.app ?? { name: '', locale: 'en', locales: {}, registrationEnabled: true },
    );
    const auth = computed<AuthContext>(() => page.props.auth ?? { user: null, wishlistCount: 0 });

    return { page, app, auth };
}

/** Current user + convenience role checks. */
export function useAuth() {
    const { auth } = useShared();

    const user = computed<User | null>(() => auth.value.user ?? null);
    const isAuthenticated = computed(() => user.value !== null);

    return {
        user,
        isAuthenticated,
        isStudent: computed(() => user.value?.role === 'student'),
        isLecturer: computed(() => user.value?.role === 'lecturer'),
        isAdmin: computed(() => user.value?.role === 'admin'),
    };
}

/** Locale-aware display helpers (Myanmar script + MMK currency). */
export function useFormatting() {
    const { app } = useShared();

    const locale = computed(() => app.value.locale ?? 'en');
    const isMyanmar = computed(() => locale.value === 'my');
    const myanmarClass = computed(() => (isMyanmar.value ? 'myanmar-text' : ''));

    /** Prices render with Myanmar numerals when the site is in Myanmar mode. */
    function formatPrice(amount: number | string | null | undefined): string {
        return isMyanmar.value ? formatMMKMyanmar(amount) : formatMMK(amount);
    }

    return { locale, isMyanmar, myanmarClass, formatPrice };
}
