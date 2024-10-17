import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
    ],
    // Move mode and minify outside of plugins
    define: {
        'process.env.NODE_ENV': JSON.stringify('development'), // Ensure development mode
    },
    build: {
        minify: false, // Disable minification for debugging
    },
});
