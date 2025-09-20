<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-800 flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden">
        <!-- 背景裝飾 -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-10 rounded-full"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white opacity-10 rounded-full"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white opacity-5 rounded-full"></div>
        </div>
        
        <!-- Logo 區域 -->
        <div class="relative z-10 mb-8">
            <slot name="logo" />
        </div>

        <!-- 登入卡片 -->
        <div class="relative z-10 w-full sm:max-w-md mx-4 px-8 py-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm shadow-2xl overflow-hidden sm:rounded-2xl border border-white/20 dark:border-gray-700/20">
            <!-- 卡片裝飾 -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-purple-500"></div>
            <slot />
        </div>
        
        <!-- 底部文字 -->
        <div class="relative z-10 mt-8 text-center text-white/80">
            <p class="text-sm">歡迎來到 GPT Chat Room</p>
            <p class="text-xs mt-1">與 AI 進行智能對話</p>
        </div>
    </div>
</template>
