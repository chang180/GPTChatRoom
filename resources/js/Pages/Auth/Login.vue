<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- 歡迎標題 -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                    <i class="fas fa-sign-in-alt mr-2 text-blue-600"></i>
                    登入您的帳戶
                </h2>
                <p class="text-gray-600 dark:text-gray-300 text-sm">開始您的智能對話之旅</p>
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
                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="請輸入您的電子郵件"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password 輸入框 -->
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
                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        placeholder="請輸入您的密碼"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- 記住我選項 -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">
                        <i class="fas fa-check-circle mr-1"></i>
                        記住我
                    </span>
                </label>
                
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                >
                    <i class="fas fa-key mr-1"></i>
                    忘記密碼？
                </Link>
            </div>

            <!-- 登入按鈕 -->
            <div class="pt-4">
                <PrimaryButton 
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg" 
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                    :disabled="form.processing"
                >
                    <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-sign-in-alt mr-2"></i>
                    {{ form.processing ? '登入中...' : '登入' }}
                </PrimaryButton>
            </div>

            <!-- 註冊連結 -->
            <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-600">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    還沒有帳戶？
                    <Link :href="route('register')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors">
                        <i class="fas fa-user-plus mr-1"></i>
                        立即註冊
                    </Link>
                </p>
            </div>
        </form>
    </AuthenticationCard>
</template>
