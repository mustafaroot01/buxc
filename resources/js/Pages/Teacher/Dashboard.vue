<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    BookOpenIcon, UsersIcon, CheckCircle2Icon, CalendarDaysIcon,
    QrCodeIcon, PlayIcon, ClockIcon, GraduationCapIcon, ChevronRightIcon,
    SunIcon, AlertTriangleIcon, SearchIcon, ArrowLeftIcon
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        my_subjects: number;
        todays_lectures: number;
        total_lectures_given: number;
        total_students: number;
    };
    activeLectures: Array<{
        id: string;
        title: string;
        subject: { name: string; code: string };
        group: { name: string; study_type: string };
        attendances: Array<any>;
        start_time: string;
    }>;
    mySubjects: Array<{
        id: string;
        name: string;
        code: string;
        stage: { id: string; name: string };
        groups: Array<{ id: string; name: string; study_type: string }>;
    }>;
    recentWarnings?: Array<{
        id: string;
        student_name: string;
        stage_name: string;
        group_name: string;
        issued_at: string;
    }>;
}>();

const now = new Date();
const greetingHour = now.getHours();
const greeting = computed(() => {
    if (greetingHour < 12) return 'صباح الخير';
    if (greetingHour < 17) return 'مساء الخير';
    return 'مساء النور';
});

const todayDate = computed(() => {
    return now.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="لوحة التحكم - معلم" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg">
                    <GraduationCapIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">لوحة التحكم</h2>
                    <p class="text-sm text-gray-500 flex items-center gap-1">
                        <SunIcon class="w-3.5 h-3.5 text-amber-500" />
                        {{ todayDate }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Greeting Banner -->
                <div class="bg-gradient-to-l from-teal-600 via-teal-500 to-emerald-600 rounded-2xl p-6 text-white shadow-xl shadow-teal-500/20 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-4 left-8 w-32 h-32 rounded-full bg-white blur-2xl"></div>
                        <div class="absolute bottom-2 right-12 w-48 h-48 rounded-full bg-white blur-3xl"></div>
                    </div>
                    <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h1 class="text-2xl font-bold mb-1">{{ greeting }}، أستاذ 👋</h1>
                            <p class="text-teal-100 text-sm">
                                لديك <span class="font-bold text-white">{{ stats.todays_lectures }}</span> محاضرة نشطة اليوم
                            </p>
                        </div>
                        <Link :href="route('teacher.lectures.create')" 
                              class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white text-sm font-bold rounded-xl transition-all backdrop-blur-sm shadow-lg">
                            <PlayIcon class="w-4 h-4" />
                            ابدأ محاضرة جديدة
                        </Link>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
                                <BookOpenIcon class="w-5 h-5 text-teal-600" />
                            </div>
                            <span class="text-3xl font-black text-gray-900">{{ stats.my_subjects }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-500">موادي الدراسية</p>
                        <div class="h-1 mt-3 rounded-full bg-gradient-to-r from-teal-500 to-emerald-400 opacity-60"></div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                                <CalendarDaysIcon class="w-5 h-5 text-emerald-600" />
                            </div>
                            <span class="text-3xl font-black text-gray-900">{{ stats.todays_lectures }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-500">محاضرات اليوم</p>
                        <div class="h-1 mt-3 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 opacity-60"></div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
                                <CheckCircle2Icon class="w-5 h-5 text-teal-700" />
                            </div>
                            <span class="text-3xl font-black text-gray-900">{{ stats.total_lectures_given }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-500">إجمالي المحاضرات</p>
                        <div class="h-1 mt-3 rounded-full bg-gradient-to-r from-teal-600 to-emerald-500 opacity-60"></div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                                <UsersIcon class="w-5 h-5 text-emerald-700" />
                            </div>
                            <span class="text-3xl font-black text-gray-900">{{ stats.total_students }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-500">إجمالي طلابي</p>
                        <div class="h-1 mt-3 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 opacity-60"></div>
                    </div>
                </div>

                <!-- Warnings Banner Alerts (New) -->
                <div v-if="recentWarnings && recentWarnings.length > 0" class="bg-amber-50 rounded-2xl p-5 border border-amber-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <AlertTriangleIcon class="w-6 h-6 text-amber-600" />
                            <h3 class="text-base font-bold text-amber-900">تنبيهات طلابي الأخيرة</h3>
                            <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ recentWarnings.length }} تنبيهات</span>
                        </div>
                        <Link :href="route('teacher.warnings.index')" class="text-sm font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 transition-colors">
                            عرض جميع الإنذارات <ArrowLeftIcon class="w-4 h-4" />
                        </Link>
                    </div>
                    
                    <div class="space-y-3">
                        <div v-for="warning in recentWarnings" :key="warning.id" class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-white rounded-xl border border-amber-100 shadow-sm transition-all hover:bg-amber-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center border border-amber-200 shrink-0">
                                    <span class="text-amber-700 font-bold text-sm">{{ warning.student_name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ warning.student_name }}</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">{{ warning.stage_name }} · {{ warning.group_name }}</p>
                                </div>
                            </div>
                            <div class="mt-2 sm:mt-0 flex items-center gap-2 text-[11px] text-amber-700 bg-amber-100/50 px-2.5 py-1 rounded-md border border-amber-200/50">
                                <ClockIcon class="w-3.5 h-3.5" />
                                صدر إنذار {{ warning.issued_at }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                    <!-- Active Lectures Today -->
                    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-teal-500 to-emerald-400"></div>
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-6 bg-gradient-to-b from-teal-500 to-emerald-500 rounded-full"></div>
                                <h3 class="text-base font-bold text-gray-900">المحاضرات النشطة اليوم</h3>
                                <span v-if="activeLectures.length > 0" 
                                      class="text-xs font-bold bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full">
                                    {{ activeLectures.length }}
                                </span>
                            </div>
                            <Link :href="route('teacher.lectures.index')" 
                                  class="text-xs font-semibold text-teal-600 hover:text-teal-800 flex items-center gap-1">
                                عرض الكل <ChevronRightIcon class="w-3 h-3" />
                            </Link>
                        </div>
                        <div class="p-4">
                            <div v-if="activeLectures.length > 0" class="space-y-3">
                                <div v-for="lecture in activeLectures" :key="lecture.id" 
                                     class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-gray-50 hover:bg-teal-50 border border-gray-100 hover:border-teal-200 transition-all gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/20 flex-shrink-0 group-hover:scale-110 transition-transform">
                                            <QrCodeIcon class="w-6 h-6" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-black text-gray-900 text-[15px] line-clamp-1">{{ lecture.title }}</p>
                                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                                <span class="text-[10px] text-teal-700 font-bold bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">{{ lecture.subject?.name }}</span>
                                                <span class="text-[10px] text-gray-500 font-bold">{{ lecture.group?.name }}</span>
                                                <span class="text-[10px] text-gray-400 flex items-center gap-1 font-mono">
                                                    <ClockIcon class="w-3 h-3" />
                                                    {{ formatTime(lecture.start_time) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end gap-4 pt-3 sm:pt-0 border-t sm:border-0 border-gray-100">
                                        <div class="flex items-center gap-2">
                                            <div class="bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                                                <p class="text-sm font-black text-emerald-600 leading-none">{{ lecture.attendances?.length ?? 0 }} <span class="text-[10px] font-bold">حضور</span></p>
                                            </div>
                                        </div>
                                        <Link :href="route('teacher.scanner.show', lecture.id)" 
                                              class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-black rounded-xl transition-all shadow-md shadow-teal-500/30 active:scale-95">
                                            <QrCodeIcon class="w-4 h-4" />
                                            <span>فتح الماسح</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                    <CalendarDaysIcon class="w-8 h-8 text-gray-300" />
                                </div>
                                <p class="font-bold text-gray-500 mb-1">لا توجد محاضرات نشطة اليوم</p>
                                <p class="text-xs text-gray-400 mb-4">ابدأ محاضرة جديدة لتسجيل الحضور</p>
                                <Link :href="route('teacher.lectures.create')" 
                                      class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-500 hover:from-teal-700 hover:to-emerald-600 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-teal-500/20">
                                    <PlayIcon class="w-4 h-4" />
                                    بدء محاضرة جديدة
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- My Subjects Panel -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-teal-500 to-emerald-400"></div>
                        <div class="p-5 border-b border-gray-100 flex items-center gap-2">
                            <div class="w-2 h-6 bg-gradient-to-b from-teal-500 to-emerald-500 rounded-full"></div>
                            <h3 class="text-base font-bold text-gray-900">موادي وصلاحياتي</h3>
                        </div>
                        <div class="p-4 space-y-3 max-h-[370px] overflow-y-auto">
                            <div v-if="mySubjects.length === 0" class="py-8 text-center">
                                <BookOpenIcon class="w-10 h-10 mx-auto text-gray-200 mb-2" />
                                <p class="text-gray-400 text-sm">لم تُسند لك أي مواد بعد</p>
                            </div>
                            <div v-for="subject in mySubjects" :key="subject.id"
                                 class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-teal-50/40 hover:border-teal-100 transition-all">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                                            <BookOpenIcon class="w-4 h-4 text-teal-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ subject.name }}</p>
                                            <p class="text-[10px] text-gray-400" dir="ltr">{{ subject.code }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold bg-teal-50 text-teal-700 px-2 py-0.5 rounded-md border border-teal-100">
                                        {{ subject.stage?.name }}
                                    </span>
                                </div>
                                <!-- Assigned Groups -->
                                <div v-if="subject.groups && subject.groups.length > 0" class="flex flex-wrap gap-1 mt-2">
                                    <span v-for="group in subject.groups" :key="group.id" 
                                          class="text-[10px] font-bold px-2 py-0.5 rounded-md border"
                                          :class="group.study_type === 'morning' 
                                              ? 'bg-amber-50 text-amber-700 border-amber-100' 
                                              : 'bg-teal-50 text-teal-700 border-teal-100'">
                                        {{ group.name }} {{ group.study_type === 'morning' ? '🌅' : '🌙' }}
                                    </span>
                                </div>
                                <div v-else class="mt-1">
                                    <span class="text-[10px] text-red-500">⚠️ لا توجد مجموعات محددة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Link :href="route('teacher.lectures.create')" 
                          class="group flex items-center gap-4 p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:border-teal-300 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-teal-500/20 group-hover:scale-105 transition-transform">
                            <PlayIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">بدء محاضرة جديدة</p>
                            <p class="text-xs text-gray-400 mt-0.5">سجّل حضور طلابك الآن باستخدام QR</p>
                        </div>
                        <ChevronRightIcon class="w-5 h-5 text-gray-300 group-hover:text-teal-500 mr-auto transition-colors" />
                    </Link>

                    <Link :href="route('teacher.lectures.index')" 
                          class="group flex items-center gap-4 p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:border-emerald-300 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                            <BookOpenIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">سجل المحاضرات</p>
                            <p class="text-xs text-gray-400 mt-0.5">عرض جميع المحاضرات السابقة وسجلات الحضور</p>
                        </div>
                        <ChevronRightIcon class="w-5 h-5 text-gray-300 group-hover:text-emerald-500 mr-auto transition-colors" />
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
