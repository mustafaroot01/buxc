<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    AlertTriangleIcon, 
    SearchIcon,
} from 'lucide-vue-next';

const props = defineProps<{
    warnings: {
        data: Array<{
            id: string;
            student_name: string;
            student_external_id: string;
            stage_name: string;
            group_name: string;
            active_warnings: number;
            total_warnings: number;
            consecutive_absences: number;
            last_warning_date: string;
        }>;
        links: any[];
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
    };
}>();

const searchTerm = ref(props.filters.search || '');

watch(searchTerm, (value) => {
    router.get(route('teacher.warnings.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
});
</script>

<template>
    <Head title="مراقبة الإنذارات" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight flex items-center pr-4 border-r-4 border-amber-500">
                <AlertTriangleIcon class="w-7 h-7 ml-3 text-amber-500" />
                مراقبة الإنذارات
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-[calc(100vh-4rem)]">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Header & Search -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            الطلاب المنذرين
                            <span class="bg-amber-100 text-amber-700 py-1 px-3 rounded-full text-sm font-semibold">
                                {{ warnings.total }} طالب
                            </span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">عرض جميع الطلاب ضمن مجموعاتك الذين لديهم إنذارات نشطة</p>
                    </div>

                    <div class="relative w-full sm:w-96">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <SearchIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            v-model="searchTerm"
                            placeholder="ابحث عن طريق اسم الطالب..."
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 sm:text-sm transition-all"
                        >
                    </div>
                </div>

                <!-- Warnings Data Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">الطالب</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">المرحلة والمجموعة</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">غياب متتالي</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">إنذارات نشطة</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">إجمالي الإنذارات</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">أحدث إنذار</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="student in warnings.data" :key="student.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full flex items-center justify-center border border-amber-200">
                                                <span class="text-amber-700 font-bold text-sm">{{ student.student_name.charAt(0) }}</span>
                                            </div>
                                            <div class="mr-4">
                                                <div class="text-sm font-bold text-gray-900">{{ student.student_name }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">الرقم: <span class="font-mono">{{ student.student_external_id }}</span></div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm text-gray-900 font-medium">{{ student.stage_name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ student.group_name }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                              :class="student.consecutive_absences > 0 ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-gray-100 text-gray-800'">
                                            {{ student.consecutive_absences }} مقاطع
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                              :class="student.active_warnings > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-green-100 text-green-800'">
                                            {{ student.active_warnings }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ student.total_warnings }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-medium" :class="student.last_warning_date === 'N/A' ? 'text-gray-400' : 'text-gray-900'">
                                            {{ student.last_warning_date }}
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="warnings.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                                <AlertTriangleIcon class="w-8 h-8 text-gray-400" />
                                            </div>
                                            <p class="text-base font-medium text-gray-900">لا يوجد طلاب منذرين</p>
                                            <p class="text-sm mt-1">لم يتم العثور على أي طلاب لديهم إنذارات في مجموعاتك الحالية.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="warnings.links.length > 3" class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <template v-for="(link, i) in warnings.links" :key="i">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                :class="[
                                    link.active 
                                        ? 'z-10 bg-amber-50 border-amber-500 text-amber-600' 
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                    i === 0 ? 'rounded-r-md' : '',
                                    i === warnings.links.length - 1 ? 'rounded-l-md' : ''
                                ]"
                            />
                            <span 
                                v-else
                                v-html="link.label"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-400"
                                :class="[
                                    i === 0 ? 'rounded-r-md' : '',
                                    i === warnings.links.length - 1 ? 'rounded-l-md' : ''
                                ]"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
