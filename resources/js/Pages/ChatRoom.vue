<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed, nextTick } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import { marked } from 'marked';

const props = defineProps({
    messages: Array,
    user: Object,
});

const messages = ref(props.messages || []);
const newMessage = ref('');
const user = ref(props.user);
const loading = ref(false);

const sendMessage = async () => {
    if (newMessage.value.trim() !== '') {
        const messageContent = newMessage.value;
        newMessage.value = '';

        messages.value.push({
            user: user.value,
            text: messageContent,
            created_at: new Date().toISOString(),
            sender_type: 'user',
        });

        loading.value = true;

        try {
            const response = await axios.post(route('chat.send-message'), {
                message: messageContent,
            });

            messages.value.push({
                user: { name: 'GPT' },
                text: response.data.gptResponse,
                created_at: new Date().toISOString(),
                sender_type: 'gpt',
            });

            await nextTick();
            loading.value = false;
        } catch (error) {
            console.error('Message send failed', error);
            loading.value = false;
        }
    }
};

const sortedMessages = computed(() => {
    // 反向排序，最新的消息在前面
    return messages.value.slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};

// 不再需要 scrollToBottom 函數
</script>

<template>
    <AppLayout title="Chatroom">
        <!-- 聊天室主容器 - 佔滿可用空間 -->
        <div class="h-screen flex flex-col pt-16">
            <div class="flex-1 flex flex-col bg-white">
                <!-- 聊天室標題 -->
                <div class="bg-blue-600 text-white p-4">
                    <h1 class="text-xl font-semibold">GPT Chat Room</h1>
                </div>

                <!-- 聊天室容器 -->
                <div class="flex flex-col flex-1 min-h-0">
                    <!-- 輸入區域 - 移到上面 -->
                    <div class="p-4 bg-white border-b flex-shrink-0">
                        <div class="flex space-x-3">
                            <input
                                v-model="newMessage"
                                @keyup.enter="sendMessage"
                                placeholder="輸入您的訊息..."
                                class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :disabled="loading"
                            />
                            <button
                                @click="sendMessage"
                                class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="loading || !newMessage.trim()"
                            >
                                <span v-if="!loading">發送</span>
                                <span v-else>處理中...</span>
                            </button>
                        </div>
                    </div>

                    <!-- 訊息區域 - 反向排列 -->
                    <div id="messages" class="flex-1 p-4 overflow-y-auto bg-gray-50 min-h-0">
                        <div v-for="(message, index) in sortedMessages" :key="index" class="mb-3">
                            <div :class="message.sender_type === 'gpt' ? 'bg-blue-100 p-3 rounded-lg' : 'bg-white p-3 rounded-lg border'">
                                <div class="flex items-start space-x-2">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-white rounded-full"
                                              :class="message.sender_type === 'gpt' ? 'bg-blue-500' : 'bg-gray-500'">
                                            {{ message.sender_type === 'gpt' ? 'AI' : message.user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ message.sender_type === 'gpt' ? 'GPT Assistant' : message.user.name }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ formatDate(message.created_at) }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-700"
                                             v-html="message.sender_type === 'gpt' ? marked.parse(message.text) : message.text">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 載入遮罩 -->
        <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="loader"></div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* CSS 保持不變 */
.loader {
    border: 4px solid #f3f3f3;
    border-radius: 50%;
    border-top: 4px solid #3498db;
    width: 40px;
    height: 40px;
    -webkit-animation: spin 2s linear infinite;
    animation: spin 2s linear infinite;
}

@-webkit-keyframes spin {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* 確保整個聊天室容器使用正確的高度計算 */
.h-screen {
    height: 100vh;
}

/* 避免 iOS Safari 的 viewport 問題 */
@supports (-webkit-touch-callout: none) {
    .h-screen {
        height: -webkit-fill-available;
    }
}

/* Markdown 樣式 */
#messages :deep(h1),
#messages :deep(h2),
#messages :deep(h3) {
    font-weight: bold;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

#messages :deep(h1) { font-size: 1.25rem; }
#messages :deep(h2) { font-size: 1.125rem; }
#messages :deep(h3) { font-size: 1rem; }

#messages :deep(code) {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.875rem;
}

#messages :deep(pre) {
    background-color: #f3f4f6;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 0.5rem 0;
}

#messages :deep(ul),
#messages :deep(ol) {
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}

#messages :deep(li) {
    margin-bottom: 0.25rem;
}

#messages :deep(blockquote) {
    border-left: 4px solid #e5e7eb;
    padding-left: 1rem;
    margin: 0.5rem 0;
    font-style: italic;
}
</style>
