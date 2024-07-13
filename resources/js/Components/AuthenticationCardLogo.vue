<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'">
        <svg
            class="w-16 h-16"
            viewBox="0 0 64 64"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <circle cx="32" cy="32" r="32" fill="#4A90E2"/>
            <path d="M32 16C23.2 16 16 23.2 16 32s7.2 16 16 16c1.6 0 3.2-.2 4.7-.7L48 48l-3.3-9.3c1.1-2.1 1.7-4.5 1.7-7 0-8.8-7.2-16-16-16zm0 28c-6.6 0-12-5.4-12-12s5.4-12 12-12 12 5.4 12 12c0 1.9-.4 3.7-1.1 5.3l-1.5 4.2 4.2 1.5C42.7 44.6 37.5 46 32 46z" fill="#fff"/>
            <path d="M22 34h20v-4H22v4z" fill="#fff"/>
        </svg>
    </Link>
</template>
