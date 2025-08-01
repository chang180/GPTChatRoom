<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { route } from 'ziggy-js';
</script>

<template>
    <Head title="Welcome to GPT Chat Room Dashboard" />

    <div class="p-6 bg-gradient-to-b from-blue-50 to-blue-100 border-b border-blue-200 lg:p-8">
        <div class="flex justify-center">
            <ApplicationLogo class="w-20 h-20 text-blue-600" />
        </div>
        <h1 class="mt-4 text-3xl font-bold text-center text-blue-800">
            GPT Chat Room 控制中心
        </h1>

        <p class="mt-4 text-lg leading-relaxed text-center text-gray-700">
            您的 AI 聊天助手已準備就緒，隨時可以回答您的問題、提供建議或進行創意討論。
            <br class="hidden md:block">
            使用下方卡片快速訪問主要功能。
        </p>

        <div class="flex justify-center mt-6">
            <Link :href="route('chat.index')" class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                立即開始聊天
            </Link>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 p-6 bg-white md:grid-cols-2 lg:grid-cols-3 lg:gap-8 lg:p-8">
        <!-- 快速聊天卡片 -->
        <div class="p-6 bg-blue-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-blue-100">
            <div class="flex items-center">
                <div class="p-3 bg-blue-600 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-blue-800 ms-3">
                    聊天室
                </h2>
            </div>

            <p class="mt-4 text-gray-600">
                進入我們的聊天室，與 GPT-4o-mini 智能助手即時對話。最新設計的聊天界面讓您無需滾動即可查看最新訊息。
            </p>

            <div class="mt-6 flex justify-between items-center">
                <Link :href="route('chat.index')" class="inline-flex items-center font-semibold text-blue-600 hover:text-blue-800">
                    進入聊天室
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-blue-600">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </Link>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">推薦</span>
            </div>
        </div>

        <!-- 個人資料卡片 -->
        <div class="p-6 bg-blue-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-blue-100">
            <div class="flex items-center">
                <div class="p-3 bg-purple-600 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-purple-800 ms-3">
                    個人資料
                </h2>
            </div>

            <p class="mt-4 text-gray-600">
                管理您的個人資料設定、密碼和安全選項。啟用雙因子認證以增強帳戶安全性，確保您的聊天體驗更加安全。
            </p>

            <div class="mt-6">
                <Link :href="route('profile.show')" class="inline-flex items-center font-semibold text-purple-600 hover:text-purple-800">
                    管理資料
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-purple-600">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- AI 功能卡片 -->
        <div class="p-6 bg-blue-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-blue-100">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-600 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-indigo-800 ms-3">
                    AI 功能
                </h2>
            </div>

            <p class="mt-4 text-gray-600">
                我們的 GPT-4o-mini 模型可以回答問題、生成內容、提供建議，並支援 Markdown 格式回應，讓您獲得結構化的高質量內容。
            </p>

            <div class="mt-6">
                <Link :href="route('chat.index')" class="inline-flex items-center font-semibold text-indigo-600 hover:text-indigo-800">
                    體驗 AI 功能
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-indigo-600">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- 聊天界面特色卡片 -->
        <div class="p-6 bg-blue-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-blue-100">
            <div class="flex items-center">
                <div class="p-3 bg-teal-600 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-teal-800 ms-3">
                    聊天界面特色
                </h2>
            </div>

            <p class="mt-4 text-gray-600">
                我們的聊天界面採用反向訊息排序，最新訊息顯示在頂部，讓您無需滾動即可查看最新內容，提供更直觀的使用體驗。
            </p>

            <div class="mt-6">
                <Link :href="route('chat.index')" class="inline-flex items-center font-semibold text-teal-600 hover:text-teal-800">
                    體驗新界面
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-teal-600">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- 技術堆疊卡片 -->
        <div class="p-6 bg-blue-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-blue-100">
            <div class="flex items-center">
                <div class="p-3 bg-gray-700 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 ms-3">
                    技術堆疊
                </h2>
            </div>

            <p class="mt-4 text-gray-600">
                本應用採用 Laravel 12 + Vue.js 3 + Inertia.js 的全端解決方案，結合 Tailwind CSS 和 OpenAI API，提供現代化的單頁應用體驗。
            </p>

            <div class="mt-6">
                <a href="https://github.com/chang180/GPTChatRoom" target="_blank" class="inline-flex items-center font-semibold text-gray-600 hover:text-gray-800">
                    查看源碼
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-gray-600">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- 更新日誌卡片 -->
        <div class="p-6 bg-blue-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-blue-100">
            <div class="flex items-center">
                <div class="p-3 bg-amber-600 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-amber-800 ms-3">
                    最新更新
                </h2>
            </div>

            <p class="mt-4 text-gray-600">
                2025-08-01: 重新設計聊天界面，將輸入框移至頂部並實現訊息反向排序，讓您無需滾動即可查看最新訊息。
            </p>

            <div class="mt-6">
                <a href="https://github.com/chang180/GPTChatRoom/blob/master/README.md#-最近更新" target="_blank" class="inline-flex items-center font-semibold text-amber-600 hover:text-amber-800">
                    查看所有更新
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-amber-600">
                        <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- 底部資訊 -->
    <div class="p-6 bg-gray-50 border-t border-gray-200 text-center">
        <p class="text-sm text-gray-500">
            GPT Chat Room 是一個使用 Laravel 12 和 Vue.js 3 構建的 AI 聊天應用，由
            <a href="https://github.com/chang180" target="_blank" class="text-blue-600 hover:underline">chang180</a> 開發。
        </p>
        <div class="mt-2 flex justify-center space-x-4">
            <a href="https://github.com/chang180/GPTChatRoom" target="_blank" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>
    </div>
</template>

<style scoped>
/* 添加一些額外的樣式，使卡片在懸停時有輕微的變換效果 */
.p-6:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
}
</style>
