<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'">
        <div class="group relative">
            <!-- 主圖示 -->
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                <i class="fas fa-robot text-white text-2xl"></i>
            </div>
            
            <!-- 返回首頁提示 -->
            <div class="absolute -top-2 -right-2 w-6 h-6 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-200">
                <i class="fas fa-home text-blue-600 text-xs"></i>
            </div>
            
            <!-- 懸停效果 -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
        </div>
    </Link>
</template>
