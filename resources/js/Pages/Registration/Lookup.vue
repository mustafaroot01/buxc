<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import QRCodeVue3 from 'qrcode-vue3';
import { ref } from 'vue';

const props = defineProps<{
    result?: {
        status: string;
        full_name: string;
        id: string;
        qr_payload?: string;
    } | null;
    searchedId?: string;
}>();

const searchId = ref(props.searchedId || '');

const search = () => {
    if (!searchId.value.trim()) return;
    router.get(route('registration.lookup'), { id: searchId.value.trim() }, { preserveState: true, replace: true });
};

const downloadQr = () => {
    const qrEl = document.getElementById('lookup-qr-code');
    if (!qrEl) return;
    const img = qrEl.querySelector('img') as HTMLImageElement | null;
    const canvas = qrEl.querySelector('canvas') as HTMLCanvasElement | null;
    const dataUrl = img?.src || canvas?.toDataURL('image/png') || '';
    if (!dataUrl) return;
    const link = document.createElement('a');
    link.href = dataUrl;
    link.download = `QR_${props.result?.id || 'student'}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Head title="استرجاع رمز QR" />

    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-emerald-50 flex flex-col" dir="rtl">

        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-10">
            <div class="max-w-2xl mx-auto px-4 py-4">
                <h1 class="text-lg font-black text-gray-900">استرجاع رمز QR</h1>
                <p class="text-xs text-gray-500 mt-0.5">ادخل رقمك الجامعي للحصول على رمز QR الخاص بك</p>
            </div>
        </header>

        <main class="flex-1 max-w-2xl mx-auto w-full px-4 py-10 space-y-6">

            <!-- Search Box -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-4">البحث بالرقم الجامعي</h2>
                <form @submit.prevent="search" class="flex gap-3">
                    <input v-model="searchId" type="text" dir="ltr"
                        class="flex-1 border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm font-mono text-left focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all"
                        placeholder="ادخل رقمك الجامعي او رقم هاتفك" />
                    <button type="submit"
                        class="px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-teal-600 to-emerald-500 rounded-xl hover:from-teal-700 hover:to-emerald-600 transition-all whitespace-nowrap">
                        بحث
                    </button>
                </form>
            </div>

            <!-- No result yet -->
            <div v-if="searchedId && !result"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-gray-700 font-bold text-lg mb-1">الرقم غير مسجل</p>
                <p class="text-gray-400 text-sm">لم يتم العثور على رقم <span class="font-mono font-bold" dir="ltr">{{ searchedId }}</span> في النظام.</p>
            </div>

            <!-- Pending -->
            <div v-if="result && result.status === 'pending'"
                class="bg-white rounded-2xl border border-amber-100 shadow-sm p-10 text-center">
                <div class="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-gray-800 font-bold text-lg mb-1">{{ result.full_name }}</p>
                <p class="text-amber-600 font-semibold text-sm mb-1">طلبك قيد المراجعة</p>
                <p class="text-gray-400 text-xs">سيتم تفعيل رمز QR الخاص بك بعد موافقة الادارة. تفقد هذه الصفحة لاحقاً.</p>
            </div>

            <!-- Rejected -->
            <div v-if="result && result.status === 'rejected'"
                class="bg-white rounded-2xl border border-rose-100 shadow-sm p-10 text-center">
                <div class="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-gray-800 font-bold text-lg mb-1">{{ result.full_name }}</p>
                <p class="text-rose-600 font-semibold text-sm">تم رفض طلبك</p>
                <p class="text-gray-400 text-xs mt-1">تواصل مع الادارة للمزيد من المعلومات.</p>
            </div>

            <!-- Approved + QR -->
            <div v-if="result && result.status === 'approved' && result.qr_payload"
                class="space-y-5">
                <!-- Student card -->
                <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-black text-gray-900 text-base">{{ result.full_name }}</p>
                        <p class="text-sm text-gray-400 font-mono">{{ result.id }}</p>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">مسجل في النظام</span>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                    <h3 class="text-base font-bold text-gray-800 mb-1">رمز QR الخاص بك</h3>
                    <p class="text-sm text-gray-400 mb-6">احتفظ بهذا الرمز. سيُستخدم لتسجيل حضورك في المحاضرات.</p>
                    <div id="lookup-qr-code" class="inline-block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-6">
                        <QRCodeVue3
                            :width="220"
                            :height="220"
                            :value="result.qr_payload"
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
                </div>
            </div>

        </main>

        <footer class="py-4 text-center text-xs text-gray-400">
            نظام الحضور الذكي
        </footer>
    </div>
</template>
