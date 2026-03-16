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

        <div class="relative z-10 flex flex-col items-center px-6 py-12 lg:py-24 space-y-24">
            
            <!-- Hero Section -->
            <div class="flex flex-col items-center min-h-[70vh] justify-center text-center space-y-12 animate-in fade-in duration-1000">
                <div class="mb-6">
                    <div class="w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-[2rem] shadow-2xl shadow-emerald-500/20 flex items-center justify-center transform hover:rotate-6 transition-transform duration-500 cursor-default">
                       <ShieldCheckIcon class="w-12 h-12 text-white" />
                    </div>
                </div>

                <div class="space-y-6">
                    <h1 class="text-5xl lg:text-8xl font-black text-slate-900 leading-[1.1] tracking-tight">
                        نظام إدارة الحضور والغياب 
                        <span class="block text-emerald-600 mt-2">بتقنيات الـ QR الذكية</span>
                    </h1>
                    <div class="h-2 w-48 bg-gradient-to-l from-emerald-500 to-teal-500 mx-auto rounded-full"></div>
                    <p class="text-2xl text-slate-500 font-bold tracking-widest">
                        جامعة بلاد الرافدين — كلية التقنيات الهندسية
                    </p>
                </div>

                <div class="flex flex-col items-center gap-12">
                    <Link
                        :href="route('login')"
                        class="group relative inline-flex items-center justify-center px-20 py-7 bg-slate-900 text-white font-black text-3xl rounded-[2.5rem] shadow-2xl shadow-slate-900/30 hover:shadow-emerald-500/40 transition-all hover:-translate-y-2 active:scale-95 overflow-hidden"
                    >
                        <span class="relative z-10 flex items-center gap-4 text-center">
                            <LogInIcon class="w-10 h-10" />
                            دخول منظومة الحضور
                            <ChevronLeftIcon class="w-7 h-7 group-hover:-translate-x-3 transition-transform duration-300" />
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-teal-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </Link>
                </div>
            </div>

            <!-- Detailed Features Narrative (Multi-Section) -->
            <div class="max-w-6xl w-full space-y-24">
                
                <!-- Section 1: Core System & Intelligent QR -->
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div class="space-y-8 text-right order-2 lg:order-1">
                        <div class="inline-flex items-center gap-3 px-5 py-2 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 font-bold text-sm">
                            <SparklesIcon class="w-4 h-4" />
                            الاحترافية الرقمية
                        </div>
                        <h2 class="text-4xl lg:text-5xl font-black text-slate-900 leading-tight">المنصة الأكاديمية السيادية والـ QR الديناميكي</h2>
                        <p class="text-xl lg:text-2xl text-slate-600 leading-relaxed text-justify font-medium">
                            نظام تسجيل الحضور والغياب الذكي هو منصة أكاديمية متكاملة صممت خصيصاً لتلبية احتياجات قسم هندسة تقنيات الحاسوب حيث يوفر حلولاً تقنية متقدمة لضبط العملية التدريسية من خلال تقديم تجربة رقمية فريدة تعتمد على تقنيات الـ QR الديناميكي التي تتغير دورياً لمنع أي محاولة للتلاعب أو تسجيل الحضور عن بُعد صمنا هذه التقنية لضمان دقة البيانات المسجلة بنسبة مائة بالمائة وتوفير أقصى درجات الموثوقية للهيئة التدريسية ورئاسة القسم.
                        </p>
                    </div>
                    <div class="relative order-1 lg:order-2">
                        <div class="absolute inset-0 bg-emerald-500/10 blur-[80px] rounded-full"></div>
                        <div class="relative bg-white/40 backdrop-blur-3xl border border-white p-4 rounded-[3rem] shadow-2xl overflow-hidden aspect-square flex items-center justify-center">
                             <GlobeIcon class="w-48 h-48 text-emerald-500/50 absolute -top-10 -left-10" />
                             <ShieldCheckIcon class="w-40 h-40 text-emerald-600" />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Mobile App & Android Support -->
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div class="relative">
                        <div class="absolute inset-0 bg-teal-500/10 blur-[80px] rounded-full"></div>
                        <div class="relative bg-white/40 backdrop-blur-3xl border border-white p-4 rounded-[3rem] shadow-2xl overflow-hidden aspect-square flex items-center justify-center">
                             <SmartphoneIcon class="w-40 h-40 text-teal-600" />
                             <div class="absolute bottom-10 right-10 bg-emerald-500 text-white px-6 py-2 rounded-full font-bold shadow-lg">متوفر للأندرويد</div>
                        </div>
                    </div>
                    <div class="space-y-8 text-right">
                        <div class="inline-flex items-center gap-3 px-5 py-2 rounded-full border border-teal-200 bg-teal-50 text-teal-700 font-bold text-sm">
                            <SmartphoneIcon class="w-4 h-4" />
                            تكامل الموبايل
                        </div>
                        <h2 class="text-4xl lg:text-5xl font-black text-slate-900 leading-tight">تطبيق الموبايل وتجربة المستخدم السلسة</h2>
                        <p class="text-xl lg:text-2xl text-slate-600 leading-relaxed text-justify font-medium">
                            نوفر دعماً كاملاً للهواتف الذكية من خلال تطبيق موبايل متخصص متوفر رسمياً لأجهزة الأندرويد يتيح للأساتذة إدارة محاضراتهم من أي مكان داخل القاعة الدراسية يتميز التطبيق بواجهة مستخدم بديهية وسريعة جداً تسمح بمسح رموز الـ QR بسرعة فائقة والتعامل مع أعداد كبيرة من الطلاب في ثوانٍ معدودة مما يقلل من هدر الوقت الأكاديمي ويجعل عملية تسجيل الحضور جزءاً انسيابياً من المحاضرة.
                        </p>
                    </div>
                </div>

                <!-- Section 3: Advanced Synchronization -->
                <div class="relative group">
                    <div class="absolute -inset-4 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 rounded-[4rem] blur-2xl"></div>
                    <div class="relative bg-white/60 backdrop-blur-3xl border border-white/80 rounded-[4rem] p-12 lg:p-20 shadow-2xl shadow-emerald-900/5 text-right space-y-10">
                        <div class="inline-flex items-center gap-3 px-5 py-2 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 font-bold text-sm">
                            <CpuIcon class="w-4 h-4" />
                            المزامنة الذكية
                        </div>
                        <h2 class="text-4xl lg:text-6xl font-black text-slate-900 tracking-tight">تقنيات المزامنة والعمل بدون إنترنت</h2>
                        <p class="text-xl lg:text-3xl text-slate-800 font-bold leading-relaxed text-justify">
                            من أهم مميزات النظام هي قدرته الفائقة على مزامنة البيانات بشكل فوري وآني بين تطبيق الموبايل والقاعدة المركزية للمعلومات كما يدعم النظام تقنية المزامنة الذكية التي تتيح تسجيل الحضور حتى في حالات انقطاع الإنترنت داخل القاعة حيث يقوم التطبيق بحفظ البيانات محلياً وإرسالها تلقائياً للخادم بمجرد توفر الاتصال لضمان عدم ضياع أي سجل حضور تحت أي ظرف تقني.
                        </p>
                    </div>
                </div>

                <!-- Section 4: Analytics & Archiving -->
                <div class="grid lg:grid-cols-2 gap-16 items-start">
                    <div class="space-y-8 text-right">
                        <div class="inline-flex items-center gap-3 px-5 py-2 rounded-full border border-slate-200 bg-slate-100 text-slate-700 font-bold text-sm">
                            <GlobeIcon class="w-4 h-4" />
                            الإحصائيات والأرشفة
                        </div>
                        <h2 class="text-4xl lg:text-5xl font-black text-slate-900 leading-tight">التقارير التفصيلية والأرشفة الرقمية</h2>
                        <p class="text-xl lg:text-2xl text-slate-600 leading-relaxed text-justify font-medium">
                            يوفر النظام أدوات متطورة لتصدير التقارير والإحصائيات دقيقة الغيابات والحضور لكل مادة دراسية على حدة وبشكل إجمالي كما يضم ميزة الأرشفة الشاملة التي تسمح بحفظ سجلات السنوات السابقة والوصول إليها بسهولة تامة مما يسهل عمل اللجان الامتحانية واللجان الإدارية في متابعة الموقف الأكاديمي للطلاب بشكل مهني وبنقرة زر واحدة.
                        </p>
                    </div>
                    <div class="space-y-8 text-right border-r-4 border-teal-500 pr-8">
                         <h3 class="text-3xl font-black text-slate-900">نظام الإنذارات والأتمتة</h3>
                         <p class="text-xl lg:text-2xl text-slate-600 leading-relaxed text-justify font-medium">
                            النظام مبرمج ليقوم بتحليل غيابات الطلاب تلقائياً وإصدار الإنذارات (الأول، الثاني، النهائي) بناءً على النسب المئوية المحددة من قبل عمادة الكلية مما يقلل من الأخطاء البشرية ويضمن تطبيق القوانين الأكاديمية بفعالية وشفافية تامة.
                         </p>
                    </div>
                </div>

            </div>

            <!-- Pre-Footer Action -->
            <div class="text-center space-y-8 py-12">
                 <h4 class="text-3xl font-black text-slate-800">جاهز للبدء في تنظيم الحضور؟</h4>
                 <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-3 text-emerald-600 font-black text-2xl hover:gap-6 transition-all duration-300"
                >
                    إنشاء وبدء محاضرة جديدة الآن
                    <ChevronLeftIcon class="w-7 h-7" />
                </Link>
            </div>

            <!-- Final Minimal Footer -->
            <footer class="w-full pt-12 pb-8 border-t border-slate-200 flex flex-col items-center gap-6">
                <div class="flex items-center gap-12 text-slate-400 font-bold text-sm tracking-widest">
                     <span>تطوير قسم هندسة تقنيات الحاسوب</span>
                     <span class="w-2 h-2 bg-emerald-300 rounded-full"></span>
                     <span>جامعة بلاد الرافدين</span>
                </div>
                <p class="text-slate-400 text-xs font-bold tracking-[0.2em] uppercase">
                    © 2026 جميع الحقوق محفوظة - النظام السيادي لإدارة الحضور
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
