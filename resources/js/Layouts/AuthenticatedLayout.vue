<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboardIcon, 
    UsersIcon, 
    GraduationCapIcon, 
    LayersIcon, 
    BookOpenIcon, 
    QrCodeIcon, 
    FileBarChartIcon, 
    ArchiveIcon, 
    ActivityIcon, 
    SettingsIcon, 
    MenuIcon,
    XIcon,
    LogOutIcon,
    UserIcon,
    AlertTriangleIcon,
    HeartIcon,
    ClipboardListIcon
} from 'lucide-vue-next';
import {
    Dialog,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';

const sidebarOpen = ref(false);
const desktopSidebarOpen = ref(true);

const page = usePage();
const settings = page.props.settings as any;

const navigation = [
    { name: 'لوحة التحكم', href: route('dashboard'), current: route().current('dashboard'), icon: LayoutDashboardIcon, roles: ['admin', 'super_admin', 'teacher'] },
    
    // Admin / Super Admin
    { name: 'الطلاب', href: route('admin.students.index'), current: route().current('admin.students.*'), icon: UsersIcon, roles: ['admin', 'super_admin'] },
    { name: 'الأساتذة', href: route('admin.teachers.index'), current: route().current('admin.teachers.*'), icon: GraduationCapIcon, roles: ['admin', 'super_admin'] },
    { name: 'المراحل', href: route('admin.stages.index'), current: route().current('admin.stages.*'), icon: LayersIcon, roles: ['admin', 'super_admin'] },
    { name: 'المجموعات', href: route('admin.groups.index'), current: route().current('admin.groups.*'), icon: UsersIcon, roles: ['admin', 'super_admin'] },
    { name: 'المواد', href: route('admin.subjects.index'), current: route().current('admin.subjects.*'), icon: BookOpenIcon, roles: ['admin', 'super_admin'] },
    { name: 'المحاضرات', href: route('admin.lectures.index'), current: route().current('admin.lectures.*'), icon: BookOpenIcon, roles: ['admin', 'super_admin'] },
    { name: 'طباعة QR', href: route('admin.print.qrs'), current: route().current('admin.print.qrs'), icon: QrCodeIcon, roles: ['admin', 'super_admin'] },
    { name: 'التقارير', href: route('admin.reports.index'), current: route().current('admin.reports.*'), icon: FileBarChartIcon, roles: ['admin', 'super_admin'] },
    { name: 'تنبيهات الغياب', href: route('admin.warnings.index'), current: route().current('admin.warnings.*'), icon: AlertTriangleIcon, roles: ['admin', 'super_admin'] },
    { name: 'الأرشيف', href: route('admin.archive.index'), current: route().current('admin.archive.*'), icon: ArchiveIcon, roles: ['admin', 'super_admin'] },
    
    // Super Admin only
    { name: 'سجل النشاطات', href: route('admin.audit.index'), current: route().current('admin.audit.*'), icon: ActivityIcon, roles: ['super_admin'] },
    { name: 'التسجيل', href: route('admin.registrations.index'), current: route().current('admin.registrations.*'), icon: ClipboardListIcon, roles: ['super_admin'] },
    { name: 'الإعدادات', href: route('admin.settings.index'), current: route().current('admin.settings.*'), icon: SettingsIcon, roles: ['super_admin'] },
    
    // Teacher only
    { name: 'محاضراتي', href: route('teacher.lectures.index'), current: route().current('teacher.lectures.*'), icon: BookOpenIcon, roles: ['teacher'], excludeRoles: ['admin', 'super_admin'] },
    { name: 'تنبيهات طلابي', href: route('teacher.warnings.index'), current: route().current('teacher.warnings.*'), icon: AlertTriangleIcon, roles: ['teacher'], excludeRoles: ['admin', 'super_admin'] },
];

const hasRole = (userTokens: any, navRoles: string[], excludeRoles?: string[]) => {
    if (!userTokens) return false;
    
    if (excludeRoles && excludeRoles.some(r => userTokens.includes(r))) {
        return false;
    }
    
    return navRoles.some(r => userTokens.includes(r));
};
</script>

<template>
    <div>
        <!-- Mobile Sidebar -->
        <TransitionRoot as="template" :show="sidebarOpen">
            <Dialog as="div" class="relative z-50 lg:hidden" @close="sidebarOpen = false">
                <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-900/80" />
                </TransitionChild>

                <div class="fixed inset-0 flex">
                    <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="translate-x-full">
                        <DialogPanel class="relative flex w-full max-w-xs flex-1">
                            <!-- Mobile Sidebar content -->
                            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 shadow-xl">
                                <div class="flex h-16 shrink-0 items-center justify-between border-b border-gray-100 mt-2">
                                    <Link :href="route('dashboard')" class="flex items-center gap-2.5">
                                        <div class="flex items-center justify-center p-1 bg-teal-50 rounded-lg">
                                            <img v-if="settings?.login_logo" :src="settings.login_logo" key="mobile-sidebar-logo" fetchpriority="high" class="h-9 w-auto object-contain" />
                                            <ApplicationLogo v-else class="h-9 w-auto fill-current text-teal-600" />
                                        </div>
                                        <span class="text-lg font-black text-gray-900 tracking-tight leading-none">{{ settings?.school_name || 'النظام الأكاديمي' }}</span>
                                    </Link>
                                    <button type="button" class="p-2 -mr-2 text-gray-500 hover:bg-gray-100 rounded-full transition-colors" @click="sidebarOpen = false">
                                        <XIcon class="h-6 w-6" aria-hidden="true" />
                                    </button>
                                </div>
                                <nav class="flex flex-1 flex-col">
                                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                        <li>
                                            <ul role="list" class="-mx-2 space-y-1">
                                                <template v-for="item in navigation" :key="item.name">
                                                    <li v-if="hasRole($page.props.auth.user.roles, item.roles, item.excludeRoles)">
                                                        <Link :href="item.href" prefetch :class="[item.current ? 'bg-teal-50 text-teal-600 font-bold' : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50', 'group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 transition-all']">
                                                            <component :is="item.icon" :class="[item.current ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-600', 'h-5 w-5 shrink-0 transition-colors']" aria-hidden="true" />
                                                            {{ item.name }}
                                                        </Link>
                                                    </li>
                                                </template>
                                            </ul>
                                        </li>
                                    </ul>
                                </nav>
                                
                                <!-- Mobile Credits Footer -->
                                <div class="mt-auto border-t border-gray-100 pt-4 pb-2">
                                    <p class="text-[10px] text-gray-500 text-center flex items-center justify-center gap-1 font-medium">
                                        صُنع بـ <HeartIcon class="w-3 h-3 text-red-500 fill-current" /> في هندسة تقنيات الحاسوب
                                    </p>
                                    <a href="https://Diyala.net" target="_blank" class="block text-[11px] text-teal-600 font-black text-center mt-1 hover:underline">
                                        iT-Diyala
                                    </a>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Static Sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:start-0 lg:z-50 lg:flex lg:w-72 lg:flex-col transition-transform duration-300 ease-in-out"
             :class="desktopSidebarOpen ? 'translate-x-0' : 'translate-x-full'">
            <!-- Sidebar component, swap this element with another sidebar if you like -->
            <div class="flex grow flex-col gap-y-5 overflow-y-auto border-e border-gray-200 bg-white px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center mt-2 border-b border-gray-100">
                    <Link :href="route('dashboard')" class="flex items-center gap-3">
                        <div class="flex items-center justify-center p-1.5 bg-teal-50 rounded-xl shadow-sm border border-teal-100/50">
                            <img v-if="settings?.login_logo" :src="settings.login_logo" key="desktop-sidebar-logo" fetchpriority="high" class="h-10 w-auto object-contain" />
                            <ApplicationLogo v-else class="h-10 w-auto fill-current text-teal-600" />
                        </div>
                        <span class="text-[17px] font-black text-gray-900 tracking-tight leading-snug drop-shadow-sm">{{ settings?.school_name || 'النظام الأكاديمي' }}</span>
                    </Link>
                </div>
                <nav class="flex flex-1 flex-col pt-2">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1.5">
                                <template v-for="item in navigation" :key="item.name">
                                    <li v-if="hasRole($page.props.auth.user.roles, item.roles, item.excludeRoles)">
                                        <Link :href="item.href" prefetch :class="[item.current ? 'bg-teal-50 text-teal-700 font-bold border-e-4 border-teal-600' : 'text-gray-600 font-medium hover:text-teal-700 hover:bg-teal-50/50 border-e-4 border-transparent hover:border-teal-300', 'group flex items-center gap-x-3 rounded-s-xl p-3 text-[15px] leading-6 transition-all duration-200']">
                                            <component :is="item.icon" :class="[item.current ? 'text-teal-600' : 'text-gray-400 group-hover:text-teal-500', 'h-5 w-5 shrink-0 transition-colors']" aria-hidden="true" />
                                            {{ item.name }}
                                        </Link>
                                    </li>
                                </template>
                            </ul>
                        </li>
                    </ul>
                </nav>

                <!-- Desktop Credits Footer -->
                <div class="mt-auto border-t border-gray-100 pt-6">
                    <p class="text-[11px] text-gray-500 flex items-center justify-center gap-1 font-medium leading-relaxed">
                        صُنع بـ <HeartIcon class="w-3 h-3 text-red-500 fill-current" /> في هندسة تقنيات الحاسوب
                    </p>
                    <a href="https://Diyala.net" target="_blank" class="block text-[13px] text-teal-600 font-black text-center mt-1 hover:underline tracking-wide">
                        iT-Diyala
                    </a>
                </div>
            </div>
        </div>

        <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out" :class="desktopSidebarOpen ? 'lg:ps-72' : 'lg:ps-0'">
            <!-- Topbar sticky header -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <!-- Mobile Menu Button -->
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden hover:bg-gray-100 rounded-full transition-colors" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <MenuIcon class="h-6 w-6" aria-hidden="true" />
                </button>

                <!-- Desktop Menu Button -->
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 hidden lg:block hover:bg-gray-100 rounded-full transition-colors" @click="desktopSidebarOpen = !desktopSidebarOpen">
                    <span class="sr-only">Toggle sidebar</span>
                    <MenuIcon class="h-6 w-6" aria-hidden="true" />
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true" />

                <div class="flex flex-1 justify-end gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Profile dropdown -->
                        <div class="relative">
                            <Dropdown align="left" width="48">
                                <template #trigger>
                                    <button class="flex items-center p-1.5 transition-colors rounded-full hover:bg-gray-100">
                                        <span class="sr-only">Open user menu</span>
                                        <div class="h-9 w-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold shadow-inner overflow-hidden">
                                            <img v-if="$page.props.auth.user.photo_path" 
                                                 :src="'/storage/' + $page.props.auth.user.photo_path" 
                                                 class="h-full w-full object-cover" 
                                                 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
                                            <span v-else>{{ $page.props.auth.user.full_name?.charAt(0) || 'U' }}</span>
                                        </div>
                                        <span class="hidden lg:flex lg:items-center">
                                            <span class="ms-3 text-sm font-bold leading-6 text-gray-900" aria-hidden="true">{{ $page.props.auth.user.full_name }}</span>
                                            <svg class="ms-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                        <UserIcon class="w-4 h-4 text-gray-400" />
                                        الملف الشخصي
                                    </DropdownLink>
                                    <div class="border-t border-gray-100 dark:border-gray-800"></div>
                                    <DropdownLink :href="route('logout')" method="post" as="button" class="flex items-center gap-2 text-red-600 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                        <LogOutIcon class="w-4 h-4" />
                                        تسجيل الخروج
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <main class="py-10 bg-gray-50 flex-1">
                <div class="px-4 sm:px-6 lg:px-8">
                    <!-- Page Heading embedded inside Main if a slot is present -->
                    <header class="mb-5 sm:mb-8" v-if="$slots.header">
                        <slot name="header" />
                    </header>
                    <div class="pb-20 lg:pb-0"> <!-- Add bottom padding for mobile bottom bars if any -->
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
