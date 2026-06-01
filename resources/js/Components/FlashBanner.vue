<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const error = computed(() => page.props.flash?.error);
const success = computed(() => page.props.flash?.success);
const message = computed(() => page.props.flash?.message);
</script>

<template>
    <div v-if="error" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 mt-0.5"></i>
            <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ error }}</p>
        </div>
    </div>

    <div v-if="success" class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
        <div class="flex items-start gap-2">
            <i class="fas fa-check-circle text-green-600 dark:text-green-400 mt-0.5"></i>
            <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ success }}</p>
        </div>
    </div>

    <div v-if="message && !success" class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
        <div class="flex items-start gap-2">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">{{ message }}</p>
        </div>
    </div>
</template>
