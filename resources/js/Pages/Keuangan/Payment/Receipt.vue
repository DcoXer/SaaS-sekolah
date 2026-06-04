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
    paid:    'bg-emerald-100 text-emerald-700',
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
        <Head :title="`Kwitansi — ${student?.name}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('keuangan.invoices.index')" class="hover:text-slate-700">Tagihan</Link>
                <span>/</span>
                <Link :href="route('keuangan.invoices.show', student?.id)" class="hover:text-slate-700">{{ student?.name }}</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Kwitansi</span>
            </div>
        </template>

        <div class="space-y-5">
            <BackButton />

            <!-- Hero Header Banner -->
            <div
                :class="[
                    'relative overflow-hidden rounded-2xl p-6 sm:p-8',
                    status === 'paid'
                        ? 'bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-700'
                        : status === 'partial'
                            ? 'bg-gradient-to-br from-amber-400 via-amber-500 to-orange-600'
                            : 'bg-gradient-to-br from-red-400 via-red-500 to-rose-700'
                ]"
            >
                <!-- Background decorative circles -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-10 -left-6 size-32 rounded-full bg-white/5"></div>

                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Status icon -->
                        <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm ring-2 ring-white/30">
                            <!-- Paid: centang -->
                            <svg v-if="status === 'paid'" class="size-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Partial: setengah -->
                            <svg v-else-if="status === 'partial'" class="size-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Unpaid: X -->
                            <svg v-else class="size-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white/75">{{ payment_type?.name }}</p>
                            <h1 class="text-2xl font-bold text-white">{{ student?.name }}</h1>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-0.5 text-xs font-semibold text-white ring-1 ring-white/30">
                                    {{ statusLabel[status] }}
                                </span>
                                <span v-if="student?.nis" class="text-xs text-white/70">NIS: {{ student.nis }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Download PDF button -->
                    <a
                        :href="`/keuangan/invoices/${invoice?.id}/receipt/pdf`"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/20 px-5 py-2.5 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur-sm transition-all duration-150 hover:bg-white/30 hover:ring-white/50 sm:w-auto"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Unduh PDF
                    </a>
                </div>
            </div>

            <!-- Info chips row -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <!-- Tanggal jatuh tempo -->
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="size-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500">Jatuh Tempo</p>
                        <p class="truncate text-sm font-semibold text-slate-800">{{ formatDate(invoice?.due_date) }}</p>
                    </div>
                </div>
                <!-- Metode pembayaran terakhir -->
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-violet-50">
                        <svg class="size-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500">Metode Terakhir</p>
                        <p class="truncate text-sm font-semibold capitalize text-slate-800">
                            {{ invoice?.payments?.length ? invoice.payments[invoice.payments.length - 1].method : '-' }}
                        </p>
                    </div>
                </div>
                <!-- Dikonfirmasi oleh -->
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                        <svg class="size-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500">Dikonfirmasi Oleh</p>
                        <p class="truncate text-sm font-semibold text-slate-800">{{ confirmed_by ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Main receipt card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <!-- Breakdown nominal -->
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Ringkasan Tagihan</p>
                    <div class="space-y-2">
                        <!-- Total tagihan -->
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="size-2 rounded-full bg-slate-300"></div>
                                <span class="text-sm text-slate-600">Total Tagihan</span>
                            </div>
                            <span class="tabular-nums text-sm font-semibold text-slate-700">{{ formatRupiah(invoice?.amount) }}</span>
                        </div>
                        <!-- Terbayar -->
                        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="size-2 rounded-full bg-emerald-400"></div>
                                <span class="text-sm text-emerald-700">Sudah Dibayar</span>
                            </div>
                            <span class="tabular-nums text-sm font-bold text-emerald-600">{{ formatRupiah(total_paid) }}</span>
                        </div>
                        <!-- Sisa -->
                        <div
                            :class="[
                                'flex items-center justify-between rounded-xl px-4 py-3',
                                remaining > 0 ? 'bg-red-50' : 'bg-emerald-50'
                            ]"
                        >
                            <div class="flex items-center gap-2.5">
                                <div :class="['size-2 rounded-full', remaining > 0 ? 'bg-red-400' : 'bg-emerald-400']"></div>
                                <span :class="['text-sm font-semibold', remaining > 0 ? 'text-red-700' : 'text-emerald-700']">
                                    Sisa Tagihan
                                </span>
                            </div>
                            <span :class="['tabular-nums text-sm font-bold', remaining > 0 ? 'text-red-600' : 'text-emerald-600']">
                                {{ formatRupiah(remaining) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Data siswa -->
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Data Siswa</p>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-xs text-slate-500">Nama Lengkap</dt>
                            <dd class="mt-0.5 font-semibold text-slate-800">{{ student?.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">NIS</dt>
                            <dd class="mt-0.5 font-semibold text-slate-800">{{ student?.nis ?? '-' }}</dd>
                        </div>
                        <div v-if="invoice?.student?.classrooms?.[0]">
                            <dt class="text-xs text-slate-500">Kelas</dt>
                            <dd class="mt-0.5 font-semibold text-slate-800">{{ invoice.student.classrooms[0].name }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Riwayat pembayaran -->
                <div class="px-6 py-5">
                    <p class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Riwayat Pembayaran</p>

                    <div v-if="!invoice?.payments?.length" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 py-8 text-center">
                        <svg class="mb-2 size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        <p class="text-sm text-slate-400">Belum ada pembayaran tercatat.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal</th>
                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Metode</th>
                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan</th>
                                    <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="pay in invoice.payments"
                                    :key="pay.id"
                                    class="border-b border-slate-50 last:border-0"
                                >
                                    <td class="py-3 text-slate-600">{{ formatDate(pay.paid_at) }}</td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center rounded-lg bg-violet-50 px-2.5 py-0.5 text-xs font-semibold capitalize text-violet-700">
                                            {{ pay.method }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-slate-400">{{ pay.note ?? '-' }}</td>
                                    <td class="py-3 text-right font-bold tabular-nums text-emerald-600">{{ formatRupiah(pay.amount) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-slate-200">
                                    <td colspan="3" class="pt-3 text-xs font-bold uppercase tracking-wide text-slate-500">Total Terbayar</td>
                                    <td class="pt-3 text-right text-base font-bold tabular-nums text-emerald-600">{{ formatRupiah(total_paid) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tanda tangan & verifikasi -->
                <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-6">
                    <div class="grid grid-cols-3 gap-4">
                        <!-- Wali Murid -->
                        <div class="text-center">
                            <p class="text-xs font-medium text-slate-500">Wali Murid / Siswa,</p>
                            <div class="mx-auto mt-3 h-14 w-full"></div>
                            <div class="mx-auto mb-1.5 h-px w-4/5 bg-slate-300"></div>
                            <p class="truncate text-xs font-semibold text-slate-700">{{ wali_name ?? '________________________' }}</p>
                        </div>

                        <!-- QR / Kode Verifikasi -->
                        <div class="text-center">
                            <div v-if="receipt_code" class="flex flex-col items-center gap-2">
                                <div class="flex size-20 items-center justify-center rounded-xl border-2 border-dashed border-emerald-200 bg-emerald-50">
                                    <svg class="size-9 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75V16.5zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400">QR tersedia di PDF</p>
                                <a :href="verify_url" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:underline">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                    Verifikasi
                                </a>
                            </div>
                            <div v-else class="flex flex-col items-center gap-1 pt-5">
                                <svg class="size-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z" />
                                </svg>
                                <p class="text-xs text-slate-400">Belum ada QR</p>
                            </div>
                        </div>

                        <!-- Petugas TU -->
                        <div class="text-center">
                            <p class="text-xs font-medium text-slate-500">Petugas TU Keuangan,</p>
                            <div class="mx-auto mt-3 h-14 w-full"></div>
                            <div class="mx-auto mb-1.5 h-px w-4/5 bg-slate-300"></div>
                            <p class="truncate text-xs font-semibold text-slate-700">{{ confirmed_by ?? '________________________' }}</p>
                        </div>
                    </div>

                    <!-- Kode verifikasi pill -->
                    <div v-if="receipt_code" class="mt-5 flex items-center justify-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-5 py-2.5">
                        <svg class="size-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span class="font-mono text-xs font-semibold tracking-widest text-emerald-700">{{ receipt_code }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-100 px-6 py-3 text-center">
                    <p class="text-xs text-slate-400">
                        Dokumen digenerate otomatis oleh sistem &middot;
                        {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
