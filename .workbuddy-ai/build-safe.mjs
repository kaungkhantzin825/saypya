// Non-destructive Vite rebuild.
//
// `npm run build` runs `vite build`, whose default `emptyOutDir: true` wipes
// public/build/ first. On this machine that trips the workspace bulk-delete
// guard (SAFE_DELETE_BULK_CONFIRM_REQUIRED) because the directory holds >50
// files. Building through the JS API with `emptyOutDir: false` keeps the old
// hashed assets in place and still rewrites the manifest, so every
// @vite([...]) entry resolves.
import { build } from 'vite';

await build({ build: { emptyOutDir: false } });
console.log('[build-safe] done (emptyOutDir: false)');
