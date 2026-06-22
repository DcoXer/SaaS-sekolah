<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const sanitizeHtml = (html) => {
    if (!html) return '';
    const doc = new DOMParser().parseFromString(html, 'text/html');
    doc.querySelectorAll('script, iframe, object, embed').forEach(el => el.remove());
    doc.querySelectorAll('*').forEach(el => {
        [...el.attributes].forEach(attr => {
            if (attr.name.toLowerCase().startsWith('on')) el.removeAttribute(attr.name);
        });
    });
    return doc.body.innerHTML;
};

const props = defineProps({
    waitingLetters:  { type: Array, required: true },
    approvedLetters: { type: Array, required: true },
    rejectedLetters: { type: Array, required: true },
});

// ── View content modal ────────────────────────────────────────────────────────
const showContent   = ref(false);
const viewedLetter  = ref(null);

const openContent = (letter) => {
    viewedLetter.value = letter;
    showContent.value  = true;
};

// ── Approve ───────────────────────────────────────────────────────────────────
const approveForm    = useForm({});
const showApproveConfirm = ref(false);
const selectedLetter = ref(null);

const openApproveConfirm = (letter) => {
    selectedLetter.value      = letter;
    showApproveConfirm.value  = true;
};

const confirmApprove = () => {
    approveForm.patch(route('kamad.letters.approve', selectedLetter.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showApproveConfirm.value = false;
            selectedLetter.value     = null;
        },
    });
};

// ── Reject ────────────────────────────────────────────────────────────────────
const showRejectModal = ref(false);
const rejectTarget    = ref(null);

const rejectForm = useForm({
    rejection_note: '',
});

const openReject = (letter) => {
    rejectTarget.value      = letter;
    rejectForm.reset();
    rejectForm.clearErrors();
    showRejectModal.value   = true;
};

const submitReject = () => {
    rejectForm.patch(route('kamad.letters.reject', rejectTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectTarget.value    = null;
        },
    });
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <AppLayout>
        <Head title="Surat Masuk" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Kamad</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Surat Masuk</span>
            </div>
        </template>

        <div class="space-y-8">

            <!-- Page heading -->
            <div>
                <h2 class="text-balance text-lg font-bold text-slate-900">Surat Masuk</h2>
                <p class="text-pretty text-sm text-slate-500">
                    Setujui atau tolak permohonan surat keterangan dari wali murid.
                </p>
            </div>

            <!-- ── Menunggu Persetujuan ────────────────────────────────────────── -->
            <section class="space-y-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-slate-700">Menunggu Persetujuan</h3>
                    <span
                        v-if="waitingLetters.length > 0"
                        class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-amber-200"
                    >
                        {{ waitingLetters.length }}
                    </span>
                </div>

                <div
                    v-if="waitingLetters.length === 0"
                    class="rounded-xl border border-dashed border-slate-200 bg-white px-5 py-8 text-center"
                >
                    <p class="text-sm text-slate-400">Tidak ada surat yang menunggu persetujuan.</p>
                </div>

                <!-- Mobile cards -->
                <div class="sm:hidden space-y-2">
                    <div
                        v-for="letter in waitingLetters"
                        :key="letter.id"
                        class="overflow-hidden rounded-xl border border-amber-200 bg-white shadow-sm"
                    >
                        <div class="p-4">
                            <p class="text-sm font-semibold text-slate-800">{{ letter.letter_template?.letter_type?.name ?? '—' }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Siswa: <span class="font-medium text-slate-700">{{ letter.student?.name ?? '—' }}</span>
                            </p>
                            <p class="text-xs text-slate-400">{{ formatDate(letter.created_at) }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 border-t border-slate-100 px-4 py-3">
                            <button @click="openContent(letter)" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Lihat
                            </button>
                            <button @click="openApproveConfirm(letter)" class="inline-flex items-center gap-1 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-600">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Setujui
                            </button>
                            <button @click="openReject(letter)" class="inline-flex items-center gap-1 rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Jenis Surat</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Siswa</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Diminta</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tanggal</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="letter in waitingLetters" :key="letter.id" class="transition-[background-color] duration-150 hover:bg-slate-50">
                                <td class="px-5 py-4"><span class="text-sm font-medium text-slate-800">{{ letter.letter_template?.letter_type?.name ?? '—' }}</span></td>
                                <td class="px-5 py-4"><span class="text-sm text-slate-700">{{ letter.student?.name ?? '—' }}</span></td>
                                <td class="px-5 py-4"><span class="text-sm text-slate-500">{{ letter.requested_by?.name ?? '—' }}</span></td>
                                <td class="px-5 py-4"><span class="tabular-nums text-sm text-slate-500">{{ formatDate(letter.created_at) }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openContent(letter)" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            Lihat
                                        </button>
                                        <button @click="openApproveConfirm(letter)" class="inline-flex items-center gap-1 rounded-lg bg-primary-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                            Setujui
                                        </button>
                                        <button @click="openReject(letter)" class="inline-flex items-center gap-1 rounded-lg bg-red-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-red-600">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ── Sudah Disetujui ─────────────────────────────────────────────── -->
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Sudah Disetujui</h3>

                <div
                    v-if="approvedLetters.length === 0"
                    class="rounded-xl border border-dashed border-slate-200 bg-white px-5 py-8 text-center"
                >
                    <p class="text-sm text-slate-400">Belum ada surat yang disetujui.</p>
                </div>

                <!-- Mobile cards -->
                <div class="sm:hidden space-y-2">
                    <div v-for="letter in approvedLetters" :key="letter.id" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="p-4">
                            <p class="text-sm font-semibold text-slate-800">{{ letter.letter_template?.letter_type?.name ?? '—' }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ letter.student?.name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ formatDate(letter.approved_at) }}</p>
                        </div>
                        <div class="flex gap-2 border-t border-slate-100 px-4 py-2.5">
                            <button @click="openContent(letter)" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Lihat
                            </button>
                            <a :href="route('kamad.letters.pdf', letter.id)" target="_blank"
                               class="inline-flex items-center gap-1 rounded-lg bg-primary-500 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-primary-600">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Jenis Surat</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Siswa</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tanggal Disetujui</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="letter in approvedLetters" :key="letter.id" class="transition-[background-color] duration-150 hover:bg-slate-50">
                                <td class="px-5 py-4"><span class="text-sm font-medium text-slate-800">{{ letter.letter_template?.letter_type?.name ?? '—' }}</span></td>
                                <td class="px-5 py-4"><span class="text-sm text-slate-700">{{ letter.student?.name ?? '—' }}</span></td>
                                <td class="px-5 py-4"><span class="tabular-nums text-sm text-slate-500">{{ formatDate(letter.approved_at) }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openContent(letter)" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            Lihat
                                        </button>
                                        <a :href="route('kamad.letters.pdf', letter.id)" target="_blank"
                                           class="inline-flex items-center gap-1 rounded-lg bg-primary-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                            Unduh PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ── Ditolak ─────────────────────────────────────────────────────── -->
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Ditolak</h3>

                <div
                    v-if="rejectedLetters.length === 0"
                    class="rounded-xl border border-dashed border-slate-200 bg-white px-5 py-8 text-center"
                >
                    <p class="text-sm text-slate-400">Tidak ada surat yang ditolak.</p>
                </div>

                <!-- Mobile cards -->
                <div class="sm:hidden space-y-2">
                    <div v-for="letter in rejectedLetters" :key="letter.id" class="overflow-hidden rounded-xl border border-red-100 bg-white shadow-sm">
                        <div class="flex items-start justify-between p-4">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ letter.letter_template?.letter_type?.name ?? '—' }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ letter.student?.name ?? '—' }}</p>
                                <p v-if="letter.rejection_note" class="mt-1 text-pretty text-xs text-red-600">{{ letter.rejection_note }}</p>
                            </div>
                            <button @click="openContent(letter)" class="ml-2 inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 shrink-0">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Lihat
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Jenis Surat</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Siswa</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Alasan Penolakan</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="letter in rejectedLetters" :key="letter.id" class="transition-[background-color] duration-150 hover:bg-slate-50">
                                <td class="px-5 py-4"><span class="text-sm font-medium text-slate-800">{{ letter.letter_template?.letter_type?.name ?? '—' }}</span></td>
                                <td class="px-5 py-4"><span class="text-sm text-slate-700">{{ letter.student?.name ?? '—' }}</span></td>
                                <td class="px-5 py-4 max-w-xs"><span class="text-pretty text-sm text-red-600">{{ letter.rejection_note ?? '—' }}</span></td>
                                <td class="px-5 py-4 text-right">
                                    <button @click="openContent(letter)" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        Lihat
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

        <!-- ── View Content Modal ─────────────────────────────────────────────── -->
        <Modal :show="showContent" max-width="lg" @close="showContent = false">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Isi Surat</h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ viewedLetter?.letter_template?.letter_type?.name }} · {{ viewedLetter?.student?.name }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showContent = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <div
                        class="prose prose-sm max-h-96 min-h-32 overflow-y-auto whitespace-pre-wrap rounded-lg border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700"
                        v-html="sanitizeHtml(viewedLetter?.content)"
                    />
                </div>
                <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                    <button
                        @click="showContent = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </Modal>

        <!-- ── Approve Confirm Modal ──────────────────────────────────────────── -->
        <Modal :show="showApproveConfirm" max-width="sm" @close="showApproveConfirm = false">
            <div class="px-6 py-5">
                <div class="mb-4 flex items-start gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-100">
                        <svg class="size-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Setujui Surat?</h3>
                        <p class="mt-1 text-pretty text-sm text-slate-500">
                            Surat <strong class="text-slate-700">{{ selectedLetter?.letter_template?.letter_type?.name }}</strong>
                            atas nama <strong class="text-slate-700">{{ selectedLetter?.student?.name }}</strong>
                            akan disetujui dan barcode verifikasi akan digenerate.
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showApproveConfirm = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="button" :disabled="approveForm.processing" @click="confirmApprove"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60">
                        <svg v-if="approveForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ approveForm.processing ? 'Memproses...' : 'Ya, Setujui' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- ── Reject Modal ───────────────────────────────────────────────────── -->
        <Modal :show="showRejectModal" max-width="md" @close="showRejectModal = false">
            <form @submit.prevent="submitReject">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Tolak Surat</h3>
                    <button type="button" @click="showRejectModal = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <p class="text-pretty text-sm text-slate-500">
                        Surat <strong class="text-slate-700">{{ rejectTarget?.letter_template?.letter_type?.name }}</strong>
                        atas nama <strong class="text-slate-700">{{ rejectTarget?.student?.name }}</strong>
                        akan ditolak.
                    </p>

                    <div>
                        <label for="rejection_note" class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="rejection_note"
                            v-model="rejectForm.rejection_note"
                            rows="4"
                            placeholder="Jelaskan alasan penolakan (min. 10 karakter)..."
                            :class="[
                                'w-full resize-none rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                'focus:border-red-400 focus:ring-2 focus:ring-red-400/20',
                                rejectForm.errors.rejection_note ? 'border-red-400' : 'border-slate-200',
                            ]"
                        />
                        <p v-if="rejectForm.errors.rejection_note" class="mt-1.5 text-xs text-red-500">
                            {{ rejectForm.errors.rejection_note }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="showRejectModal = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="rejectForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60">
                        <svg v-if="rejectForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ rejectForm.processing ? 'Memproses...' : 'Tolak Surat' }}
                    </button>
                </div>
            </form>
        </Modal>

    </AppLayout>
</template>
