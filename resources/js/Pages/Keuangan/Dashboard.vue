<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend);

const props = defineProps({
    stats:           { type: Object, required: true },
    monthlyPayments: { type: Array,  default: () => [] },
    recentPayments:  { type: Array,  default: () => [] },
    academicYears:   { type: Array,  default: () => [] },
});

const user     = computed(() => usePage().props.auth.user);
const hour     = new Date().getHours();
const greeting = hour < 12 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 19 ? 'Selamat sore' : 'Selamat malam';
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

const formatRupiah = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount ?? 0);

const formatCompact = (amount) => {
    if (!amount) return 'Rp 0';
    if (amount >= 1_000_000_000) return `Rp ${(amount / 1_000_000_000).toFixed(1)}M`;
    if (amount >= 1_000_000)     return `Rp ${(amount / 1_000_000).toFixed(1)}jt`;
    if (amount >= 1_000)         return `Rp ${(amount / 1_000).toFixed(0)}rb`;
    return `Rp ${amount}`;
};

const totalInvoices = computed(() =>
    (props.stats.unpaid || 0) + (props.stats.partial || 0) + (props.stats.paid || 0)
);

const doughnutData = computed(() => ({
    labels: ['Belum Bayar', 'Bayar Sebagian', 'Lunas'],
    datasets: [{
        data: [props.stats.unpaid || 0, props.stats.partial || 0, props.stats.paid || 0],
        backgroundColor: ['#fca5a5', '#fcd34d', '#6ee7b7'],
        borderColor:     ['#ef4444', '#f59e0b', '#10b981'],
        borderWidth: 2,
        hoverOffset: 4,
    }],
}));

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: { font: { size: 11 }, padding: 12, boxWidth: 10, boxHeight: 10 },
        },
    },
};

const barData = computed(() => ({
    labels: props.monthlyPayments.map(m => m.label),
    datasets: [{
        label: 'Pembayaran',
        data:  props.monthlyPayments.map(m => m.total),
        backgroundColor: 'rgba(16, 185, 129, 0.85)',
        borderRadius: 6,
        borderSkipped: false,
    }],
}));

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: { label: ctx => ` ${formatCompact(ctx.parsed.y)}` },
        },
    },
    scales: {
        x: {
            grid:   { display: false },
            border: { display: false },
            ticks:  { font: { size: 11 }, color: '#94a3b8' },
        },
        y: {
            grid:   { color: '#f1f5f9' },
            border: { display: false },
            ticks:  { font: { size: 10 }, color: '#94a3b8', callback: v => formatCompact(v) },
        },
    },
};

const methodLabel = { cash: 'Tunai', midtrans: 'Midtrans' };

const activeYear   = computed(() => props.academicYears.find(y => y.status === 'active'));
const exportYearId = ref(activeYear.value?.id ?? '');
const exporting    = ref(false);

const doExport = () => {
    if (!exportYearId.value) return;
    exporting.value = true;
    const a  = document.createElement('a');
    a.href   = `/keuangan/reports/export?academic_year_id=${exportYearId.value}`;
    a.click();
    setTimeout(() => { exporting.value = false; }, 2000);
};

const quickActions = [
    { label: 'Tagihan',       href: '/keuangan/invoices',      bg: 'bg-emerald-50 group-hover:bg-emerald-100', text: 'text-emerald-600', icon: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z' },
    { label: 'Pembayaran',    href: '/keuangan/payments',      bg: 'bg-sky-50 group-hover:bg-sky-100',         text: 'text-sky-600',     icon: 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z' },
    { label: 'Honor Guru',    href: '/keuangan/honorariums',   bg: 'bg-amber-50 group-hover:bg-amber-100',     text: 'text-amber-600',   icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Jenis Tagihan', href: '/keuangan/payment-types', bg: 'bg-violet-50 group-hover:bg-violet-100',   text: 'text-violet-600',  icon: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z' },
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Keuangan" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-6">

            <!-- Welcome banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500 p-6 text-white shadow-lg">
                <div class="pointer-events-none absolute -right-8 -top-8 size-48 rounded-full bg-white/10 blur-2xl" />
                <div class="pointer-events-none absolute -bottom-10 right-16 size-36 rounded-full bg-teal-300/20 blur-2xl" />
                <div class="pointer-events-none absolute left-1/3 -top-4 size-28 rounded-full bg-emerald-300/20 blur-xl" />

                <div class="relative flex items-start gap-4">
                    <div class="size-14 shrink-0 overflow-hidden rounded-2xl shadow-md ring-2 ring-white/30">
                        <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="size-full object-cover" />
                        <div v-else class="flex size-full items-center justify-center bg-white/20 text-lg font-bold text-white backdrop-blur-sm">
                            {{ initials }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-emerald-100">{{ greeting }},</p>
                        <h2 class="truncate text-xl font-extrabold tracking-tight">{{ user.name }}</h2>
                        <div class="mt-1.5 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold backdrop-blur-sm">
                                Staff Keuangan
                            </span>
                            <span v-if="activeYear" class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium text-emerald-100 backdrop-blur-sm">
                                {{ activeYear.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats strip -->
                <div class="relative mt-5 grid grid-cols-3 gap-2">
                    <div class="rounded-xl bg-white/15 p-3 text-center backdrop-blur-sm">
                        <p class="tabular-nums text-2xl font-extrabold">{{ totalInvoices }}</p>
                        <p class="mt-0.5 text-xs text-emerald-100">Total Tagihan</p>
                    </div>
                    <div class="rounded-xl bg-white/15 p-3 text-center backdrop-blur-sm">
                        <p class="tabular-nums text-2xl font-extrabold">{{ stats.paid ?? 0 }}</p>
                        <p class="mt-0.5 text-xs text-emerald-100">Lunas</p>
                    </div>
                    <div class="rounded-xl bg-white/15 p-3 text-center backdrop-blur-sm">
                        <p class="tabular-nums text-2xl font-extrabold">{{ (stats.unpaid ?? 0) + (stats.partial ?? 0) }}</p>
                        <p class="mt-0.5 text-xs text-emerald-100">Belum Lunas</p>
                    </div>
                </div>
            </div>

            <!-- Status + nominal cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl border border-red-100 bg-red-50 p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-500">Belum Bayar</p>
                    <p class="tabular-nums mt-1.5 text-3xl font-extrabold text-red-700">{{ stats.unpaid ?? 0 }}</p>
                    <p class="mt-0.5 text-xs text-red-400">tagihan</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-500">Sebagian</p>
                    <p class="tabular-nums mt-1.5 text-3xl font-extrabold text-amber-700">{{ stats.partial ?? 0 }}</p>
                    <p class="mt-0.5 text-xs text-amber-400">tagihan</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-500">Lunas</p>
                    <p class="tabular-nums mt-1.5 text-3xl font-extrabold text-emerald-700">{{ stats.paid ?? 0 }}</p>
                    <p class="mt-0.5 text-xs text-emerald-400">tagihan</p>
                </div>
                <div class="col-span-2 rounded-2xl bg-gradient-to-r from-slate-800 to-slate-700 p-4 shadow-sm sm:col-span-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Masuk</p>
                    <p class="tabular-nums mt-1.5 text-lg font-extrabold text-white">{{ formatCompact(stats.total_amount) }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">total pembayaran</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2">
                    <div class="mb-4 border-b border-slate-50 pb-3">
                        <h3 class="text-sm font-bold text-slate-800">Status Tagihan</h3>
                        <p class="text-xs text-slate-400">Distribusi pembayaran</p>
                    </div>
                    <div class="h-44">
                        <Doughnut :data="doughnutData" :options="doughnutOptions" />
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-3">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-50 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Pembayaran 6 Bulan Terakhir</h3>
                            <p class="text-xs text-slate-400">Total nominal per bulan</p>
                        </div>
                    </div>
                    <div class="h-44">
                        <Bar :data="barData" :options="barOptions" />
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-slate-800">Menu Cepat</h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <Link
                        v-for="action in quickActions"
                        :key="action.label"
                        :href="action.href"
                        class="group flex flex-col items-center gap-2.5 rounded-2xl border border-slate-100 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div class="flex size-11 items-center justify-center rounded-2xl transition-colors duration-150" :class="[action.bg, action.text]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="action.icon" />
                            </svg>
                        </div>
                        <span class="text-center text-xs font-semibold text-slate-600 group-hover:text-slate-800">{{ action.label }}</span>
                    </Link>
                </div>
            </div>

            <!-- Export laporan -->
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Export Laporan Keuangan</h3>
                        <p class="text-xs text-slate-400">Download rekap tagihan & pembayaran per tahun ajaran (.xlsx)</p>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <select
                        v-model="exportYearId"
                        class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="" disabled>Pilih Tahun Ajaran</option>
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                            {{ year.name }}{{ year.status === 'active' ? ' (Aktif)' : year.status === 'closed' ? ' (Selesai)' : '' }}
                        </option>
                    </select>
                    <button
                        @click="doExport"
                        :disabled="!exportYearId || exporting"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-emerald-600 hover:to-teal-600 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <svg v-if="exporting" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        {{ exporting ? 'Mengunduh...' : 'Download Excel' }}
                    </button>
                </div>
            </div>

            <!-- Recent payments -->
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-50 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Pembayaran Terbaru</h3>
                        <p class="text-xs text-slate-400">Transaksi yang baru masuk</p>
                    </div>
                    <Link href="/keuangan/payments" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                        Lihat semua →
                    </Link>
                </div>

                <div v-if="recentPayments.length === 0" class="flex flex-col items-center py-14 text-center">
                    <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-500">Belum ada pembayaran</p>
                    <p class="mt-1 text-xs text-slate-400">Transaksi yang masuk akan muncul di sini</p>
                </div>

                <div v-else class="divide-y divide-slate-50">
                    <div
                        v-for="payment in recentPayments"
                        :key="payment.id"
                        class="flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50/60"
                    >
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-xs font-bold text-white shadow-sm">
                            {{ payment.student_name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ payment.student_name }}</p>
                            <p class="text-xs text-slate-400">{{ payment.payment_type }} · {{ payment.paid_at }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="tabular-nums text-sm font-bold text-slate-800">{{ formatRupiah(payment.amount) }}</span>
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                :class="payment.method === 'midtrans'
                                    ? 'bg-indigo-100 text-indigo-600'
                                    : 'bg-emerald-100 text-emerald-600'"
                            >
                                {{ methodLabel[payment.method] ?? payment.method }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
