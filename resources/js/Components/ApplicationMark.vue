<template>
    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Background Circle -->
        <circle cx="24" cy="24" r="24" fill="#4CAF50"/>
        <!-- Inner Circle with Shadow Effect -->
        <circle cx="24" cy="24" r="20" fill="#81C784" filter="url(#shadow)"/>
        <!-- Chat Bubble Icon -->
        <path d="M16 18h16v10H20l-4 4v-4H16z" fill="#FFFFFF"/>
        <!-- Check Mark Icon -->
        <path d="M22 26l4 4 8-8" stroke="#4CAF50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <!-- SVG Filter for Shadow Effect -->
        <defs>
            <filter id="shadow" x="-5" y="-5" width="60" height="60">
                <feDropShadow dx="2" dy="4" stdDeviation="3" flood-color="#000000" flood-opacity="0.2"/>
            </filter>
        </defs>
    </svg>
</template>
