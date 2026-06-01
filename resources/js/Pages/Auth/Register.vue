<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import FlashBanner from '@/Components/FlashBanner.vue';
import GoogleAuthButton from '@/Components/GoogleAuthButton.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="註冊帳戶 - GPT Chat Room" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <FlashBanner />

        <!-- 一般錯誤訊息 -->
        <div v-if="form.errors.message || form.hasErrors" class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 mr-2"></i>
                <p class="text-sm font-medium text-red-800 dark:text-red-300">
                    {{ form.errors.message || '註冊失敗，請檢查輸入的資訊是否正確' }}
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- 歡迎標題 -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                    <i class="fas fa-user-plus mr-2 text-purple-600"></i>
                    建立您的帳戶
                </h2>
                <p class="text-gray-600 dark:text-gray-300 text-sm">開始您的智能對話之旅</p>
            </div>

            <!-- 姓名輸入框 -->
            <div class="relative">
                <InputLabel for="name" value="姓名" class="text-gray-700 dark:text-gray-300 font-medium" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                        placeholder="請輸入您的姓名"
                        required
                        autofocus
                        autocomplete="name"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email 輸入框 -->
            <div class="relative">
                <InputLabel for="email" value="電子郵件" class="text-gray-700 dark:text-gray-300 font-medium" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                        placeholder="請輸入您的電子郵件"
                        required
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- 密碼輸入框 -->
            <div class="relative">
                <InputLabel for="password" value="密碼" class="text-gray-700 dark:text-gray-300 font-medium" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                        placeholder="請輸入您的密碼"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- 確認密碼輸入框 -->
            <div class="relative">
                <InputLabel for="password_confirmation" value="確認密碼" class="text-gray-700 dark:text-gray-300 font-medium" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                        placeholder="請再次輸入密碼"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <!-- 服務條款 -->
            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="relative">
                <label class="flex items-start">
                    <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="mt-1" />
                    <div class="ms-3 text-sm text-gray-600 dark:text-gray-300">
                        <i class="fas fa-check-circle mr-1 text-green-500"></i>
                        我同意 
                        <a target="_blank" :href="route('terms.show')" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 font-medium transition-colors">
                            服務條款
                        </a> 
                        和 
                        <a target="_blank" :href="route('policy.show')" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 font-medium transition-colors">
                            隱私政策
                        </a>
                    </div>
                </label>
                <InputError class="mt-2" :message="form.errors.terms" />
            </div>

            <!-- 註冊按鈕 -->
            <div class="pt-4">
                <PrimaryButton 
                    class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg" 
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                    :disabled="form.processing"
                >
                    <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-user-plus mr-2"></i>
                    {{ form.processing ? '註冊中...' : '立即註冊' }}
                </PrimaryButton>
            </div>

            <!-- Google 註冊 / 停用說明 -->
            <GoogleAuthButton label="使用 Google 註冊" intent="register" />

            <!-- 登入連結 -->
            <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-600">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    已有帳戶？
                    <Link :href="route('login')" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 font-medium transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i>
                        立即登入
                    </Link>
                </p>
            </div>
        </form>
    </AuthenticationCard>
</template>
