import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@app': path.resolve(__dirname, 'resources/js/app'),
            '@pages': path.resolve(__dirname, 'resources/js/pages'),
            '@widgets': path.resolve(__dirname, 'resources/js/widgets'),
            '@features': path.resolve(__dirname, 'resources/js/features'),
            '@entities': path.resolve(__dirname, 'resources/js/entities'),
            '@shared': path.resolve(__dirname, 'resources/js/shared'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});