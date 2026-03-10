<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, SearchIcon, UsersIcon, Trash2Icon, PencilIcon } from 'lucide-vue-next';
import Pagination from '@/Components/Pagination.vue';

defineProps<{
    teachers: any;
    filters: { search?: string };
}>();

const deleteTeacher = (id: string) => {
    if (confirm('هل أنت متأكد من حذف هذا الأستاذ؟')) {
        router.delete(route('admin.teachers.destroy', id));
    }
};
</script>

<template>
    <Head title="إدارة الأساتذة" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight flex items-center">
                    <UsersIcon class="w-6 h-6 ml-2 text-emerald-600" />
                    إدارة الأساتذة
                </h2>
                <Link :href="route('admin.teachers.create')" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-lg shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:from-emerald-700 hover:to-teal-700 focus:outline-none transition-all duration-200">
                    <PlusIcon class="w-5 h-5 ml-2" />
                    إضافة أستاذ جديد
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <form @submit.prevent="" class="flex items-center w-full max-w-2xl">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <SearchIcon class="w-5 h-5 text-gray-400" />
                            </div>
                            <input type="text" name="search" :value="filters.search" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full pr-12 p-3 transition-colors" placeholder="ابحث باسم الأستاذ أو البريد الإلكتروني...">
                        </div>
                        <button type="submit" class="p-3 mr-3 text-sm font-medium text-emerald-600 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                            <SearchIcon class="w-5 h-5" />
                        </button>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-xl shadow-gray-200/50 sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-16">#</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">الأستاذ</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">البريد الإلكتروني</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">تاريخ الانضمام</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <tr v-for="(teacher, index) in teachers.data" :key="teacher.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-500 text-center">
                                        {{ (Number(teachers.current_page) - 1) * Number(teachers.per_page) + index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div v-if="teacher.photo_path" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden ml-3 border border-gray-200 shadow-sm shrink-0">
                                                <img :src="'/storage/' + teacher.photo_path" class="w-full h-full object-cover">
                                            </div>
                                            <div v-else class="w-10 h-10 rounded-full bg-gradient-to-tr from-emerald-100 to-teal-100 flex items-center justify-center text-emerald-700 font-bold ml-3 border border-emerald-200 shadow-sm text-lg shrink-0">
                                                {{ teacher.full_name?.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ teacher.full_name }}</div>
                                                <div class="text-xs text-emerald-600 font-medium">
                                                    <span v-if="teacher.academic_title">{{ teacher.academic_title }}</span>
                                                    <span v-else>أستاذ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ teacher.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(teacher.created_at).toLocaleDateString('ar-EG') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.teachers.edit', teacher.id)" class="p-2 text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors" title="تعديل">
                                                <PencilIcon class="w-4 h-4" />
                                            </Link>
                                            <button @click="deleteTeacher(teacher.id)" class="p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" title="حذف">
                                                <Trash2Icon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="teachers.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <UsersIcon class="w-12 h-12 mb-4 text-gray-300" />
                                            <p class="text-lg font-medium">لا يوجد أساتذة مسجلون.</p>
                                            <p class="text-sm text-gray-400 mt-1">اضغط على "إضافة أستاذ جديد" للبدء.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Component -->
                    <Pagination v-if="teachers.data.length > 0" :links="teachers.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
