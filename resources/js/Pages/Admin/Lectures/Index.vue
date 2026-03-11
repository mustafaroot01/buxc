<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    BookOpenIcon, SearchIcon, FilterIcon,
    CheckCircle2Icon, XCircleIcon, ClockIcon,
    LayersIcon, XIcon, DownloadIcon
} from 'lucide-vue-next';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    lectures: any;
    stats: { total: number; active: number; closed: number };
    teachers: { id: string; full_name: string }[];
    stages: { id: string; name: string }[];
    groups: { id: string; name: string; stage_id: string; study_type: string }[];
    filters: {
        search?: string;
        status?: string;
        teacher_id?: string;
        stage_id?: string;
        group_id?: string;
        study_type?: string;
    };
}>();

const form = ref({ ...props.filters });

// ── Cascading filters ───────────────────────────────────────────────────────

// Step 2: groups filtered by stage + study_type
const filteredGroups = computed(() => {
    let g = props.groups;
    if (form.value.stage_id) g = g.filter(x => x.stage_id === form.value.stage_id);
    if (form.value.study_type) g = g.filter(x => x.study_type === form.value.study_type);
    return g;
});

// Reset downstream when stage changes
watch(() => form.value.stage_id, () => {
    form.value.group_id = '';
    form.value.study_type = '';
    form.value.teacher_id = '';
    applyFilters();
});

watch(() => form.value.study_type, () => {
    form.value.group_id = '';
    applyFilters();
});

// ── Active filter badges ────────────────────────────────────────────────────
const activeFilters = computed(() => {
    const chips: { key: string; label: string }[] = [];
    if (form.value.stage_id) {
        const s = props.stages.find(x => x.id === form.value.stage_id);
        if (s) chips.push({ key: 'stage_id', label: `📚 ${s.name}` });
    }
    if (form.value.study_type) {
        chips.push({ key: 'study_type', label: form.value.study_type === 'morning' ? '🌤 صباحي' : '🌙 مسائي' });
    }
    if (form.value.group_id) {
        const g = props.groups.find(x => x.id === form.value.group_id);
        if (g) chips.push({ key: 'group_id', label: `👥 ${g.name}` });
    }
    if (form.value.teacher_id) {
        const t = props.teachers.find(x => x.id === form.value.teacher_id);
        if (t) chips.push({ key: 'teacher_id', label: `👨‍🏫 ${t.full_name}` });
    }
    if (form.value.status) {
        chips.push({ key: 'status', label: form.value.status === 'active' ? '🟢 نشطة' : '🔴 مغلقة' });
    }
    if (form.value.search) {
        chips.push({ key: 'search', label: `🔍 ${form.value.search}` });
    }
    return chips;
});

const removeFilter = (key: string) => {
    (form.value as any)[key] = '';
    // If removing stage, also reset downstream
    if (key === 'stage_id') { form.value.study_type = ''; form.value.group_id = ''; form.value.teacher_id = ''; }
    if (key === 'study_type') { form.value.group_id = ''; }
    applyFilters();
};

// ── Apply / Reset ───────────────────────────────────────────────────────────
let debounceTimer: any;
const applyFilters = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('admin.lectures.index'), form.value, { preserveState: true, replace: true });
    }, 300);
};

const resetFilters = () => {
    form.value = {};
    router.get(route('admin.lectures.index'), {}, { preserveState: false });
};

// ── Helpers ─────────────────────────────────────────────────────────────────
const formatDate = (dt: string) =>
    dt ? new Date(dt).toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';

const formatTime = (dt: string) =>
    dt ? new Date(dt).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' }) : '—';

const downloadLecture = (id: string) => {
    window.location.href = route('admin.lectures.export', id);
};
</script>

<template>
    <Head title="المحاضرات" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <BookOpenIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-900">المحاضرات</h2>
                    <p class="text-xs text-gray-500">مراقبة جميع محاضرات الأساتذة</p>
                </div>
            </div>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- ─── Stats Cards ──────────────────────────────────────── -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <BookOpenIcon class="w-6 h-6 text-indigo-600" />
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-900">{{ stats.total }}</p>
                            <p class="text-sm text-gray-500 font-semibold">إجمالي المحاضرات</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <CheckCircle2Icon class="w-6 h-6 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-3xl font-black text-emerald-600">{{ stats.active }}</p>
                            <p class="text-sm text-gray-500 font-semibold">محاضرات نشطة</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <XCircleIcon class="w-6 h-6 text-gray-400" />
                        </div>
                        <div>
                            <p class="text-3xl font-black text-gray-600">{{ stats.closed }}</p>
                            <p class="text-sm text-gray-500 font-semibold">محاضرات مغلقة</p>
                        </div>
                    </div>
                </div>

                <!-- ─── Filters ─────────────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <FilterIcon class="w-4 h-4 text-indigo-500" />
                            <span class="text-sm font-black text-gray-800">تصفية المحاضرات</span>
                        </div>
                        <button v-if="activeFilters.length > 0" @click="resetFilters"
                                class="text-xs text-red-500 hover:text-red-700 font-bold flex items-center gap-1 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all">
                            <XIcon class="w-3 h-3" />
                            مسح الكل
                        </button>
                    </div>

                    <!-- Step-by-step cascading selects -->
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">

                        <!-- Step 1: Stage -->
                        <div class="flex flex-col gap-1">
                            <label class="text-[11px] font-black text-indigo-600 flex items-center gap-1">
                                <span class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[9px] font-black">1</span>
                                المرحلة
                            </label>
                            <select v-model="form.stage_id"
                                    :class="form.stage_id ? 'border-indigo-400 bg-indigo-50 text-indigo-800 ring-2 ring-indigo-200' : 'border-gray-200 bg-gray-50 text-gray-600'"
                                    class="w-full px-3 py-2.5 text-sm rounded-xl outline-none transition-all font-semibold">
                                <option value="">كل المراحل</option>
                                <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>

                        <!-- Step 2: Study Type -->
                        <div class="flex flex-col gap-1">
                            <label class="text-[11px] font-black flex items-center gap-1"
                                   :class="form.stage_id ? 'text-amber-600' : 'text-gray-400'">
                                <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-black"
                                      :class="form.stage_id ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400'">2</span>
                                نوع الدراسة
                            </label>
                            <select v-model="form.study_type"
                                    :disabled="!form.stage_id"
                                    :class="form.study_type
                                        ? 'border-amber-400 bg-amber-50 text-amber-800 ring-2 ring-amber-200'
                                        : form.stage_id ? 'border-gray-200 bg-gray-50 text-gray-600' : 'border-gray-100 bg-gray-50 text-gray-300 cursor-not-allowed'"
                                    class="w-full px-3 py-2.5 text-sm rounded-xl outline-none transition-all font-semibold">
                                <option value="">الكل</option>
                                <option value="morning">🌤 صباحي</option>
                                <option value="evening">🌙 مسائي</option>
                            </select>
                        </div>

                        <!-- Step 3: Group -->
                        <div class="flex flex-col gap-1">
                            <label class="text-[11px] font-black flex items-center gap-1"
                                   :class="form.stage_id ? 'text-emerald-600' : 'text-gray-400'">
                                <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-black"
                                      :class="form.stage_id ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'">3</span>
                                المجموعة
                            </label>
                            <select v-model="form.group_id" @change="applyFilters"
                                    :disabled="!form.stage_id"
                                    :class="form.group_id
                                        ? 'border-emerald-400 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-200'
                                        : form.stage_id ? 'border-gray-200 bg-gray-50 text-gray-600' : 'border-gray-100 bg-gray-50 text-gray-300 cursor-not-allowed'"
                                    class="w-full px-3 py-2.5 text-sm rounded-xl outline-none transition-all font-semibold">
                                <option value="">كل المجموعات</option>
                                <option v-for="g in filteredGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                            </select>
                        </div>

                        <!-- Step 4: Teacher -->
                        <div class="flex flex-col gap-1">
                            <label class="text-[11px] font-black text-purple-600 flex items-center gap-1">
                                <span class="w-4 h-4 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-[9px] font-black">4</span>
                                الأستاذ
                            </label>
                            <select v-model="form.teacher_id" @change="applyFilters"
                                    :class="form.teacher_id ? 'border-purple-400 bg-purple-50 text-purple-800 ring-2 ring-purple-200' : 'border-gray-200 bg-gray-50 text-gray-600'"
                                    class="w-full px-3 py-2.5 text-sm rounded-xl outline-none transition-all font-semibold">
                                <option value="">كل الأساتذة</option>
                                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.full_name }}</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col gap-1">
                            <label class="text-[11px] font-black text-rose-600 flex items-center gap-1">
                                <span class="w-4 h-4 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-[9px] font-black">5</span>
                                الحالة
                            </label>
                            <select v-model="form.status" @change="applyFilters"
                                    :class="form.status ? 'border-rose-400 bg-rose-50 text-rose-800 ring-2 ring-rose-200' : 'border-gray-200 bg-gray-50 text-gray-600'"
                                    class="w-full px-3 py-2.5 text-sm rounded-xl outline-none transition-all font-semibold">
                                <option value="">كل الحالات</option>
                                <option value="active">🟢 نشطة</option>
                                <option value="closed">🔴 مغلقة</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="flex flex-col gap-1">
                            <label class="text-[11px] font-black text-gray-500 flex items-center gap-1">
                                <span class="w-4 h-4 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-[9px] font-black">6</span>
                                بحث
                            </label>
                            <div class="relative">
                                <SearchIcon class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                                <input v-model="form.search" @input="applyFilters"
                                       type="text" placeholder="عنوان أو أستاذ..."
                                       :class="form.search ? 'border-gray-400 bg-white ring-2 ring-gray-200' : 'border-gray-200 bg-gray-50'"
                                       class="w-full pr-9 pl-3 py-2.5 text-sm rounded-xl outline-none transition-all" />
                            </div>
                        </div>
                    </div>

                    <!-- Active filter badges -->
                    <div v-if="activeFilters.length > 0" class="px-5 pb-4 flex flex-wrap gap-2">
                        <span v-for="chip in activeFilters" :key="chip.key"
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-full text-xs font-black">
                            {{ chip.label }}
                            <button @click="removeFilter(chip.key)"
                                    class="w-4 h-4 rounded-full bg-indigo-200 hover:bg-indigo-300 flex items-center justify-center transition-colors">
                                <XIcon class="w-2.5 h-2.5" />
                            </button>
                        </span>
                    </div>
                </div>

                <!-- ─── Lectures Table ───────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

                    <!-- Table Head -->
                    <div class="hidden md:grid grid-cols-12 px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500">
                        <div class="col-span-2">المحاضرة</div>
                        <div class="col-span-2 text-center">الأستاذ</div>
                        <div class="col-span-2 text-center">المجموعة</div>
                        <div class="col-span-2 text-center">التاريخ والوقت</div>
                        <div class="col-span-1 text-center">الحضور</div>
                        <div class="col-span-1 text-center">الغياب</div>
                        <div class="col-span-1 text-center">الحالة</div>
                        <div class="col-span-1 text-center">الإجراء</div>
                    </div>

                    <!-- Rows -->
                    <div v-if="lectures.data.length === 0" class="py-20 text-center">
                        <BookOpenIcon class="w-12 h-12 text-gray-200 mx-auto mb-3" />
                        <p class="text-gray-400 font-medium">لا توجد محاضرات تطابق الفلتر</p>
                    </div>

                    <div v-for="(lecture, index) in lectures.data" :key="lecture.id"
                         class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-0 px-5 py-4 border-b border-gray-50 hover:bg-gray-50/70 transition-colors"
                         :class="(index as number) % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'">

                        <!-- Title + Subject -->
                        <div class="col-span-2 flex flex-col justify-center">
                            <p class="text-sm font-black text-gray-900 leading-tight">{{ lecture.title }}</p>
                            <p class="text-xs text-indigo-600 font-medium mt-0.5">
                                <BookOpenIcon class="w-3 h-3 inline ml-1" />{{ lecture.subject?.name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                <LayersIcon class="w-3 h-3 inline ml-1" />{{ lecture.group?.stage?.name }}
                            </p>
                        </div>

                        <!-- Teacher -->
                        <div class="col-span-2 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-700 font-black text-sm mx-auto mb-1">
                                    {{ lecture.teacher?.full_name?.charAt(0) }}
                                </div>
                                <p class="text-xs font-semibold text-gray-700">{{ lecture.teacher?.full_name }}</p>
                            </div>
                        </div>

                        <!-- Group -->
                        <div class="col-span-2 flex flex-col items-center justify-center gap-1">
                            <span class="text-xs font-bold text-gray-700">{{ lecture.group?.name }}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                                  :class="lecture.group?.study_type === 'morning'
                                      ? 'bg-amber-50 text-amber-700'
                                      : 'bg-blue-50 text-blue-700'">
                                {{ lecture.group?.study_type === 'morning' ? '🌤 صباحي' : '🌙 مسائي' }}
                            </span>
                        </div>

                        <!-- Date & Time -->
                        <div class="col-span-2 flex flex-col items-center justify-center text-center">
                            <p class="text-xs font-bold text-gray-700 flex items-center gap-1">
                                <ClockIcon class="w-3 h-3 text-gray-400" />
                                {{ formatDate(lecture.start_time) }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ formatTime(lecture.start_time) }}</p>
                        </div>

                        <!-- Present -->
                        <div class="col-span-1 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-lg font-black text-emerald-600">{{ lecture.present_count ?? 0 }}</p>
                                <p class="text-[10px] text-gray-400">حاضر</p>
                            </div>
                        </div>

                        <!-- Absent -->
                        <div class="col-span-1 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-lg font-black text-red-500">{{ lecture.absent_count ?? 0 }}</p>
                                <p class="text-[10px] text-gray-400">غائب</p>
                            </div>
                        </div>

                        <div class="col-span-1 flex items-center justify-center">
                            <span :class="lecture.status === 'active'
                                ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                : 'bg-gray-100 text-gray-500 border-gray-200'"
                                  class="text-[10px] font-black px-3 py-1 rounded-full border whitespace-nowrap">
                                {{ lecture.status === 'active' ? '🟢 نشطة' : '🔴 مغلقة' }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="col-span-1 flex items-center justify-center">
                            <button v-if="lecture.status === 'closed'"
                                    @click="downloadLecture(lecture.id)"
                                    class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                    title="تحميل التقرير">
                                <DownloadIcon class="w-4 h-4" />
                            </button>
                            <span v-else class="text-xs text-gray-300">—</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                        <span>إجمالي: <strong class="text-gray-900">{{ lectures.total }}</strong> محاضرة</span>
                        <Pagination :links="lectures.links" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
