<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import BackButton from '@/Components/BackButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student:  { type: Object, required: true },
    invoices: { type: Array,  required: true },
});

const statusColor = {
    unpaid:  'bg-red-100 text-red-700 ring-1 ring-red-200',
    partial: 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
    paid:    'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
};
const statusDot = {
    unpaid:  'bg-red-500',
    partial: 'bg-amber-500',
    paid:    'bg-emerald-500',
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

// ── Catat Pembayaran ──────────────────────────────────────────────────────────
const payTarget = ref(null);

const payForm = useForm({
    amount:     '',
    note:       '',
    proof_file: null,
});

const openPay = (inv) => {
    payTarget.value  = inv;
    payForm.amount   = inv.remaining_amount ?? inv.amount;
    payForm.note     = '';
    payForm.proof_file = null;
    payForm.clearErrors();
};

const onProofChange = (e) => {
    payForm.proof_file = e.target.files[0] ?? null;
};

const submitPay = () => {
    payForm.post(route('keuangan.payments.store', payTarget.value.id), {
        forceFormData: true,
        onSuccess: () => {
            payTarget.value = null;
            payForm.reset();
        },
    });
};

// ── Hapus Pembayaran ──────────────────────────────────────────────────────────
const deletePaymentTarget = ref(null);
const deletePaymentForm   = useForm({});

const submitDeletePayment = () => {
    deletePaymentForm.delete(route('keuangan.payments.destroy', deletePaymentTarget.value.id), {
        onSuccess: () => { deletePaymentTarget.value = null; },
    });
};

// ── classroom aktif ──────────────────────────────────────────────────────────
const activeClassroom = props.student.classrooms?.[0];
</script>

<template>
    <AppLayout>
        <Head :title="`Tagihan — ${student.name}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('keuangan.invoices.index')" class="hover:text-slate-700 transition-colors">Tagihan</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ student.name }}</span>
            </div>
        </template>

        <div class="space-y-6">
            <BackButton :href="route('keuangan.invoices.index')" />

            <!-- ── Student Identity Card ────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <!-- Top gradient strip -->
                <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600"></div>

                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Avatar + name -->
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-xl font-extrabold text-white shadow-md shadow-emerald-200/60">
                            {{ student.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ student.name }}</h2>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    <svg class="size-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                    </svg>
                                    NIS {{ student.nis }}
                                </span>
                                <span v-if="activeClassroom" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                    <svg class="size-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                    </svg>
                                    {{ activeClassroom.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary chips -->
                    <div class="flex flex-wrap gap-2 sm:flex-col sm:items-end sm:gap-1.5">
                        <div class="text-right">
                            <p class="text-xs text-slate-400">Total tagihan</p>
                            <p class="text-base font-extrabold tabular-nums text-slate-800">{{ formatRupiah(invoices.reduce((s, i) => s + (i.amount ?? 0), 0)) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Empty State ─────────────────────────────────────────────────── -->
            <div v-if="invoices.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center">
                <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-slate-100">
                    <svg class="size-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Tidak ada tagihan</p>
                <p class="mt-1.5 max-w-xs text-xs text-slate-400">Siswa ini belum memiliki tagihan di tahun ajaran aktif.</p>
            </div>

            <!-- ── Invoice List ────────────────────────────────────────────────── -->
            <div v-else class="space-y-4">
                <div
                    v-for="inv in invoices"
                    :key="inv.id"
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <!-- Invoice header bar -->
                    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Left: name + status + due date -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-bold text-slate-800">{{ inv.payment_type?.name }}</span>
                                    <span :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', statusColor[inv.status]]">
                                        <span :class="['size-1.5 rounded-full', statusDot[inv.status]]"></span>
                                        {{ statusLabel[inv.status] }}
                                    </span>
                                    <!-- Request cash badge -->
                                    <span
                                        v-if="inv.payment_request?.status === 'pending'"
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"
                                        title="Siswa mengajukan pembayaran cash"
                                    >
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Request Cash
                                    </span>
                                </div>
                                <p class="mt-0.5 flex items-center gap-1 text-xs text-slate-400">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                                    </svg>
                                    Jatuh tempo {{ formatDate(inv.due_date) }}
                                </p>
                            </div>
                        </div>

                        <!-- Right: amount + actions -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Amount info -->
                            <div class="mr-1 text-right">
                                <p class="text-base font-extrabold tabular-nums text-slate-800">{{ formatRupiah(inv.amount) }}</p>
                                <p v-if="inv.status !== 'paid'" class="text-xs font-semibold tabular-nums text-red-500">
                                    Sisa {{ formatRupiah(inv.remaining_amount) }}
                                </p>
                                <p v-else class="text-xs font-semibold text-emerald-600">Lunas</p>
                            </div>
                            <!-- Catat bayar -->
                            <button
                                v-if="inv.status !== 'paid'"
                                @click="openPay(inv)"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-emerald-200 transition-all duration-150 hover:bg-emerald-600 hover:shadow-emerald-300 active:scale-95"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Catat Bayar
                            </button>
                            <!-- Kwitansi -->
                            <Link
                                :href="route('keuangan.payments.receipt', inv.id)"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 shadow-sm transition-all duration-150 hover:bg-slate-50 hover:border-slate-300 active:scale-95"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Kwitansi
                            </Link>
                        </div>
                    </div>

                    <!-- Remaining highlight (if partial) -->
                    <div v-if="inv.status === 'partial'" class="flex items-center gap-3 border-b border-amber-100 bg-amber-50/60 px-5 py-2.5">
                        <svg class="size-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <p class="text-xs font-semibold text-amber-700">
                            Masih ada kekurangan sebesar
                            <span class="font-extrabold">{{ formatRupiah(inv.remaining_amount) }}</span>
                            yang belum dibayarkan.
                        </p>
                    </div>

                    <!-- Payment History -->
                    <div class="px-5 py-3">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Riwayat Pembayaran</p>

                        <!-- Empty payments -->
                        <div v-if="!inv.payments?.length" class="flex items-center gap-2.5 rounded-xl border border-dashed border-slate-200 px-4 py-3.5">
                            <svg class="size-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
                            </svg>
                            <p class="text-xs text-slate-400">Belum ada pembayaran yang tercatat untuk tagihan ini.</p>
                        </div>

                        <!-- Timeline payments -->
                        <div v-else class="relative space-y-0">
                            <!-- Timeline line -->
                            <div class="absolute left-4 top-2 bottom-2 w-px bg-slate-100"></div>

                            <div
                                v-for="(pay, idx) in inv.payments"
                                :key="pay.id"
                                class="relative flex items-start gap-3 rounded-xl px-3 py-2.5 transition-colors duration-150 hover:bg-slate-50"
                            >
                                <!-- Timeline dot -->
                                <div class="relative z-10 mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-white ring-2 ring-emerald-400">
                                    <span class="size-2 rounded-full bg-emerald-400"></span>
                                </div>

                                <!-- Content -->
                                <div class="flex flex-1 flex-wrap items-start justify-between gap-2">
                                    <div class="space-y-0.5">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <!-- Method badge -->
                                            <span :class="[
                                                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold uppercase tracking-wide',
                                                pay.method === 'cash'
                                                    ? 'bg-teal-50 text-teal-700 ring-1 ring-teal-100'
                                                    : 'bg-blue-50 text-blue-700 ring-1 ring-blue-100'
                                            ]">
                                                <svg v-if="pay.method === 'cash'" class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
                                                </svg>
                                                <svg v-else class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                                </svg>
                                                {{ pay.method }}
                                            </span>
                                            <span class="text-xs text-slate-500">{{ formatDate(pay.paid_at) }}</span>
                                            <span v-if="pay.note" class="text-xs text-slate-400">· {{ pay.note }}</span>
                                        </div>
                                        <!-- Confirmed by + proof link -->
                                        <div class="flex flex-wrap items-center gap-2 pt-0.5">
                                            <span v-if="pay.confirmed_by" class="flex items-center gap-1 text-xs text-slate-400">
                                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                                {{ pay.confirmed_by?.name ?? 'Staf Keuangan' }}
                                            </span>
                                            <a v-if="pay.proof_url" :href="pay.proof_url" target="_blank" rel="noopener"
                                                class="flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                                </svg>
                                                Bukti Transfer
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Amount + delete -->
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-extrabold tabular-nums text-emerald-600">{{ formatRupiah(pay.amount) }}</span>
                                        <button
                                            @click="deletePaymentTarget = pay"
                                            class="ml-1 flex size-7 items-center justify-center rounded-lg text-slate-300 transition-all duration-150 hover:bg-red-50 hover:text-red-500"
                                            title="Hapus pembayaran"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Total paid row -->
                            <div class="mt-1 flex items-center justify-between border-t border-slate-100 pt-2.5 pl-8">
                                <span class="text-xs font-semibold text-slate-500">Total terbayar</span>
                                <span class="text-sm font-extrabold tabular-nums text-emerald-600">
                                    {{ formatRupiah(inv.payments.reduce((s, p) => s + (p.amount ?? 0), 0)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Modal Catat Pembayaran ───────────────────────────────────────────── -->
        <Modal :show="!!payTarget" max-width="sm" @close="payTarget = null">
            <form @submit.prevent="submitPay">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-100">
                            <svg class="size-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Catat Pembayaran</h3>
                            <p class="text-xs text-slate-500">{{ payTarget?.payment_type?.name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="payTarget = null"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-all duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <!-- Info sisa tagihan -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                        <div class="space-y-1.5 px-4 py-3">
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Total tagihan</span>
                                <span class="font-semibold text-slate-700">{{ formatRupiah(payTarget?.amount) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Sudah dibayar</span>
                                <span class="font-semibold text-emerald-600">{{ formatRupiah((payTarget?.amount ?? 0) - (payTarget?.remaining_amount ?? 0)) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 bg-white px-4 py-2.5 text-xs">
                            <span class="font-semibold text-slate-700">Sisa tagihan</span>
                            <span class="font-extrabold tabular-nums text-red-600">{{ formatRupiah(payTarget?.remaining_amount) }}</span>
                        </div>
                    </div>

                    <!-- Nominal -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal <span class="text-red-500">*</span></label>
                        <input v-model="payForm.amount" type="number" min="1000" placeholder="Contoh: 150000"
                            :class="['w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-all duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', payForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="payForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ payForm.errors.amount }}</p>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input v-model="payForm.note" type="text" placeholder="Mis: Pembayaran bulan Januari..."
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-all duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" />
                    </div>

                    <!-- Bukti -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Bukti Pembayaran <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-4 py-3 text-xs font-semibold text-slate-500 transition-all duration-150 hover:border-emerald-300 hover:bg-emerald-50/30 hover:text-emerald-700">
                            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <span class="truncate">{{ payForm.proof_file ? payForm.proof_file.name : 'Pilih file (jpg/png/pdf, maks 2MB)' }}</span>
                            <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" @change="onProofChange" />
                        </label>
                        <p v-if="payForm.errors.proof_file" class="mt-1.5 text-xs text-red-500">{{ payForm.errors.proof_file }}</p>
                    </div>
                </div>

                <!-- Footer actions -->
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    <button type="button" @click="payTarget = null"
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="payForm.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition-all duration-150 hover:bg-emerald-600 disabled:opacity-60 active:scale-95">
                        <svg v-if="payForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ payForm.processing ? 'Menyimpan...' : 'Simpan Pembayaran' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Modal Hapus Pembayaran ──────────────────────────────────────────── -->
        <Modal :show="!!deletePaymentTarget" max-width="sm" @close="deletePaymentTarget = null">
            <div class="px-6 py-6">
                <div class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-red-100">
                    <svg class="size-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Hapus Pembayaran?</h3>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    Yakin ingin menghapus pembayaran sebesar
                    <span class="font-extrabold tabular-nums text-slate-800">{{ formatRupiah(deletePaymentTarget?.amount) }}</span>?
                    Status tagihan akan diperbarui otomatis setelah penghapusan.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                <button type="button" @click="deletePaymentTarget = null"
                    class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-150 hover:bg-slate-100">
                    Batal
                </button>
                <button @click="submitDeletePayment" :disabled="deletePaymentForm.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-red-200 transition-all duration-150 hover:bg-red-600 disabled:opacity-60 active:scale-95">
                    <svg v-if="deletePaymentForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deletePaymentForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
