<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { UsersIcon, BookOpenIcon, PresentationIcon, ActivityIcon, AlertTriangleIcon } from 'lucide-vue-next';

defineProps<{
    stats: {
        total_students: number;
        total_teachers: number;
        active_subjects: number;
        todays_lectures: number;
        active_warnings: number;
        banned_students: number;
    };
    recentActivity: Array<{
        id: number;
        description: string;
        causer_name: string;
        created_at: string;
    }>;
    todayDate: string;
}>();
</script>

<template>
    <Head title="لوحة التحكم" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="text-2xl font-black leading-tight text-gray-900 tracking-tight">
                    لوحة تحكم الإدارة
                </h2>
                <div class="text-[14px] font-bold text-gray-500 bg-white px-5 py-2.5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 ml-2.5 text-indigo-500"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    {{ todayDate }}
                </div>
            </div>
        </template>

        <div class="py-12 bg-[#fafafa] min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Premium Stats Grid: 3 columns -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    
                    <!-- Total Students -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-7 shadow-sm border border-gray-100/80 hover:border-indigo-100 transition-all hover:shadow-xl hover:-translate-y-1.5 duration-300 cursor-default">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-sm font-bold text-gray-400 mb-1.5">إجمالي الطلاب</p>
                                <p class="text-4xl font-black text-gray-900 tracking-tight">{{ stats.total_students }}</p>
                            </div>
                            <div class="w-16 h-16 rounded-3xl bg-indigo-50 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-lg group-hover:shadow-indigo-500/30">
                                <UsersIcon class="w-8 h-8" />
                            </div>
                        </div>
                        <div class="flex items-center text-[11px] font-bold text-indigo-600 bg-indigo-50/80 w-fit px-3 py-1.5 rounded-xl group-hover:bg-indigo-100 transition-colors">
                            الطلاب المسجلين بالنظام
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-400 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Total Teachers -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-7 shadow-sm border border-gray-100/80 hover:border-teal-100 transition-all hover:shadow-xl hover:-translate-y-1.5 duration-300 cursor-default">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-sm font-bold text-gray-400 mb-1.5">الأساتذة والمحاضرين</p>
                                <p class="text-4xl font-black text-gray-900 tracking-tight">{{ stats.total_teachers }}</p>
                            </div>
                            <div class="w-16 h-16 rounded-3xl bg-teal-50 flex items-center justify-center text-teal-500 group-hover:bg-teal-500 group-hover:text-white transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-lg group-hover:shadow-teal-500/30">
                                <PresentationIcon class="w-8 h-8" />
                            </div>
                        </div>
                        <div class="flex items-center text-[11px] font-bold text-teal-600 bg-teal-50/80 w-fit px-3 py-1.5 rounded-xl group-hover:bg-teal-100 transition-colors">
                            الكادر التدريسي المعتمد
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-teal-400 to-teal-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Active Subjects -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-7 shadow-sm border border-gray-100/80 hover:border-amber-100 transition-all hover:shadow-xl hover:-translate-y-1.5 duration-300 cursor-default">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-sm font-bold text-gray-400 mb-1.5">المواد الدراسية</p>
                                <p class="text-4xl font-black text-gray-900 tracking-tight">{{ stats.active_subjects }}</p>
                            </div>
                            <div class="w-16 h-16 rounded-3xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300 group-hover:scale-110 group-hover:-rotate-3 group-hover:shadow-lg group-hover:shadow-amber-500/30">
                                <BookOpenIcon class="w-8 h-8" />
                            </div>
                        </div>
                        <div class="flex items-center text-[11px] font-bold text-amber-600 bg-amber-50/80 w-fit px-3 py-1.5 rounded-xl group-hover:bg-amber-100 transition-colors">
                            المواد المفتوحة حالياً
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 to-amber-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Today's Lectures -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-7 shadow-sm border border-gray-100/80 hover:border-sky-100 transition-all hover:shadow-xl hover:-translate-y-1.5 duration-300 cursor-default">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-sm font-bold text-gray-400 mb-1.5">محاضرات اليوم</p>
                                <p class="text-4xl font-black text-gray-900 tracking-tight">{{ stats.todays_lectures }}</p>
                            </div>
                            <div class="w-16 h-16 rounded-3xl bg-sky-50 flex items-center justify-center text-sky-500 group-hover:bg-sky-500 group-hover:text-white transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-lg group-hover:shadow-sky-500/30">
                                <ActivityIcon class="w-8 h-8" />
                            </div>
                        </div>
                        <div class="flex items-center text-[11px] font-bold text-sky-600 bg-sky-50/80 w-fit px-3 py-1.5 rounded-xl group-hover:bg-sky-100 transition-colors">
                            الجدول اليومي النشط
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-sky-400 to-sky-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Active Warnings -->
                    <Link :href="route('admin.warnings.index')" class="group relative overflow-hidden bg-white rounded-[2rem] p-7 shadow-sm border border-gray-100/80 hover:border-orange-100 transition-all hover:shadow-xl hover:-translate-y-1.5 duration-300 block">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-sm font-bold text-gray-400 mb-1.5">تنبيهات الغياب</p>
                                <p class="text-4xl font-black text-orange-500 tracking-tight">{{ stats.active_warnings }}</p>
                            </div>
                            <div class="w-16 h-16 rounded-3xl bg-orange-50 flex items-center justify-center text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition-all duration-300 group-hover:scale-110 group-hover:-rotate-3 group-hover:shadow-lg group-hover:shadow-orange-500/30" :class="{'animate-pulse': stats.active_warnings > 0}">
                                <AlertTriangleIcon class="w-8 h-8" />
                            </div>
                        </div>
                        <div class="flex items-center text-[11px] font-bold text-orange-600 bg-orange-50/80 w-fit px-3 py-1.5 rounded-xl group-hover:bg-orange-100 transition-colors">
                            غيابات تحتاج لإجراء
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-orange-400 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </Link>

                    <!-- Banned Students -->
                    <Link :href="route('admin.students.index', { is_banned: 1 })" class="group relative overflow-hidden bg-white rounded-[2rem] p-7 shadow-sm border border-gray-100/80 hover:border-rose-100 transition-all hover:shadow-xl hover:-translate-y-1.5 duration-300 block">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-sm font-bold text-gray-400 mb-1.5">الطلاب المحظورين</p>
                                <p class="text-4xl font-black text-rose-600 tracking-tight">{{ stats.banned_students }}</p>
                            </div>
                            <div class="w-16 h-16 rounded-3xl bg-rose-50 flex items-center justify-center text-rose-500 group-hover:bg-rose-600 group-hover:text-white transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-lg group-hover:shadow-rose-500/30" :class="{'animate-pulse': stats.banned_students > 0}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path><path d="m9 12 2 2 4-4"></path></svg>
                            </div>
                        </div>
                        <div class="flex items-center text-[11px] font-bold text-rose-600 bg-rose-50/80 w-fit px-3 py-1.5 rounded-xl group-hover:bg-rose-100 transition-colors">
                            انقر لمراجعة سجلاتهم
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-rose-500 to-rose-700 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </Link>

                </div>

                <!-- Premium Timeline Redesign -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100/80 p-6 md:p-10 mb-8">
                    <div class="flex items-center mb-10 pb-6 border-b border-gray-100/60">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50/80 flex items-center justify-center text-indigo-600 ml-5 shadow-sm">
                            <ActivityIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-1">أحدث نشاطات النظام</h3>
                            <p class="text-sm font-medium text-gray-400">سجل زمني لعمليات الأساتذة والإدارة</p>
                        </div>
                    </div>
                    
                    <div class="relative border-r-2 border-dashed border-gray-200 pr-10 ml-6 md:ml-12">
                        <ul v-if="recentActivity.length > 0" class="space-y-10">
                            <li v-for="activity in recentActivity" :key="activity.id" class="relative group">
                                <div class="absolute w-5 h-5 bg-white rounded-full -right-[49px] top-1.5 border-[5px] border-indigo-100 group-hover:border-indigo-500 transition-colors duration-500 shadow-sm z-10"></div>
                                <div class="bg-gray-50/40 group-hover:bg-indigo-50/20 transition-colors p-6 rounded-2xl border border-gray-100 group-hover:border-indigo-100 group-hover:shadow-md duration-300">
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 gap-2">
                                        <span class="font-black text-lg text-gray-900">{{ activity.causer_name }}</span>
                                        <span class="text-xs font-bold text-gray-500 bg-white px-4 py-1.5 rounded-full border border-gray-100 shadow-sm">{{ activity.created_at }}</span>
                                    </div>
                                    <p class="text-[15px] font-semibold text-gray-600 leading-relaxed">{{ activity.description }}</p>
                                </div>
                            </li>
                        </ul>
                        <div v-else class="text-center py-20">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-50 mb-5 border border-gray-100">
                                <ActivityIcon class="w-10 h-10 text-gray-300" />
                            </div>
                            <p class="text-xl font-black text-gray-900 mb-2">النظام هادئ جداً</p>
                            <p class="text-[15px] font-medium text-gray-500">لا يوجد بيانات او نشاطات مسجلة حتى الآن.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
