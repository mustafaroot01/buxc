<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { MailIcon, LockIcon, AlertCircleIcon, ArrowRightIcon, CheckCircle2Icon } from 'lucide-vue-next';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const page = usePage();
const settings = page.props.settings as any;

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="تسجيل الدخول" />

    <div class="min-h-screen flex items-center justify-center bg-[#f8fafc] overflow-hidden relative font-sans" dir="rtl">
        <!-- Background Elements -->
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-teal-200/40 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-emerald-200/40 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute top-[20%] right-[10%] w-[15%] h-[15%] bg-teal-100/30 rounded-full blur-[80px] pointer-events-none"></div>

        <div class="w-full max-w-[440px] px-6 py-12 relative z-10">
            <!-- Branding -->
            <div class="text-center mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div v-if="settings?.login_logo" class="inline-block mb-6 relative">
                    <div class="absolute inset-0 bg-teal-500/10 blur-2xl rounded-full scale-110"></div>
                    <img :src="settings.login_logo" alt="Logo" class="h-28 w-28 object-contain relative z-10 drop-shadow-sm mx-auto">
                </div>
                <div v-else class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-tr from-teal-600 to-emerald-500 rounded-3xl shadow-xl shadow-teal-200 mb-6 rotate-3">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                
                <h1 class="text-3xl font-black text-gray-900 mb-2 tracking-tight">
                    {{ settings?.school_name || 'نظام إدارة الحضور' }}
                </h1>
                <p class="text-gray-500 font-medium text-base">
                    {{ settings?.login_text || 'مرحباً بك مجدداً، يرجى تسجيل الدخول' }}
                </p>
            </div>

            <!-- Login Card -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(30,41,59,0.06)] border border-white/60 p-10 animate-in fade-in zoom-in-95 duration-700 delay-150">
                <div v-if="status" class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-2xl text-sm font-bold border border-emerald-100 flex items-center">
                    <CheckCircle2Icon class="w-5 h-5 ml-2" />
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 mr-1 block">البريد الإلكتروني</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <MailIcon class="h-5 w-5 text-gray-400 group-focus-within:text-teal-500 transition-colors" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full bg-gray-50 border-gray-200 text-gray-900 rounded-2xl py-3.5 pr-11 pl-4 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all placeholder:text-gray-400"
                                placeholder="name@school.com"
                            />
                        </div>
                        <InputError class="mt-1 mr-1" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-bold text-gray-700 mr-1">كلمة المرور</label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <LockIcon class="h-5 w-5 text-gray-400 group-focus-within:text-teal-500 transition-colors" />
                            </div>
                            <input
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                class="w-full bg-gray-50 border-gray-200 text-gray-900 rounded-2xl py-3.5 pr-11 pl-4 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all placeholder:text-gray-400"
                                placeholder="••••••••"
                            />
                        </div>
                        <InputError class="mt-1 mr-1" :message="form.errors.password" />
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between px-1">
                        <label class="flex items-center cursor-pointer select-none">
                            <Checkbox name="remember" v-model:checked="form.remember" class="rounded-md border-gray-300 text-teal-600 focus:ring-teal-500" />
                            <span class="ms-2 text-sm font-semibold text-gray-600">تذكرني</span>
                        </label>
                        
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-bold text-teal-600 hover:text-teal-700 transition-colors"
                        >
                            نسيت كلمة المرور؟
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-4 px-6 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-2xl font-bold text-lg shadow-xl shadow-teal-200 flex items-center justify-center gap-3 transition-all active:scale-[0.98] disabled:opacity-50"
                    >
                        <span>دخول للمنصة</span>
                        <ArrowRightIcon v-if="!form.processing" class="w-5 h-5 rotate-180" />
                        <div v-else class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-10 text-center animate-in fade-in slide-in-from-top-4 duration-700 delay-300">
                <p class="text-gray-500 font-medium">
                    &copy; {{ new Date().getFullYear() }} {{ settings?.school_name || 'اسم المؤسسة' }}. جميع الحقوق محفوظة.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap');

.font-sans {
    font-family: 'Tajawal', sans-serif;
}

input:focus {
    outline: none;
}
</style>
