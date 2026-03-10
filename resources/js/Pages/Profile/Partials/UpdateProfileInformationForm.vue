<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    full_name: user.full_name,
    email: user.email,
    academic_title: user.academic_title || '',
    degree: user.degree || '',
    phone_number: user.phone_number || '',
    gender: user.gender || '',
    department: user.department || '',
    photo: null as File | null,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.post(route('profile.update', { _method: 'patch' }))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Full Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.full_name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.full_name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Profile Info Adjustments -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <InputLabel for="academic_title" value="اللقب العلمي" />
                    <select id="academic_title" v-model="form.academic_title" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">اختر اللقب العلمي</option>
                        <option value="Professor">أستاذ (Professor)</option>
                        <option value="Assistant Professor">أستاذ مساعد (Asst. Prof)</option>
                        <option value="Lecturer">مدرس (Lecturer)</option>
                        <option value="Assistant Lecturer">مدرس مساعد (Asst. Lecturer)</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.academic_title" />
                </div>
                <div>
                    <InputLabel for="degree" value="الشهادة الأكاديمية" />
                    <select id="degree" v-model="form.degree" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">اختر الشهادة الأكاديمية</option>
                        <option value="Ph.D">دكتوراه (Ph.D)</option>
                        <option value="Master">ماجستير (Master's)</option>
                        <option value="Bachelor">بكالوريوس (Bachelor's)</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.degree" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <InputLabel for="department" value="القسم / الكلية" />
                    <TextInput id="department" type="text" class="mt-1 block w-full" v-model="form.department" />
                    <InputError class="mt-2" :message="form.errors.department" />
                </div>
                <div>
                    <InputLabel for="phone_number" value="رقم الهاتف" />
                    <TextInput id="phone_number" type="tel" class="mt-1 block w-full" v-model="form.phone_number" dir="ltr" />
                    <InputError class="mt-2" :message="form.errors.phone_number" />
                </div>
            </div>

            <div class="mt-6">
                <InputLabel for="gender" value="الجنس" />
                <select id="gender" v-model="form.gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">اختر الجنس</option>
                    <option value="male">ذكر</option>
                    <option value="female">أنثى</option>
                </select>
                <InputError class="mt-2" :message="form.errors.gender" />
            </div>

            <div class="mt-6">
                <InputLabel for="photo" value="صورة الملف الشخصي" />
                <div class="flex items-center gap-4 mt-1">
                    <div v-if="user.photo_path" class="w-16 h-16 rounded-xl overflow-hidden shadow-sm shrink-0 border border-gray-200">
                        <img :src="'/storage/' + user.photo_path" class="w-full h-full object-cover">
                    </div>
                    <input id="photo" type="file" @input="form.photo = ($event.target as HTMLInputElement).files?.[0] || null" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none p-2">
                </div>
                <InputError class="mt-2" :message="form.errors.photo" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
