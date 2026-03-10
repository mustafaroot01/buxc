<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { PrinterIcon, ArrowRightIcon, QrCodeIcon } from 'lucide-vue-next';

const props = defineProps<{
    students: Array<{
        id: string;
        name: string;
        external_id: string;
        group: string;
        stage: string;
        qr_svg: string;
    }>;
}>();

const printPage = () => {
    window.print();
};

onMounted(() => {
    // Optionally trigger print dialog automatically: 
    // setTimeout(printPage, 500);
});
</script>

<template>
    <Head title="طباعة بطاقات QR" />

    <div class="print-container bg-[#f8fafc] min-h-screen p-4 sm:p-8 text-slate-800 font-sans relative" dir="rtl">
        <!-- Action Header -->
        <div class="max-w-7xl mx-auto mb-8 print:hidden flex flex-col sm:flex-row justify-between items-center bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100">
            <div class="flex items-center mb-4 sm:mb-0">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 ml-4 border border-indigo-100">
                    <PrinterIcon class="w-6 h-6" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">مركز طباعة البطاقات</h1>
                    <p class="text-slate-500 text-sm mt-1 font-medium">تم العثور على <span class="text-indigo-600 font-bold mx-1">{{ students.length }}</span> بطاقة جاهزة للطباعة.</p>
                </div>
            </div>
            <div class="flex space-x-3 space-x-reverse">
                <Link :href="route('admin.print.qrs')" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 hover:text-slate-900 font-bold transition-all flex items-center shadow-sm">
                    <ArrowRightIcon class="w-5 h-5 ml-2 text-slate-400" />
                    رجوع للفلترة
                </Link>
                <button @click="printPage" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 font-bold transition-all shadow-md shadow-indigo-500/30 flex items-center">
                    <PrinterIcon class="w-5 h-5 ml-2" />
                    بدء الطباعة الآن
                </button>
            </div>
        </div>

        <!-- Print Grid -->
        <div class="print-area mx-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 print:gap-3 print:grid-cols-5 w-full max-w-7xl">
            <div 
                v-for="student in students" 
                :key="student.id" 
                class="student-card relative bg-white border-2 border-slate-100 print:border-slate-800 p-0 flex flex-col items-center text-center rounded-2xl shadow-sm print:shadow-none break-inside-avoid overflow-hidden"
            >
                <!-- Card Header -->
                <div class="w-full bg-slate-50 border-b border-slate-100 print:bg-white print:border-b-2 print:border-slate-800 py-2 px-3">
                    <h3 class="text-[11px] font-black text-slate-800 tracking-tight">بطاقة الحضور الذكية</h3>
                </div>

                <!-- Card Body -->
                <div class="p-3 flex flex-col items-center w-full">
                    <div class="qr-wrapper bg-white p-2 rounded-xl shadow-sm border border-slate-100 print:shadow-none print:border-none mb-3 w-28 h-28 flex items-center justify-center">
                        <!-- Injecting the raw SVG using src -->
                        <img :src="'data:image/svg+xml;base64,' + student.qr_svg" alt="QR Code" class="w-full h-full object-contain" />
                    </div>
                    
                    <div class="mb-1 font-black text-[13px] text-slate-900 leading-tight w-full truncate px-1">
                        {{ student.name }}
                    </div>
                    
                    <div class="w-full mt-2 space-y-1">
                        <div class="text-[10px] font-bold text-slate-700 bg-slate-50 print:bg-white print:border border-slate-100 rounded-lg py-1 px-2 flex justify-between items-center">
                            <span class="text-slate-400 font-medium">الرقم الجامعي</span>
                            <span class="font-mono text-slate-800 block text-left" dir="ltr">{{ student.external_id }}</span>
                        </div>
                        <div class="text-[10px] font-bold text-slate-600 bg-slate-50 print:bg-white print:border border-slate-100 rounded-lg py-1 px-2 flex justify-between items-center text-right overflow-hidden whitespace-nowrap">
                            <span class="text-slate-800 truncate">{{ student.stage }} - {{ student.group }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Card Footer Decorative Line -->
                <div class="h-1 w-full bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-500 print:hidden absolute bottom-0 left-0"></div>
            </div>
            
            <div v-if="students.length === 0" class="col-span-full py-24 text-center text-slate-500 bg-white rounded-[2rem] shadow-sm border border-slate-100 print:hidden flex flex-col items-center justify-center">
                <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                    <QrCodeIcon class="w-10 h-10 text-slate-300" />
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">لا توجد سجلات للطباعة</h3>
                <p class="text-slate-500 font-medium">يرجى العودة للصفحة السابقة وتحديد الطلاب المطلوب طباعة بطاقاتهم أولاً.</p>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        margin: 0;
        padding: 0;
        background-color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .print-container {
        background: white !important;
        padding: 0 !important;
    }
    .student-card {
        page-break-inside: avoid;
        break-inside: avoid;
        border-color: #000 !important;
        border-width: 1px !important;
        border-radius: 8px !important;
    }
    .student-card * {
        color: #000 !important;
    }
    
    @page { 
        margin: 1cm; /* Print margins */
        size: A4 portrait;
    }
}
</style>
