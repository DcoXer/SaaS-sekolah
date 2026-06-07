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
    paid:    'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
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
        <div class="space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">Tagihan PPDB</h1>
                <p class="mt-0.5 text-sm text-slate-500">Uang masuk pendaftar yang telah diterima</p>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input v-model="search" type="search" placeholder="Cari nama, no. daftar, telepon..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
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
                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-500">
                    Filter
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">No. Daftar</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Calon Siswa</th>
                                <th class="hidden px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 sm:table-cell">Orang Tua</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Nominal</th>
                                <th class="hidden px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 md:table-cell">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="!invoices.data?.length">
                                <td colspan="7" class="py-16 text-center text-sm text-slate-400">Belum ada tagihan PPDB.</td>
                            </tr>
                            <tr v-for="inv in invoices.data" :key="inv.id" class="transition-colors hover:bg-slate-50/60">
                                <td class="px-4 py-3 font-mono text-xs text-slate-500">
                                    {{ inv.ppdb_registration?.registration_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ inv.ppdb_registration?.full_name }}</p>
                                    <p class="text-xs text-slate-400">{{ inv.ppdb_registration?.gender === 'male' ? 'L' : 'P' }} · {{ inv.ppdb_registration?.birth_place }}</p>
                                </td>
                                <td class="hidden px-4 py-3 sm:table-cell">
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
                                            :class="{ 'bg-red-500': inv.status === 'unpaid', 'bg-amber-500': inv.status === 'partial', 'bg-emerald-500': inv.status === 'paid' }"/>
                                        {{ statusLabel[inv.status] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <button v-if="inv.status !== 'paid'" @click="openPay(inv)" title="Catat Pembayaran"
                                            class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition-colors hover:bg-emerald-100">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                            </svg>
                                        </button>
                                        <a v-if="inv.status === 'paid' || inv.total_paid > 0"
                                            :href="route('keuangan.payments.receipt', inv.id)"
                                            title="Kwitansi"
                                            class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors hover:bg-slate-50">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                            </svg>
                                        </a>
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
                                ? 'bg-emerald-600 text-white'
                                : link.url ? 'border border-slate-200 text-slate-600 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300 pointer-events-none'"
                        />
                    </div>
                </div>
            </div>
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
                        <span class="font-semibold text-emerald-600">{{ formatRupiah(payTarget?.total_paid) }}</span>
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
                            class="w-full rounded-xl border py-2.5 pl-9 pr-4 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                            :class="payForm.errors.amount ? 'border-red-300 bg-red-50' : 'border-slate-200'"/>
                    </div>
                    <p v-if="payForm.errors.amount" class="mt-1 text-xs text-red-500">{{ payForm.errors.amount }}</p>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Catatan</label>
                    <input v-model="payForm.note" type="text" placeholder="Opsional"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"/>
                </div>

                <!-- Bukti -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Bukti Pembayaran</label>
                    <input type="file" accept=".jpg,.jpeg,.png,.pdf" @change="onProofChange"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100"/>
                    <p v-if="payForm.errors.proof_file" class="mt-1 text-xs text-red-500">{{ payForm.errors.proof_file }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="payTarget = null"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="payForm.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-bold text-white transition-colors hover:bg-emerald-500 disabled:opacity-60">
                        <svg v-if="payForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>
