import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/admin.js',
                'resources/js/v6.js',
                'resources/js/home-v6.js',
            ],
            refresh: true,
        }),
    ],
});
