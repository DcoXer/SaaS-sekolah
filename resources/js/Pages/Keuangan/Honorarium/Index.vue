<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

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
    return props.honorariums.filter(h => h.teacher?.user?.name?.toLowerCase().includes(q));
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
const showGenerate     = ref(false);
const showGenerateAll  = ref(false);
const showGenerateMenu = ref(false);
const now             = new Date();

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

const generateAllForm = useForm({
    academic_year_id: props.activeYear?.id ?? '',
    period_month:     now.getMonth() + 1,
    period_year:      now.getFullYear(),
});

const submitGenerateAll = () => {
    generateAllForm.post(route('keuangan.honorariums.generate-all'), {
        onSuccess: () => { showGenerateAll.value = false; generateAllForm.reset('period_month', 'period_year'); },
    });
};

// ── Send WA (single) ───────────────────────────────────────────────────────────
const sendTarget = ref(null);
const sendForm   = useForm({});
const submitSend = () => {
    sendForm.post(route('keuangan.honorariums.send-slip', sendTarget.value.id), {
        onSuccess: () => { sendTarget.value = null; },
    });
};

// ── Send All WA ────────────────────────────────────────────────────────────────
const showSendAll    = ref(false);
const sendAllForm    = useForm({
    period_month: now.getMonth() + 1,
    period_year:  now.getFullYear(),
});
const submitSendAll = () => {
    sendAllForm.post(route('keuangan.honorariums.send-all-slips'), {
        onSuccess: () => { showSendAll.value = false; },
    });
};

// ── Mark Paid (single) ─────────────────────────────────────────────────────────
const paidTarget = ref(null);
const paidForm   = useForm({});
const submitMarkPaid = () => {
    paidForm.patch(route('keuangan.honorariums.mark-paid', paidTarget.value.id), {
        onSuccess: () => { paidTarget.value = null; },
    });
};

// ── Mark All Paid ──────────────────────────────────────────────────────────────
const showMarkAllPaid  = ref(false);
const markAllPaidForm  = useForm({
    period_month: now.getMonth() + 1,
    period_year:  now.getFullYear(),
});
const submitMarkAllPaid = () => {
    markAllPaidForm.post(route('keuangan.honorariums.mark-all-paid'), {
        onSuccess: () => { showMarkAllPaid.value = false; },
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
    draft: { label: 'Belum Dibayar', badge: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' },
    paid:  { label: 'Lunas',         badge: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' },
};

const monthOptions = monthNames.slice(1).map((name, i) => ({ value: i + 1, label: name }));
const yearOptions  = [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1];

// ── Generate modal select options ──────────────────────────────────────────────
const teachersWithHoursOptions = computed(() =>
    props.teachersWithHours.map(t => ({ value: t.id, label: t.user.name }))
);
const academicYearOptions = computed(() =>
    props.academicYears.map(y => ({ value: y.id, label: y.name }))
);
const periodMonthOptions = monthOptions;
const periodYearOptions  = yearOptions.map(y => ({ value: y, label: String(y) }));

// Close generate dropdown on outside click
const closeGenerateMenu = () => { showGenerateMenu.value = false; };
onMounted(() => document.addEventListener('click', closeGenerateMenu));
onUnmounted(() => document.removeEventListener('click', closeGenerateMenu));

// Summary
const totalAll    = computed(() => filtered.value.reduce((s, h) => s + h.total_amount, 0));
const totalPaid   = computed(() => filtered.value.filter(h => h.status === 'paid').reduce((s, h) => s + h.total_amount, 0));
const totalDraft  = computed(() => filtered.value.filter(h => h.status === 'draft').reduce((s, h) => s + h.total_amount, 0));
const hasDraftSlips = computed(() => filtered.value.some(h => h.status === 'draft'));
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

            <!-- Header Banner -->
            <div class="relative rounded-2xl bg-gradient-to-br from-amber-500 via-amber-400 to-orange-400 p-6 text-white shadow-lg">
                <!-- Decorative circles (clipped inside their own layer) -->
                <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-2xl">
                    <div class="absolute -right-6 -top-6 size-40 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-8 right-16 size-28 rounded-full bg-white/10"></div>
                    <div class="absolute bottom-3 right-3 size-14 rounded-full bg-orange-300/30"></div>
                </div>

                <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Title row -->
                    <div class="flex items-center gap-3">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-white/20 ring-2 ring-white/30 shadow-md">
                            <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-white leading-tight">Honor Guru</h2>
                            <p class="text-xs font-medium text-amber-100">Generate dan kelola slip honor bulanan guru</p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Kirim Semua -->
                        <button @click="!hasDraftSlips && (showSendAll = true)"
                            :disabled="hasDraftSlips"
                            :title="hasDraftSlips ? 'Bayar semua slip terlebih dahulu sebelum mengirim WA' : 'Kirim semua slip ke WA'"
                            :class="hasDraftSlips
                                ? 'cursor-not-allowed opacity-50 bg-white/10 ring-white/20'
                                : 'hover:bg-green-500 bg-green-500/80 ring-white/30'"
                            class="flex flex-1 sm:flex-none items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-white ring-1 backdrop-blur-sm transition shadow-sm">
                            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            <span class="hidden sm:inline">Kirim Semua</span>
                        </button>
                        <!-- Bayar Semua -->
                        <button @click="showMarkAllPaid = true"
                            class="flex flex-1 sm:flex-none items-center justify-center gap-2 rounded-xl bg-emerald-500/80 px-3 py-2 text-xs font-semibold text-white ring-1 ring-white/30 backdrop-blur-sm transition hover:bg-emerald-500 shadow-sm">
                            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="hidden sm:inline">Bayar Semua</span>
                        </button>
                        <!-- Generate dropdown -->
                        <div class="relative flex-1 sm:flex-none">
                            <button @click.stop="showGenerateMenu = !showGenerateMenu"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/20 px-3 py-2 text-xs font-semibold text-white ring-1 ring-white/30 backdrop-blur-sm transition hover:bg-white/30 shadow-sm">
                                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                                <span>Generate</span>
                                <svg class="size-3.5 shrink-0 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <!-- Dropdown menu -->
                            <div v-if="showGenerateMenu"
                                class="absolute right-0 top-full z-20 mt-1.5 min-w-[170px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                                @click.stop>
                                <button type="button" @click="showGenerateAll = true; showGenerateMenu = false"
                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                                    </svg>
                                    Generate Semua
                                </button>
                                <div class="mx-3 border-t border-slate-100"/>
                                <button type="button" @click="showGenerate = true; showGenerateMenu = false"
                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                    Buat Satu Slip
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <!-- Total Draft -->
                <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-amber-100">
                            <svg class="size-4.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Belum Dibayar</p>
                    </div>
                    <p class="tabular-nums text-xl font-bold text-amber-700">Rp {{ fmt(totalDraft) }}</p>
                    <p class="mt-1 text-xs text-amber-500">menunggu pembayaran</p>
                </div>

                <!-- Total Lunas -->
                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-100">
                            <svg class="size-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Sudah Dibayar</p>
                    </div>
                    <p class="tabular-nums text-xl font-bold text-emerald-700">Rp {{ fmt(totalPaid) }}</p>
                    <p class="mt-1 text-xs text-emerald-500">honor lunas</p>
                </div>

                <!-- Total Nominal -->
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-slate-100">
                            <svg class="size-4.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Nominal</p>
                    </div>
                    <p class="tabular-nums text-xl font-bold text-slate-800">Rp {{ fmt(totalAll) }}</p>
                    <p class="mt-1 text-xs text-slate-400">semua slip honor</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                    </svg>
                    <input v-model="search" type="search" placeholder="Cari nama guru..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-400/20"/>
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
                <FilterSelect
                    v-model="filterMonth"
                    :options="[{ value: '', label: 'Semua Bulan' }, ...monthOptions]"
                />
                <FilterSelect
                    v-model="filterYear"
                    :options="[{ value: '', label: 'Semua Tahun' }, ...yearOptions.map(y => ({ value: y, label: String(y) }))]"
                />
                <button v-if="hasFilter" @click="resetFilters"
                    class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors">Reset</button>
            </div>

            <!-- Empty -->
            <div v-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-amber-50/40 py-16 text-center">
                <div class="mb-3 flex size-14 items-center justify-center rounded-2xl bg-amber-100">
                    <svg class="size-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">{{ hasFilter ? 'Tidak ada hasil' : 'Belum ada slip honor' }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ hasFilter ? 'Coba ubah filter.' : 'Klik "Buat Slip" untuk membuat slip honor pertama.' }}</p>
                <button v-if="hasFilter" @click="resetFilters" class="mt-3 text-xs font-semibold text-amber-600 hover:underline">Reset filter</button>
                <button v-else @click="showGenerate = true"
                    class="mt-4 flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-600 transition-colors">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Buat Slip Honor
                </button>
            </div>

            <!-- List -->
            <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <!-- Mobile cards -->
                <ul class="divide-y divide-slate-100 sm:hidden">
                    <li v-for="h in paginated" :key="h.id" class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-sm font-bold text-white shadow-sm">
                                    {{ h.teacher.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ h.teacher.user.name }}</p>
                                    <p class="text-xs text-slate-400">{{ periodLabel(h) }} · {{ h.academic_year?.name }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusConfig[h.status]?.badge">
                                {{ statusConfig[h.status]?.label }}
                            </span>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-3 space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-white px-2.5 py-2 text-center shadow-sm">
                                    <p class="text-xs text-slate-400">Jam Pelajaran</p>
                                    <p class="text-xs font-bold text-slate-700 mt-0.5">Rp {{ fmt(h.teaching_hours_amount) }}</p>
                                    <p class="text-xs text-slate-400">{{ h.teaching_hours }} jam</p>
                                </div>
                                <div class="rounded-lg bg-white px-2.5 py-2 text-center shadow-sm">
                                    <p class="text-xs text-slate-400">Transport</p>
                                    <p class="text-xs font-bold text-slate-700 mt-0.5">Rp {{ fmt(h.transport_amount) }}</p>
                                    <p class="text-xs text-slate-400">{{ h.transport_days }} hari</p>
                                </div>
                            </div>
                            <div v-if="h.position_allowance > 0" class="flex items-center justify-between rounded-lg bg-amber-50 px-2.5 py-1.5">
                                <span class="text-xs text-amber-600 font-medium">{{ h.position_name }}</span>
                                <span class="text-xs font-bold text-amber-700">Rp {{ fmt(h.position_allowance) }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-200 pt-2">
                                <span class="text-xs font-semibold text-slate-500">Total</span>
                                <span class="text-sm font-bold text-slate-900">Rp {{ fmt(h.total_amount) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="route('keuangan.honorariums.slip', h.id)" target="_blank"
                                class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Cetak PDF
                            </a>
                            <button
                                :disabled="h.status === 'draft'"
                                :title="h.status === 'draft' ? 'Tandai lunas dahulu sebelum mengirim WA' : 'Kirim slip ke WhatsApp'"
                                @click="h.status !== 'draft' && (sendTarget = h)"
                                :class="h.status === 'draft'
                                    ? 'cursor-not-allowed opacity-40 border-slate-200 text-slate-400'
                                    : 'border-green-200 text-green-600 hover:bg-green-50'"
                                class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Kirim WA
                            </button>
                            <button v-if="h.status === 'draft'" @click="paidTarget = h"
                                class="flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600 transition-colors">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Bayar
                            </button>
                            <button v-if="h.status === 'draft'" @click="deleteTarget = h"
                                class="flex items-center gap-1.5 rounded-lg border border-red-100 px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                Hapus
                            </button>
                        </div>
                    </li>
                </ul>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3.5 text-left whitespace-nowrap">Guru</th>
                                <th class="px-5 py-3.5 text-left whitespace-nowrap">Periode</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Jam Pelajaran</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Transport</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Total</th>
                                <th class="px-5 py-3.5 text-center whitespace-nowrap">Status</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="h in paginated" :key="h.id" class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-800">{{ h.teacher.user.name }}</p>
                                    <p class="text-xs text-slate-400">{{ h.academic_year?.name }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-slate-700 font-medium whitespace-nowrap">{{ periodLabel(h) }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <p class="font-medium text-slate-700">Rp {{ fmt(h.teaching_hours_amount) }}</p>
                                    <p class="text-xs text-slate-400">{{ h.teaching_hours }} jam</p>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <p class="font-medium text-slate-700">Rp {{ fmt(h.transport_amount) }}</p>
                                    <p class="text-xs text-slate-400">{{ h.transport_days }} hari</p>
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-slate-900 whitespace-nowrap">Rp {{ fmt(h.total_amount) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusConfig[h.status]?.badge">
                                        {{ statusConfig[h.status]?.label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a :href="route('keuangan.honorariums.slip', h.id)" target="_blank"
                                            class="flex size-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors" title="Cetak PDF">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        </a>
                                        <button
                                            :disabled="h.status === 'draft'"
                                            :title="h.status === 'draft' ? 'Tandai lunas dahulu sebelum mengirim WA' : 'Kirim ke WhatsApp'"
                                            @click="h.status !== 'draft' && (sendTarget = h)"
                                            :class="h.status === 'draft'
                                                ? 'cursor-not-allowed opacity-40 border-slate-200 text-slate-400'
                                                : 'border-green-200 text-green-600 hover:bg-green-50'"
                                            class="flex size-7 items-center justify-center rounded-lg border transition-colors">
                                            <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        </button>
                                        <button v-if="h.status === 'draft'" @click="paidTarget = h"
                                            class="flex items-center gap-1 rounded-lg bg-emerald-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-600 transition-colors">
                                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            Bayar
                                        </button>
                                        <button v-if="h.status === 'draft'" @click="deleteTarget = h"
                                            class="flex size-7 items-center justify-center rounded-lg border border-red-100 text-red-400 hover:bg-red-50 hover:text-red-500 transition-colors" title="Hapus">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                :current-page="currentPage" :total-pages="totalPages"
                :total="filtered.length" :per-page="PER_PAGE" label="slip"
                @update:current-page="currentPage = $event"
            />

        </div>

        <!-- Modal Generate -->
        <Modal :show="showGenerate" @close="showGenerate = false" max-width="md">
            <div class="rounded-2xl">
                <!-- Modal Header -->
                <div class="rounded-t-2xl bg-gradient-to-br from-amber-500 to-orange-400 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-white/20">
                            <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Buat Slip Honor</h3>
                            <p class="text-xs text-amber-100">Honor dihitung otomatis dari jam pelajaran + absensi</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form @submit.prevent="submitGenerate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Guru</label>
                            <FilterSelect
                                v-model="generateForm.teacher_id"
                                :options="[{ value: '', label: '-- Pilih Guru --' }, ...teachersWithHoursOptions]"
                                :has-error="!!generateForm.errors.teacher_id"
                                block
                            />
                            <p v-if="generateForm.errors.teacher_id" class="mt-1 text-xs text-red-500">{{ generateForm.errors.teacher_id }}</p>
                            <p v-if="teachersWithHours.length === 0" class="mt-1.5 flex items-center gap-1.5 text-xs text-amber-600">
                                <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                                Belum ada guru dengan konfigurasi jam pelajaran di tahun ajaran aktif.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Ajaran</label>
                            <FilterSelect
                                v-model="generateForm.academic_year_id"
                                :options="[{ value: '', label: '-- Pilih --' }, ...academicYearOptions]"
                                :has-error="!!generateForm.errors.academic_year_id"
                                block
                            />
                            <p v-if="generateForm.errors.academic_year_id" class="mt-1 text-xs text-red-500">{{ generateForm.errors.academic_year_id }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                                <FilterSelect
                                    v-model="generateForm.period_month"
                                    :options="periodMonthOptions"
                                    :has-error="!!generateForm.errors.period_month"
                                    block
                                />
                                <p v-if="generateForm.errors.period_month" class="mt-1 text-xs text-red-500">{{ generateForm.errors.period_month }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                                <FilterSelect
                                    v-model="generateForm.period_year"
                                    :options="periodYearOptions"
                                    block
                                />
                            </div>
                        </div>
                        <div class="rounded-xl bg-blue-50 px-4 py-3 text-xs text-blue-700 flex items-start gap-2">
                            <svg class="mt-0.5 size-3.5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            </svg>
                            Pastikan absensi bulan ini sudah lengkap sebelum membuat slip. Jumlah hari hadir akan otomatis dihitung.
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" @click="showGenerate = false"
                                class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                            <button type="submit" :disabled="generateForm.processing"
                                class="rounded-xl bg-amber-500 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-50 transition-colors shadow-sm">
                                {{ generateForm.processing ? 'Memproses...' : 'Buat Slip' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Modal Kirim Semua WA -->
        <Modal :show="showSendAll" @close="showSendAll = false" max-width="sm">
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-green-100">
                        <svg class="size-5 text-green-600" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Kirim Semua Slip via WA</h3>
                        <p class="text-xs text-slate-400">PDF slip dikirim ke semua guru sekaligus</p>
                    </div>
                </div>
                <form @submit.prevent="submitSendAll" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                            <FilterSelect v-model="sendAllForm.period_month" :options="periodMonthOptions" block />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                            <FilterSelect v-model="sendAllForm.period_year" :options="periodYearOptions" block />
                        </div>
                    </div>
                    <div class="rounded-xl bg-green-50 px-4 py-3 text-xs text-green-700 flex items-start gap-2">
                        <svg class="mt-0.5 size-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                        Semua slip periode ini akan dikirim ke WA guru masing-masing. Guru yang tidak punya nomor HP akan dilewati.
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showSendAll = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                        <button type="submit" :disabled="sendAllForm.processing"
                            class="rounded-xl bg-green-500 px-5 py-2 text-sm font-semibold text-white hover:bg-green-600 disabled:opacity-50 transition-colors shadow-sm">
                            {{ sendAllForm.processing ? 'Mengirim...' : 'Kirim Semua' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Kirim WA -->
        <Modal :show="!!sendTarget" @close="sendTarget = null" max-width="sm">
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-green-100">
                        <svg class="size-5 text-green-600" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Kirim Slip ke WhatsApp</h3>
                        <p class="text-xs text-slate-400">PDF slip akan dikirim via Fonnte</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-2">
                    Kirim slip honor <strong class="text-slate-800">{{ sendTarget?.teacher.user.name }}</strong> periode <strong class="text-slate-800">{{ sendTarget ? periodLabel(sendTarget) : '' }}</strong> ke WhatsApp?
                </p>
                <div class="mb-5 rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-500">
                    Nomor: <strong class="text-slate-700">{{ sendTarget?.teacher.phone || 'Belum ada nomor HP' }}</strong>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="sendTarget = null"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                    <button @click="submitSend" :disabled="sendForm.processing || !sendTarget?.teacher.phone"
                        class="rounded-xl bg-green-500 px-5 py-2 text-sm font-semibold text-white hover:bg-green-600 disabled:opacity-50 transition-colors">
                        {{ sendForm.processing ? 'Mengirim...' : 'Kirim WA' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Modal Konfirmasi Bayar -->
        <Modal :show="!!paidTarget" @close="paidTarget = null" max-width="sm">
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Konfirmasi Pembayaran</h3>
                        <p class="text-xs text-slate-400">Tandai honor sebagai lunas</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-3">
                    Honor <strong class="text-slate-800">{{ paidTarget?.teacher.user.name }}</strong> periode <strong class="text-slate-800">{{ paidTarget ? periodLabel(paidTarget) : '' }}</strong> akan ditandai lunas.
                </p>
                <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 font-medium">
                    Total: <strong>Rp {{ paidTarget ? fmt(paidTarget.total_amount) : '' }}</strong>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="paidTarget = null"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                    <button @click="submitMarkPaid" :disabled="paidForm.processing"
                        class="rounded-xl bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50 transition-colors">
                        Ya, Tandai Lunas
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Modal Bayar Semua -->
        <Modal :show="showMarkAllPaid" @close="showMarkAllPaid = false" max-width="sm">
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Bayar Semua Honor</h3>
                        <p class="text-xs text-slate-400">Tandai semua slip periode ini sebagai lunas</p>
                    </div>
                </div>
                <form @submit.prevent="submitMarkAllPaid" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                            <FilterSelect v-model="markAllPaidForm.period_month" :options="periodMonthOptions" block />
                            <p v-if="markAllPaidForm.errors.period_month" class="mt-1 text-xs text-red-500">{{ markAllPaidForm.errors.period_month }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                            <FilterSelect v-model="markAllPaidForm.period_year" :options="periodYearOptions" block />
                        </div>
                    </div>
                    <div class="rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-700 flex items-start gap-2">
                        <svg class="mt-0.5 size-3.5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        Semua slip berstatus <strong>belum dibayar</strong> di periode ini akan ditandai lunas sekaligus. Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showMarkAllPaid = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                        <button type="submit" :disabled="markAllPaidForm.processing"
                            class="rounded-xl bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50 transition-colors shadow-sm">
                            {{ markAllPaidForm.processing ? 'Memproses...' : 'Bayar Semua' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Generate Semua -->
        <Modal :show="showGenerateAll" @close="showGenerateAll = false" max-width="md">
            <div class="rounded-2xl">
                <div class="rounded-t-2xl bg-gradient-to-br from-amber-500 to-orange-400 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-white/20">
                            <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Generate Semua Slip Honor</h3>
                            <p class="text-xs text-amber-100">Buat slip sekaligus untuk semua guru yang punya konfigurasi jam</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form @submit.prevent="submitGenerateAll" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Ajaran</label>
                            <FilterSelect
                                v-model="generateAllForm.academic_year_id"
                                :options="[{ value: '', label: '-- Pilih --' }, ...academicYearOptions]"
                                :has-error="!!generateAllForm.errors.academic_year_id"
                                block
                            />
                            <p v-if="generateAllForm.errors.academic_year_id" class="mt-1 text-xs text-red-500">{{ generateAllForm.errors.academic_year_id }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                                <FilterSelect
                                    v-model="generateAllForm.period_month"
                                    :options="periodMonthOptions"
                                    :has-error="!!generateAllForm.errors.period_month"
                                    block
                                />
                                <p v-if="generateAllForm.errors.period_month" class="mt-1 text-xs text-red-500">{{ generateAllForm.errors.period_month }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                                <FilterSelect
                                    v-model="generateAllForm.period_year"
                                    :options="periodYearOptions"
                                    block
                                />
                            </div>
                        </div>
                        <div class="rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-700 flex items-start gap-2">
                            <svg class="mt-0.5 size-3.5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            </svg>
                            Hanya guru yang punya konfigurasi jam pelajaran yang akan dibuatkan slip. Guru yang sudah punya slip periode ini akan dilewati secara otomatis.
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" @click="showGenerateAll = false"
                                class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                            <button type="submit" :disabled="generateAllForm.processing"
                                class="rounded-xl bg-amber-500 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-50 transition-colors shadow-sm">
                                {{ generateAllForm.processing ? 'Memproses...' : 'Generate Semua' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Modal Hapus -->
        <Modal :show="!!deleteTarget" @close="deleteTarget = null" max-width="sm">
            <div class="p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-red-100">
                        <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Hapus Slip Honor?</h3>
                        <p class="text-xs text-slate-400">Tindakan ini tidak bisa dibatalkan</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-5">
                    Slip honor <strong class="text-slate-800">{{ deleteTarget?.teacher.user.name }}</strong> periode <strong class="text-slate-800">{{ deleteTarget ? periodLabel(deleteTarget) : '' }}</strong> akan dihapus permanen.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="deleteTarget = null"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                    <button @click="submitDelete" :disabled="deleteForm.processing"
                        class="rounded-xl bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-50 transition-colors">Ya, Hapus</button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
