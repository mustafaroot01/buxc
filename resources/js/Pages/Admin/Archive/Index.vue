<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArchiveIcon, Undo2Icon } from 'lucide-vue-next';

defineProps<{
    students: any; // Using any for pagination wrapper
}>();

const form = useForm({});

const restoreStudent = (id: string) => {
    if (confirm('Are you sure you want to restore this student to active status?')) {
        form.post(route('admin.archive.restore', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Archive Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <ArchiveIcon class="w-6 h-6 mr-3 text-gray-400" />
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Archive Management Center
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div v-if="($page.props.flash as any)?.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                    {{ ($page.props.flash as any).success }}
                </div>
                
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Archived Students (Soft Deleted)</h3>
                        <span class="bg-gray-200 text-gray-700 py-1 px-3 rounded-full text-xs font-semibold">Historical Data</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">External ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous Group</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archived At</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="student in students.data" :key="student.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ student.first_name }} {{ student.last_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ student.student_external_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ student.group?.name }} ({{ student.group?.stage?.name }})</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(student.deleted_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium border-l border-gray-100">
                                        <button @click="restoreStudent(student.id)" :disabled="form.processing" class="text-teal-600 hover:text-indigo-900 flex items-center justify-end w-full disabled:opacity-50 transition-colors">
                                            <Undo2Icon class="w-4 h-4 mr-1.5" /> Restore
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="students.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center text-sm text-gray-500 bg-gray-50/50">
                                        <ArchiveIcon class="mx-auto h-12 w-12 text-gray-300 mb-3" />
                                        No archived students found in the historical data.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
