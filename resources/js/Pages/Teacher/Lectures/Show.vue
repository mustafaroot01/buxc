<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    ArrowRightIcon, BookOpenIcon, UsersIcon, CheckCircle2Icon, 
    XCircleIcon, QrCodeIcon, DownloadIcon,
    ClockIcon, MapPinIcon, HandIcon, Trash2Icon, SearchIcon
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    lecture: {
        id: string;
        title: string;
        status: string;
        start_time: string;
        subject: { name: string; code: string };
        group: { name: string; study_type: string; stage: { name: string } };
    };
    students: Array<{
        id: string;
        name: string;
        student_id: string;
        status: 'present' | 'absent';
        check_in_method: string | null;
        check_in_at: string | null;
        attendance_id: string | null;
    }>;
    summary: {
        total: number;
        present: number;
        absent: number;
    };
}>();

// ── Reactive state ──────────────────────────────────────────────────────────
const rawSearch   = ref('');
const search      = ref('');
const filter      = ref<'all' | 'present' | 'absent'>('all');
const loadingIds  = ref<Set<string>>(new Set());

// Debounce the search
let debounceTimer: ReturnType<typeof setTimeout>;
watch(rawSearch, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { search.value = val.trim(); }, 250);
});

// Local reactive copy — instant UI updates without re-fetching
const localStudents = ref(props.students.map(s => ({ ...s })));

// ── Computed ────────────────────────────────────────────────────────────────
const filteredStudents = computed(() => {
    const q = search.value.toLowerCase();
    return localStudents.value.filter(s => {
        const matchFilter =
            filter.value === 'all' ? true :
            filter.value === 'present' ? s.status === 'present' : s.status === 'absent';
        if (!matchFilter) return false;
        if (!q) return true;
        return s.name.toLowerCase().includes(q) || (s.student_id ?? '').toLowerCase().includes(q);
    });
});

const localSummary = computed(() => ({
    total:   localStudents.value.length,
    present: localStudents.value.filter(s => s.status === 'present').length,
    absent:  localStudents.value.filter(s => s.status === 'absent').length,
}));

// Edits are now always allowed regardless of time (Phase 16 Optimization)
const isLocked = computed(() => false);

// ── Manual attendance toggle ─────────────────────────────────────────────────
const markLoading = (id: string, flag: boolean) => {
    const next = new Set(loadingIds.value);
    flag ? next.add(id) : next.delete(id);
    loadingIds.value = next;
};

const toggleAttendance = async (student: typeof props.students[0]) => {
    if (loadingIds.value.has(student.id)) return;
    markLoading(student.id, true);

    try {
        const token = decodeURIComponent(
            document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
        );
        const res = await fetch(route('teacher.lectures.mark-manual', props.lecture.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': token,
                'Accept':       'application/json',
            },
            body: JSON.stringify({ student_id: student.id }),
        });
        const data = await res.json();

        if (data.success) {
            const idx = localStudents.value.findIndex(s => s.id === student.id);
            if (idx !== -1) {
                if (data.action === 'added') {
                    localStudents.value[idx].status           = 'present';
                    localStudents.value[idx].check_in_method  = 'manual';
                    localStudents.value[idx].attendance_id    = data.attendance_id;
                } else {
                    localStudents.value[idx].status           = 'absent';
                    localStudents.value[idx].check_in_method  = null;
                    localStudents.value[idx].attendance_id    = null;
                }
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        markLoading(student.id, false);
    }
};

// ── Delete lecture ───────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deleting        = ref(false);
const deleteLecture   = () => {
    deleting.value = true;
    router.delete(route('teacher.lectures.destroy', props.lecture.id), {
        onFinish: () => { deleting.value = false; showDeleteModal.value = false; },
    });
};

// ── Helpers ──────────────────────────────────────────────────────────────────
const formatDate = (dt: string) =>
    new Date(dt).toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
const formatTime = (dt: string) =>
    new Date(dt).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head :title="`تفاصيل محاضرة: ${lecture.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('teacher.lectures.index')"
                      class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 transition-colors">
                    <ArrowRightIcon class="w-5 h-5" />
                </Link>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg">
                    <BookOpenIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 line-clamp-1">{{ lecture.title }}</h2>
                    <p class="text-sm text-gray-500">{{ lecture.subject?.name }} — {{ lecture.group?.name }}</p>
                </div>
            </div>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-5">

                <!-- ─── Lecture Info Bar ─────────────────────────────────── -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex flex-col gap-3 text-sm text-gray-600">
                            <span class="flex items-center gap-2 font-bold text-gray-800">
                                <ClockIcon class="w-4 h-4 text-teal-500" />
                                {{ formatDate(lecture.start_time) }} — {{ formatTime(lecture.start_time) }}
                            </span>
                            <span class="flex items-center gap-2 font-medium">
                                <MapPinIcon class="w-4 h-4 text-teal-500" />
                                {{ lecture.group?.stage?.name }} | {{ lecture.group?.name }}
                                <span class="bg-gray-100 px-2 py-0.5 rounded-md text-[10px]">{{ lecture.group?.study_type === 'morning' ? 'صباحي' : 'مسائي' }}</span>
                            </span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="flex items-center justify-between sm:justify-start gap-3">
                                <span :class="lecture.status === 'active'
                                        ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                        : 'bg-gray-100 text-gray-600 border-gray-200'"
                                      class="text-xs font-black px-4 py-2 rounded-full border">
                                    {{ lecture.status === 'active' ? '🟢 نشطة' : '🔴 مغلقة' }}
                                </span>
                                <button @click="showDeleteModal = true"
                                        class="sm:hidden p-2 text-red-600 bg-red-50 border border-red-100 rounded-xl">
                                    <Trash2Icon class="w-5 h-5" />
                                </button>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-2">
                                <a :href="route('teacher.lectures.export', lecture.id)"
                                   class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-teal-600 to-emerald-500 hover:from-teal-700 hover:to-emerald-600 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-teal-500/20">
                                    <DownloadIcon class="w-4 h-4" /> تصدير Excel
                                </a>
                                <Link v-if="lecture.status === 'active' && !isLocked" :href="route('teacher.scanner.show', lecture.id)"
                                      class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-teal-500 text-teal-700 text-sm font-black rounded-xl hover:bg-teal-50 transition-all leading-none">
                                    <QrCodeIcon class="w-4 h-4" /> فتح الماسح
                                </Link>
                                <button @click="showDeleteModal = true"
                                        class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-50 transition-all">
                                    <Trash2Icon class="w-4 h-4" /> حذف
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Summary Cards ────────────────────────────────────── -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
                    <button @click="filter = 'all'"
                            class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border text-center hover:shadow-md transition-all col-span-2 md:col-span-1"
                            :class="filter === 'all' ? 'ring-2 ring-teal-400 border-teal-200' : 'border-gray-100'">
                        <div class="text-2xl md:text-3xl font-black text-gray-900">{{ localSummary.total }}</div>
                        <div class="text-[11px] md:text-sm text-gray-500 font-black mt-1 flex items-center justify-center gap-1.5">
                            <UsersIcon class="w-3.5 h-3.5 md:w-4 md:h-4" /> إجمالي الطلاب
                        </div>
                    </button>
                    <button @click="filter = 'present'"
                            class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border text-center hover:shadow-md transition-all"
                            :class="filter === 'present' ? 'ring-2 ring-emerald-400 border-emerald-200' : 'border-gray-100'">
                        <div class="text-2xl md:text-3xl font-black text-emerald-600">{{ localSummary.present }}</div>
                        <div class="text-[11px] md:text-sm text-gray-500 font-black mt-1 flex items-center justify-center gap-1.5">
                            <CheckCircle2Icon class="w-3.5 h-3.5 md:w-4 md:h-4 text-emerald-500" /> الحاضرون
                        </div>
                    </button>
                    <button @click="filter = 'absent'"
                            class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border text-center hover:shadow-md transition-all"
                            :class="filter === 'absent' ? 'ring-2 ring-red-400 border-red-200' : 'border-gray-100'">
                        <div class="text-2xl md:text-3xl font-black text-red-500">{{ localSummary.absent }}</div>
                        <div class="text-[11px] md:text-sm text-gray-500 font-black mt-1 flex items-center justify-center gap-1.5">
                            <XCircleIcon class="w-3.5 h-3.5 md:w-4 md:h-4 text-red-400" /> الغائبون
                        </div>
                    </button>
                </div>

                <!-- ─── Students List ────────────────────────────────────── -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-teal-500 to-emerald-400"></div>

                    <!-- List Header -->
                    <div class="p-4 border-b border-gray-100 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-gradient-to-b from-teal-500 to-emerald-500 rounded-full"></div>
                            <h3 class="text-base font-bold text-gray-900 flex-1">
                                كشف الطلاب
                                <span class="text-sm font-normal text-gray-400 mr-1">
                                    ({{ filteredStudents.length }} من {{ localSummary.total }})
                                </span>
                            </h3>
                        </div>
                        <!-- Search -->
                        <div class="relative">
                            <SearchIcon class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                            <input v-model="rawSearch" type="text"
                                   placeholder="بحث بالاسم أو الرقم الجامعي..."
                                   class="pr-9 pl-3 py-3 text-sm border border-gray-200 rounded-2xl w-full bg-gray-50 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all" />
                        </div>
                    </div>

                    <!-- Manual attendance tip -->
                    <div class="mx-4 mt-3 mb-2 px-3 py-2 bg-amber-50 rounded-xl border border-amber-200 flex items-center gap-2 text-xs text-amber-700">
                        <HandIcon class="w-3.5 h-3.5 flex-shrink-0" />
                        <span>اضغط على زر الحالة لتغييرها يدوياً. التعديل متاح دائماً.</span>
                    </div>

                    <!-- Table Head (Desktop only) -->
                    <div class="hidden md:grid grid-cols-12 gap-0 px-4 py-2 bg-gray-50 border-y border-gray-100 text-xs font-bold text-gray-500">
                        <div class="col-span-1 text-center">#</div>
                        <div class="col-span-5 pr-2">اسم الطالب</div>
                        <div class="col-span-2 text-center">الرقم</div>
                        <div class="col-span-2 text-center">الطريقة</div>
                        <div class="col-span-2 text-center">الحالة</div>
                    </div>

                    <!-- Scrollable Student Rows -->
                    <div class="overflow-y-auto" style="max-height: 560px;">
                        <div v-if="filteredStudents.length === 0"
                             class="py-16 text-center text-gray-400 text-sm">
                            لا توجد نتائج مطابقة للبحث
                        </div>

                        <div v-for="(student, index) in filteredStudents"
                             :key="student.id"
                             class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-0 px-4 py-4 md:py-2.5 border-b border-gray-50 select-none transition-colors"
                             :class="[
                                 loadingIds.has(student.id) ? 'opacity-40 pointer-events-none' : '',
                                 index % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'
                             ]">

                            <!-- Desktop Index -->
                            <div class="hidden md:flex col-span-1 items-center justify-center text-xs font-bold text-gray-400">
                                {{ index + 1 }}
                            </div>

                            <!-- Name & ID (Stacked on Mobile) -->
                            <div class="col-span-8 md:col-span-5 flex flex-col md:flex-row md:items-center pr-1 md:pr-0">
                                <span class="text-sm md:text-sm font-black md:font-semibold text-gray-900 leading-tight">{{ student.name }}</span>
                                <span class="md:hidden text-[10px] text-gray-400 font-mono mt-1" dir="ltr">#{{ student.student_id || '---' }}</span>
                            </div>

                            <!-- Desktop Student ID -->
                            <div class="hidden md:flex col-span-2 items-center justify-center text-xs text-gray-400 font-mono" dir="ltr">
                                {{ student.student_id || '—' }}
                            </div>

                            <!-- Method badge & Status (Actions area on Mobile) -->
                            <div class="col-span-12 md:col-span-4 flex items-center justify-between md:justify-around mt-2 md:mt-0">
                                <!-- Method -->
                                <div class="flex items-center">
                                    <span v-if="student.check_in_method === 'manual'"
                                          class="text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 px-2 py-1 rounded-lg flex items-center gap-1">
                                        <HandIcon class="w-3 h-3" /> يدوي
                                    </span>
                                    <span v-else-if="student.check_in_method === 'qr'"
                                          class="text-[10px] font-bold bg-teal-100 text-teal-700 border border-teal-200 px-2 py-1 rounded-lg flex items-center gap-1">
                                        <QrCodeIcon class="w-3 h-3" /> QR
                                    </span>
                                    <span v-else class="text-[10px] text-gray-400 font-medium italic">بانتظار التحضير...</span>
                                </div>

                                <!-- Status Toggle -->
                                <button v-if="student.status === 'present'"
                                      :disabled="isLocked"
                                      @click.stop="toggleAttendance(student)"
                                      class="flex items-center justify-center gap-2 px-6 md:px-3 py-2.5 md:py-1 bg-emerald-50 hover:bg-emerald-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-emerald-700 border-2 border-emerald-200 md:border text-[11px] md:text-[10px] font-black rounded-xl cursor-pointer min-w-[100px] active:scale-95">
                                    <CheckCircle2Icon class="w-4 h-4 md:w-3 md:h-3" /> حاضـر
                                </button>
                                <button v-else
                                      :disabled="isLocked"
                                      @click.stop="toggleAttendance(student)"
                                      class="flex items-center justify-center gap-2 px-6 md:px-3 py-2.5 md:py-1 bg-red-50 hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-red-600 border-2 border-red-200 md:border text-[11px] md:text-[10px] font-black rounded-xl cursor-pointer min-w-[100px] active:scale-95">
                                    <XCircleIcon class="w-4 h-4 md:w-3 md:h-3" /> غائـب
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom summary bar -->
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-sm">
                        <span class="text-gray-500">إجمالي: <strong class="text-gray-900">{{ localSummary.total }}</strong> طالب</span>
                        <div class="flex gap-4">
                            <span class="text-emerald-700 font-bold">✔ حاضر: {{ localSummary.present }}</span>
                            <span class="text-red-600 font-bold">✘ غائب: {{ localSummary.absent }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>

    <!-- ─── Delete Confirmation Modal ─────────────────────────────────────── -->
    <Teleport to="body">
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                        <Trash2Icon class="w-6 h-6 text-red-500" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">حذف المحاضرة</h3>
                        <p class="text-sm text-gray-500">هل أنت متأكد؟ لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                </div>
                <div class="bg-red-50 rounded-xl p-3 mb-5 border border-red-100">
                    <p class="text-sm font-bold text-red-700">{{ lecture.title }}</p>
                    <p class="text-xs text-red-500 mt-0.5">{{ lecture.subject?.name }} — {{ lecture.group?.name }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false"
                            class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-colors">
                        إلغاء
                    </button>
                    <button @click="deleteLecture" :disabled="deleting"
                            class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <Trash2Icon class="w-4 h-4" />
                        {{ deleting ? 'جاري الحذف...' : 'نعم، احذف المحاضرة' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
