<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    honorariums:   { type: Array,  required: true },
    academicYears: { type: Array,  required: true },
    filters:       { type: Object, default: () => ({}) },
});

// ── Filter ─────────────────────────────────────────────────────────────────────
const filterMonth  = ref(props.filters.month  ?? '');
const filterYear   = ref(props.filters.year   ?? '');
const filterStatus = ref(props.filters.status ?? '');
const search       = ref('');

watch([filterMonth, filterYear, filterStatus], () => {
    router.get(route('kamad.honorariums.index'), {
        month:  filterMonth.value  || undefined,
        year:   filterYear.value   || undefined,
        status: filterStatus.value || undefined,
    }, { preserveState: true });
});

const filtered = computed(() => {
    if (!search.value.trim()) return props.honorariums;
    const q = search.value.toLowerCase();
    return props.honorariums.filter(h => h.teacher?.user?.name?.toLowerCase().includes(q));
});

const hasFilter = computed(() => search.value.trim() || filterMonth.value || filterYear.value || filterStatus.value);
const resetFilters = () => {
    search.value = ''; filterMonth.value = ''; filterYear.value = ''; filterStatus.value = '';
    router.get(route('kamad.honorariums.index'));
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

// ── Summary ────────────────────────────────────────────────────────────────────
const totalAll   = computed(() => filtered.value.reduce((s, h) => s + h.total_amount, 0));
const totalPaid  = computed(() => filtered.value.filter(h => h.status === 'paid').reduce((s, h) => s + h.total_amount, 0));
const totalDraft = computed(() => filtered.value.filter(h => h.status === 'draft').reduce((s, h) => s + h.total_amount, 0));
const countDraft = computed(() => filtered.value.filter(h => h.status === 'draft').length);

// ── Helpers ────────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const periodLabel = (h) => `${monthNames[h.period_month]} ${h.period_year}`;

const statusConfig = {
    draft: { label: 'Belum Dibayar', badge: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' },
    paid:  { label: 'Lunas',         badge: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' },
};

const now          = new Date();
const monthOptions = monthNames.slice(1).map((name, i) => ({ value: i + 1, label: name }));
const yearOptions  = [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1];

const avatarColor = (name) => {
    const colors = [
        'bg-violet-100 text-violet-700',
        'bg-sky-100 text-sky-700',
        'bg-emerald-100 text-emerald-700',
        'bg-rose-100 text-rose-700',
        'bg-amber-100 text-amber-700',
        'bg-indigo-100 text-indigo-700',
    ];
    const idx = (name?.charCodeAt(0) ?? 0) % colors.length;
    return colors[idx];
};
</script>

<template>
    <AppLayout>
        <Head title="Rekap Honor Guru" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Kamad</span><span>/</span>
                <span class="font-semibold text-slate-700">Honor Guru</span>
            </div>
        </template>

        <div class="space-y-5 pb-8">

            <!-- Heading -->
            <div>
                <h2 class="text-lg font-bold text-slate-900">Rekap Honor Guru</h2>
                <p class="text-sm text-slate-500">Pantau status pembayaran honor seluruh guru.</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Total -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="pointer-events-none absolute right-0 top-0 size-20 rounded-full bg-slate-50 blur-2xl" />
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="inline-flex size-10 items-center justify-center rounded-xl bg-slate-100 ring-4 ring-slate-50">
                                <svg class="size-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-400">{{ filtered.length }} slip</span>
                        </div>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Total Honor</p>
                        <p class="mt-0.5 tabular-nums text-xl font-extrabold text-slate-800">Rp {{ fmt(totalAll) }}</p>
                    </div>
                </div>

                <!-- Sudah Dibayar -->
                <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="pointer-events-none absolute right-0 top-0 size-20 rounded-full bg-emerald-50 blur-2xl" />
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="inline-flex size-10 items-center justify-center rounded-xl bg-emerald-50 ring-4 ring-emerald-100">
                                <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">
                                {{ filtered.filter(h => h.status === 'paid').length }} slip
                            </span>
                        </div>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Sudah Dibayar</p>
                        <p class="mt-0.5 tabular-nums text-xl font-extrabold text-emerald-600">Rp {{ fmt(totalPaid) }}</p>
                    </div>
                </div>

                <!-- Belum Dibayar -->
                <div class="relative overflow-hidden rounded-2xl border bg-white p-5 shadow-sm"
                    :class="countDraft > 0 ? 'border-amber-200' : 'border-slate-100'">
                    <div class="pointer-events-none absolute right-0 top-0 size-20 rounded-full bg-amber-50 blur-2xl" />
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="inline-flex size-10 items-center justify-center rounded-xl ring-4"
                                :class="countDraft > 0 ? 'bg-amber-50 ring-amber-100' : 'bg-slate-50 ring-slate-100'">
                                <svg class="size-5" :class="countDraft > 0 ? 'text-amber-500' : 'text-slate-300'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span v-if="countDraft > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600">
                                <span class="relative flex size-1.5">
                                    <span class="animate-ping absolute inline-flex size-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex size-1.5 rounded-full bg-amber-500"></span>
                                </span>
                                {{ countDraft }} slip
                            </span>
                            <span v-else class="text-xs font-semibold text-slate-300">0 slip</span>
                        </div>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Belum Dibayar</p>
                        <p class="mt-0.5 tabular-nums text-xl font-extrabold"
                            :class="countDraft > 0 ? 'text-amber-500' : 'text-slate-300'">
                            Rp {{ fmt(totalDraft) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                    </svg>
                    <input v-model="search" type="search" placeholder="Cari nama guru..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"/>
                </div>
                <div class="h-5 w-px bg-slate-200"/>
                <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
                    <button v-for="opt in [{ value: '', label: 'Semua' }, { value: 'draft', label: 'Belum Bayar' }, { value: 'paid', label: 'Lunas' }]"
                        :key="opt.value" @click="filterStatus = opt.value"
                        :class="filterStatus === opt.value
                            ? 'bg-white text-slate-800 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700'"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150">
                        {{ opt.label }}
                    </button>
                </div>
                <FilterSelect v-model="filterMonth" :options="[{ value: '', label: 'Semua Bulan' }, ...monthOptions]" />
                <FilterSelect v-model="filterYear" :options="[{ value: '', label: 'Semua Tahun' }, ...yearOptions.map(y => ({ value: y, label: String(y) }))]" />
                <button v-if="hasFilter" @click="resetFilters"
                    class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors">Reset</button>
            </div>

            <!-- Empty -->
            <div v-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center">
                <div class="mb-3 flex size-12 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="size-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">{{ hasFilter ? 'Tidak ada hasil' : 'Belum ada data honor' }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ hasFilter ? 'Coba ubah filter pencarian' : 'Data honor akan muncul di sini' }}</p>
                <button v-if="hasFilter" @click="resetFilters" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset filter</button>
            </div>

            <!-- Table -->
            <div v-else class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">

                <!-- Mobile list -->
                <ul class="divide-y divide-slate-100 sm:hidden">
                    <li v-for="h in paginated" :key="h.id"
                        class="transition-colors hover:bg-slate-50/60"
                        :class="h.status === 'draft' ? 'border-l-2 border-amber-400' : 'border-l-2 border-transparent'">
                        <div class="space-y-3 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                                        :class="avatarColor(h.teacher?.user?.name)">
                                        {{ h.teacher?.user?.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ h.teacher?.user?.name }}</p>
                                        <p class="text-xs text-slate-400">{{ periodLabel(h) }} · {{ h.academic_year?.name }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="statusConfig[h.status]?.badge">
                                    {{ statusConfig[h.status]?.label }}
                                </span>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2.5 space-y-1.5">
                                <div class="grid grid-cols-2 gap-2 text-center">
                                    <div>
                                        <p class="text-[11px] text-slate-400">Jam Pelajaran</p>
                                        <p class="text-xs font-bold text-slate-700">Rp {{ fmt(h.teaching_hours_amount) }}</p>
                                        <p class="text-[11px] text-slate-400">{{ h.teaching_hours }} jam</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-slate-400">Transport</p>
                                        <p class="text-xs font-bold text-slate-700">Rp {{ fmt(h.transport_amount) }}</p>
                                        <p class="text-[11px] text-slate-400">{{ h.transport_days }} hari</p>
                                    </div>
                                </div>
                                <div v-if="h.position_allowance > 0"
                                    class="flex items-center justify-between border-t border-slate-200 pt-1.5">
                                    <span class="text-xs font-medium text-amber-600">{{ h.position_name }}</span>
                                    <span class="text-xs font-bold text-amber-700">Rp {{ fmt(h.position_allowance) }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-200 pt-1.5">
                                    <span class="text-xs font-semibold text-slate-500">Total</span>
                                    <span class="text-sm font-bold text-slate-900">Rp {{ fmt(h.total_amount) }}</span>
                                </div>
                            </div>
                            <div v-if="h.status === 'paid'" class="text-xs text-slate-400">
                                Dibayar oleh <span class="font-medium text-slate-600">{{ h.tu_keuangan?.name ?? '—' }}</span>
                                · {{ h.paid_at ? new Date(h.paid_at).toLocaleDateString('id-ID') : '' }}
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Desktop table -->
                <table class="hidden w-full sm:table">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="w-8 py-3 pl-4 pr-2"></th>
                            <th class="py-3 pl-2 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Guru</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Periode</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Jam Pelajaran</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Transport</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Total</th>
                            <th class="py-3 pl-4 pr-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="h in paginated" :key="h.id"
                            class="transition-colors hover:bg-slate-50/60">

                            <!-- Left indicator -->
                            <td class="w-1 py-3.5 pl-0 pr-0">
                                <div class="h-full w-0.5 rounded-full mx-auto"
                                    :class="h.status === 'draft' ? 'bg-amber-400' : 'bg-transparent'">
                                </div>
                            </td>

                            <!-- Guru -->
                            <td class="py-3.5 pl-2 pr-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                                        :class="avatarColor(h.teacher?.user?.name)">
                                        {{ h.teacher?.user?.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ h.teacher?.user?.name }}</p>
                                        <p class="truncate text-xs text-slate-400">{{ h.academic_year?.name }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Periode -->
                            <td class="px-4 py-3.5">
                                <p class="whitespace-nowrap text-sm text-slate-700">{{ periodLabel(h) }}</p>
                            </td>

                            <!-- Jam Pelajaran -->
                            <td class="px-4 py-3.5 text-right">
                                <p class="whitespace-nowrap text-sm font-medium text-slate-800">Rp {{ fmt(h.teaching_hours_amount) }}</p>
                                <p class="text-xs text-slate-400">{{ h.teaching_hours }} jam</p>
                            </td>

                            <!-- Transport -->
                            <td class="px-4 py-3.5 text-right">
                                <p class="whitespace-nowrap text-sm font-medium text-slate-800">Rp {{ fmt(h.transport_amount) }}</p>
                                <p class="text-xs text-slate-400">{{ h.transport_days }} hari</p>
                            </td>

                            <!-- Total -->
                            <td class="px-4 py-3.5 text-right">
                                <p class="whitespace-nowrap text-sm font-bold text-slate-900">Rp {{ fmt(h.total_amount) }}</p>
                                <p v-if="h.position_allowance > 0" class="text-xs text-amber-600">+ tunjangan jabatan</p>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 pl-4 pr-5">
                                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="statusConfig[h.status]?.badge">
                                    {{ statusConfig[h.status]?.label }}
                                </span>
                                <p v-if="h.status === 'paid'" class="mt-0.5 truncate text-xs text-slate-400">
                                    {{ h.tu_keuangan?.name ?? '—' }}
                                    · {{ h.paid_at ? new Date(h.paid_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '' }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                    <!-- Footer -->
                    <tfoot v-if="paginated.length > 0">
                        <tr class="border-t border-slate-100 bg-slate-50/50">
                            <td colspan="5" class="py-3 pl-4 pr-4 text-xs font-medium text-slate-400">
                                Menampilkan {{ paginated.length }} dari {{ filtered.length }} slip
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-slate-700">
                                Rp {{ fmt(paginated.reduce((s, h) => s + h.total_amount, 0)) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <Pagination
                :current-page="currentPage" :total-pages="totalPages"
                :total="filtered.length" :per-page="PER_PAGE" label="slip"
                @update:current-page="currentPage = $event"
            />

        </div>
    </AppLayout>
</template>
