<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const updateAdminStatus = (user, isAdmin) => {
    router.patch(route('admin.users.update', user.id), { is_admin: isAdmin }, {
        preserveScroll: true,
    });
};

const formatDate = (isoString) => {
    if (!isoString) {
        return '—';
    }

    return new Date(isoString).toLocaleString('zh-TW');
};
</script>

<template>
    <AppLayout title="管理">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                使用者管理
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="page.props.flash?.success"
                    class="mb-4 rounded-md bg-green-50 dark:bg-green-900/30 p-4 text-sm text-green-700 dark:text-green-300"
                >
                    {{ page.props.flash.success }}
                </div>
                <div
                    v-if="page.props.flash?.error"
                    class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 p-4 text-sm text-red-700 dark:text-red-300"
                >
                    {{ page.props.flash.error }}
                </div>

                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        名稱
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        角色
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        註冊時間
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ user.name }}
                                        <span
                                            v-if="user.id === currentUserId"
                                            class="ml-2 text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            (你)
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ user.email }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            :class="user.is_admin
                                                ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'"
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        >
                                            {{ user.is_admin ? '管理員' : '一般使用者' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <button
                                            v-if="!user.is_admin"
                                            type="button"
                                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500"
                                            @click="updateAdminStatus(user, true)"
                                        >
                                            設為管理員
                                        </button>
                                        <button
                                            v-else-if="user.id !== currentUserId"
                                            type="button"
                                            class="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-medium text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                            @click="updateAdminStatus(user, false)"
                                        >
                                            降級為一般使用者
                                        </button>
                                        <span
                                            v-else
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            無法自行降級
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="users.links?.length > 3"
                        class="flex flex-wrap gap-2 border-t border-gray-200 dark:border-gray-700 px-6 py-4"
                    >
                        <template v-for="(link, index) in users.links" :key="index">
                            <span
                                v-if="!link.url"
                                class="rounded-md px-3 py-1 text-sm text-gray-400 dark:text-gray-500"
                                v-html="link.label"
                            />
                            <button
                                v-else
                                type="button"
                                :class="link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'"
                                class="rounded-md px-3 py-1 text-sm"
                                @click="router.visit(link.url, { preserveScroll: true })"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    管理員可清除公開主題聊天室記錄。系統必須保留至少一位管理員。
                </p>
            </div>
        </div>
    </AppLayout>
</template>
