<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    CpuIcon, 
    ActivityIcon, 
    PlayIcon, 
    SquareIcon, 
    RefreshCcwIcon, 
    FileTextIcon,
    ServerIcon,
    AlertCircleIcon,
    CheckCircle2Icon
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    services: Array<{
        id: string;
        name: string;
        status: string;
        description: string;
    }>;
    server_info: {
        php_version: string;
        os: string;
        storage_logs: Record<string, string>;
    }
}>();

const selectedLog = ref('worker.log');
const form = useForm({
    service: '',
    action: '' as 'start' | 'stop' | 'restart'
});

const handleAction = (serviceId: string, action: 'start' | 'stop' | 'restart') => {
    if (confirm(`هل أنت متأكد من تنفيذ عملية (${action}) للخدمة ${serviceId}؟`)) {
        form.service = serviceId;
        form.action = action;
        form.post(route('admin.system.action'), {
            preserveScroll: true,
        });
    }
};

const getStatusColor = (status: string) => {
    switch (status.toUpperCase()) {
        case 'RUNNING': return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        case 'STOPPED': return 'text-rose-600 bg-rose-50 border-rose-100';
        case 'STARTING': return 'text-amber-600 bg-amber-50 border-amber-100 animate-pulse';
        default: return 'text-gray-600 bg-gray-50 border-gray-100';
    }
};
</script>

<template>
    <Head title="إدارة النظام" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                    <ServerIcon class="w-8 h-8 text-teal-600" />
                    إدارة خدمات النظام (Supervisor)
                </h2>
                <div class="flex items-center gap-4 text-sm text-gray-500 font-bold bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm">
                    <span class="flex items-center gap-1"><CpuIcon class="w-4 h-4" /> PHP: {{ server_info.php_version }}</span>
                    <span class="w-px h-4 bg-gray-200"></span>
                    <span>OS: {{ server_info.os }}</span>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Services Status Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="service in services" :key="service.id" 
                     class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-teal-50 text-teal-600 shadow-inner">
                                <ActivityIcon class="w-7 h-7" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900">{{ service.name }}</h3>
                                <p class="text-xs text-gray-400 font-mono">{{ service.id }}</p>
                            </div>
                        </div>
                        <div :class="['px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-wider', getStatusColor(service.status)]">
                            {{ service.status }}
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-4 mb-6 border border-gray-100">
                        <p class="text-xs text-gray-500 font-bold mb-1">تفاصيل الجلسة:</p>
                        <p class="text-[13px] text-gray-700 font-mono break-all">{{ service.description || 'لا يوجد تفاصيل متاحة' }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="handleAction(service.id, 'start')" 
                                :disabled="service.status === 'RUNNING' || form.processing"
                                class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20">
                            <PlayIcon class="w-4 h-4" /> تشغيل
                        </button>
                        <button @click="handleAction(service.id, 'restart')" 
                                :disabled="form.processing"
                                class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20">
                            <RefreshCcwIcon class="w-4 h-4" /> إعادة تشغيل
                        </button>
                        <button @click="handleAction(service.id, 'stop')" 
                                :disabled="service.status === 'STOPPED' || form.processing"
                                class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20">
                            <SquareIcon class="w-4 h-4" /> إيقاف
                        </button>
                    </div>
                </div>
            </div>

            <!-- Logs Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                        <FileTextIcon class="w-6 h-6 text-indigo-500" />
                        سجلات النظام (Logs)
                    </h3>
                    <div class="flex gap-2 p-1 bg-white rounded-xl border border-gray-200">
                        <button v-for="(content, fileName) in server_info.storage_logs" :key="fileName"
                                @click="selectedLog = fileName.toString()"
                                :class="['px-4 py-1.5 rounded-lg text-xs font-black transition-all', selectedLog === fileName ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 text-gray-400']">
                            {{ fileName }}
                        </button>
                    </div>
                </div>
                <div class="p-6 bg-slate-950 font-mono text-[13px] leading-relaxed text-indigo-300 min-h-[400px] overflow-auto relative group">
                    <div class="absolute top-4 left-4 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="bg-indigo-600 text-white px-2 py-1 rounded text-[10px] font-bold">آخر ٢٠ سطر</button>
                    </div>
                    <pre class="whitespace-pre-wrap">{{ server_info.storage_logs[selectedLog] }}</pre>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex items-center gap-2 border-t border-gray-100">
                    <AlertCircleIcon class="w-4 h-4 text-amber-500" />
                    <span class="text-[11px] text-gray-500 font-bold">يتم جلب آخر تحديثات الملفات من المجلد `/storage/logs/` لحظة تحميل الصفحة.</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
pre::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
pre::-webkit-scrollbar-track {
    background: #020617;
}
pre::-webkit-scrollbar-thumb {
    background: #312e81;
    border-radius: 10px;
}
</style>
