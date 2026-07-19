import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// NOTE : plugin Tailwind retiré — l'application est basée sur Bootstrap 5
// (bundlé dans resources/css/app.css). Conserver Tailwind injecterait un reset
// concurrent de celui de Bootstrap.

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
