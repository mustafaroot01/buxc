<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UsersIcon, SaveIcon, ArrowRightIcon } from 'lucide-vue-next';

const props = defineProps<{
    group: {
        id: string;
        name: string;
        stage_id: string;
        study_type: string;
        stage?: { id: string; name: string };
    };
    stages: Array<{ id: string; name: string }>;
}>();

const form = useForm({
    name: props.group.name,
    stage_id: props.group.stage_id,
    study_type: props.group.study_type,
});

const submit = () => {
    form.put(route('admin.groups.update', props.group.id));
};
</script>

<template>
    <Head title="تعديل المجموعة الدراسية" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('admin.groups.index')" class="mr-4 text-gray-500 hover:text-gray-700"><ArrowRightIcon class="w-6 h-6" /></Link>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center pr-4 border-r-2 border-gray-200">
                    <UsersIcon class="w-6 h-6 ml-2 text-blue-600" /> تعديل المجموعة: {{ group.name }}
                </h2>
            </div>
        </template>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-blue-500 to-cyan-500"></div>
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">اسم المجموعة <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.name" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3" placeholder="مثال: مجموعة أ، مجموعة ب..." required>
                                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Stage -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">المرحلة الدراسية <span class="text-red-500">*</span></label>
                                <select v-model="form.stage_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3" required>
                                    <option value="" disabled>-- اختر المرحلة --</option>
                                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                </select>
                                <p v-if="form.errors.stage_id" class="mt-2 text-sm text-red-600">{{ form.errors.stage_id }}</p>
                            </div>

                            <!-- Study Type -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">نوع الدراسة <span class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    <!-- صباحي - Morning -->
                                    <div
                                        @click="form.study_type = 'morning'"
                                        class="flex-1 flex items-center justify-center gap-3 p-4 rounded-xl cursor-pointer border-2 select-none transition-all duration-150"
                                        :class="form.study_type === 'morning' ? 'bg-yellow-50 border-yellow-400 shadow-md' : 'bg-gray-50 border-gray-200 hover:border-yellow-200'"
                                    >
                                        <span class="text-2xl">☀️</span>
                                        <span class="font-bold text-gray-800">صباحي</span>
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="form.study_type === 'morning' ? 'border-yellow-500 bg-yellow-500' : 'border-gray-300'">
                                            <div v-if="form.study_type === 'morning'" class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <!-- مسائي - Evening -->
                                    <div
                                        @click="form.study_type = 'evening'"
                                        class="flex-1 flex items-center justify-center gap-3 p-4 rounded-xl cursor-pointer border-2 select-none transition-all duration-150"
                                        :class="form.study_type === 'evening' ? 'bg-teal-50 border-indigo-400 shadow-md' : 'bg-gray-50 border-gray-200 hover:border-indigo-200'"
                                    >
                                        <span class="text-2xl">🌙</span>
                                        <span class="font-bold text-gray-800">مسائي</span>
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="form.study_type === 'evening' ? 'border-indigo-500 bg-teal-500' : 'border-gray-300'">
                                            <div v-if="form.study_type === 'evening'" class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.study_type" class="mt-2 text-sm text-red-600">{{ form.errors.study_type }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                                <Link :href="route('admin.groups.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-all ml-3">إلغاء</Link>
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg hover:from-blue-700 hover:to-cyan-700 transition-all disabled:opacity-50">
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
