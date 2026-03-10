<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UsersIcon, SaveIcon, ArrowRightIcon } from 'lucide-vue-next';

const form = useForm({
    teacher_external_id: '',
    department: '',
    full_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    academic_title: '',
    degree: '',
    phone_number: '',
    gender: '',
    photo: null as File | null,
});

const submit = () => {
    form.post(route('admin.teachers.store'));
};
</script>

<template>
    <Head title="إضافة أستاذ جديد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('admin.teachers.index')" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
                    <ArrowRightIcon class="w-6 h-6" />
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight flex items-center pr-4 border-r-2 border-gray-200">
                    <UsersIcon class="w-6 h-6 ml-2 text-emerald-600" />
                    إضافة أستاذ جديد
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="full_name" class="block text-sm font-bold text-gray-700 mb-2">الاسم الكامل <span class="text-red-500">*</span></label>
                                <input id="full_name" type="text" v-model="form.full_name" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="مثال: أ.د. محمد أحمد العباسي" required>
                                <p v-if="form.errors.full_name" class="mt-2 text-sm text-red-600">{{ form.errors.full_name }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="teacher_external_id" class="block text-sm font-bold text-gray-700 mb-2">الرقم الوظيفي / الجامعي</label>
                                    <input id="teacher_external_id" type="text" v-model="form.teacher_external_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="مثال: 20250001">
                                    <p v-if="form.errors.teacher_external_id" class="mt-2 text-sm text-red-600">{{ form.errors.teacher_external_id }}</p>
                                </div>
                                <div>
                                    <label for="department" class="block text-sm font-bold text-gray-700 mb-2">القسم / الكلية</label>
                                    <input id="department" type="text" v-model="form.department" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="مثال: قسم علوم الحاسوب">
                                    <p v-if="form.errors.department" class="mt-2 text-sm text-red-600">{{ form.errors.department }}</p>
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني <span class="text-red-500">*</span></label>
                                <input id="email" type="email" v-model="form.email" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="teacher@example.com" required>
                                <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="academic_title" class="block text-sm font-bold text-gray-700 mb-2">اللقب العلمي</label>
                                    <select id="academic_title" v-model="form.academic_title" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors">
                                        <option value="">اختر اللقب العلمي</option>
                                        <option value="Professor">أستاذ (Professor)</option>
                                        <option value="Assistant Professor">أستاذ مساعد (Asst. Prof)</option>
                                        <option value="Lecturer">مدرس (Lecturer)</option>
                                        <option value="Assistant Lecturer">مدرس مساعد (Asst. Lecturer)</option>
                                    </select>
                                    <p v-if="form.errors.academic_title" class="mt-2 text-sm text-red-600">{{ form.errors.academic_title }}</p>
                                </div>
                                <div>
                                    <label for="degree" class="block text-sm font-bold text-gray-700 mb-2">الشهادة الأكاديمية</label>
                                    <select id="degree" v-model="form.degree" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors">
                                        <option value="">اختر الشهادة الأكاديمية</option>
                                        <option value="Ph.D">دكتوراه (Ph.D)</option>
                                        <option value="Master">ماجستير (Master's)</option>
                                        <option value="Bachelor">بكالوريوس (Bachelor's)</option>
                                    </select>
                                    <p v-if="form.errors.degree" class="mt-2 text-sm text-red-600">{{ form.errors.degree }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="phone_number" class="block text-sm font-bold text-gray-700 mb-2">رقم الهاتف</label>
                                    <input id="phone_number" type="tel" v-model="form.phone_number" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="مثال: 07700000000">
                                    <p v-if="form.errors.phone_number" class="mt-2 text-sm text-red-600">{{ form.errors.phone_number }}</p>
                                </div>
                                <div>
                                    <label for="gender" class="block text-sm font-bold text-gray-700 mb-2">الجنس</label>
                                    <select id="gender" v-model="form.gender" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors">
                                        <option value="">اختر الجنس</option>
                                        <option value="male">ذكر</option>
                                        <option value="female">أنثى</option>
                                    </select>
                                    <p v-if="form.errors.gender" class="mt-2 text-sm text-red-600">{{ form.errors.gender }}</p>
                                </div>
                            </div>

                            <div>
                                <label for="photo" class="block text-sm font-bold text-gray-700 mb-2">صورة الملف الشخصي</label>
                                <input id="photo" type="file" @input="form.photo = ($event.target as HTMLInputElement).files?.[0] || null" accept="image/*" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 transition-colors cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                <p v-if="form.errors.photo" class="mt-2 text-sm text-red-600">{{ form.errors.photo }}</p>
                                <p class="mt-1 text-xs text-gray-500">مسموح بالصور بصيغة JPG، PNG، GIF وبحجم لا يتجاوز 2MB.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">كلمة المرور <span class="text-red-500">*</span></label>
                                    <input id="password" type="password" v-model="form.password" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="8 أحرف على الأقل" required>
                                    <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">تأكيد كلمة المرور <span class="text-red-500">*</span></label>
                                    <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="إعادة كتابة كلمة المرور" required>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                                <Link :href="route('admin.teachers.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-all duration-200 ml-3">
                                    إلغاء
                                </Link>
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-lg shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:from-emerald-700 hover:to-teal-700 focus:outline-none transition-all duration-200 disabled:opacity-50">
                                    <SaveIcon class="w-5 h-5 ml-2" />
                                    حفظ البيانات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
