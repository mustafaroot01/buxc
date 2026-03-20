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
    BookOpenIcon,
    AlertCircleIcon
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    SearchIcon, 
    FilterIcon, 
    Trash2Icon,
    ChevronLeftIcon,
    ChevronRightIcon
} from 'lucide-vue-next';

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
                deleted_at: string | null;
                subject?: {
                    name: string;
                },
                teacher?: {
                    full_name: string;
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
        search?: string;
        status?: string;
        date?: string;
    }
}>();

const searchTerm = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFilter = ref(props.filters.date || '');
const confirmingLogDeletion = ref(false);

const applyFilters = () => {
    router.get(route('admin.system.sync-logs'), {
        search: searchTerm.value,
        status: statusFilter.value,
        date: dateFilter.value
    }, {
        preserveState: true,
        replace: true
    });
};

watch([searchTerm, statusFilter, dateFilter], () => {
    // Basic debounce for search
    const timeoutId = setTimeout(() => {
        applyFilters();
    }, 500);
    return () => clearTimeout(timeoutId);
});

const clearLogs = () => {
    confirmingLogDeletion.value = true;
};

const deleteLogs = () => {
    router.delete(route('admin.system.sync-logs.clear-old'), {
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingLogDeletion.value = false;
};

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
                    <button 
                        @click="clearLogs"
                        class="px-5 py-2.5 rounded-2xl text-sm font-black transition-all flex items-center gap-2 border bg-white text-gray-600 border-gray-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 shadow-sm"
                    >
                        <Trash2Icon class="w-4 h-4" />
                        تنظيف السجلات القديمة
                    </button>
                    <Link 
                        :href="route('admin.system.sync-logs.errors')"
                        class="px-5 py-2.5 rounded-2xl text-sm font-black transition-all flex items-center gap-2 border bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100 shadow-sm"
                    >
                        <AlertTriangleIcon class="w-4 h-4" />
                        عرض المشاكل المكتشفة
                    </Link>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-wrap items-center gap-4 mt-8">
                <div class="flex-1 min-w-[250px] relative">
                    <SearchIcon class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                        v-model="searchTerm"
                        type="text" 
                        placeholder="بحث برقم المزامنة، الجهاز، أو عنوان المحاضرة..."
                        class="w-full pr-10 pl-4 py-2.5 rounded-2xl border-gray-100 bg-gray-50/50 focus:bg-white focus:ring-teal-500/20 focus:border-teal-500 text-sm font-bold transition-all"
                    >
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <FilterIcon class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                        <select 
                            v-model="statusFilter"
                            class="pr-9 pl-4 py-2.5 rounded-2xl border-gray-100 bg-gray-50/50 focus:bg-white focus:ring-teal-500/20 focus:border-teal-500 text-xs font-black transition-all"
                        >
                            <option value="">كل الحالات</option>
                            <option value="success">ناجح (Success)</option>
                            <option value="partial">جزئي (Partial)</option>
                            <option value="failed">فاشل (Failed)</option>
                        </select>
                    </div>

                    <input 
                        v-model="dateFilter"
                        type="date" 
                        class="px-4 py-2.5 rounded-2xl border-gray-100 bg-gray-50/50 focus:bg-white focus:ring-teal-500/20 focus:border-teal-500 text-xs font-black transition-all"
                    >
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
                                            <div class="text-[14px] font-black text-gray-900 flex items-center gap-1.5">
                                                <BookOpenIcon class="w-4 h-4 text-teal-600" />
                                                <template v-if="log.lecture">
                                                    {{ log.lecture.title }}
                                                    <span v-if="log.lecture.deleted_at" class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded border border-gray-200 font-bold">محذوفة</span>
                                                </template>
                                                <span v-else class="text-rose-600">محاضرة غير معروفة</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[11px] text-gray-500 font-bold" v-if="log.lecture?.subject">
                                                    المادة: {{ log.lecture.subject.name }}
                                                </span>
                                                <span class="text-[11px] text-gray-400">|</span>
                                                <span class="text-[11px] text-indigo-600 font-bold" v-if="log.lecture?.teacher">
                                                    المدرس: {{ log.lecture.teacher.full_name }}
                                                </span>
                                            </div>
                                            <div class="text-[9px] text-gray-400 font-mono mt-1 opacity-60">ID: {{ log.sync_id }}</div>
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

        <!-- Confirmation Modal -->
        <Modal :show="confirmingLogDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-rose-50 rounded-full text-rose-600">
                    <AlertCircleIcon class="w-8 h-8" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">تأكيد مسح السجلات</h3>
                <p class="text-gray-500 text-center font-bold text-sm leading-relaxed mb-8">
                    هل أنت متأكد من رغبتك في حذف **السجلات الحالية** بالكامل؟ 
                    <br>
                    <span class="text-rose-600">تنبيه: لا يمكن التراجع عن هذه العملية بعد التنفيذ.</span>
                </p>

                <div class="flex flex-col gap-3">
                    <DangerButton 
                        class="w-full justify-center py-4 rounded-2xl text-[15px] font-black shadow-lg shadow-rose-200" 
                        @click="deleteLogs"
                    >
                        نعم، قم بالمسح الآن
                    </DangerButton>
                    
                    <SecondaryButton 
                        class="w-full justify-center py-4 rounded-2xl text-[15px] font-black border-none bg-gray-50 text-gray-500 hover:bg-gray-100" 
                        @click="closeModal"
                    >
                        إلغاء العملية
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
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
