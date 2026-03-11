<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, SearchIcon, QrCodeIcon, Trash2Icon, AlertTriangleIcon } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import QRCodeVue3 from 'qrcode-vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    students: any; // Using any for pagination object brevity
    stages: Array<any>;
    filters: { 
        search?: string;
        stage_id?: string;
        group_id?: string;
        study_type?: string;
    };
}>();

const search = ref(props.filters.search || '');
const stage_id = ref(props.filters.stage_id || '');
const group_id = ref(props.filters.group_id || '');
const study_type = ref(props.filters.study_type || '');

// Derived available groups
const availableGroups = computed(() => {
    if (!stage_id.value) return [];
    const stage = props.stages.find(s => s.id === stage_id.value);
    if (!stage) return [];
    
    // If study type is selected, filter by it, otherwise show all stage groups
    if (study_type.value) {
        return stage.groups.filter((g: any) => g.study_type === study_type.value);
    }
    return stage.groups;
});

// Reset group when stage changes
watch([stage_id, study_type], () => {
    // Only clear if the current group is no longer valid
    if (group_id.value) {
        const isValid = availableGroups.value.some((g: any) => g.id === group_id.value);
        if (!isValid) group_id.value = '';
    }
    filterData();
});

const filterData = () => {
    router.get(
        route('admin.students.index'),
        { 
            search: search.value,
            stage_id: stage_id.value,
            group_id: group_id.value,
            study_type: study_type.value
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

// Also watch search to fire automatically or we can leave it to the form submit
let searchTimeout: any;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterData();
    }, 300);
});

// QR Code Downloading Logic
const downloadingQrFor = ref<string | null>(null);

const downloadQrCode = (studentId: string, studentName: string) => {
    downloadingQrFor.value = studentId;
    
    // Give Vue a moment to render the hidden QR component
    setTimeout(() => {
        const qrElement = document.getElementById(`qr-container-${studentId}`);
        if (qrElement) {
            const img = qrElement.querySelector('img');
            const canvas = qrElement.querySelector('canvas');
            
            let dataUrl = '';
            if (img && img.src) {
                dataUrl = img.src;
            } else if (canvas) {
                dataUrl = canvas.toDataURL('image/png');
            }
            
            if (dataUrl) {
                const link = document.createElement('a');
                link.href = dataUrl;
                link.download = `QR_Code_${studentName.replace(/\s+/g, '_')}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
        downloadingQrFor.value = null;
    }, 100);
};

const confirmingStudentDeletion = ref(false);
const studentIdToDelete = ref<string | null>(null);

const deleteStudent = (id: string) => {
    studentIdToDelete.value = id;
    confirmingStudentDeletion.value = true;
};

const closeModal = () => {
    confirmingStudentDeletion.value = false;
    studentIdToDelete.value = null;
};

const confirmDeleteStudent = () => {
    if (studentIdToDelete.value) {
        router.delete(route('admin.students.destroy', studentIdToDelete.value), {
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

</script>

<template>
    <Head title="إدارة الطلاب" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight">
                    إدارة الطلاب
                </h2>
                <Link :href="route('admin.students.create')" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <PlusIcon class="w-5 h-5 ml-2" />
                    إضافة طالب جديد
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Search and Filters Section -->
                <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <form @submit.prevent="filterData" class="space-y-4">
                        <!-- Search Box -->
                        <div class="flex items-center w-full">
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <SearchIcon class="w-5 h-5 text-gray-400" />
                                </div>
                                <input type="text" v-model="search" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 p-3 transition-colors" placeholder="ابحث عن طريق الاسم أو رقم الطالب الخارجي...">
                            </div>
                        </div>

                        <!-- Filters Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Stage Filter -->
                            <div>
                                <select v-model="stage_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                    <option value="">جميع المراحل</option>
                                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                        {{ stage.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Study Type Filter -->
                            <div>
                                <select v-model="study_type" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors">
                                    <option value="">كل أنواع الدراسة (صباحي/مسائي)</option>
                                    <option value="morning">صباحي</option>
                                    <option value="evening">مسائي</option>
                                </select>
                            </div>

                            <!-- Group Filter -->
                            <div>
                                <select v-model="group_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors disabled:bg-gray-100" :disabled="availableGroups.length === 0">
                                    <option value="">جميع الكروبات</option>
                                    <option v-for="group in availableGroups" :key="group.id" :value="group.id">
                                        {{ group.name }} {{ study_type ? '' : `(${group.study_type === 'morning' ? 'صباحي' : 'مسائي'})` }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Data Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100/60">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-5 text-right text-[12px] font-black text-gray-500 uppercase tracking-wider">#</th>
                                    <th scope="col" class="px-6 py-5 text-right text-[12px] font-black text-gray-500 uppercase tracking-wider">اسم الطالب</th>
                                    <th scope="col" class="px-6 py-5 text-center text-[12px] font-black text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-5 text-center text-[12px] font-black text-gray-500 uppercase tracking-wider">المرحلة</th>
                                    <th scope="col" class="px-6 py-5 text-center text-[12px] font-black text-gray-500 uppercase tracking-wider">المجموعة</th>
                                    <th scope="col" class="px-6 py-5 text-center text-[12px] font-black text-gray-500 uppercase tracking-wider">الدراسة</th>
                                    <th scope="col" class="px-6 py-5 text-left text-[12px] font-black text-gray-500 uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50/80">
                                <tr v-for="(student, index) in students.data" :key="student.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-400 font-bold">
                                        {{ (Number(students.current_page) - 1) * Number(students.per_page) + Number(index) + 1 }}
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div v-if="student.photo_path" class="w-10 h-10 rounded-full ml-4 border border-gray-200 shadow-sm overflow-hidden flex-shrink-0">
                                                <img :src="'/storage/' + student.photo_path" class="w-full h-full object-cover bg-gray-100" />
                                            </div>
                                            <div v-else class="w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 font-bold ml-4 border border-indigo-100 flex-shrink-0">
                                                {{ student.first_name.charAt(0) }}
                                            </div>
                                            <div class="text-[15px] font-bold text-gray-900">{{ student.first_name }} {{ student.second_name }} {{ student.last_name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-xs font-bold rounded-lg bg-gray-50 text-gray-600 border border-gray-200">
                                            {{ student.student_external_id }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center text-sm text-gray-500 font-medium">
                                        {{ student.group?.stage?.name }}
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center text-[15px] text-gray-900 font-black">
                                        {{ student.group?.name }}
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                        <span v-if="student.group?.study_type === 'morning'" class="px-3 py-1 inline-flex text-xs font-bold rounded-lg bg-amber-50 text-amber-600 border border-amber-100">صباحي</span>
                                        <span v-else class="px-3 py-1 inline-flex text-xs font-bold rounded-lg bg-teal-50 text-teal-600 border border-indigo-100">مسائي</span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-left text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-60 group-hover:opacity-100 transition-opacity">
                                            <Link :href="route('admin.students.show', student.id)" class="px-4 py-2 text-xs font-bold text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-xl transition-colors" title="الملف الشخصي">
                                                عرض الملف
                                            </Link>
                                            <Link :href="route('admin.students.edit', student.id)" class="px-4 py-2 text-xs font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors" title="تعديل البيانات">
                                                تعديل
                                            </Link>
                                            <button @click="downloadQrCode(student.id, `${student.first_name}_${student.last_name}`)" class="flex items-center justify-center p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-colors" title="تنزيل رمز QR">
                                                <QrCodeIcon class="w-4 h-4" :class="{'animate-pulse': downloadingQrFor === student.id}" /> 
                                            </button>
                                            <button @click="deleteStudent(student.id)" class="flex items-center justify-center p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors" title="حذف الطالب">
                                                <Trash2Icon class="w-4 h-4" />
                                            </button>
                                            
                                            <!-- Hidden QR Code for downloading -->
                                            <div v-if="downloadingQrFor === student.id" :id="`qr-container-${student.id}`" class="hidden">
                                                <QRCodeVue3
                                                    :width="1000"
                                                    :height="1000"
                                                    :value="student.qr_payload"
                                                    :qrOptions="{ typeNumber: '0', mode: 'Byte', errorCorrectionLevel: 'Q' }"
                                                    :imageOptions="{ hideBackgroundDots: true, imageSize: 0.4, margin: 0 }"
                                                    :dotsOptions="{ type: 'rounded', color: '#111827' }"
                                                    :cornersSquareOptions="{ type: 'extra-rounded', color: '#111827' }"
                                                    :cornersDotOptions="{ type: 'dot', color: '#111827' }"
                                                />
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                                <tr v-if="students.data.length === 0">
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <SearchIcon class="w-12 h-12 mb-4 text-gray-300" />
                                            <p class="text-lg font-medium">لم يتم العثور على أي طلاب.</p>
                                            <p class="text-sm text-gray-400 mt-1">جرب تغيير كلمات البحث أو أضف طالباً جديداً.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deletion Confirmation Modal -->
        <Modal :show="confirmingStudentDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-red-50 rounded-full">
                    <AlertTriangleIcon class="w-8 h-8 text-red-600 animate-bounce" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تأكيد نقل الطالب للأرشيف
                </h3>
                
                <p class="text-center text-gray-500 text-sm leading-relaxed mb-8">
                    هل أنت متأكد من رغبتك في حذف هذا الطالب؟ سيتم نقله إلى "مركز إدارة الأرشيف" ويمكنك استعادته لاحقاً إذا دعت الحاجة.
                </p>

                <div class="flex items-center gap-3">
                    <DangerButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold"
                        @click="confirmDeleteStudent"
                    >
                        حذف ونقل للأرشيف
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
