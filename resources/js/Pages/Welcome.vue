<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { LogInIcon, ShieldCheckIcon, CpuIcon, SmartphoneIcon, GlobeIcon, ChevronLeftIcon } from 'lucide-vue-next';

defineProps<{
    canLogin?: boolean;
}>();
</script>

<template>
    <Head title="نظام تسجيل الحضور - كلية التقنيات الهندسية" />

    <div class="min-h-screen bg-[#fdfdfd] text-gray-900 font-sans selection:bg-emerald-100 selection:text-emerald-900 overflow-x-hidden rtl" dir="rtl">
        <!-- Background Elements -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-emerald-50 rounded-full blur-[120px] opacity-60"></div>
            <div class="absolute top-[20%] -right-[5%] w-[30%] h-[30%] bg-teal-50 rounded-full blur-[100px] opacity-50"></div>
            <div class="absolute -bottom-[5%] left-[20%] w-[25%] h-[25%] bg-emerald-50 rounded-full blur-[80px] opacity-40"></div>
        </div>

        <div class="relative z-10 flex flex-col items-center px-6 py-12 lg:py-24">
            <!-- Header/Logo Area -->
            <div class="mb-12 animate-in fade-in slide-in-from-top-4 duration-1000">
                <div class="w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl shadow-2xl shadow-emerald-500/20 flex items-center justify-center transform rotate-3 hover:rotate-0 transition-transform duration-500">
                   <ShieldCheckIcon class="w-12 h-12 text-white" />
                </div>
            </div>

            <div class="max-w-4xl w-full text-center space-y-10">
                <!-- Main Title -->
                <div class="space-y-4">
                    <h1 class="text-4xl lg:text-6xl font-black text-gray-900 leading-tight tracking-tight">
                        النظام الأكاديمي الذكي 
                        <span class="block text-transparent bg-clip-text bg-gradient-to-l from-emerald-600 to-teal-600">
                            لإدارة الحضور والغياب
                        </span>
                    </h1>
                    <p class="text-lg text-emerald-700 font-bold tracking-widest uppercase">جامعة بلاد الرافدين - كلية التقنيات الهندسية</p>
                </div>

                <!-- Narrative Features Section -->
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-3xl blur opacity-5 group-hover:opacity-10 transition-opacity duration-500"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-white/50 rounded-3xl p-8 lg:p-12 shadow-xl shadow-gray-200/50 text-right leading-[1.8] lg:leading-[2.2]">
                        <p class="text-lg lg:text-xl text-gray-700 font-medium text-justify">
                            نظام تسجيل الحضور والغياب الذكي هو منصة أكاديمية متكاملة صممت خصيصاً لتلبية احتياجات كلية التقنيات الهندسية في جامعة بلاد الرافدين حيث يوفر حلولاً تقنية متقدمة لضبط العملية التدريسية من خلال تقديم تجربة رقمية فرصة تعتمد على تقنيات الـ QR الديناميكي التي تمنع التلاعب وتضمن دقة البيانات المسجلة وهو ما يتيح للأساتذة إدارة محاضراتهم بمرونة عالية تشمل فتح المحاضرة بضغطة زر واحدة ومتابعة الحضور الآني للطلاب ومراجعة السجلات التاريخية بدقة متناهية كما يضم النظام لوحة تحكم إدارية شاملة تمنح رئاسة القسم صلاحيات واسعة لإدارة بيانات المدرسين والطلبة والمواد الدراسية بفعالية قصوى مع نظام إنذرات ذكي يقوم بتحليل غيابات الطلاب تلقائياً وتنبيههم قبل وصولهم لمرحلة الخطر الأكاديمي بالإضافة إلى توفير واجهات برمجية متطورة تتيح ربط النظام بتطبيقات الهواتف الذكية لضمان سهولة الوصول وسرعة الاستجابة ومن المميزات الرائدة أيضاً دعمه الكامل للعمل كتطبيق ويب متقدم يتميز بالاستقرار والسرعة الفائقة وتوافقية تامة مع مختلف الأجهزة المحمولة والمتصفحات مما يجعله نظاماً سيادياً يعزز من جودة التعليم ويقلل من الأعباء الإدارية الروتينية بأسلوب عصري وأنيق.
                        </p>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex flex-col items-center gap-6 pt-4">
                    <Link
                        :href="route('login')"
                        class="group relative inline-flex items-center justify-center px-10 py-5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-xl rounded-2xl shadow-2xl shadow-emerald-500/40 hover:shadow-emerald-500/60 transition-all active:scale-95 overflow-hidden"
                    >
                        <span class="relative z-10 flex items-center gap-3">
                            <LogInIcon class="w-6 h-6" />
                            الدخول للمنظومة
                            <ChevronLeftIcon class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                        </span>
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </Link>
                    
                    <div class="flex items-center gap-8 text-gray-400 font-medium">
                        <div class="flex items-center gap-2">
                             <CpuIcon class="w-4 h-4" />
                             <span>تقنية الذكاء</span>
                        </div>
                        <div class="flex items-center gap-2">
                             <SmartphoneIcon class="w-4 h-4" />
                             <span>تطبيق الموبايل</span>
                        </div>
                        <div class="flex items-center gap-2">
                             <GlobeIcon class="w-4 h-4" />
                             <span>دعم PWA</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Info -->
                <footer class="pt-12 text-gray-400 text-sm font-medium border-t border-gray-100">
                    <p>© 2026 جميع الحقوق محفوظة - قسم هندسة تقنيات الحاسوب</p>
                </footer>
            </div>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap');

.font-sans {
    font-family: 'Tajawal', sans-serif !important;
}

.rtl {
    direction: rtl;
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}
</style>
