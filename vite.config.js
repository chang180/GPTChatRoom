import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return;
                    }

                    if (id.includes('ably') || id.includes('@ably')) {
                        return 'vendor-ably';
                    }

                    if (id.includes('marked')) {
                        return 'vendor-marked';
                    }

                    if (
                        id.includes('vue')
                        || id.includes('@vue')
                        || id.includes('@inertiajs')
                    ) {
                        return 'vendor-vue';
                    }

                    if (id.includes('ziggy')) {
                        return 'vendor-ziggy';
                    }

                    return 'vendor';
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            ziggy: path.resolve('vendor/tightenco/ziggy/dist/vue'),
        },
    },
    server: {
        watch: {
            usePolling: true,
            interval: 1000,
        },
        host: 'localhost',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
            protocol: 'ws',
            clientPort: 5173,
        },
        https: false,
    },
});
