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
                <!-- Stats Grid with Glassmorphism and Gradients -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 xl:grid-cols-5">
                    <!-- Total Students -->
                    <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-xl shadow-blue-500/10 rounded-2xl transition-all hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20 duration-300">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400 to-indigo-500 opacity-20 blur-3xl rounded-full -mr-16 -mt-16"></div>
                        <div class="p-6 relative z-10 flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-sm font-semibold text-gray-500 uppercase tracking-wider">إجمالي الطلاب</p>
                                <p class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">{{ stats.total_students }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-teal-50 to-emerald-100 text-blue-600 shadow-inner">
                                <UsersIcon class="w-8 h-8" />
                            </div>
                        </div>
                    </div>

                    <!-- Total Teachers -->
                    <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-xl shadow-emerald-500/10 rounded-2xl transition-all hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/20 duration-300">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-400 to-teal-500 opacity-20 blur-3xl rounded-full -mr-16 -mt-16"></div>
                        <div class="p-6 relative z-10 flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-sm font-semibold text-gray-500 uppercase tracking-wider">إجمالي الأساتذة</p>
                                <p class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">{{ stats.total_teachers }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 text-emerald-600 shadow-inner">
                                <PresentationIcon class="w-8 h-8" />
                            </div>
                        </div>
                    </div>

                    <!-- Active Subjects -->
                    <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-xl shadow-purple-500/10 rounded-2xl transition-all hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/20 duration-300">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-400 to-emerald-400 opacity-20 blur-3xl rounded-full -mr-16 -mt-16"></div>
                        <div class="p-6 relative z-10 flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-sm font-semibold text-gray-500 uppercase tracking-wider">المواد النشطة</p>
                                <p class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-emerald-500">{{ stats.active_subjects }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-purple-100 to-fuchsia-100 text-teal-600 shadow-inner">
                                <BookOpenIcon class="w-8 h-8" />
                            </div>
                        </div>
                    </div>

                    <!-- Today's Lectures -->
                    <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-xl shadow-orange-500/10 rounded-2xl transition-all hover:scale-105 hover:shadow-2xl hover:shadow-orange-500/20 duration-300">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-orange-400 to-amber-500 opacity-20 blur-3xl rounded-full -mr-16 -mt-16"></div>
                        <div class="p-6 relative z-10 flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-sm font-semibold text-gray-500 uppercase tracking-wider">محاضرات اليوم</p>
                                <p class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">{{ stats.todays_lectures }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-orange-100 to-amber-100 text-orange-600 shadow-inner">
                                <ActivityIcon class="w-8 h-8" />
                            </div>
                        </div>
                    </div>

                    <!-- Active Warnings (Link to page) -->
                    <Link :href="route('admin.warnings.index')" class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-xl shadow-red-500/10 rounded-2xl transition-all hover:scale-105 hover:shadow-2xl hover:shadow-red-500/20 duration-300 block">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-400 to-rose-500 opacity-20 blur-3xl rounded-full -mr-16 -mt-16"></div>
                        <div class="p-6 relative z-10 flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-[11px] font-bold text-red-600 uppercase tracking-wider">تنبيهات الغياب</p>
                                <p class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">{{ stats.active_warnings }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-red-100 to-rose-100 text-red-600 shadow-inner" :class="{'animate-pulse': stats.active_warnings > 0}">
                                <AlertTriangleIcon class="w-8 h-8" />
                            </div>
                        </div>
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
