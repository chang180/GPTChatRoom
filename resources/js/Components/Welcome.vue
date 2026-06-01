<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
</script>

<template>
    <Head title="Welcome to GPT Chat Room Dashboard" />

    <!-- Hero Section -->
    <div class="p-6 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 border-b border-gray-200 dark:border-gray-700 lg:p-8">
        <div class="flex justify-center">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-robot text-white text-3xl"></i>
            </div>
        </div>
        <h1 class="mt-4 text-3xl font-bold text-center text-gray-900 dark:text-white">
            GPT Chat Room 控制中心
        </h1>

        <p class="mt-4 text-lg leading-relaxed text-center text-gray-600 dark:text-gray-300">
            您的 AI 聊天助手已準備就緒，隨時可以回答您的問題、提供建議或進行創意討論。
        </p>

        <div class="flex justify-center mt-6">
            <Link :href="route('chat.index')" class="group px-8 py-4 text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center">
                <i class="fas fa-comments mr-3 group-hover:animate-pulse"></i>
                立即開始聊天
            </Link>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 bg-white dark:bg-gray-800 lg:p-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-700 dark:to-gray-600 rounded-xl border border-blue-200 dark:border-gray-600">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-600 rounded-lg">
                        <i class="fas fa-comments text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">聊天室</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">與 AI 智能對話</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gradient-to-br from-green-50 to-green-100 dark:from-gray-700 dark:to-gray-600 rounded-xl border border-green-200 dark:border-gray-600">
                <div class="flex items-center">
                    <div class="p-3 bg-green-600 rounded-lg">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">個人資料</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">管理帳戶設定</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-gray-700 dark:to-gray-600 rounded-xl border border-purple-200 dark:border-gray-600">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-600 rounded-lg">
                        <i class="fas fa-cog text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">設定</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">自訂體驗</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Link :href="route('chat.index')" class="group p-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl text-white hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-2">公開主題聊天</h3>
                        <p class="text-blue-100">與 GPT 在四個主題房對話</p>
                    </div>
                    <i class="fas fa-comments text-2xl group-hover:translate-x-1 transition-transform duration-200"></i>
                </div>
            </Link>

            <Link :href="route('chat.private.index')" class="group p-6 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl text-white hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-2">私人聊天室</h3>
                        <p class="text-emerald-100">建立群組、邀請朋友一起聊</p>
                    </div>
                    <i class="fas fa-user-group text-2xl group-hover:translate-x-1 transition-transform duration-200"></i>
                </div>
            </Link>

            <Link :href="route('profile.show')" class="group p-6 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">個人資料</h3>
                        <p class="text-gray-600 dark:text-gray-300">管理您的帳戶設定</p>
                    </div>
                    <i class="fas fa-user text-2xl text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200 transition-colors duration-200"></i>
                </div>
            </Link>
        </div>
    </div>
</template>