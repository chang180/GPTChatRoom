<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    label: {
        type: String,
        default: '使用 Google 繼續',
    },
    intent: {
        type: String,
        default: 'login',
    },
});

const page = usePage();
const oauth = computed(() => page.props.googleOAuth ?? { enabled: false, disabledReason: '' });
</script>

<template>
    <div>
        <!-- 分隔線 -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">或</span>
            </div>
        </div>

        <!-- 啟用：導向 Google（整頁跳轉，非 Inertia） -->
        <a
            v-if="oauth.enabled"
            :href="route('auth.google.redirect', { intent })"
            class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
        >
            <i class="fab fa-google text-red-500"></i>
            {{ label }}
        </a>

        <!-- 未啟用（含本機 local）：停用區塊 + 頁面內說明（ADR-007） -->
        <div
            v-else
            class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/40 p-4 text-center"
        >
            <p class="flex items-center justify-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                <i class="fab fa-google"></i>
                Google 登入暫不可用
            </p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                {{ oauth.disabledReason }}
            </p>
        </div>
    </div>
</template>
