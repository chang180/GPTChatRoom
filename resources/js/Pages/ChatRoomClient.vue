<template>
    <div class="flex flex-col h-screen">
        <h1 class="p-4 text-2xl font-bold text-white bg-blue-600">Chat Room</h1>
        <div id="messages" class="flex-1 p-4 overflow-y-auto bg-gray-100">
            <div v-for="(message, index) in sortedMessages" :key="index" class="mb-2">
                <div :class="message.sender_type === 'gpt' ? 'bg-gray-200 p-2 rounded' : ''">
                    <strong>{{ message.sender_type === 'gpt' ? 'GPT' : message.user.name }}:</strong>
                    <span v-html="message.sender_type === 'gpt' ? marked.parse(message.text) : message.text"></span>
                    <span class="ml-2 text-xs text-gray-500">{{ formatDate(message.created_at) }}</span>
                </div>
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
        <div v-if="loading" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="loader"></div>
        </div>
    </div>
</template>

<script setup>
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

        scrollToBottom();

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
            scrollToBottom();  // 確保在GPT回應後再次滾動到底部
        } catch (error) {
            console.error('Message send failed', error);
            loading.value = false;
        }
    }
};

const scrollToBottom = () => {
    const messagesContainer = document.getElementById('messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
};

const sortedMessages = computed(() => {
    return messages.value.slice().sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
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
</style>
