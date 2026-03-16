<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { HeartIcon, GraduationCapIcon, UsersIcon, CodeIcon } from 'lucide-vue-next';

</script>

<template>
    <Head title="عن التطبيق" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-6 bg-gradient-to-b from-white to-emerald-50/30">
        <!-- Back Button -->
        <div class="w-full max-w-2xl mb-8">
            <Link :href="'/'" class="inline-flex items-center text-emerald-600 hover:text-emerald-700 font-bold transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                الرجوع للرئيسية
            </Link>
        </div>

        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl shadow-emerald-200/50 border border-white overflow-hidden">
            <!-- Header Section with Gradient -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-8 flex flex-col items-center">
                <div class="w-32 h-32 bg-white/20 backdrop-blur-md rounded-full p-1 border-2 border-white/50 mb-4 shadow-xl">
                    <img src="/img/logo_dept.jpg" alt="شعار القسم" class="w-full h-full object-contain rounded-full bg-white" />
                </div>
                <h1 class="text-2xl font-black text-white text-center leading-relaxed">
                    قسم هندسة تقنيات الحاسوب
                </h1>
                <p class="text-emerald-50 font-medium text-center text-sm mt-1">
                    كلية التقنيات الهندسية - جامعة بلاد الرافدين
                </p>
            </div>

            <!-- Content Area -->
            <div class="p-8">
                <div class="flex flex-col items-center mb-12">
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <span class="text-gray-600 font-bold text-lg">تم صنع بـ</span>
                        <HeartIcon class="w-8 h-8 text-emerald-600 fill-emerald-600 animate-pulse" />
                        <span class="text-gray-600 font-bold text-lg">في</span>
                    </div>
                    <div class="space-y-1 text-center">
                        <p class="text-xl font-black text-gray-800">جامعة بلاد الرافدين</p>
                        <p class="text-lg font-bold text-emerald-600">كلية التقنيات الهندسية</p>
                        <p class="text-md font-bold text-gray-600">قسم هندسة تقنيات الحاسوب</p>
                    </div>
                </div>

                <!-- Students Section -->
                <div class="relative">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-emerald-100 rounded-lg">
                            <UsersIcon class="w-6 h-6 text-emerald-600" />
                        </div>
                        <h2 class="text-xl font-black text-gray-900">الطلاب المطورون</h2>
                    </div>

                    <div class="grid gap-6">
                        <!-- Student 1 -->
                        <div class="group flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/50 transition-all duration-300">
                            <div class="w-16 h-16 rounded-xl overflow-hidden border-2 border-white shadow-md ml-4 group-hover:scale-110 transition-transform">
                                <img src="/img/mustafa_samir.jpg" alt="مصطفى سمير غني" class="w-full h-full object-cover" />
                            </div>
                            <div>
                                <h3 class="font-black text-gray-800 text-lg">مصطفى سمير غني</h3>
                                <p class="text-sm text-gray-500 font-medium tracking-wide">مطوّر برمجيات</p>
                            </div>
                        </div>

                        <!-- Student 2 -->
                        <div class="group flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/50 transition-all duration-300">
                            <div class="w-16 h-16 rounded-xl overflow-hidden border-2 border-white shadow-md ml-4 group-hover:scale-110 transition-transform">
                                <img src="/img/mustafa_qasim.jpg" alt="مصطفى قاسم نعمان" class="w-full h-full object-cover" />
                            </div>
                            <div>
                                <h3 class="font-black text-gray-800 text-lg">مصطفى قاسم نعمان</h3>
                                <p class="text-sm text-gray-500 font-medium tracking-wide">مطوّر برمجيات</p>
                            </div>
                        </div>

                        <!-- Student 3 -->
                        <div class="group flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/50 transition-all duration-300">
                            <div class="w-16 h-16 rounded-xl overflow-hidden border-2 border-white shadow-md ml-4 group-hover:scale-110 transition-transform">
                                <img src="/img/ather_harith.jpg" alt="اثير حارث خالد" class="w-full h-full object-cover" />
                            </div>
                            <div>
                                <h3 class="font-black text-gray-800 text-lg">اثير حارث خالد</h3>
                                <p class="text-sm text-gray-500 font-medium tracking-wide">مطوّر برمجيات</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400 font-medium">© 2026 جميع الحقوق محفوظة - قسم هندسة تقنيات الحاسوب</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');

template {
    font-family: 'Cairo', sans-serif;
}
</style>
