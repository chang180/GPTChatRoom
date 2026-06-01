<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();
const oauth = computed(() => page.props.googleOAuth ?? { enabled: false, disabledReason: '' });
const account = computed(() => page.props.googleAccount ?? { linked: false, hasPassword: false });

// 綁定走整頁 POST（controller 會 302 導向 Google），需帶 CSRF token。
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const unlinkForm = useForm({});

const unlink = () => {
    unlinkForm.delete(route('user.google.unlink'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Google 帳號
        </template>

        <template #description>
            綁定 Google 帳號後可使用 Google 快速登入。Google 登入／註冊僅在已佈署環境提供。
        </template>

        <template #content>
            <!-- 未啟用（含本機 local）：顯示說明，不提供按鈕（ADR-007） -->
            <div
                v-if="!oauth.enabled"
                class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/40 p-4"
            >
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                    <i class="fab fa-google mr-1"></i> Google 綁定暫不可用
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ oauth.disabledReason }}
                </p>
            </div>

            <!-- 已啟用 -->
            <div v-else>
                <div v-if="account.linked" class="flex items-center gap-2 text-sm font-medium text-green-700 dark:text-green-400">
                    <i class="fas fa-check-circle"></i>
                    此帳號已綁定 Google。
                </div>
                <div v-else class="text-sm text-gray-600 dark:text-gray-300">
                    尚未綁定 Google 帳號。
                </div>

                <p
                    v-if="account.linked && !account.hasPassword"
                    class="mt-2 text-xs text-amber-600 dark:text-amber-400"
                >
                    你目前僅以 Google 登入；解除綁定前請先於上方設定密碼，以免無法登入。
                </p>

                <InputError :message="unlinkForm.errors.password" class="mt-2" />
                <InputError :message="unlinkForm.errors.google" class="mt-2" />

                <div class="mt-4 flex items-center">
                    <ActionMessage :on="unlinkForm.recentlySuccessful" class="me-3">
                        已更新。
                    </ActionMessage>

                    <!-- 綁定：native form POST → 302 導向 Google -->
                    <form v-if="!account.linked" method="POST" :action="route('user.google.link')">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <PrimaryButton type="submit">
                            <i class="fab fa-google mr-2"></i> 綁定 Google 帳號
                        </PrimaryButton>
                    </form>

                    <!-- 解除綁定：Inertia DELETE -->
                    <DangerButton
                        v-else
                        :class="{ 'opacity-25': unlinkForm.processing }"
                        :disabled="unlinkForm.processing"
                        @click="unlink"
                    >
                        解除綁定
                    </DangerButton>
                </div>
            </div>
        </template>
    </ActionSection>
</template>
