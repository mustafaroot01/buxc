<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LayersIcon, SaveIcon, ArrowRightIcon } from 'lucide-vue-next';

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
                            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                                <Link :href="route('admin.stages.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-all duration-200 ml-3">
                                    إلغاء
                                </Link>
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 disabled:opacity-50">
                                    <SaveIcon class="w-5 h-5 ml-2" />
                                    حفظ التعديلات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
