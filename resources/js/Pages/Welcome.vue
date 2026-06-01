<script setup>
import { Head, Link } from '@inertiajs/vue3';
import illustration from '/public/images/gptchatroom_illustration.webp';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="GPT Chat Room - AI 智能聊天助手" />
    
    <!-- 主要容器 -->
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-300">
        
        <!-- 導航欄 -->
        <header class="relative z-50">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <!-- Logo 區域 -->
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-robot text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">GPT Chat Room</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">AI 智能聊天助手</p>
                        </div>
                    </div>
                    
                    <!-- 導航按鈕 -->
                    <div class="flex items-center space-x-4">
                        <DarkModeToggle />
                        <template v-if="canLogin">
                            <template v-if="$page.props.auth.user">
                                <Link :href="route('dashboard')" 
                                      class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                                    <i class="fas fa-tachometer-alt mr-2"></i>控制台
                                </Link>
                                <Link :href="route('chat.index')" 
                                      class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    <i class="fas fa-comments mr-2"></i>開始聊天
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="route('login')" 
                                      class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                                    <i class="fas fa-sign-in-alt mr-2"></i>登入
                                </Link>
                                <Link v-if="canRegister" :href="route('register')" 
                                      class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    <i class="fas fa-user-plus mr-2"></i>註冊
                                </Link>
                            </template>
                        </template>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero 區域 -->
        <main class="relative">
            <!-- 背景裝飾 -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
                <div class="absolute top-40 left-40 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
                <div class="text-center">
                    <!-- 主要標題 -->
                    <h2 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                        <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            與 AI 智能對話
                        </span>
                    </h2>
                    
                    <!-- 副標題 -->
                    <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto leading-relaxed">
                        體驗最先進的 AI 聊天技術，獲得智能回答、創意靈感和專業建議
                    </p>

                    <!-- 插圖區域 -->
                    <div class="mb-12">
                        <div class="relative inline-block">
                            <img :src="illustration" 
                                 alt="GPT Chat Room 智能聊天" 
                                 class="w-full max-w-lg mx-auto rounded-2xl shadow-2xl transform hover:scale-105 transition-transform duration-300">
                            <div class="absolute -top-4 -right-4 w-8 h-8 bg-green-400 rounded-full flex items-center justify-center animate-pulse">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 行動按鈕 -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                        <template v-if="canLogin">
                            <template v-if="!$page.props.auth.user">
                                <Link :href="route('register')" 
                                      class="group px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-lg font-semibold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    <i class="fas fa-rocket mr-3 group-hover:animate-bounce"></i>
                                    立即開始體驗
                                </Link>
                                <Link :href="route('login')" 
                                      class="px-8 py-4 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-lg font-semibold rounded-xl hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                    <i class="fas fa-sign-in-alt mr-3"></i>
                                    已有帳號？登入
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="route('chat.index')"
                                      class="group px-8 py-4 bg-gradient-to-r from-green-500 to-blue-600 text-white text-lg font-semibold rounded-xl hover:from-green-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    <i class="fas fa-comments mr-3 group-hover:animate-pulse"></i>
                                    公開主題聊天
                                </Link>
                                <Link :href="route('chat.private.index')"
                                      class="px-8 py-4 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-lg font-semibold rounded-xl hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                                    <i class="fas fa-user-group mr-3"></i>
                                    建立私人房
                                </Link>
                            </template>
                        </template>
                    </div>
                </div>

                <!-- 功能特色 -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-20">
                    <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-brain text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">智能對話</h3>
                        <p class="text-gray-600 dark:text-gray-300">與先進的 GPT 模型進行自然流暢的對話，獲得準確且有用的回答</p>
                    </div>

                    <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-bolt text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">即時回應</h3>
                        <p class="text-gray-600 dark:text-gray-300">快速響應，流暢的聊天體驗，讓您的問題得到即時解答</p>
                    </div>

                    <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">安全可靠</h3>
                        <p class="text-gray-600 dark:text-gray-300">採用先進的安全技術，保護您的隱私和對話內容</p>
                    </div>
                </div>

                <!-- 技術資訊 -->
                <div class="text-center mt-16 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Powered by Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }}) • Built with ❤️ using Vue.js & Inertia.js
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
