import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.tsx',
        'resources/css/filament/admin/theme.css'
      ],
      refresh: [
        'resources/views/**',
        'resources/js/**',
        'app/Filament/**',
        'packages/box/nicole/core/resources/views/**/*.blade.php',
        'packages/box/nicole/core/src/**/*.php',
        'packages/box/valerie/industry-stone/resources/views/**/*.blade.php',
        'packages/box/valerie/industry-stone/src/**/*.php',
      ],
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
