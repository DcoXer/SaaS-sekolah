<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    router.get(route('kamad.teacher-attendances.recap'), {
        month: selectedMonth.value,
        year:  selectedYear.value,
    }, { preserveState: true, replace: true });
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
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Rekap Absensi Guru</h1>
                    <p class="mt-0.5 text-sm text-slate-500">Ringkasan kehadiran semua guru — {{ currentMonthName }}</p>
                </div>

                <!-- Filter -->
                <div class="flex items-center gap-2">
                    <FilterSelect v-model="selectedMonth" :options="monthOptions" @change="applyFilter" />
                    <FilterSelect v-model="selectedYear"  :options="yearOptions"  @change="applyFilter" />
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">

            <!-- Summary cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-primary-100 bg-primary-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary-600">Total Hadir</p>
                    <p class="mt-1 text-3xl font-extrabold text-primary-700">{{ totals.hadir }}</p>
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
                                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-primary-50 text-sm font-bold text-primary-700">
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
                                                <div class="h-full rounded-full bg-primary-500 transition-all"
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
                                <td class="px-5 py-3.5 text-center text-primary-700">{{ totals.hadir }}</td>
                                <td class="px-5 py-3.5 text-center text-sky-700">{{ totals.izin }}</td>
                                <td class="px-5 py-3.5 text-center text-amber-700">{{ totals.sakit }}</td>
                                <td class="px-5 py-3.5 text-center text-red-700">{{ totals.alpha }}</td>
                                <td class="px-5 py-3.5 text-center">{{ totals.total }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span v-if="totals.total > 0" class="text-sm font-bold text-primary-700">
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
