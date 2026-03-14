<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    RefreshCcwIcon, 
    ActivityIcon, 
    CheckCircle2Icon, 
    XCircleIcon, 
    AlertTriangleIcon,
    SmartphoneIcon,
    ClockIcon,
    ArrowRightIcon,
    BookOpenIcon
} from 'lucide-vue-next';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';

const props = defineProps<{
    logs: {
        data: Array<{
            id: string;
            sync_id: string;
            device_id: string;
            lecture_id: string;
            scans_received: number;
            scans_processed: number;
            failed_scans: number;
            sent_at: string;
            synced_at: string;
            duration_ms: number;
            status: 'success' | 'partial' | 'failed';
            error_details?: Array<{
                student_id: string;
                error: string;
                request_id: string;
            }>;
            lecture?: {
                id: string;
                title: string;
                subject?: {
                    name: string;
                }
            }
        }>;
        links: Array<any>;
        last_page?: number;
        meta?: {
            last_page: number;
        };
    };
    stats: {
        total_scans_today: number;
        failed_syncs_today: number;
        avg_duration_ms: number;
        total_devices: number;
    };
    filters: {
        errors_only?: string;
    }
}>();

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'success':
            return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'partial':
            return 'bg-amber-50 text-amber-700 border-amber-100';
        case 'failed':
            return 'bg-rose-50 text-rose-700 border-rose-100';
        default:
            return 'bg-gray-50 text-gray-700 border-gray-100';
    }
};

const formatDate = (date: string) => {
    return format(new Date(date), 'yyyy/MM/dd HH:mm', { locale: ar });
};
</script>

<template>
    <Head title="سجلات المزامنة" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                        <RefreshCcwIcon class="w-8 h-8 text-teal-600" />
                        سجلات مزامنة البيانات (Sync Logs)
                    </h2>
                    <p class="text-gray-500 font-bold mt-1 text-sm">متابعة دقة وسرعة رفع سجلات الحضور من الأجهزة المختلفة.</p>
                </div>
                
                <div class="flex gap-2">
                    <Link 
                        :href="route('admin.system.sync-logs.errors')"
                        class="px-5 py-2.5 rounded-2xl text-sm font-black transition-all flex items-center gap-2 border bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100 shadow-sm"
                    >
                        <AlertTriangleIcon class="w-4 h-4" />
                        عرض المشاكل المكتشفة
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Stats Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Scans Today -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 rounded-2xl bg-teal-50 text-teal-600">
                            <CheckCircle2Icon class="w-6 h-6" />
                        </div>
                        <span class="text-sm font-black text-gray-500">سجلات اليوم</span>
                    </div>
                    <div class="text-3xl font-black text-gray-900">{{ stats.total_scans_today }}</div>
                    <p class="text-[11px] text-gray-400 font-bold mt-2">إجمالي الطلاب المسجلين اليوم عبر المزامنة.</p>
                </div>

                <!-- Failed Syncs Today -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 rounded-2xl bg-rose-50 text-rose-600">
                            <XCircleIcon class="w-6 h-6" />
                        </div>
                        <span class="text-sm font-black text-gray-500">فشل المزامنة اليوم</span>
                    </div>
                    <div class="text-3xl font-black text-rose-600">{{ stats.failed_syncs_today }}</div>
                    <p class="text-[11px] text-gray-400 font-bold mt-2 text-rose-500" v-if="stats.failed_syncs_today > 0">تنبيه: يوجد عمليات مزامنة فاشلة.</p>
                    <p class="text-[11px] text-gray-400 font-bold mt-2" v-else>لا توجد أعطال حالياً.</p>
                </div>

                <!-- Avg Duration -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600">
                            <ClockIcon class="w-6 h-6" />
                        </div>
                        <span class="text-sm font-black text-gray-500">متوسط سرعة المعالجة</span>
                    </div>
                    <div class="text-3xl font-black text-gray-900">{{ stats.avg_duration_ms }} <span class="text-sm font-bold text-gray-400">ms</span></div>
                    <p class="text-[11px] text-gray-400 font-bold mt-2">زمن معالجة الطلب الواحد في السيرفر.</p>
                </div>

                <!-- Total Devices -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 rounded-2xl bg-amber-50 text-amber-600">
                            <SmartphoneIcon class="w-6 h-6" />
                        </div>
                        <span class="text-sm font-black text-gray-500">الأجهزة النشطة</span>
                    </div>
                    <div class="text-3xl font-black text-gray-900">{{ stats.total_devices }}</div>
                    <p class="text-[11px] text-gray-400 font-bold mt-2">عدد الهواتف الفريدة التي قامت بالمزامنة.</p>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                        <ActivityIcon class="w-6 h-6 text-teal-600" />
                        السجلات الأخيرة
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-start text-xs font-black text-gray-500 uppercase tracking-wider">الحالة</th>
                                <th class="px-6 py-4 text-start text-xs font-black text-gray-500 uppercase tracking-wider">المحاضرة / طلب المزامنة</th>
                                <th class="px-6 py-4 text-start text-xs font-black text-gray-500 uppercase tracking-wider">السجلات (مستلم/معالج)</th>
                                <th class="px-6 py-4 text-start text-xs font-black text-gray-500 uppercase tracking-wider">الجهاز</th>
                                <th class="px-6 py-4 text-start text-xs font-black text-gray-500 uppercase tracking-wider">توقيت المزامنة</th>
                                <th class="px-6 py-4 text-start text-xs font-black text-gray-500 uppercase tracking-wider">المدة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="log in logs.data" :key="log.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-3 py-1 rounded-full text-[10px] font-black border uppercase tracking-widest', getStatusBadge(log.status)]">
                                            {{ log.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-black text-gray-900 flex items-center gap-1">
                                                <BookOpenIcon class="w-3.5 h-3.5 text-teal-600" />
                                                {{ log.lecture?.title || 'محاضرة غير معروفة' }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 font-mono mt-0.5">{{ log.sync_id }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-gray-700">{{ log.scans_processed }}</span>
                                            <ArrowRightIcon class="w-3 h-3 text-gray-300" />
                                            <span class="text-sm font-bold text-gray-400">{{ log.scans_received }}</span>
                                            <span v-if="log.failed_scans > 0" class="ms-2 px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 text-[10px] font-black border border-rose-100">
                                                {{ log.failed_scans }} فشل
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2 text-sm text-gray-600 font-bold">
                                            <SmartphoneIcon class="w-4 h-4 text-gray-400" />
                                            <span class="truncate max-w-[120px]" :title="log.device_id">{{ log.device_id.substring(0, 8) }}...</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-bold">
                                        {{ formatDate(log.synced_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-400">
                                        {{ log.duration_ms }}ms
                                    </td>
                                </tr>
                                <tr v-if="log.error_details && log.error_details.length > 0" class="bg-rose-50/30">
                                    <td colspan="6" class="px-6 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="(err, idx) in log.error_details" :key="idx" class="px-2 py-1 bg-white border border-rose-100 rounded-lg text-[10px] font-bold text-rose-600 flex items-center gap-1.5 shadow-sm">
                                                <AlertTriangleIcon class="w-3 h-3" />
                                                طالب ({{ err.student_id.substring(0, 8) }}): {{ err.error }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <RefreshCcwIcon class="w-12 h-12 text-gray-200 animate-spin-slow" />
                                        <p class="text-gray-400 font-black">لم يتم تسجيل أي عمليات مزامنة حتى الآن.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="(logs.meta?.last_page || logs.last_page || 0) > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-center gap-2">
                    <Link v-for="link in logs.links" :key="link.label"
                          :href="link.url"
                          v-html="link.label"
                          :disabled="!link.url"
                          :class="['px-4 py-2 rounded-xl text-xs font-black transition-all border', 
                                    link.active ? 'bg-teal-600 text-white border-teal-600 shadow-lg shadow-teal-500/20' : 
                                    'bg-white text-gray-600 border-gray-200 hover:border-teal-300 hover:text-teal-600 shadow-sm',
                                    !link.url ? 'opacity-30 cursor-not-allowed' : '']"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 3s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
