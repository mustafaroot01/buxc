<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { FileSpreadsheetIcon, DownloadIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    lectures: Array<{
        id: string;
        title: string;
        subject: { name: string };
        group: { name: string; stage: { name: string } };
        date: string;
    }>;
}>();

const selectedLecture = ref('');

const downloadReport = () => {
    let url = route('admin.reports.export');
    if (selectedLecture.value) {
        url += `?lecture_id=${selectedLecture.value}`;
    }
    window.location.href = url;
};
</script>

<template>
    <Head title="Exporting & Reporting" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <FileSpreadsheetIcon class="w-6 h-6 ml-3 text-indigo-500" />
                <h2 class="text-xl font-semibold leading-tight text-gray-800 border-r-2 pr-4 border-gray-200">
                    التقارير والإحصائيات
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-8">
                    <div class="p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">توليد سجل الحضور والغياب</h3>
                        <p class="text-gray-500 mb-6 text-sm">استخراج شيت Excel متكامل لمعرفة الطلاب الغائبين والحاضرين.</p>
                        
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">تحديد التقرير المطلوب</label>
                            <select v-model="selectedLecture" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base p-3">
                                <option value="">سجل جميع الغيابات (لكل الأقسام والأيام)</option>
                                <optgroup label="تقارير حسب المحاضرات">
                                    <option v-for="lecture in lectures" :key="lecture.id" :value="lecture.id">
                                        {{ lecture.date }} | {{ lecture.subject?.name }} 
                                        ({{ lecture.group?.stage?.name }} - {{ lecture.group?.name }})
                                    </option>
                                </optgroup>
                            </select>
                            <p class="text-xs text-gray-400 mt-2">اتركه فارغاً لاستخراج كل بيانات الحضور والغياب منذ بداية النظام.</p>
                        </div>

                        <div class="flex items-center justify-end">
                            <button @click="downloadReport" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                <DownloadIcon class="w-5 h-5 ml-2 -mr-1" />
                                استخراج Excel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-teal-50 border-r-4 border-blue-400 p-4 rounded text-sm text-teal-700 w-full mt-4">
                    <h4 class="font-bold flex items-center mb-1">
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        حول الملف المستخرج
                    </h4>
                    <p>ملف الإكسيل المستخرج يدعم اللغة العربية بشكل تلقائي، ويحتوي على كافة أوقات الحضور الفعلية. يتم الفلترة بناءً على الوقت والمادة المطلوبة.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
