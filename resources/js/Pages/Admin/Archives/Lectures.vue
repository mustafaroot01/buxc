<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    ArchiveIcon, 
    RotateCcwIcon, 
    CalendarIcon, 
    SearchIcon,
    HistoryIcon,
    InboxIcon,
    Trash2Icon,
    AlertTriangleIcon,
    BookOpenIcon,
    UsersIcon
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps<{
    lectures: {
        data: Array<{
            id: string;
            title: string;
            deleted_at: string;
            subject: {
                name: string;
            };
            group: {
                name: string;
                stage: {
                    name: string;
                }
            };
            teacher: {
                name: string;
            };
        }>;
        links: any[];
    };
}>();

const searchTerm = ref('');
const form = useForm({});

const filteredLectures = computed(() => {
    if (!searchTerm.value) return props.lectures.data;
    const term = searchTerm.value.toLowerCase();
    return props.lectures.data.filter(l => 
        l.title.toLowerCase().includes(term) ||
        l.subject?.name.toLowerCase().includes(term) ||
        l.teacher?.name.toLowerCase().includes(term)
    );
});

const confirmingRestoration = ref(false);
const confirmingDeletion = ref(false);
const lectureToProcess = ref<any>(null);

const restoreLecture = (lecture: any) => {
    lectureToProcess.value = lecture;
    confirmingRestoration.value = true;
};

const deletePermanently = (lecture: any) => {
    lectureToProcess.value = lecture;
    confirmingDeletion.value = true;
};

const closeModal = () => {
    confirmingRestoration.value = false;
    confirmingDeletion.value = false;
    lectureToProcess.value = null;
};

const confirmRestore = () => {
    if (lectureToProcess.value) {
        form.post(route('admin.archives.lectures.restore', lectureToProcess.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const confirmPermanentDelete = () => {
    if (lectureToProcess.value) {
        form.delete(route('admin.archives.lectures.destroy', lectureToProcess.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="أرشيف المحاضرات" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-2 bg-indigo-50 rounded-xl ml-4 shadow-sm">
                        <ArchiveIcon class="w-6 h-6 text-indigo-600" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 leading-tight">أرشيف المحاضرات</h2>
                        <p class="text-sm text-gray-500 font-medium">إدارة واستعادة المحاضرات المحذوفة</p>
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

                <!-- Search -->
                <div class="mb-8 relative max-w-md">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <SearchIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input 
                        type="text" 
                        v-model="searchTerm"
                        placeholder="بحث بالعنوان، المادة، أو الأستاذ..." 
                        class="block w-full pr-11 pl-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all"
                    >
                </div>

                <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 bg-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-black text-gray-900">سجل المحاضرات المؤرشفة</h3>
                            <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-100 italic">Historical Lectures</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">المحاضرة</th>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">المجموعة / المادة</th>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">الأستاذ</th>
                                    <th class="px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">تاريخ الحذف</th>
                                    <th class="px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="lecture in filteredLectures" :key="lecture.id" class="group hover:bg-indigo-50/30 transition-all duration-300">
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 ml-4 group-hover:scale-110 transition-transform shadow-inner">
                                                <BookOpenIcon class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-gray-900">{{ lecture.title }}</div>
                                                <div class="text-[11px] text-gray-400 font-medium">سجل محاضرة قديم</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2">
                                                <UsersIcon class="w-3 h-3 text-gray-400" />
                                                <span class="text-sm font-bold text-gray-700">{{ lecture.group?.name }}</span>
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-500 text-[10px] font-black rounded-md">{{ lecture.group?.stage?.name }}</span>
                                            </div>
                                            <div class="text-[11px] text-gray-500 font-medium">{{ lecture.subject?.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-sm font-bold text-gray-600">
                                        {{ lecture.teacher?.name }}
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center text-gray-500 gap-2">
                                            <CalendarIcon class="w-4 h-4 text-indigo-300" />
                                            <span class="text-xs font-bold">{{ formatDate(lecture.deleted_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-left">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                @click="restoreLecture(lecture)" 
                                                :disabled="form.processing"
                                                class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl text-xs font-black transition-all duration-300 shadow-sm border border-emerald-100 disabled:opacity-50"
                                            >
                                                <RotateCcwIcon class="w-3.5 h-3.5 ml-2" />
                                                استعادة
                                            </button>
                                            <button 
                                                @click="deletePermanently(lecture)" 
                                                :disabled="form.processing"
                                                class="inline-flex items-center p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl text-xs font-black transition-all duration-300 shadow-sm border border-rose-100 disabled:opacity-50"
                                                title="حذف نهائي"
                                            >
                                                <Trash2Icon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="filteredLectures.length === 0">
                                    <td colspan="5" class="px-8 py-24 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-4">
                                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                                                <InboxIcon class="w-10 h-10 text-gray-200" />
                                            </div>
                                            <div>
                                                <p class="text-xl font-black text-gray-400">لا توجد محاضرات مؤرشفة</p>
                                                <p class="text-sm text-gray-400 mt-1">لم يتم العثور على أي محاضرات في الأرشيف حالياً.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    <Pagination :links="lectures.links" />
                </div>
            </div>
        </div>

        <!-- Restoration Confirmation Modal -->
        <Modal :show="confirmingRestoration" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-emerald-50 rounded-full">
                    <RotateCcwIcon class="w-8 h-8 text-emerald-600 animate-spin-slow" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تأكيد استعادة المحاضرة
                </h3>
                
                <p class="text-center text-gray-500 text-sm leading-relaxed mb-8">
                    هل أنت متأكد من رغبتك في استعادة محاضرة <span class="font-bold text-gray-900">{{ lectureToProcess?.title }}</span>؟ ستعود لتظهر في تقارير الحضور والغياب.
                </p>

                <div class="flex items-center gap-3">
                    <PrimaryButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-700 border-none"
                        @click="confirmRestore"
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
        <Modal :show="confirmingDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-rose-50 rounded-full">
                    <AlertTriangleIcon class="w-8 h-8 text-rose-600" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تأكيد الحذف النهائي للمحاضرة
                </h3>
                
                <p class="text-center text-rose-600 text-sm leading-relaxed mb-8 font-bold">
                    تحذير: هذا الإجراء سيقوم بحذف المحاضرة <span class="bg-rose-100 px-1 rounded">{{ lectureToProcess?.title }}</span> وكافة سجلات الحضور المرتبطة بها نهائياً. لا يمكن التراجع عن هذا الإجراء أبداً.
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
