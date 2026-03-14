<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { 
    SmartphoneIcon, 
    ShieldCheckIcon, 
    ShieldAlertIcon, 
    CheckCircle2Icon, 
    XCircleIcon,
    TerminalIcon,
    HistoryIcon,
    AlertCircleIcon
} from 'lucide-vue-next';
import { format } from 'date-fns';
import { ar } from 'date-fns/locale';

const props = defineProps<{
    devices: {
        data: Array<{
            id: number;
            device_id: string;
            model: string;
            os_version: string;
            app_version: string;
            status: 'active' | 'blocked';
            last_seen_at: string;
        }>;
        links: Array<any>;
        last_page?: number;
        meta?: {
            last_page: number;
        };
    }
}>();

const toggleStatus = (id: number) => {
    if (confirm('هل أنت متأكد من تغيير حالة هذا الجهاز؟ في حال حظره، قد لا يتمكن من القيام بعمليات المزامنة.')) {
        router.patch(route('admin.system.devices.toggle-status', id));
    }
};

const formatDate = (date: string) => {
    if (!date) return 'لم يُرَ بعد';
    return format(new Date(date), 'yyyy/MM/dd HH:mm', { locale: ar });
};
</script>

<template>
    <Head title="إدارة الأجهزة" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                        <SmartphoneIcon class="w-8 h-8 text-teal-600" />
                        سجل الأجهزة المسجلة (Device Registry)
                    </h2>
                    <p class="text-gray-500 font-bold mt-1 text-sm">إدارة الأجهزة التي تستخدم التطبيق والتحكم في صلاحيات وصولها.</p>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Devices Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="device in devices.data" :key="device.id" 
                     class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all relative overflow-hidden group">
                    
                    <!-- Status Decoration -->
                    <div :class="['absolute top-0 right-0 w-24 h-24 -mt-10 -mr-10 rounded-full opacity-5 transition-transform group-hover:scale-110', 
                                  device.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500']"></div>

                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div :class="['p-4 rounded-2xl shadow-inner', 
                                          device.status === 'active' ? 'bg-teal-50 text-teal-600' : 'bg-rose-50 text-rose-600']">
                                <SmartphoneIcon class="w-7 h-7" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900">{{ device.model || 'جهاز غير معروف' }}</h3>
                                <p class="text-[10px] text-gray-400 font-mono tracking-tighter">{{ device.device_id }}</p>
                            </div>
                        </div>
                        <div :class="['px-3 py-1 rounded-full text-[10px] font-black border uppercase tracking-widest', 
                                      device.status === 'active' ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-rose-600 bg-rose-50 border-rose-100']">
                            {{ device.status === 'active' ? 'نشط' : 'محظور' }}
                        </div>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between bg-gray-50/50 p-2.5 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-2">
                                <TerminalIcon class="w-4 h-4 text-gray-400" />
                                <span class="text-xs font-bold text-gray-500">الإصدار:</span>
                            </div>
                            <span class="text-xs font-black text-indigo-600 font-mono">{{ device.app_version || 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50/50 p-2.5 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-2">
                                <HistoryIcon class="w-4 h-4 text-gray-400" />
                                <span class="text-xs font-bold text-gray-500">آخر ظهور:</span>
                            </div>
                            <span class="text-[11px] font-black text-gray-700">{{ formatDate(device.last_seen_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50/50 p-2.5 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-2">
                                <AlertCircleIcon class="w-4 h-4 text-gray-400" />
                                <span class="text-xs font-bold text-gray-500">نظام التشغيل:</span>
                            </div>
                            <span class="text-[11px] font-black text-gray-700 truncate max-w-[120px]" :title="device.os_version">{{ device.os_version || 'غير محدد' }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="toggleStatus(device.id)"
                                :class="['flex-1 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 shadow-lg', 
                                         device.status === 'active' ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 shadow-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 shadow-emerald-100']">
                            <component :is="device.status === 'active' ? ShieldAlertIcon : ShieldCheckIcon" class="w-4 h-4" />
                            {{ device.status === 'active' ? 'إيقاف الصلاحية' : 'تفعيل الصلاحية' }}
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="devices.data.length === 0" class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                    <div class="flex flex-col items-center gap-4">
                        <SmartphoneIcon class="w-16 h-16 text-gray-100" />
                        <p class="text-gray-400 font-black">لا توجد أجهزة مسجلة حالياً.</p>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="(devices.meta?.last_page || devices.last_page || 0) > 1" class="flex items-center justify-center gap-2">
                <Link v-for="link in devices.links" :key="link.label"
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
    </AuthenticatedLayout>
</template>
