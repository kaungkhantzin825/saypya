import type { Component } from 'vue';

/**
 * Inertia's Vue adapter supports a `layout` component option for persistent
 * layouts (`defineOptions({ layout: PublicLayout })`). Vue's own types don't
 * know about it, so declare it here.
 *
 * Use `Component` (not `DefineComponent`): vue-tsc wraps an SFC imported into a
 * `<script setup>` block in `__VLS_WithSlots<...>`, which is assignable to
 * `Component` but *not* to `DefineComponent`.
 */
declare module 'vue' {
    interface ComponentCustomOptions {
        layout?: Component | Component[];
    }
}

export {};
