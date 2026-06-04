<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    paymentTypes: { type: Array,  required: true },
    activeYear:   { type: Object, default: null },
});

// ── Helpers ───────────────────────────────────────────────────────────────────
const cycleLabel = { monthly: 'Bulanan', yearly: 'Tahunan', once: 'Sekali' };
const cycleColor = {
    monthly: 'bg-blue-100 text-blue-700',
    yearly:  'bg-violet-100 text-violet-700',
    once:    'bg-amber-100 text-amber-700',
};

const cycleOptions = [
    { value: 'once',    label: 'Sekali Bayar' },
    { value: 'yearly',  label: 'Tahunan' },
    { value: 'monthly', label: 'Bulanan' },
];
const gradeOptions = [
    { value: '', label: 'Semua kelas' },
    ...([1,2,3,4,5,6].map(g => ({ value: g, label: `Kelas ${g}` }))),
];

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);

// ── Create ────────────────────────────────────────────────────────────────────
const showCreate = ref(false);

const createForm = useForm({
    academic_year_id: props.activeYear?.id ?? '',
    name:             '',
    cycle:            'once',
    amount:           '',
    due_date:         '',
    grade:            '',
    is_exam_related:  false,
});

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    createForm.academic_year_id = props.activeYear?.id ?? '';
    createForm.cycle            = 'once';
    createForm.is_exam_related  = false;
    showCreate.value = true;
};

const submitCreate = () => {
    createForm.post(route('keuangan.payment-types.store'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};

// ── Generate SPP ──────────────────────────────────────────────────────────────
const showSpp   = ref(false);
const sppForm   = useForm({ amount: '' });

const submitSpp = () => {
    sppForm.post(route('keuangan.payment-types.generate-spp'), {
        onSuccess: () => {
            showSpp.value = false;
            sppForm.reset();
        },
    });
};

// ── Edit ──────────────────────────────────────────────────────────────────────
const editTarget = ref(null);

const editForm = useForm({
    name:            '',
    amount:          '',
    due_date:        '',
    grade:           '',
    is_exam_related: false,
    is_active:       true,
});

const openEdit = (pt) => {
    editTarget.value   = pt;
    editForm.name            = pt.name;
    editForm.amount          = pt.amount;
    editForm.due_date        = pt.due_date ?? '';
    editForm.grade           = pt.grade ?? '';
    editForm.is_exam_related = pt.is_exam_related;
    editForm.is_active       = pt.is_active;
    editForm.clearErrors();
};

const submitEdit = () => {
    editForm.put(route('keuangan.payment-types.update', editTarget.value.id), {
        onSuccess: () => { editTarget.value = null; },
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('keuangan.payment-types.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Search & Filter ───────────────────────────────────────────────────────────
const search       = ref('');
const filterCycle  = ref('');
const filterGrade  = ref('');
const filterStatus = ref('');

const filtered = computed(() => {
    let list = props.paymentTypes;
    if (filterCycle.value)  list = list.filter(p => p.cycle === filterCycle.value);
    if (filterGrade.value)  list = list.filter(p => String(p.grade) === filterGrade.value);
    if (filterStatus.value === 'active')   list = list.filter(p => p.is_active);
    if (filterStatus.value === 'inactive') list = list.filter(p => !p.is_active);
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(p => p.name.toLowerCase().includes(q));
    }
    return list;
});

const PER_PAGE    = 12;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch([search, filterCycle, filterGrade, filterStatus], () => { currentPage.value = 1; });

const resetFilters = () => {
    search.value = '';
    filterCycle.value = '';
    filterGrade.value = '';
    filterStatus.value = '';
};
</script>

<template>
    <AppLayout>
        <Head title="Jenis Tagihan" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Keuangan</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Jenis Tagihan</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Hero Banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500 px-6 py-6 shadow-sm sm:px-8">
                <!-- Decorative shapes -->
                <div class="pointer-events-none absolute -right-6 -top-6 size-36 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-8 -left-4 size-28 rounded-full bg-white/5"></div>

                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 ring-2 ring-white/30">
                            <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white/75">Manajemen Keuangan</p>
                            <h1 class="text-xl font-bold text-white sm:text-2xl">Jenis Tagihan</h1>
                            <p class="mt-0.5 text-sm text-white/70">SPP bulanan, tahunan, maupun sekali bayar</p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div v-if="activeYear" class="flex shrink-0 items-center gap-2">
                        <button
                            @click="showSpp = true"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white/20 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur-sm transition-all duration-150 hover:bg-white/30"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                            </svg>
                            Generate SPP
                        </button>
                        <button
                            @click="openCreate"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm transition-all duration-150 hover:bg-emerald-50"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah
                        </button>
                    </div>
                </div>
            </div>

            <!-- No active year -->
            <div
                v-if="!activeYear"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <div class="mb-4 flex size-14 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="size-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
                <p class="mt-1 text-xs text-slate-400">Aktifkan tahun ajaran terlebih dahulu.</p>
            </div>

            <!-- Empty state -->
            <div
                v-else-if="paymentTypes.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <div class="mb-4 flex size-14 items-center justify-center rounded-2xl bg-emerald-50">
                    <svg class="size-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Belum ada jenis tagihan</p>
                <p class="mt-1 text-xs text-slate-400">Tambah tagihan atau generate SPP bulanan.</p>
                <div class="mt-5 flex items-center gap-2">
                    <button @click="showSpp = true"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-150 hover:bg-slate-50">
                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                        </svg>
                        Generate SPP
                    </button>
                    <button @click="openCreate"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-emerald-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Tagihan
                    </button>
                </div>
            </div>

            <!-- Search & Filter + Table -->
            <template v-else>

                <!-- Filter bar -->
                <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                    <div class="relative flex-1 min-w-[180px]">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari nama tagihan..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
                        />
                    </div>
                    <div class="h-5 w-px bg-slate-200"/>
                    <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
                        <button
                            v-for="opt in [{ value: '', label: 'Semua' }, { value: 'monthly', label: 'Bulanan' }, { value: 'yearly', label: 'Tahunan' }, { value: 'once', label: 'Sekali' }]"
                            :key="opt.value"
                            @click="filterCycle = opt.value"
                            :class="filterCycle === opt.value
                                ? 'bg-white text-slate-800 shadow-sm'
                                : 'text-slate-500 hover:text-slate-700'"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150"
                        >{{ opt.label }}</button>
                    </div>
                    <FilterSelect
                        v-model="filterGrade"
                        :options="[
                            { value: '', label: 'Semua Kelas' },
                            { value: '1', label: 'Kelas 1' },
                            { value: '2', label: 'Kelas 2' },
                            { value: '3', label: 'Kelas 3' },
                            { value: '4', label: 'Kelas 4' },
                            { value: '5', label: 'Kelas 5' },
                            { value: '6', label: 'Kelas 6' },
                        ]"
                    />
                    <FilterSelect
                        v-model="filterStatus"
                        :options="[
                            { value: '', label: 'Semua Status' },
                            { value: 'active', label: 'Aktif' },
                            { value: 'inactive', label: 'Nonaktif' },
                        ]"
                    />
                </div>

                <!-- No results -->
                <div
                    v-if="filtered.length === 0"
                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-12 text-center"
                >
                    <svg class="mb-3 size-9 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                    <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau hapus filter.</p>
                    <button @click="resetFilters" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
                </div>

                <template v-else>

                    <!-- ── Mobile cards ────────────────────────────────────────── -->
                    <div class="space-y-2.5 sm:hidden">
                        <div
                            v-for="pt in paginated"
                            :key="pt.id"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-opacity"
                            :class="!pt.is_active ? 'opacity-60' : ''"
                        >
                            <!-- Card top accent line -->
                            <div :class="['h-1', pt.cycle === 'monthly' ? 'bg-blue-400' : pt.cycle === 'yearly' ? 'bg-violet-400' : 'bg-amber-400']"></div>
                            <div class="px-4 pt-3.5 pb-3">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800">{{ pt.name }}</p>
                                    <span :class="['shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', cycleColor[pt.cycle]]">
                                        {{ cycleLabel[pt.cycle] }}
                                    </span>
                                </div>
                                <p class="mt-1 tabular-nums text-base font-bold text-slate-900">{{ formatRupiah(pt.amount) }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <span v-if="pt.grade" class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                                        Kelas {{ pt.grade }}
                                    </span>
                                    <span v-if="pt.is_exam_related" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
                                        <svg class="size-2.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                        </svg>
                                        Syarat Ujian
                                    </span>
                                    <span v-if="!pt.is_active" class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 ring-1 ring-slate-200">
                                        Nonaktif
                                    </span>
                                    <span v-if="pt.due_date" class="text-xs text-slate-400">Jatuh tempo: {{ pt.due_date }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-1 border-t border-slate-100 px-3 py-2.5">
                                <button
                                    @click="openEdit(pt)"
                                    aria-label="Edit tagihan"
                                    class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400 transition-all duration-150 hover:bg-slate-100 hover:text-slate-700"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteTarget = pt"
                                    aria-label="Hapus tagihan"
                                    class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400 transition-all duration-150 hover:bg-red-50 hover:text-red-500"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── Desktop table ───────────────────────────────────────── -->
                    <div class="hidden sm:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-gradient-to-r from-slate-50 to-slate-50/80">
                                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Nama Tagihan</th>
                                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Siklus</th>
                                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Nominal</th>
                                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Kelas</th>
                                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Jatuh Tempo</th>
                                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="pt in paginated"
                                    :key="pt.id"
                                    class="group transition-colors duration-100 hover:bg-emerald-50/40"
                                    :class="!pt.is_active ? 'opacity-60' : ''"
                                >
                                    <!-- Nama + badge ujian -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <!-- Dot warna siklus -->
                                            <span :class="[
                                                'size-2 shrink-0 rounded-full',
                                                pt.cycle === 'monthly' ? 'bg-blue-400' : pt.cycle === 'yearly' ? 'bg-violet-400' : 'bg-amber-400'
                                            ]"></span>
                                            <span class="text-sm font-semibold text-slate-800">{{ pt.name }}</span>
                                            <span v-if="pt.is_exam_related" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
                                                <svg class="size-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                                </svg>
                                                Ujian
                                            </span>
                                        </div>
                                    </td>
                                    <!-- Siklus badge -->
                                    <td class="px-5 py-4">
                                        <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', cycleColor[pt.cycle]]">
                                            {{ cycleLabel[pt.cycle] }}
                                        </span>
                                    </td>
                                    <!-- Nominal -->
                                    <td class="px-5 py-4">
                                        <span class="tabular-nums text-sm font-bold text-slate-800">{{ formatRupiah(pt.amount) }}</span>
                                    </td>
                                    <!-- Kelas -->
                                    <td class="px-5 py-4">
                                        <span v-if="pt.grade" class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                                            Kelas {{ pt.grade }}
                                        </span>
                                        <span v-else class="text-sm text-slate-400">Semua</span>
                                    </td>
                                    <!-- Jatuh tempo -->
                                    <td class="px-5 py-4">
                                        <span class="tabular-nums text-sm text-slate-600">{{ pt.due_date ?? '—' }}</span>
                                    </td>
                                    <!-- Status -->
                                    <td class="px-5 py-4">
                                        <span
                                            :class="pt.is_active
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                : 'bg-slate-100 text-slate-500 ring-slate-200'"
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                        >
                                            <span :class="['mr-1.5 size-1.5 rounded-full', pt.is_active ? 'bg-emerald-500' : 'bg-slate-400']"></span>
                                            {{ pt.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <!-- Aksi -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-1 opacity-0 transition-opacity duration-100 group-hover:opacity-100">
                                            <button @click="openEdit(pt)" aria-label="Edit tagihan"
                                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-all duration-150 hover:bg-emerald-100 hover:text-emerald-600">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>
                                            <button @click="deleteTarget = pt" aria-label="Hapus tagihan"
                                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-all duration-150 hover:bg-red-50 hover:text-red-500">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        v-if="totalPages > 1"
                        :current-page="currentPage"
                        :total-pages="totalPages"
                        :total="filtered.length"
                        :per-page="PER_PAGE"
                        label="tagihan"
                        @update:current-page="currentPage = $event"
                    />

                </template>
            </template>

        </div>

        <!-- ── Generate SPP Modal ──────────────────────────────────────────────── -->
        <Modal :show="showSpp" max-width="sm" @close="showSpp = false">
            <form @submit.prevent="submitSpp">
                <!-- Modal header gradient -->
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-white/20">
                            <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Generate SPP Bulanan</h3>
                    </div>
                    <button type="button" @click="showSpp = false"
                        class="flex size-7 items-center justify-center rounded-lg text-white/70 transition-all hover:bg-white/20 hover:text-white">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <p class="mb-4 text-sm text-slate-500">
                        Sistem akan otomatis membuat SPP untuk setiap bulan di tahun ajaran aktif
                        <span class="font-semibold text-slate-700">{{ activeYear?.name }}</span>.
                    </p>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal SPP <span class="text-red-500">*</span></label>
                        <input v-model.number="sppForm.amount" type="number" min="1000" placeholder="Contoh: 150000"
                            :class="['w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', sppForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="sppForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ sppForm.errors.amount }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="showSpp = false"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 transition-all hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="sppForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-emerald-600 disabled:opacity-60">
                        <svg v-if="sppForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ sppForm.processing ? 'Memproses...' : 'Generate SPP' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Create Modal ────────────────────────────────────────────────────── -->
        <Modal :show="showCreate" max-width="lg" @close="showCreate = false">
            <form @submit.prevent="submitCreate">
                <!-- Modal header gradient -->
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-white/20">
                            <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Tambah Jenis Tagihan</h3>
                    </div>
                    <button type="button" @click="showCreate = false"
                        class="flex size-7 items-center justify-center rounded-lg text-white/70 transition-all hover:bg-white/20 hover:text-white">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <!-- Nama -->
                    <div class="col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Tagihan <span class="text-red-500">*</span></label>
                        <input v-model="createForm.name" type="text" placeholder="Contoh: Uang Gedung"
                            :class="['w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.name ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.name }}</p>
                    </div>
                    <!-- Siklus -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Siklus <span class="text-red-500">*</span></label>
                        <FilterSelect v-model="createForm.cycle" :options="cycleOptions" block />
                        <p v-if="createForm.errors.cycle" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.cycle }}</p>
                    </div>
                    <!-- Nominal -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input v-model.number="createForm.amount" type="number" min="1000" placeholder="150000"
                            :class="['w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.amount }}</p>
                    </div>
                    <!-- Jatuh tempo -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input v-model="createForm.due_date" type="date"
                            :class="['w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.due_date ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.due_date" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.due_date }}</p>
                    </div>
                    <!-- Kelas (opsional) -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Kelas (opsional)</label>
                        <FilterSelect v-model="createForm.grade" :options="gradeOptions" block />
                    </div>
                    <!-- Toggle is_exam_related -->
                    <div class="col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Keterangan Tambahan</label>
                        <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition-colors hover:bg-slate-100"
                            :class="createForm.is_exam_related ? 'border-red-200 bg-red-50' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['flex size-8 items-center justify-center rounded-lg', createForm.is_exam_related ? 'bg-red-100' : 'bg-slate-200']">
                                    <svg class="size-4" :class="createForm.is_exam_related ? 'text-red-600' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                    </svg>
                                </div>
                                <div>
                                    <p :class="['text-sm font-semibold', createForm.is_exam_related ? 'text-red-700' : 'text-slate-700']">
                                        Wajib lunas untuk ujian
                                    </p>
                                    <p class="text-xs text-slate-400">Siswa tidak bisa ujian jika tagihan belum lunas</p>
                                </div>
                            </div>
                            <!-- Toggle switch -->
                            <div class="relative">
                                <input id="c-exam" v-model="createForm.is_exam_related" type="checkbox" class="sr-only" />
                                <div :class="['h-6 w-11 rounded-full transition-colors duration-200', createForm.is_exam_related ? 'bg-red-500' : 'bg-slate-300']"></div>
                                <div :class="['absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition-transform duration-200', createForm.is_exam_related ? 'translate-x-5' : 'translate-x-0']"></div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="showCreate = false"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 transition-all hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="createForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-emerald-600 disabled:opacity-60">
                        <svg v-if="createForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Edit Modal ──────────────────────────────────────────────────────── -->
        <Modal :show="!!editTarget" max-width="lg" @close="editTarget = null">
            <form @submit.prevent="submitEdit">
                <!-- Modal header gradient -->
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-white/20">
                            <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Edit Jenis Tagihan</h3>
                            <p class="text-xs text-white/70">{{ editTarget?.name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="editTarget = null"
                        class="flex size-7 items-center justify-center rounded-lg text-white/70 transition-all hover:bg-white/20 hover:text-white">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <div class="col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Tagihan <span class="text-red-500">*</span></label>
                        <input v-model="editForm.name" type="text"
                            :class="['w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.name ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input v-model.number="editForm.amount" type="number" min="1000"
                            :class="['w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="editForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.amount }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Jatuh Tempo</label>
                        <input v-model="editForm.due_date" type="date"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Kelas (opsional)</label>
                        <FilterSelect v-model="editForm.grade" :options="gradeOptions" block />
                    </div>
                    <!-- Status aktif -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Status</label>
                        <label class="flex cursor-pointer items-center justify-between rounded-xl border px-4 py-3 transition-colors"
                            :class="editForm.is_active ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50'"
                        >
                            <span :class="['text-sm font-semibold', editForm.is_active ? 'text-emerald-700' : 'text-slate-500']">
                                {{ editForm.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <div class="relative">
                                <input id="e-active" v-model="editForm.is_active" type="checkbox" class="sr-only" />
                                <div :class="['h-6 w-11 rounded-full transition-colors duration-200', editForm.is_active ? 'bg-emerald-500' : 'bg-slate-300']"></div>
                                <div :class="['absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition-transform duration-200', editForm.is_active ? 'translate-x-5' : 'translate-x-0']"></div>
                            </div>
                        </label>
                    </div>
                    <!-- Toggle syarat ujian -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Syarat Ujian</label>
                        <label class="flex cursor-pointer items-center justify-between rounded-xl border px-4 py-3 transition-colors"
                            :class="editForm.is_exam_related ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-slate-50'"
                        >
                            <span :class="['text-sm font-semibold', editForm.is_exam_related ? 'text-red-700' : 'text-slate-500']">
                                {{ editForm.is_exam_related ? 'Wajib Lunas' : 'Tidak Wajib' }}
                            </span>
                            <div class="relative">
                                <input id="e-exam" v-model="editForm.is_exam_related" type="checkbox" class="sr-only" />
                                <div :class="['h-6 w-11 rounded-full transition-colors duration-200', editForm.is_exam_related ? 'bg-red-500' : 'bg-slate-300']"></div>
                                <div :class="['absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition-transform duration-200', editForm.is_exam_related ? 'translate-x-5' : 'translate-x-0']"></div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="editTarget = null"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 transition-all hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="editForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-emerald-600 disabled:opacity-60">
                        <svg v-if="editForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ editForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Delete Confirm ──────────────────────────────────────────────────── -->
        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div class="px-6 py-6">
                <div class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-red-100">
                    <svg class="size-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Hapus Jenis Tagihan</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Semua tagihan siswa yang terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="deleteTarget = null"
                    class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 transition-all hover:bg-slate-100">
                    Batal
                </button>
                <button @click="submitDelete" :disabled="deleteForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-red-600 disabled:opacity-60">
                    <svg v-if="deleteForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
