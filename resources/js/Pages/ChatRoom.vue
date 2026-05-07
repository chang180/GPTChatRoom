<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, onUnmounted, computed, nextTick, watch, reactive } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import { marked } from 'marked';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    messages: Array,
    pagination: Object,
    user: Object,
    currentChatRoom: Object,
    themes: Array,
});

// 創建一個純淨的消息數組，避免序列化問題
const messages = reactive([...(props.messages || [])]);
const pagination = reactive(props.pagination || {});
const newMessage = ref('');
const user = ref(props.user);
const loading = ref(false);
const loadingMore = ref(false);
const error = ref(null);
const abortController = ref(null);
const activeBroadcastChannel = ref(null);

// 主題聊天室相關
const currentChatRoom = ref(props.currentChatRoom);
const themes = ref(props.themes || []);
const messagesContainer = ref(null);

// 監聽 props 變化，更新響應式變數
watch(() => props.currentChatRoom, (newChatRoom) => {
    currentChatRoom.value = newChatRoom;
});

watch(() => props.themes, (newThemes) => {
    themes.value = newThemes || [];
});

// 計算屬性：判斷是否為當前活躍的主題
const isActiveTheme = (themeSlug) => {
    return currentChatRoom.value && currentChatRoom.value.slug === themeSlug;
};

// 新增的功能變數
const messageType = ref('ai'); // 'ai' 或 'direct'
const messagesPerPage = ref(30);

// 使用一個簡單的響應式變數來追蹤更新
const updateCounter = ref(0);
const messageIdCounter = ref(0);

// 生成唯一的消息 ID
const generateMessageId = () => {
    return `msg_${Date.now()}_${++messageIdCounter.value}`;
};

const getEcho = () => window.Echo;

const getSocketHeaders = () => {
    const socketId = getEcho()?.socketId?.();

    return socketId ? { 'X-Socket-ID': socketId } : {};
};

const upsertMessage = (incomingMessage) => {
    const existingIndex = messages.findIndex((message) => String(message.id) === String(incomingMessage.id));

    if (existingIndex !== -1) {
        messages.splice(existingIndex, 1, {
            ...messages[existingIndex],
            ...incomingMessage,
        });
        return;
    }

    messages.push(incomingMessage);
    triggerUpdate();
};

const replaceMessage = (targetId, nextMessage) => {
    const messageIndex = messages.findIndex((message) => String(message.id) === String(targetId));

    if (messageIndex === -1) {
        upsertMessage(nextMessage);
        return;
    }

    messages.splice(messageIndex, 1, {
        ...messages[messageIndex],
        ...nextMessage,
    });
    triggerUpdate();
};

const subscribeToChatRoom = (chatRoom) => {
    if (!chatRoom?.id || !getEcho()) {
        return;
    }

    const channelName = `chat-room.${chatRoom.id}`;
    activeBroadcastChannel.value = channelName;

    getEcho()
        .private(channelName)
        .listen('.chat.message.created', ({ message }) => {
            if (message.chat_room_id !== currentChatRoom.value?.id) {
                return;
            }

            upsertMessage(message);
        })
        .listen('.chat.ai-reply.completed', ({ message }) => {
            if (message.chat_room_id !== currentChatRoom.value?.id) {
                return;
            }

            upsertMessage(message);
        })
        .listen('.chat.room.cleared', ({ chat_room_id }) => {
            if (chat_room_id !== currentChatRoom.value?.id) {
                return;
            }

            messages.splice(0, messages.length);
            Object.assign(pagination, {
                current_page: 1,
                last_page: 1,
                per_page: messagesPerPage.value,
                total: 0,
                has_more_pages: false,
            });
            triggerUpdate(true);
        });
};

const unsubscribeFromChatRoom = (chatRoomId) => {
    if (!chatRoomId || !getEcho()) {
        return;
    }

    getEcho().leave(`chat-room.${chatRoomId}`);
    activeBroadcastChannel.value = null;
};

// 載入更多歷史訊息
const loadMoreMessages = async () => {
    if (loadingMore.value || !pagination.has_more_pages) {
        return;
    }
    
    loadingMore.value = true;
    
    try {
        const nextPage = pagination.current_page + 1;
        const response = await axios.get(route('chat.load-more'), {
            params: {
                page: nextPage,
                per_page: messagesPerPage.value,
                theme: currentChatRoom.value?.slug || 'work',
            }
        });
        
        // 將新訊息添加到現有訊息列表的前面
        messages.unshift(...response.data.messages);
        
        // 更新分頁資訊
        Object.assign(pagination, response.data.pagination);
        
    } catch (error) {
        console.error('Failed to load more messages:', error);
    } finally {
        loadingMore.value = false;
    }
};

// 處理滾動事件，實現無限滾動
const handleScroll = () => {
    if (!messagesContainer.value) return;
    
    const container = messagesContainer.value;
    const scrollTop = container.scrollTop;
    const scrollHeight = container.scrollHeight;
    const clientHeight = container.clientHeight;
    
    // 當滾動到頂部附近時載入更多訊息
    if (scrollTop < 100 && pagination.has_more_pages && !loadingMore.value) {
        loadMoreMessages();
    }
};

// 強制觸發響應式更新（減少不必要的更新）
const triggerUpdate = (forceScroll = false) => {
    updateCounter.value++;
    if (forceScroll) {
        nextTick(() => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = 0;
            }
        });
    }
};

const sendMessage = async () => {
    if (newMessage.value.trim() !== '') {
        const messageContent = newMessage.value;
        newMessage.value = '';
        error.value = null;

        // 獲取當前時間戳
        const now = new Date();
        const userTimestamp = now.toISOString();

        // 添加用戶消息
        const userMessage = {
            id: generateMessageId(),
            user: {
                name: user.value?.name || 'User',
            },
            text: messageContent,
            created_at: userTimestamp,
            sender_type: 'user',
            message_type: messageType.value, // 記錄訊息類型
        };
        const optimisticUserMessageId = userMessage.id;
        messages.push(userMessage);

        // 觸發更新，確保新消息顯示在最上方
        triggerUpdate(true);

        // 如果是直接發送模式，需要保存到數據庫但不需要 AI 回應
        if (messageType.value === 'direct') {
            // 發送直接訊息到後端保存
            try {
                const response = await axios.post(route('chat.send-message'), {
                    message: messageContent,
                    theme: currentChatRoom.value?.slug || 'work',
                    message_type: 'direct'
                }, {
                    headers: getSocketHeaders(),
                });
                replaceMessage(optimisticUserMessageId, {
                    ...(messages.find((message) => String(message.id) === String(optimisticUserMessageId)) || {}),
                    ...response.data.message,
                });
            } catch (error) {
                console.error('Failed to save direct message:', error);
                // 如果保存失敗，可以選擇移除前端顯示的訊息或顯示錯誤
            }
            return;
        }

        // 如果是 AI 發問模式，發送給 GPT
        loading.value = true;

        // GPT 消息時間戳稍微晚一點，確保它在排序中位於最上方
        const gptTimestamp = new Date(now.getTime() + 1).toISOString();

        // 創建 GPT 回應消息（初始為空）
        const gptMessage = {
            id: generateMessageId(),
            user: { name: 'GPT' },
            text: '',
            created_at: gptTimestamp,
            sender_type: 'gpt',
            isStreaming: true,
        };
        const optimisticGptMessageId = gptMessage.id;

        // 將消息添加到響應式數組中，這樣 Vue 可以追蹤變化
        const messageIndex = messages.length;
        messages.push(gptMessage);

        // 創建新的 AbortController
        abortController.value = new AbortController();

        // 用於追蹤已處理的響應長度
        let processedLength = 0;

        try {
            // 使用 axios 進行流式請求處理
            const response = await axios({
                method: 'POST',
                url: route('chat.send-message-stream'),
                data: { 
                    message: messageContent,
                    theme: currentChatRoom.value?.slug || 'work'
                },
                responseType: 'text',
                signal: abortController.value.signal, // 添加取消信號
                headers: {
                    'Accept': 'text/event-stream',
                    'Cache-Control': 'no-cache',
                    ...getSocketHeaders(),
                },
                onDownloadProgress: (progressEvent) => {
                    const xhr = progressEvent.event.target;
                    const responseText = xhr.responseText || '';

                    // 只處理新的數據部分
                    const newData = responseText.substring(processedLength);
                    processedLength = responseText.length;

                    // 處理新的 SSE 數據
                    const lines = newData.split('\n');

                    for (const line of lines) {
                        if (line.trim() && line.startsWith('data: ')) {
                            try {
                                const jsonStr = line.substring(6).trim();
                                const eventData = JSON.parse(jsonStr);

                                if ('messageId' in eventData && !('done' in eventData) && eventData.messageId !== null) {
                                    replaceMessage(optimisticUserMessageId, {
                                        ...(messages.find((message) => String(message.id) === String(optimisticUserMessageId)) || {}),
                                        id: eventData.messageId,
                                    });
                                }

                                // 處理內容
                                if ('content' in eventData && eventData.content !== null) {
                                    // 直接修改響應式數組中的對象，Vue 會檢測到變化
                                    messages[messageIndex].text += eventData.content;
                                    
                                    // 第一次收到內容時，停止 loading 狀態
                                    if (loading.value) {
                                        loading.value = false;
                                    }
                                    // 不在每次流式更新時調用 triggerUpdate，讓 Vue 自然響應
                                }

                                // 處理完成
                                if (eventData.done) {
                                    replaceMessage(optimisticGptMessageId, {
                                        ...(messages.find((message) => String(message.id) === String(optimisticGptMessageId)) || {}),
                                        id: eventData.messageId ?? optimisticGptMessageId,
                                        isStreaming: false,
                                    });
                                    loading.value = false;
                                    // 只在完成時觸發一次更新
                                    triggerUpdate();
                                }
                            } catch (e) {
                                console.error('Error parsing SSE data:', e, line);
                            }
                        }
                    }
                }
            });

            loading.value = false;
            messages[messageIndex].isStreaming = false;
            abortController.value = null;
            triggerUpdate();

        } catch (error) {
            console.error('Message send failed', error);
            loading.value = false;
            messages[messageIndex].isStreaming = false;
            abortController.value = null;

            // 只有在不是取消錯誤時才顯示錯誤訊息
            if (!axios.isCancel(error)) {
                handleError(error);
            }
        }
    }
};

// 處理錯誤的函數
const handleError = (error) => {
    let errorMessage = '發送訊息失敗，請稍後再試。';

    if (error.response) {
        if (error.response.status === 429) {
            errorMessage = 'API 請求頻率過高，請稍後再試。';
        } else if (error.response.data && error.response.data.message) {
            errorMessage = `錯誤: ${error.response.data.message}`;
        } else {
            errorMessage = `伺服器錯誤 (${error.response.status})，請稍後再試。`;
        }
    } else if (error.request) {
        errorMessage = '無法連接到伺服器，請檢查您的網絡連接。';
    } else if (error.message) {
        errorMessage = `錯誤: ${error.message}`;
    }

    // 添加錯誤訊息
    const errorMessage_obj = {
        id: generateMessageId(),
        user: { name: 'System' },
        text: errorMessage,
        created_at: new Date().toISOString(),
        sender_type: 'error',
    };
    messages.push(errorMessage_obj);

    triggerUpdate(true);
    error.value = errorMessage;
};

// 取消請求
const cancelRequest = () => {
    if (abortController.value) {
        abortController.value.abort('Request cancelled by user');
        abortController.value = null;
        loading.value = false;

        // 查找最後一個 GPT 消息並添加取消標記
        let lastGptMessageIndex = -1;
        for (let i = messages.length - 1; i >= 0; i--) {
            if (messages[i].sender_type === 'gpt' && messages[i].isStreaming) {
                lastGptMessageIndex = i;
                break;
            }
        }
        if (lastGptMessageIndex !== -1) {
            messages[lastGptMessageIndex].text += "\n\n*[請求已取消]*";
            messages[lastGptMessageIndex].isStreaming = false;
            triggerUpdate(true);
        }
    }
};

const sortedMessages = computed(() => {
    // 反向排序，最新的消息在前面
    // 只在 updateCounter 改變時重新計算，避免流式更新時的頻繁重新排序
    updateCounter.value; // 這會觸發重新計算
    return [...messages].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
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

// 清除所有聊天記錄
const clearAllMessages = async () => {
    const roomName = currentChatRoom.value?.name || '當前聊天室';
    if (confirm(`確定要清除「${roomName}」的所有聊天記錄嗎？此操作無法復原。`)) {
        try {
            loading.value = true;
            
            // 調用 API 清除服務器端的記錄
            const response = await axios.delete(route('chat.clear'), {
                data: {
                    theme: currentChatRoom.value?.slug || 'work'
                },
                headers: getSocketHeaders(),
            });
            
            if (response.data.success) {
                // 清除客戶端的記錄
                messages.splice(0, messages.length);
                Object.assign(pagination, {
                    current_page: 1,
                    last_page: 1,
                    per_page: messagesPerPage.value,
                    total: 0,
                    has_more_pages: false,
                });
                triggerUpdate(true);
                
                // 顯示成功訊息
                console.log(`✅ 「${roomName}」${response.data.message} (已刪除 ${response.data.deleted_count} 條記錄)`);
            } else {
                throw new Error(response.data.message || '清除記錄失敗');
            }
        } catch (error) {
            console.error('清除記錄失敗:', error);
            alert('清除記錄時發生錯誤，請稍後再試。');
        } finally {
            loading.value = false;
        }
    }
};

// 切換主題聊天室
const switchTheme = async (themeSlug) => {
    try {
        loading.value = true;
        
        // 使用 Inertia 導航到新的主題聊天室
        await router.visit(route('chat.theme', { theme: themeSlug }), {
            preserveState: false,
            preserveScroll: false,
            replace: true, // 使用 replace 而不是 push
        });
    } catch (error) {
        console.error('切換主題失敗:', error);
    } finally {
        loading.value = false;
    }
};

// 更改每頁訊息數量
const changeMessagesPerPage = async () => {
    try {
        // 重新載入訊息以應用新的分頁設定
        messages.splice(0, messages.length);
        Object.assign(pagination, {
            current_page: 1,
            last_page: 1,
            per_page: messagesPerPage.value,
            total: 0,
            has_more_pages: false,
        });
        
        // 載入第一頁
        await loadMoreMessages();
    } catch (error) {
        console.error('更改分頁設定失敗:', error);
    }
};

// 組件掛載時確保滾動位置正確並添加滾動事件監聽器
onMounted(() => {
    subscribeToChatRoom(currentChatRoom.value);

    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = 0;
            // 添加滾動事件監聽器
            messagesContainer.value.addEventListener('scroll', handleScroll);
        }
    });
});

// 組件卸載時清理正在進行的請求並移除事件監聽器
onUnmounted(() => {
    if (abortController.value) {
        abortController.value.abort('Component unmounted');
        abortController.value = null;
    }
    
    // 移除滾動事件監聽器
    if (messagesContainer.value) {
        messagesContainer.value.removeEventListener('scroll', handleScroll);
    }

    unsubscribeFromChatRoom(currentChatRoom.value?.id);
    
    loading.value = false;
});

watch(() => props.currentChatRoom?.id, (newRoomId, oldRoomId) => {
    if (oldRoomId && oldRoomId !== newRoomId) {
        unsubscribeFromChatRoom(oldRoomId);
    }

    if (newRoomId && newRoomId !== oldRoomId) {
        subscribeToChatRoom(props.currentChatRoom);
    }
});
</script>

<template>
    <AppLayout title="Chatroom">
        <!-- 聊天室主容器 - 佔滿可用空間，移除頂部間距 -->
        <div class="h-screen flex flex-col">
            <div class="flex-1 flex flex-col bg-white dark:bg-gray-900">
                <!-- 主題頁籤區域 -->
                <div class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex">
                        <button
                            v-for="theme in themes"
                            :key="theme.id"
                            @click="switchTheme(theme.slug)"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200',
                                isActiveTheme(theme.slug)
                                    ? 'text-blue-600 dark:text-blue-400 border-blue-600 dark:border-blue-400 bg-white dark:bg-gray-900'
                                    : 'text-gray-600 dark:text-gray-400 border-transparent hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <i :class="{
                                'fas fa-briefcase': theme.slug === 'work',
                                'fas fa-graduation-cap': theme.slug === 'study',
                                'fas fa-lightbulb': theme.slug === 'creative',
                                'fas fa-comments': theme.slug === 'daily'
                            }" class="mr-2"></i>
                            {{ theme.name }}
                        </button>
                    </div>
                </div>

                <!-- 控制區域 -->
                <div class="bg-blue-600 dark:bg-blue-700 text-white p-4 flex justify-between items-center shadow-lg">
                    <div class="flex items-center space-x-4">
                        <!-- 訊息類型選擇 -->
                        <div class="flex bg-blue-500 dark:bg-blue-600 rounded-lg p-1">
                            <button
                                @click="messageType = 'ai'"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md transition-colors duration-200',
                                    messageType === 'ai' 
                                        ? 'bg-white text-blue-600 dark:bg-blue-800 dark:text-blue-200' 
                                        : 'text-blue-100 hover:text-white'
                                ]"
                            >
                                <i class="fas fa-robot mr-1"></i>
                                AI 發問
                            </button>
                            <button
                                @click="messageType = 'direct'"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md transition-colors duration-200',
                                    messageType === 'direct' 
                                        ? 'bg-white text-blue-600 dark:bg-blue-800 dark:text-blue-200' 
                                        : 'text-blue-100 hover:text-white'
                                ]"
                            >
                                <i class="fas fa-paper-plane mr-1"></i>
                                直接發送
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- 每頁訊息數量選擇 -->
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-blue-100">每頁:</label>
                            <select 
                                v-model="messagesPerPage" 
                                @change="changeMessagesPerPage"
                                class="bg-blue-500 dark:bg-blue-600 text-white text-sm rounded px-2 py-1 border-0 focus:ring-2 focus:ring-blue-300"
                            >
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        
                        <!-- 清除聊天記錄按鈕 -->
                        <button
                            @click="clearAllMessages"
                            class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-sm rounded-md transition-colors duration-200"
                            title="清除所有聊天記錄"
                        >
                            <i class="fas fa-trash-alt mr-1"></i>
                            清除記錄
                        </button>
                        
                        <!-- 取消請求按鈕 -->
                        <button
                            v-if="loading"
                            @click="cancelRequest"
                            class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded-md transition-colors duration-200"
                        >
                            <i class="fas fa-stop mr-1"></i>
                            取消請求
                        </button>
                    </div>
                </div>

                <!-- 聊天室容器 -->
                <div class="flex flex-col flex-1 min-h-0">
                    <!-- 輸入區域 -->
                    <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                        <div class="flex space-x-3">
                            <input
                                v-model="newMessage"
                                @keyup.enter="sendMessage"
                                :placeholder="messageType === 'ai' ? '向 AI 發問...' : '輸入您的訊息...'"
                                class="flex-1 p-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 placeholder-gray-500 dark:placeholder-gray-400"
                                :disabled="loading"
                            />
                            <button
                                @click="sendMessage"
                                class="px-6 py-3 text-white bg-blue-600 dark:bg-blue-700 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                :disabled="loading || !newMessage.trim()"
                            >
                                <i v-if="!loading" :class="messageType === 'ai' ? 'fas fa-robot mr-2' : 'fas fa-paper-plane mr-2'"></i>
                                <i v-else class="fas fa-spinner fa-spin mr-2"></i>
                                <span v-if="!loading">{{ messageType === 'ai' ? '發送給 AI' : '發送' }}</span>
                                <span v-else>處理中...</span>
                            </button>
                        </div>

                        <!-- 錯誤提示 -->
                        <div v-if="error" class="mt-2 p-3 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-lg flex justify-between items-center border border-red-200 dark:border-red-800">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span>{{ error }}</span>
                            </div>
                            <button @click="clearError" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors duration-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 訊息區域 -->
                    <div id="messages" ref="messagesContainer" class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-900 min-h-0">
                        <div v-for="(message, index) in sortedMessages" :key="message.id || `fallback-${index}`" class="mb-3">
                            <div :class="{
                                'bg-blue-100 dark:bg-blue-900/30 p-3 rounded-lg border border-blue-200 dark:border-blue-700': message.sender_type === 'gpt',
                                'bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700': message.sender_type === 'user',
                                'bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-200 dark:border-red-800': message.sender_type === 'error'
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
                                                      'text-gray-900 dark:text-gray-100': message.sender_type !== 'error',
                                                      'text-red-700 dark:text-red-400': message.sender_type === 'error'
                                                  }">
                                                {{ message.sender_type === 'gpt' ? 'GPT Assistant' :
                                                   message.sender_type === 'error' ? '系統訊息' :
                                                   message.user.name }}
                                            </span>
                                            <!-- 訊息類型標示 -->
                                            <span v-if="message.sender_type === 'user' && message.message_type" 
                                                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                  :class="{
                                                      'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': message.message_type === 'ai',
                                                      'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300': message.message_type === 'direct'
                                                  }">
                                                <i :class="message.message_type === 'ai' ? 'fas fa-robot mr-1' : 'fas fa-paper-plane mr-1'"></i>
                                                {{ message.message_type === 'ai' ? 'AI 發問' : '直接發送' }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDate(message.created_at) }}
                                            </span>
                                            <!-- 顯示流式接收指示器 -->
                                            <span v-if="message.isStreaming" class="inline-flex items-center ml-2">
                                                <span class="typing-indicator"></span>
                                            </span>
                                        </div>
                                        <div class="mt-1 text-sm"
                                             :class="{
                                                 'text-gray-700 dark:text-gray-300': message.sender_type !== 'error',
                                                 'text-red-600 dark:text-red-400': message.sender_type === 'error'
                                             }">
                                            <div v-if="message.sender_type === 'gpt'" v-html="marked.parse(message.text || '')"></div>
                                            <div v-else>{{ message.text }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 載入更多訊息的指示器 -->
        <div v-if="loadingMore" class="flex justify-center py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
                <div class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
                <span>載入更多訊息...</span>
            </div>
        </div>

        <!-- 載入遮罩 (只在初始加載時顯示) -->
        <div v-if="loading && messages.filter(m => m.sender_type === 'gpt' && m.isStreaming).length === 0" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
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
