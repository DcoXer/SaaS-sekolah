<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    academicYears:    { type: Array,  required: true },
    selectedYearId:   { type: Number, default: null },
    teachingHourData: { type: Array,  required: true },
});

// ── Filter Tahun Ajaran ───────────────────────────────────────────────────────
const selectedYear = ref(props.selectedYearId);
watch(selectedYear, (val) => {
    router.get(route('operator.teaching-hours.index'), { academic_year_id: val }, { preserveState: true });
});

// ── Search & Filter ───────────────────────────────────────────────────────────
const search     = ref('');
const filterType = ref('');

const filtered = computed(() => {
    let list = props.teachingHourData;
    if (filterType.value) list = list.filter(d => d.teacher.type === filterType.value);
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(d =>
            d.teacher.user.name.toLowerCase().includes(q) ||
            (d.teacher.nip && d.teacher.nip.toLowerCase().includes(q))
        );
    }
    return list;
});

const hasFilter = computed(() => search.value.trim() || filterType.value);
const resetFilters = () => { search.value = ''; filterType.value = ''; };

// ── Pagination ────────────────────────────────────────────────────────────────
const PER_PAGE    = 15;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch([search, filterType, selectedYear], () => { currentPage.value = 1; });

// ── Set / Edit Modal ──────────────────────────────────────────────────────────
const showModal   = ref(false);
const editingItem = ref(null);

const form = useForm({
    teacher_id:           '',
    academic_year_id:     props.selectedYearId ?? '',
    total_hours:          '',
    hourly_rate:          '',
    daily_transport_rate: '',
    position_name:        '',
    position_allowance:   '',
});

const openSet = (item) => {
    editingItem.value = item;
    form.teacher_id           = item.teacher.id;
    form.academic_year_id     = item.academic_year_id;
    form.total_hours          = item.teaching_hour?.total_hours ?? '';
    form.hourly_rate          = item.teaching_hour?.hourly_rate ?? '';
    form.daily_transport_rate = item.teaching_hour?.daily_transport_rate ?? '';
    form.position_name        = item.teaching_hour?.position_name ?? '';
    form.position_allowance   = item.teaching_hour?.position_allowance ?? '';
    form.clearErrors();
    showModal.value = true;
};

const submitSet = () => {
    form.post(route('operator.teaching-hours.store'), {
        onSuccess: () => { showModal.value = false; },
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.teaching-hours.destroy', deleteTarget.value.teaching_hour.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('id-ID').format(n ?? 0);

const typeConfig = {
    guru_kelas:  { label: 'Guru Kelas',  class: 'bg-emerald-50 text-emerald-700' },
    guru_bidang: { label: 'Guru Bidang', class: 'bg-violet-50 text-violet-700'   },
};
</script>

<template>
    <AppLayout>
        <Head title="Jam Pelajaran Guru" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span><span>/</span>
                <span class="font-semibold text-slate-700">Jam Pelajaran</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Heading -->
            <div>
                <h2 class="text-lg font-bold text-slate-900">Jam Pelajaran Guru</h2>
                <p class="text-sm text-slate-500">Kelola jam pelajaran dan tarif honor per guru per tahun ajaran.</p>
            </div>

            <!-- Tahun Ajaran Selector -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500 shrink-0">Tahun Ajaran</label>
                <FilterSelect
                    v-model="selectedYear"
                    :options="[{ value: null, label: '-- Pilih --' }, ...academicYears.map(y => ({ value: y.id, label: y.name }))]"
                />
            </div>

            <template v-if="selectedYear">

                <!-- Search & Filter -->
                <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                    <div class="relative flex-1 min-w-[180px]">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                        </svg>
                        <input v-model="search" type="search" placeholder="Cari nama guru / NIP..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"/>
                    </div>
                    <div class="h-5 w-px bg-slate-200"/>
                    <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
                        <button v-for="opt in [{ value: '', label: 'Semua' }, { value: 'guru_kelas', label: 'Guru Kelas' }, { value: 'guru_bidang', label: 'Guru Bidang' }]"
                            :key="opt.value" @click="filterType = opt.value"
                            :class="filterType === opt.value
                                ? 'bg-white text-slate-800 shadow-sm'
                                : 'text-slate-500 hover:text-slate-700'"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150">
                            {{ opt.label }}
                        </button>
                    </div>
                    <button v-if="hasFilter" @click="resetFilters"
                        class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors">Reset</button>
                </div>

                <!-- Empty state -->
                <div v-if="filtered.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                    <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">Tidak ada data guru</p>
                    <button v-if="hasFilter" @click="resetFilters" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
                </div>

                <!-- List -->
                <div v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <!-- Desktop header -->
                    <div class="hidden grid-cols-12 gap-4 border-b border-slate-100 bg-slate-50 px-5 py-2.5 sm:grid">
                        <div class="col-span-4 text-xs font-semibold uppercase tracking-wide text-slate-400">Guru</div>
                        <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Jam/Bln</div>
                        <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Tarif/Jam</div>
                        <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Transport/Hari</div>
                        <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Aksi</div>
                    </div>

                    <ul class="divide-y divide-slate-100">
                        <li v-for="item in paginated" :key="item.teacher.id"
                            class="px-4 py-4 transition-colors hover:bg-slate-50 sm:px-5 sm:py-3.5">

                            <!-- Mobile layout -->
                            <div class="sm:hidden">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-700">
                                            {{ item.teacher.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ item.teacher.user.name }}</p>
                                            <span class="mt-0.5 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                                                :class="typeConfig[item.teacher.type]?.class">
                                                {{ typeConfig[item.teacher.type]?.label }}
                                            </span>
                                        </div>
                                    </div>
                                    <button @click="openSet(item)"
                                        class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                                        :class="item.teaching_hour ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-500 text-white hover:bg-emerald-600'">
                                        {{ item.teaching_hour ? 'Edit' : 'Set' }}
                                    </button>
                                </div>
                                <div v-if="item.teaching_hour" class="mt-3 space-y-2">
                                    <div class="grid grid-cols-3 gap-2 rounded-lg bg-slate-50 px-3 py-2.5">
                                        <div class="text-center">
                                            <p class="text-xs text-slate-400">Jam/Bln</p>
                                            <p class="text-sm font-bold text-slate-700">{{ item.teaching_hour.total_hours }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs text-slate-400">Tarif/Jam</p>
                                            <p class="text-sm font-bold text-slate-700">{{ fmt(item.teaching_hour.hourly_rate) }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs text-slate-400">Transport</p>
                                            <p class="text-sm font-bold text-slate-700">{{ fmt(item.teaching_hour.daily_transport_rate) }}</p>
                                        </div>
                                    </div>
                                    <div v-if="item.teaching_hour.position_name"
                                        class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2">
                                        <span class="text-xs font-medium text-amber-700">{{ item.teaching_hour.position_name }}</span>
                                        <span class="text-xs font-bold text-amber-800">Rp {{ fmt(item.teaching_hour.position_allowance) }}</span>
                                    </div>
                                </div>
                                <div v-else class="mt-2">
                                    <p class="text-xs text-slate-400 italic">Belum dikonfigurasi</p>
                                </div>
                            </div>

                            <!-- Desktop layout -->
                            <div class="hidden grid-cols-12 items-center gap-4 sm:grid">
                                <div class="col-span-4 flex items-center gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">
                                        {{ item.teacher.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ item.teacher.user.name }}</p>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="typeConfig[item.teacher.type]?.class">
                                            {{ typeConfig[item.teacher.type]?.label }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-span-2 text-right">
                                    <span v-if="item.teaching_hour" class="text-sm font-semibold text-slate-800">{{ item.teaching_hour.total_hours }} jam</span>
                                    <span v-else class="text-sm text-slate-300">—</span>
                                </div>
                                <div class="col-span-2 text-right text-sm text-slate-600">
                                    <span v-if="item.teaching_hour">Rp {{ fmt(item.teaching_hour.hourly_rate) }}</span>
                                    <span v-else class="text-slate-300">—</span>
                                </div>
                                <div class="col-span-2 text-right text-sm text-slate-600">
                                    <span v-if="item.teaching_hour">Rp {{ fmt(item.teaching_hour.daily_transport_rate) }}</span>
                                    <span v-else class="text-slate-300">—</span>
                                </div>
                                <div class="col-span-2 flex items-center justify-end gap-2">
                                    <button @click="openSet(item)"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                                        :class="item.teaching_hour ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-500 text-white hover:bg-emerald-600'">
                                        {{ item.teaching_hour ? 'Edit' : 'Set' }}
                                    </button>
                                    <button v-if="item.teaching_hour" @click="deleteTarget = item"
                                        class="rounded-lg px-2 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                        </li>
                    </ul>
                </div>

                <Pagination
                    :current-page="currentPage" :total-pages="totalPages"
                    :total="filtered.length" :per-page="PER_PAGE" label="guru"
                    @update:current-page="currentPage = $event"
                />

            </template>

            <div v-else class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Pilih tahun ajaran terlebih dahulu</p>
            </div>

        </div>

        <!-- Modal Set/Edit -->
        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-0.5">
                    {{ editingItem?.teaching_hour ? 'Edit' : 'Set' }} Jam Pelajaran
                </h3>
                <p class="text-sm text-slate-500 mb-5">{{ editingItem?.teacher.user.name }}</p>

                <form @submit.prevent="submitSet" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Jam Pelajaran per Bulan</label>
                        <input v-model="form.total_hours" type="number" min="1" placeholder="Contoh: 24"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                            :class="{ 'border-red-400': form.errors.total_hours }"/>
                        <p v-if="form.errors.total_hours" class="mt-1 text-xs text-red-500">{{ form.errors.total_hours }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tarif per Jam Pelajaran (Rp)</label>
                        <input v-model="form.hourly_rate" type="number" min="0" placeholder="Contoh: 50000"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                            :class="{ 'border-red-400': form.errors.hourly_rate }"/>
                        <p v-if="form.errors.hourly_rate" class="mt-1 text-xs text-red-500">{{ form.errors.hourly_rate }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Uang Transport per Hari Hadir (Rp)</label>
                        <input v-model="form.daily_transport_rate" type="number" min="0" placeholder="Contoh: 15000"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                            :class="{ 'border-red-400': form.errors.daily_transport_rate }"/>
                        <p v-if="form.errors.daily_transport_rate" class="mt-1 text-xs text-red-500">{{ form.errors.daily_transport_rate }}</p>
                    </div>

                    <!-- Uang Jabatan (opsional) -->
                    <div class="rounded-lg border border-dashed border-slate-200 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Uang Jabatan <span class="normal-case font-normal text-slate-400">(opsional)</span>
                        </p>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Jabatan</label>
                            <input v-model="form.position_name" type="text" placeholder="Contoh: Wali Kelas, Wakamad, Kepala Madrasah"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Besaran Uang Jabatan (Rp)</label>
                            <input v-model="form.position_allowance" type="number" min="0" placeholder="Contoh: 200000"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"/>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div v-if="form.total_hours && form.hourly_rate"
                        class="rounded-lg bg-emerald-50 px-4 py-3 text-sm space-y-1.5">
                        <div class="flex justify-between text-emerald-800">
                            <span>Jam pelajaran</span>
                            <span class="font-semibold">Rp {{ fmt(form.total_hours * form.hourly_rate) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-emerald-600">
                            <span>Transport (asumsi 25 hari)</span>
                            <span>Rp {{ fmt(25 * (form.daily_transport_rate || 0)) }}</span>
                        </div>
                        <div v-if="form.position_allowance" class="flex justify-between text-xs text-emerald-600">
                            <span>Uang jabatan ({{ form.position_name || '—' }})</span>
                            <span>Rp {{ fmt(form.position_allowance) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-emerald-200 pt-1.5 text-emerald-900 font-semibold">
                            <span>Estimasi total/bulan</span>
                            <span>Rp {{ fmt(
                                (form.total_hours * form.hourly_rate) +
                                (25 * (form.daily_transport_rate || 0)) +
                                (Number(form.position_allowance) || 0)
                            ) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showModal = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Hapus -->
        <Modal :show="!!deleteTarget" @close="deleteTarget = null" max-width="sm">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-2">Hapus Jam Pelajaran?</h3>
                <p class="text-sm text-slate-600 mb-5">
                    Konfigurasi jam pelajaran <strong>{{ deleteTarget?.teacher.user.name }}</strong> akan dihapus permanen.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="deleteTarget = null"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button @click="submitDelete" :disabled="deleteForm.processing"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-50">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
