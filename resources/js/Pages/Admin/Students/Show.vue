<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRightIcon, UserIcon, MapPinIcon, ShieldAlertIcon, CheckCircle2Icon, XCircleIcon, ClockIcon, DownloadIcon, ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next';
import QRCodeVue3 from 'qrcode-vue3';

const props = defineProps<{
    student: {
        id: string;
        first_name: string;
        last_name: string;
        student_external_id: string;
        gender: string;
        study_type: string;
        qr_payload: string;
        photo_path?: string;
        group?: {
            name: string;
            study_type?: string;
            stage?: { name: string };
        };
        warnings?: Array<any>;
        consecutive_absences: number;
    };
    attendances: {
        data: Array<any>;
        links: Array<any>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}>();

const printQrCode = () => {
    window.print();
};
</script>

<template>
    <Head :title="`ملف الطالب: ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        
        <div class="bg-gray-50 border-b border-gray-100 print-hidden">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between py-6">
                    <div class="flex items-center">
                        <Link :href="route('admin.students.index')" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <ArrowRightIcon class="w-5 h-5" />
                        </Link>
                        <h2 class="text-xl font-bold leading-tight text-gray-800 flex items-center">
                            الملف الشخصي للطالب
                            <UserIcon class="w-5 h-5 mr-3 text-indigo-500" />
                        </h2>
                    </div>
                </div>
                <div class="flex justify-end pb-4">
                    <Link :href="route('admin.students.edit', student.id)" class="inline-flex items-center px-5 py-2 text-sm font-bold text-white bg-gradient-to-r from-teal-600 to-emerald-500 rounded-lg shadow-sm hover:from-teal-700 hover:to-emerald-600 hover:shadow-md transition-all">
                        تعديل البيانات
                    </Link>
                </div>
            </div>
        </div>

        <div class="py-10 bg-[#fafafa] min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Active Warning Alert Banner -->
                <div v-if="student.warnings && student.warnings.filter(w => !w.resolved_at).length > 0" class="mb-8 bg-red-50 border-r-4 border-red-500 rounded-2xl p-6 shadow-sm flex items-start print-hidden">
                    <div class="flex-shrink-0">
                        <AlertTriangleIcon class="h-8 w-8 text-red-500 animate-pulse" />
                    </div>
                    <div class="mr-4">
                        <h3 class="text-lg font-bold text-red-800">
                            إنذار تجاوز الحد المسموح للغياب!
                        </h3>
                        <div class="mt-2 text-sm text-red-700 space-y-2">
                            <p v-for="warning in student.warnings.filter(w => !w.resolved_at)" :key="warning.id" class="font-medium">
                                - {{ warning.reason }} (تاريخ الإنذار: {{ new Date(warning.issued_at).toLocaleDateString('ar-IQ') }})
                            </p>
                            <p class="font-bold bg-red-100 inline-block px-3 py-1 rounded-lg mt-2 text-red-800">
                                تنبيه للإدارة: سيتم إنهاء الإنذار تلقائياً بمجرد حضور الطالب المحاضرة القادمة، لكن الغيابات ستبقي مسجلة في ملفه.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse lg:flex-row gap-8">
                    
                    <!-- Left Section: Stats & History (Wider) -->
                    <div class="w-full lg:w-2/3 space-y-8 flex-col flex-col-reverse print-hidden">
                        
                        <!-- Stats Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- Attendance Stat -->
                            <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-gray-100/60 flex items-center justify-between">
                                <div class="text-right">
                                    <p class="text-[13px] font-bold text-gray-500 mb-1">مرات الحضور</p>
                                    <p class="text-3xl font-black text-gray-900 leading-none">
                                        {{ props.attendances.data.filter(a => a.status === 'present').length }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500">
                                    <CheckCircle2Icon class="w-6 h-6" />
                                </div>
                            </div>
                            
                            <!-- Absence Stat -->
                            <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-gray-100/60 flex items-center justify-between">
                                <div class="text-right">
                                    <p class="text-[13px] font-bold text-gray-500 mb-1">مرات الغياب</p>
                                    <p class="text-3xl font-black text-gray-900 leading-none">
                                        {{ props.attendances.data.filter(a => a.status === 'absent').length }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-rose-50 rounded-full flex items-center justify-center text-rose-500">
                                    <XCircleIcon class="w-6 h-6" />
                                </div>
                            </div>
                            
                            <!-- Warnings/Consecutive Stat -->
                            <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-gray-100/60 flex items-center justify-between">
                                <div class="text-right">
                                    <p class="text-[13px] font-bold text-gray-500 mb-1">الغياب المتتالي الحالي</p>
                                    <p class="text-3xl font-black leading-none" :class="student.consecutive_absences > 0 ? 'text-red-500' : 'text-gray-900'">
                                        {{ student.consecutive_absences }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-full flex items-center justify-center" :class="student.consecutive_absences > 0 ? 'bg-red-50 text-red-500' : 'bg-gray-50 text-gray-400'">
                                    <ShieldAlertIcon class="w-6 h-6" />
                                </div>
                            </div>
                            
                        </div>

                        <!-- Full Attendance History -->
                        <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100/60 flex flex-col overflow-hidden">
                            <div class="p-6 border-b border-gray-50">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                    سجل المحاضرات الأخيرة
                                    <MapPinIcon class="w-5 h-5 mr-2 text-indigo-500" />
                                </h3>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-right">
                                    <thead class="bg-gray-50/50 text-gray-500 font-medium">
                                        <tr>
                                            <th class="px-6 py-4">#</th>
                                            <th class="px-6 py-4">المادة</th>
                                            <th class="px-6 py-4">التاريخ والوقت</th>
                                            <th class="px-6 py-4">الحالة</th>
                                            <th class="px-6 py-4 text-left">طريقة التسجيل</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 text-gray-700">
                                        <tr v-for="(attendance, index) in attendances.data" :key="attendance.id" class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-5 font-bold text-gray-400">
                                                {{ (Number(attendances.current_page) - 1) * Number(attendances.per_page) + Number(index) + 1 }}
                                            </td>
                                            <td class="px-6 py-5 font-bold">{{ attendance.lecture?.subject?.name || '---' }}</td>
                                            <td class="px-6 py-5 text-gray-500">
                                                <div class="flex items-center">
                                                    <ClockIcon class="w-4 h-4 ml-1.5 text-gray-400" />
                                                    <span dir="ltr">{{ new Date(attendance.scanned_at || attendance.created_at).toLocaleString('en-GB') }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <span v-if="attendance.status === 'present'" class="inline-flex items-center justify-center px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100">
                                                    حاضر
                                                </span>
                                                <span v-else-if="attendance.status === 'absent'" class="inline-flex items-center justify-center px-3 py-1 bg-rose-50 text-rose-700 text-xs font-bold rounded-lg border border-rose-100">
                                                    غائب
                                                </span>
                                                <span v-else class="inline-flex items-center justify-center px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-100">
                                                    متأخر
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-left text-gray-400">
                                                <span v-if="attendance.check_in_method === 'qr'" class="text-xs font-bold text-indigo-500 bg-teal-50 px-2 py-1 rounded inline-flex">QR مسح</span>
                                                <span v-else class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded inline-flex">يدوي</span>
                                            </td>
                                        </tr>
                                        <tr v-if="attendances.data.length === 0">
                                            <td colspan="5" class="px-6 py-16 text-center text-gray-400 font-medium">
                                                لا يوجد سجل حضور وغياب حتى الآن للطالب.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="p-6 border-t border-gray-50 flex items-center justify-between" v-if="attendances.total > 10">
                                <p class="text-sm text-gray-500">
                                    عرض {{ attendances.data.length }} من أصل {{ attendances.total }} سجل
                                </p>
                                <div class="flex text-left items-center justify-end gap-1" dir="ltr">
                                    <template v-for="(link, index) in attendances.links" :key="index">
                                        <Link 
                                            v-if="link.url"
                                            :href="link.url" 
                                            preserve-scroll
                                            class="w-8 h-8 flex items-center justify-center rounded-lg border text-sm font-medium transition-colors"
                                            :class="link.active ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                            v-html="link.label"
                                        />
                                        <span v-else class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-100 bg-gray-50 text-gray-400 text-sm font-medium" v-html="link.label"></span>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Section: Profile & QR Card (Narrower) -->
                    <div class="w-full lg:w-1/3 space-y-6">
                        
                        <!-- Profile Card -->
                        <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100/60 overflow-hidden print-hidden">
                            <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-500 relative overflow-hidden">
                            </div>
                            
                            <div class="px-6 pb-8 relative text-center">
                                <!-- Avatar -->
                                <div class="w-28 h-28 mx-auto -mt-14 bg-white rounded-full p-1 shadow-md relative z-10">
                                    <template v-if="student.photo_path">
                                        <img :src="'/storage/' + student.photo_path" class="w-full h-full rounded-full object-cover bg-gray-100" />
                                    </template>
                                    <div v-else class="w-full h-full rounded-full bg-indigo-900 flex items-center justify-center text-white font-black text-4xl">
                                        {{ student.first_name.charAt(0) }}
                                    </div>
                                </div>
                                
                                <h3 class="mt-4 text-[22px] font-black text-gray-900">{{ student.first_name }} {{ student.last_name }}</h3>
                                <div class="mt-2 flex justify-center">
                                    <span class="text-teal-600 font-medium font-mono text-sm bg-teal-50 px-4 py-1.5 rounded-full border border-indigo-100">{{ student.student_external_id }}</span>
                                </div>

                                <div class="mt-8 grid grid-cols-2 gap-3 text-right">
                                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-100/60 flex flex-col justify-center text-center">
                                        <p class="text-gray-400 text-[11px] font-bold mb-1">المرحلة</p>
                                        <p class="font-black text-gray-900 text-sm">{{ student.group?.stage?.name }}</p>
                                    </div>
                                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-100/60 flex flex-col justify-center text-center">
                                        <p class="text-gray-400 text-[11px] font-bold mb-1">المجموعة</p>
                                        <p class="font-black text-gray-900 text-[16px]">{{ student.group?.name }}</p>
                                    </div>
                                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-100/60 flex flex-col justify-center text-center">
                                        <p class="text-gray-400 text-[11px] font-bold mb-1">الجنس</p>
                                        <p class="font-black text-gray-900 text-sm">{{ student.gender === 'male' ? 'ذكر' : 'أنثى' }}</p>
                                    </div>
                                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-100/60 flex flex-col justify-center text-center">
                                        <p class="text-gray-400 text-[11px] font-bold mb-1">نوع الدراسة</p>
                                        <p class="font-black text-gray-900 text-sm flex items-center justify-center gap-1.5">
                                            {{ student.group?.study_type === 'morning' ? 'صباحي' : 'مسائي' }}
                                            <span class="text-lg">{{ student.group?.study_type === 'morning' ? '☀️' : '🌙' }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Card -->
                        <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100/60 p-8 text-center print-visible relative overflow-hidden group">
                           
                           <h3 class="text-lg font-bold text-gray-900 mb-6 relative z-10">بطاقة الهوية السريعة (QR)</h3>
                           
                           <div class="bg-white p-4 rounded-3xl shadow-sm border border-gray-100 inline-block relative z-10 mb-2 mx-auto overflow-hidden">
                                <QRCodeVue3
                                    :width="200"
                                    :height="200"
                                    :value="student.qr_payload"
                                    :qrOptions="{ typeNumber: '0', mode: 'Byte', errorCorrectionLevel: 'Q' }"
                                    :imageOptions="{ hideBackgroundDots: true, imageSize: 0.4, margin: 0 }"
                                    :dotsOptions="{ type: 'rounded', color: '#111827' }"
                                    :cornersSquareOptions="{ type: 'extra-rounded', color: '#111827' }"
                                    :cornersDotOptions="{ type: 'dot', color: '#111827' }"
                                />
                           </div>

                           <div class="print-hidden relative z-10 mt-6">
                                <p class="text-xs text-gray-400 font-medium">الرمز مشفر أمنياً من قبل النظام.</p>
                           </div>

                           <!-- Print-only labels -->
                           <div class="hidden print:block mt-8 text-center border-t border-dashed border-gray-300 pt-6">
                                <p class="font-black text-2xl uppercase">{{ student.first_name }} {{ student.last_name }}</p>
                                <p class="text-gray-500 mt-2 font-mono text-lg tracking-widest">{{ student.student_external_id }}</p>
                           </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
       visibility: hidden;
    }
    .print-visible, .print-visible * {
        visibility: visible;
    }
    .print-visible {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
    }
    .print-hidden {
        display: none !important;
    }
}
</style>
