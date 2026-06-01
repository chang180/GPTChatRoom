<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) {
        return;
    }

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            個人資料
        </template>

        <template #description>
            更新顯示名稱、電子郵件與大頭貼。
        </template>

        <template #form>
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="大頭貼" />

                <div v-show="! photoPreview" class="mt-2">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full h-20 w-20 object-cover ring-2 ring-gray-200 dark:ring-gray-600">
                </div>

                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center ring-2 ring-blue-400"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <SecondaryButton type="button" @click.prevent="selectNewPhoto">
                        <i class="fas fa-camera mr-1"></i> 選擇照片
                    </SecondaryButton>

                    <SecondaryButton
                        v-if="user.profile_photo_path"
                        type="button"
                        @click.prevent="deletePhoto"
                    >
                        移除照片
                    </SecondaryButton>
                </div>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="顯示名稱" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    required
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="電子郵件" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null" class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        您的電子郵件尚未驗證。
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline font-medium hover:text-amber-900 dark:hover:text-amber-100 ms-1"
                            @click.prevent="sendEmailVerification"
                        >
                            按此重新寄送驗證信
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                        新的驗證連結已寄至您的信箱。
                    </div>
                </div>
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                已儲存。
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-1"></i>
                儲存
            </PrimaryButton>
        </template>
    </FormSection>
</template>
