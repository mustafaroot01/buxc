<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    ActivityIcon, 
    DatabaseIcon, 
    CpuIcon, 
    HardDriveIcon, 
    LayersIcon, 
    CheckCircleIcon, 
    XCircleIcon, 
    ZapIcon, 
    ClockIcon, 
    ServerIcon,
    RefreshCcwIcon,
    TerminalIcon,
    RadioIcon
} from 'lucide-vue-next';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';

interface Metrics {
    system: {
        cpu_load: string;
        memory: { total: string; used: string; percentage: number };
        disk: { total: string; free: string; usage_percentage: number };
        uptime: string;
        os: string;
        php_version: string;
    };
    database: {
        status: string;
        driver: string;
        version: string;
        size: string;
        connections: string;
    };
    redis: {
        status: string;
        info: {
            version?: string;
            memory_used?: string;
            uptime_days?: string;
            clients?: string;
        };
    };
    queue: {
        pending_jobs: number;
        failed_jobs: number;
        active_workers: number | string;
    };
    reverb: {
        status: string;
        port: number;
    };
    logs: string;
}

const props = defineProps<{
    metrics: Metrics;
    server_time: string;
}>();

const getHealthColor = (status: string) => {
    if (status.includes('Connected') || status === 'Active' || status === 'All clear') return 'text-teal-500 bg-teal-50 border-teal-100';
    if (status.includes('Error') || status === 'Inactive' || status === 'Disabled') return 'text-rose-500 bg-rose-50 border-rose-100';
    return 'text-amber-500 bg-amber-50 border-amber-100';
};

const getProgressColor = (percentage: number) => {
    if (percentage > 90) return 'bg-rose-500';
    if (percentage > 70) return 'bg-amber-500';
    return 'bg-teal-500';
};
</script>

<template>
    <Head title="مركز صحة السيرفر" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 rounded-2xl shadow-sm">
                            <ActivityIcon class="w-8 h-8 text-indigo-600" />
                        </div>
                        مركز صحة ومراقبة السيرفر
                    </h2>
                    <p class="text-slate-500 font-bold mt-1 text-sm">مراقبة حية وشاملة لموارد النظام، الخدمات، وقواعد البيانات.</p>
                </div>
                
                <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl border border-slate-100 shadow-sm">
                    <ClockIcon class="w-4 h-4 text-slate-400" />
                    <span class="text-xs font-black text-slate-600">توقيت السيرفر: {{ format(new Date(server_time), 'HH:mm:ss') }}</span>
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-12">
            <!-- Part 1: System Vitals -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- CPU & Load -->
                <div class="bg-white p-8 rounded-[40px] shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                        <CpuIcon class="w-32 h-32 text-indigo-900" />
                    </div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="p-3 bg-indigo-50 rounded-2xl">
                            <CpuIcon class="w-6 h-6 text-indigo-600" />
                        </div>
                        <h3 class="text-lg font-black text-slate-800">وحدة المعالجة (CPU)</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">معدل التحميل (Load)</span>
                            <span class="text-xl font-black text-slate-900">{{ metrics.system.cpu_load }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-400 font-bold border-t border-slate-50 pt-3 mt-3">
                            <span>نظام التشغيل</span>
                            <span class="text-slate-700">{{ metrics.system.os }}</span>
                        </div>
                    </div>
                </div>

                <!-- Memory Usage -->
                <div class="bg-white p-8 rounded-[40px] shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                        <LayersIcon class="w-32 h-32 text-teal-900" />
                    </div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="p-3 bg-teal-50 rounded-2xl">
                            <LayersIcon class="w-6 h-6 text-teal-600" />
                        </div>
                        <h3 class="text-lg font-black text-slate-800">الذاكرة (RAM)</h3>
                    </div>
                    <div class="space-y-5">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-black text-slate-400 tracking-widest">{{ metrics.system.memory.used }} / {{ metrics.system.memory.total }}</span>
                            <span class="text-lg font-black text-slate-900">{{ metrics.system.memory.percentage }}%</span>
                        </div>
                        <div class="h-3 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div :class="['h-full transition-all duration-1000', getProgressColor(metrics.system.memory.percentage)]" 
                                 :style="{ width: metrics.system.memory.percentage + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-xs text-slate-400 font-bold border-t border-slate-50 pt-3">
                            <span>نسخة PHP</span>
                            <span class="text-slate-700">{{ metrics.system.php_version }}</span>
                        </div>
                    </div>
                </div>

                <!-- Disk Usage -->
                <div class="bg-white p-8 rounded-[40px] shadow-sm border border-slate-100 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                        <HardDriveIcon class="w-32 h-32 text-amber-900" />
                    </div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="p-3 bg-amber-50 rounded-2xl">
                            <HardDriveIcon class="w-6 h-6 text-amber-600" />
                        </div>
                        <h3 class="text-lg font-black text-slate-800">التخزين (Disk)</h3>
                    </div>
                    <div class="space-y-5">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-black text-slate-400 tracking-widest">{{ metrics.system.disk.free }} متاح من {{ metrics.system.disk.total }}</span>
                            <span class="text-lg font-black text-slate-900">{{ metrics.system.disk.usage_percentage }}%</span>
                        </div>
                        <div class="h-3 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div :class="['h-full transition-all duration-1000', getProgressColor(metrics.system.disk.usage_percentage)]" 
                                 :style="{ width: metrics.system.disk.usage_percentage + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-xs text-slate-400 font-bold border-t border-slate-50 pt-3">
                            <span>تشغيل النظام (Uptime)</span>
                            <span class="text-slate-700">{{ metrics.system.uptime }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part 2: Database & Services -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Database Center -->
                <div class="bg-white p-10 rounded-[48px] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-10">
                        <div class="flex items-center gap-5">
                            <div class="p-4 bg-sky-50 rounded-3xl shadow-sm border border-sky-100">
                                <DatabaseIcon class="w-8 h-8 text-sky-600" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900">قاعدة البيانات</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span :class="['px-3 py-0.5 rounded-full text-[10px] font-black border uppercase', getHealthColor(metrics.database.status)]">
                                        {{ metrics.database.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">إجمالي حجم البيانات</p>
                            <p class="text-2xl font-black text-sky-600">{{ metrics.database.size }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 p-6 rounded-[32px] border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">المحرك والإصدار</p>
                            <p class="text-md font-black text-slate-800">{{ metrics.database.driver }} ({{ metrics.database.version }})</p>
                        </div>
                        <div class="bg-slate-50/50 p-6 rounded-[32px] border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">الاتصالات النشطة</p>
                            <div class="flex items-end gap-2">
                                <p class="text-2xl font-black text-slate-900">{{ metrics.database.connections }}</p>
                                <span class="text-xs font-bold text-slate-400 mb-1">Threads</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Redis & Services Status -->
                <div class="bg-white p-10 rounded-[48px] shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-10">
                        <div class="flex items-center gap-5">
                            <div class="p-4 bg-rose-50 rounded-3xl shadow-sm border border-rose-100">
                                <ZapIcon class="w-8 h-8 text-rose-600" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900">Redis & Real-time</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span :class="['px-3 py-0.5 rounded-full text-[10px] font-black border uppercase', getHealthColor(metrics.redis.status)]">
                                        Redis: {{ metrics.redis.status }}
                                    </span>
                                    <span :class="['px-3 py-0.5 rounded-full text-[10px] font-black border uppercase', getHealthColor(metrics.reverb.status)]">
                                        Reverb: {{ metrics.reverb.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Redis Info Card -->
                        <div class="p-6 bg-slate-900 rounded-[32px] text-white">
                            <div class="flex justify-between items-start mb-4">
                                <span class="p-2 bg-rose-500/20 rounded-xl">
                                    <RadioIcon class="w-4 h-4 text-rose-400" />
                                </span>
                                <span class="text-[9px] font-black text-slate-500 uppercase">Performance</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Memory Used</span>
                                    <span class="text-xs font-black">{{ metrics.redis.info.memory_used || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Clients</span>
                                    <span class="text-xs font-black">{{ metrics.redis.info.clients || '0' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Reverb Card -->
                        <div class="p-6 bg-slate-50/50 border border-slate-100 rounded-[32px]">
                            <div class="flex justify-between items-start mb-4">
                                <span class="p-2 bg-indigo-50 rounded-xl">
                                    <ServerIcon class="w-4 h-4 text-indigo-500" />
                                </span>
                                <span class="text-[9px] font-black text-slate-400 uppercase">Broadcasting</span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-black text-slate-400 uppercase">WebSocket Port</p>
                                <p class="text-xl font-black text-slate-900">{{ metrics.reverb.port }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part 3: Queues Monitoring -->
            <div class="bg-white p-10 rounded-[48px] shadow-sm border border-slate-100 overflow-hidden relative">
                <div class="absolute right-0 top-0 w-1/3 h-full bg-gradient-to-l from-indigo-50/20 to-transparent"></div>
                
                <div class="flex items-center gap-6 mb-10 relative z-10">
                    <div class="p-4 bg-indigo-600 rounded-3xl shadow-lg shadow-indigo-100">
                        <RefreshCcwIcon class="w-8 h-8 text-white" />
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900">طوابير المعالجة (Queues)</h3>
                        <p class="text-slate-400 font-bold text-sm tracking-wide">تصفير الغيابات، التنبيهات، والمزامنة الخلفية.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
                    <div class="p-8 bg-slate-50/50 rounded-[40px] border border-slate-100 text-center">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">المهام المنتظرة حالياً</p>
                        <p class="text-5xl font-black text-slate-900 tabular-nums">{{ metrics.queue.pending_jobs }}</p>
                    </div>
                    <div class="p-8 bg-rose-50/50 rounded-[40px] border border-rose-100 text-center">
                        <p class="text-xs font-black text-rose-400 uppercase tracking-widest mb-2">المهام الفاشلة</p>
                        <p class="text-5xl font-black text-rose-600 tabular-nums">{{ metrics.queue.failed_jobs }}</p>
                    </div>
                    <div class="p-8 bg-sky-50 rounded-[40px] border border-sky-100 text-center shadow-inner">
                        <p class="text-xs font-black text-sky-500 uppercase tracking-widest mb-2">عدد العمال النشطين</p>
                        <div class="flex items-center justify-center gap-3">
                            <p class="text-5xl font-black text-sky-700 tabular-nums">{{ metrics.queue.active_workers }}</p>
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-sky-500"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part 4: System Logs -->
            <div class="bg-slate-950 p-10 rounded-[48px] shadow-2xl">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4 text-white">
                        <div class="p-2 bg-slate-800 rounded-xl">
                            <TerminalIcon class="w-6 h-6 text-teal-400" />
                        </div>
                        <h3 class="text-xl font-black tracking-wide">أحدث سجلات النظام (System Logs)</h3>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">laravel.log preview</span>
                </div>
                
                <div class="bg-slate-900 rounded-3xl p-8 border border-slate-800 shadow-inner overflow-auto max-h-[400px] custom-scrollbar">
                    <pre class="text-xs font-mono text-teal-400/80 leading-relaxed">{{ metrics.logs }}</pre>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f172a;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569;
}
</style>
