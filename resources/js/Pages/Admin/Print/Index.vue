<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { QrCodeIcon, FilterIcon, PrinterIcon } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stages: Array<{
        id: string;
        name: string;
        groups: Array<{
            id: string;
            name: string;
            study_type: string;
        }>;
    }>;
}>();

const form = useForm({
    stage_id: '',
    group_id: '',
    study_type: '',
});

const availableGroups = computed(() => {
    if (!form.stage_id) return [];
    const stage = props.stages.find(s => s.id === form.stage_id);
    if (!stage) return [];
    
    if (form.study_type) {
        return stage.groups.filter(g => g.study_type === form.study_type);
    }
    return stage.groups;
});

const resetGroup = () => {
    form.group_id = '';
};

const submit = () => {
    // Open in same window, but since it's a completely different layout (printable),
    // we use standard browser navigation so the user can easily click "Back"
    const params = new URLSearchParams();
    if (form.stage_id) params.append('stage_id', form.stage_id);
    if (form.group_id) params.append('group_id', form.group_id);
    if (form.study_type) params.append('study_type', form.study_type);
    
    window.location.href = route('admin.print.qrs.generate') + '?' + params.toString();
};
</script>

<template>
    <Head title="طباعة بطاقات QR" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <QrCodeIcon class="w-6 h-6 mr-3 text-indigo-600" />
                <h2 class="text-xl font-bold leading-tight text-gray-800">
                    مركز طباعة بطاقات الحضور
                </h2>
            </div>
        </template>

        <div class="py-12 bg-[#fafafa] min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                
                <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 overflow-hidden relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>

                    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-gray-900">تصفية السجلات للطباعة</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">اختر المرحلة والمجموعة الدراسية لتوليد وطباعة بطاقات الطلاب.</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <FilterIcon class="w-6 h-6" />
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-8">
                        <div class="space-y-6">
                            
                            <!-- Stage -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">المرحلة الدراسية</label>
                                <select v-model="form.stage_id" @change="resetGroup" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                    <option value="">جميع المراحل</option>
                                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                        {{ stage.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Study Type -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">نوع الدراسة</label>
                                    <select v-model="form.study_type" @change="resetGroup" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                        <option value="">جميع أنواع الدراسة</option>
                                        <option value="morning">صباحي</option>
                                        <option value="evening">مسائي</option>
                                    </select>
                                </div>

                                <!-- Group -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">المجموعة</label>
                                    <select v-model="form.group_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors" :disabled="!form.stage_id">
                                        <option value="">جميع المجموعات</option>
                                        <option v-for="group in availableGroups" :key="group.id" :value="group.id">
                                            {{ group.name }} ({{ group.study_type === 'morning' ? 'صباحي' : 'مسائي' }})
                                        </option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                <PrinterIcon class="w-5 h-5 ml-2" />
                                توليد وعرض البطاقات للطباعة
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
