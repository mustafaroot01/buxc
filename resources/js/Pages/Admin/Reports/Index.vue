<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    FileSpreadsheetIcon, DownloadIcon, UploadIcon,
    LayersIcon, UsersIcon, FilterIcon,
    AlertCircleIcon, CheckCircle2Icon
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps<{
    stages: Array<{
        id: string;
        name: string;
        groups: Array<{ id: string; name: string; study_type: string }>;
        subjects: Array<{ id: string; name: string }>;
    }>;
}>();

const exportForm = useForm({
    stage_id: '',
    study_type: '',
    group_id: '',
});

const importForm = useForm({
    stage_id: '',
    study_type: '',
    group_id: '',
    file: null as File | null,
});

const showImportModal = ref(false);

const availableGroupsExport = computed(() => {
    if (!exportForm.stage_id) return [];
    const stage = props.stages.find(s => s.id === exportForm.stage_id);
    if (!stage) return [];
    let groups = stage.groups;
    if (exportForm.study_type) {
        groups = groups.filter(g => g.study_type === exportForm.study_type);
    }
    return groups;
});

const availableGroupsImport = computed(() => {
    if (!importForm.stage_id) return [];
    const stage = props.stages.find(s => s.id === importForm.stage_id);
    if (!stage) return [];
    let groups = stage.groups;
    if (importForm.study_type) {
        groups = groups.filter(g => g.study_type === importForm.study_type);
    }
    return groups;
});

const handleExport = () => {
    const params = new URLSearchParams();
    if (exportForm.stage_id) params.append('stage_id', exportForm.stage_id);
    if (exportForm.study_type) params.append('study_type', exportForm.study_type);
    if (exportForm.group_id) params.append('group_id', exportForm.group_id);

    window.location.href = route('admin.reports.export-students') + '?' + params.toString();
};

const handleImport = () => {
    importForm.post(route('admin.reports.import-students'), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        },
    });
};
</script>

<template>
    <Head title="إدارة بيانات الطلاب والتقارير" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center ml-3">
                        <FileSpreadsheetIcon class="w-6 h-6 text-teal-600" />
                    </div>
                    <h2 class="text-xl font-black text-gray-800 border-r-2 pr-4 border-gray-200">
                        سجل تقارير الطلاب
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- Export Card -->
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden flex flex-col group hover:shadow-2xl transition-all duration-500">
                        <div class="h-2 bg-gradient-to-r from-teal-500 to-emerald-500"></div>
                        <div class="p-10 flex-1 flex flex-col">
                            <div class="w-16 h-16 rounded-2xl bg-teal-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <DownloadIcon class="w-8 h-8 text-teal-600" />
                            </div>
                            
                            <h3 class="text-2xl font-black text-gray-900 mb-2">تصدير قائمة الطلاب</h3>
                            <p class="text-gray-500 font-bold text-sm mb-8 leading-relaxed">قم بتصدير أسماء الطلاب والمعلومات الأكاديمية إلى ملف Excel جاهز للطباعة.</p>

                            <div class="space-y-4 mt-auto">
                                <div>
                                    <label class="block text-xs font-black text-gray-400 mb-2 mr-2 uppercase tracking-wider text-right">المرحلة الدراسية</label>
                                    <select v-model="exportForm.stage_id" class="w-full bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-14">
                                        <option value="">كل المراحل</option>
                                        <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-black text-gray-400 mb-2 mr-2 uppercase tracking-wider text-right">نوع الدراسة</label>
                                        <select v-model="exportForm.study_type" class="w-full bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-14">
                                            <option value="">الكل</option>
                                            <option value="morning">صباحي</option>
                                            <option value="evening">مسائي</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-400 mb-2 mr-2 uppercase tracking-wider text-right">المجموعة</label>
                                        <select v-model="exportForm.group_id" :disabled="!exportForm.stage_id" class="w-full bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all font-bold text-sm h-14 disabled:opacity-40">
                                            <option value="">الكل</option>
                                            <option v-for="group in availableGroupsExport" :key="group.id" :value="group.id">{{ group.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <button @click="handleExport" class="w-full mt-6 py-4 bg-teal-600 hover:bg-teal-700 text-white font-black rounded-2xl shadow-lg shadow-teal-200 transition-all flex items-center justify-center gap-2 text-lg">
                                    <DownloadIcon class="w-5 h-5" />
                                    تصدير كملف Excel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Import Card -->
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden flex flex-col group hover:shadow-2xl transition-all duration-500">
                        <div class="h-2 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
                        <div class="p-10 flex-1 flex flex-col">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <UploadIcon class="w-8 h-8 text-indigo-600" />
                            </div>
                            
                            <h3 class="text-2xl font-black text-gray-900 mb-2">استيراد الطلاب (Bulk)</h3>
                            <p class="text-gray-500 font-bold text-sm mb-8 leading-relaxed">ارفع ملف Excel يحتوي على قائمة الطلاب لإضافتهم تلقائياً للمرحلة والمجموعة المختارة.</p>

                            <div class="mt-auto space-y-6">
                                <div class="bg-indigo-50/50 p-6 rounded-3xl border border-indigo-100/50">
                                    <h4 class="text-indigo-900 font-black text-sm mb-3 flex items-center gap-2">
                                        <AlertCircleIcon class="w-4 h-4" />
                                        تعليمات الملف
                                    </h4>
                                    <ul class="text-[11px] text-indigo-700 font-bold space-y-1.5 opacity-80">
                                        <li>• يجب أن يحتوي الملف على رؤوس (first_name, last_name, gender, student_external_id).</li>
                                        <li>• سيتم توليد رموز QR تلقائياً لكل طالب مستورد.</li>
                                    </ul>
                                </div>

                                <button @click="showImportModal = true" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2 text-lg">
                                    <UploadIcon class="w-5 h-5" />
                                    استيراد قائمة جديدة
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Note -->
                <p class="text-center text-gray-400 mt-12 font-bold text-xs italic">
                    * يدعم النظام ملفات Excel (.xlsx, .xls) والملفات النصية الكومية (.csv).
                </p>

            </div>
        </div>

        <!-- Import Modal -->
        <Modal :show="showImportModal" @close="showImportModal = false" maxWidth="lg">
            <div class="p-8">
                <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
                    <UploadIcon class="w-6 h-6 text-indigo-600" />
                    تجهيز استيراد الطلاب
                </h3>

                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 mb-2 mr-2 uppercase tracking-wider text-right">المرحلة الدراسية الهدف</label>
                        <select v-model="importForm.stage_id" class="w-full bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-sm h-14">
                            <option value="">اختر المرحلة...</option>
                            <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 mb-2 mr-2 uppercase tracking-wider text-right">نوع الدراسة</label>
                            <select v-model="importForm.study_type" class="w-full bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-sm h-14">
                                <option value="morning">صباحي</option>
                                <option value="evening">مسائي</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 mb-2 mr-2 uppercase tracking-wider text-right">المجموعة الـهدف</label>
                            <select v-model="importForm.group_id" :disabled="!importForm.stage_id" class="w-full bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-sm h-14 disabled:opacity-40">
                                <option value="">اختر المجموعة...</option>
                                <option v-for="group in availableGroupsImport" :key="group.id" :value="group.id">{{ group.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-black text-gray-400 mb-2 mr-2 uppercase tracking-wider text-right">اختر ملف Excel</label>
                        <input 
                            type="file" 
                            @input="importForm.file = ($event.target as HTMLInputElement).files?.[0] || null"
                            class="w-full p-4 border-2 border-dashed border-gray-200 rounded-2xl text-sm font-bold text-gray-500 file:hidden cursor-pointer hover:border-indigo-300 transition-colors"
                        >
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <button 
                            @click="handleImport"
                            :disabled="importForm.processing || !importForm.file"
                            class="flex-1 py-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-black rounded-2xl shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2"
                        >
                            <RefreshCwIcon v-if="importForm.processing" class="w-5 h-5 animate-spin" />
                            <CheckCircle2Icon v-else class="w-5 h-5" />
                            بدء عملية الاستيراد
                        </button>
                        <SecondaryButton @click="showImportModal = false" class="py-4 px-8 rounded-2xl font-black">إلغاء</SecondaryButton>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Optional: Arabic font specific tweaks if needed */
</style>
