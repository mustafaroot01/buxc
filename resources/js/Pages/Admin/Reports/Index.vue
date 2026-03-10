<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    FileSpreadsheetIcon, DownloadIcon, CalendarIcon, 
    LayersIcon, UsersIcon, BookOpenIcon, FilterIcon,
    RefreshCwIcon
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    stages: Array<{
        id: string;
        name: string;
        groups: Array<{ id: string; name: string; study_type: string }>;
        subjects: Array<{ id: string; name: string }>;
    }>;
    lectures: Array<{
        id: string;
        title: string;
        subject_id: string;
        group_id: string;
        subject: { name: string };
        group: { name: string; stage_id: string; stage: { name: string } };
        date: string;
    }>;
}>();

const form = useForm({
    lecture_id: '',
    start_date: '',
    end_date: '',
    stage_id: '',
    study_type: '',
    subject_id: '',
    group_id: '',
});

const availableGroups = computed(() => {
    if (!form.stage_id) return [];
    const stage = props.stages.find(s => s.id === form.stage_id);
    if (!stage) return [];
    
    let groups = stage.groups;
    if (form.study_type) {
        groups = groups.filter(g => g.study_type === form.study_type);
    }
    return groups;
});

const availableSubjects = computed(() => {
    if (!form.stage_id) return [];
    const stage = props.stages.find(s => s.id === form.stage_id);
    return stage ? stage.subjects : [];
});

const lectureSearch = ref('');

const filteredLectures = computed(() => {
    let result = props.lectures.filter(lecture => {
        // Filter by Stage
        if (form.stage_id && lecture.group?.stage_id !== form.stage_id) return false;
        
        // Filter by Subject
        if (form.subject_id && lecture.subject_id !== form.subject_id) return false;
        
        // Filter by Group
        if (form.group_id && lecture.group_id !== form.group_id) return false;
        
        // Filter by Dates
        if (form.start_date && lecture.date < form.start_date) return false;
        if (form.end_date && lecture.date > form.end_date) return false;

        // Search Match
        if (lectureSearch.value) {
            const searchLower = lectureSearch.value.toLowerCase();
            const subjectName = (lecture.subject?.name || '').toLowerCase();
            const dateStr = lecture.date || '';
            const titleStr = (lecture.title || '').toLowerCase();
            
            if (!subjectName.includes(searchLower) && 
                !dateStr.includes(searchLower) && 
                !titleStr.includes(searchLower)) {
                return false;
            }
        }
        
        return true;
    });

    // Limit to 50 for better performance and UX if no specific search is active
    return result.slice(0, 100);
});

const downloadReport = () => {
    const params = new URLSearchParams();
    if (form.lecture_id) params.append('lecture_id', form.lecture_id);
    if (form.start_date) params.append('start_date', form.start_date);
    if (form.end_date) params.append('end_date', form.end_date);
    if (form.stage_id) params.append('stage_id', form.stage_id);
    if (form.study_type) params.append('study_type', form.study_type);
    if (form.subject_id) params.append('subject_id', form.subject_id);
    if (form.group_id) params.append('group_id', form.group_id);

    window.location.href = route('admin.reports.export') + '?' + params.toString();
};

const resetFilters = () => {
    form.reset();
};
</script>

<template>
    <Head title="التقارير والإحصائيات" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center ml-3">
                        <FileSpreadsheetIcon class="w-6 h-6 text-indigo-600" />
                    </div>
                    <h2 class="text-xl font-black text-gray-800 border-r-2 pr-4 border-gray-200">
                        التقارير والإحصائيات
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                
                <!-- Main Controls Card -->
                <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden mb-8">
                    <div class="h-2 bg-gradient-to-r from-teal-500 via-emerald-500 to-teal-600"></div>
                    
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                                    توليد تقرير الحضور والغياب
                                    <span class="px-3 py-1 bg-teal-50 text-teal-700 text-xs rounded-full border border-teal-100 font-bold">Smart Export</span>
                                </h3>
                                <p class="text-gray-500 mt-2 font-medium">قم بفلترة البيانات بدقة لاستخراج تقارير Excel احترافية.</p>
                            </div>
                            <button @click="resetFilters" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-xl transition-all">
                                <RefreshCwIcon class="w-4 h-4" />
                                إعادة تعيين الفلاتر
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Date Range Group -->
                            <div class="space-y-4 lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                <div class="md:col-span-2 flex items-center gap-2 mb-2">
                                    <CalendarIcon class="w-4 h-4 text-teal-600" />
                                    <span class="text-sm font-black text-gray-700">النطاق الزمني للتقرير</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-2 mr-1">من تاريخ</label>
                                    <input type="date" v-model="form.start_date" class="w-full bg-white border-gray-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-2 mr-1">إلى تاريخ</label>
                                    <input type="date" v-model="form.end_date" class="w-full bg-white border-gray-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-12">
                                </div>
                            </div>

                            <!-- Stage Selection -->
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-sm font-black text-gray-700 mb-2 mr-1">
                                    <LayersIcon class="w-4 h-4 text-teal-600" />
                                    المرحلة الدراسية
                                </label>
                                <select v-model="form.stage_id" @change="form.group_id = ''; form.subject_id = ''" class="w-full bg-gray-50 border-gray-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-12">
                                    <option value="">-- كل المراحل --</option>
                                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                </select>
                            </div>

                            <!-- Study Type -->
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-sm font-black text-gray-700 mb-2 mr-1">
                                    <FilterIcon class="w-4 h-4 text-teal-600" />
                                    نوع الدراسة
                                </label>
                                <select v-model="form.study_type" @change="form.group_id = ''" class="w-full bg-gray-50 border-gray-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-12">
                                    <option value="">-- كل الدراسات --</option>
                                    <option value="morning">صباحي ☀️</option>
                                    <option value="evening">مسائي 🌙</option>
                                </select>
                            </div>

                            <!-- Group Selection -->
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-sm font-black text-gray-700 mb-2 mr-1">
                                    <UsersIcon class="w-4 h-4 text-teal-600" />
                                    المجموعة (الكروب)
                                </label>
                                <select v-model="form.group_id" :disabled="!form.stage_id" class="w-full bg-gray-50 border-gray-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-12 disabled:opacity-50">
                                    <option value="">-- كل المجموعات --</option>
                                    <option v-for="group in availableGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                                </select>
                            </div>

                            <!-- Subject Selection -->
                            <div class="space-y-2 lg:col-span-2">
                                <label class="flex items-center gap-2 text-sm font-black text-gray-700 mb-2 mr-1">
                                    <BookOpenIcon class="w-4 h-4 text-teal-600" />
                                    المادة الدراسية
                                </label>
                                <select v-model="form.subject_id" :disabled="!form.stage_id" class="w-full bg-gray-50 border-gray-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-12 disabled:opacity-50">
                                    <option value="">-- كل المواد --</option>
                                    <option v-for="subject in availableSubjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center justify-between text-sm font-black text-gray-700 mb-2 mr-1">
                                    <div class="flex items-center gap-2">
                                        <RefreshCwIcon class="w-4 h-4 text-teal-600" />
                                        أو اختر محاضرة محددة
                                    </div>
                                    <span v-if="lectureSearch || form.stage_id" class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                                        {{ filteredLectures.length }} نتيجة
                                    </span>
                                </label>
                                
                                <div class="relative group">
                                    <input 
                                        v-model="lectureSearch" 
                                        type="text" 
                                        placeholder="ابحث عن مادة أو تاريخ..." 
                                        class="w-full bg-amber-50/50 border-amber-100 rounded-t-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-medium text-xs h-10 border-b-0 placeholder:text-amber-300"
                                    >
                                    <select v-model="form.lecture_id" class="w-full bg-amber-50/30 border-amber-100 rounded-b-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-bold text-sm h-12 border-t-dashed">
                                        <option value="">-- لا يوجد (سحب شامل) --</option>
                                        <option v-for="lecture in filteredLectures" :key="lecture.id" :value="lecture.id">
                                            {{ lecture.date }} | {{ lecture.subject?.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-between mt-12 pt-8 border-t border-gray-100">
                            <p class="text-[11px] text-gray-400 font-medium max-w-sm">
                                * سيتم استخراج البيانات بناءً على الفلاتر المختارة أعلاه في ملف Excel يدعم اللغة العربية.
                            </p>
                            <button @click="downloadReport" class="group relative inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-teal-600 to-emerald-500 hover:from-teal-700 hover:to-emerald-600 text-white font-black rounded-[1.25rem] shadow-xl shadow-teal-200/50 transition-all hover:scale-[1.02] active:scale-95 overflow-hidden">
                                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                <DownloadIcon class="w-5 h-5 relative z-10" />
                                <span class="relative z-10">استخراج التقرير الشامل</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Tip -->
                <div class="bg-indigo-50 border-r-4 border-indigo-400 p-6 rounded-[1.5rem] flex items-start gap-4 shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-black text-indigo-900 mb-1">نصيحة تقنية</h4>
                        <p class="text-sm text-indigo-700 leading-relaxed font-medium">تم تحسين النظام ليدعم تحميل أكثر من <strong class="underline">5000 سجل</strong> دفعة واحدة بسرعة عالية. إذا كنت تواجه بطئاً، حاول تضييق النطاق الزمني للتقرير.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
