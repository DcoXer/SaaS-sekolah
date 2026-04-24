<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    letter: { type: Object, default: null },
    valid:  { type: Boolean, required: true },
});

const formatDate = (d) => d
    ? new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(d))
    : '—';
</script>

<template>
    <Head title="Verifikasi Surat" />

    <div class="min-h-screen bg-slate-50 flex items-start justify-center px-4 py-12">
        <div class="w-full max-w-lg">

            <!-- Header -->
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl"
                     :class="valid ? 'bg-emerald-100' : 'bg-red-100'">
                    <!-- Valid icon -->
                    <svg v-if="valid" class="size-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <!-- Invalid icon -->
                    <svg v-else class="size-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold" :class="valid ? 'text-emerald-700' : 'text-red-600'">
                    {{ valid ? 'Surat Terverifikasi' : 'Surat Tidak Valid' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ valid
                        ? 'Surat ini asli dan telah disetujui oleh Kepala Madrasah.'
                        : 'Kode barcode tidak ditemukan atau surat belum disetujui.' }}
                </p>
            </div>

            <!-- Detail card (hanya jika valid) -->
            <div v-if="valid && letter" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

                <!-- Status banner -->
                <div class="bg-emerald-50 border-b border-emerald-100 px-5 py-3 flex items-center gap-2">
                    <svg class="size-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold text-emerald-700">Surat Resmi — Status: Disetujui</span>
                </div>

                <div class="divide-y divide-slate-100">

                    <!-- Jenis Surat -->
                    <div class="px-5 py-3.5 flex justify-between gap-4">
                        <span class="text-xs font-medium text-slate-400 shrink-0">Jenis Surat</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">
                            {{ letter.letter_template?.letter_type?.name ?? '—' }}
                        </span>
                    </div>

                    <!-- Nama Siswa -->
                    <div class="px-5 py-3.5 flex justify-between gap-4">
                        <span class="text-xs font-medium text-slate-400 shrink-0">Nama Siswa</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">
                            {{ letter.student?.name ?? '—' }}
                        </span>
                    </div>

                    <!-- NIS -->
                    <div class="px-5 py-3.5 flex justify-between gap-4">
                        <span class="text-xs font-medium text-slate-400 shrink-0">NIS</span>
                        <span class="text-sm text-slate-700 text-right tabular-nums">
                            {{ letter.student?.nis ?? '—' }}
                        </span>
                    </div>

                    <!-- Disetujui oleh -->
                    <div class="px-5 py-3.5 flex justify-between gap-4">
                        <span class="text-xs font-medium text-slate-400 shrink-0">Disetujui Oleh</span>
                        <span class="text-sm text-slate-700 text-right">
                            {{ letter.approved_by?.name ?? '—' }}
                        </span>
                    </div>

                    <!-- Tanggal disetujui -->
                    <div class="px-5 py-3.5 flex justify-between gap-4">
                        <span class="text-xs font-medium text-slate-400 shrink-0">Tanggal Disetujui</span>
                        <span class="text-sm text-slate-700 text-right">
                            {{ letter.approved_at ? formatDate(letter.approved_at) : '—' }}
                        </span>
                    </div>

                    <!-- Kode Barcode -->
                    <div class="px-5 py-3.5 flex justify-between gap-4">
                        <span class="text-xs font-medium text-slate-400 shrink-0">Kode Verifikasi</span>
                        <span class="text-xs font-mono text-slate-500 text-right break-all">
                            {{ letter.barcode_code }}
                        </span>
                    </div>

                </div>
            </div>

            <!-- Invalid card -->
            <div v-else-if="!valid" class="rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-center">
                <p class="text-sm text-red-700">
                    Kode barcode yang Anda scan tidak valid atau surat belum mendapatkan persetujuan dari Kepala Madrasah.
                </p>
                <p class="mt-2 text-xs text-red-500">
                    Hubungi pihak sekolah untuk informasi lebih lanjut.
                </p>
            </div>

            <!-- Footer note -->
            <p class="mt-6 text-center text-xs text-slate-400">
                Sistem Manajemen Sekolah &mdash; Verifikasi Surat Resmi
            </p>

        </div>
    </div>
</template>
