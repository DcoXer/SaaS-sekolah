<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import FilterSelect from '@/Components/FilterSelect.vue';

const props = defineProps({
    invoices: { type: Object, required: true },
    filters:  { type: Object, default: () => ({}) },
});

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val ?? 0);

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const statusColor = {
    unpaid:  'bg-red-100 text-red-700 ring-1 ring-red-200',
    partial: 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
    paid:    'bg-primary-100 text-primary-700 ring-1 ring-primary-200',
};
const statusLabel = {
    unpaid:  'Belum Bayar',
    partial: 'Kurang Bayar',
    paid:    'Lunas',
};

// ── Filter ─────────────────────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilter = () => {
    router.get(route('keuangan.ppdb-invoices.index'), { search: search.value, status: status.value }, { preserveState: true });
};

// ── Catat Pembayaran ────────────────────────────────────────────────────────
const payTarget = ref(null);

const payForm = useForm({
    amount:     '',
    note:       '',
    proof_file: null,
});

const openPay = (inv) => {
    payTarget.value    = inv;
    payForm.amount     = inv.remaining_amount ?? inv.amount;
    payForm.note       = '';
    payForm.proof_file = null;
    payForm.clearErrors();
};

const onProofChange = (e) => {
    payForm.proof_file = e.target.files[0] ?? null;
};

const submitPay = () => {
    // PPDB invoice adalah Invoice model biasa, gunakan route payments.store yang sudah ada
    payForm.post(route('keuangan.payments.store', payTarget.value.id), {
        forceFormData: true,
        onSuccess: () => {
            payTarget.value = null;
            payForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Tagihan PPDB" />
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Keuangan</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tagihan PPDB</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-500 to-teal-500 px-6 py-6 shadow-lg shadow-primary-200/60">
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-10 right-16 size-28 rounded-full bg-white/8"></div>
                <div class="relative flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm ring-1 ring-white/30">
                            <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">Tagihan PPDB</h1>
                            <p class="mt-0.5 text-sm text-primary-100">Uang masuk pendaftar yang telah diterima</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input v-model="search" type="search" placeholder="Cari nama, no. daftar, telepon..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-400/20"
                        @keyup.enter="applyFilter"/>
                </div>
                <FilterSelect
                    v-model="status"
                    @change="applyFilter"
                    :options="[
                        { value: '', label: 'Semua Status' },
                        { value: 'unpaid', label: 'Belum Bayar' },
                        { value: 'partial', label: 'Kurang Bayar' },
                        { value: 'paid', label: 'Lunas' },
                    ]"
                />
                <button @click="applyFilter"
                    class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-500">
                    Filter
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="!invoices.data?.length"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center">
                <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-slate-100">
                    <svg class="size-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Belum ada tagihan PPDB</p>
                <p class="mt-1.5 max-w-xs text-xs text-slate-400">Tidak ada tagihan yang cocok dengan filter saat ini.</p>
            </div>

            <template v-else>

                <!-- Mobile card list -->
                <div class="sm:hidden space-y-2">
                    <div v-for="inv in invoices.data" :key="inv.id" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-start justify-between p-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ inv.ppdb_registration?.full_name }}</p>
                                <p class="text-xs text-slate-400">
                                    No. {{ inv.ppdb_registration?.registration_number ?? '-' }}
                                    <template v-if="inv.ppdb_registration?.parent_name"> · {{ inv.ppdb_registration.parent_name }}</template>
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1 ml-2">
                                <button v-if="inv.status !== 'paid'" @click="openPay(inv)" title="Catat Pembayaran"
                                    class="flex size-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 transition-colors hover:bg-primary-100">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                    </svg>
                                </button>
                                <!-- Kwitansi PPDB: belum tersedia karena tidak ada halaman receipt khusus PPDB -->
                                <span v-if="inv.status === 'paid' || inv.total_paid > 0"
                                    title="Kwitansi PPDB belum tersedia"
                                    class="flex size-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-100 bg-slate-50 text-slate-300"
                                    aria-label="Kwitansi belum tersedia untuk PPDB">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-4 py-2.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColor[inv.status]">
                                <span class="size-1.5 rounded-full"
                                    :class="{ 'bg-red-500': inv.status === 'unpaid', 'bg-amber-500': inv.status === 'partial', 'bg-primary-500': inv.status === 'paid' }"/>
                                {{ statusLabel[inv.status] }}
                            </span>
                            <span class="text-xs font-semibold tabular-nums text-slate-700">{{ formatRupiah(inv.amount) }}</span>
                            <span v-if="inv.status !== 'paid'" class="text-xs tabular-nums text-red-500">Sisa {{ formatRupiah(inv.remaining_amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">No. Daftar</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Calon Siswa</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Orang Tua</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Nominal</th>
                                    <th class="hidden px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 md:table-cell">Jatuh Tempo</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="inv in invoices.data" :key="inv.id" class="transition-colors hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">
                                        {{ inv.ppdb_registration?.registration_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800">{{ inv.ppdb_registration?.full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ inv.ppdb_registration?.gender === 'male' ? 'L' : 'P' }} · {{ inv.ppdb_registration?.birth_place }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-700">{{ inv.ppdb_registration?.parent_name }}</p>
                                        <p class="text-xs text-slate-400">{{ inv.ppdb_registration?.parent_phone }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold tabular-nums text-slate-800">{{ formatRupiah(inv.amount) }}</p>
                                        <p v-if="inv.status !== 'paid'" class="text-xs tabular-nums text-red-500">Sisa {{ formatRupiah(inv.remaining_amount) }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 text-xs text-slate-500 md:table-cell">
                                        {{ formatDate(inv.due_date) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColor[inv.status]">
                                            <span class="size-1.5 rounded-full"
                                                :class="{ 'bg-red-500': inv.status === 'unpaid', 'bg-amber-500': inv.status === 'partial', 'bg-primary-500': inv.status === 'paid' }"/>
                                            {{ statusLabel[inv.status] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <button v-if="inv.status !== 'paid'" @click="openPay(inv)" title="Catat Pembayaran"
                                                class="flex size-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 transition-colors hover:bg-primary-100">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                                </svg>
                                            </button>
                                            <!-- Kwitansi PPDB: belum tersedia (tidak ada route/halaman receipt khusus PPDB) -->
                                            <span v-if="inv.status === 'paid' || inv.total_paid > 0"
                                                title="Kwitansi PPDB belum tersedia"
                                                class="flex size-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-100 bg-slate-50 text-slate-300"
                                                aria-label="Kwitansi belum tersedia untuk PPDB">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="invoices.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
                        <p class="text-xs text-slate-400">{{ invoices.from }}–{{ invoices.to }} dari {{ invoices.total }}</p>
                        <div class="flex gap-1">
                            <a v-for="link in invoices.links" :key="link.label"
                                :href="link.url || undefined"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                :class="link.active
                                    ? 'bg-primary-600 text-white'
                                    : link.url ? 'border border-slate-200 text-slate-600 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300 pointer-events-none'"
                            />
                        </div>
                    </div>
                </div>

                <!-- Mobile pagination -->
                <div v-if="invoices.last_page > 1" class="sm:hidden flex items-center justify-between rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs text-slate-400">{{ invoices.from }}–{{ invoices.to }} dari {{ invoices.total }}</p>
                    <div class="flex gap-1">
                        <a v-for="link in invoices.links" :key="link.label"
                            :href="link.url || undefined"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="link.active
                                ? 'bg-primary-600 text-white'
                                : link.url ? 'border border-slate-200 text-slate-600 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300 pointer-events-none'"
                        />
                    </div>
                </div>

            </template>
        </div>

        <!-- Modal Catat Pembayaran -->
        <Modal :show="!!payTarget" max-width="sm" @close="payTarget = null">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Catat Pembayaran</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ payTarget?.ppdb_registration?.full_name }}</p>
                </div>
                <button @click="payTarget = null" class="flex size-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitPay" class="space-y-4 p-6">
                <!-- Info tagihan -->
                <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tagihan</span>
                        <span class="font-semibold text-slate-700">{{ formatRupiah(payTarget?.amount) }}</span>
                    </div>
                    <div class="mt-1 flex justify-between">
                        <span class="text-slate-500">Sudah Bayar</span>
                        <span class="font-semibold text-primary-600">{{ formatRupiah(payTarget?.total_paid) }}</span>
                    </div>
                    <div class="mt-1 flex justify-between border-t border-slate-200 pt-1">
                        <span class="font-semibold text-slate-700">Sisa</span>
                        <span class="font-bold text-red-600">{{ formatRupiah(payTarget?.remaining_amount) }}</span>
                    </div>
                </div>

                <!-- Nominal -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nominal Pembayaran <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input v-model="payForm.amount" type="number" min="1000" :max="payTarget?.remaining_amount"
                            class="w-full rounded-xl border py-2.5 pl-9 pr-4 text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100"
                            :class="payForm.errors.amount ? 'border-red-300 bg-red-50' : 'border-slate-200'"/>
                    </div>
                    <p v-if="payForm.errors.amount" class="mt-1 text-xs text-red-500">{{ payForm.errors.amount }}</p>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Catatan</label>
                    <input v-model="payForm.note" type="text" placeholder="Opsional"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100"/>
                </div>

                <!-- Bukti -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Bukti Pembayaran</label>
                    <input type="file" accept=".jpg,.jpeg,.png,.pdf" @change="onProofChange"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary-700 hover:file:bg-primary-100"/>
                    <p v-if="payForm.errors.proof_file" class="mt-1 text-xs text-red-500">{{ payForm.errors.proof_file }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="payTarget = null"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="payForm.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2 text-sm font-bold text-white transition-colors hover:bg-primary-500 disabled:opacity-60">
                        <svg v-if="payForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>
