<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    receiptData: { type: Object, required: true },
});

const { invoice, student, payment_type, total_paid, remaining, status, receipt_code, verify_url, confirmed_by, wali_name } = props.receiptData;

const statusColor = {
    unpaid:  'bg-red-100 text-red-700',
    partial: 'bg-amber-100 text-amber-700',
    paid:    'bg-primary-100 text-primary-700',
};
const statusLabel = {
    unpaid:  'Belum Bayar',
    partial: 'Kurang Bayar',
    paid:    'Lunas',
};

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val ?? 0);

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Kwitansi — ${payment_type?.name}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/siswa/invoices" class="hover:text-slate-700">Tagihan</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Kwitansi</span>
            </div>
        </template>

        <div class="space-y-4">
            <BackButton href="/siswa/invoices" />

            <!-- Action bar -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Kwitansi Pembayaran</h2>
                    <p class="text-sm text-slate-500">{{ payment_type?.name }}</p>
                </div>
                <a
                    :href="`/siswa/invoices/${invoice?.id}/receipt/pdf`"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-[background-color] duration-150 hover:bg-slate-50"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh PDF
                </a>
            </div>

            <!-- Receipt card -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">

                <!-- Header -->
                <div class="border-b border-slate-100 px-4 py-6 text-center sm:px-8">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Kwitansi Pembayaran</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ payment_type?.name }}</h1>
                    <div class="mt-2 flex items-center justify-center gap-2">
                        <span :class="['inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold', statusColor[status]]">
                            {{ statusLabel[status] }}
                        </span>
                    </div>
                </div>

                <!-- Info -->
                <div class="grid grid-cols-1 divide-y divide-slate-100 border-b border-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                    <div class="px-4 py-5 sm:px-8">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Data Siswa</p>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">Nama</dt>
                                <dd class="text-right font-semibold text-slate-800">{{ student?.name }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">NIS</dt>
                                <dd class="font-semibold text-slate-800">{{ student?.nis }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="px-4 py-5 sm:px-8">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Detail Tagihan</p>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">Total tagihan</dt>
                                <dd class="font-semibold text-slate-800">{{ formatRupiah(invoice?.amount) }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">Terbayar</dt>
                                <dd class="font-semibold text-primary-600">{{ formatRupiah(total_paid) }}</dd>
                            </div>
                            <div class="flex justify-between gap-4 border-t border-slate-100 pt-2">
                                <dt class="font-semibold text-slate-700">Sisa</dt>
                                <dd :class="['font-bold', remaining > 0 ? 'text-red-600' : 'text-primary-600']">{{ formatRupiah(remaining) }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">Jatuh tempo</dt>
                                <dd class="font-semibold text-slate-800">{{ formatDate(invoice?.due_date) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Riwayat pembayaran -->
                <div class="px-4 py-5 sm:px-8">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Riwayat Pembayaran</p>
                    <div v-if="!invoice?.payments?.length" class="text-sm text-slate-400">
                        Belum ada pembayaran.
                    </div>
                    <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-left">
                                <th class="pb-2 text-xs font-semibold text-slate-500">Tanggal</th>
                                <th class="pb-2 text-xs font-semibold text-slate-500">Metode</th>
                                <th class="pb-2 text-right text-xs font-semibold text-slate-500">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="pay in invoice.payments" :key="pay.id">
                                <td class="py-2 text-slate-600">{{ formatDate(pay.paid_at) }}</td>
                                <td class="py-2 capitalize text-slate-600">{{ pay.method }}</td>
                                <td class="py-2 text-right font-semibold text-primary-600">{{ formatRupiah(pay.amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200">
                                <td colspan="2" class="pt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Total Terbayar</td>
                                <td class="pt-2 text-right font-bold text-primary-600">{{ formatRupiah(total_paid) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>

                <!-- Tanda Tangan & Verifikasi -->
                <div class="border-t border-slate-100 px-4 py-6 sm:px-8">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-xs text-slate-500">Wali Murid / Siswa,</p>
                            <div class="mx-auto mt-2 h-14 w-full"></div>
                            <div class="mx-auto mb-1 h-px w-4/5 bg-slate-300"></div>
                            <p class="truncate text-xs font-semibold text-slate-700">{{ wali_name ?? '________________________' }}</p>
                        </div>
                        <div class="text-center">
                            <div v-if="receipt_code" class="flex flex-col items-center gap-1">
                                <div class="flex size-20 items-center justify-center rounded-lg border border-slate-200 bg-slate-50">
                                    <svg class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75V16.5zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400">QR ada di PDF</p>
                                <a :href="verify_url" target="_blank"
                                   class="text-xs font-semibold text-primary-600 hover:underline">
                                    Verifikasi
                                </a>
                            </div>
                            <div v-else class="flex flex-col items-center gap-1 pt-4">
                                <p class="text-xs text-slate-400">Belum ada pembayaran</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-slate-500">Petugas TU Keuangan,</p>
                            <div class="mx-auto mt-2 h-14 w-full"></div>
                            <div class="mx-auto mb-1 h-px w-4/5 bg-slate-300"></div>
                            <p class="truncate text-xs font-semibold text-slate-700">{{ confirmed_by ?? '________________________' }}</p>
                        </div>
                    </div>
                    <div v-if="receipt_code" class="mt-4 flex items-center justify-center gap-2 rounded-lg bg-slate-50 px-4 py-2">
                        <svg class="size-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span class="font-mono text-xs text-slate-500">{{ receipt_code }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-100 px-4 py-4 text-center sm:px-8">
                    <p class="text-xs text-slate-400">Dokumen ini digenerate otomatis oleh sistem · {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

