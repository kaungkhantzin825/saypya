import type { Component } from 'vue';

/**
 * Prop-shape types for the UI primitives.
 *
 * These live outside the SFCs on purpose: `<script setup>` cannot contain ES
 * module exports, so types shared with consumers belong in a plain `.ts` module.
 */

export interface RadioOption {
    value: string;
    label: string;
    description?: string;
}

export interface SelectOption {
    value: string;
    label: string;
    disabled?: boolean;
}

export interface MenuItem {
    label?: string;
    href?: string;
    /** Force a full page load instead of an Inertia visit (e.g. Blade-only panels). */
    external?: boolean;
    icon?: Component;
    /** Called when the item is chosen (ignored when `href` is set). */
    onSelect?: () => void;
    destructive?: boolean;
    disabled?: boolean;
    /** Render a divider instead of an item. */
    separator?: boolean;
    /** Render a non-interactive section heading. */
    heading?: boolean;
}

export interface TabItem {
    value: string;
    label: string;
    icon?: Component;
    badge?: string | number;
    disabled?: boolean;
}

export interface AccordionEntry {
    value: string;
    title: string;
    /** Small text on the right of the header, e.g. lesson count. */
    meta?: string;
    disabled?: boolean;
}

export type ToastVariant = 'default' | 'success' | 'error' | 'warning' | 'info';

export interface Toast {
    id: number;
    title?: string;
    description?: string;
    variant: ToastVariant;
    duration: number;
}
