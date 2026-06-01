<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title>
            刪除帳號
        </template>

        <template #description>
            永久刪除此帳號與所有相關資料。
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                帳號刪除後將無法復原。若需保留資料，請在刪除前先自行備份。
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    <i class="fas fa-trash-alt mr-1"></i> 刪除帳號
                </DangerButton>
            </div>

            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title>
                    確認刪除帳號
                </template>

                <template #content>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        確定要刪除帳號嗎？此操作無法復原。請輸入密碼以確認。
                    </p>

                    <div class="mt-4">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="請輸入密碼"
                            autocomplete="current-password"
                            @keyup.enter="deleteUser"
                        />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        取消
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        確認刪除
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
