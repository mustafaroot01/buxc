<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { HeartIcon } from 'lucide-vue-next';

const page = usePage();
const settings = page.props.settings as any;
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div class="text-center">
            <Link href="/" class="flex flex-col items-center gap-4">
                <div class="p-3 bg-white rounded-[2rem] shadow-xl shadow-teal-500/10 border border-white">
                    <img v-if="settings?.login_logo" :src="settings.login_logo" class="h-20 w-auto object-contain" />
                    <ApplicationLogo v-else class="h-20 w-20 fill-current text-teal-600" />
                </div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">{{ settings?.school_name || 'النظام الأكاديمي' }}</h2>
            </Link>
        </div>

        <div
            class="mt-10 w-full overflow-hidden bg-white/80 backdrop-blur-lg px-8 py-8 shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white/60 sm:max-w-md sm:rounded-[2.5rem]"
        >
            <slot />
        </div>

        <!-- Guest Footer Credits -->
        <div class="mt-8 mb-6 text-center">
            <p class="text-[11px] text-gray-500 flex items-center justify-center gap-1 font-medium">
                صُنع بـ <HeartIcon class="w-3.5 h-3.5 text-red-500 fill-current" /> في هندسة تقنيات الحاسوب
            </p>
            <a href="https://Diyala.net" target="_blank" class="block text-xs text-teal-600 font-black mt-1 hover:underline">
                iT-Diyala
            </a>
        </div>
    </div>
</template>
