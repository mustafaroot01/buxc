<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
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
}>();
</script>

<template>
    <Head title="لوحة التحكم" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight">
                لوحة تحكم الإدارة
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Grid - Premium & Neat Redesign -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 mb-10">
                    <!-- Total Students -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100/60 transition-all hover:shadow-xl hover:-translate-y-1 duration-300">
                        <div class="flex flex-col h-full justify-between gap-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-colors duration-300">
                                    <UsersIcon class="w-6 h-6" />
                                </div>
                                <span class="text-[10px] font-black tracking-widest text-indigo-300 uppercase">STUDENTS</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-gray-400 mb-1">إجمالي الطلاب</p>
                                <p class="text-3xl font-black text-gray-900">{{ stats.total_students }}</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-indigo-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Total Teachers -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100/60 transition-all hover:shadow-xl hover:-translate-y-1 duration-300">
                        <div class="flex flex-col h-full justify-between gap-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                                    <PresentationIcon class="w-6 h-6" />
                                </div>
                                <span class="text-[10px] font-black tracking-widest text-emerald-300 uppercase">TEACHERS</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-gray-400 mb-1">إجمالي الأساتذة</p>
                                <p class="text-3xl font-black text-gray-900">{{ stats.total_teachers }}</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Active Subjects -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100/60 transition-all hover:shadow-xl hover:-translate-y-1 duration-300">
                        <div class="flex flex-col h-full justify-between gap-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                                    <BookOpenIcon class="w-6 h-6" />
                                </div>
                                <span class="text-[10px] font-black tracking-widest text-amber-300 uppercase">SUBJECTS</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-gray-400 mb-1">المواد النشطة</p>
                                <p class="text-3xl font-black text-gray-900">{{ stats.active_subjects }}</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Today's Lectures -->
                    <div class="group relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100/60 transition-all hover:shadow-xl hover:-translate-y-1 duration-300">
                        <div class="flex flex-col h-full justify-between gap-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 group-hover:bg-rose-500 group-hover:text-white transition-colors duration-300">
                                    <ActivityIcon class="w-6 h-6" />
                                </div>
                                <span class="text-[10px] font-black tracking-widest text-rose-300 uppercase">TODAY</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-gray-400 mb-1">محاضرات اليوم</p>
                                <p class="text-3xl font-black text-gray-900">{{ stats.todays_lectures }}</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </div>

                    <!-- Active Warnings -->
                    <Link :href="route('admin.warnings.index')" class="group relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100/60 transition-all hover:shadow-xl hover:-translate-y-1 duration-300 block">
                        <div class="flex flex-col h-full justify-between gap-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-colors duration-300" :class="{'animate-pulse': stats.active_warnings > 0}">
                                    <AlertTriangleIcon class="w-6 h-6" />
                                </div>
                                <span class="text-[10px] font-black tracking-widest text-red-300 uppercase">WARNINGS</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-gray-400 mb-1">تنبيهات الغياب</p>
                                <p class="text-3xl font-black text-red-600">{{ stats.active_warnings }}</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-red-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </Link>

                    <!-- Banned Students -->
                    <Link :href="route('admin.students.index', { is_banned: 1 })" class="group relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100/60 transition-all hover:shadow-xl hover:-translate-y-1 duration-300 block">
                        <div class="flex flex-col h-full justify-between gap-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 group-hover:bg-rose-500 group-hover:text-white transition-colors duration-300" :class="{'animate-pulse': stats.banned_students > 0}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path><path d="m9 12 2 2 4-4"></path></svg>
                                </div>
                                <span class="text-[10px] font-black tracking-widest text-rose-300 uppercase">BANNED</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-gray-400 mb-1">الطلاب المحظورين</p>
                                <p class="text-3xl font-black text-rose-600">{{ stats.banned_students }}</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </Link>
                </div>

                <!-- Recent Activity Timeline -->
                <div class="overflow-hidden bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 relative">
                     <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                    <div class="p-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <ActivityIcon class="w-6 h-6 ml-2 text-indigo-500" />
                            أحدث نشاطات النظام
                        </h3>
                        
                        <div class="relative border-r-2 border-indigo-100 pr-6">
                            <ul v-if="recentActivity.length > 0" class="space-y-8">
                                <li v-for="activity in recentActivity" :key="activity.id" class="relative">
                                    <div class="absolute w-4 h-4 bg-teal-500 rounded-full -right-[31px] top-1 border-4 border-white shadow-md"></div>
                                    <div class="bg-gray-50 hover:bg-teal-50/50 transition-colors p-5 rounded-2xl border border-gray-100 shadow-sm">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-bold text-lg text-indigo-700">{{ activity.causer_name }}</span>
                                            <span class="text-xs font-semibold bg-teal-100 text-teal-800 px-3 py-1 rounded-full shadow-inner">{{ activity.created_at }}</span>
                                        </div>
                                        <p class="text-gray-600">{{ activity.description }}</p>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                    <ActivityIcon class="w-8 h-8 text-gray-400" />
                                </div>
                                <p class="text-lg text-gray-500">لا يوجد نشاطات حديثة مسجلة.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
