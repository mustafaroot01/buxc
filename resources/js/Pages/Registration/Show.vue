<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QRCodeVue3 from 'qrcode-vue3';
import { ref } from 'vue';

const props = defineProps<{
    form: any;
    isOpen: boolean;
    success?: boolean;
    submission?: {
        id: string;
        full_name: string;
        student_external_id: string;
        qr_payload: string;
    };
}>();

const submitting = useForm({
    first_name: '',
    second_name: '',
    last_name: '',
    gender: 'male',
    student_external_id: '',
    photo: null as File | null,
});

const submit = () => {
    submitting.post(route('registration.submit', props.form.slug));
};

const downloadQr = () => {
    const qrEl = document.getElementById('success-qr-code');
    if (!qrEl) return;
    const img = qrEl.querySelector('img') as HTMLImageElement | null;
    const canvas = qrEl.querySelector('canvas') as HTMLCanvasElement | null;
    const dataUrl = img?.src || canvas?.toDataURL('image/png') || '';
    if (!dataUrl) return;
    const link = document.createElement('a');
    link.href = dataUrl;
    link.download = `QR_${props.submission?.student_external_id || 'student'}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Head :title="form.title" />

    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-indigo-50 flex flex-col" dir="rtl">

        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-10">
            <div class="max-w-2xl mx-auto px-4 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-black text-gray-900">{{ form.title }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ form.stage?.name }} &bull; {{ form.group?.name }} &bull;
                        {{ form.study_type === 'morning' ? 'صباحي' : 'مسائي' }}
                    </p>
                </div>
                <span :class="isOpen ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200'"
                    class="text-xs font-bold px-3 py-1 rounded-full border">
                    {{ isOpen ? 'الاستمارة مفتوحة' : 'الاستمارة مغلقة' }}
                </span>
            </div>
        </header>

        <main class="flex-1 max-w-2xl mx-auto w-full px-4 py-10">

            <!-- CLOSED STATE -->
            <div v-if="!isOpen" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-black text-gray-800 mb-2">الاستمارة مغلقة</h2>
                <p class="text-gray-500">انتهى وقت التسجيل في هذه الاستمارة. تواصل مع الادارة للمزيد من المعلومات.</p>
            </div>

            <!-- SUCCESS STATE -->
            <div v-else-if="success && submission" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-8 text-center">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900 mb-1">تمت عملية التسجيل</h2>
                    <p class="text-gray-500 text-sm">{{ submission.full_name }}</p>
                    <p class="text-gray-400 text-xs mt-1 font-mono">{{ submission.student_external_id }}</p>
                </div>

                <!-- QR Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                    <h3 class="text-base font-bold text-gray-800 mb-1">رمز QR الخاص بك</h3>
                    <p class="text-sm text-gray-400 mb-6">احتفظ بهذا الرمز. سيُستخدم لتسجيل حضورك في المحاضرات.</p>
                    <div id="success-qr-code" class="inline-block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-6">
                        <QRCodeVue3
                            :width="220"
                            :height="220"
                            :value="submission.qr_payload"
                            :qrOptions="{ typeNumber: '0', mode: 'Byte', errorCorrectionLevel: 'Q' }"
                            :dotsOptions="{ type: 'rounded', color: '#111827' }"
                            :cornersSquareOptions="{ type: 'extra-rounded', color: '#111827' }"
                            :cornersDotOptions="{ type: 'dot', color: '#111827' }"
                        />
                    </div>
                    <div>
                        <button @click="downloadQr"
                            class="inline-flex items-center gap-2 px-8 py-3 text-sm font-bold text-white bg-gradient-to-r from-teal-600 to-emerald-500 rounded-xl shadow-md hover:from-teal-700 hover:to-emerald-600 transition-all">
                            تحميل رمز QR
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">يمكنك ايضا الرجوع لاسترجاع رمز QR لاحقاً من خلال رابط البحث بالرقم الجامعي.</p>
                </div>
            </div>

            <!-- FORM STATE -->
            <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-teal-500 to-indigo-500" />
                <div class="p-7">
                    <div class="mb-7 p-4 bg-teal-50 border border-teal-100 rounded-xl">
                        <p class="text-sm text-teal-800 font-medium leading-relaxed">
                            ادخل معلوماتك بحذر. بعد التسجيل ستتمكن من تحميل رمز QR الخاص بك مباشرة.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">الاسم الاول <span class="text-red-500">*</span></label>
                                <input v-model="submitting.first_name" type="text" required
                                    class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all"
                                    placeholder="الاسم الاول" />
                                <p v-if="submitting.errors.first_name" class="text-red-500 text-xs mt-1">{{ submitting.errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">الاسم الثاني (اسم الاب)</label>
                                <input v-model="submitting.second_name" type="text"
                                    class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all"
                                    placeholder="الاسم الثاني" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">اللقب <span class="text-red-500">*</span></label>
                                <input v-model="submitting.last_name" type="text" required
                                    class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all"
                                    placeholder="اللقب" />
                                <p v-if="submitting.errors.last_name" class="text-red-500 text-xs mt-1">{{ submitting.errors.last_name }}</p>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">الجنس <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="submitting.gender" value="male"
                                        class="w-4 h-4 text-teal-600 border-gray-300 focus:ring-teal-500" />
                                    <span class="text-sm text-gray-700 font-medium">ذكر</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="submitting.gender" value="female"
                                        class="w-4 h-4 text-teal-600 border-gray-300 focus:ring-teal-500" />
                                    <span class="text-sm text-gray-700 font-medium">انثى</span>
                                </label>
                            </div>
                        </div>

                        <!-- Student External ID -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">الرقم الجامعي <span class="text-red-500">*</span></label>
                            <input v-model="submitting.student_external_id" type="text" required dir="ltr"
                                class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm text-left font-mono focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all"
                                placeholder="ادخل رقمك الجامعي" />
                            <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">
                                الرقم الجامعي مرفق في هويتك الجامعية تحت صورتك. في حال لا تمتلك هوية جامعية، ادخل رقم هاتفك.
                            </p>
                            <p v-if="submitting.errors.student_external_id" class="text-red-500 text-xs mt-1">{{ submitting.errors.student_external_id }}</p>
                        </div>

                        <!-- Photo -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">صورة شخصية (اختياري)</label>
                            <input type="file" accept="image/*"
                                @change="submitting.photo = ($event.target as HTMLInputElement).files?.[0] || null"
                                class="w-full border border-gray-200 bg-gray-50 text-sm rounded-xl p-2.5 file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-colors" />
                            <p class="text-xs text-gray-400 mt-1">ستظهر في ملفك الشخصي داخل النظام.</p>
                        </div>

                        <!-- Submit -->
                        <div class="pt-2">
                            <button type="submit" :disabled="submitting.processing"
                                class="w-full py-3.5 text-base font-bold text-white bg-gradient-to-r from-teal-600 to-indigo-600 rounded-xl shadow-lg hover:from-teal-700 hover:to-indigo-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ submitting.processing ? 'جاري التسجيل...' : 'تسجيل والحصول على رمز QR' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>

        <footer class="py-4 text-center text-xs text-gray-400">
            نظام الحضور الذكي
        </footer>
    </div>
</template>
