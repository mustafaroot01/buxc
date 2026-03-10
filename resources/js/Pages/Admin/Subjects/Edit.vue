<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BookOpenIcon, SaveIcon, ArrowRightIcon } from 'lucide-vue-next';

const props = defineProps<{
    subject: {
        id: string;
        name: string;
        code: string;
        stage_id: string;
        teacher_id: string;
        stage?: { id: string; name: string };
        teacher?: { id: string; full_name: string };
        groups?: Array<{ id: string; name: string }>;
    };
    stages: Array<{ id: string; name: string; groups: Array<{ id: string; name: string; study_type: string }> }>;
    teachers: Array<{ id: string; full_name: string }>;
}>();

const form = useForm({
    name: props.subject.name,
    code: props.subject.code,
    stage_id: props.subject.stage_id,
    teacher_id: props.subject.teacher_id,
    group_ids: props.subject.groups ? props.subject.groups.map(g => g.id) : [] as string[],
});

import { computed } from 'vue';

const availableGroups = computed(() => {
    if (!form.stage_id) return [];
    const stage = props.stages.find(s => s.id === form.stage_id);
    return stage ? stage.groups : [];
});

const submit = () => {
    form.put(route('admin.subjects.update', props.subject.id));
};
</script>

<template>
    <Head title="تعديل مادة دراسية" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('admin.subjects.index')" class="mr-4 text-gray-500 hover:text-gray-700"><ArrowRightIcon class="w-6 h-6" /></Link>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center pr-4 border-r-2 border-gray-200">
                    <BookOpenIcon class="w-6 h-6 ml-2 text-teal-600" /> تعديل المادة: {{ subject.name }}
                </h2>
            </div>
        </template>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-teal-500 to-emerald-400"></div>
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">اسم المادة <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.name" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full p-3" placeholder="مثال: الرياضيات، الفيزياء..." required>
                                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">كود المادة <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.code" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full p-3" placeholder="مثال: MATH101، CS202..." required>
                                <p v-if="form.errors.code" class="mt-2 text-sm text-red-600">{{ form.errors.code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">المرحلة الدراسية <span class="text-red-500">*</span></label>
                                <select v-model="form.stage_id" @change="form.group_ids = []" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full p-3" required>
                                    <option value="" disabled>-- اختر المرحلة --</option>
                                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                </select>
                                <p v-if="form.errors.stage_id" class="mt-2 text-sm text-red-600">{{ form.errors.stage_id }}</p>
                            </div>

                            <!-- Groups Selection -->
                            <div v-if="form.stage_id">
                                <label class="block text-sm font-bold text-gray-700 mb-2">المسارات / المجموعات المخصصة <span class="text-gray-400 text-xs font-normal">(اختر المجموعات التي سيدرسها هذا الأستاذ)</span></label>
                                <div v-if="availableGroups.length === 0" class="text-sm text-gray-500 italic p-3 bg-gray-50 rounded-lg">
                                    لا توجد مجموعات مسجلة لهذه المرحلة بعد.
                                </div>
                                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <label v-for="group in availableGroups" :key="group.id" class="flex items-center p-3 cursor-pointer bg-white rounded-lg border border-gray-200 shadow-sm hover:border-teal-300 hover:bg-teal-50 transition-colors">
                                        <input type="checkbox" :value="group.id" v-model="form.group_ids" class="w-5 h-5 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500">
                                        <div class="ml-3 rtl:ml-0 rtl:mr-3">
                                            <span class="block text-sm font-bold text-gray-900">{{ group.name }}</span>
                                            <span class="block text-xs font-medium text-gray-500">{{ group.study_type === 'morning' ? 'دراسة صباحية' : 'دراسة مسائية' }}</span>
                                        </div>
                                    </label>
                                </div>
                                <p v-if="form.errors.group_ids" class="mt-2 text-sm text-red-600">{{ form.errors.group_ids }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">الأستاذ المسؤول <span class="text-red-500">*</span></label>
                                <select v-model="form.teacher_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full p-3" required>
                                    <option value="" disabled>-- اختر أستاذاً --</option>
                                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option>
                                </select>
                                <p v-if="form.errors.teacher_id" class="mt-2 text-sm text-red-600">{{ form.errors.teacher_id }}</p>
                            </div>
                            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                                <Link :href="route('admin.subjects.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-all ml-3">إلغاء</Link>
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-emerald-500 rounded-lg shadow-lg hover:from-teal-700 hover:to-emerald-600 transition-all disabled:opacity-50">
                                    <SaveIcon class="w-5 h-5 ml-2" /> حفظ التعديلات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
