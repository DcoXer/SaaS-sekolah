<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const showExportMenu = ref(false);

const props = defineProps({
    recap: { type: Array, default: () => [] },
    month: { type: Number, required: true },
    year:  { type: Number, required: true },
});

const selectedMonth = ref(props.month);
const selectedYear  = ref(props.year);

const monthNames = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const currentMonthName = computed(() => `${monthNames[selectedMonth.value]} ${selectedYear.value}`);

const years = computed(() => {
    const current = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => current - i);
});

const monthOptions = computed(() => monthNames.slice(1).map((name, idx) => ({ value: idx + 1, label: name })));
const yearOptions  = computed(() => years.value.map(y => ({ value: y, label: String(y) })));

function applyFilter() {
    router.get(route('operator.teacher-attendances.recap'), {
        month: selectedMonth.value,
        year:  selectedYear.value,
    }, { preserveState: true, replace: true });
}

function doExport(format) {
    showExportMenu.value = false;
    const url = new URL(route('operator.teacher-attendances.recap.export'), window.location.origin);
    url.searchParams.set('month', selectedMonth.value);
    url.searchParams.set('year', selectedYear.value);
    url.searchParams.set('format', format);
    window.location.href = url.toString();
}

const totals = computed(() => ({
    hadir: props.recap.reduce((s, r) => s + r.hadir, 0),
    izin:  props.recap.reduce((s, r) => s + r.izin,  0),
    sakit: props.recap.reduce((s, r) => s + r.sakit, 0),
    alpha: props.recap.reduce((s, r) => s + r.alpha, 0),
    total: props.recap.reduce((s, r) => s + r.total, 0),
}));

const typeLabel = { guru_kelas: 'Guru Kelas', guru_bidang: 'Guru Bidang' };
</script>

<template>
    <Head title="Rekap Absensi Guru" />

    <AppLayout>
        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Rekap Absensi Guru</span>
            </div>
        </template>

        <div class="mx-auto max-w-6xl space-y-6 p-6">

            <!-- Heading + Filter + Export -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Rekap Absensi Guru</h2>
                    <p class="text-sm text-slate-500">Ringkasan kehadiran semua guru — {{ currentMonthName }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <FilterSelect v-model="selectedMonth" :options="monthOptions" @change="applyFilter" />
                    <FilterSelect v-model="selectedYear"  :options="yearOptions"  @change="applyFilter" />

                    <!-- Export dropdown -->
                    <div class="relative">
                        <button
                            @click="showExportMenu = !showExportMenu"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export
                        </button>
                        <div
                            v-if="showExportMenu"
                            class="absolute right-0 z-20 mt-1.5 w-36 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-lg"
                            @mouseleave="showExportMenu = false"
                        >
                            <button @click="doExport('xlsx')" class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                <svg class="size-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                                Excel (.xlsx)
                            </button>
                            <button @click="doExport('csv')" class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                <svg class="size-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                CSV (.csv)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Total Hadir</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700">{{ totals.hadir }}</p>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">Total Izin</p>
                    <p class="mt-1 text-3xl font-extrabold text-sky-700">{{ totals.izin }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Total Sakit</p>
                    <p class="mt-1 text-3xl font-extrabold text-amber-700">{{ totals.sakit }}</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Total Alpha</p>
                    <p class="mt-1 text-3xl font-extrabold text-red-700">{{ totals.alpha }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3.5 text-left">Nama Guru</th>
                                <th class="px-5 py-3.5 text-left">Tipe</th>
                                <th class="px-5 py-3.5 text-center">Hadir</th>
                                <th class="px-5 py-3.5 text-center">Izin</th>
                                <th class="px-5 py-3.5 text-center">Sakit</th>
                                <th class="px-5 py-3.5 text-center">Alpha</th>
                                <th class="px-5 py-3.5 text-center">Total</th>
                                <th class="px-5 py-3.5 text-center">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="!recap.length">
                                <td colspan="8" class="py-16 text-center text-slate-400">Belum ada data absensi untuk periode ini.</td>
                            </tr>
                            <tr v-for="row in recap" :key="row.id" class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-800">{{ row.name }}</p>
                                    <p v-if="row.nip" class="text-xs text-slate-400">NIP {{ row.nip }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                        :class="row.type === 'guru_kelas'
                                            ? 'bg-violet-100 text-violet-700'
                                            : 'bg-sky-100 text-sky-700'">
                                        {{ typeLabel[row.type] ?? row.type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-emerald-50 text-sm font-bold text-emerald-700">
                                        {{ row.hadir }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-sky-50 text-sm font-bold text-sky-700">
                                        {{ row.izin }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-amber-50 text-sm font-bold text-amber-700">
                                        {{ row.sakit }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex size-8 items-center justify-center rounded-xl text-sm font-bold"
                                        :class="row.alpha > 0 ? 'bg-red-50 text-red-700' : 'bg-slate-50 text-slate-400'">
                                        {{ row.alpha }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center font-semibold text-slate-600">{{ row.total }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <template v-if="row.total > 0">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-emerald-500 transition-all"
                                                    :style="{ width: ((row.hadir / row.total) * 100).toFixed(0) + '%' }" />
                                            </div>
                                            <span class="w-10 text-right text-xs font-semibold text-slate-600">
                                                {{ ((row.hadir / row.total) * 100).toFixed(0) }}%
                                            </span>
                                        </div>
                                    </template>
                                    <span v-else class="text-xs text-slate-300">—</span>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="recap.length" class="border-t-2 border-slate-200 bg-slate-50">
                            <tr class="text-sm font-bold text-slate-700">
                                <td class="px-5 py-3.5" colspan="2">Total ({{ recap.length }} guru)</td>
                                <td class="px-5 py-3.5 text-center text-emerald-700">{{ totals.hadir }}</td>
                                <td class="px-5 py-3.5 text-center text-sky-700">{{ totals.izin }}</td>
                                <td class="px-5 py-3.5 text-center text-amber-700">{{ totals.sakit }}</td>
                                <td class="px-5 py-3.5 text-center text-red-700">{{ totals.alpha }}</td>
                                <td class="px-5 py-3.5 text-center">{{ totals.total }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span v-if="totals.total > 0" class="text-sm font-bold text-emerald-700">
                                        {{ ((totals.hadir / totals.total) * 100).toFixed(0) }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
