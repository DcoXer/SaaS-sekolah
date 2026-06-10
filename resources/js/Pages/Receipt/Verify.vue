<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    valid:        { type: Boolean, required: true },
    invoice:      { type: Object, default: null },
    student:      { type: Object, default: null },
    payment_type: { type: Object, default: null },
    total_paid:   { type: Number, default: 0 },
    remaining:    { type: Number, default: 0 },
    status:       { type: String, default: null },
    confirmed_by: { type: String, default: null },
    confirmed_at: { type: String, default: null },
});

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val ?? 0);

const statusColor = {
    unpaid:  'bg-red-100 text-red-700',
    partial: 'bg-amber-100 text-amber-700',
    paid:    'bg-primary-100 text-primary-700',
};
const statusLabel = {
    unpaid:  'Belum Lunas',
    partial: 'Kurang Bayar',
    paid:    'Lunas',
};
</script>

<template>
    <Head :title="valid ? 'Kwitansi Valid' : 'Kwitansi Tidak Valid'" />

    <div class="flex min-h-dvh flex-col items-center justify-center bg-slate-50 px-4 py-12">
        <div class="w-full max-w-md">

            <!-- Logo / Brand -->
            <div class="mb-6 text-center">
                <p class="text-xs font-semibold uppercase text-slate-400">Sistem Manajemen Sekolah</p>
                <p class="mt-1 text-sm text-slate-500">Verifikasi Kwitansi Pembayaran</p>
            </div>

            <!-- Invalid state -->
            <div v-if="!valid" class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm">
                <div class="flex flex-col items-center px-8 py-10 text-center">
                    <div class="flex size-16 items-center justify-center rounded-full bg-red-100">
                        <svg class="size-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h1 class="mt-4 text-lg font-bold text-slate-900">Kwitansi Tidak Valid</h1>
                    <p class="mt-2 text-sm text-slate-500">
                        Kode verifikasi tidak ditemukan atau kwitansi ini belum dikonfirmasi oleh petugas.
                    </p>
                </div>
            </div>

            <!-- Valid state -->
            <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <!-- Status banner -->
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-full bg-primary-100">
                            <svg class="size-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Kwitansi Terverifikasi</p>
                            <p class="text-xs text-slate-400">Dokumen asli dari sistem</p>
                        </div>
                    </div>
                    <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', statusColor[status]]">
                        {{ statusLabel[status] }}
                    </span>
                </div>

                <!-- Detail -->
                <div class="px-6 py-5 space-y-4">
                    <!-- Jenis tagihan -->
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-400">Jenis Tagihan</p>
                        <p class="mt-0.5 text-base font-bold text-slate-900">{{ payment_type?.name }}</p>
                    </div>

                    <!-- Siswa -->
                    <div class="rounded-lg bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase text-slate-400">Data Siswa</p>
                        <div class="mt-2 space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Nama</span>
                                <span class="font-semibold text-slate-800">{{ student?.name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">NIS</span>
                                <span class="tabular-nums font-semibold text-slate-800">{{ student?.nis }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rincian pembayaran -->
                    <div class="rounded-lg bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase text-slate-400">Rincian Pembayaran</p>
                        <div class="mt-2 space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Total tagihan</span>
                                <span class="tabular-nums font-semibold text-slate-800">{{ formatRupiah(invoice?.amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Terbayar</span>
                                <span class="tabular-nums font-semibold text-primary-600">{{ formatRupiah(total_paid) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 pt-1.5 text-sm">
                                <span class="font-semibold text-slate-700">Sisa</span>
                                <span :class="['tabular-nums font-bold', remaining > 0 ? 'text-red-600' : 'text-primary-600']">
                                    {{ formatRupiah(remaining) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Konfirmasi -->
                    <div v-if="confirmed_by" class="rounded-lg border border-primary-100 bg-primary-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase text-primary-600">Dikonfirmasi oleh</p>
                        <p class="mt-0.5 text-sm font-bold text-slate-800">{{ confirmed_by }}</p>
                        <p v-if="confirmed_at" class="text-xs text-slate-500">{{ confirmed_at }}</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 px-6 py-4 text-center">
                    <p class="text-xs text-slate-400">Dokumen ini terverifikasi secara digital oleh sistem</p>
                </div>
            </div>

        </div>
    </div>
</template>
