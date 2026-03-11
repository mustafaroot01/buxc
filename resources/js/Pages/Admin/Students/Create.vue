<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { SaveIcon, ArrowRightIcon, UserIcon } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    stages: Array<{
        id: string;
        name: string;
        groups: Array<{
            id: string;
            name: string;
            stage_id: string;
            study_type: string;
        }>;
    }>;
}>();

const form = useForm({
    first_name: '',
    second_name: '',
    last_name: '',
    student_external_id: '',
    gender: 'male',
    stage_id: '',
    group_id: '',
    study_type: 'morning',
    photo: null as File | null,
});

// Computed property to filter groups based on selected stage and study type
const availableGroups = computed(() => {
    if (!form.stage_id || !form.study_type) return [];
    
    const stage = props.stages.find(s => s.id === form.stage_id);
    if (!stage) return [];
    
    return stage.groups.filter((g: any) => g.study_type === form.study_type);
});

// Reset group when stage or study type changes
watch([() => form.stage_id, () => form.study_type], () => {
    form.group_id = '';
});

const submit = () => {
    form.post(route('admin.students.store'));
};
</script>

<template>
    <Head title="إضافة طالب جديد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('admin.students.index')" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
                    <ArrowRightIcon class="w-6 h-6" />
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-800 flex items-center pr-4 border-r-2 border-gray-200">
                    <UserIcon class="w-6 h-6 ml-2 text-teal-600" />
                    إضافة طالب جديد
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-8">
                            
                            <!-- Personal Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-900 border-b pb-2">المعلومات الشخصية</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">الاسم الأول <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="form.first_name" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" placeholder="الاسم الأول">
                                        <div v-if="form.errors.first_name" class="mt-2 text-sm text-red-600">{{ form.errors.first_name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">الاسم الثاني (الأب) </label>
                                        <input type="text" v-model="form.second_name" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" placeholder="الاسم الثاني">
                                        <div v-if="form.errors.second_name" class="mt-2 text-sm text-red-600">{{ form.errors.second_name }}</div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">اللقب <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="form.last_name" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" placeholder="اللقب">
                                        <div v-if="form.errors.last_name" class="mt-2 text-sm text-red-600">{{ form.errors.last_name }}</div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">الرقم الجامعي (External ID) <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="form.student_external_id" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors text-left" dir="ltr" placeholder="STUD-2026-001">
                                        <div v-if="form.errors.student_external_id" class="mt-2 text-sm text-red-600">{{ form.errors.student_external_id }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">الجنس <span class="text-red-500">*</span></label>
                                        <select v-model="form.gender" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                            <option value="male">ذكر</option>
                                            <option value="female">أنثى</option>
                                        </select>
                                        <div v-if="form.errors.gender" class="mt-2 text-sm text-red-600">{{ form.errors.gender }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">الصورة الشخصية (اختياري)</label>
                                        <input type="file" @input="form.photo = ($event.target as HTMLInputElement).files?.[0] || null" accept="image/*" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 block w-full p-2 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-indigo-700 hover:file:bg-teal-100" />
                                        <div v-if="form.errors.photo" class="mt-2 text-sm text-red-600">{{ form.errors.photo }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mt-8">البيانات الأكاديمية</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">المرحلة الدراسية <span class="text-red-500">*</span></label>
                                        <select v-model="form.stage_id" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                            <option value="" disabled>-- إختر المرحلة --</option>
                                            <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                                {{ stage.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.stage_id" class="mt-2 text-sm text-red-600">{{ form.errors.stage_id }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">نوع الدراسة <span class="text-red-500">*</span></label>
                                        <select v-model="form.study_type" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                            <option value="morning">صباحي</option>
                                            <option value="evening">مسائي</option>
                                        </select>
                                        <div v-if="form.errors.study_type" class="mt-2 text-sm text-red-600">{{ form.errors.study_type }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">المجموعة الدراسية (الگروب) <span class="text-red-500">*</span></label>
                                        <select v-model="form.group_id" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors disabled:bg-gray-100" :disabled="availableGroups.length === 0">
                                            <option value="" disabled v-if="!form.stage_id">اختر المرحلة ونوع الدراسة أولاً</option>
                                            <option value="" disabled v-else-if="availableGroups.length === 0">لا توجد مجموعات مسجلة لهذه المرحلة وهذا الكورس</option>
                                            <option value="" disabled v-else>-- اختر المجموعة --</option>
                                            <option v-for="group in availableGroups" :key="group.id" :value="group.id">
                                                {{ group.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.group_id" class="mt-2 text-sm text-red-600">{{ form.errors.group_id }}</div>
                                    </div>

                                </div>
                            </div>
                            
                            <!-- Notice -->
                            <div class="bg-teal-50 border border-blue-100 rounded-xl p-4 flex items-start">
                                <QrCodeIcon class="w-5 h-5 text-blue-600 mt-0.5 ml-3 flex-shrink-0" />
                                <p class="text-sm font-medium text-teal-800">
                                    سيتم توليد رمز استجابة سريعة <span class="font-bold">QR Code</span> مشفر تلقائياً بمجرد حفظ بيانات الطالب، ويمكن طباعته لاحقاً من لوحة الإدارة.
                                </p>
                            </div>

                            <div class="flex items-center justify-end pt-6 border-t border-gray-100">
                                <Link :href="route('admin.students.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-all duration-200 ml-3">
                                    إلغاء
                                </Link>
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-blue-700 focus:outline-none transition-all duration-200 disabled:opacity-50">
                                    <SaveIcon class="w-5 h-5 ml-2" />
                                    حفظ وتوليد الـ QR
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

