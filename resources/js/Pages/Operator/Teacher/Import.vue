<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    previewData: { type: Object, default: null },
});

const step      = ref(props.previewData ? 2 : 1);
const preview   = ref(props.previewData ?? null);
const uploading = ref(false);
const fileInput = ref(null);
const dragOver  = ref(false);
const selectedFile = ref(null);

const page = usePage();

// ── Step 1 ────────────────────────────────────────────────────────────────────

const onFileChange = (e) => {
    const file = e.target.files?.[0];
    if (file) selectedFile.value = file;
};

const onDrop = (e) => {
    dragOver.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) selectedFile.value = file;
};

const uploadPreview = () => {
    if (!selectedFile.value) return;
    uploading.value = true;

    const formData = new FormData();
    formData.append('file', selectedFile.value);

    router.post(route('operator.teachers.import.preview'), formData, {
        forceFormData: true,
        onSuccess: () => {
            preview.value = page.props.previewData;
            step.value = 2;
            uploading.value = false;
        },
        onError: () => {
            uploading.value = false;
        },
    });
};

// ── Step 2 ────────────────────────────────────────────────────────────────────

const confirming = ref(false);

const confirmImport = () => {
    confirming.value = true;
    router.post(route('operator.teachers.import.confirm'), {
        temp_path: preview.value.tempPath,
    }, {
        onFinish: () => {
            confirming.value = false;
        },
    });
};

const resetToStep1 = () => {
    step.value = 1;
    preview.value = null;
    selectedFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

// ── Helpers ───────────────────────────────────────────────────────────────────

const hasErrors  = computed(() => preview.value?.errors?.length > 0);
const existingSet = computed(() => new Set(preview.value?.existingNips ?? []));

const typeLabel = (t) => t === 'guru_kelas' ? 'Guru Kelas' : t === 'guru_bidang' ? 'Guru Bidang' : t;
const genderLabel = (g) => g === 'L' ? 'Laki-laki' : g === 'P' ? 'Perempuan' : g;
</script>

<template>
    <AppLayout>
        <Head title="Import Data Guru" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.teachers.index')" class="hover:text-slate-700">Guru</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Import</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Step indicator -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div :class="['flex size-7 items-center justify-center rounded-full text-xs font-bold', step === 1 ? 'bg-primary-500 text-white' : 'bg-primary-100 text-primary-700']">1</div>
                    <span :class="['text-sm font-semibold', step === 1 ? 'text-slate-900' : 'text-slate-500']">Upload File</span>
                </div>
                <div class="h-px flex-1 bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <div :class="['flex size-7 items-center justify-center rounded-full text-xs font-bold', step === 2 ? 'bg-primary-500 text-white' : 'bg-slate-100 text-slate-400']">2</div>
                    <span :class="['text-sm font-semibold', step === 2 ? 'text-slate-900' : 'text-slate-400']">Preview & Konfirmasi</span>
                </div>
            </div>

            <!-- ── Step 1 ── -->
            <template v-if="step === 1">
                <div class="mx-auto max-w-lg space-y-5">

                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Import Data Guru</h2>
                        <p class="mt-1 text-sm text-slate-500">Upload file Excel (.xlsx) atau CSV berisi data guru.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">

                        <div v-if="$page.props.errors?.file" class="rounded-lg bg-red-50 p-3 text-sm text-red-600">
                            {{ $page.props.errors.file }}
                        </div>

                        <!-- Drop zone -->
                        <div
                            @dragover.prevent="dragOver = true"
                            @dragleave="dragOver = false"
                            @drop.prevent="onDrop"
                            @click="fileInput.click()"
                            :class="[
                                'flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 text-center transition-[border-color,background-color] duration-150',
                                dragOver ? 'border-primary-400 bg-primary-50' : 'border-slate-200 hover:border-primary-300 hover:bg-slate-50',
                            ]"
                        >
                            <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <p v-if="selectedFile" class="text-sm font-semibold text-primary-700">{{ selectedFile.name }}</p>
                            <p v-else class="text-sm font-semibold text-slate-700">Klik atau seret file ke sini</p>
                            <p class="mt-1 text-xs text-slate-400">Format: .xlsx, .xls, .csv — Maks. 5 MB</p>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                class="sr-only"
                                @change="onFileChange"
                            />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <a
                                :href="route('operator.teachers.import.template')"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:underline"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Download Template
                            </a>

                            <div class="flex gap-3">
                                <Link
                                    :href="route('operator.teachers.index')"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                                >
                                    Batal
                                </Link>
                                <button
                                    :disabled="!selectedFile || uploading"
                                    @click="uploadPreview"
                                    class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-50"
                                >
                                    <svg v-if="uploading" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    {{ uploading ? 'Memproses...' : 'Upload & Preview' }}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </template>

            <!-- ── Step 2 ── -->
            <template v-else-if="step === 2 && preview">

                <div v-if="hasErrors" class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="mb-2 text-sm font-semibold text-red-700">Ditemukan {{ preview.errors.length }} error. Perbaiki file dan upload ulang.</p>
                    <ul class="space-y-1">
                        <li v-for="(err, i) in preview.errors" :key="i" class="text-xs text-red-600">{{ err.message }}</li>
                    </ul>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="font-semibold text-slate-700">{{ preview.rows.length }} baris ditemukan</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-700 ring-1 ring-primary-200">
                        Baru: {{ preview.rows.filter(r => !existingSet.has(r.nip)).length }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                        Update: {{ preview.rows.filter(r => existingSet.has(r.nip)).length }}
                    </span>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-xs">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">#</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">Status</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">NIP</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">Nama</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">Kelamin</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">No. HP</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">Tipe</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-500">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(row, i) in preview.rows" :key="i" class="hover:bg-slate-50">
                                    <td class="px-3 py-2.5 text-slate-400">{{ i + 1 }}</td>
                                    <td class="px-3 py-2.5">
                                        <span v-if="existingSet.has(row.nip)" class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Update</span>
                                        <span v-else class="inline-flex rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700 ring-1 ring-primary-200">Baru</span>
                                    </td>
                                    <td class="px-3 py-2.5 font-mono text-slate-700">{{ row.nip || '—' }}</td>
                                    <td class="px-3 py-2.5 font-semibold text-slate-800">{{ row.name }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ genderLabel(row.gender) || '—' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ row.phone || '—' }}</td>
                                    <td class="px-3 py-2.5">
                                        <span v-if="row.type" class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                            :class="row.type === 'guru_kelas' ? 'bg-primary-50 text-primary-700 ring-primary-200' : 'bg-violet-50 text-violet-700 ring-violet-200'">
                                            {{ typeLabel(row.type) }}
                                        </span>
                                        <span v-else class="text-slate-400">—</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ row.position || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <button
                        @click="resetToStep1"
                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Upload Ulang
                    </button>

                    <button
                        v-if="!hasErrors"
                        :disabled="confirming"
                        @click="confirmImport"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                    >
                        <svg v-if="confirming" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ confirming ? 'Mengimpor...' : 'Konfirmasi Import' }}
                    </button>
                </div>

            </template>

        </div>
    </AppLayout>
</template>
