import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

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
        manifest: true, // 添加这个配置项来生成manifest文件
        outDir: 'public/build', // 生成文件的目录
        rollupOptions: {
            input: {
                app: 'resources/js/app.js', // 入口文件
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        watch: {
            usePolling: true, // 使用轮询机制进行文件监视
            interval: 1000, // 轮询间隔，单位为毫秒
        },
        host: true, // 使服务器可在局域网内访问
        hmr: {
            host: 'localhost', // 配置热模块替换的主机
        },
    },
});
