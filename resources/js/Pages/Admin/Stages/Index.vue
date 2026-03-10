<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PlusIcon, SearchIcon, LayersIcon } from 'lucide-vue-next';
import Pagination from '@/Components/Pagination.vue';

defineProps<{
    stages: any; 
    filters: { search?: string };
}>();
</script>

<template>
    <Head title="المراحل الدراسية" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight flex items-center">
                    <LayersIcon class="w-6 h-6 ml-2 text-teal-600" />
                    إدارة المراحل الدراسية
                </h2>
                <Link :href="route('admin.stages.create')" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <PlusIcon class="w-5 h-5 ml-2" />
                    إضافة مرحلة جديدة
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Search and Filters Section -->
                <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <form @submit.prevent="" class="flex items-center w-full max-w-2xl">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <SearchIcon class="w-5 h-5 text-gray-400" />
                            </div>
                            <input type="text" name="search" :value="filters.search" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 p-3 transition-colors" placeholder="ابحث عن مرحلة دراسية...">
                        </div>
                        <button type="submit" class="p-3 mr-3 text-sm font-medium text-teal-600 bg-teal-50 rounded-xl hover:bg-teal-100 focus:ring-4 focus:outline-none focus:ring-indigo-300 transition-colors">
                            <span class="sr-only">بحث</span>
                            <SearchIcon class="w-5 h-5" />
                        </button>
                    </form>
                </div>

                <!-- Data Table -->
                <div class="overflow-hidden bg-white shadow-xl shadow-gray-200/50 sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">اسم المرحلة</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">الوصف</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">تاريخ الإضافة</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <tr v-for="stage in stages.data" :key="stage.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-700 font-bold ml-3 border border-indigo-200 shadow-sm">
                                                <LayersIcon class="w-5 h-5" />
                                            </div>
                                            <div class="text-sm font-bold text-gray-900">{{ stage.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                        {{ stage.description || 'لا يوجد وصف' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(stage.created_at).toLocaleDateString('ar-EG') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-3 space-x-reverse">
                                            <Link :href="route('admin.stages.edit', stage.id)" class="p-2 text-teal-600 bg-teal-50 hover:bg-teal-100 hover:text-indigo-900 rounded-lg transition-colors" title="تعديل">
                                                تعديل
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="stages.data.length === 0">
                                    <td colspan="4" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <LayersIcon class="w-12 h-12 mb-4 text-gray-300" />
                                            <p class="text-lg font-medium">لم يتم العثور على أي مراحل دراسية.</p>
                                            <p class="text-sm text-gray-400 mt-1">اضغط على إضافة مرحلة جديدة للبدء.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Component -->
                    <Pagination :links="stages.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
