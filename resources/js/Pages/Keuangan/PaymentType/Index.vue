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

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Jenis Tagihan</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Kelola jenis tagihan siswa — SPP bulanan, tahunan, maupun sekali bayar.
                    </p>
                </div>
                <div v-if="activeYear" class="flex shrink-0 items-center gap-2">
                    <button
                        @click="showSpp = true"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-[background-color] duration-150 hover:bg-slate-50 sm:flex-none"
                    >
                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                        </svg>
                        Generate SPP
                    </button>
                    <button
                        @click="openCreate"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600 sm:flex-none"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>

            <!-- No active year -->
            <div
                v-if="!activeYear"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
                <p class="mt-1 text-xs text-slate-400">Aktifkan tahun ajaran terlebih dahulu.</p>
            </div>

            <!-- Empty state -->
            <div
                v-else-if="paymentTypes.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada jenis tagihan</p>
                <p class="mt-1 text-xs text-slate-400">Tambah tagihan atau generate SPP bulanan.</p>
                <div class="mt-4 flex items-center gap-2">
                    <button @click="showSpp = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition-[background-color] duration-150 hover:bg-slate-50">
                        Generate SPP
                    </button>
                    <button @click="openCreate"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Tagihan
                    </button>
                </div>
            </div>

            <!-- Search & Filter -->
            <template v-else>

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
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
                >
                    <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                    <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau hapus filter.</p>
                    <button @click="resetFilters" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
                </div>

                <template v-else>

                    <!-- ── Mobile cards (primary) ────────────────────────────────── -->
                    <div class="space-y-2 sm:hidden">
                        <div
                            v-for="pt in paginated"
                            :key="pt.id"
                            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                            :class="!pt.is_active ? 'opacity-60' : ''"
                        >
                            <div class="px-4 pt-4 pb-3">
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
                                    <span v-if="pt.is_exam_related" class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
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
                                    class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteTarget = pt"
                                    aria-label="Hapus tagihan"
                                    class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── Desktop table (secondary) ─────────────────────────────── -->
                    <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Nama Tagihan</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Siklus</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Nominal</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Kelas</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Jatuh Tempo</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Status</th>
                                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="pt in paginated"
                                    :key="pt.id"
                                    class="transition-[background-color] duration-150 hover:bg-slate-50"
                                    :class="!pt.is_active ? 'opacity-60' : ''"
                                >
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-800">{{ pt.name }}</span>
                                            <span v-if="pt.is_exam_related" class="inline-flex items-center rounded-full bg-red-50 px-1.5 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">Ujian</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', cycleColor[pt.cycle]]">
                                            {{ cycleLabel[pt.cycle] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="tabular-nums text-sm font-semibold text-slate-700">{{ formatRupiah(pt.amount) }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span v-if="pt.grade" class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                                            Kelas {{ pt.grade }}
                                        </span>
                                        <span v-else class="text-sm text-slate-400">Semua</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="tabular-nums text-sm text-slate-600">{{ pt.due_date ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span
                                            :class="pt.is_active
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                : 'bg-slate-100 text-slate-500 ring-slate-200'"
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                        >
                                            {{ pt.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click="openEdit(pt)" aria-label="Edit tagihan"
                                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>
                                            <button @click="deleteTarget = pt" aria-label="Hapus tagihan"
                                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500">
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
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Generate SPP Bulanan</h3>
                    <button type="button" @click="showSpp = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
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
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', sppForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="sppForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ sppForm.errors.amount }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="showSpp = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="sppForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60">
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
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Tambah Jenis Tagihan</h3>
                    <button type="button" @click="showCreate = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
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
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.name ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.name }}</p>
                    </div>
                    <!-- Siklus -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Siklus <span class="text-red-500">*</span></label>
                        <select v-model="createForm.cycle"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.cycle ? 'border-red-400' : 'border-slate-200']">
                            <option value="once">Sekali Bayar</option>
                            <option value="yearly">Tahunan</option>
                            <option value="monthly">Bulanan</option>
                        </select>
                        <p v-if="createForm.errors.cycle" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.cycle }}</p>
                    </div>
                    <!-- Nominal -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input v-model.number="createForm.amount" type="number" min="1000" placeholder="150000"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.amount }}</p>
                    </div>
                    <!-- Jatuh tempo -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input v-model="createForm.due_date" type="date"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.due_date ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.due_date" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.due_date }}</p>
                    </div>
                    <!-- Kelas (opsional) -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Kelas (opsional)</label>
                        <select v-model="createForm.grade"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20">
                            <option value="">Semua kelas</option>
                            <option v-for="g in [1,2,3,4,5,6]" :key="g" :value="g">Kelas {{ g }}</option>
                        </select>
                    </div>
                    <!-- Syarat ujian -->
                    <div class="col-span-2 flex items-center gap-2">
                        <input id="c-exam" v-model="createForm.is_exam_related" type="checkbox"
                            class="size-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400" />
                        <label for="c-exam" class="text-sm text-slate-700">Wajib lunas untuk mengikuti ujian</label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="showCreate = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="createForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60">
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
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Edit Jenis Tagihan</h3>
                    <button type="button" @click="editTarget = null"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <div class="col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Tagihan <span class="text-red-500">*</span></label>
                        <input v-model="editForm.name" type="text"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.name ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input v-model.number="editForm.amount" type="number" min="1000"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.amount ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="editForm.errors.amount" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.amount }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Jatuh Tempo</label>
                        <input v-model="editForm.due_date" type="date"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Kelas (opsional)</label>
                        <select v-model="editForm.grade"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20">
                            <option value="">Semua kelas</option>
                            <option v-for="g in [1,2,3,4,5,6]" :key="g" :value="g">Kelas {{ g }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="e-exam" v-model="editForm.is_exam_related" type="checkbox"
                            class="size-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400" />
                        <label for="e-exam" class="text-sm text-slate-700">Syarat ujian</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="e-active" v-model="editForm.is_active" type="checkbox"
                            class="size-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400" />
                        <label for="e-active" class="text-sm text-slate-700">Aktif</label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="editTarget = null"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="editForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60">
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
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Hapus Jenis Tagihan</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Semua tagihan siswa yang terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="deleteTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                    Batal
                </button>
                <button @click="submitDelete" :disabled="deleteForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60">
                    <svg v-if="deleteForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
