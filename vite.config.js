import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        // Generate unique filenames with hash for cache busting
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        },
        // Ensure source maps for debugging
        sourcemap: true,
        // Optimize chunks
        chunkSizeWarningLimit: 1000
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: {
            origin: '*',
        },
        hmr: {
            // Para desenvolvimento local
            host: '192.168.5.118',
            // Para Docker
            port: 5173,
            clientPort: 5173,
            protocol: 'ws',
        },
        watch: {
            usePolling: true,
        },
        // Anti-cache headers for development
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        }
    },
});
