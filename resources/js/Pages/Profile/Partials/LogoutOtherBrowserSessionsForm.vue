<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ActionSection from '@/Components/ActionSection.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    sessions: Array,
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmLogout = () => {
    confirmingLogout.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title>
            瀏覽器工作階段
        </template>

        <template #description>
            管理並登出其他裝置或瀏覽器上的登入狀態。
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                若有需要，可一次登出所有其他裝置上的工作階段。下方為近期登入紀錄（可能不完整）。若懷疑帳號外洩，請一併更新密碼。
            </div>

            <div v-if="sessions.length > 0" class="mt-5 space-y-4">
                <div
                    v-for="(session, i) in sessions"
                    :key="i"
                    class="flex items-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600"
                >
                    <div class="text-gray-500 dark:text-gray-400">
                        <i v-if="session.agent.is_desktop" class="fas fa-desktop text-xl"></i>
                        <i v-else class="fas fa-mobile-alt text-xl"></i>
                    </div>

                    <div class="ms-3 min-w-0">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                            {{ session.agent.platform || '未知平台' }} · {{ session.agent.browser || '未知瀏覽器' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ session.ip_address }}
                            <span v-if="session.is_current_device" class="text-green-600 dark:text-green-400 font-semibold ms-1">（此裝置）</span>
                            <span v-else class="ms-1">· 上次活動 {{ session.last_active }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center mt-5">
                <PrimaryButton @click="confirmLogout">
                    <i class="fas fa-sign-out-alt mr-1"></i> 登出其他工作階段
                </PrimaryButton>

                <ActionMessage :on="form.recentlySuccessful" class="ms-3">
                    已完成。
                </ActionMessage>
            </div>

            <DialogModal :show="confirmingLogout" @close="closeModal">
                <template #title>
                    登出其他工作階段
                </template>

                <template #content>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        請輸入密碼以確認登出所有其他裝置上的工作階段。
                    </p>

                    <div class="mt-4">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="請輸入密碼"
                            autocomplete="current-password"
                            @keyup.enter="logoutOtherBrowserSessions"
                        />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        取消
                    </SecondaryButton>

                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="logoutOtherBrowserSessions"
                    >
                        確認登出
                    </PrimaryButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
