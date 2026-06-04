<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, inject, onMounted } from 'vue';

const addToast = inject('addToast', () => {});

// ── Load Midtrans Snap hanya di halaman ini ───────────────────────────────────
const midtrans = usePage().props.midtrans;
onMounted(() => {
    if (document.getElementById('midtrans-snap-js')) return; // sudah di-load
    const script = document.createElement('script');
    script.id  = 'midtrans-snap-js';
    script.src = midtrans?.is_production
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', midtrans?.client_key ?? '');
    document.head.appendChild(script);
});

const props = defineProps({
    invoices:      { type: Array,   required: true },
    hasExamAccess: { type: Boolean, required: true },
    activeYear:    { type: Object,  default: null },
});

// ── Status config ─────────────────────────────────────────────────────────────
const statusConfig = {
    unpaid:  { label: 'Belum Bayar', badge: 'bg-red-100 text-red-700 ring-red-200' },
    partial: { label: 'Kurang Bayar', badge: 'bg-amber-100 text-amber-700 ring-amber-200' },
    paid:    { label: 'Lunas',        badge: 'bg-emerald-100 text-emerald-700 ring-emerald-200' },
};

// ── Pilih metode bayar ────────────────────────────────────────────────────────
const methodTarget = ref(null);

const openMethodModal = (invoice) => { methodTarget.value = invoice; };

// ── Cash request ──────────────────────────────────────────────────────────────
const cashForm = useForm({});

const submitCash = () => {
    cashForm.post(route('siswa.payments.request-cash', methodTarget.value.id), {
        onSuccess: () => { methodTarget.value = null; },
    });
};

// ── Midtrans payment ──────────────────────────────────────────────────────────
const paying = ref(null);

const payOnline = async () => {
    const invoice = methodTarget.value;
    methodTarget.value = null;

    if (paying.value) return;
    paying.value = invoice.id;

    try {
        const { data } = await window.axios.post(route('siswa.payments.initiate', invoice.id));

        if (data.snap_token) {
            const orderId = data.order_id;
            const finishBase = route('siswa.payments.finish');

            window.snap.pay(data.snap_token, {
                onSuccess: () => {
                    paying.value = null;
                    window.location.href = `${finishBase}?order_id=${orderId}&transaction_status=settlement`;
                },
                onPending: () => {
                    paying.value = null;
                    window.location.href = `${finishBase}?order_id=${orderId}&transaction_status=pending`;
                },
                onError:   () => {
                    paying.value = null;
                    addToast('Pembayaran gagal. Silakan coba lagi.', 'error');
                },
                onClose:   () => {
                    paying.value = null;
                    addToast('Pembayaran dibatalkan.', 'error');
                },
            });
        } else {
            addToast('Gagal memulai sesi pembayaran. Silakan coba lagi.', 'error');
            paying.value = null;
        }
    } catch {
        addToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
        paying.value = null;
    }
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val ?? 0);

const formatDate = (val) => {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const totalUnpaid = computed(() =>
    props.invoices
        .filter(i => i.status !== 'paid')
        .reduce((acc, i) => acc + (i.amount - (i.payments?.reduce((s, p) => s + p.amount, 0) ?? 0)), 0)
);

// ── Search ────────────────────────────────────────────────────────────────────
const search = ref('');
const filteredInvoices = computed(() => {
    if (!search.value.trim()) return props.invoices;
    const q = search.value.toLowerCase();
    return props.invoices.filter(i => i.payment_type?.name?.toLowerCase().includes(q));
});
</script>

<template>
    <AppLayout>
        <Head title="Tagihan" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Siswa</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tagihan</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- ── Summary Banner ──────────────────────────────────────────── -->
            <!-- Ada tunggakan -->
            <div
                v-if="invoices.length > 0 && totalUnpaid > 0"
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-600 via-red-600 to-rose-700 p-5 shadow-lg shadow-red-200"
            >
                <!-- decorative circles -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-6 right-16 size-24 rounded-full bg-white/10"></div>

                <div class="relative flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="relative flex size-2.5">
                                <span class="absolute inline-flex size-full animate-ping rounded-full bg-white opacity-60"></span>
                                <span class="relative inline-flex size-2.5 rounded-full bg-white"></span>
                            </span>
                            <p class="text-xs font-semibold uppercase tracking-widest text-red-100">Ada Tunggakan</p>
                        </div>
                        <p class="mt-2 text-2xl font-extrabold tabular-nums text-white drop-shadow-sm">{{ formatRupiah(totalUnpaid) }}</p>
                        <p class="mt-1 text-sm text-red-100">
                            {{ invoices.filter(i => i.status !== 'paid').length }} tagihan belum lunas
                            <span v-if="activeYear"> &middot; {{ activeYear.name }}</span>
                        </p>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                        <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Semua lunas -->
            <div
                v-else-if="invoices.length > 0 && totalUnpaid === 0"
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600 p-5 shadow-lg shadow-emerald-200"
            >
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-6 right-16 size-24 rounded-full bg-white/10"></div>

                <div class="relative flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-100">Status Pembayaran</p>
                        <p class="mt-2 text-xl font-extrabold text-white">Semua Tagihan Lunas</p>
                        <p class="mt-1 text-sm text-emerald-100">
                            {{ invoices.length }} tagihan telah diselesaikan
                            <span v-if="activeYear"> &middot; {{ activeYear.name }}</span>
                        </p>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                        <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Heading fallback (no invoices) -->
            <div v-else>
                <h2 class="text-balance text-lg font-bold text-slate-900">Tagihan Saya</h2>
                <p class="text-pretty text-sm text-slate-500">
                    {{ activeYear ? `Tahun ajaran: ${activeYear.name}` : 'Belum ada tahun ajaran aktif.' }}
                </p>
            </div>

            <!-- ── Exam access warning ─────────────────────────────────────── -->
            <div
                v-if="!hasExamAccess"
                class="flex items-start gap-4 rounded-2xl border-2 border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-4 shadow-sm"
            >
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-red-800">Akses Ujian Ditangguhkan</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-red-700">
                        Terdapat tagihan ujian yang belum dilunasi. Segera selesaikan pembayaran untuk mendapatkan kembali akses ujian.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="inline-flex items-center rounded-full bg-red-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm">Terkunci</span>
                </div>
            </div>

            <!-- ── Search bar ─────────────────────────────────────────────── -->
            <div v-if="invoices.length > 0" class="relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 size-4.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Cari jenis tagihan..."
                    class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none transition-all duration-200 focus:border-rose-400 focus:bg-white focus:shadow-md focus:ring-4 focus:ring-rose-400/15"
                />
            </div>

            <!-- ── Empty state ────────────────────────────────────────────── -->
            <div
                v-if="invoices.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-16 text-center"
            >
                <div class="mb-4 flex size-14 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="size-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Tidak ada tagihan</p>
                <p class="mt-1 text-xs text-slate-400">Belum ada tagihan untuk tahun ajaran ini.</p>
            </div>

            <div v-else-if="filteredInvoices.length === 0" class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-12 text-center">
                <div class="mb-3 flex size-12 items-center justify-center rounded-xl bg-slate-100">
                    <svg class="size-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Tidak ada hasil pencarian</p>
                <button @click="search = ''" class="mt-2 text-xs font-semibold text-rose-600 hover:underline">Reset pencarian</button>
            </div>

            <template v-else>
                <!-- ── Mobile cards ───────────────────────────────────────── -->
                <div class="space-y-3 sm:hidden">
                    <div
                        v-for="invoice in filteredInvoices"
                        :key="invoice.id"
                        class="overflow-hidden rounded-2xl border bg-white shadow-sm transition-shadow duration-200 hover:shadow-md"
                        :class="invoice.status === 'paid'
                            ? 'border-emerald-100 bg-gradient-to-br from-white to-emerald-50/40 opacity-80'
                            : 'border-slate-200'"
                    >
                        <!-- Card header -->
                        <div class="px-4 pt-4 pb-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="truncate text-sm font-bold text-slate-800">{{ invoice.payment_type?.name }}</p>
                                        <span
                                            v-if="invoice.payment_type?.is_exam_related"
                                            class="inline-flex shrink-0 items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-bold text-red-600 ring-1 ring-red-200"
                                        >Ujian</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">
                                        <span class="font-medium text-slate-500">Jatuh tempo:</span> {{ formatDate(invoice.due_date) }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1"
                                    :class="statusConfig[invoice.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                >
                                    {{ statusConfig[invoice.status]?.label ?? invoice.status }}
                                </span>
                            </div>

                            <!-- Amount -->
                            <div class="mt-3 flex items-baseline gap-1.5">
                                <p
                                    class="tabular-nums text-xl font-extrabold"
                                    :class="invoice.status === 'paid' ? 'text-emerald-700' : 'text-slate-900'"
                                >{{ formatRupiah(invoice.amount) }}</p>
                                <svg v-if="invoice.status === 'paid'" class="size-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                        </div>

                        <!-- Card actions -->
                        <div class="flex items-center gap-2 border-t border-slate-100 bg-slate-50/60 px-4 py-3">
                            <Link
                                :href="route('siswa.payments.receipt', invoice.id)"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-semibold text-slate-600 shadow-sm transition-all duration-150 hover:border-slate-300 hover:bg-slate-50 hover:shadow"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                                Kwitansi
                            </Link>
                            <span
                                v-if="invoice.status !== 'paid' && invoice.payment_request?.status === 'pending'"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-amber-100 py-2.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Datang ke Sekolah
                            </span>
                            <button
                                v-else-if="invoice.status !== 'paid'"
                                @click="openMethodModal(invoice)"
                                :disabled="paying === invoice.id"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 py-2.5 text-xs font-bold text-white shadow-sm shadow-red-200 transition-all duration-150 hover:from-rose-600 hover:to-red-700 hover:shadow-md hover:shadow-red-300 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg v-if="paying === invoice.id" class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <svg v-else class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                {{ paying === invoice.id ? 'Memproses...' : 'Bayar Sekarang' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── Desktop table ──────────────────────────────────────── -->
                <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:block">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-800 to-slate-700">
                                <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-300">Jenis Tagihan</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-300">Nominal</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-300">Jatuh Tempo</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-300">Status</th>
                                <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="invoice in filteredInvoices"
                                :key="invoice.id"
                                class="transition-colors duration-150"
                                :class="invoice.status === 'paid'
                                    ? 'bg-emerald-50/50 opacity-75 hover:bg-emerald-50 hover:opacity-100'
                                    : 'hover:bg-rose-50/30'"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-800">{{ invoice.payment_type?.name }}</span>
                                        <span
                                            v-if="invoice.payment_type?.is_exam_related"
                                            class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-bold text-red-600 ring-1 ring-red-200"
                                        >Ujian</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="tabular-nums text-sm font-bold"
                                        :class="invoice.status === 'paid' ? 'text-emerald-700' : 'text-slate-900'"
                                    >{{ formatRupiah(invoice.amount) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="tabular-nums text-sm text-slate-500">{{ formatDate(invoice.due_date) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1"
                                        :class="statusConfig[invoice.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                    >
                                        {{ statusConfig[invoice.status]?.label ?? invoice.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('siswa.payments.receipt', invoice.id)"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition-all duration-150 hover:border-slate-300 hover:bg-slate-50 hover:shadow"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                            </svg>
                                            Kwitansi
                                        </Link>
                                        <span
                                            v-if="invoice.status !== 'paid' && invoice.payment_request?.status === 'pending'"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Datang ke Sekolah
                                        </span>
                                        <button
                                            v-else-if="invoice.status !== 'paid'"
                                            @click="openMethodModal(invoice)"
                                            :disabled="paying === invoice.id"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-rose-500 to-red-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm shadow-red-200 transition-all duration-150 hover:from-rose-600 hover:to-red-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            <svg v-if="paying === invoice.id" class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            {{ paying === invoice.id ? 'Memproses...' : 'Bayar' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

        </div>

        <!-- ── Modal Pilih Metode Pembayaran ─────────────────────────────────── -->
        <Modal :show="!!methodTarget" max-width="sm" @close="methodTarget = null">
            <!-- Modal header gradient -->
            <div class="relative overflow-hidden rounded-t-xl bg-gradient-to-br from-rose-600 via-red-600 to-rose-700 px-6 py-5">
                <div class="pointer-events-none absolute -right-6 -top-6 size-28 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-4 right-12 size-16 rounded-full bg-white/10"></div>
                <div class="relative flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-white">Pilih Metode Pembayaran</h3>
                        <p class="mt-0.5 text-xs text-red-100">{{ methodTarget?.payment_type?.name }}</p>
                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-white/20 px-3 py-1.5 backdrop-blur-sm">
                            <svg class="size-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                            <span class="text-xs font-bold tabular-nums text-white">{{ formatRupiah(methodTarget?.amount) }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        aria-label="Tutup modal"
                        @click="methodTarget = null"
                        class="flex size-8 items-center justify-center rounded-lg bg-white/20 text-white backdrop-blur-sm transition-colors duration-150 hover:bg-white/30"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Option cards -->
            <div class="space-y-3 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Pilih cara bayar</p>

                <!-- Cash option card -->
                <button
                    type="button"
                    @click="submitCash"
                    :disabled="cashForm.processing"
                    class="group flex w-full items-center gap-4 rounded-2xl border-2 border-slate-200 bg-white p-4 text-left transition-all duration-200 hover:border-amber-400 hover:bg-amber-50/50 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 transition-colors duration-200 group-hover:bg-amber-200">
                        <svg class="size-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800 group-hover:text-amber-900">Bayar Tunai (Cash)</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-slate-400 group-hover:text-amber-700">Kirim permintaan lalu datang ke kantor TU sekolah untuk melunasi tagihan</p>
                    </div>
                    <div class="shrink-0 rounded-full border-2 border-slate-200 p-0.5 transition-colors duration-200 group-hover:border-amber-400">
                        <div class="size-3 rounded-full transition-colors duration-200 group-hover:bg-amber-400"></div>
                    </div>
                </button>

                <!-- Online option card -->
                <button
                    type="button"
                    @click="payOnline"
                    class="group flex w-full items-center gap-4 rounded-2xl border-2 border-slate-200 bg-white p-4 text-left transition-all duration-200 hover:border-emerald-400 hover:bg-emerald-50/50 hover:shadow-md"
                >
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 transition-colors duration-200 group-hover:bg-emerald-200">
                        <svg class="size-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800 group-hover:text-emerald-900">Bayar Online</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-slate-400 group-hover:text-emerald-700">Transfer bank, kartu kredit, QRIS, dompet digital via Midtrans — langsung lunas</p>
                    </div>
                    <div class="shrink-0 rounded-full border-2 border-slate-200 p-0.5 transition-colors duration-200 group-hover:border-emerald-400">
                        <div class="size-3 rounded-full transition-colors duration-200 group-hover:bg-emerald-400"></div>
                    </div>
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
