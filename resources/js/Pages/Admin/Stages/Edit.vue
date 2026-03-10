<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { LayersIcon, SaveIcon, ArrowRightIcon, Trash2Icon, AlertTriangleIcon } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps<{
    stage: {
        id: string;
        name: string;
        description?: string;
    };
}>();

const form = useForm({
    name: props.stage.name,
    description: props.stage.description ?? '',
});

const submit = () => {
    form.put(route('admin.stages.update', props.stage.id));
};

const confirmingStageDeletion = ref(false);

const deleteStage = () => {
    confirmingStageDeletion.value = true;
};

const closeModal = () => {
    confirmingStageDeletion.value = false;
};

const confirmDeleteStage = () => {
    router.delete(route('admin.stages.destroy', props.stage.id), {
        onSuccess: () => closeModal(),
        onFinish: () => closeModal(),
    });
};
</script>

<template>
    <Head title="تعديل المرحلة الدراسية" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('admin.stages.index')" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
                    <ArrowRightIcon class="w-6 h-6" />
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight flex items-center pr-4 border-r-2 border-gray-200">
                    <LayersIcon class="w-6 h-6 ml-2 text-teal-600" />
                    تعديل المرحلة: {{ stage.name }}
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">اسم المرحلة <span class="text-red-500">*</span></label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors"
                                    placeholder="مثال: المرحلة الأولى"
                                    required
                                >
                                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">وصف المرحلة (اختياري)</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition-colors"
                                    placeholder="تفاصيل إضافية حول المرحلة الدراسية..."
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <button
                                    type="button"
                                    @click="deleteStage"
                                    class="inline-flex items-center px-4 py-2 text-sm font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all duration-200"
                                >
                                    <Trash2Icon class="w-4 h-4 ml-2" />
                                    حذف المرحلة
                                </button>
                                
                                <div class="flex items-center">
                                    <Link :href="route('admin.stages.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-all duration-200 ml-3">
                                        إلغاء
                                    </Link>
                                    <button type="submit" :disabled="form.processing" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 disabled:opacity-50">
                                        <SaveIcon class="w-5 h-5 ml-2" />
                                        حفظ التعديلات
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stage Deletion Confirmation Modal -->
        <Modal :show="confirmingStageDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-red-50 rounded-full">
                    <AlertTriangleIcon class="w-8 h-8 text-red-600 animate-bounce" />
                </div>
                
                <h3 class="text-xl font-black text-center text-gray-900 mb-2">
                    تأكيد حذف المرحلة الدراسية
                </h3>
                
                <p class="text-center text-gray-500 text-sm leading-relaxed mb-8">
                    هل أنت متأكد من رغبتك في حذف المرحلة <span class="font-bold text-gray-900">{{ stage.name }}</span>؟ سيؤدي هذا إلى حذف جميع البيانات المرتبطة بها بشكل نهائي.
                </p>

                <div class="flex items-center gap-3">
                    <DangerButton 
                        class="flex-1 justify-center py-3 rounded-xl font-bold"
                        @click="confirmDeleteStage"
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
