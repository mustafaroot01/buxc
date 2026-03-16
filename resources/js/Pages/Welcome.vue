<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { LogInIcon, ShieldCheckIcon, CpuIcon, SmartphoneIcon, GlobeIcon, ChevronLeftIcon, SparklesIcon } from 'lucide-vue-next';

defineProps<{
    canLogin?: boolean;
}>();
</script>

<template>
    <Head title="نظام تسجيل الحضور والغياب الذكي" />

    <div class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-emerald-100 selection:text-emerald-900 overflow-x-hidden rtl" dir="rtl">
        
        <!-- Animated Soft Background -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-emerald-100/40 rounded-full blur-[120px] animate-pulse"></div>
            <div class="absolute top-[20%] -right-[5%] w-[40%] h-[40%] bg-teal-100/40 rounded-full blur-[100px] animate-pulse delay-700"></div>
            <div class="absolute bottom-[10%] left-[10%] w-[30%] h-[30%] bg-emerald-100/30 rounded-full blur-[80px] animate-pulse delay-1000"></div>
        </div>

        <div class="relative z-10 flex flex-col items-center px-6 py-12 lg:py-24 min-h-screen justify-center">
            
            <!-- Elegant Header Card -->
            <div class="mb-10 animate-in fade-in slide-in-from-top-4 duration-1000">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-xl shadow-emerald-500/20 flex items-center justify-center transform hover:scale-110 transition-transform duration-500 cursor-default">
                   <ShieldCheckIcon class="w-10 h-10 text-white" />
                </div>
            </div>

            <div class="max-w-4xl w-full text-center space-y-12">
                <!-- Main Header -->
                <div class="space-y-4">
                    <h1 class="text-4xl lg:text-7xl font-black text-slate-900 leading-[1.1] tracking-tight">
                        نظام إدارة الحضور والغياب 
                        <span class="block text-emerald-600 mt-2">بتقنيات الـ QR الذكية</span>
                    </h1>
                    <div class="h-1.5 w-32 bg-gradient-to-l from-emerald-500 to-teal-500 mx-auto rounded-full"></div>
                    <p class="text-xl text-slate-500 font-bold tracking-widest">
                        جامعة بلاد الرافدين — كلية التقنيات الهندسية
                    </p>
                </div>

                <!-- Features Narrative -->
                <div class="relative max-w-5xl mx-auto">
                    <div class="absolute -inset-4 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 rounded-[3rem] blur-2xl"></div>
                    <div class="relative bg-white/60 backdrop-blur-2xl border border-white/80 rounded-[3rem] p-10 lg:p-16 shadow-2xl shadow-emerald-900/5 text-right space-y-8">
                        
                        <p class="text-xl lg:text-3xl text-slate-800 font-bold leading-relaxed text-justify">
                            نظام تسجيل الحضور والغياب الذكي هو منصة أكاديمية متكاملة صممت خصيصاً لتلبية احتياجات كلية التقنيات الهندسية في جامعة بلاد الرافدين حيث يوفر حلولاً تقنية متقدمة لضبط العملية التدريسية من خلال تقديم تجربة رقمية فريدة تعتمد على تقنيات الـ QR الديناميكي التي تمنع التلاعب وتضمن دقة البيانات المسجلة.
                        </p>

                        <p class="text-lg lg:text-2xl text-slate-600 font-medium leading-relaxed text-justify">
                            يتيح النظام للأساتذة إدارة محاضراتهم بمرونة عالية تشمل فتح المحاضرة بضغطة زر واحدة ومتابعة الحضور الآني للطلاب ومراجعة السجلات التاريخية بدقة متناهية كما يضم لوحة تحكم إدارية شاملة تمنح رئاسة القسم صلاحيات واسعة لإدارة بيانات المدرسين والطلبة والمواد الدراسية بفعالية قصوى.
                        </p>

                        <p class="text-lg lg:text-2xl text-slate-600 font-medium leading-relaxed text-justify border-r-4 border-emerald-500 pr-6">
                            يتميز النظام أيضاً بنظام إنذارات ذكي يقوم بتحليل غيابات الطلاب تلقائياً وتنبيههم قبل وصولهم لمرحلة الخطر الأكاديمي بالإضافة إلى توفير واجهات برمجية متطورة ودعمه الكامل للعمل كتطبيق ويب متقدم يتميز بالاستقرار والسرعة الفائقة وتوافقية تامة مع مختلف الأجهزة المحمولة مما يجعله نظاماً سيادياً يعزز من جودة التعليم.
                        </p>
                    </div>
                </div>

                <!-- Primary Action -->
                <div class="flex flex-col items-center gap-12 pt-8">
                    <Link
                        :href="route('login')"
                        class="group relative inline-flex items-center justify-center px-16 py-6 bg-slate-900 text-white font-black text-2xl rounded-[2rem] shadow-2xl shadow-slate-900/20 hover:shadow-emerald-500/40 transition-all hover:-translate-y-1 active:scale-95 overflow-hidden"
                    >
                        <span class="relative z-10 flex items-center gap-4">
                            <LogInIcon class="w-8 h-8" />
                            دخول بوابة النظام
                            <ChevronLeftIcon class="w-6 h-6 group-hover:-translate-x-2 transition-transform duration-300" />
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-teal-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </Link>
                    
                    <!-- Modern Tech Badges -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-6 text-slate-400 font-bold text-xs">
                        <div class="flex items-center gap-3 hover:text-emerald-600 transition-colors cursor-default group">
                             <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                <SmartphoneIcon class="w-4 h-4 text-emerald-600" />
                             </div>
                             <span>تكامل الموبايل</span>
                        </div>
                        <div class="flex items-center gap-3 hover:text-emerald-600 transition-colors cursor-default group">
                             <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                <GlobeIcon class="w-4 h-4 text-emerald-600" />
                             </div>
                             <span>دعم PWA</span>
                        </div>
                        <div class="flex items-center gap-3 hover:text-emerald-600 transition-colors cursor-default group">
                             <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                <ShieldCheckIcon class="w-4 h-4 text-emerald-600" />
                             </div>
                             <span>نظام سيادي</span>
                        </div>
                    </div>
                </div>

                <!-- Minimal Footer -->
                <footer class="pt-20 pb-8 border-t border-slate-100 flex flex-col items-center gap-4">
                    <div class="w-12 h-1 bg-gradient-to-l from-emerald-500 to-teal-500 rounded-full opacity-30"></div>
                    <p class="text-slate-400 text-sm font-bold tracking-[0.2em] uppercase">
                        © 2026 جميع الحقوق محفوظة - قسم هندسة تقنيات الحاسوب
                    </p>
                </footer>
            </div>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap');

.font-sans {
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
}

.rtl {
    direction: rtl;
}

@keyframes pulse {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.1); }
}

.animate-pulse {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-700 { animation-delay: 0.7s; }
.delay-1000 { animation-delay: 1s; }
</style>
