<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    AlertTriangleIcon,
    ArrowRightIcon,
    SmartphoneIcon,
    ClockIcon,
    RefreshCcwIcon,
    SearchIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    UserIcon,
    GlobeIcon,
    CodeIcon,
    TerminalIcon,
    DatabaseIcon,
    Trash2Icon
} from 'lucide-vue-next';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps<{
    logs: {
        data: Array<{
            id: string;
            user_id: number | null;
            method: string;
            url: string;
            payload: any;
            status_code: number;
            message: string;
            exception_class: string | null;
            stack_trace: Array<any> | null;
            ip_address: string | null;
            device_id: string | null;
            created_at: string;
            user?: {
                full_name: string;
                email: string;
            }
        }>;
        links: Array<any>;
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

const formatDate = (date: string) => {
    return format(new Date(date), 'yyyy/MM/dd HH:mm:ss', { locale: ar });
};

const getStatusColor = (code: number) => {
    if (code >= 500) return 'bg-rose-100 text-rose-700 border-rose-200';
    if (code >= 400) return 'bg-amber-100 text-amber-700 border-amber-200';
    return 'bg-blue-100 text-blue-700 border-blue-200';
};

const confirmingClear = ref(false);

const clearLogs = () => {
    router.delete(route('admin.system.sync-logs.errors.clear'), {
        onSuccess: () => confirmingClear.value = false,
        onFinish: () => confirmingClear.value = false,
    });
};
</script>

<template>
    <Head title="مركز أخطاء API" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 flex items-center gap-3">
                        <div class="p-2 bg-rose-100 rounded-2xl shadow-sm">
                            <TerminalIcon class="w-8 h-8 text-rose-600" />
                        </div>
                        مركز مراقبة أخطاء API الموحد
                    </h2>
                    <p class="text-slate-500 font-bold mt-1 text-sm">تتبع وتحليل كافة المشاكل البرمجية التي تحدث في واجهة التطبيق (API).</p>
                </div>
                
                <div class="flex gap-2">
                    <button 
                        @click="confirmingClear = true"
                        class="px-5 py-2.5 rounded-2xl text-sm font-black transition-all flex items-center gap-2 border bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100 shadow-sm"
                    >
                        <Trash2Icon class="w-4 h-4" />
                        تصفير السجل
                    </button>
                    <Link 
                        :href="route('admin.system.sync-logs')"
                        class="px-5 py-2.5 rounded-2xl text-sm font-black transition-all flex items-center gap-2 border bg-white text-gray-700 border-gray-200 hover:border-teal-300 hover:text-teal-600 shadow-sm"
                    >
                        <RefreshCcwIcon class="w-4 h-4" />
                        سجلات المزامنة
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="p-3 bg-rose-50 rounded-2xl">
                        <AlertTriangleIcon class="w-6 h-6 text-rose-500" />
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-wider">إجمالي الأخطاء المسجلة</p>
                        <p class="text-2xl font-black text-slate-900">{{ logs.total }}</p>
                    </div>
                </div>
                <!-- More stats can be added here -->
            </div>

            <!-- Error List -->
            <div class="grid grid-cols-1 gap-6">
                <div v-for="log in logs.data" :key="log.id" class="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-all duration-300">
                    <!-- Top Info Bar -->
                    <div class="px-8 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span :class="['px-4 py-1.5 rounded-full text-[11px] font-black border uppercase tracking-widest', getStatusColor(log.status_code)]">
                                HTTP {{ log.status_code }}
                            </span>
                            <div class="h-4 w-px bg-slate-200"></div>
                            <div class="text-xs font-bold text-slate-500 flex items-center gap-2">
                                <ClockIcon class="w-3.5 h-3.5" />
                                {{ formatDate(log.created_at) }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-slate-400 bg-white px-3 py-1 rounded-full border border-slate-100 uppercase">{{ log.method }}</span>
                            <div class="text-[10px] font-mono text-slate-400 bg-white px-3 py-1 rounded-full border border-slate-100">
                                {{ log.id.substring(0, 8) }}
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <!-- Left: Error & Request Details -->
                            <div class="lg:col-span-7 space-y-6">
                                <div>
                                    <h3 class="text-lg font-black text-rose-700 leading-snug flex items-start gap-2">
                                        <CodeIcon class="w-6 h-6 shrink-0 mt-0.5" />
                                        {{ log.message }}
                                    </h3>
                                    <p class="text-xs font-mono text-slate-400 mt-2 break-all opacity-70">{{ log.url }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-slate-50 rounded-3xl border border-slate-100">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 mb-2 uppercase">
                                            <UserIcon class="w-3.5 h-3.5" /> المستخدم
                                        </div>
                                        <p class="text-sm font-black text-slate-700">{{ log.user?.full_name || 'زائر غير مسجل' }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold overflow-hidden text-ellipsis">{{ log.user?.email || 'N/A' }}</p>
                                    </div>
                                    <div class="p-4 bg-slate-50 rounded-3xl border border-slate-100">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 mb-2 uppercase">
                                            <SmartphoneIcon class="w-3.5 h-3.5" /> الجهاز المكتشف
                                        </div>
                                        <p class="text-sm font-black text-slate-700 truncate">{{ log.device_id || 'غير متوفر' }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold">IP: {{ log.ip_address }}</p>
                                    </div>
                                </div>
                                
                                <div v-if="log.exception_class" class="p-4 bg-rose-50/30 rounded-3xl border border-rose-100/50">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-rose-400 mb-2 uppercase">
                                        <DatabaseIcon class="w-3.5 h-3.5" /> نوع الاستثناء (Exception)
                                    </div>
                                    <p class="text-xs font-mono text-rose-600 font-bold break-all">{{ log.exception_class }}</p>
                                </div>
                            </div>

                            <!-- Right: Payload / Stack Trace -->
                            <div class="lg:col-span-5 space-y-6">
                                <div class="space-y-3">
                                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <GlobeIcon class="w-3.5 h-3.5" /> بيانات الطلب (Payload)
                                    </h4>
                                    <div class="bg-slate-900 rounded-3xl p-5 overflow-auto max-h-[200px] shadow-inner custom-scrollbar">
                                        <pre class="text-[10px] font-mono text-teal-400 leading-relaxed">{{ JSON.stringify(log.payload, null, 2) }}</pre>
                                    </div>
                                </div>

                                <div v-if="log.stack_trace" class="space-y-3">
                                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <TerminalIcon class="w-3.5 h-3.5" /> تتبع الخطأ (Stack Trace Preview)
                                    </h4>
                                    <div class="bg-slate-800 rounded-3xl p-5 overflow-auto max-h-[250px] shadow-inner custom-scrollbar">
                                        <div v-for="(frame, i) in log.stack_trace" :key="i" class="mb-3 last:mb-0 border-b border-slate-700/50 pb-2 last:border-0">
                                            <p class="text-[10px] font-black text-slate-300 truncate">{{ frame.file?.split('/').pop() }}:{{ frame.line }}</p>
                                            <p class="text-[9px] font-mono text-rose-400 mt-0.5 opacity-80">{{ frame.function || frame.class }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="logs.data.length === 0" class="bg-white rounded-[40px] py-32 text-center border border-slate-100 shadow-sm">
                <div class="flex flex-col items-center gap-6">
                    <div class="w-24 h-24 bg-teal-50 text-teal-500 rounded-full flex items-center justify-center">
                        <RefreshCcwIcon class="w-12 h-12" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900">سجل الأخطاء نظيف تماماً</h3>
                        <p class="text-slate-500 font-bold mt-2">لا توجد أي أخطاء API مسجلة حالياً. التطبيق يعمل بكفاءة.</p>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="flex justify-center mt-12 pb-12">
                <div class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                    <Link v-for="link in logs.links" :key="link.label"
                          :href="link.url"
                          v-html="link.label"
                          :disabled="!link.url"
                          :class="['px-4 py-2 rounded-xl text-xs font-black transition-all', 
                                    link.active ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 
                                    'text-slate-600 hover:bg-slate-50',
                                    !link.url ? 'opacity-30 cursor-not-allowed' : '']"
                    />
                </div>
            </div>
        </div>

        <!-- Clear Confirmation Modal -->
        <Modal :show="confirmingClear" @close="confirmingClear = false" maxWidth="md">
            <div class="p-8 text-right">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-rose-50 rounded-full">
                    <Trash2Icon class="w-8 h-8 text-rose-600" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تصفير سجل الأخطاء
                </h3>
                
                <p class="text-center text-gray-500 text-sm leading-relaxed mb-8">
                    هل أنت متأكد من رغبتك في حذف كافة سجلات الأخطاء؟ هذه العملية لا يمكن التراجع عنها وسيتم مسح {{ logs.total }} سجل.
                </p>

                <div class="flex items-center gap-3">
                    <DangerButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold"
                        @click="clearLogs"
                    >
                        تأكيد التصفير
                    </DangerButton>
                    
                    <SecondaryButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold border-gray-200"
                        @click="confirmingClear = false"
                    >
                        إلغاء
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.rounded-\[32px\] { border-radius: 32px; }
.rounded-\[40px\] { border-radius: 40px; }
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 10px;
}
</style>
