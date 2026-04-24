<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    honorariums:       { type: Array,  required: true },
    academicYears:     { type: Array,  required: true },
    activeYear:        { type: Object, default: null  },
    teachers:          { type: Array,  required: true },
    teachersWithHours: { type: Array,  required: true },
    filters:           { type: Object, default: () => ({}) },
});

// ── Filter ─────────────────────────────────────────────────────────────────────
const filterMonth  = ref(props.filters.month ?? '');
const filterYear   = ref(props.filters.year  ?? '');
const filterStatus = ref(props.filters.status ?? '');
const search       = ref('');

watch([filterMonth, filterYear, filterStatus], () => {
    router.get(route('keuangan.honorariums.index'), {
        month:  filterMonth.value  || undefined,
        year:   filterYear.value   || undefined,
        status: filterStatus.value || undefined,
    }, { preserveState: true });
});

const filtered = computed(() => {
    if (!search.value.trim()) return props.honorariums;
    const q = search.value.toLowerCase();
    return props.honorariums.filter(h => h.teacher.user.name.toLowerCase().includes(q));
});

const hasFilter = computed(() => search.value.trim() || filterMonth.value || filterYear.value || filterStatus.value);
const resetFilters = () => {
    search.value = ''; filterMonth.value = ''; filterYear.value = ''; filterStatus.value = '';
    router.get(route('keuangan.honorariums.index'));
};

// ── Pagination ─────────────────────────────────────────────────────────────────
const PER_PAGE    = 15;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch([search, filterMonth, filterYear, filterStatus], () => { currentPage.value = 1; });

// ── Generate Modal ─────────────────────────────────────────────────────────────
const showGenerate = ref(false);
const now          = new Date();

const generateForm = useForm({
    teacher_id:       '',
    academic_year_id: props.activeYear?.id ?? '',
    period_month:     now.getMonth() + 1,
    period_year:      now.getFullYear(),
});

const submitGenerate = () => {
    generateForm.post(route('keuangan.honorariums.generate'), {
        onSuccess: () => { showGenerate.value = false; generateForm.reset(); },
    });
};

// ── Mark Paid ──────────────────────────────────────────────────────────────────
const paidTarget = ref(null);
const paidForm   = useForm({});
const submitMarkPaid = () => {
    paidForm.patch(route('keuangan.honorariums.mark-paid', paidTarget.value.id), {
        onSuccess: () => { paidTarget.value = null; },
    });
};

// ── Delete ─────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});
const submitDelete = () => {
    deleteForm.delete(route('keuangan.honorariums.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Helpers ────────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const periodLabel = (h) => `${monthNames[h.period_month]} ${h.period_year}`;

const statusConfig = {
    draft: { label: 'Belum Dibayar', badge: 'bg-amber-50 text-amber-700' },
    paid:  { label: 'Lunas',         badge: 'bg-emerald-50 text-emerald-700' },
};

const monthOptions = monthNames.slice(1).map((name, i) => ({ value: i + 1, label: name }));
const yearOptions  = [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1];

// Summary
const totalAll   = computed(() => filtered.value.reduce((s, h) => s + h.total_amount, 0));
const totalPaid  = computed(() => filtered.value.filter(h => h.status === 'paid').reduce((s, h) => s + h.total_amount, 0));
const totalDraft = computed(() => filtered.value.filter(h => h.status === 'draft').reduce((s, h) => s + h.total_amount, 0));
</script>

<template>
    <AppLayout>
        <Head title="Honor Guru" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Keuangan</span><span>/</span>
                <span class="font-semibold text-slate-700">Honor Guru</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Heading -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Honor Guru</h2>
                    <p class="text-sm text-slate-500">Generate dan kelola slip honor bulanan guru.</p>
                </div>
                <button @click="showGenerate = true"
                    class="flex shrink-0 items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 transition-colors">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span class="hidden sm:inline">Buat Slip</span>
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm sm:block sm:p-5">
                    <p class="text-xs font-semibold uppercase text-slate-400">Total Honor</p>
                    <p class="tabular-nums text-base font-bold text-slate-800 sm:mt-1">Rp {{ fmt(totalAll) }}</p>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm sm:block sm:p-5">
                    <p class="text-xs font-semibold uppercase text-slate-400">Sudah Dibayar</p>
                    <p class="tabular-nums text-base font-bold text-emerald-600 sm:mt-1">Rp {{ fmt(totalPaid) }}</p>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm sm:block sm:p-5">
                    <p class="text-xs font-semibold uppercase text-slate-400">Belum Dibayar</p>
                    <p class="tabular-nums text-base font-bold text-amber-500 sm:mt-1">Rp {{ fmt(totalDraft) }}</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="space-y-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input v-model="search" type="search" placeholder="Cari nama guru..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-9 pr-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"/>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex gap-1">
                        <button v-for="opt in [{ value: '', label: 'Semua' }, { value: 'draft', label: 'Belum Bayar' }, { value: 'paid', label: 'Lunas' }]"
                            :key="opt.value" @click="filterStatus = opt.value"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                            :class="filterStatus === opt.value ? 'bg-emerald-500 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                            {{ opt.label }}
                        </button>
                    </div>
                    <select v-model="filterMonth"
                        class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-7 text-xs font-semibold text-slate-600 outline-none focus:border-emerald-400">
                        <option value="">Semua Bulan</option>
                        <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                    <select v-model="filterYear"
                        class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-7 text-xs font-semibold text-slate-600 outline-none focus:border-emerald-400">
                        <option value="">Semua Tahun</option>
                        <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                    </select>
                    <button v-if="hasFilter" @click="resetFilters"
                        class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors">Reset</button>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                </svg>
                <p class="text-sm font-semibold text-slate-700">{{ hasFilter ? 'Tidak ada hasil' : 'Belum ada slip honor' }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ hasFilter ? 'Coba ubah filter.' : 'Klik "Buat Slip" untuk membuat slip honor pertama.' }}</p>
                <button v-if="hasFilter" @click="resetFilters" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset filter</button>
            </div>

            <!-- List -->
            <div v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <!-- Desktop header -->
                <div class="hidden grid-cols-12 gap-3 border-b border-slate-100 bg-slate-50 px-5 py-2.5 sm:grid">
                    <div class="col-span-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Guru</div>
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Periode</div>
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Jam Pelajaran</div>
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Transport</div>
                    <div class="col-span-1 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Total</div>
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Aksi</div>
                </div>

                <ul class="divide-y divide-slate-100">
                    <li v-for="h in paginated" :key="h.id"
                        class="px-4 py-4 transition-colors hover:bg-slate-50 sm:px-5 sm:py-3.5">

                        <!-- Mobile -->
                        <div class="sm:hidden space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-700">
                                        {{ h.teacher.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ h.teacher.user.name }}</p>
                                        <p class="text-xs text-slate-400">{{ periodLabel(h) }} · {{ h.academic_year?.name }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="statusConfig[h.status]?.badge">
                                    {{ statusConfig[h.status]?.label }}
                                </span>
                            </div>
                            <div class="rounded-lg bg-slate-50 px-3 py-2.5 space-y-1.5">
                                <div class="grid gap-2" :class="h.position_allowance > 0 ? 'grid-cols-2' : 'grid-cols-2'">
                                    <div class="text-center">
                                        <p class="text-xs text-slate-400">Jam Pelajaran</p>
                                        <p class="text-xs font-bold text-slate-700">Rp {{ fmt(h.teaching_hours_amount) }}</p>
                                        <p class="text-xs text-slate-400">{{ h.teaching_hours }} jam</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-slate-400">Transport</p>
                                        <p class="text-xs font-bold text-slate-700">Rp {{ fmt(h.transport_amount) }}</p>
                                        <p class="text-xs text-slate-400">{{ h.transport_days }} hari</p>
                                    </div>
                                </div>
                                <div v-if="h.position_allowance > 0"
                                    class="flex items-center justify-between border-t border-slate-200 pt-1.5">
                                    <span class="text-xs text-amber-600 font-medium">{{ h.position_name }}</span>
                                    <span class="text-xs font-bold text-amber-700">Rp {{ fmt(h.position_allowance) }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-200 pt-1.5">
                                    <span class="text-xs font-semibold text-slate-500">Total</span>
                                    <span class="text-sm font-bold text-slate-900">Rp {{ fmt(h.total_amount) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a :href="route('keuangan.honorariums.slip', h.id)" target="_blank"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                    Cetak PDF
                                </a>
                                <button v-if="h.status === 'draft'" @click="paidTarget = h"
                                    class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600 transition-colors">
                                    Bayar
                                </button>
                                <button v-if="h.status === 'draft'" @click="deleteTarget = h"
                                    class="rounded-lg border border-red-100 px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 transition-colors">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <!-- Desktop -->
                        <div class="hidden grid-cols-12 items-center gap-3 sm:grid">
                            <div class="col-span-3 flex items-center gap-3">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">
                                    {{ h.teacher.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ h.teacher.user.name }}</p>
                                    <p class="text-xs text-slate-400">{{ h.academic_year?.name }}</p>
                                </div>
                            </div>
                            <div class="col-span-2 text-sm text-slate-600">{{ periodLabel(h) }}</div>
                            <div class="col-span-2 text-right">
                                <p class="text-sm text-slate-700">Rp {{ fmt(h.teaching_hours_amount) }}</p>
                                <p class="text-xs text-slate-400">{{ h.teaching_hours }} jam</p>
                            </div>
                            <div class="col-span-2 text-right">
                                <p class="text-sm text-slate-700">Rp {{ fmt(h.transport_amount) }}</p>
                                <p class="text-xs text-slate-400">{{ h.transport_days }} hari</p>
                            </div>
                            <div class="col-span-1 text-right font-bold text-slate-900">
                                Rp {{ fmt(h.total_amount) }}
                            </div>
                            <div class="col-span-2 flex items-center justify-end gap-1.5">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="statusConfig[h.status]?.badge">
                                    {{ statusConfig[h.status]?.label }}
                                </span>
                                <a :href="route('keuangan.honorariums.slip', h.id)" target="_blank"
                                    class="rounded-lg border border-slate-200 p-1.5 text-slate-500 hover:bg-slate-50 transition-colors" title="Cetak PDF">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                    </svg>
                                </a>
                                <button v-if="h.status === 'draft'" @click="paidTarget = h"
                                    class="rounded-lg bg-emerald-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-600 transition-colors">
                                    Bayar
                                </button>
                                <button v-if="h.status === 'draft'" @click="deleteTarget = h"
                                    class="rounded-lg border border-red-100 p-1.5 text-red-400 hover:bg-red-50 transition-colors" title="Hapus">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </li>
                </ul>
            </div>

            <Pagination
                :current-page="currentPage" :total-pages="totalPages"
                :total="filtered.length" :per-page="PER_PAGE" label="slip"
                @update:current-page="currentPage = $event"
            />

        </div>

        <!-- Modal Generate -->
        <Modal :show="showGenerate" @close="showGenerate = false" max-width="md">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-1">Buat Slip Honor</h3>
                <p class="text-sm text-slate-500 mb-5">Honor dihitung otomatis dari jam pelajaran + absensi bulan tersebut.</p>
                <form @submit.prevent="submitGenerate" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Guru</label>
                        <select v-model="generateForm.teacher_id"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                            :class="{ 'border-red-400': generateForm.errors.teacher_id }">
                            <option value="">-- Pilih Guru --</option>
                            <option v-for="t in teachersWithHours" :key="t.id" :value="t.id">{{ t.user.name }}</option>
                        </select>
                        <p v-if="generateForm.errors.teacher_id" class="mt-1 text-xs text-red-500">{{ generateForm.errors.teacher_id }}</p>
                        <p v-if="teachersWithHours.length === 0" class="mt-1 text-xs text-amber-600">
                            Belum ada guru dengan konfigurasi jam pelajaran di tahun ajaran aktif.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Ajaran</label>
                        <select v-model="generateForm.academic_year_id"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                            :class="{ 'border-red-400': generateForm.errors.academic_year_id }">
                            <option value="">-- Pilih --</option>
                            <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                        </select>
                        <p v-if="generateForm.errors.academic_year_id" class="mt-1 text-xs text-red-500">{{ generateForm.errors.academic_year_id }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Bulan</label>
                            <select v-model="generateForm.period_month"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                                :class="{ 'border-red-400': generateForm.errors.period_month }">
                                <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                            </select>
                            <p v-if="generateForm.errors.period_month" class="mt-1 text-xs text-red-500">{{ generateForm.errors.period_month }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tahun</label>
                            <select v-model="generateForm.period_year"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20">
                                <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="rounded-lg bg-blue-50 px-4 py-3 text-xs text-blue-700">
                        Pastikan absensi bulan ini sudah lengkap sebelum membuat slip. Jumlah hari hadir akan otomatis dihitung.
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showGenerate = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="generateForm.processing"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50">
                            {{ generateForm.processing ? 'Memproses...' : 'Buat Slip' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Konfirmasi Bayar -->
        <Modal :show="!!paidTarget" @close="paidTarget = null" max-width="sm">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-2">Konfirmasi Pembayaran</h3>
                <p class="text-sm text-slate-600 mb-2">
                    Tandai honor <strong>{{ paidTarget?.teacher.user.name }}</strong> periode <strong>{{ paidTarget ? periodLabel(paidTarget) : '' }}</strong> sebagai lunas?
                </p>
                <div class="mb-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Total: <strong>Rp {{ paidTarget ? fmt(paidTarget.total_amount) : '' }}</strong>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="paidTarget = null"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button @click="submitMarkPaid" :disabled="paidForm.processing"
                        class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50">
                        Ya, Lunas
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Modal Hapus -->
        <Modal :show="!!deleteTarget" @close="deleteTarget = null" max-width="sm">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-2">Hapus Slip Honor?</h3>
                <p class="text-sm text-slate-600 mb-5">
                    Slip honor <strong>{{ deleteTarget?.teacher.user.name }}</strong> periode <strong>{{ deleteTarget ? periodLabel(deleteTarget) : '' }}</strong> akan dihapus permanen.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="deleteTarget = null"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button @click="submitDelete" :disabled="deleteForm.processing"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-50">Ya, Hapus</button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
