<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    unpaidInvoices: { type: Array, required: true },
    summary:        { type: Object, default: null },
    activeYear:     { type: Object, default: null },
});

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val ?? 0);

// Group invoices by student
const grouped = computed(() => {
    const map = new Map();
    for (const inv of props.unpaidInvoices) {
        const sid = inv.student?.id;
        if (sid == null) continue; // skip invoice dengan relasi student null/broken
        if (!map.has(sid)) {
            map.set(sid, { student: inv.student, invoices: [] });
        }
        map.get(sid).invoices.push(inv);
    }
    return [...map.values()];
});

// ── Filters ───────────────────────────────────────────────────────────────────
const search       = ref('');
const filterStatus = ref('');
const filterGrade  = ref('');

const filtered = computed(() => {
    let groups = grouped.value;

    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        groups = groups.filter(
            g => g.student?.name?.toLowerCase().includes(q) || g.student?.nis?.toLowerCase().includes(q)
        );
    }

    if (filterGrade.value) {
        groups = groups.filter(g => String(g.student?.grade) === filterGrade.value);
    }

    if (filterStatus.value) {
        groups = groups.filter(g => g.invoices.some(i => i.status === filterStatus.value));
    }

    return groups;
});

const PER_PAGE    = 15;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});

watch([search, filterStatus, filterGrade], () => { currentPage.value = 1; });

const resetFilters = () => {
    search.value = '';
    filterStatus.value = '';
    filterGrade.value = '';
};

const hasActiveFilter = computed(() => search.value.trim() || filterStatus.value || filterGrade.value);

// Per-group helpers
const unpaidCount  = (group) => group.invoices.filter(i => i.status === 'unpaid').length;
const partialCount = (group) => group.invoices.filter(i => i.status === 'partial').length;
</script>

<template>
    <AppLayout>
        <Head title="Tagihan" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Keuangan</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tagihan</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Heading -->
            <div>
                <h2 class="text-balance text-lg font-bold text-slate-900">Tagihan Siswa</h2>
                <p class="text-pretty text-sm text-slate-500">
                    <span v-if="activeYear">Tahun ajaran {{ activeYear.name }} — tagihan yang belum atau kurang terbayar.</span>
                    <span v-else class="text-amber-600">Tidak ada tahun ajaran aktif.</span>
                </p>
            </div>

            <!-- Summary Cards -->
            <div v-if="summary" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm sm:block sm:p-5">
                    <p class="text-xs font-semibold uppercase text-slate-400">Total Tagihan</p>
                    <p class="tabular-nums text-base font-bold text-slate-800 sm:mt-1">{{ formatRupiah(summary.total_amount) }}</p>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm sm:block sm:p-5">
                    <p class="text-xs font-semibold uppercase text-slate-400">Terbayar</p>
                    <p class="tabular-nums text-base font-bold text-emerald-600 sm:mt-1">{{ formatRupiah(summary.total_paid) }}</p>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm sm:block sm:p-5">
                    <p class="text-xs font-semibold uppercase text-slate-400">Belum Bayar</p>
                    <p class="tabular-nums text-base font-bold text-red-500 sm:mt-1">{{ formatRupiah(summary.total_unpaid) }}</p>
                </div>
            </div>

            <!-- No active year -->
            <div v-if="!activeYear" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
                <p class="mt-1 text-xs text-slate-400">Hubungi operator untuk mengaktifkan tahun ajaran.</p>
            </div>

            <template v-else>

                <!-- Search & Filter -->
                <div class="space-y-2">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari nama / NIS..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-9 pr-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                        />
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Status filter -->
                        <div class="flex items-center gap-1">
                            <button
                                v-for="opt in [{ value: '', label: 'Semua' }, { value: 'unpaid', label: 'Belum Bayar' }, { value: 'partial', label: 'Kurang Bayar' }]"
                                :key="opt.value"
                                @click="filterStatus = opt.value"
                                :class="filterStatus === opt.value
                                    ? 'bg-emerald-500 text-white'
                                    : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-[background-color,color] duration-150"
                            >{{ opt.label }}</button>
                        </div>

                        <!-- Grade filter -->
                        <select
                            v-model="filterGrade"
                            class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-7 text-xs font-semibold text-slate-600 outline-none transition-[border-color] duration-150 focus:border-emerald-400"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="g in [1,2,3,4,5,6]" :key="g" :value="String(g)">Kelas {{ g }}</option>
                        </select>

                        <!-- Reset -->
                        <button
                            v-if="hasActiveFilter"
                            @click="resetFilters"
                            class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition-[color] duration-150"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="filtered.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
                >
                    <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">
                        {{ hasActiveFilter ? 'Tidak ada hasil' : 'Semua tagihan sudah lunas' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ hasActiveFilter ? 'Coba ubah kata kunci atau hapus filter.' : 'Tidak ada tagihan yang belum terbayar.' }}
                    </p>
                    <button v-if="hasActiveFilter" @click="resetFilters" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">
                        Reset pencarian
                    </button>
                </div>

                <!-- List -->
                <div v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <ul class="divide-y divide-slate-100">
                        <li v-for="group in paginated" :key="group.student?.id">
                            <Link
                                :href="route('keuangan.invoices.show', group.student?.id)"
                                class="flex items-start gap-3 px-4 py-4 transition-[background-color] duration-150 hover:bg-slate-50 sm:items-center sm:px-5 sm:py-3.5"
                            >
                                <!-- Avatar -->
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                                    {{ group.student?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <!-- Name + chevron row -->
                                    <div class="flex items-start justify-between gap-2 sm:items-center">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-slate-800">{{ group.student?.name }}</p>
                                            <p class="text-xs text-slate-400">
                                                NIS {{ group.student?.nis }}
                                                <template v-if="group.student?.grade"> · Kelas {{ group.student.grade }}</template>
                                            </p>
                                        </div>

                                        <!-- Desktop: status summary inline -->
                                        <div class="hidden shrink-0 items-center gap-3 sm:flex">
                                            <div class="text-right">
                                                <p class="text-xs text-slate-400">{{ group.invoices.length }} tagihan belum lunas</p>
                                                <div class="mt-0.5 flex items-center justify-end gap-1">
                                                    <span v-if="unpaidCount(group) > 0"
                                                        class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">
                                                        {{ unpaidCount(group) }} belum bayar
                                                    </span>
                                                    <span v-if="partialCount(group) > 0"
                                                        class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                                        {{ partialCount(group) }} kurang bayar
                                                    </span>
                                                </div>
                                            </div>
                                            <svg class="size-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </div>

                                        <!-- Mobile: just chevron -->
                                        <svg class="mt-0.5 size-4 shrink-0 text-slate-300 sm:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </div>

                                    <!-- Mobile: status badges below, wrapping naturally -->
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5 sm:hidden">
                                        <span class="text-xs text-slate-400">{{ group.invoices.length }} tagihan belum lunas</span>
                                        <span v-if="unpaidCount(group) > 0"
                                            class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">
                                            {{ unpaidCount(group) }} belum bayar
                                        </span>
                                        <span v-if="partialCount(group) > 0"
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                            {{ partialCount(group) }} kurang bayar
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </li>
                    </ul>
                </div>

                <Pagination
                    v-if="totalPages > 1"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :total="filtered.length"
                    :per-page="PER_PAGE"
                    label="siswa"
                    @update:current-page="currentPage = $event"
                />

            </template>

        </div>
    </AppLayout>
</template>
