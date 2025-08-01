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
const error = ref(null); // 新增錯誤狀態
const streamingMessage = ref(null); // 用於存儲正在流式接收的消息

const sendMessage = async () => {
    if (newMessage.value.trim() !== '') {
        const messageContent = newMessage.value;
        newMessage.value = '';
        error.value = null; // 重置錯誤狀態

        messages.value.push({
            user: user.value,
            text: messageContent,
            created_at: new Date().toISOString(),
            sender_type: 'user',
        });

        loading.value = true;

        try {
            // 創建一個空的流式消息
            streamingMessage.value = {
                user: { name: 'GPT' },
                text: '', // 初始為空，將逐漸填充
                created_at: new Date().toISOString(),
                sender_type: 'gpt',
                isStreaming: true, // 標記為正在流式接收
            };

            // 將流式消息添加到消息列表
            messages.value.push(streamingMessage.value);

            // 使用 EventSource 接收流式數據
            const eventSource = new EventSource(route('chat.send-message-stream') + '?message=' + encodeURIComponent(messageContent));

            eventSource.onmessage = (event) => {
                const data = JSON.parse(event.data);

                // 處理消息 ID
                if (data.messageId) {
                    // 可以保存消息 ID 以備後用
                    console.log('Message ID:', data.messageId);
                }

                // 處理內容片段
                if (data.content) {
                    streamingMessage.value.text += data.content;
                }

                // 處理完成信號
                if (data.done) {
                    eventSource.close();
            loading.value = false;
                    streamingMessage.value.isStreaming = false; // 標記流式接收完成
                }
};

            eventSource.onerror = (error) => {
                console.error('EventSource error:', error);
                eventSource.close();
                loading.value = false;
                streamingMessage.value.isStreaming = false;

                // 處理錯誤
                handleError(new Error('流式連接錯誤，請稍後再試。'));
};

        } catch (error) {
            console.error('Message send failed', error);
            loading.value = false;
            handleError(error);
        }
    }
};

// 處理錯誤的函數
const handleError = (error) => {
    // 添加錯誤處理
    let errorMessage = '發送訊息失敗，請稍後再試。';

    if (error.response) {
        // 服務器回應了錯誤
        if (error.response.status === 429) {
            errorMessage = 'API 請求頻率過高，請稍後再試。';
        } else if (error.response.data && error.response.data.message) {
            errorMessage = `錯誤: ${error.response.data.message}`;
        } else {
            errorMessage = `伺服器錯誤 (${error.response.status})，請稍後再試。`;
        }
    } else if (error.request) {
        // 請求已發送但沒有收到回應
        errorMessage = '無法連接到伺服器，請檢查您的網絡連接。';
    }

    // 將錯誤訊息添加到對話中
    messages.value.push({
        user: { name: 'System' },
        text: errorMessage,
        created_at: new Date().toISOString(),
        sender_type: 'error',
    });

    error.value = errorMessage;
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

// 清除錯誤訊息
const clearError = () => {
    error.value = null;
};
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

                        <!-- 錯誤提示 -->
                        <div v-if="error" class="mt-2 p-2 bg-red-100 text-red-700 rounded-lg flex justify-between items-center">
                            <span>{{ error }}</span>
                            <button @click="clearError" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- 訊息區域 - 反向排列 -->
                    <div id="messages" class="flex-1 p-4 overflow-y-auto bg-gray-50 min-h-0">
                        <div v-for="(message, index) in sortedMessages" :key="index" class="mb-3">
                            <div :class="{
                                'bg-blue-100 p-3 rounded-lg': message.sender_type === 'gpt',
                                'bg-white p-3 rounded-lg border': message.sender_type === 'user',
                                'bg-red-50 p-3 rounded-lg border border-red-200': message.sender_type === 'error'
                            }">
                                <div class="flex items-start space-x-2">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-white rounded-full"
                                              :class="{
                                                  'bg-blue-500': message.sender_type === 'gpt',
                                                  'bg-gray-500': message.sender_type === 'user',
                                                  'bg-red-500': message.sender_type === 'error'
                                              }">
                                            {{ message.sender_type === 'gpt' ? 'AI' :
                                               message.sender_type === 'error' ? '!' :
                                               message.user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium"
                                                  :class="{
                                                      'text-gray-900': message.sender_type !== 'error',
                                                      'text-red-700': message.sender_type === 'error'
                                                  }">
                                                {{ message.sender_type === 'gpt' ? 'GPT Assistant' :
                                                   message.sender_type === 'error' ? '系統訊息' :
                                                   message.user.name }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ formatDate(message.created_at) }}
                                            </span>
                                            <!-- 顯示流式接收指示器 -->
                                            <span v-if="message.isStreaming" class="inline-flex items-center ml-2">
                                                <span class="typing-indicator"></span>
                                            </span>
                                        </div>
                                        <div class="mt-1 text-sm"
                                             :class="{
                                                 'text-gray-700': message.sender_type !== 'error',
                                                 'text-red-600': message.sender_type === 'error'
                                             }"
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

        <!-- 載入遮罩 (只在初始加載時顯示) -->
        <div v-if="loading && !streamingMessage" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
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

/* 打字指示器動畫 */
.typing-indicator {
    display: inline-block;
    width: 20px;
    height: 10px;
    position: relative;
}

.typing-indicator::after {
    content: '...';
    position: absolute;
    left: 0;
    top: -5px;
    animation: typing 1.5s infinite;
    color: #3b82f6;
    font-weight: bold;
}

@keyframes typing {
    0%, 20% { content: '.'; }
    40%, 60% { content: '..'; }
    80%, 100% { content: '...'; }
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
