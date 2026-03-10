<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { UsersIcon, ArrowLeftIcon, AlertCircleIcon, CheckCircle2Icon, AlertTriangleIcon } from 'lucide-vue-next';
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
}>();

const scannedStudents = ref<Array<{name: string, time: string, external_id?: string}>>([...props.initial_students]);
const totalScanned = ref(props.initial_students.length);

const scannerStatus = ref<'ready' | 'scanning' | 'success' | 'duplicate' | 'error'>('ready');
const lastScanMessage = ref('');
const isScanning = ref(false);

let html5QrCode: Html5Qrcode | null = null;
let scanTimeout: any = null;

// Smart cache to track recently scanned QRs and avoid sending duplicate requests instantly
const recentScans = new Set<string>();

const startScanner = async () => {
    try {
        html5QrCode = new Html5Qrcode("reader");
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
            isScanning.value = false;
            scannerStatus.value = 'ready';
        } catch (err) {
            console.error("Failed to stop scanner", err);
        }
    }
};

// Preload audio objects so they can be played on mobile browsers immediately upon interaction
const audioSuccess = new Audio('/success.mp3');
const audioError = new Audio('/error.mp3');

const playSound = (type: 'success' | 'error') => {
    try {
        const audio = type === 'success' ? audioSuccess : audioError;
        audio.currentTime = 0; // Reset to start
        audio.volume = 1.0;
        const playPromise = audio.play();
        if (playPromise !== undefined) {
             playPromise.catch((e) => console.log('Audio autoplay prevented by browser', e));
        }
    } catch (e) {
        console.log("Audio not supported or blocked");
    }
};

// Fast-disappearing toast notification state
const toastMessage = ref('');
const showToast = ref(false);
let toastTimeout: any = null;

const displayToast = (message: string, isError: boolean = false) => {
    toastMessage.value = message;
    showToast.value = true;
    
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        showToast.value = false;
    }, 1500); // Disappears very quickly (1.5 seconds)
};

const onScanSuccess = async (decodedText: string, decodedResult: any) => {
    // If we just scanned this exact QR code in the last 3-4 seconds, ignore it entirely (Local Debounce)
    if (recentScans.has(decodedText)) return;
    
    // Add to local cache instantly to prevent double-firing
    recentScans.add(decodedText);
    setTimeout(() => {
        recentScans.delete(decodedText);
    }, 4000); // Clear from cache after 4 seconds

    scannerStatus.value = 'scanning'; // Keep the UI in scanning mode so it doesn't freeze!
    
    try {
        const response = await axios.post(`/teacher/scanner/${props.lecture.id}/scan`, {
            qr_payload: decodedText
        });
        
        if (response.data.success) {
            scannerStatus.value = 'success';
            lastScanMessage.value = response.data.message;
            
            // Add to local list and increment count (ensure no UI duplicates)
            const exists = scannedStudents.value.find(s => s.external_id === response.data.student.external_id);
            if(!exists) {
                scannedStudents.value.unshift({
                    name: response.data.student.name,
                    time: response.data.student.time,
                    external_id: response.data.student.external_id
                });
                totalScanned.value++;
            }
            
            playSound('success');
            displayToast(`✅ تم تسجيل حضور: ${response.data.student.name}`);
        }
    } catch (error: any) {
        if (error.response && error.response.status === 400 && error.response.data.message.includes('مسبقاً')) {
            scannerStatus.value = 'duplicate';
            lastScanMessage.value = error.response.data.message;
            displayToast(`⚠️ مسجل مسبقاً!`, true);
        } else {
            scannerStatus.value = 'error';
            lastScanMessage.value = error.response?.data?.message || 'لقد حدث خطأ أثناء فحص الرمز.';
            playSound('error');
            displayToast(`❌ حدث خطأ في التسجيل`, true);
        }
    }

    // Briefly show the success/error message for 1.5s then return to scanning status UI
    clearTimeout(scanTimeout);
    scanTimeout = setTimeout(() => {
        if(scannerStatus.value !== 'scanning') {
            scannerStatus.value = 'scanning';
            lastScanMessage.value = '';
        }
    }, 1500);
};

const onScanFailure = (error: any) => {
    // html5-qrcode calls this frequently when no QR is in frame. 
    // Usually safe to ignore to avoid console spam.
};


onMounted(() => {
    startScanner();

    // Listen to the Reverb WebSocket channel for this specific lecture (for real-time dashboard sync if needed)
    if (window.Echo) {
        window.Echo.channel(`lecture.${props.lecture.id}`)
            .listen('.student.scanned', (e: any) => {
                // If scanned by another device
                const exists = scannedStudents.value.find(s => s.external_id === e.studentData.external_id);
                if(!exists) {
                    scannedStudents.value.unshift(e.studentData);
                    totalScanned.value++;
                }
            });
    }
});

onUnmounted(() => {
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
                <Link :href="route('teacher.scanner.close', lecture.id)" method="post" as="button" type="button" class="px-5 py-2.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-bold transition-all shadow-lg shadow-rose-500/30">
                    إنهاء المحاضرة 
                </Link>
            </div>
        </template>


        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Scanner Interface Area -->
                <div class="lg:col-span-2 overflow-hidden bg-white shadow-xl shadow-gray-200/50 rounded-2xl border border-gray-100 flex flex-col items-center p-8 min-h-[500px] relative">
                    
                    <h3 class="text-xl font-bold text-gray-900 mb-6 w-full text-center">وجه الكاميرا نحو بطاقة الطالب</h3>
                    
                    <!-- Scanner Container (Enlarged) -->
                    <div class="w-full max-w-md aspect-square bg-gray-100 rounded-2xl border-4 border-dashed border-gray-300 flex items-center justify-center overflow-hidden relative shadow-inner">
                        <div id="reader" class="w-full h-full object-cover"></div>
                        
                        <!-- Overlay when scanning -->
                        <div v-if="scannerStatus === 'scanning'" class="absolute inset-0 border-[6px] border-indigo-500/50 rounded-xl pointer-events-none animate-pulse"></div>
                        
                        <!-- Fast Toast Notification Overlay -->
                        <transition name="toast-slide">
                            <div v-if="showToast" class="absolute top-4 inset-x-4 flex justify-center pointer-events-none z-50">
                                <div class="bg-white/95 backdrop-blur shadow-2xl rounded-2xl px-6 py-4 flex items-center border-b-4" :class="scannerStatus === 'success' ? 'border-emerald-500 border-l-emerald-500' : 'border-rose-500'">
                                    <span class="text-lg font-black tracking-tight" :class="scannerStatus === 'success' ? 'text-emerald-700' : 'text-rose-700'">
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
                            <div v-else class="bg-teal-50 border border-indigo-100 text-teal-800 px-4 py-3 rounded-xl flex items-center shadow-sm w-full justify-center opacity-80">
                                <span class="font-medium text-sm animate-pulse">جاري فحص الكاميرا...</span>
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
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        </div>
                        <div class="p-4 flex-1 overflow-y-auto max-h-[400px]">
                            <transition-group name="list" tag="ul" class="space-y-3" v-if="scannedStudents.length > 0">
                                <li v-for="(student, index) in scannedStudents" :key="index" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-center">
                                        <div class="w-8 flex justify-center text-sm font-black text-gray-400">
                                            {{ totalScanned - index }}
                                        </div>
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-emerald-100 to-teal-100 flex items-center justify-center text-emerald-700 font-bold ml-2 border border-emerald-200">
                                            {{ student.name.charAt(0) }}
                                        </div>
                                        <div class="mr-2 text-right">
                                            <p class="text-sm font-bold text-gray-900">{{ student.name }}</p>
                                            <p class="text-xs font-mono text-gray-500 mt-0.5" dir="ltr">ID: {{ student.external_id || '---' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-left flex flex-col items-end">
                                        <p class="text-xs text-emerald-600 font-bold mb-1 flex items-center">
                                            <CheckCircle2Icon class="w-3 h-3 ml-1" /> حاضر
                                        </p>
                                        <span class="text-xs font-bold text-gray-400 font-mono bg-gray-50 px-2 py-0.5 rounded" dir="ltr">{{ student.time }}</span>
                                    </div>
                                </li>
                            </transition-group>
                            <div v-else class="text-center text-gray-400 py-12 text-sm flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
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
</style>
