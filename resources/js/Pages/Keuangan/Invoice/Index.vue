<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
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

        <div class="space-y-6">

            <!-- ── Hero Banner ─────────────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500 px-6 py-6 shadow-lg shadow-emerald-200/60">
                <!-- Decorative circles -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-10 right-16 size-28 rounded-full bg-white/8"></div>

                <div class="relative flex items-center gap-4">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm ring-1 ring-white/30">
                        <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Tagihan Siswa</h1>
                        <p class="mt-0.5 text-sm text-emerald-100">
                            <span v-if="activeYear">Tahun ajaran {{ activeYear.name }} — tagihan belum atau kurang terbayar</span>
                            <span v-else>Tidak ada tahun ajaran aktif saat ini</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Summary Cards ───────────────────────────────────────────────── -->
            <div v-if="summary" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Total Tagihan -->
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="absolute right-0 top-0 h-full w-1.5 rounded-r-2xl bg-slate-200"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tagihan</p>
                            <p class="mt-2 text-xl font-extrabold tabular-nums text-slate-800">{{ formatRupiah(summary.total_amount) }}</p>
                        </div>
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100">
                            <svg class="size-4.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2.5 text-xs text-slate-400">Seluruh tagihan aktif</p>
                </div>

                <!-- Belum Lunas -->
                <div class="group relative overflow-hidden rounded-2xl border border-red-100 bg-gradient-to-br from-red-50 to-white px-5 py-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="absolute right-0 top-0 h-full w-1.5 rounded-r-2xl bg-red-400"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-red-400">Belum Lunas</p>
                            <p class="mt-2 text-xl font-extrabold tabular-nums text-red-600">{{ formatRupiah(summary.total_unpaid) }}</p>
                        </div>
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-red-100">
                            <svg class="size-4.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2.5 text-xs text-red-400">Perlu segera ditindaklanjuti</p>
                </div>

                <!-- Sudah Lunas -->
                <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white px-5 py-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="absolute right-0 top-0 h-full w-1.5 rounded-r-2xl bg-emerald-400"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Sudah Terbayar</p>
                            <p class="mt-2 text-xl font-extrabold tabular-nums text-emerald-600">{{ formatRupiah(summary.total_paid) }}</p>
                        </div>
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                            <svg class="size-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2.5 text-xs text-emerald-500">Pembayaran telah dikonfirmasi</p>
                </div>
            </div>

            <!-- No active year -->
            <div v-if="!activeYear" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-amber-50/50 py-20 text-center">
                <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-amber-100">
                    <svg class="size-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-amber-800">Tidak ada tahun ajaran aktif</p>
                <p class="mt-1.5 max-w-xs text-xs text-amber-600">Hubungi operator untuk mengaktifkan tahun ajaran terlebih dahulu.</p>
            </div>

            <template v-else>

                <!-- ── Search & Filter Bar ─────────────────────────────────────── -->
                <div class="flex flex-wrap items-center gap-2.5 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm">
                    <!-- Search -->
                    <div class="relative min-w-[200px] flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari nama atau NIS siswa..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2.5 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-all duration-150 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
                        />
                    </div>

                    <!-- Divider -->
                    <div class="h-6 w-px bg-slate-200 max-sm:hidden"/>

                    <!-- Status toggle -->
                    <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
                        <button
                            v-for="opt in [
                                { value: '', label: 'Semua' },
                                { value: 'unpaid', label: 'Belum Bayar' },
                                { value: 'partial', label: 'Kurang Bayar' },
                            ]"
                            :key="opt.value"
                            @click="filterStatus = opt.value"
                            :class="filterStatus === opt.value
                                ? 'bg-white text-slate-800 shadow-sm'
                                : 'text-slate-500 hover:text-slate-700'"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150"
                        >{{ opt.label }}</button>
                    </div>

                    <!-- Grade filter -->
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

                    <!-- Reset -->
                    <button
                        v-if="hasActiveFilter"
                        @click="resetFilters"
                        class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-400 transition-all duration-150 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </button>
                </div>

                <!-- Result count hint -->
                <div v-if="hasActiveFilter" class="flex items-center gap-1.5 px-1">
                    <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21L21 17.25" />
                    </svg>
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-semibold text-slate-700">{{ filtered.length }}</span> siswa
                    </p>
                </div>

                <!-- ── Empty State ──────────────────────────────────────────────── -->
                <div
                    v-if="filtered.length === 0"
                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center"
                >
                    <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-slate-100">
                        <svg class="size-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">
                        {{ hasActiveFilter ? 'Tidak ada hasil yang cocok' : 'Semua tagihan sudah lunas' }}
                    </p>
                    <p class="mt-1.5 max-w-xs text-xs text-slate-400">
                        {{ hasActiveFilter ? 'Coba ubah kata kunci atau hapus filter yang aktif.' : 'Tidak ada tagihan yang belum terbayar saat ini.' }}
                    </p>
                    <button v-if="hasActiveFilter" @click="resetFilters"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 transition-colors duration-150 hover:bg-emerald-100">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Hapus semua filter
                    </button>
                </div>

                <!-- ── Student List ─────────────────────────────────────────────── -->
                <div v-else class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

                    <!-- Table header (desktop) -->
                    <div class="hidden border-b border-slate-100 bg-slate-50/70 px-5 py-3 sm:grid sm:grid-cols-12 sm:gap-4">
                        <div class="col-span-5 text-xs font-semibold uppercase tracking-wider text-slate-400">Siswa</div>
                        <div class="col-span-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Status Tagihan</div>
                        <div class="col-span-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Jumlah</div>
                        <div class="col-span-1"></div>
                    </div>

                    <ul class="divide-y divide-slate-100">
                        <li v-for="group in paginated" :key="group.student?.id">
                            <Link
                                :href="route('keuangan.invoices.show', group.student?.id)"
                                class="group flex items-center gap-3 px-4 py-4 transition-colors duration-150 hover:bg-emerald-50/40 sm:grid sm:grid-cols-12 sm:gap-4 sm:px-5 sm:py-3.5"
                            >
                                <!-- Avatar + info (col 5) -->
                                <div class="col-span-5 flex min-w-0 items-center gap-3">
                                    <div class="relative flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-sm font-bold text-white shadow-sm">
                                        {{ group.student?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800 group-hover:text-emerald-700">{{ group.student?.name }}</p>
                                        <p class="flex items-center gap-1.5 text-xs text-slate-400">
                                            <span>NIS {{ group.student?.nis }}</span>
                                            <template v-if="group.student?.grade">
                                                <span class="size-1 rounded-full bg-slate-300"></span>
                                                <span>Kelas {{ group.student.grade }}</span>
                                            </template>
                                        </p>
                                    </div>
                                </div>

                                <!-- Status badges (col 3) -->
                                <div class="col-span-3 hidden sm:block">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-if="unpaidCount(group) > 0"
                                            class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-100">
                                            <span class="size-1.5 rounded-full bg-red-500"></span>
                                            {{ unpaidCount(group) }} belum bayar
                                        </span>
                                        <span v-if="partialCount(group) > 0"
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                            <span class="size-1.5 rounded-full bg-amber-500"></span>
                                            {{ partialCount(group) }} kurang bayar
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">{{ group.invoices.length }} tagihan aktif</p>
                                </div>

                                <!-- Amount placeholder (col 3) -->
                                <div class="col-span-3 hidden text-right sm:block">
                                    <p class="text-xs text-slate-400">Lihat detail</p>
                                </div>

                                <!-- Chevron (col 1) -->
                                <div class="col-span-1 hidden justify-end sm:flex">
                                    <svg class="size-4 text-slate-300 transition-transform duration-150 group-hover:translate-x-0.5 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>

                                <!-- Mobile right side -->
                                <div class="ml-auto flex shrink-0 items-center gap-2 sm:hidden">
                                    <div class="flex flex-col items-end gap-1">
                                        <span v-if="unpaidCount(group) > 0"
                                            class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">
                                            {{ unpaidCount(group) }} belum bayar
                                        </span>
                                        <span v-if="partialCount(group) > 0"
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                            {{ partialCount(group) }} kurang bayar
                                        </span>
                                    </div>
                                    <svg class="size-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </Link>
                        </li>
                    </ul>

                    <!-- Footer count -->
                    <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-2.5">
                        <p class="text-xs text-slate-400">
                            Total <span class="font-semibold text-slate-600">{{ filtered.length }}</span> siswa dengan tagihan aktif
                        </p>
                    </div>
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
