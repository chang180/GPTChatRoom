<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => ! enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (! twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: 'confirmTwoFactorAuthentication',
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            雙因素驗證
        </template>

        <template #description>
            登入時除密碼外，另需驗證碼以提升帳號安全性。
        </template>

        <template #content>
            <h3 v-if="twoFactorEnabled && ! confirming" class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <i class="fas fa-shield-alt text-green-600 dark:text-green-400 mr-1"></i>
                已啟用雙因素驗證
            </h3>

            <h3 v-else-if="twoFactorEnabled && confirming" class="text-lg font-medium text-gray-900 dark:text-gray-100">
                請完成雙因素驗證設定
            </h3>

            <h3 v-else class="text-lg font-medium text-gray-900 dark:text-gray-100">
                尚未啟用雙因素驗證
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p>
                    啟用後，登入時需輸入驗證應用程式（如 Google Authenticator）產生的一次性驗證碼。
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p v-if="confirming" class="font-semibold text-gray-800 dark:text-gray-200">
                            請使用驗證應用程式掃描下方 QR Code，或手動輸入設定金鑰，並輸入產生的驗證碼以完成啟用。
                        </p>

                        <p v-else>
                            雙因素驗證已啟用。可使用驗證應用程式掃描 QR Code 或輸入設定金鑰。
                        </p>
                    </div>

                    <div class="mt-4 p-2 inline-block bg-white rounded-lg" v-html="qrCode" />

                    <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold text-gray-800 dark:text-gray-200">
                            設定金鑰：<span class="font-mono" v-html="setupKey"></span>
                        </p>
                    </div>

                    <div v-if="confirming" class="mt-4">
                        <InputLabel for="code" value="驗證碼" />

                        <TextInput
                            id="code"
                            v-model="confirmationForm.code"
                            type="text"
                            name="code"
                            class="block mt-1 w-full max-w-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            @keyup.enter="confirmTwoFactorAuthentication"
                        />

                        <InputError :message="confirmationForm.errors.code" class="mt-2" />
                    </div>
                </div>

                <div v-if="recoveryCodes.length > 0 && ! confirming">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold text-gray-800 dark:text-gray-200">
                            請將下列復原碼妥善保存（建議使用密碼管理器）。遺失驗證裝置時可用於登入。
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div v-for="code in recoveryCodes" :key="code" class="text-gray-800 dark:text-gray-200">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <div v-if="! twoFactorEnabled">
                    <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
                        <PrimaryButton type="button" :class="{ 'opacity-25': enabling }" :disabled="enabling">
                            <i class="fas fa-lock mr-1"></i> 啟用
                        </PrimaryButton>
                    </ConfirmsPassword>
                </div>

                <div v-else class="flex flex-wrap gap-2">
                    <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
                        <PrimaryButton
                            v-if="confirming"
                            type="button"
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                        >
                            確認
                        </PrimaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
                        <SecondaryButton v-if="recoveryCodes.length > 0 && ! confirming">
                            重新產生復原碼
                        </SecondaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="showRecoveryCodes">
                        <SecondaryButton v-if="recoveryCodes.length === 0 && ! confirming">
                            顯示復原碼
                        </SecondaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <SecondaryButton
                            v-if="confirming"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                        >
                            取消
                        </SecondaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <DangerButton
                            v-if="! confirming"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                        >
                            停用
                        </DangerButton>
                    </ConfirmsPassword>
                </div>
            </div>
        </template>
    </ActionSection>
</template>
