<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-col sm:flex-row items-center justify-center p-6 border-t border-gray-50 bg-white sm:rounded-b-[1.5rem]">
        <div class="flex items-center gap-1.5" dir="ltr">
            <template v-for="(link, index) in links" :key="index">
                <!-- Clickable Link -->
                <Link 
                    v-if="link.url"
                    :href="link.url" 
                    preserve-scroll
                    class="min-w-[40px] h-10 px-2 flex items-center justify-center rounded-xl border text-sm font-bold transition-all shadow-sm"
                    :class="link.active 
                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-transparent' 
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-blue-600'"
                    v-html="link.label"
                />
                <!-- Active or Disabled State -->
                <span v-else 
                    class="min-w-[40px] h-10 px-2 flex items-center justify-center rounded-xl border border-gray-100 bg-gray-50/50 text-gray-400 text-sm font-medium" 
                    v-html="link.label">
                </span>
            </template>
        </div>
    </div>
</template>
