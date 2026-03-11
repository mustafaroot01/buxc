<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { HistoryIcon, SearchIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    activities: any;
    filters: { search?: string };
}>();

const search = ref(props.filters.search || '');

const submitSearch = () => {
    router.get(route('admin.audit.index'), { search: search.value }, { preserveState: true, replace: true });
};

let searchTimeout: any;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        submitSearch();
    }, 300);
});

// Map event names to Arabic
const eventLabel = (desc: string): { label: string; color: string } => {
    if (desc?.startsWith('تم إضافة') || desc?.includes('created'))
        return { label: 'إضافة', color: 'bg-emerald-100 text-emerald-800' };
    if (desc?.startsWith('تم تعديل') || desc?.includes('updated'))
        return { label: 'تعديل', color: 'bg-amber-100 text-amber-800' };
    if (desc?.startsWith('تم حذف') || desc?.includes('deleted'))
        return { label: 'حذف', color: 'bg-red-100 text-red-800' };
    if (desc?.startsWith('تم استعادة') || desc?.includes('restored'))
        return { label: 'استعادة', color: 'bg-teal-100 text-teal-800' };
    return { label: 'عملية', color: 'bg-gray-100 text-gray-700' };
};

const logBadgeColor = (logName: string): string => {
    const map: Record<string, string> = {
        'الطلاب': 'bg-blue-100 text-blue-800',
        'الأساتذة': 'bg-teal-100 text-teal-800',
        'المواد': 'bg-amber-100 text-amber-800',
        'المراحل': 'bg-indigo-100 text-indigo-800',
        'المجموعات': 'bg-purple-100 text-purple-800',
        'المحاضرات': 'bg-rose-100 text-rose-800',
    };
    return map[logName] ?? 'bg-gray-100 text-gray-700';
};

const formatDate = (dateStr: string): string => {
    return new Date(dateStr).toLocaleString('ar-IQ', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};
</script>

<template>
    <Head title="سجل النشاطات" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight flex items-center">
                    <HistoryIcon class="w-6 h-6 ml-2 text-teal-600" />
                    سجل النشاطات والعمليات
                </h2>
                <span class="text-sm font-medium text-gray-500">{{ activities.total }} سجل</span>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Search Bar -->
                <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <form @submit.prevent="submitSearch" class="flex items-center w-full max-w-2xl">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <SearchIcon class="w-5 h-5 text-gray-400" />
                            </div>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="ابحث في النشاطات..."
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 block w-full pr-12 p-3 transition-colors"
                            >
                        </div>
                        <button type="submit" class="p-3 mr-3 text-sm font-medium text-teal-600 bg-teal-50 rounded-xl hover:bg-teal-100 transition-colors">
                            <SearchIcon class="w-5 h-5" />
                        </button>
                    </form>
                </div>

                <!-- Activity Table -->
                <div class="overflow-hidden bg-white shadow-xl shadow-gray-200/50 sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-16">#</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">التاريخ والوقت</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">المستخدم</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">نوع العملية</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">القسم</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <tr v-for="(activity, index) in activities.data" :key="activity.id" class="hover:bg-slate-50 transition-colors">
                                    <!-- Row Number -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-400 text-center">
                                        {{ (Number(activities.current_page) - 1) * Number(activities.per_page) + Number(index) + 1 }}
                                    </td>

                                    <!-- Date -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-medium text-gray-700">{{ formatDate(activity.created_at) }}</div>
                                    </td>

                                    <!-- User / Causer -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div v-if="activity.causer" class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-sm mb-1">
                                                {{ (activity.causer.full_name || activity.causer.email || 'N')?.charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="text-xs font-semibold text-gray-800">{{ activity.causer.full_name || activity.causer.email }}</span>
                                        </div>
                                        <span v-else class="text-xs text-gray-400 italic">النظام</span>
                                    </td>

                                    <!-- Event Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span :class="['px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full', eventLabel(activity.description).color]">
                                            {{ eventLabel(activity.description).label }}
                                        </span>
                                    </td>

                                    <!-- Log Name Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span v-if="activity.log_name" :class="['px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full', logBadgeColor(activity.log_name)]">
                                            {{ activity.log_name }}
                                        </span>
                                        <span v-else class="text-gray-400 text-xs">—</span>
                                    </td>

                                    <!-- Description -->
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium text-center max-w-xs">
                                        <span :title="activity.description" class="block truncate max-w-[250px] mx-auto">{{ activity.description }}</span>
                                    </td>
                                </tr>

                                <tr v-if="activities.data.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <HistoryIcon class="w-12 h-12 mb-4 text-gray-300" />
                                            <p class="text-lg font-medium">لا توجد نشاطات مسجلة.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="activities.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            الصفحة {{ activities.current_page }} من {{ activities.last_page }}
                        </span>
                        <div class="flex gap-2">
                            <a
                                v-if="activities.prev_page_url"
                                :href="activities.prev_page_url"
                                class="px-4 py-2 text-sm font-medium text-teal-600 bg-teal-50 rounded-lg hover:bg-teal-100 transition-colors"
                            >السابق</a>
                            <a
                                v-if="activities.next_page_url"
                                :href="activities.next_page_url"
                                class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-teal-600 to-emerald-500 rounded-lg hover:from-teal-700 hover:to-emerald-600 transition-all shadow-sm"
                            >التالي</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
