<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const openChatRoomClient = () => {
    window.open('/chat-client', '_blank');
};
</script>

<template>
    <AppLayout title="Chatroom">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                ChatRoom
            </h2>
        </template>

        <div class="flex items-center justify-center py-12">
            <button
                @click="openChatRoomClient"
                class="px-4 py-2 text-white bg-blue-600 rounded shadow-lg hover:bg-blue-700 focus:outline-none"
            >
                Open Chat Room
            </button>
        </div>
    </AppLayout>
</template>
