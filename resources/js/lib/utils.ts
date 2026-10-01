import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Merge Tailwind classes, resolving conflicts so later classes win.
 * Used by every shadcn-vue component.
 */
export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}

const MYANMAR_NUMERALS: Record<string, string> = {
    '0': '၀',
    '1': '၁',
    '2': '၂',
    '3': '၃',
    '4': '၄',
    '5': '၅',
    '6': '၆',
    '7': '၇',
    '8': '၈',
    '9': '၉',
};

/** Format an amount in Myanmar Kyat, e.g. 25000 -> "25,000 Ks". Mirrors CurrencyHelper::formatMMK. */
export function formatMMK(amount: number | string | null | undefined, freeLabel = 'Free'): string {
    const value = Number(amount ?? 0);
    if (!value) return freeLabel;
    return `${value.toLocaleString('en-US')} Ks`;
}

/** Format an amount using Myanmar numerals, e.g. 25000 -> "၂၅,၀၀၀ ကျပ်". */
export function formatMMKMyanmar(amount: number | string | null | undefined, freeLabel = 'အခမဲ့'): string {
    const value = Number(amount ?? 0);
    if (!value) return freeLabel;
    const formatted = value.toLocaleString('en-US');
    return `${formatted.replace(/[0-9]/g, (d) => MYANMAR_NUMERALS[d])} ကျပ်`;
}

/** Human-readable duration from a number of minutes. */
export function formatDuration(minutes: number | null | undefined): string {
    const total = Number(minutes ?? 0);
    if (!total) return '—';
    const hours = Math.floor(total / 60);
    const mins = Math.round(total % 60);
    if (!hours) return `${mins}m`;
    if (!mins) return `${hours}h`;
    return `${hours}h ${mins}m`;
}

/** Format a seconds value as mm:ss (or h:mm:ss). */
export function formatClock(seconds: number): string {
    const s = Math.max(0, Math.floor(seconds));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    const pad = (n: number) => String(n).padStart(2, '0');
    return h ? `${h}:${pad(m)}:${pad(sec)}` : `${pad(m)}:${pad(sec)}`;
}

/** Format an ISO date string for display. */
export function formatDate(value: string | null | undefined, locale = 'en-GB'): string {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(locale, { year: 'numeric', month: 'short', day: 'numeric' });
}

/** "3 days ago" style relative time. */
export function timeAgo(value: string | null | undefined): string {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    const diff = Date.now() - date.getTime();
    const minutes = Math.round(diff / 60000);
    if (minutes < 1) return 'just now';
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.round(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.round(hours / 24);
    if (days < 30) return `${days}d ago`;
    const months = Math.round(days / 30);
    if (months < 12) return `${months}mo ago`;
    return `${Math.round(months / 12)}y ago`;
}

/** Initials for avatar fallbacks, e.g. "Aung Ko" -> "AK". */
export function initials(name: string | null | undefined): string {
    if (!name) return '?';
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

/** Strip HTML tags for use in meta descriptions / excerpts. */
export function stripHtml(html: string | null | undefined, max = 160): string {
    if (!html) return '';
    const text = html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    return text.length > max ? `${text.slice(0, max - 1)}…` : text;
}

/** Percentage clamped to 0-100. */
export function clampPercent(value: number | string | null | undefined): number {
    const n = Number(value ?? 0);
    if (Number.isNaN(n)) return 0;
    return Math.min(100, Math.max(0, Math.round(n)));
}
