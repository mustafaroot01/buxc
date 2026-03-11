<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ClipboardListIcon, PlusIcon, Trash2Icon, ExternalLinkIcon, ToggleLeftIcon, ToggleRightIcon, UsersIcon, CheckCircle2Icon, ClockIcon } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    forms: Array<any>;
    stages: Array<any>;
}>();

const showCreateModal = ref(false);
const copiedSlug = ref<string | null>(null);

const form = useForm({
    title: '',
    stage_id: '',
    group_id: '',
    study_type: 'morning',
});

const availableGroups = computed(() => {
    if (!form.stage_id) return [];
    const stage = props.stages.find(s => s.id === form.stage_id);
    return stage ? stage.groups.filter((g: any) => form.study_type ? g.study_type === form.study_type : true) : [];
});

watch([() => form.stage_id, () => form.study_type], () => {
    form.group_id = '';
});

const createForm = () => {
    form.post(route('admin.registrations.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const copyLink = (slug: string) => {
    const url = `${window.location.origin}/register/${slug}`;
    navigator.clipboard.writeText(url);
    copiedSlug.value = slug;
    setTimeout(() => { copiedSlug.value = null; }, 2000);
};

const toggleForm = (id: string) => {
    router.post(route('admin.registrations.toggle', id));
};

const deleteForm = (id: string) => {
    if (confirm('هل أنت متأكد من حذف هذه الاستمارة؟ سيتم حذف جميع الطلبات معها.')) {
        router.delete(route('admin.registrations.destroy', id));
    }
};
</script>

<template>
    <Head title="استمارات التسجيل" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <ClipboardListIcon class="w-7 h-7 text-teal-600" />
                    استمارات التسجيل
                </h2>
                <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-emerald-500 rounded-xl shadow-lg shadow-teal-500/20 hover:from-teal-700 hover:to-emerald-600 transition-all">
                    <PlusIcon class="w-5 h-5" />
                    استمارة جديدة
                </button>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-5">

                <!-- Empty State -->
                <div v-if="forms.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                    <ClipboardListIcon class="w-16 h-16 mx-auto text-gray-200 mb-4" />
                    <p class="text-gray-500 font-medium text-lg">لا توجد استمارات بعد</p>
                    <p class="text-gray-400 text-sm mt-1">أنشئ استمارة جديدة وشارك رابطها مع الطلاب.</p>
                    <button @click="showCreateModal = true"
                        class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-all">
                        <PlusIcon class="w-4 h-4" />
                        إنشاء أول استمارة
                    </button>
                </div>

                <!-- Forms List -->
                <div v-for="f in forms" :key="f.id"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="h-1" :class="f.is_open ? 'bg-gradient-to-r from-teal-500 to-emerald-400' : 'bg-gray-300'" />
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-lg font-bold text-gray-900">{{ f.title }}</h3>
                                    <span :class="f.is_open ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'"
                                        class="text-xs font-bold px-3 py-1 rounded-full border">
                                        {{ f.is_open ? 'مفتوحة' : 'مغلقة' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500">
                                    {{ f.stage?.name }} &bull; {{ f.group?.name }} &bull;
                                    {{ f.study_type === 'morning' ? 'صباحي' : 'مسائي' }}
                                </p>
                                <div class="flex items-center gap-5 mt-3 text-sm">
                                    <span class="flex items-center gap-1.5 text-gray-500">
                                        <UsersIcon class="w-4 h-4" />
                                        {{ f.submissions_count }} طلب
                                    </span>
                                    <span class="flex items-center gap-1.5 text-amber-600">
                                        <ClockIcon class="w-4 h-4" />
                                        {{ f.pending_count }} معلق
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Copy Link -->
                                <button @click="copyLink(f.slug)"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-xl transition-colors border border-teal-100">
                                    <ExternalLinkIcon class="w-4 h-4" />
                                    {{ copiedSlug === f.slug ? 'تم النسخ!' : 'نسخ الرابط' }}
                                </button>
                                <!-- View Submissions -->
                                <a :href="route('admin.registrations.submissions', f.id)"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-colors border border-indigo-100">
                                    <CheckCircle2Icon class="w-4 h-4" />
                                    مراجعة الطلبات
                                </a>
                                <!-- Toggle Open/Close -->
                                <button @click="toggleForm(f.id)"
                                    :class="f.is_open ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-100'"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl transition-colors border">
                                    <component :is="f.is_open ? ToggleRightIcon : ToggleLeftIcon" class="w-4 h-4" />
                                    {{ f.is_open ? 'إغلاق' : 'فتح' }}
                                </button>
                                <!-- Delete -->
                                <button @click="deleteForm(f.id)"
                                    class="flex items-center gap-2 px-3 py-2 text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors border border-rose-100">
                                    <Trash2Icon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Teleport to="body">
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCreateModal = false" />
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 z-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <ClipboardListIcon class="w-6 h-6 text-teal-600" />
                        إنشاء استمارة تسجيل
                    </h3>
                    <form @submit.prevent="createForm" class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">عنوان الاستمارة <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" required
                                class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all"
                                placeholder="مثال: تسجيل المرحلة الثانية - صباحي" />
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">المرحلة <span class="text-red-500">*</span></label>
                                <select v-model="form.stage_id" required
                                    class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all">
                                    <option value="">-- اختر --</option>
                                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5">نوع الدراسة <span class="text-red-500">*</span></label>
                                <select v-model="form.study_type" required
                                    class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all">
                                    <option value="morning">صباحي</option>
                                    <option value="evening">مسائي</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">المجموعة / الكروب <span class="text-red-500">*</span></label>
                            <select v-model="form.group_id" required :disabled="availableGroups.length === 0"
                                class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all disabled:bg-gray-100 disabled:text-gray-400">
                                <option value="">{{ availableGroups.length === 0 ? 'اختر المرحلة ونوع الدراسة أولاً' : '-- اختر الكروب --' }}</option>
                                <option v-for="group in availableGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                            </select>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 py-3 text-sm font-bold text-white bg-gradient-to-r from-teal-600 to-emerald-500 rounded-xl hover:from-teal-700 hover:to-emerald-600 transition-all disabled:opacity-50">
                                {{ form.processing ? 'جاري الإنشاء...' : 'إنشاء الاستمارة' }}
                            </button>
                            <button type="button" @click="showCreateModal = false"
                                class="px-6 py-3 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
