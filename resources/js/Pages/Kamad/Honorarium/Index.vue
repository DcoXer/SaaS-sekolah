<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
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

// ── Helpers ────────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const periodLabel = (h) => `${monthNames[h.period_month]} ${h.period_year}`;

const statusConfig = {
    draft: { label: 'Belum Dibayar', badge: 'bg-amber-50 text-amber-700' },
    paid:  { label: 'Lunas',         badge: 'bg-emerald-50 text-emerald-700' },
};

const now          = new Date();
const monthOptions = monthNames.slice(1).map((name, i) => ({ value: i + 1, label: name }));
const yearOptions  = [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1];
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

        <div class="space-y-5">

            <!-- Heading -->
            <div>
                <h2 class="text-lg font-bold text-slate-900">Rekap Honor Guru</h2>
                <p class="text-sm text-slate-500">Pantau status pembayaran honor seluruh guru.</p>
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
                <p class="text-sm font-semibold text-slate-700">{{ hasFilter ? 'Tidak ada hasil' : 'Belum ada data honor' }}</p>
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
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Status & Bayar</div>
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
                                        <p class="text-xs text-slate-400">{{ periodLabel(h) }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="statusConfig[h.status]?.badge">
                                    {{ statusConfig[h.status]?.label }}
                                </span>
                            </div>
                            <div class="rounded-lg bg-slate-50 px-3 py-2.5 space-y-1.5">
                                <div class="grid grid-cols-2 gap-2 text-center">
                                    <div>
                                        <p class="text-xs text-slate-400">Jam Pelajaran</p>
                                        <p class="text-xs font-bold text-slate-700">Rp {{ fmt(h.teaching_hours_amount) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400">Transport</p>
                                        <p class="text-xs font-bold text-slate-700">Rp {{ fmt(h.transport_amount) }}</p>
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
                            <div v-if="h.status === 'paid'" class="text-xs text-slate-400">
                                Dibayar oleh {{ h.tu_keuangan?.name ?? '—' }}
                                · {{ h.paid_at ? new Date(h.paid_at).toLocaleDateString('id-ID') : '' }}
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
                            <div class="col-span-1 text-right font-bold text-slate-900 text-sm">
                                Rp {{ fmt(h.total_amount) }}
                            </div>
                            <div class="col-span-2">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="statusConfig[h.status]?.badge">
                                    {{ statusConfig[h.status]?.label }}
                                </span>
                                <p v-if="h.status === 'paid'" class="mt-0.5 text-xs text-slate-400 truncate">
                                    {{ h.tu_keuangan?.name ?? '—' }}
                                </p>
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
    </AppLayout>
</template>
