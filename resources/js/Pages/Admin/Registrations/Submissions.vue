<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRightIcon, CheckCircle2Icon, XCircleIcon, ClockIcon, UsersIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    form: any;
    submissions: Array<any>;
    filters: { status?: string };
}>();

const filter = ref(props.filters.status || '');

const applyFilter = () => {
    router.get(route('admin.registrations.submissions', props.form.id), { status: filter.value }, {
        preserveState: true, preserveScroll: true, replace: true
    });
};

const approve = (submissionId: string) => {
    router.post(route('admin.registrations.approve', { id: props.form.id, submissionId }));
};

const approveAll = () => {
    if (confirm('هل تريد الموافقة على جميع الطلبات المعلقة وإضافتهم للنظام؟')) {
        router.post(route('admin.registrations.approve-all', props.form.id));
    }
};

const reject = (submissionId: string) => {
    if (confirm('هل تريد رفض هذا الطلب؟')) {
        router.post(route('admin.registrations.reject', { id: props.form.id, submissionId }));
    }
};

const pendingCount = props.submissions.filter(s => s.status === 'pending').length;

const statusColors: Record<string, string> = {
    pending:  'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-rose-50 text-rose-600 border-rose-200',
};

const statusLabels: Record<string, string> = {
    pending:  'معلق',
    approved: 'موافق عليه',
    rejected: 'مرفوض',
};
</script>

<template>
    <Head :title="`طلبات: ${form.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.registrations.index')" class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 transition-colors">
                    <ArrowRightIcon class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ form.title }}</h2>
                    <p class="text-sm text-gray-500">{{ form.stage?.name }} &bull; {{ form.group?.name }} &bull; {{ form.study_type === 'morning' ? 'صباحي' : 'مسائي' }}</p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-5">

                <!-- Summary + Bulk Action -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-6 text-sm">
                        <span class="flex items-center gap-2 text-gray-600 font-medium">
                            <UsersIcon class="w-4 h-4" />
                            إجمالي: <strong>{{ submissions.length }}</strong>
                        </span>
                        <span class="flex items-center gap-2 text-amber-600 font-semibold">
                            <ClockIcon class="w-4 h-4" />
                            معلق: {{ pendingCount }}
                        </span>
                        <span class="flex items-center gap-2 text-emerald-600 font-semibold">
                            <CheckCircle2Icon class="w-4 h-4" />
                            موافق عليه: {{ submissions.filter(s => s.status === 'approved').length }}
                        </span>
                    </div>
                    <button v-if="pendingCount > 0" @click="approveAll"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-500 rounded-xl shadow-sm hover:from-emerald-700 hover:to-teal-600 transition-all">
                        <CheckCircle2Icon class="w-4 h-4" />
                        موافقة جماعية ({{ pendingCount }})
                    </button>
                </div>

                <!-- Filter -->
                <div class="flex items-center gap-3">
                    <button v-for="(label, val) in { '': 'الكل', pending: 'معلق', approved: 'موافق عليه', rejected: 'مرفوض' }"
                        :key="val"
                        @click="filter = val; applyFilter()"
                        :class="filter === val ? 'bg-teal-600 text-white border-transparent' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                        class="px-4 py-2 text-sm font-semibold rounded-xl border transition-all">
                        {{ label }}
                    </button>
                </div>

                <!-- Empty -->
                <div v-if="submissions.length === 0" class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <ClockIcon class="w-12 h-12 mx-auto text-gray-200 mb-3" />
                    <p class="text-gray-500 font-medium">لا توجد طلبات حتى الآن</p>
                </div>

                <!-- Submissions Table -->
                <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-teal-500 to-emerald-400" />
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-4 text-right text-xs font-black text-gray-500 uppercase">#</th>
                                    <th class="px-5 py-4 text-right text-xs font-black text-gray-500 uppercase">الاسم الكامل</th>
                                    <th class="px-5 py-4 text-center text-xs font-black text-gray-500 uppercase">الرقم الجامعي</th>
                                    <th class="px-5 py-4 text-center text-xs font-black text-gray-500 uppercase">الجنس</th>
                                    <th class="px-5 py-4 text-center text-xs font-black text-gray-500 uppercase">الحالة</th>
                                    <th class="px-5 py-4 text-center text-xs font-black text-gray-500 uppercase">الإجراء</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(sub, i) in submissions" :key="sub.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 text-sm text-gray-400 font-bold">{{ i + 1 }}</td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-900 text-sm">
                                            {{ sub.first_name }} {{ sub.second_name }} {{ sub.last_name }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-lg text-gray-600">{{ sub.student_external_id }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center text-sm text-gray-500">{{ sub.gender === 'male' ? 'ذكر' : 'أنثى' }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span :class="statusColors[sub.status]"
                                            class="text-xs font-bold px-3 py-1 rounded-full border">
                                            {{ statusLabels[sub.status] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div v-if="sub.status === 'pending'" class="flex items-center justify-center gap-2">
                                            <button @click="approve(sub.id)"
                                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl border border-emerald-100 transition-colors">
                                                <CheckCircle2Icon class="w-3.5 h-3.5" />
                                                موافقة
                                            </button>
                                            <button @click="reject(sub.id)"
                                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-100 transition-colors">
                                                <XCircleIcon class="w-3.5 h-3.5" />
                                                رفض
                                            </button>
                                        </div>
                                        <span v-else class="text-xs text-gray-400 italic">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
