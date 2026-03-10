<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { SaveIcon, SettingsIcon } from 'lucide-vue-next';

const props = defineProps<{
    settings: Record<string, string>;
}>();

const form = useForm({
    settings: {
        school_name: props.settings.school_name || 'اسم المؤسسة',
        academic_year: props.settings.academic_year || '2026-2027',
        attendance_warning_threshold: props.settings.attendance_warning_threshold || '5',
        allow_teacher_qr_generation: props.settings.allow_teacher_qr_generation || '1',
        login_text: props.settings.login_text || 'مرحباً بك في نظام إدارة الحضور',
    },
    login_logo: null as File | null,
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="إعدادات النظام" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="bg-gray-50 border-b border-gray-100">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between py-6">
                    <div class="flex items-center">
                        <SettingsIcon class="w-8 h-8 mr-3 text-indigo-600" />
                        <div>
                            <h2 class="text-2xl font-black leading-tight text-gray-900 tracking-tight">
                                إعدادات النظام
                            </h2>
                            <p class="text-sm font-medium text-gray-500 mt-1">
                                إدارة الإعدادات العامة والتكوينات المتعلقة بالمؤسسة التعليمية.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 bg-[#fafafa] min-h-screen">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                
                <!-- Success Message -->
                <transition name="fade">
                    <div v-if="$page.props.flash?.success" class="mb-6 bg-emerald-50 border border-emerald-200 shadow-sm text-emerald-700 px-6 py-4 rounded-2xl flex items-center">
                        <svg class="w-6 h-6 ml-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="font-bold">{{ $page.props.flash.success }}</span>
                    </div>
                </transition>
                
                <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100/60 overflow-hidden relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-500"></div>

                    <form @submit.prevent="submit" class="divide-y divide-gray-100/80">
                        
                        <!-- Section 1: General Details -->
                        <div class="p-8">
                            <h3 class="text-lg font-black text-gray-900 flex items-center mb-6">
                                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center ml-3 border border-indigo-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </span>
                                البيانات الأساسية
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- School Name -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">اسم المؤسسة التعليمية</label>
                                    <input type="text" v-model="form.settings.school_name" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                    <p class="text-[12px] text-gray-400 font-medium italic">سيظهر هذا الاسم في أعلى جميع الصفحات (Header)، وفي صفحة تسجيل الدخول والتقارير.</p>
                                </div>
                                
                                <!-- Academic Year -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">العام الدراسي الحالي</label>
                                    <input type="text" v-model="form.settings.academic_year" placeholder="مثال: 2026-2027" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" dir="ltr">
                                    <p class="text-[12px] text-gray-400 font-medium text-right">صيغة العام الدراسي الفعّال حالياً.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Attendance Rules -->
                        <div class="p-8 bg-slate-50/50">
                            <h3 class="text-lg font-black text-gray-900 flex items-center mb-6">
                                <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center ml-3 border border-teal-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                                قواعد الحضور والغياب
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Warning Threshold -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">حد الإنذار التلقائي (الغياب المتتالي)</label>
                                    <input type="number" min="1" v-model="form.settings.attendance_warning_threshold" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-xl font-mono text-left focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full p-3 transition-colors" dir="ltr">
                                    <p class="text-[12px] text-gray-500 font-medium">عدد محاضرات الغياب المتتالية قبل إصدار إنذار تلقائي للطالب.</p>
                                </div>
                                
                                <!-- QR Generation Permission -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">صلاحية توليد بطاقات QR</label>
                                    <select v-model="form.settings.allow_teacher_qr_generation" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full p-3 transition-colors">
                                        <option value="1">مُفعل - يمكن للأساتذة توليد بطاقات طلابهم</option>
                                        <option value="0">مُعطل - الإدارة فقط مخولة بتوليد البطاقات</option>
                                    </select>
                                    <p class="text-[12px] text-gray-500 font-medium">تحكم بمن يمكنه تصدير وإنشاء رمز استجابة للطالب.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Login Page Customization -->
                        <div class="p-8">
                            <h3 class="text-lg font-black text-gray-900 flex items-center mb-6">
                                <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center ml-3 border border-orange-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                </span>
                                إعدادات صفحة تسجيل الدخول
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Login Text -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">نص الترحيب في صفحة الدخول</label>
                                    <input type="text" v-model="form.settings.login_text" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 block w-full p-3 transition-colors">
                                    <p class="text-[12px] text-gray-400 font-medium text-right">سيظهر هذا النص تحت الشعار في صفحة تسجيل الدخول.</p>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">شعار المؤسسة (System Logo)</label>
                                    <div class="flex items-center gap-4">
                                        <div v-if="props.settings.login_logo" class="w-16 h-16 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden flex-shrink-0">
                                            <img :src="props.settings.login_logo" alt="Logo" class="max-w-full max-h-full object-contain">
                                        </div>
                                        <div v-else class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    <input type="file" @input="form.login_logo = ($event.target as HTMLInputElement).files?.[0] || null" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition-all cursor-pointer" accept="image/*">
                                    </div>
                                    <p class="text-[12px] text-gray-400 font-medium italic">سيحل هذا الشعار محل الشعار الافتراضي في أعلى النظام وفي صفحة الدخول.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="p-6 bg-gray-50 flex items-center justify-end rounded-b-[1.5rem]">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-all">
                                <SaveIcon class="w-5 h-5 ml-2" />
                                حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
