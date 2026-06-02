<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    calendar:     { type: Array,  required: true },
    summary:      { type: Object, required: true },
    currentMonth: { type: Number, required: true },
    currentYear:  { type: Number, required: true },
});

// ── Navigasi Bulan ─────────────────────────────────────────────────────────────
const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const prevMonth = () => {
    let m = props.currentMonth - 1, y = props.currentYear;
    if (m < 1) { m = 12; y--; }
    router.get(route('guru.attendance.index'), { month: m, year: y });
};
const nextMonth = () => {
    let m = props.currentMonth + 1, y = props.currentYear;
    if (m > 12) { m = 1; y++; }
    router.get(route('guru.attendance.index'), { month: m, year: y });
};

const now = new Date();
const isCurrentMonth = computed(() =>
    props.currentMonth === now.getMonth() + 1 && props.currentYear === now.getFullYear()
);
const todayStr = now.toISOString().slice(0, 10);

// ── Filter ────────────────────────────────────────────────────────────────────
const filterStatus = ref('');

const filtered = computed(() => {
    if (!filterStatus.value) return props.calendar;
    if (filterStatus.value === 'kosong') return props.calendar.filter(e => !e.attendance && !e.is_weekend);
    return props.calendar.filter(e => e.attendance?.status === filterStatus.value);
});

// ── Pagination ────────────────────────────────────────────────────────────────
const PER_PAGE    = 15;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch(filterStatus, () => { currentPage.value = 1; });

// ── Status Config ─────────────────────────────────────────────────────────────
const statusConfig = {
    hadir: { label: 'Hadir', badge: 'bg-emerald-50 text-emerald-700', dot: 'bg-emerald-500' },
    izin:  { label: 'Izin',  badge: 'bg-blue-50 text-blue-700',       dot: 'bg-blue-500'    },
    sakit: { label: 'Sakit', badge: 'bg-amber-50 text-amber-700',     dot: 'bg-amber-500'   },
    alpha: { label: 'Alpha', badge: 'bg-red-50 text-red-700',         dot: 'bg-red-500'     },
};

// ── Add Modal ─────────────────────────────────────────────────────────────────
const showAdd = ref(false);
const addForm = useForm({ date: '', status: 'hadir', notes: '' });

const openAdd = (date) => {
    addForm.date = date; addForm.status = 'hadir'; addForm.notes = '';
    addForm.clearErrors();
    showAdd.value = true;
};
const submitAdd = () => {
    addForm.post(route('guru.attendance.store'), { onSuccess: () => { showAdd.value = false; } });
};

// ── Edit Modal ────────────────────────────────────────────────────────────────
const showEdit   = ref(false);
const editTarget = ref(null);
const editForm   = useForm({ status: '', notes: '' });

const openEdit = (entry) => {
    editTarget.value = entry.attendance;
    editForm.status  = entry.attendance.status;
    editForm.notes   = entry.attendance.notes ?? '';
    editForm.clearErrors();
    showEdit.value = true;
};
const submitEdit = () => {
    editForm.patch(route('guru.attendance.update', editTarget.value.id), {
        onSuccess: () => { showEdit.value = false; },
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});
const submitDelete = () => {
    if (!deleteTarget.value?.attendance?.id) return;
    deleteForm.delete(route('guru.attendance.destroy', deleteTarget.value.attendance.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Absensi Saya" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Guru</span><span>/</span>
                <span class="font-semibold text-slate-700">Absensi</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Heading + Nav -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Absensi Saya</h2>
                    <p class="text-sm text-slate-500">Catat kehadiran harian — kehadiran dihitung sebagai uang transport.</p>
                </div>
                <div class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <button @click="prevMonth" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <span class="min-w-[110px] text-center text-sm font-semibold text-slate-700">
                        {{ monthNames[currentMonth] }} {{ currentYear }}
                    </span>
                    <button @click="nextMonth" :disabled="isCurrentMonth"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors disabled:opacity-30">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-2 sm:gap-3">
                <div v-for="(cfg, key) in statusConfig" :key="key"
                    class="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white py-3 shadow-sm">
                    <span class="text-xl font-bold text-slate-800 tabular-nums">{{ summary[key] }}</span>
                    <span class="mt-0.5 text-xs font-medium" :class="cfg.badge.split(' ')[1]">{{ cfg.label }}</span>
                </div>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap gap-2">
                <button v-for="opt in [
                    { value: '', label: 'Semua' },
                    { value: 'hadir', label: 'Hadir' },
                    { value: 'izin', label: 'Izin' },
                    { value: 'sakit', label: 'Sakit' },
                    { value: 'alpha', label: 'Alpha' },
                    { value: 'kosong', label: 'Belum Input' },
                ]" :key="opt.value" @click="filterStatus = opt.value"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                    :class="filterStatus === opt.value ? 'bg-emerald-500 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                    {{ opt.label }}
                </button>
            </div>

            <!-- Empty -->
            <div v-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <p class="text-sm font-semibold text-slate-700">Tidak ada data untuk filter ini</p>
            </div>

            <!-- List -->
            <div v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <!-- Desktop header -->
                <div class="hidden grid-cols-12 gap-4 border-b border-slate-100 bg-slate-50 px-5 py-2.5 sm:grid">
                    <div class="col-span-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal</div>
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Hari</div>
                    <div class="col-span-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Status</div>
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Keterangan</div>
                    <div class="col-span-2 text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Aksi</div>
                </div>

                <ul class="divide-y divide-slate-100">
                    <li v-for="entry in paginated" :key="entry.date"
                        class="px-4 py-3.5 transition-colors sm:px-5"
                        :class="{
                            'bg-slate-50/60': entry.is_weekend,
                            'bg-emerald-50/40': entry.date === todayStr,
                        }">

                        <!-- Mobile -->
                        <div class="sm:hidden">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl font-mono font-bold text-sm"
                                        :class="entry.date === todayStr ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600'">
                                        {{ entry.day }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">
                                            {{ entry.day_name }}
                                            <span v-if="entry.date === todayStr" class="ml-1 text-xs text-emerald-600 font-semibold">Hari ini</span>
                                        </p>
                                        <span v-if="entry.attendance"
                                            class="mt-0.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="statusConfig[entry.attendance.status]?.badge">
                                            <span class="size-1.5 rounded-full" :class="statusConfig[entry.attendance.status]?.dot"></span>
                                            {{ statusConfig[entry.attendance.status]?.label }}
                                        </span>
                                        <span v-else-if="entry.is_weekend" class="text-xs text-slate-400">Libur</span>
                                        <span v-else class="text-xs text-slate-400 italic">Belum diisi</span>
                                    </div>
                                </div>
                                <div v-if="!entry.is_weekend" class="flex shrink-0 gap-1.5">
                                    <button v-if="!entry.attendance && entry.date <= todayStr"
                                        @click="openAdd(entry.date)"
                                        class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600">
                                        Input
                                    </button>
                                    <template v-if="entry.attendance">
                                        <button @click="openEdit(entry)"
                                            class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                        <button @click="deleteTarget = entry"
                                            class="rounded-lg border border-red-100 px-2.5 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50">Hapus</button>
                                    </template>
                                </div>
                            </div>
                            <p v-if="entry.attendance?.notes" class="mt-2 text-xs text-slate-500 pl-12">{{ entry.attendance.notes }}</p>
                        </div>

                        <!-- Desktop -->
                        <div class="hidden grid-cols-12 items-center gap-4 sm:grid">
                            <div class="col-span-3 flex items-center gap-2">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-lg font-mono text-xs font-bold"
                                    :class="entry.date === todayStr ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600'">
                                    {{ entry.day }}
                                </div>
                                <span class="text-xs text-slate-400">{{ entry.date }}</span>
                            </div>
                            <div class="col-span-2 text-sm text-slate-600">
                                {{ entry.day_name }}
                                <span v-if="entry.date === todayStr" class="ml-1 text-xs text-emerald-600 font-semibold">· Hari ini</span>
                            </div>
                            <div class="col-span-3">
                                <span v-if="entry.attendance"
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="statusConfig[entry.attendance.status]?.badge">
                                    <span class="size-1.5 rounded-full" :class="statusConfig[entry.attendance.status]?.dot"></span>
                                    {{ statusConfig[entry.attendance.status]?.label }}
                                </span>
                                <span v-else-if="entry.is_weekend" class="text-xs text-slate-400">Libur</span>
                                <span v-else class="text-xs text-slate-400 italic">—</span>
                            </div>
                            <div class="col-span-2 text-xs text-slate-500 truncate">{{ entry.attendance?.notes ?? '' }}</div>
                            <div class="col-span-2 flex items-center justify-end gap-1.5">
                                <button v-if="!entry.is_weekend && !entry.attendance && entry.date <= todayStr"
                                    @click="openAdd(entry.date)"
                                    class="rounded-lg bg-emerald-500 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-600">
                                    Input
                                </button>
                                <template v-if="entry.attendance">
                                    <button @click="openEdit(entry)"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                    <button @click="deleteTarget = entry"
                                        class="rounded-lg border border-red-100 px-2.5 py-1 text-xs font-semibold text-red-500 hover:bg-red-50">Hapus</button>
                                </template>
                            </div>
                        </div>

                    </li>
                </ul>
            </div>

            <Pagination
                :current-page="currentPage" :total-pages="totalPages"
                :total="filtered.length" :per-page="PER_PAGE" label="hari"
                @update:current-page="currentPage = $event"
            />

        </div>

        <!-- Modal Tambah -->
        <Modal :show="showAdd" @close="showAdd = false" max-width="sm">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-1">Input Absensi</h3>
                <p class="text-sm text-slate-500 mb-5">{{ addForm.date }}</p>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status Kehadiran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="(cfg, val) in statusConfig" :key="val"
                                class="flex cursor-pointer items-center gap-2.5 rounded-lg border p-2.5 transition-colors"
                                :class="addForm.status === val ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" v-model="addForm.status" :value="val" class="sr-only"/>
                                <span class="size-2.5 rounded-full" :class="cfg.dot"></span>
                                <span class="text-sm font-medium text-slate-700">{{ cfg.label }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input v-model="addForm.notes" type="text" placeholder="Contoh: izin keperluan keluarga"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"/>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showAdd = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="addForm.processing"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50">Simpan</button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Edit -->
        <Modal :show="showEdit" @close="showEdit = false" max-width="sm">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-4">Edit Absensi</h3>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status Kehadiran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="(cfg, val) in statusConfig" :key="val"
                                class="flex cursor-pointer items-center gap-2.5 rounded-lg border p-2.5 transition-colors"
                                :class="editForm.status === val ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" v-model="editForm.status" :value="val" class="sr-only"/>
                                <span class="size-2.5 rounded-full" :class="cfg.dot"></span>
                                <span class="text-sm font-medium text-slate-700">{{ cfg.label }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input v-model="editForm.notes" type="text"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"/>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showEdit = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="editForm.processing"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50">Perbarui</button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Hapus -->
        <Modal :show="!!deleteTarget" @close="deleteTarget = null" max-width="sm">
            <div class="p-6">
                <h3 class="text-base font-bold text-slate-900 mb-2">Hapus Absensi?</h3>
                <p class="text-sm text-slate-600 mb-5">
                    Absensi tanggal <strong>{{ deleteTarget?.date }}</strong> akan dihapus.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="deleteTarget = null"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button @click="submitDelete" :disabled="deleteForm.processing"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-50">Ya, Hapus</button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
