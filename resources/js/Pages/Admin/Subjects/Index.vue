<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, SearchIcon, BookOpenIcon, Trash2Icon, PencilIcon, FilterIcon, ChevronDownIcon, ChevronLeftIcon, AlertTriangleIcon } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps<{
    stagesWithSubjects: any;
    stages: { id: string, name: string }[];
    filters: { search?: string, stage_id?: string };
}>();

const applyFilters = (stageId: string = props.filters.stage_id || '') => {
  router.get(
    route('admin.subjects.index'),
    { search: props.filters.search, stage_id: stageId },
    { preserveState: true, replace: true, only: ['stagesWithSubjects', 'filters'] }
  );
};

const confirmingSubjectDeletion = ref(false);
const subjectToDelete = ref<any>(null);

const deleteSubject = (subject: any) => {
    subjectToDelete.value = subject;
    confirmingSubjectDeletion.value = true;
};

const closeModal = () => {
    confirmingSubjectDeletion.value = false;
    subjectToDelete.value = null;
};

const confirmDeleteSubject = () => {
    if (subjectToDelete.value) {
        router.delete(route('admin.subjects.destroy', subjectToDelete.value.id), {
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

const expandedStages = ref<number[]>([]);

const toggleStage = (id: number) => {
    if (expandedStages.value.includes(id)) {
        expandedStages.value = expandedStages.value.filter(stageId => stageId !== id);
    } else {
        expandedStages.value.push(id);
    }
};


</script>

<template>
    <Head title="إدارة المواد الدراسية" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight flex items-center">
                    <BookOpenIcon class="w-6 h-6 ml-2 text-teal-600" />
                    إدارة المواد الدراسية
                </h2>
                <Link :href="route('admin.subjects.create')" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-emerald-500 border border-transparent rounded-lg shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:from-teal-700 hover:to-emerald-600 focus:outline-none transition-all duration-200">
                    <PlusIcon class="w-5 h-5 ml-2" />
                    إضافة مادة جديدة
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <form @submit.prevent="applyFilters()" class="flex flex-col md:flex-row gap-4 items-center w-full max-w-4xl">
                        <div class="relative w-full md:flex-1">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <SearchIcon class="w-5 h-5 text-gray-400" />
                            </div>
                            <input type="text" name="search" :value="filters.search" @input="(e) => router.get(route('admin.subjects.index'), { ...filters, search: (e.target as HTMLInputElement).value }, { preserveState: true, replace: true })" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full pr-12 p-3 transition-colors" placeholder="ابحث باسم المادة الدراسية...">
                        </div>
                        
                        <div class="relative w-full md:w-64 shrink-0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <FilterIcon class="w-5 h-5 text-gray-400" />
                            </div>
                            <select 
                                name="stage" 
                                :value="filters.stage_id"
                                @change="applyFilters(($event.target as HTMLSelectElement).value)"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full pr-12 p-3 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="">كل المراحل الدراسية</option>
                                <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                    {{ stage.name }}
                                </option>
                            </select>
                        </div>
                        
                        <button type="submit" class="p-3 w-full md:w-auto text-sm font-medium text-teal-600 bg-teal-50 rounded-xl hover:bg-teal-100 transition-colors flex items-center justify-center shrink-0">
                            <SearchIcon class="w-5 h-5 ml-2 md:hidden" />
                            <span class="md:sr-only">بحث</span>
                            <SearchIcon class="hidden md:block w-5 h-5" />
                        </button>
                    </form>
                </div>

                <div class="space-y-6">
                    <div v-for="stage in stagesWithSubjects.data" :key="'stage-'+stage.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        
                        <!-- Stage Header (Accordion Toggle) -->
                        <div @click="toggleStage(stage.id)" class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 cursor-pointer hover:bg-gray-100/50 transition-colors">
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <button class="p-1 rounded-lg hover:bg-gray-200 text-gray-500 transition-colors">
                                    <ChevronDownIcon v-if="expandedStages.includes(stage.id)" class="w-5 h-5 transition-transform" />
                                    <ChevronLeftIcon v-else class="w-5 h-5 transition-transform rtl:rotate-180" />
                                </button>
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-teal-50 to-emerald-100 flex items-center justify-center text-teal-700 shadow-sm ml-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layers"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 12 12 17 22 12"/><polyline points="2 17 12 22 22 17"/></svg>
                                </div>
                                <div class="text-right flex-1">
                                    <h3 class="text-lg font-bold text-gray-900">{{ stage.name }}</h3>
                                    <p class="text-xs font-medium text-gray-500">{{ stage.subjects?.length || 0 }} مادة دراسية</p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <!-- Placeholders for shift badges if needed -->
                                <div v-if="stage.level === 1" class="px-4 py-1.5 rounded-full bg-amber-100 border border-amber-200 text-amber-800 text-sm font-bold flex items-center shadow-sm">
                                    ☀️ 4 صباحي
                                </div>
                                <div v-if="stage.level === 1" class="px-4 py-1.5 rounded-full bg-teal-100 border border-indigo-200 text-teal-800 text-sm font-bold flex items-center shadow-sm">
                                    🌙 2 مسائي
                                </div>
                            </div>
                        </div>

                        <!-- Subjects Inner Table -->
                        <div v-show="expandedStages.includes(stage.id)" class="bg-white">
                            <div v-if="!stage.subjects || stage.subjects.length === 0" class="text-center py-8 text-gray-500 font-medium bg-gray-50 border-t border-dashed border-gray-200">
                                لا توجد مواد دراسية مضافة لهذه المرحلة.
                            </div>
                            
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50/50">
                                        <tr>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-16">#</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">المادة الدراسية</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">الأستاذ المسؤول</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">كود المادة</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="(subject, index) in stage.subjects" :key="subject.id" class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-500 text-center">
                                                {{ Number(index) + 1 }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold shrink-0">
                                                        <BookOpenIcon class="w-4 h-4" />
                                                    </div>
                                                    <div class="flex flex-col items-start text-right">
                                                        <span class="font-bold text-gray-900">{{ subject.name }}</span>
                                                        <div v-if="subject.groups && subject.groups.length > 0" class="flex flex-wrap gap-1 mt-1 justify-start">
                                                            <span v-for="group in subject.groups.slice(0, 3)" :key="group.id" class="px-2 py-0.5 text-[9px] font-bold bg-teal-50 text-teal-700 border border-teal-100 rounded-md whitespace-nowrap">
                                                                {{ group.name }}
                                                            </span>
                                                            <span v-if="subject.groups.length > 3" class="px-1.5 py-0.5 text-[9px] font-bold bg-gray-50 text-gray-500 border border-gray-100 rounded-md">
                                                                +{{ subject.groups.length - 3 }}
                                                            </span>
                                                        </div>
                                                        <span v-else class="text-[10px] text-red-500 mt-1">بدون مجموعات محددة</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600 font-medium text-center">
                                                {{ subject.teacher?.full_name || 'غير محدد' }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 font-medium text-center" dir="ltr">
                                                {{ subject.code }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex items-center justify-center gap-2">
                                                    <Link :href="route('admin.subjects.edit', subject.id)" prefetch class="p-1.5 text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors" title="تعديل">
                                                        <PencilIcon class="w-4 h-4" />
                                                    </Link>
                                                    <button @click="deleteSubject(subject)" class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" title="حذف">
                                                        <Trash2Icon class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Empty State for Search or No Stages -->
                    <div v-if="stagesWithSubjects.data.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-box mb-4 text-gray-300"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            <p class="text-lg font-medium">لا توجد سجلات مطابقة.</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <Pagination v-if="stagesWithSubjects.data.length > 0" :links="stagesWithSubjects.links" />
                </div>
            </div>
        </div>

        <!-- Subject Deletion Confirmation Modal -->
        <Modal :show="confirmingSubjectDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-red-50 rounded-full">
                    <AlertTriangleIcon class="w-8 h-8 text-red-600 animate-bounce" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تأكيد حذف المادة الدراسية
                </h3>
                
                <p class="text-center text-gray-500 text-sm leading-relaxed mb-8">
                    هل أنت متأكد من رغبتك في حذف المادة <span class="font-bold text-gray-900">{{ subjectToDelete?.name }}</span>؟ سيؤدي هذا إلى إزالتها من سجلات المراحل والمجموعات المرتبطة.
                </p>

                <div class="flex items-center gap-3">
                    <DangerButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold"
                        @click="confirmDeleteSubject"
                    >
                        تأكيد الحذف
                    </DangerButton>
                    
                    <SecondaryButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold border-gray-200"
                        @click="closeModal"
                    >
                        إلغاء الأمر
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
