<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ShieldAlertIcon, DownloadIcon, ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next';

defineProps<{
    warnings: {
        data: Array<{
            id: string;
            student_name: string;
            student_external_id: string;
            stage_name: string;
            group_name: string;
            active_warnings: number;
            total_warnings: number;
            consecutive_absences: number;
            last_warning_date: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    }
}>();
</script>

<template>
    <Head title="تنبيهات الغياب" />

    <AuthenticatedLayout>
        
        <!-- Header Section -->
        <div class="bg-gray-50 border-b border-gray-100 print-hidden">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between py-6">
                    <div class="flex items-center">
                        <ShieldAlertIcon class="w-8 h-8 mr-3 text-red-500" />
                        <div>
                            <h2 class="text-2xl font-black leading-tight text-gray-900 tracking-tight">
                                سجل إنذارات الطلبة
                            </h2>
                            <p class="text-sm font-medium text-gray-500 mt-1">
                                قائمة بالطلبة الذين تجاوزوا الحد المسموح للغياب وحصلوا على إنذارات.
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <a :href="route('admin.warnings.export')" target="_blank" class="inline-flex items-center px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm hover:bg-emerald-100 hover:shadow-md transition-all">
                            <DownloadIcon class="w-4 h-4 ml-2" />
                            تصدير تقرير Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="py-12 bg-[#fafafa] min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100/60 overflow-hidden relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-500 via-rose-500 to-orange-500"></div>

                    <!-- Filter Bar (Optional for future) -->
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            إجمالي الطلبة المُنذرين: <span class="text-red-600 mr-2">{{ warnings.total }}</span>
                        </h3>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-right">
                            <thead class="bg-gray-50/80 text-gray-600 font-bold text-[13px] border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 truncate">اسم الطالب</th>
                                    <th class="px-6 py-4 truncate">المرحلة والمجموعة</th>
                                    <th class="px-4 py-4 text-center truncate w-32">الغياب المتتالي الحالي</th>
                                    <th class="px-4 py-4 text-center truncate w-32">إجمالي الإنذارات</th>
                                    <th class="px-4 py-4 text-center truncate w-32">الإنذارات النشطة</th>
                                    <th class="px-4 py-4 text-center truncate w-40">تاريخ آخر إنذار</th>
                                    <th class="px-6 py-4 text-left truncate w-32">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50/80 bg-white">
                                <tr v-for="student in warnings.data" :key="student.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-gray-900 text-sm whitespace-nowrap">{{ student.student_name }}</div>
                                        <div class="text-[11px] font-mono text-gray-500 mt-1">{{ student.student_external_id }}</div>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-medium text-gray-600 whitespace-nowrap">
                                        {{ student.stage_name }} - {{ student.group_name }}
                                    </td>
                                    <td class="px-4 py-5 text-center">
                                        <div class="flex justify-center items-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-black text-sm border shadow-sm"
                                                :class="student.consecutive_absences > 0 ? 'bg-red-50 text-red-600 border-red-100' : 'bg-gray-50 text-gray-400 border-gray-100'">
                                                {{ student.consecutive_absences }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 text-center">
                                        <div class="flex justify-center items-center">
                                            <span class="font-bold text-gray-700 text-sm">
                                                {{ student.total_warnings }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 text-center">
                                        <div class="flex justify-center items-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm border shadow-sm"
                                                :class="student.active_warnings > 0 ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                                                {{ student.active_warnings > 0 ? student.active_warnings : '0' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 text-center whitespace-nowrap">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-[13px] font-mono font-bold text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 shadow-sm">
                                                {{ student.last_warning_date }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-left">
                                        <Link :href="route('admin.students.show', student.id)" 
                                            class="inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-sm">
                                            ملف الطالب
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="warnings.data.length === 0">
                                    <td colspan="7" class="px-6 py-24 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <ShieldAlertIcon class="w-16 h-16 text-gray-200 mb-4" />
                                            <p class="text-lg font-medium text-gray-500">لا يوجد أي طلاب حاصلين على إنذار حالياً</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-gray-50 flex flex-col sm:flex-row items-center justify-between" v-if="warnings.total > warnings.per_page">
                        <p class="text-sm text-gray-500 font-medium mb-4 sm:mb-0">
                            إظهار <span class="font-bold text-gray-900">{{ warnings.data.length }}</span> من أصل <span class="font-bold text-gray-900">{{ warnings.total }}</span> طالب
                        </p>
                        
                        <div class="flex items-center gap-1.5" dir="ltr">
                            <template v-for="(link, index) in warnings.links" :key="index">
                                <Link 
                                    v-if="link.url"
                                    :href="link.url" 
                                    preserve-scroll
                                    class="min-w-[40px] h-10 px-2 flex items-center justify-center rounded-xl border text-sm font-bold transition-all shadow-sm"
                                    :class="link.active 
                                        ? 'bg-gradient-to-r from-red-600 to-rose-500 text-white border-transparent' 
                                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300'"
                                    v-html="link.label"
                                />
                                <span v-else 
                                    class="min-w-[40px] h-10 px-2 flex items-center justify-center rounded-xl border border-gray-100 bg-gray-50/50 text-gray-300 text-sm font-medium" 
                                    v-html="link.label">
                                </span>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
