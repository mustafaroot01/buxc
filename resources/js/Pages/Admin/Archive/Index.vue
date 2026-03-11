<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    ArchiveIcon, 
    RotateCcwIcon, 
    UserIcon, 
    CalendarIcon, 
    SearchIcon,
    HistoryIcon,
    InboxIcon,
    Trash2Icon,
    AlertTriangleIcon
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps<{
    students: {
        data: Array<{
            id: string;
            first_name: string;
            last_name: string;
            student_external_id: string;
            deleted_at: string;
            group: {
                name: string;
                stage: {
                    name: string;
                }
            }
        }>;
        links: any[];
    };
}>();

const searchTerm = ref('');
const form = useForm({});

const filteredStudents = computed(() => {
    if (!searchTerm.value) return props.students.data;
    const term = searchTerm.value.toLowerCase();
    return props.students.data.filter(s => 
        (s.first_name + ' ' + s.last_name).toLowerCase().includes(term) ||
        s.student_external_id?.toLowerCase().includes(term)
    );
});

const confirmingStudentRestoration = ref(false);
const confirmingStudentDeletion = ref(false);
const studentToProcess = ref<any>(null);

const restoreStudent = (student: any) => {
    studentToProcess.value = student;
    confirmingStudentRestoration.value = true;
};

const deleteStudentPermanently = (student: any) => {
    studentToProcess.value = student;
    confirmingStudentDeletion.value = true;
};

const closeModal = () => {
    confirmingStudentRestoration.value = false;
    confirmingStudentDeletion.value = false;
    studentToProcess.value = null;
};

const confirmRestoreStudent = () => {
    if (studentToProcess.value) {
        form.post(route('admin.archive.restore', studentToProcess.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

const confirmPermanentDelete = () => {
    if (studentToProcess.value) {
        form.delete(route('admin.archive.destroy', studentToProcess.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="مركز إدارة الأرشيف" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-2 bg-indigo-50 rounded-xl ml-4 shadow-sm">
                        <ArchiveIcon class="w-6 h-6 text-indigo-600" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 leading-tight">مركز إدارة الأرشيف</h2>
                        <p class="text-sm text-gray-500 font-medium">عرض وإدارة السجلات المؤرشفة (المحذوفة مؤقتاً)</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Flash Message -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="($page.props.flash as any)?.success" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl flex items-center shadow-sm">
                        <HistoryIcon class="w-5 h-5 ml-3" />
                        <span class="font-bold">{{ ($page.props.flash as any).success }}</span>
                    </div>
                </Transition>

                <!-- Search & Filters -->
                <div class="mb-8 relative max-w-md">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <SearchIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input 
                        type="text" 
                        v-model="searchTerm"
                        placeholder="بحث بالاسم أو الرقم الجامعي..." 
                        class="block w-full pr-11 pl-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all"
                    >
                </div>

                <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 bg-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-black text-gray-900">سجل الطلاب المؤرشفين</h3>
                            <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-100 italic">Historical Data</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">الطالب</th>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">الرقم التعريفي</th>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">المجموعة السابقة</th>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">تاريخ الأرشفة</th>
                                    <th class="px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="student in filteredStudents" :key="student.id" class="group hover:bg-indigo-50/30 transition-all duration-300">
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 ml-4 group-hover:scale-110 transition-transform shadow-inner">
                                                <UserIcon class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-gray-900">{{ student.first_name }} {{ student.last_name }}</div>
                                                <div class="text-[11px] text-gray-400 font-medium">سجل طالب قديم</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg">{{ student.student_external_id || '---' }}</span>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-bold text-gray-700">{{ student.group?.name }}</div>
                                            <div class="px-2 py-0.5 bg-indigo-50 text-indigo-500 text-[10px] font-black rounded-md">{{ student.group?.stage?.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center text-gray-500 gap-2">
                                            <CalendarIcon class="w-4 h-4 text-indigo-300" />
                                            <span class="text-xs font-bold">{{ formatDate(student.deleted_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-left">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                @click="restoreStudent(student)" 
                                                :disabled="form.processing"
                                                class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl text-xs font-black transition-all duration-300 shadow-sm border border-emerald-100 disabled:opacity-50"
                                            >
                                                <RotateCcwIcon class="w-3.5 h-3.5 ml-2" />
                                                استعادة
                                            </button>
                                            <button 
                                                @click="deleteStudentPermanently(student)" 
                                                :disabled="form.processing"
                                                class="inline-flex items-center p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl text-xs font-black transition-all duration-300 shadow-sm border border-rose-100 disabled:opacity-50"
                                                title="حذف نهائي"
                                            >
                                                <Trash2Icon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="filteredStudents.length === 0">
                                    <td colspan="5" class="px-8 py-24 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-4">
                                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                                                <InboxIcon class="w-10 h-10 text-gray-200" />
                                            </div>
                                            <div>
                                                <p class="text-xl font-black text-gray-400">لا توجد بيانات مؤرشفة</p>
                                                <p class="text-sm text-gray-400 mt-1">لم يتم العثور على أي طلاب في الأرشيف حالياً.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Hint -->
                <p class="mt-6 text-center text-[11px] text-gray-400 font-medium">
                    ملاحظة: استعادة الطالب تعيده إلى مجموعته السابقة ليظهر مرة أخرى في قوائم الحضور والغياب.
                </p>
            </div>
        </div>

        <!-- Restoration Confirmation Modal -->
        <Modal :show="confirmingStudentRestoration" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-emerald-50 rounded-full">
                    <RotateCcwIcon class="w-8 h-8 text-emerald-600 animate-spin-slow" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تأكيد استعادة الطالب
                </h3>
                
                <p class="text-center text-gray-500 text-sm leading-relaxed mb-8">
                    هل أنت متأكد من رغبتك في استعادة الطالب <span class="font-bold text-gray-900">{{ studentToProcess?.first_name }}</span> إلى القائمة النشطة؟ سيتمكن من تسجيل الحضور في المحاضرات القادمة.
                </p>

                <div class="flex items-center gap-3">
                    <PrimaryButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-700 border-none"
                        @click="confirmRestoreStudent"
                        :disabled="form.processing"
                    >
                        تأكيد الاستعادة
                    </PrimaryButton>
                    
                    <SecondaryButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold border-gray-200"
                        @click="closeModal"
                    >
                        إلغاء
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

        <!-- Permanent Deletion Modal -->
        <Modal :show="confirmingStudentDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-rose-50 rounded-full">
                    <AlertTriangleIcon class="w-8 h-8 text-rose-600" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تأكيد الحذف النهائي
                </h3>
                
                <p class="text-center text-rose-600 text-sm leading-relaxed mb-8 font-bold">
                    تحذير: هذا الإجراء سيقوم بحذف الطالب <span class="bg-rose-100 px-1 rounded">{{ studentToProcess?.first_name }} {{ studentToProcess?.last_name }}</span> نهائياً من النظام. لا يمكن التراجع عن هذا الإجراء أبداً.
                </p>

                <div class="flex items-center gap-3">
                    <DangerButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold"
                        @click="confirmPermanentDelete"
                        :disabled="form.processing"
                    >
                        حذف نهائي للأبد
                    </DangerButton>
                    
                    <SecondaryButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold border-gray-200"
                        @click="closeModal"
                    >
                        تراجع
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 3s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
