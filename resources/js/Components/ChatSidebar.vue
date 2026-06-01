<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import axios from 'axios';

const props = defineProps({
    themes: { type: Array, default: () => [] },
    privateRooms: { type: Array, default: () => [] },
    currentChatRoom: { type: Object, default: null },
    roomMode: { type: String, default: 'theme' },
    canDelete: { type: Boolean, default: false },
});

const themeIcon = (slug) => ({
    work: 'fas fa-briefcase',
    study: 'fas fa-graduation-cap',
    creative: 'fas fa-lightbulb',
    daily: 'fas fa-comments',
}[slug] || 'fas fa-comment');

const isThemeActive = (slug) => props.roomMode === 'theme' && props.currentChatRoom?.slug === slug;
const isPrivateActive = (id) => props.roomMode === 'private' && props.currentChatRoom?.id === id;

const switchTheme = (slug) => {
    router.visit(route('chat.theme', { theme: slug }), { preserveState: false, replace: true });
};

const openPrivate = (id) => {
    router.visit(route('chat.private.show', id), { preserveState: false, replace: true });
};

// 建立私人房 modal
const showCreate = ref(false);
const createForm = ref({ name: '', description: '' });
const creating = ref(false);
const createError = ref('');

const openCreate = () => {
    createForm.value = { name: '', description: '' };
    createError.value = '';
    showCreate.value = true;
};

const submitCreate = () => {
    creating.value = true;
    createError.value = '';
    router.post(route('chat.private.store'), createForm.value, {
        onSuccess: () => {
            showCreate.value = false;
        },
        onError: (errors) => {
            createError.value = errors.name || errors.description || '建立失敗，請再試一次。';
        },
        onFinish: () => {
            creating.value = false;
        },
    });
};

// 邀請連結 modal
const showInvite = ref(false);
const inviteUrl = ref('');
const inviteLoading = ref(false);
const inviteError = ref('');
const copied = ref(false);

const openInvite = async () => {
    if (!props.currentChatRoom?.id) {
        return;
    }

    showInvite.value = true;
    inviteUrl.value = '';
    inviteError.value = '';
    copied.value = false;
    inviteLoading.value = true;

    try {
        const { data } = await axios.post(
            route('chat.private.invitations.store', props.currentChatRoom.id)
        );
        inviteUrl.value = data.accept_url;
    } catch (e) {
        inviteError.value = e.response?.status === 403
            ? '只有房主可以產生邀請連結。'
            : '產生邀請連結失敗，請再試一次。';
    } finally {
        inviteLoading.value = false;
    }
};

const copyInvite = async () => {
    try {
        await navigator.clipboard.writeText(inviteUrl.value);
        copied.value = true;
    } catch {
        copied.value = false;
    }
};

// 關閉私人房
const showCloseConfirm = ref(false);
const closing = ref(false);
const closeError = ref('');

const openCloseConfirm = () => {
    closeError.value = '';
    showCloseConfirm.value = true;
};

const confirmCloseRoom = () => {
    if (!props.currentChatRoom?.id) {
        return;
    }

    closing.value = true;
    closeError.value = '';

    router.delete(route('chat.private.destroy', props.currentChatRoom.id), {
        preserveState: false,
        onSuccess: () => {
            showCloseConfirm.value = false;
        },
        onError: () => {
            closeError.value = '關閉聊天室失敗，請再試一次。';
        },
        onFinish: () => {
            closing.value = false;
        },
    });
};

watch(
    () => props.currentChatRoom?.id,
    () => {
        showCloseConfirm.value = false;
        closeError.value = '';
    },
);
</script>

<template>
    <aside class="w-60 flex-shrink-0 flex flex-col bg-gray-100 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
        <!-- 區塊 A：公開主題（僅公開聊天） -->
        <div v-if="roomMode === 'theme'" class="p-3">
            <h3 class="px-2 mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                公開主題
            </h3>
            <button
                v-for="theme in themes"
                :key="theme.id"
                @click="switchTheme(theme.slug)"
                :class="[
                    'w-full flex items-center gap-2 px-3 py-2 mb-1 text-sm rounded-lg transition-colors',
                    isThemeActive(theme.slug)
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
                ]"
            >
                <i :class="themeIcon(theme.slug)"></i>
                {{ theme.name }}
            </button>
        </div>

        <!-- 區塊 B：私人聊天室（僅私人聊天） -->
        <div v-if="roomMode === 'private'" class="p-3">
            <div class="flex items-center justify-between px-2 mb-2">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    私人聊天室
                </h3>
                <button
                    @click="openCreate"
                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                    title="建立新房"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <p v-if="privateRooms.length === 0" class="px-2 py-2 text-xs text-gray-500 dark:text-gray-400">
                尚無私人房，點選＋建立第一個並邀請朋友。
            </p>

            <button
                v-for="room in privateRooms"
                :key="room.id"
                @click="openPrivate(room.id)"
                :class="[
                    'w-full flex items-center justify-between gap-2 px-3 py-2 mb-1 text-sm rounded-lg transition-colors',
                    isPrivateActive(room.id)
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
                ]"
            >
                <span class="truncate"><i class="fas fa-user-group mr-2"></i>{{ room.name }}</span>
                <span class="text-xs opacity-75">{{ room.member_count }}人</span>
            </button>
        </div>

        <!-- 邀請／關閉（僅在私人房內顯示） -->
        <div v-if="roomMode === 'private' && currentChatRoom?.id" class="mt-auto p-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
            <button
                @click="openInvite"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors"
            >
                <i class="fas fa-user-plus"></i>
                邀請成員
            </button>
            <button
                v-if="canDelete"
                type="button"
                @click="openCloseConfirm"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
            >
                <i class="fas fa-door-closed"></i>
                關閉聊天室
            </button>
        </div>
    </aside>

    <!-- 建立私人房 modal -->
    <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showCreate = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-user-group mr-2 text-blue-600"></i>建立私人聊天室
            </h3>
            <form @submit.prevent="submitCreate" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">房間名稱</label>
                    <input
                        v-model="createForm.name"
                        type="text"
                        required
                        maxlength="255"
                        placeholder="例如：週末讀書會"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">描述（選填）</label>
                    <input
                        v-model="createForm.description"
                        type="text"
                        maxlength="1000"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                <p v-if="createError" class="text-sm text-red-600 dark:text-red-400">{{ createError }}</p>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">
                        取消
                    </button>
                    <button type="submit" :disabled="creating" class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">
                        <i v-if="creating" class="fas fa-spinner fa-spin mr-1"></i>
                        建立
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 關閉私人房確認 -->
    <div v-if="showCloseConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showCloseConfirm = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                <i class="fas fa-door-closed mr-2 text-red-600"></i>關閉聊天室？
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                將永久刪除「{{ currentChatRoom?.name }}」的所有訊息、成員與邀請連結，此操作無法復原。
            </p>
            <p v-if="closeError" class="text-sm text-red-600 dark:text-red-400 mb-3">{{ closeError }}</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showCloseConfirm = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">
                    取消
                </button>
                <button
                    type="button"
                    :disabled="closing"
                    @click="confirmCloseRoom"
                    class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white disabled:opacity-50"
                >
                    <i v-if="closing" class="fas fa-spinner fa-spin mr-1"></i>
                    確認關閉
                </button>
            </div>
        </div>
    </div>

    <!-- 邀請連結 modal -->
    <div v-if="showInvite" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showInvite = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-user-plus mr-2 text-green-600"></i>邀請成員
            </h3>

            <p v-if="inviteLoading" class="text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-spinner fa-spin mr-1"></i>產生邀請連結中…
            </p>
            <p v-else-if="inviteError" class="text-sm text-red-600 dark:text-red-400">{{ inviteError }}</p>
            <template v-else>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">複製以下連結分享給朋友（連結 7 天內有效）：</p>
                <div class="flex gap-2">
                    <input
                        :value="inviteUrl"
                        readonly
                        class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                    />
                    <button @click="copyInvite" class="px-3 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                        <i :class="copied ? 'fas fa-check' : 'fas fa-copy'"></i>
                    </button>
                </div>
                <p v-if="copied" class="mt-2 text-xs text-green-600 dark:text-green-400">已複製到剪貼簿。</p>
            </template>

            <div class="flex justify-end pt-4">
                <button @click="showInvite = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">
                    關閉
                </button>
            </div>
        </div>
    </div>
</template>
