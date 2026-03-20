<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();

const formatLabel = (label: string) => {
    if (label.includes('Previous')) {
        return '« السابق';
    }
    if (label.includes('Next')) {
        return 'التالي »';
    }
    return label;
};
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-col sm:flex-row items-center justify-center p-8 bg-transparent">
        <div class="flex items-center gap-2 rtl:flex-row-reverse" dir="ltr">
            <template v-for="(link, index) in links" :key="index">
                <!-- Clickable Link -->
                <Link 
                    v-if="link.url"
                    :href="link.url" 
                    preserve-scroll
                    class="min-w-[45px] h-11 px-4 flex items-center justify-center rounded-2xl border text-[14px] font-black transition-all duration-300 shadow-sm first:rounded-r-2xl last:rounded-l-2xl"
                    :class="link.active 
                        ? 'bg-gradient-to-br from-indigo-600 to-blue-600 text-white border-transparent shadow-indigo-500/30 scale-105 z-10' 
                        : 'bg-white text-gray-500 border-gray-100 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 hover:-translate-y-0.5'"
                    v-html="formatLabel(link.label)"
                />
                <!-- Active or Disabled State -->
                <span v-else 
                    class="min-w-[45px] h-11 px-4 flex items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50 text-gray-400 text-[14px] font-bold opacity-60 first:rounded-r-2xl last:rounded-l-2xl" 
                    v-html="formatLabel(link.label)">
                </span>
            </template>
        </div>
    </div>
</template>
