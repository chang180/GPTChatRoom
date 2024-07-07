<script>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Chat Room">
        <div class="flex flex-col h-screen">
            <h1 class="text-2xl font-bold p-4 bg-blue-600 text-white">Chat Room</h1>
            <div id="messages" class="flex-1 p-4 overflow-y-auto bg-gray-100">
                <div v-for="(message, index) in messages" :key="index" class="mb-2">
                    <strong>{{ message.user.name }}:</strong> {{ message.text }}
                    <span class="text-gray-500 text-xs ml-2">{{ message.created_at }}</span>
                </div>
            </div>
            <div class="p-4 bg-white flex">
                <input
                    v-model="newMessage"
                    @keyup.enter="sendMessage"
                    placeholder="Type a message"
                    class="flex-1 border p-2 rounded"
                />
                <button
                    @click="sendMessage"
                    class="ml-2 p-2 bg-blue-600 text-white rounded"
                >
                    Send
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { route } from 'ziggy-js';

// 获取页面属性
const { props } = usePage();
const messages = ref(props.messages || []);
const newMessage = ref('');
const user = ref(props.user);

const sendMessage = async () => {
    if (newMessage.value.trim() !== '') {
        const messageContent = newMessage.value;
        newMessage.value = '';

        // 在本地添加用户的消息
        messages.value.push({
            user: user.value,
            text: messageContent,
            created_at: new Date().toLocaleTimeString(),
        });

        try {
            const response = await axios.post(route('chat.send-message'), {
                message: messageContent,
            });

            // 在本地添加 GPT 的回复
            messages.value.push({
                user: { name: 'GPT-3.5' },
                text: response.data.gptResponse,
                created_at: new Date().toLocaleTimeString(),
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
