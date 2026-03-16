<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { LogInIcon, ShieldCheckIcon, CpuIcon, SmartphoneIcon, GlobeIcon, ChevronLeftIcon, SparklesIcon } from 'lucide-vue-next';

defineProps<{
    canLogin?: boolean;
}>();
</script>

<template>
    <Head title="النظام الأكاديمي الذكي - كلية التقنيات الهندسية" />

    <div class="min-h-screen bg-[#020617] text-slate-200 font-sans selection:bg-emerald-500/30 selection:text-white overflow-x-hidden rtl selection:backdrop-blur-sm" dir="rtl">
        
        <!-- Premium Hero Background -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-950/40 to-slate-950/90 z-10"></div>
            <img 
                src="/img/welcome_hero.png" 
                class="absolute inset-0 w-full h-full object-cover opacity-40 scale-110 animate-pulse-slow"
                alt="Background"
            />
            
            <!-- Animated Light Blobs -->
            <div class="absolute top-[10%] left-[15%] w-[30rem] h-[30rem] bg-emerald-600/20 rounded-full blur-[120px] animate-blob"></div>
            <div class="absolute bottom-[20%] right-[10%] w-[25rem] h-[25rem] bg-teal-600/20 rounded-full blur-[100px] animate-blob animation-delay-2000"></div>
        </div>

        <div class="relative z-20 flex flex-col items-center px-6 py-12 lg:py-24 min-h-screen justify-center">
            
            <!-- Floating Logo Card -->
            <div class="mb-12 relative group animate-fade-in-down">
                <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-[2.5rem] blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative w-28 h-28 bg-slate-900 border border-slate-800 rounded-[2.5rem] shadow-2xl flex items-center justify-center transform group-hover:scale-105 transition-all duration-500">
                   <ShieldCheckIcon class="w-14 h-14 text-emerald-400 drop-shadow-[0_0_15px_rgba(52,211,153,0.5)]" />
                </div>
            </div>

            <div class="max-w-4xl w-full text-center space-y-12">
                <!-- Main Title with Glow -->
                <div class="space-y-6 animate-fade-in-up">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-emerald-500/20 bg-emerald-500/5 backdrop-blur-md mb-4">
                        <SparklesIcon class="w-4 h-4 text-emerald-400" />
                        <span class="text-xs font-bold text-emerald-400 tracking-widest uppercase">الإصدار الذكي 2026</span>
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-black text-white leading-[1.1] tracking-tight">
                        النظام الأكاديمي <span class="text-transparent bg-clip-text bg-gradient-to-l from-emerald-400 via-teal-400 to-emerald-500 drop-shadow-sm">السيادي</span>
                        <br/>
                        <span class="lg:text-6xl text-4xl opacity-90">لإدارة الحضور الذكي</span>
                    </h1>
                    <p class="text-xl text-emerald-500/90 font-bold tracking-[0.2em] flex items-center justify-center gap-4">
                        <span class="w-8 h-px bg-emerald-500/30"></span>
                        جامعة بلاد الرافدين - كلية التقنيات الهندسية
                        <span class="w-8 h-px bg-emerald-500/30"></span>
                    </p>
                </div>

                <!-- Premium Narrative Card -->
                <div class="relative group animate-fade-in-up animation-delay-500">
                    <div class="absolute -inset-px bg-gradient-to-r from-emerald-500/20 via-teal-500/10 to-emerald-500/20 rounded-[2rem] blur-sm"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-2xl border border-white/10 rounded-[2rem] p-8 lg:p-14 shadow-2xl text-right overflow-hidden">
                        <!-- Decorative element -->
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
                        
                        <p class="text-lg lg:text-2xl text-slate-300 font-medium text-justify leading-[1.9] lg:leading-[2.3] relative z-10">
                            نظام تسجيل الحضور والغياب الذكي هو منصة أكاديمية متكاملة صممت خصيصاً لتلبية احتياجات كلية التقنيات الهندسية في جامعة بلاد الرافدين حيث يوفر حلولاً تقنية متقدمة لضبط العملية التدريسية من خلال تقديم تجربة رقمية فرصة تعتمد على تقنيات الـ QR الديناميكي التي تمنع التلاعب وتضمن دقة البيانات المسجلة وهو ما يتيح للأساتذة إدارة محاضراتهم بمرونة عالية تشمل فتح المحاضرة بضغطة زر واحدة ومتابعة الحضور الآني للطلاب ومراجعة السجلات التاريخية بدقة متناهية كما يضم النظام لوحة تحكم إدارية شاملة تمنح رئاسة القسم صلاحيات واسعة لإدارة بيانات المدرسين والطلبة والمواد الدراسية بفعالية قصوى مع نظام إنذرات ذكي يقوم بتحليل غيابات الطلاب تلقائياً وتنبيههم قبل وصولهم لمرحلة الخطر الأكاديمي بالإضافة إلى توفير واجهات برمجية متطورة تتيح ربط النظام بتطبيقات الهواتف الذكية لضمان سهولة الوصول وسرعة الاستجابة ومن المميزات الرائدة أيضاً دعمه الكامل للعمل كتطبيق ويب متقدم يتميز بالاستقرار والسرعة الفائقة وتوافقية تامة مع مختلف الأجهزة المحمولة والمتصفحات مما يجعله نظاماً سيادياً يعزز من جودة التعليم ويقلل من الأعباء الإدارية الروتينية بأسلوب عصري وأنيق.
                        </p>
                    </div>
                </div>

                <!-- Elevated Action Button -->
                <div class="flex flex-col items-center gap-10 pt-6 animate-fade-in-up animation-delay-1000">
                    <Link
                        :href="route('login')"
                        class="group relative inline-flex items-center justify-center px-16 py-6 bg-white text-slate-950 font-black text-2xl rounded-2xl transition-all hover:scale-105 active:scale-95 overflow-hidden shadow-[0_0_40px_rgba(52,211,153,0.3)] hover:shadow-[0_0_60px_rgba(52,211,153,0.5)]"
                    >
                        <span class="relative z-10 flex items-center gap-4">
                            <LogInIcon class="w-8 h-8" />
                            دخول بوابة النظام
                            <ChevronLeftIcon class="w-6 h-6 group-hover:-translate-x-2 transition-transform duration-300" />
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <span class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 pointer-events-none group-hover:text-white transition-colors duration-500 z-0"></span>
                    </Link>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-12 gap-y-6 text-slate-500 font-bold uppercase tracking-widest text-xs">
                        <div class="flex items-center gap-3 hover:text-emerald-400 transition-colors cursor-default group">
                             <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center group-hover:bg-emerald-500/10 transition-colors">
                                <CpuIcon class="w-4 h-4" />
                             </div>
                             <span>الذكاء الاصطناعي</span>
                        </div>
                        <div class="flex items-center gap-3 hover:text-emerald-400 transition-colors cursor-default group">
                             <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center group-hover:bg-emerald-500/10 transition-colors">
                                <SmartphoneIcon class="w-4 h-4" />
                             </div>
                             <span>تكامل الموبايل</span>
                        </div>
                        <div class="flex items-center gap-3 hover:text-emerald-400 transition-colors cursor-default group">
                             <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center group-hover:bg-emerald-500/10 transition-colors">
                                <GlobeIcon class="w-4 h-4" />
                             </div>
                             <span>تقنية PWA</span>
                        </div>
                        <div class="flex items-center gap-3 hover:text-emerald-400 transition-colors cursor-default group">
                             <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center group-hover:bg-emerald-500/10 transition-colors">
                                <ShieldCheckIcon class="w-4 h-4" />
                             </div>
                             <span>تشفير سيادي</span>
                        </div>
                    </div>
                </div>

                <!-- Footer with line -->
                <footer class="pt-16 pb-8 space-y-4">
                    <div class="w-24 h-px bg-gradient-to-r from-transparent via-emerald-500/50 to-transparent mx-auto"></div>
                    <p class="text-slate-600 text-[13px] font-bold tracking-[0.3em] uppercase">
                        © 2026 جميع الحقوق محفوظة - قسم هندسة تقنيات الحاسوب
                    </p>
                </footer>
            </div>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap');

.font-sans {
    font-family: 'Cairo', 'Tajawal', sans-serif !important;
}

@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

@keyframes pulse-slow {
    0%, 100% { transform: scale(1.1); opacity: 0.4; }
    50% { transform: scale(1.15); opacity: 0.5; }
}

.animate-pulse-slow {
    animation: pulse-slow 15s ease-in-out infinite;
}

@keyframes fade-in-down {
    0% { opacity: 0; transform: translateY(-20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes fade-in-up {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-down {
    animation: fade-in-down 1s ease-out forwards;
}

.animate-fade-in-up {
    animation: fade-in-up 1s ease-out forwards;
}

.animation-delay-500 {
    animation-delay: 0.5s;
}

.animation-delay-1000 {
    animation-delay: 1s;
}

.rtl {
    direction: rtl;
}
</style>
