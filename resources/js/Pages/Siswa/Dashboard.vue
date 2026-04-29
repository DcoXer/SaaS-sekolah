<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeYear:     { type: String, default: null },
    student:        { type: Object, default: null },
    stats:          { type: Object, required: true },
    unpaidInvoices: { type: Array,  default: () => [] },
});

const user     = computed(() => usePage().props.auth.user);
const hour     = new Date().getHours();
const greeting = hour < 12 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 19 ? 'Selamat sore' : 'Selamat malam';
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

const formatRupiah = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

const totalUnpaid = computed(() =>
    props.unpaidInvoices.reduce((sum, inv) => sum + inv.amount, 0)
);

const statusConfig = {
    unpaid:  { label: 'Belum Bayar', badge: 'bg-red-100 text-red-700' },
    partial: { label: 'Bayar Sebagian', badge: 'bg-amber-100 text-amber-700' },
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-sm font-bold text-white shadow-sm">
                        {{ initials }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ greeting }}, {{ user.name.split(' ')[0] }}
                        </h2>
                        <p class="text-xs text-slate-400">
                            {{ activeYear ?? 'Belum ada tahun ajaran aktif' }}
                        </p>
                    </div>
                </div>
                <!-- Student info badge -->
                <div v-if="student" class="hidden sm:flex flex-col items-end gap-0.5">
                    <span class="text-sm font-semibold text-slate-700">{{ student.name }}</span>
                    <span class="text-xs text-slate-400">
                        NISN: {{ student.nisn ?? 'â€”' }}<span v-if="student.nis"> â€¢ NIS: {{ student.nis }}</span>
                    </span>
                </div>
            </div>

            <!-- No student linked -->
            <div v-if="!student" class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 size-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Akun belum terhubung ke data siswa</p>
                        <p class="mt-0.5 text-xs text-amber-700">Hubungi operator sekolah untuk menghubungkan akun Anda.</p>
                    </div>
                </div>
            </div>

            <template v-else>

                <!-- Student info card (mobile) -->
                <div class="sm:hidden rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ student.name }}</p>
                            <p class="text-xs text-slate-400">
                                NISN: {{ student.nisn ?? 'â€”' }}<span v-if="student.nis"> â€¢ NIS: {{ student.nis }}</span>
                            </p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            Siswa
                        </span>
                    </div>
                </div>

                <!-- Tagihan hero -->
                <div
                    v-if="unpaidInvoices.length > 0"
                    class="rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 p-5 text-white shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-red-100">Tagihan Belum Lunas</p>
                            <p class="tabular-nums mt-1 text-3xl font-bold">{{ formatRupiah(totalUnpaid) }}</p>
                            <p class="mt-1 text-xs text-red-200">{{ unpaidInvoices.length }} tagihan perlu dibayarkan</p>
                        </div>
                        <Link
                            href="/siswa/invoices"
                            class="shrink-0 rounded-xl bg-white/20 px-4 py-2 text-xs font-semibold text-white backdrop-blur-sm transition-colors hover:bg-white/30"
                        >
                            Bayar Sekarang
                        </Link>
                    </div>
                </div>

                <div
                    v-else
                    class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
                >
                    <svg class="size-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-emerald-800">Semua tagihan sudah lunas.</p>
                </div>

                <!-- Invoice list -->
                <div v-if="unpaidInvoices.length > 0" class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-50 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-800">Rincian Tagihan</h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div
                            v-for="inv in unpaidInvoices"
                            :key="inv.id"
                            class="flex items-center gap-3 px-5 py-3.5"
                        >
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-red-50">
                                <svg class="size-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ inv.payment_type }}</p>
                                <p class="text-xs text-slate-400">Jatuh tempo: {{ inv.due_date ?? '—' }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="tabular-nums text-sm font-semibold text-slate-800">{{ formatRupiah(inv.amount) }}</span>
                                <span
                                    class="rounded-md px-1.5 py-0.5 text-xs font-semibold"
                                    :class="statusConfig[inv.status]?.badge ?? 'bg-slate-100 text-slate-600'"
                                >
                                    {{ statusConfig[inv.status]?.label ?? inv.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick links -->
                <div class="grid grid-cols-2 gap-3">
                    <Link
                        href="/siswa/report-cards"
                        class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-shadow duration-150 hover:shadow-md"
                    >
                        <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-emerald-50 ring-4 ring-emerald-100">
                            <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-800">Nilai & Raport</p>
                        <p class="mt-0.5 text-xs text-slate-400">Lihat hasil belajar</p>
                    </Link>
                    <Link
                        href="/siswa/letters"
                        class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-shadow duration-150 hover:shadow-md"
                    >
                        <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-sky-50 ring-4 ring-sky-100">
                            <svg class="size-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-800">Surat</p>
                        <p class="mt-0.5 text-xs text-slate-400">Request surat keterangan</p>
                    </Link>
                </div>

            </template>

        </div>
    </AppLayout>
</template>
