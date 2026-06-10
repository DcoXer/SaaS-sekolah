<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    configs:      { type: Array,  required: true },
    academicYear: { type: Object, default: null },
});

// Inisialisasi 4 baris predikat (A–D) — isi dari props.configs jika ada
const defaultRows = [
    { predicate: 'A', min_score: 90, max_score: 100 },
    { predicate: 'B', min_score: 75, max_score: 89 },
    { predicate: 'C', min_score: 60, max_score: 74 },
    { predicate: 'D', min_score: 0,  max_score: 59 },
];

function buildRows() {
    return defaultRows.map((def) => {
        const existing = props.configs.find(c => c.predicate === def.predicate);
        return {
            predicate: def.predicate,
            min_score: existing ? existing.min_score : def.min_score,
            max_score: existing ? existing.max_score : def.max_score,
        };
    });
}

const syncForm = useForm({
    configs: buildRows(),
});

watch(() => props.configs, () => {
    syncForm.configs = buildRows();
});

const submitSync = () => {
    if (!props.academicYear) return;
    syncForm.post(route('operator.predicate-configs.sync', props.academicYear.id), {
        onSuccess: () => {},
    });
};

const predicateColor = {
    A: 'bg-primary-100 text-primary-700',
    B: 'bg-blue-100 text-blue-700',
    C: 'bg-amber-100 text-amber-700',
    D: 'bg-red-100 text-red-700',
};
</script>

<template>
    <AppLayout>
        <Head title="Konfigurasi Predikat" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Konfigurasi Predikat</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Konfigurasi Predikat</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Atur rentang nilai untuk predikat A, B, C, dan D pada tahun ajaran aktif.
                    </p>
                </div>
                <button
                    v-if="academicYear"
                    @click="submitSync"
                    :disabled="syncForm.processing"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                >
                    <svg v-if="syncForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                    </svg>
                    {{ syncForm.processing ? 'Menyimpan...' : 'Simpan Konfigurasi' }}
                </button>
            </div>

            <!-- No active year -->
            <div
                v-if="!academicYear"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
                <p class="mt-1 text-xs text-slate-400">Aktifkan tahun ajaran terlebih dahulu untuk mengatur predikat.</p>
            </div>

            <!-- Config table -->
            <div v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <!-- Academic year info -->
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                    </svg>
                    <span class="text-sm font-semibold text-slate-700">{{ academicYear.name }}</span>
                    <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700">Aktif</span>
                </div>

                <!-- Header row (desktop only) -->
                <div class="hidden sm:grid grid-cols-4 border-b border-slate-100 bg-slate-50 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <span>Predikat</span>
                    <span>Nilai Minimum</span>
                    <span>Nilai Maksimum</span>
                    <span class="text-right">Rentang</span>
                </div>

                <!-- Rows -->
                <div class="divide-y divide-slate-100">
                    <div
                        v-for="(row, i) in syncForm.configs"
                        :key="row.predicate"
                        class="px-5 py-3"
                    >
                        <!-- Mobile layout -->
                        <div class="flex items-center gap-3 sm:hidden">
                            <span :class="['inline-flex size-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold', predicateColor[row.predicate]]">
                                {{ row.predicate }}
                            </span>
                            <div class="flex flex-1 items-center gap-2">
                                <div class="flex-1">
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Min</label>
                                    <input
                                        v-model.number="syncForm.configs[i].min_score"
                                        type="number" min="0" max="100"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20"
                                    />
                                </div>
                                <span class="mt-5 text-xs text-slate-400">—</span>
                                <div class="flex-1">
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Max</label>
                                    <input
                                        v-model.number="syncForm.configs[i].max_score"
                                        type="number" min="0" max="100"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Desktop layout -->
                        <div class="hidden sm:grid grid-cols-4 items-center gap-4">
                            <!-- Predicate badge -->
                            <div class="flex items-center gap-2">
                                <span :class="['inline-flex size-8 items-center justify-center rounded-lg text-sm font-bold', predicateColor[row.predicate]]">
                                    {{ row.predicate }}
                                </span>
                            </div>

                            <!-- Min score -->
                            <div>
                                <input
                                    v-model.number="syncForm.configs[i].min_score"
                                    type="number"
                                    min="0"
                                    max="100"
                                    :class="[
                                        'w-28 rounded-lg border bg-white px-3 py-2 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        'border-slate-200',
                                    ]"
                                />
                            </div>

                            <!-- Max score -->
                            <div>
                                <input
                                    v-model.number="syncForm.configs[i].max_score"
                                    type="number"
                                    min="0"
                                    max="100"
                                    :class="[
                                        'w-28 rounded-lg border bg-white px-3 py-2 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        'border-slate-200',
                                    ]"
                                />
                            </div>

                            <!-- Range display -->
                            <div class="text-right text-sm text-slate-500">
                                {{ row.min_score }} – {{ row.max_score }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info footer -->
                <div class="border-t border-slate-100 bg-amber-50 px-5 py-3">
                    <p class="text-xs text-amber-700">
                        <span class="font-semibold">Catatan:</span> Pastikan rentang nilai tidak overlap dan mencakup 0–100 secara keseluruhan.
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
