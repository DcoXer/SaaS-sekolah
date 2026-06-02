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

            <!-- Heading -->
            <div>
                <h2 class="text-balance text-lg font-bold text-slate-900">Tagihan Saya</h2>
                <p class="text-pretty text-sm text-slate-500">
                    {{ activeYear ? `Tahun ajaran: ${activeYear.name}` : 'Belum ada tahun ajaran aktif.' }}
                </p>
            </div>

            <!-- Exam access warning -->
            <div
                v-if="!hasExamAccess"
                class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4"
            >
                <svg class="mt-0.5 size-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-800">Akses ujian ditangguhkan</p>
                    <p class="mt-0.5 text-xs text-red-700">Ada tagihan ujian yang belum lunas. Selesaikan pembayaran untuk mendapatkan akses ujian.</p>
                </div>
            </div>

            <!-- Summary -->
            <div
                v-if="invoices.length > 0 && totalUnpaid > 0"
                class="rounded-xl border border-amber-200 bg-amber-50 p-4"
            >
                <p class="text-sm text-amber-700">
                    Total tunggakan:
                    <strong class="text-amber-900">{{ formatRupiah(totalUnpaid) }}</strong>
                </p>
            </div>

            <!-- Search -->
            <div v-if="invoices.length > 0" class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Cari jenis tagihan..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-9 pr-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                />
            </div>

            <!-- Empty -->
            <div
                v-if="invoices.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tagihan</p>
                <p class="mt-1 text-xs text-slate-400">Belum ada tagihan untuk tahun ajaran ini.</p>
            </div>

            <div v-else-if="filteredInvoices.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center">
                <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                <button @click="search = ''" class="mt-2 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
            </div>

            <template v-else>
                <!-- Mobile cards (primary) -->
                <div class="space-y-3 sm:hidden">
                    <div
                        v-for="invoice in filteredInvoices"
                        :key="invoice.id"
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="px-4 pt-4 pb-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ invoice.payment_type?.name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">Jatuh tempo: {{ formatDate(invoice.due_date) }}</p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                        :class="statusConfig[invoice.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                    >
                                        {{ statusConfig[invoice.status]?.label ?? invoice.status }}
                                    </span>
                                    <span
                                        v-if="invoice.payment_type?.is_exam_related"
                                        class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600 ring-1 ring-red-200"
                                    >Ujian</span>
                                </div>
                            </div>
                            <p class="mt-2 tabular-nums text-base font-bold text-slate-900">{{ formatRupiah(invoice.amount) }}</p>
                        </div>
                        <div class="flex items-center gap-2 border-t border-slate-100 px-4 py-3">
                            <Link
                                :href="route('siswa.payments.receipt', invoice.id)"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white py-2 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                                Kwitansi
                            </Link>
                            <span
                                v-if="invoice.status !== 'paid' && invoice.payment_request?.status === 'pending'"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-amber-100 py-2 text-xs font-semibold text-amber-700"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Silahkan ke Sekolah
                            </span>
                            <button
                                v-else-if="invoice.status !== 'paid'"
                                @click="openMethodModal(invoice)"
                                :disabled="paying === invoice.id"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-500 py-2 text-xs font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                            >
                                <svg v-if="paying === invoice.id" class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                {{ paying === invoice.id ? 'Memproses...' : 'Bayar' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Jenis Tagihan</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Nominal</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Jatuh Tempo</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Status</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="invoice in filteredInvoices"
                                :key="invoice.id"
                                class="transition-[background-color] duration-150 hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <span class="text-sm font-medium text-slate-800">{{ invoice.payment_type?.name }}</span>
                                    <span
                                        v-if="invoice.payment_type?.is_exam_related"
                                        class="ml-2 inline-flex items-center rounded-full bg-red-50 px-1.5 py-0.5 text-xs font-semibold text-red-600 ring-1 ring-red-200"
                                    >
                                        Ujian
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="tabular-nums text-sm font-semibold text-slate-800">{{ formatRupiah(invoice.amount) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="tabular-nums text-sm text-slate-600">{{ formatDate(invoice.due_date) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                        :class="statusConfig[invoice.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                    >
                                        {{ statusConfig[invoice.status]?.label ?? invoice.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('siswa.payments.receipt', invoice.id)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                            </svg>
                                            Kwitansi
                                        </Link>
                                        <span
                                            v-if="invoice.status !== 'paid' && invoice.payment_request?.status === 'pending'"
                                            class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-700"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Silahkan ke Sekolah
                                        </span>
                                        <button
                                            v-else-if="invoice.status !== 'paid'"
                                            @click="openMethodModal(invoice)"
                                            :disabled="paying === invoice.id"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
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
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pilih Metode Pembayaran</h3>
                    <p class="mt-0.5 text-xs text-slate-500">{{ methodTarget?.payment_type?.name }}</p>
                </div>
                <button
                    type="button"
                    aria-label="Tutup modal"
                    @click="methodTarget = null"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-3 px-6 py-5">
                <p class="text-sm text-slate-500">
                    Nominal: <span class="font-semibold text-slate-800">{{ formatRupiah(methodTarget?.amount) }}</span>
                </p>

                <!-- Cash -->
                <button
                    type="button"
                    @click="submitCash"
                    :disabled="cashForm.processing"
                    class="flex w-full items-center gap-4 rounded-xl border border-slate-200 px-4 py-4 text-left transition-[border-color,background-color] duration-150 hover:border-slate-300 hover:bg-slate-50 disabled:opacity-60"
                >
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                        <svg class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Bayar Cash</p>
                        <p class="text-xs text-slate-400">Kirim request, lalu datang ke kantor TU untuk menyelesaikan pembayaran</p>
                    </div>
                </button>

                <!-- Online -->
                <button
                    type="button"
                    @click="payOnline"
                    class="flex w-full items-center gap-4 rounded-xl border border-slate-200 px-4 py-4 text-left transition-[border-color,background-color] duration-150 hover:border-slate-300 hover:bg-slate-50"
                >
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                        <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Bayar Online</p>
                        <p class="text-xs text-slate-400">Transfer, kartu kredit, dompet digital via Midtrans</p>
                    </div>
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
