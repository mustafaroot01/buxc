<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, SearchIcon, UsersIcon, LayersIcon, ChevronDownIcon, ChevronUpIcon, Trash2Icon, PencilIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps<{
    stages: {
        data: Array<{
            id: string;
            name: string;
            groups: Array<{
                id: string;
                name: string;
                study_type: string;
            }>;
        }>;
        links: Array<any>;
    };
    filters: { search?: string };
}>();

// Track which stages are open — all closed by default
const openStages = ref<Record<string, boolean>>({});

const toggle = (stageId: string) => {
    openStages.value[stageId] = !openStages.value[stageId];
};

const deleteGroup = (id: string) => {
    if (confirm('هل أنت متأكد من حذف هذه المجموعة الدراسية؟')) {
        router.delete(route('admin.groups.destroy', id));
    }
};
</script>

<template>
    <Head title="إدارة المجموعات الدراسية" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <UsersIcon class="w-6 h-6 ml-2 text-blue-600" />
                    إدارة المجموعات الدراسية
                </h2>
                <Link :href="route('admin.groups.create')" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg shadow-blue-500/30 hover:from-blue-700 hover:to-cyan-700 transition-all">
                    <PlusIcon class="w-5 h-5 ml-2" /> إضافة مجموعة جديدة
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

                <!-- Search -->
                <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <form @submit.prevent="" class="flex items-center w-full max-w-2xl">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <SearchIcon class="w-5 h-5 text-gray-400" />
                            </div>
                            <input type="text" name="search" :value="filters.search" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 p-3" placeholder="ابحث باسم المجموعة...">
                        </div>
                        <button type="submit" class="p-3 mr-3 text-blue-600 bg-teal-50 rounded-xl hover:bg-teal-100 transition-colors">
                            <SearchIcon class="w-5 h-5" />
                        </button>
                    </form>
                </div>

                <!-- Accordion Stages -->
                <div class="space-y-4">
                    <div v-for="stage in stages.data" :key="stage.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-200">

                        <!-- Stage Header (clickable) -->
                        <button
                            type="button"
                            @click="toggle(stage.id)"
                            class="w-full flex items-center justify-between px-6 py-5 hover:bg-gray-50 transition-colors focus:outline-none group"
                        >
                            <div class="flex items-center gap-4">
                                <!-- Stage icon -->
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-teal-50 to-cyan-100 flex items-center justify-center text-teal-700 border border-blue-200 shadow-sm group-hover:shadow-md transition-shadow">
                                    <LayersIcon class="w-5 h-5" />
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 text-lg leading-tight">{{ stage.name }}</p>
                                    <p class="text-sm text-gray-400 mt-0.5">
                                        {{ stage.groups.length }} مجموعة
                                        <span v-if="stage.groups.length === 0" class="text-red-400">(لا توجد مجموعات)</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <!-- Group count badges -->
                                <div class="hidden sm:flex items-center gap-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                        ☀️ {{ stage.groups.filter((g: any) => g.study_type === 'morning').length }} صباحي
                                    </span>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-teal-100 text-indigo-700">
                                        🌙 {{ stage.groups.filter((g: any) => g.study_type === 'evening').length }} مسائي
                                    </span>
                                </div>
                                <!-- Chevron -->
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors" :class="openStages[stage.id] ? 'bg-teal-100 text-blue-600' : 'bg-gray-100 text-gray-400'">
                                    <ChevronUpIcon v-if="openStages[stage.id]" class="w-5 h-5 transition-transform" />
                                    <ChevronDownIcon v-else class="w-5 h-5 transition-transform" />
                                </div>
                            </div>
                        </button>

                        <!-- Divider when open -->
                        <div v-if="openStages[stage.id]" class="h-px bg-gradient-to-r from-transparent via-blue-200 to-transparent mx-6"></div>

                        <!-- Groups Table (collapsible) -->
                        <div v-if="openStages[stage.id]" class="px-6 pb-4 pt-2">
                            <div v-if="stage.groups.length > 0" class="overflow-hidden rounded-xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50/80">
                                        <tr>
                                            <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase">اسم المجموعة</th>
                                            <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase">نوع الدراسة</th>
                                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-50">
                                        <tr v-for="group in stage.groups" :key="group.id" class="hover:bg-slate-50 transition-colors">
                                            <td class="px-5 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-teal-50 to-cyan-100 flex items-center justify-center text-teal-700 font-bold text-sm border border-blue-200">
                                                        {{ group.name.charAt(0) }}
                                                    </div>
                                                    <span class="font-bold text-gray-900">{{ group.name }}</span>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3">
                                                <span v-if="group.study_type === 'morning'" class="px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-700">☀️ صباحي</span>
                                                <span v-else class="px-3 py-1 text-xs font-bold rounded-full bg-teal-100 text-indigo-700">🌙 مسائي</span>
                                            </td>
                                            <td class="px-5 py-3 text-left">
                                                <div class="flex items-center justify-end gap-2">
                                                    <Link :href="route('admin.groups.edit', group.id)" class="p-1.5 text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors">
                                                        <PencilIcon class="w-4 h-4" />
                                                    </Link>
                                                    <button @click="deleteGroup(group.id)" class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                                        <Trash2Icon class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="py-8 text-center text-gray-400 text-sm">
                                <UsersIcon class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                                لا توجد مجموعات في هذه المرحلة بعد.
                            </div>
                        </div>

                    </div>

                    <!-- Empty state -->
                    <div v-if="stages.data.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 py-16 text-center">
                        <LayersIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                        <p class="text-lg font-medium text-gray-500">لا توجد مراحل دراسية مضافة.</p>
                        <p class="text-sm text-gray-400 mt-1">أضف مرحلة دراسية أولاً من صفحة المراحل.</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-8" v-if="stages.data.length > 0">
                    <Pagination :links="stages.links" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
