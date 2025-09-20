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
        // manifest: true, // 添加这个配置项来生成manifest文件
        // outDir: 'public/build', // 生成文件的目录
        rollupOptions: {
            input: {
                app: 'resources/js/app.js', // 入口文件
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            ziggy: path.resolve('vendor/tightenco/ziggy/dist/vue'), // 添加 ziggy 的別名
        },
    },
    server: {
        watch: {
            usePolling: true, // 使用轮询机制进行文件监视
            interval: 1000, // 轮询间隔，单位为毫秒
        },
        host: 'localhost', // 限制为 localhost
        port: 5173,
        hmr: {
            host: 'localhost', // HMR 使用 localhost
            port: 5173,
            protocol: 'ws', // 使用 WebSocket 而不是 WSS
            clientPort: 5173, // 明確指定客戶端端口
        },
        https: false, // 在 Herd 环境中禁用 HTTPS
    },
});
