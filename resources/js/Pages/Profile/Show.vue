<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FlashBanner from '@/Components/FlashBanner.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import GoogleAccountForm from '@/Pages/Profile/Partials/GoogleAccountForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="個人設定">
        <template #header>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
                    <i class="fas fa-user-cog text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        個人設定
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        管理個人資料、密碼與帳號安全
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <FlashBanner />

                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm />
                </div>

                <GoogleAccountForm />

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
                </div>

                <LogoutOtherBrowserSessionsForm :sessions="sessions" />

                <DeleteUserForm v-if="$page.props.jetstream.hasAccountDeletionFeatures" />
            </div>
        </div>
    </AppLayout>
</template>
