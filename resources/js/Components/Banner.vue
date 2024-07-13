<script setup>
import { ref, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(true);
const style = ref('success');
const message = ref('');

watchEffect(async () => {
    style.value = page.props.jetstream.flash?.bannerStyle || 'success';
    message.value = page.props.jetstream.flash?.banner || '';
    show.value = true;
});
</script>

<template>
    <div>
        <div v-if="show && message" :class="{ 'bg-green-500': style == 'success', 'bg-red-500': style == 'danger' }">
            <div class="max-w-screen-xl px-3 py-2 mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between">
                    <div class="flex items-center flex-1 w-0 min-w-0">
                        <span class="flex p-2 rounded-lg" :class="{ 'bg-green-600': style == 'success', 'bg-red-600': style == 'danger' }">
                            <svg v-if="style == 'success'" class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3 3-6 6m-3-3l3-3 6-6" />
                            </svg>

                            <svg v-if="style == 'danger'" class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12l6 6m-6-6l-6 6m6-6l6-6m-6 6l-6-6" />
                            </svg>
                        </span>

                        <p class="text-sm font-medium text-white truncate ms-3">
                            {{ message }}
                        </p>
                    </div>

                    <div class="shrink-0 sm:ms-3">
                        <button
                            type="button"
                            class="flex p-2 transition rounded-md -me-1 focus:outline-none sm:-me-2"
                            :class="{ 'hover:bg-green-600 focus:bg-green-600': style == 'success', 'hover:bg-red-600 focus:bg-red-600': style == 'danger' }"
                            aria-label="Dismiss"
                            @click.prevent="show = false"
                        >
                            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
