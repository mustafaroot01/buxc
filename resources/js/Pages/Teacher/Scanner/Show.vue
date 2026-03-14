<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { UsersIcon, ArrowLeftIcon, AlertCircleIcon, CheckCircle2Icon, AlertTriangleIcon, XIcon, LogOutIcon } from 'lucide-vue-next';
// @ts-ignore
import { Html5Qrcode } from 'html5-qrcode';
import axios from 'axios';

const props = defineProps<{
    lecture: {
        id: string;
        title: string;
        subject: { name: string; code: string };
        group: { name: string };
    };
    initial_students: Array<{name: string, time: string, external_id: string}>;
    total_students: number;
}>();

const scannedStudents = ref<Array<{name: string, time: string, external_id?: string, status?: 'present' | 'pending' | 'failed', qr_payload?: string}>>([...props.initial_students.map(s => ({...s, status: 'present' as const}))]);
const totalScanned = ref(props.initial_students.length);

const scannerStatus = ref<'ready' | 'scanning' | 'success' | 'duplicate' | 'error'>('ready');
const lastScanMessage = ref('');
const isScanning = ref(false);
const isOffline = ref(!navigator.onLine);
const isCameraGranted = ref(false);

let html5QrCode: Html5Qrcode | null = null;
let scanTimeout: any = null;

const updateOnlineStatus = () => {
    isOffline.value = !navigator.onLine;
    if (isOffline.value) {
        stopScanner();
        displayToast('انقطع الاتصال بالإنترنت التام. تم إيقاف الماسح', true);
    } else {
        if (isCameraGranted.value) {
            startScanner();
            displayToast('عاد الاتصال. تم تفعيل الكاميرا', false);
        } else {
            displayToast('عاد الاتصال. الأنظمة جاهزة لبدء المسح', false);
        }
    }
};

const recentScans = new Set<string>();

let audioCtx: AudioContext | null = null;
const initAudio = () => {
    if (!audioCtx) {
        const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
        if (AudioContextClass) {
            audioCtx = new AudioContextClass();
        }
    }
    if (audioCtx && audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
};

const grantCameraAndStart = () => {
    if (isCameraGranted.value || isScanning.value) return; // Prevent double-click race condition
    initAudio(); // Required to unlock audio on iOS/Safari/Chrome!
    isCameraGranted.value = true;
    startScanner();
};

const startScanner = async () => {
    if (isOffline.value || isScanning.value) return;
    try {
        // Only initialize a new instance if one doesn't exist
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }
        const config = { fps: 30, qrbox: { width: 350, height: 350 } };
        
        await html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        );
        isScanning.value = true;
        scannerStatus.value = 'scanning';
    } catch (err) {
        console.error("Error starting scanner:", err);
        scannerStatus.value = 'error';
        lastScanMessage.value = 'تعذر تشغيل الكاميرا. يرجى التأكد من منح الصلاحيات.';
    }
};

const stopScanner = async () => {
    if (html5QrCode && isScanning.value) {
        try {
            await html5QrCode.stop();
            try { html5QrCode.clear(); } catch(e){} // Safely clear DOM overrides
            html5QrCode = null; // Important to force a clean recreation on reconnect
            isScanning.value = false;
            scannerStatus.value = 'ready';
        } catch (err) {
            console.error("Failed to stop scanner", err);
        }
    }
};

const playSound = (type: 'success' | 'error') => {
    try {
        if (!audioCtx) return;
        
        const osc = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        
        osc.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        
        if (type === 'success') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
            osc.frequency.setValueAtTime(1108, audioCtx.currentTime + 0.1); // C#6
            
            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.2);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(300, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(150, audioCtx.currentTime + 0.2);
            
            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.3);
        }
    } catch (e) {
        console.log("Audio not supported or blocked", e);
    }
};

const toastMessage = ref('');
const showToast = ref(false);
let toastTimeout: any = null;

const displayToast = (message: string, isError: boolean = false) => {
    toastMessage.value = message;
    showToast.value = true;
    
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        showToast.value = false;
    }, 1500); 
};

const onScanSuccess = async (decodedText: string, decodedResult: any) => {
    if (isOffline.value) return;

    // Fast-Scan Dedup (Prevents double scanning from single long-held frame)
    if (recentScans.has(decodedText)) return;
    recentScans.add(decodedText);
    setTimeout(() => recentScans.delete(decodedText), 6000); // Wait 6 seconds before allowing the same QR to be re-evaluated

    // Frontend Dedup (Global) - Check if already scanned in THIS session
    const alreadyScanned = scannedStudents.value.find(s => s.qr_payload === decodedText);
    if (alreadyScanned) {
        if (alreadyScanned.status === 'present') {
            displayToast(`⚠️ مسجل مسبقاً!`, true);
            playSound('error');
        }
        return;
    }

    scannerStatus.value = 'scanning'; 
    
    // Send the request directly
    processScanRequest(decodedText);
};

const processScanRequest = async (qr_payload: string) => {
    try {
        const response = await axios.post(`/teacher/scanner/${props.lecture.id}/scan`, {
            qr_payload: qr_payload
        });
        
        if (response.data.success) {
            // Update UI dynamically only on success
            const newDoc = {
                name: response.data.student.name,
                external_id: response.data.student.external_id,
                time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }),
                status: 'present' as const,
                qr_payload: qr_payload
            };
            
            scannedStudents.value.unshift(newDoc);
            totalScanned.value++;
            scannerStatus.value = 'success';
            lastScanMessage.value = response.data.message;
            displayToast(`✅ تم التسجيل: ${newDoc.name}`);
            playSound('success');
        }
    } catch (error: any) {
        if (error.response && error.response.status >= 400 && error.response.status < 500) {
            // Client error (Duplicate, Invalid) → Permanent failure — keep in recentScans
            scannerStatus.value = error.response.data.message.includes('مسبقاً') ? 'duplicate' : 'error';
            lastScanMessage.value = error.response.data.message;
            displayToast(`❌ ${lastScanMessage.value}`, true);
            playSound('error');
            return;
        }
        
        // Network Error or Server 5xx → Allow immediate retry by removing from debounce set
        recentScans.delete(qr_payload);
        scannerStatus.value = 'error';
        lastScanMessage.value = 'ضعف في الشبكة... تعذر الاتصال بالسيرفر';
        displayToast(`❌ فشل الاتصال بالسيرفر، حاول مسح الطالب مجدداً`, true);
        playSound('error');
    }

    scanTimeout = setTimeout(() => {
        if(scannerStatus.value !== 'scanning') {
            scannerStatus.value = 'scanning';
            lastScanMessage.value = '';
        }
    }, 500);
};

const showCloseModal = ref(false);
const isClosing = ref(false);

const closeLecture = () => {
    showCloseModal.value = true;
};

const confirmClose = async () => {
    isClosing.value = true;
    await stopScanner();
    router.post(route('teacher.scanner.close', props.lecture.id));
};

const cancelClose = () => {
    showCloseModal.value = false;
};

const onScanFailure = (error: any) => {};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    
    // startScanner() is now triggered by the explicit "Grant Permissions" button

    if (window.Echo) {
        window.Echo.channel(`lecture.${props.lecture.id}`)
            .listen('.student.scanned', (e: any) => {
                const exists = scannedStudents.value.find(s => s.external_id === e.studentData.external_id);
                if(!exists) {
                    scannedStudents.value.unshift({...e.studentData, status: 'present'});
                    totalScanned.value++;
                }
            });
    }
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
    stopScanner();
    if (window.Echo) {
         window.Echo.leaveChannel(`lecture.${props.lecture.id}`);
    }
});
</script>

<template>
    <Head title="Live Scanner" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center">
                    <Link :href="route('teacher.dashboard')" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </Link>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 flex items-center pr-4 border-r-2 border-gray-200">
                        <span class="text-teal-600 ml-2">●</span>
                        تسجيل الحضور: {{ lecture.title || lecture.subject?.name }}
                    </h2>
                </div>
                <button @click="closeLecture" type="button" class="px-5 py-2.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-bold transition-all shadow-lg shadow-rose-500/30">
                    إنهاء المحاضرة 
                </button>
            </div>
        </template>


        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Scanner Interface Area -->
                <!-- Offline Banner -->
                <transition name="fade">
                    <div v-if="isOffline" class="col-span-1 lg:col-span-3 bg-red-600 text-white p-5 rounded-2xl flex items-center justify-center font-bold shadow-xl shadow-red-500/30 animate-pulse z-50 border-4 border-red-400">
                        <AlertTriangleIcon class="w-8 h-8 ml-3 text-red-100" />
                        <span class="text-xl">انقطع الاتصال بالإنترنت التام! الكاميرا متوقفة مؤقتاً لحين عودة الاتصال لمنع ضياع البيانات!</span>
                    </div>
                </transition>

                <div class="lg:col-span-2 overflow-hidden bg-white shadow-xl shadow-gray-200/50 rounded-2xl border border-gray-100 flex flex-col items-center p-8 min-h-[500px] relative">
                    
                    <h3 class="text-xl font-bold text-gray-900 mb-6 w-full text-center">وجه الكاميرا نحو بطاقة الطالب</h3>
                    
                    <!-- Scanner Container (Enlarged) -->
                    <div class="w-full max-w-md aspect-square bg-gray-100 rounded-2xl border-4 border-dashed flex items-center justify-center overflow-hidden relative shadow-inner" 
                        :class="isOffline ? 'border-red-300' : 'border-gray-300'">
                        
                        <div v-show="isCameraGranted" id="reader" class="w-full h-full object-cover" :class="{'opacity-50 grayscale': isOffline}"></div>

                        <div v-if="!isCameraGranted && !isOffline" class="absolute inset-0 bg-white z-20 flex flex-col items-center justify-center p-6 text-center">
                            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-6 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800 mb-2">تسجيل الحضور بالكاميرا</h4>
                            <p class="text-gray-500 mb-8 text-sm leading-relaxed">يتطلب النظام صلاحية الوصول لكاميرا الجهاز لبدء مسح هويات الطلاب. يرجى التأكد من الموافقة على طلب المتصفح.</p>
                            <button @click="grantCameraAndStart" class="px-8 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-lg shadow-lg shadow-emerald-500/30 hover:shadow-emerald-600/50 hover:scale-105 transition-all w-full flex items-center justify-center gap-2">
                                <span>منح الصلاحية وبدء المسح</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Overlay when scanning -->
                        <div v-if="scannerStatus === 'scanning' && !isOffline" class="absolute inset-0 border-[6px] border-indigo-500/50 rounded-xl pointer-events-none animate-pulse"></div>
                        
                        <!-- Fast Toast Notification Overlay -->
                        <transition name="toast-slide">
                            <div v-if="showToast" class="absolute top-4 inset-x-4 flex justify-center pointer-events-none z-50">
                                <div class="bg-white/95 backdrop-blur shadow-2xl rounded-2xl px-6 py-4 flex items-center border-b-4" :class="scannerStatus === 'success' || !toastMessage.includes('❌') ? 'border-emerald-500 border-l-emerald-500' : 'border-rose-500'">
                                    <span class="text-lg font-black tracking-tight" :class="scannerStatus === 'success' || !toastMessage.includes('❌') ? 'text-emerald-700' : 'text-rose-700'">
                                        {{ toastMessage }}
                                    </span>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- Status Display -->
                    <div class="mt-8 w-full max-w-sm h-20">
                        <transition name="fade" mode="out-in">
                            <div v-if="scannerStatus === 'success'" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center shadow-sm w-full">
                                <CheckCircle2Icon class="w-6 h-6 ml-3 text-emerald-600 flex-shrink-0" />
                                <span class="font-bold text-sm">{{ lastScanMessage }}</span>
                            </div>
                            <div v-else-if="scannerStatus === 'duplicate'" class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl flex items-center shadow-sm w-full">
                                <AlertTriangleIcon class="w-6 h-6 ml-3 text-amber-600 flex-shrink-0" />
                                <span class="font-bold text-sm">{{ lastScanMessage }}</span>
                            </div>
                            <div v-else-if="scannerStatus === 'error'" class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center shadow-sm w-full">
                                <AlertCircleIcon class="w-6 h-6 ml-3 text-rose-600 flex-shrink-0" />
                                <span class="font-bold text-sm">{{ lastScanMessage }}</span>
                            </div>
                            <div v-else class="bg-teal-50 border border-indigo-100 text-teal-800 px-4 py-3 rounded-xl flex items-center shadow-sm w-full justify-center opacity-80" :class="{'bg-red-50 text-red-800 border-red-200': isOffline}">
                                <span class="font-medium text-sm" :class="{'animate-pulse': !isOffline}">{{ isOffline ? 'النظام معلق حالياً' : 'جاري فحص الكاميرا...' }}</span>
                            </div>
                        </transition>
                    </div>

                </div>

                <!-- Real-time Stats & Feed -->
                <div class="flex flex-col gap-6">
                    
                    <!-- Counter Card -->
                    <div class="overflow-hidden bg-gradient-to-br from-indigo-600 to-purple-700 shadow-xl shadow-indigo-200/50 rounded-2xl p-6 text-white text-center border border-indigo-500">
                        <div class="flex items-center justify-center mb-2">
                            <UsersIcon class="w-6 h-6 ml-2 opacity-80" />
                            <h3 class="text-lg font-medium opacity-90">الطلاب الحاضرين</h3>
                        </div>
                        <p class="text-6xl font-extrabold tracking-tight">{{ totalScanned }}</p>
                    </div>

                    <!-- Live Feed List -->
                    <div class="overflow-hidden bg-white shadow-xl shadow-gray-200/50 rounded-2xl flex-1 flex flex-col border border-gray-100">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80 flex items-center justify-between">
                            <h3 class="text-md font-bold text-gray-800">سجل المسح المباشر</h3>
                            <span class="w-2 h-2 rounded-full" :class="isOffline ? 'bg-red-500' : 'bg-emerald-500 animate-ping'"></span>
                        </div>
                        <div class="p-4 flex-1 overflow-y-auto max-h-[400px]">
                            <transition-group name="list" tag="ul" class="space-y-3" v-if="scannedStudents.length > 0">
                                <li v-for="(student, index) in scannedStudents" :key="index" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl shadow-sm transition-shadow"
                                    :class="{
                                        'ring-2 ring-amber-400 bg-amber-50/50': student.status === 'pending',
                                        'ring-2 ring-rose-400 bg-rose-50/50': student.status === 'failed'
                                    }">
                                    <div class="flex items-center">
                                        <div class="w-8 flex justify-center text-sm font-black" :class="student.status === 'present' ? 'text-gray-400' : (student.status === 'pending' ? 'text-amber-500 animate-bounce' : 'text-rose-500 animate-pulse')">
                                            {{ student.status === 'present' ? totalScanned - Array.from(scannedStudents.filter(s => s.status === 'present')).indexOf(student) : '⌛' }}
                                        </div>
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold ml-2 border"
                                            :class="{
                                                'bg-gradient-to-tr from-emerald-100 to-teal-100 text-emerald-700 border-emerald-200': student.status === 'present',
                                                'bg-gradient-to-tr from-amber-100 to-yellow-100 text-amber-700 border-amber-200': student.status === 'pending',
                                                'bg-gradient-to-tr from-rose-100 to-red-100 text-rose-700 border-rose-200': student.status === 'failed'
                                            }">
                                            {{ student.name.charAt(0) }}
                                        </div>
                                        <div class="mr-2 text-right">
                                            <p class="text-sm font-bold text-gray-900" :class="{'text-xs': student.status !== 'present'}">{{ student.name }}</p>
                                            <p class="text-[10px] font-mono mt-0.5" dir="ltr" :class="student.status === 'present' ? 'text-gray-500' : 'text-gray-400'">ID: {{ student.external_id || '---' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-left flex flex-col items-end">
                                        <p class="text-[11px] font-bold mb-1 flex items-center whitespace-nowrap" 
                                           :class="{
                                              'text-emerald-600': student.status === 'present',
                                              'text-amber-600 animate-pulse': student.status === 'pending',
                                              'text-rose-600 animate-pulse': student.status === 'failed'
                                           }">
                                            <CheckCircle2Icon v-if="student.status === 'present'" class="w-3 h-3 ml-1" /> 
                                            <AlertCircleIcon v-else class="w-3 h-3 ml-1" />
                                            {{ student.status === 'present' ? 'حاضر' : (student.status === 'pending' ? 'مزامنة...' : 'إعادة محاولة...') }}
                                        </p>
                                        <span class="text-[10px] font-bold font-mono px-2 py-0.5 rounded cursor-default" dir="ltr"
                                            :class="student.status === 'present' ? 'text-gray-400 bg-gray-50' : 'text-gray-600 bg-white shadow-sm border border-gray-100'">{{ student.time }}</span>
                                    </div>
                                </li>
                            </transition-group>
                            <div v-else class="text-center text-gray-400 py-12 text-sm flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                    <UsersIcon class="w-8 h-8 text-gray-300" />
                                </div>
                                <p class="font-medium">بانتظار مسح أول بطاقة QR...</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>

    <!-- ─── Close Lecture Confirmation Modal ──────────────────────────── -->
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" @click="cancelClose"></div>

                <!-- Modal Panel -->
                <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden z-10">
                    <!-- Top danger stripe -->
                    <div class="h-2 bg-gradient-to-r from-rose-500 via-red-500 to-rose-600"></div>

                    <div class="p-8">
                        <!-- Icon -->
                        <div class="flex justify-center mb-5">
                            <div class="w-20 h-20 rounded-2xl bg-rose-50 border-2 border-rose-100 flex items-center justify-center shadow-inner">
                                <LogOutIcon class="w-10 h-10 text-rose-500" />
                            </div>
                        </div>

                        <!-- Title -->
                        <h3 class="text-2xl font-black text-gray-900 text-center mb-2">إنهاء المحاضرة</h3>
                        <p class="text-center text-gray-500 text-sm mb-6 leading-relaxed">
                            هل أنت متأكد من إنهاء جلسة المسح؟<br/>
                            <strong class="text-rose-600">سيتم تسجيل المتغيبين تلقائياً</strong> ولا يمكن فتح الماسح مجدداً.
                        </p>

                        <!-- Stats summary -->
                        <div class="bg-gray-50 rounded-2xl p-4 mb-6 flex items-center justify-around border border-gray-100">
                            <div class="text-center">
                                <p class="text-3xl font-black text-emerald-600">{{ totalScanned }}</p>
                                <p class="text-xs text-gray-500 font-bold mt-1">✅ حاضر</p>
                            </div>
                            <div class="w-px h-10 bg-gray-200"></div>
                            <div class="text-center">
                                <p class="text-3xl font-black text-gray-400">{{ total_students - totalScanned }}</p>
                                <p class="text-xs text-gray-500 font-bold mt-1">🔴 سيُسجَّل غائباً</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button @click="cancelClose" :disabled="isClosing"
                                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-gray-700 text-sm font-bold rounded-2xl transition-all">
                                إلغاء
                            </button>
                            <button @click="confirmClose" :disabled="isClosing"
                                    class="flex-1 px-4 py-3 bg-gradient-to-r from-rose-600 to-red-500 hover:from-rose-700 hover:to-red-600 disabled:opacity-60 text-white text-sm font-black rounded-2xl transition-all shadow-lg shadow-rose-500/30 flex items-center justify-center gap-2">
                                <LogOutIcon class="w-4 h-4" />
                                {{ isClosing ? 'جاري الإنهاء...' : 'نعم، إنهاء المحاضرة' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.list-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}
.list-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
