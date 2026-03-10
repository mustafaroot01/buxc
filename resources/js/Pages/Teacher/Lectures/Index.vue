<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, SearchIcon, BookOpenIcon, ClockIcon, MapPinIcon, QrCodeIcon, CheckCircle2Icon, EyeIcon, XCircleIcon, DownloadIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    lectures: any; 
    subjects: Array<{ id: number, name: string }>;
    stages: Array<{ id: number, name: string }>;
    filters: { search?: string, subject_id?: string, stage_id?: string, status?: string };
}>();

const search = ref(props.filters.search || '');
const subject_id = ref(props.filters.subject_id || '');
const stage_id = ref(props.filters.stage_id || '');
const statusFilter = ref(props.filters.status || '');

let filterTimeout: ReturnType<typeof setTimeout>;

const submitFilters = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(
            route('teacher.lectures.index'),
            { 
                search: search.value, 
                subject_id: subject_id.value, 
                stage_id: stage_id.value, 
                status: statusFilter.value 
            },
            { preserveState: true, replace: true }
        );
    }, 300);
};

watch([search, subject_id, stage_id, statusFilter], () => {
    submitFilters();
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="محاضراتي" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-800 flex items-center">
                    <BookOpenIcon class="w-6 h-6 ml-2 text-teal-600" />
                    سجل المحاضرات
                </h2>
                <Link :href="route('teacher.lectures.create')" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-purple-700 focus:outline-none transition-all duration-200">
                    <PlusIcon class="w-5 h-5 ml-2" />
                    محاضرة جديدة (فتح ماسح QR)
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Filters Section -->
                <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <SearchIcon class="w-5 h-5 text-gray-400" />
                            </div>
                            <input v-model="search" type="text" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 p-3 transition-colors" placeholder="ابحث باسم المحاضرة...">
                        </div>

                        <!-- Stage Filter -->
                        <div>
                            <select v-model="stage_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                <option value="">كل المراحل</option>
                                <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                    {{ stage.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Subject Filter -->
                        <div>
                            <select v-model="subject_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                <option value="">كل المواد</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select v-model="statusFilter" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                <option value="">كل الحالات</option>
                                <option value="active">نشطة (قيد التسجيل)</option>
                                <option value="completed">مكتملة (مغلقة)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Compact List of Lectures -->
                <div v-if="lectures.data.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    
                    <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-100 text-sm font-bold text-gray-500">
                        <div class="col-span-4 flex items-center gap-2">
                            <span class="w-6 text-center text-gray-400">#</span>
                            <span>المحاضرة والمادة</span>
                        </div>
                        <div class="col-span-3">التاريخ والوقت</div>
                        <div class="col-span-3">المكان والحضور</div>
                        <div class="col-span-2 text-left">الإجراءات</div>
                    </div>

                    <div class="divide-y divide-gray-50">
                        <div v-for="(lecture, index) in lectures.data" :key="lecture.id" 
                             class="group grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-4 px-4 md:px-6 py-5 items-center hover:bg-teal-50/30 transition-all duration-200 border-b border-gray-50 last:border-0">
                            
                            <!-- Title & Subject -->
                            <div class="col-span-1 md:col-span-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 text-center text-gray-400 font-mono text-sm hidden md:inline-block border-l pl-2 py-1">
                                        {{ (lectures.from || 1) + index }}
                                    </span>
                                    <div class="w-12 h-12 md:w-10 md:h-10 rounded-2xl md:rounded-xl flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-teal-500 to-emerald-600 md:bg-indigo-100 text-white md:text-indigo-600 shadow-lg shadow-teal-500/10 md:shadow-none group-hover:scale-105 transition-transform">
                                        <BookOpenIcon class="w-6 h-6 md:w-5 md:h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-[15px] md:text-base font-black text-gray-900 line-clamp-1 leading-tight mb-1 group-hover:text-teal-700 transition-colors">
                                            {{ lecture.title }}
                                        </h3>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] md:text-xs font-bold md:font-semibold text-teal-700 md:text-indigo-600 bg-teal-50 md:bg-indigo-50 px-2 py-0.5 rounded-md border border-teal-100 md:border-indigo-100">
                                                {{ lecture.subject?.name }}
                                            </span>
                                            <span v-if="lecture.status === 'completed'" class="md:hidden px-2 py-0.5 text-[10px] font-bold text-gray-500 bg-gray-100 rounded-md border border-gray-200">مغلقة</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Date & Time -->
                            <div class="col-span-1 md:col-span-3 flex md:flex-col items-center md:items-start justify-between md:justify-center border-t border-gray-50 md:border-0 pt-3 md:pt-0">
                                <div class="flex items-center text-xs md:text-sm font-bold md:font-medium text-gray-700 mb-1">
                                    <ClockIcon class="w-4 h-4 ml-1.5 text-teal-500 md:text-gray-400 flex-shrink-0" />
                                    <span class="hidden md:inline">{{ formatDate(lecture.date) }}</span>
                                    <span class="md:hidden">{{ formatDate(lecture.date).split(',')[0] }}</span> <!-- Short weekday for mobile -->
                                </div>
                                <div class="text-xs md:text-xs text-teal-600 md:text-gray-500 font-black md:font-mono md:pr-[22px] flex items-center bg-teal-50 md:bg-transparent px-2 py-1 md:p-0 rounded-lg">
                                    <span dir="ltr">{{ lecture.time }}</span>
                                </div>
                            </div>

                            <!-- Location & Attendance -->
                            <div class="col-span-1 md:col-span-3 flex md:flex-col items-center md:items-start justify-between md:justify-center border-t border-gray-50 md:border-0 pt-3 md:pt-0">
                                <div class="flex items-center text-xs md:text-sm font-bold md:font-medium text-gray-700 mb-2 md:mb-2 line-clamp-1">
                                    <MapPinIcon class="w-4 h-4 ml-1.5 text-teal-500 md:text-gray-400 flex-shrink-0" />
                                    {{ lecture.group?.stage?.name }} | {{ lecture.group?.name }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center text-[10px] md:text-xs text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 md:py-0.5 rounded-full md:rounded-md border border-emerald-100">
                                        <CheckCircle2Icon class="w-3.5 h-3.5 ml-1 text-emerald-600" />
                                        <span class="hidden md:inline">الحضور: </span>{{ lecture.present_count ?? (lecture.attendances?.length || 0) }}
                                    </div>
                                    <div class="flex items-center text-[10px] md:text-xs text-rose-700 font-bold bg-rose-50 px-2.5 py-1 md:py-0.5 rounded-full md:rounded-md border border-rose-100">
                                        <XCircleIcon class="w-3.5 h-3.5 ml-1 text-rose-600" />
                                        <span class="hidden md:inline">الغياب: </span>{{ lecture.absent_count ?? 0 }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="col-span-1 md:col-span-2 flex flex-row md:flex-wrap items-center justify-stretch md:justify-end gap-2 mt-4 md:mt-0 pt-4 md:pt-0 border-t border-gray-100 md:border-0">
                                <Link :href="route('teacher.lectures.show', lecture.id)" 
                                      class="flex-1 md:flex-none inline-flex justify-center items-center gap-1.5 px-3 py-3 md:py-1.5 text-[11px] md:text-[11px] font-black text-teal-700 bg-white border border-teal-200 rounded-xl md:rounded-lg hover:bg-teal-50 shadow-sm transition-all" 
                                      title="عرض التفاصيل">
                                    <EyeIcon class="w-4 h-4 md:w-3.5 md:h-3.5" />
                                    <span>التفاصيل</span>
                                </Link>
                                
                                <Link v-if="lecture.status === 'active'" :href="route('teacher.scanner.show', lecture.id)" 
                                      class="flex-1 md:flex-none inline-flex justify-center items-center gap-1.5 px-3 py-3 md:py-1.5 text-[11px] md:text-[11px] font-black text-white bg-teal-600 rounded-xl md:rounded-lg hover:bg-teal-700 shadow-md shadow-teal-500/20 transition-all" 
                                      title="فتح الماسح">
                                    <QrCodeIcon class="w-4 h-4 md:w-3.5 md:h-3.5" />
                                    <span>المسح</span>
                                </Link>

                                <a :href="route('teacher.lectures.export', lecture.id)" 
                                   class="flex-1 md:flex-none inline-flex justify-center items-center gap-1.5 px-3 py-3 md:py-1.5 text-[11px] md:text-[11px] font-black text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-xl md:rounded-lg hover:bg-indigo-100 transition-all" 
                                   title="تصدير Excel">
                                    <DownloadIcon class="w-4 h-4 md:w-3.5 md:h-3.5" />
                                    <span>تصدير</span>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 py-16 text-center">
                    <BookOpenIcon class="w-16 h-16 mx-auto mb-4 text-gray-200" />
                    <p class="text-xl font-bold text-gray-600 mb-2">لا توجد محاضرات في السجل</p>
                    <p class="text-gray-400 mb-6">قم بإنشاء محاضرة جديدة للبدء في مسح حضور الطلاب</p>
                    <Link :href="route('teacher.lectures.create')" class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                        <PlusIcon class="w-5 h-5 ml-2" /> بدء محاضرة جديدة
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="lectures.links?.length > 3" class="mt-8 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, key) in lectures.links" :key="key">
                            <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400 bg-white border border-gray-200 rounded-lg" v-html="link.label" />
                            <Link v-else :href="link.url" class="px-4 py-2 text-sm font-medium rounded-lg border focus:ring-2 focus:ring-indigo-500 transition-colors" :class="link.active ? 'bg-teal-50 border-indigo-200 text-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'" v-html="link.label" />
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
