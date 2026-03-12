<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PlayIcon, ArrowRightIcon, BookOpenIcon, QrCodeIcon } from 'lucide-vue-next';
import { computed, watch } from 'vue';

const props = defineProps<{
    subjects: Array<{ 
        id: string; 
        name: string; 
        stage_id: string;
        stage: { id: string; name: string };
        groups: Array<{ id: string; name: string; study_type: string }>;
    }>;
}>();

// Get current date/time strings for defaults
const now = new Date();
const todayDate = now.toLocaleDateString('en-CA'); // Gets YYYY-MM-DD in local time
const todayTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

const form = useForm({
    title: '',
    subject_id: '',
    stage_id: '',
    group_id: '',
    study_type: '',
    date: todayDate,
    time: todayTime,
});

// Derive the stage implicitly from the selected subject
const selectedSubjectStage = computed(() => {
    if (!form.subject_id) return null;
    const subject = props.subjects.find(s => s.id === form.subject_id);
    return subject ? subject.stage : null;
});

// Update the form's stage_id whenever subject changes
watch(() => form.subject_id, (newSubjectId) => {
    const subject = props.subjects.find(s => s.id === newSubjectId);
    if (subject) {
        form.stage_id = subject.stage_id;
        
        // Auto-select first study type if available
        const types = [...new Set(subject.groups.map(g => g.study_type))];
        if (types.length > 0) {
            form.study_type = types[0];
        } else {
            form.study_type = '';
        }
    } else {
        form.stage_id = '';
        form.study_type = '';
    }
    form.group_id = '';
});

// Computed to get unique study types for the selected subject
const availableStudyTypes = computed(() => {
    if (!form.subject_id) return [];
    const subject = props.subjects.find(s => s.id === form.subject_id);
    if (!subject) return [];
    
    const types = [...new Set(subject.groups.map(g => g.study_type))];
    return types.map(t => ({
        value: t,
        label: t === 'morning' ? 'صباحي' : 'مسائي'
    }));
});

// Filter available groups based on selected subject and study type
const availableGroups = computed(() => {
    if (!form.subject_id || !form.study_type) return [];
    
    const subject = props.subjects.find(s => s.id === form.subject_id);
    if (!subject) return [];
    
    // Only return groups strictly assigned to this subject
    return subject.groups.filter((g: any) => g.study_type === form.study_type);
});

// Reset group when study type changes
watch(() => form.study_type, () => {
    form.group_id = '';
});

const submit = () => {
    form.post(route('teacher.lectures.store'));
};
</script>

<template>
    <Head title="بدء محاضرة جديدة" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('teacher.lectures.index')" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
                    <ArrowRightIcon class="w-6 h-6" />
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-800 flex items-center pr-4 border-r-2 border-gray-200">
                    <BookOpenIcon class="w-6 h-6 ml-2 text-teal-600" />
                    بدء محاضرة جديدة
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                    <div class="p-8">
                        
                        <div class="mb-8 p-4 bg-teal-50 rounded-xl border border-indigo-100 flex items-start">
                            <QrCodeIcon class="w-6 h-6 text-teal-600 mt-0.5 ml-3 flex-shrink-0" />
                            <div>
                                <h4 class="font-bold text-indigo-900 mb-1">فتح مسجل الحضور</h4>
                                <p class="text-sm text-indigo-700">بمجرد إنشاء المحاضرة، سيتم تحويلك فوراً إلى صفحة المسح الضوئي (QR Scanner) لتبدأ بتسجيل حضور الطلاب هذه المحاضرة.</p>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            
                            <!-- General Info -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-900 border-b pb-2">معلومات المحاضرة الأساسية</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">عنوان / موضوع المحاضرة <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="form.title" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" placeholder="مثال: مقدمة في قواعد البيانات - الأسبوع الأول">
                                        <div v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">المادة الدراسية <span class="text-red-500">*</span></label>
                                        <select v-model="form.subject_id" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                            <option value="" disabled>-- إختر المادة --</option>
                                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                                {{ subject.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.subject_id" class="mt-2 text-sm text-red-600">{{ form.errors.subject_id }}</div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">تاريخ المحاضرة</label>
                                            <input type="date" v-model="form.date" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" dir="ltr">
                                            <div v-if="form.errors.date" class="mt-2 text-sm text-red-600">{{ form.errors.date }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">وقت البدء</label>
                                            <input type="time" v-model="form.time" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" dir="ltr">
                                            <div v-if="form.errors.time" class="mt-2 text-sm text-red-600">{{ form.errors.time }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Targeting -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mt-8">الجمهور المستهدف (الطلاب)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">المرحلة الدراسية <span class="text-red-500">*</span></label>
                                        <div v-if="selectedSubjectStage" class="bg-gray-100 border border-gray-200 text-gray-700 font-bold text-sm rounded-xl block w-full p-3 cursor-not-allowed">
                                            {{ selectedSubjectStage.name }}
                                        </div>
                                        <div v-else class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded-xl block w-full p-3">
                                            يرجى اختيار المادة أولاً
                                        </div>
                                        <input type="hidden" v-model="form.stage_id" required>
                                        <div v-if="form.errors.stage_id" class="mt-2 text-sm text-red-600">{{ form.errors.stage_id }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">نوع الدراسة <span class="text-red-500">*</span></label>
                                        <select v-model="form.study_type" required 
                                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors disabled:bg-gray-100 disabled:opacity-50"
                                                :disabled="!form.subject_id || availableStudyTypes.length === 0">
                                            <option value="" disabled v-if="!form.subject_id">اختر المادة أولاً</option>
                                            <option value="" disabled v-else-if="availableStudyTypes.length === 0">لا توجد أنواع دراسة متاحة</option>
                                            <option v-for="type in availableStudyTypes" :key="type.value" :value="type.value">
                                                {{ type.label }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.study_type" class="mt-2 text-sm text-red-600">{{ form.errors.study_type }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">المجموعة (الگروب) <span class="text-red-500">*</span></label>
                                        <select v-model="form.group_id" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors disabled:bg-gray-100 disabled:opacity-50" :disabled="!form.study_type || !form.stage_id || availableGroups.length === 0">
                                            <option value="" disabled v-if="!form.stage_id">اختر المادة أولاً</option>
                                            <option value="" disabled v-else-if="!form.study_type">اختر نوع الدراسة</option>
                                            <option value="" disabled v-else-if="availableGroups.length === 0">لا توجد مجموعات مخصصة لك</option>
                                            <option value="" disabled v-else>-- اختر المجموعة --</option>
                                            <option v-for="group in availableGroups" :key="group.id" :value="group.id">
                                                {{ group.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.group_id" class="mt-2 text-sm text-red-600">{{ form.errors.group_id }}</div>
                                    </div>

                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end pt-6 border-t border-gray-100">
                                <Link :href="route('teacher.lectures.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-all duration-200 ml-3">
                                    إلغاء
                                </Link>
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-purple-700 focus:outline-none transition-all duration-200 disabled:opacity-50 group">
                                    <PlayIcon class="w-5 h-5 ml-2 group-hover:scale-110 transition-transform" />
                                    حفظ وبدء المسح الآن
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
