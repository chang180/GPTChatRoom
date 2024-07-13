<script setup>
import { Head, Link } from '@inertiajs/vue3';
import illustration from '/public/images/gptchatroom_illustration.webp';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Welcome to GptChatRoom" />
    <div class="flex flex-col items-center justify-center h-screen overflow-hidden text-black bg-blue-100">
        <header class="flex items-center justify-between w-full max-w-2xl px-6 py-6 lg:max-w-7xl">
            <div class="flex items-center">
                <svg class="w-auto h-12 text-blue-600 lg:h-16" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- SVG path here -->
                </svg>
                <h1 class="ml-4 text-2xl font-bold">Welcome to GptChatRoom</h1>
            </div>
            <nav v-if="canLogin" class="flex space-x-4">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-black hover:text-gray-700">
                    Welcome
                </Link>
                <Link v-if="$page.props.auth.user" :href="route('chat')" class="text-black hover:text-gray-700">
                    ChatRoom
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="text-black hover:text-gray-700">
                        Log in
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="text-black hover:text-gray-700">
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <main class="flex flex-col items-center justify-center flex-1 w-full max-w-2xl px-6 py-6 lg:max-w-7xl">
            <img :src="illustration" alt="GptChatRoom Illustration" class="mb-6 rounded-lg shadow-lg max-w-full max-h-90%">
            <h2 class="mb-6 text-xl font-semibold">Welcome to GptChatRoom</h2>
            <p class="mb-4">This is a simple landing page created with Laravel and Inertia.js. Use the navigation links to log in or register, and explore the features of our application.</p>
            <p class="text-sm text-gray-700">Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>
        </main>
    </div>
</template>
