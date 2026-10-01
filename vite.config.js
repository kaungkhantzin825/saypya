import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Legacy Blade entries — still used by the admin (AdminLTE) and
                // instructor panels, which have NOT been migrated to Inertia yet.
                'resources/css/app.css',
                'resources/css/adminlte.min.css',
                'resources/js/app.js',
                // Inertia + Vue entry (public site + student area).
                'resources/js/inertia.ts',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // Let Vite resolve asset URLs so @/assets imports work in SFCs.
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
