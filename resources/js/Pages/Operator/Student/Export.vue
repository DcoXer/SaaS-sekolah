<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const format      = ref('xlsx');
const filterGrade  = ref('');
const filterStatus = ref('');

const gradeOptions = [
    { value: '', label: 'Semua Kelas' },
    ...([1, 2, 3, 4, 5, 6].map(g => ({ value: String(g), label: `Kelas ${g}` }))),
];

const statusOptions = [
    { value: '',       label: 'Semua Status' },
    { value: 'active', label: 'Aktif' },
    { value: 'alumni', label: 'Alumni' },
    { value: 'mutasi', label: 'Mutasi' },
];

const doExport = () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('operator.students.export');

    const addField = (name, value) => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = name;
        input.value = value;
        form.appendChild(input);
    };

    const meta = document.querySelector('meta[name="csrf-token"]');
    addField('_token', meta?.content ?? '');
    addField('format', format.value);
    if (filterGrade.value)  addField('grade',  filterGrade.value);
    if (filterStatus.value) addField('status', filterStatus.value);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};
</script>

<template>
    <AppLayout>
        <Head title="Export Data Siswa" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.students.index')" class="hover:text-slate-700">Siswa</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Export</span>
            </div>
        </template>

        <div class="mx-auto max-w-lg space-y-6">

            <div>
                <h2 class="text-lg font-bold text-slate-900">Export Data Siswa</h2>
                <p class="mt-1 text-sm text-slate-500">Unduh data siswa dalam format Excel atau CSV.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">

                <!-- Format -->
                <div>
                    <p class="mb-2 text-xs font-semibold text-slate-600">Format File</p>
                    <div class="flex gap-2">
                        <label
                            v-for="opt in [{ value: 'xlsx', label: 'Excel (.xlsx)' }, { value: 'csv', label: 'CSV' }]"
                            :key="opt.value"
                            :class="[
                                'flex flex-1 cursor-pointer items-center justify-center rounded-lg border px-4 py-2.5 text-sm font-medium transition-[border-color,background-color] duration-150',
                                format === opt.value
                                    ? 'border-emerald-400 bg-emerald-50 text-emerald-700'
                                    : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50',
                            ]"
                        >
                            <input type="radio" :value="opt.value" v-model="format" class="sr-only" />
                            {{ opt.label }}
                        </label>
                    </div>
                </div>

                <!-- Filter Kelas -->
                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">Filter Kelas</label>
                    <FilterSelect
                        v-model="filterGrade"
                        :options="gradeOptions"
                        block
                    />
                </div>

                <!-- Filter Status -->
                <div>
                    <label class="mb-2 block text-xs font-semibold text-slate-600">Filter Status</label>
                    <FilterSelect
                        v-model="filterStatus"
                        :options="statusOptions"
                        block
                    />
                </div>

                <div class="flex items-center justify-end gap-3 pt-1">
                    <Link
                        :href="route('operator.students.index')"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </Link>
                    <button
                        @click="doExport"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Unduh File
                    </button>
                </div>

            </div>

        </div>
    </AppLayout>
</template>
