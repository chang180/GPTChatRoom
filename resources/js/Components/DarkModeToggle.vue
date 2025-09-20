<template>
    <button
        @click="toggleDarkMode"
        class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
        :class="[
            isDark ? 'bg-gray-700 text-yellow-400 hover:bg-gray-600 focus:ring-gray-500' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400'
        ]"
        :title="isDark ? '切換到淺色模式' : '切換到深色模式'"
    >
        <i v-if="isDark" class="fas fa-sun text-lg"></i>
        <i v-else class="fas fa-moon text-lg"></i>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isDark = ref(false);

// 初始化 dark mode 狀態
onMounted(() => {
    // 檢查 localStorage 中的偏好設定
    const savedTheme = localStorage.getItem('darkMode');
    if (savedTheme !== null) {
        isDark.value = savedTheme === 'true';
    } else {
        // 如果沒有儲存的偏好，檢查系統偏好
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    
    // 同步組件狀態與實際的 DOM 狀態
    // 因為 HTML 已經在載入時設定了正確的 class
    const htmlElement = document.documentElement;
    isDark.value = htmlElement.classList.contains('dark');
    
    // 監聽系統主題變化
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', handleSystemThemeChange);
});

// 切換 dark mode
const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    applyTheme();
    localStorage.setItem('darkMode', isDark.value.toString());
};

// 應用主題到 HTML 元素
const applyTheme = () => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

// 處理系統主題變化
const handleSystemThemeChange = (e) => {
    // 只有在用戶沒有手動設定偏好時才跟隨系統
    if (localStorage.getItem('darkMode') === null) {
        isDark.value = e.matches;
        applyTheme();
    }
};
</script>
