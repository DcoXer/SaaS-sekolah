<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
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
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

const formatCompact = (amount) => {
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
        data: [props.stats.unpaid, props.stats.partial, props.stats.paid],
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
        backgroundColor: 'rgba(99, 102, 241, 0.85)',
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

// ── Export ────────────────────────────────────────────────────────────────────
const activeYear   = computed(() => props.academicYears.find(y => y.status === 'active'));
const exportYearId = ref(activeYear.value?.id ?? '');
const exporting    = ref(false);

const doExport = () => {
    if (!exportYearId.value) return;
    exporting.value = true;
    const url = `/keuangan/reports/export?academic_year_id=${exportYearId.value}`;
    const a   = document.createElement('a');
    a.href    = url;
    a.click();
    setTimeout(() => { exporting.value = false; }, 2000);
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Keuangan" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="size-11 shrink-0 overflow-hidden rounded-2xl shadow-sm">
                        <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="size-full object-cover" />
                        <div v-else class="flex size-full items-center justify-center bg-gradient-to-br from-sky-500 to-cyan-600 text-sm font-bold text-white">
                            {{ initials }}
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ greeting }}, {{ user.name.split(' ')[0] }}
                        </h2>
                        <p class="text-xs text-slate-400">Ringkasan keuangan sekolah</p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-200">
                    Staff Keuangan
                </span>
            </div>

            <!-- Total collected hero card -->
            <div class="rounded-2xl bg-gradient-to-br from-sky-600 to-cyan-600 p-6 text-white shadow-sm">
                <p class="text-sm font-medium text-sky-100">Total Pembayaran Masuk</p>
                <p class="tabular-nums mt-1 text-3xl font-bold">{{ formatRupiah(stats.total_amount) }}</p>
                <p class="mt-2 text-xs text-sky-200">Dari {{ totalInvoices }} tagihan terdaftar</p>
            </div>

            <!-- Status cards -->
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center">
                    <p class="text-xs font-medium text-slate-400">Belum Bayar</p>
                    <p class="tabular-nums mt-1 text-2xl font-bold text-red-500">{{ stats.unpaid ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center">
                    <p class="text-xs font-medium text-slate-400">Sebagian</p>
                    <p class="tabular-nums mt-1 text-2xl font-bold text-amber-500">{{ stats.partial ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center">
                    <p class="text-xs font-medium text-slate-400">Lunas</p>
                    <p class="tabular-nums mt-1 text-2xl font-bold text-emerald-500">{{ stats.paid ?? 0 }}</p>
                </div>
            </div>

            <!-- Charts row -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">

                <!-- Doughnut -->
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2">
                    <h3 class="mb-1 text-sm font-semibold text-slate-800">Status Tagihan</h3>
                    <p class="mb-4 text-xs text-slate-400">Distribusi pembayaran</p>
                    <div class="h-44">
                        <Doughnut :data="doughnutData" :options="doughnutOptions" />
                    </div>
                </div>

                <!-- Monthly bar -->
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-3">
                    <h3 class="mb-1 text-sm font-semibold text-slate-800">Pembayaran 6 Bulan Terakhir</h3>
                    <p class="mb-4 text-xs text-slate-400">Total nominal per bulan</p>
                    <div class="h-44">
                        <Bar :data="barData" :options="barOptions" />
                    </div>
                </div>

            </div>

            <!-- Export Laporan -->
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-slate-800">Export Laporan Keuangan</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Download rekap tagihan & pembayaran per tahun ajaran (.xlsx)</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <select
                        v-model="exportYearId"
                        class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    >
                        <option value="" disabled>Pilih Tahun Ajaran</option>
                        <option
                            v-for="year in academicYears"
                            :key="year.id"
                            :value="year.id"
                        >
                            {{ year.name }}
                            <template v-if="year.status === 'active'"> (Aktif)</template>
                            <template v-else-if="year.status === 'closed'"> (Selesai)</template>
                        </option>
                    </select>

                    <button
                        @click="doExport"
                        :disabled="!exportYearId || exporting"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <svg v-if="exporting" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Pembayaran Terbaru</h3>
                </div>

                <div v-if="recentPayments.length === 0" class="px-5 py-10 text-center">
                    <p class="text-sm text-slate-400">Belum ada pembayaran tercatat.</p>
                </div>

                <div v-else class="divide-y divide-slate-50">
                    <div
                        v-for="payment in recentPayments"
                        :key="payment.id"
                        class="flex items-center gap-3 px-5 py-3.5"
                    >
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">
                            {{ payment.student_name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ payment.student_name }}</p>
                            <p class="text-xs text-slate-400">{{ payment.payment_type }} · {{ payment.paid_at }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="tabular-nums text-sm font-semibold text-slate-800">{{ formatRupiah(payment.amount) }}</span>
                            <span
                                class="rounded-md px-1.5 py-0.5 text-xs font-medium"
                                :class="payment.method === 'midtrans' ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600'"
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
