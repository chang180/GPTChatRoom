<template>
    <AppLayout title="Chat Room">
        <div class="flex flex-col h-screen">
            <h1 class="p-4 text-2xl font-bold text-white bg-blue-600">Chat Room</h1>
            <div id="messages" class="flex-1 p-4 overflow-y-auto bg-gray-100">
                <div v-for="(message, index) in sortedMessages" :key="index" class="mb-2">
                    <strong>{{ message.sender_type === 'gpt' ? 'GPT' : message.user.name }}:</strong>
                    <span v-html="message.sender_type === 'gpt' ? marked.parse(message.text) : message.text"></span>
                    <span class="ml-2 text-xs text-gray-500">{{ message.created_at }}</span>
                </div>
            </div>
            <div class="flex p-4 bg-white">
                <input
                    v-model="newMessage"
                    @keyup.enter="sendMessage"
                    placeholder="Type a message"
                    class="flex-1 p-2 border rounded"
                />
                <button
                    @click="sendMessage"
                    class="p-2 ml-2 text-white bg-blue-600 rounded"
                >
                    Send
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue';
import { marked } from 'marked';

// 定义接收的属性
const props = defineProps({
    messages: Array,
    user: Object,
});

const messages = ref(props.messages || []);
const newMessage = ref('');
const user = ref(props.user);

// 发送消息
const sendMessage = async () => {
    if (newMessage.value.trim() !== '') {
        const messageContent = newMessage.value;
        newMessage.value = '';

        // 在本地添加用户的消息
        messages.value.push({
            user: user.value,
            text: messageContent,
            created_at: new Date().toISOString(),
            sender_type: 'user',
        });

        try {
            const response = await axios.post(route('chat.send-message'), {
                message: messageContent,
            });

            // 在本地添加 GPT 的回复
            messages.value.push({
                user: { name: 'GPT' },
                text: response.data.gptResponse,
                created_at: new Date().toISOString(),
                sender_type: 'gpt',
            });
        } catch (error) {
            console.error('Message send failed', error);
        }

        scrollToBottom();
    }
};

// 滚动到底部
const scrollToBottom = () => {
    const messagesContainer = document.getElementById('messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
};

// 反转并排序消息
const sortedMessages = computed(() => {
    return messages.value.slice().sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

onMounted(() => {
    scrollToBottom();
});
</script>

<style scoped>
#messages {
    border: 1px solid #ccc;
    padding: 10px;
    height: 300px;
    overflow-y: scroll;
}
input {
    width: calc(100% - 100px);
}
button {
    width: 80px;
}
</style>
