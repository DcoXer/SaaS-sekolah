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
                <Link :href="route('keuangan.invoices.index')" class="hover:text-slate-700">Tagihan</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ student.name }}</span>
            </div>
        </template>

        <div class="space-y-5">
            <BackButton :href="route('keuangan.invoices.index')" />

            <!-- Student card -->
            <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">
                    {{ student.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div>
                    <p class="font-bold text-slate-900">{{ student.name }}</p>
                    <p class="text-sm text-slate-500">
                        NIS {{ student.nis }}
                        <span v-if="activeClassroom"> · {{ activeClassroom.name }}</span>
                    </p>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="invoices.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tagihan</p>
                <p class="mt-1 text-xs text-slate-400">Siswa ini belum memiliki tagihan di tahun ajaran aktif.</p>
            </div>

            <!-- Invoice list -->
            <div v-else class="space-y-3">
                <div
                    v-for="inv in invoices"
                    :key="inv.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <!-- Invoice header -->
                    <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-slate-800">{{ inv.payment_type?.name }}</span>
                                <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', statusColor[inv.status]]">
                                    {{ statusLabel[inv.status] }}
                                </span>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-400">Jatuh tempo: {{ formatDate(inv.due_date) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ formatRupiah(inv.amount) }}</p>
                                <p v-if="inv.status !== 'paid'" class="text-xs text-red-500">
                                    Sisa {{ formatRupiah(inv.remaining_amount) }}
                                </p>
                            </div>
                            <!-- Badge request cash pending -->
                            <span
                                v-if="inv.payment_request?.status === 'pending'"
                                class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-700"
                                title="Siswa mengajukan pembayaran cash"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Request Cash
                            </span>
                            <!-- Action buttons -->
                            <button
                                v-if="inv.status !== 'paid'"
                                @click="openPay(inv)"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Catat Bayar
                            </button>
                            <Link
                                :href="route('keuangan.payments.receipt', inv.id)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Kwitansi
                            </Link>
                        </div>
                    </div>

                    <!-- Payment history -->
                    <div v-if="inv.payments?.length > 0" class="divide-y divide-slate-50 px-5 py-1">
                        <div
                            v-for="pay in inv.payments"
                            :key="pay.id"
                            class="flex items-center justify-between py-2.5"
                        >
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold capitalize text-slate-600">
                                    {{ pay.method }}
                                </span>
                                <span class="text-xs text-slate-500">{{ formatDate(pay.paid_at) }}</span>
                                <span v-if="pay.note" class="text-xs text-slate-400">· {{ pay.note }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-emerald-600">{{ formatRupiah(pay.amount) }}</span>
                                <button
                                    @click="deletePaymentTarget = pay"
                                    class="inline-flex size-6 items-center justify-center rounded-lg text-slate-300 transition-[color] duration-150 hover:text-red-500"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-5 py-3">
                        <p class="text-xs text-slate-400">Belum ada pembayaran tercatat.</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Modal Catat Pembayaran ───────────────────────────────────────────── -->
        <Modal :show="!!payTarget" max-width="sm" @close="payTarget = null">
            <form @submit.prevent="submitPay">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Catat Pembayaran</h3>
                        <p class="text-xs text-slate-500">{{ payTarget?.payment_type?.name }}</p>
                    </div>
                    <button type="button" @click="payTarget = null"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <!-- Info sisa -->
                    <div class="rounded-lg bg-slate-50 px-4 py-3">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Total tagihan</span>
                            <span class="font-semibold text-slate-700">{{ formatRupiah(payTarget?.amount) }}</span>
                        </div>
                        <div class="mt-1 flex justify-between text-xs text-slate-500">
                            <span>Sudah dibayar</span>
                            <span class="font-semibold text-emerald-600">{{ formatRupiah((payTarget?.amount ?? 0) - (payTarget?.remaining_amount ?? 0)) }}</span>
                        </div>
                        <div class="mt-1 flex justify-between border-t border-slate-200 pt-1 text-xs">
                            <span class="font-semibold text-slate-700">Sisa tagihan</span>
                            <span class="font-bold text-red-600">{{ formatRupiah(payTarget?.remaining_amount) }}</span>
                        </div>
                    </div>
                    <!-- Nominal -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal <span class="text-red-500">*</span></label>
                        <input v-model="payForm.amount" type="number" min="1000" placeholder="Contoh: 150000"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', payForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="payForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ payForm.errors.amount }}</p>
                    </div>
                    <!-- Catatan -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Catatan</label>
                        <input v-model="payForm.note" type="text" placeholder="Opsional..."
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" />
                    </div>
                    <!-- Bukti -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Bukti Pembayaran</label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-500 transition-[background-color] duration-150 hover:bg-slate-100">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            {{ payForm.proof_file ? payForm.proof_file.name : 'Pilih file (jpg/png/pdf, maks 2MB)' }}
                            <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" @change="onProofChange" />
                        </label>
                        <p v-if="payForm.errors.proof_file" class="mt-1.5 text-xs text-red-500">{{ payForm.errors.proof_file }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="payTarget = null"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="payForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60">
                        <svg v-if="payForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ payForm.processing ? 'Menyimpan...' : 'Simpan Pembayaran' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Modal Hapus Pembayaran ──────────────────────────────────────────── -->
        <Modal :show="!!deletePaymentTarget" max-width="sm" @close="deletePaymentTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Hapus Pembayaran</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    Yakin hapus pembayaran <span class="font-semibold text-slate-700">{{ formatRupiah(deletePaymentTarget?.amount) }}</span>?
                    Status tagihan akan diperbarui otomatis.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="deletePaymentTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                    Batal
                </button>
                <button @click="submitDeletePayment" :disabled="deletePaymentForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60">
                    <svg v-if="deletePaymentForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deletePaymentForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
