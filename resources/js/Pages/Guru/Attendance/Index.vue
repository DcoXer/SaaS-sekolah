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
    schoolLat:    { type: Number, default: null },
    schoolLng:    { type: Number, default: null },
    schoolRadius: { type: Number, default: 100 },
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

const todayEntry = computed(() => props.calendar.find(e => e.date === todayStr) ?? null);

// ── Filter ─────────────────────────────────────────────────────────────────────
const filterStatus = ref('');
const filtered = computed(() => {
    if (!filterStatus.value) return props.calendar;
    if (filterStatus.value === 'kosong') return props.calendar.filter(e => !e.attendance && !e.is_weekend);
    return props.calendar.filter(e => e.attendance?.status === filterStatus.value);
});

// ── Pagination ─────────────────────────────────────────────────────────────────
const PER_PAGE    = 15;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch(filterStatus, () => { currentPage.value = 1; });

// ── Status Config ──────────────────────────────────────────────────────────────
const statusConfig = {
    hadir: { label: 'Hadir', badge: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', dot: 'bg-emerald-500' },
    izin:  { label: 'Izin',  badge: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',          dot: 'bg-blue-500'    },
    sakit: { label: 'Sakit', badge: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',        dot: 'bg-amber-500'   },
    alpha: { label: 'Alpha', badge: 'bg-red-50 text-red-700 ring-1 ring-red-200',              dot: 'bg-red-500'     },
};

// ── Geolocation ────────────────────────────────────────────────────────────────
const geoStatus   = ref('idle'); // idle | checking | success | error | unsupported
const geoDistance = ref(null);
const geoLat      = ref(null);
const geoLng      = ref(null);
const geoError    = ref('');

const schoolConfigured = computed(() => props.schoolLat !== null && props.schoolLng !== null);

const isInRange = computed(() => {
    if (!schoolConfigured.value) return true;
    if (geoStatus.value !== 'success') return false;
    return geoDistance.value <= props.schoolRadius;
});

function haversine(lat1, lon1, lat2, lon2) {
    const R  = 6371000;
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;
    const a  = Math.sin(Δφ / 2) ** 2 + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function checkGeo() {
    if (!schoolConfigured.value) return;
    if (!navigator.geolocation) {
        geoStatus.value = 'unsupported';
        return;
    }
    geoStatus.value   = 'checking';
    geoDistance.value = null;
    geoLat.value      = null;
    geoLng.value      = null;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            geoLat.value      = pos.coords.latitude;
            geoLng.value      = pos.coords.longitude;
            geoDistance.value = Math.round(haversine(
                pos.coords.latitude, pos.coords.longitude,
                props.schoolLat, props.schoolLng
            ));
            geoStatus.value = 'success';
        },
        (err) => {
            geoError.value  = err.code === 1 ? 'Akses lokasi ditolak. Izinkan akses lokasi di browser.'
                            : err.code === 2 ? 'Lokasi tidak dapat ditentukan.'
                            : 'Timeout saat mengambil lokasi.';
            geoStatus.value = 'error';
        },
        { timeout: 10000, maximumAge: 0, enableHighAccuracy: true }
    );
}

function fmtDistance(m) {
    if (m === null || m === undefined) return '';
    return m >= 1000 ? `${(m / 1000).toFixed(1)} km` : `${m} m`;
}

// ── Add Modal ──────────────────────────────────────────────────────────────────
const showAdd = ref(false);
const isToday = ref(false);
const addForm = useForm({ date: '', status: 'hadir', notes: '', latitude: null, longitude: null });

const openAdd = (date) => {
    addForm.date      = date;
    addForm.status    = 'hadir';
    addForm.notes     = '';
    addForm.latitude  = null;
    addForm.longitude = null;
    addForm.clearErrors();

    isToday.value     = (date === todayStr);
    geoStatus.value   = 'idle';
    geoDistance.value = null;
    geoLat.value      = null;
    geoLng.value      = null;

    showAdd.value = true;
    if (isToday.value && schoolConfigured.value) checkGeo();
};

watch(geoLat, (val) => { addForm.latitude  = val; });
watch(geoLng, (val) => { addForm.longitude = val; });

const canSubmitAdd = computed(() => {
    if (!isToday.value) return true;
    if (addForm.status !== 'hadir') return true;
    if (!schoolConfigured.value) return true;
    return isInRange.value;
});

const hadirDisabled = computed(() =>
    isToday.value && schoolConfigured.value &&
    geoStatus.value !== 'idle' && geoStatus.value !== 'checking' && !isInRange.value
);

const submitAdd = () => {
    addForm.post(route('guru.attendance.store'), { onSuccess: () => { showAdd.value = false; } });
};

// ── Edit Modal ─────────────────────────────────────────────────────────────────
const showEdit   = ref(false);
const editTarget = ref(null);
const editForm   = useForm({ status: '', notes: '' });

const openEdit = (entry) => {
    editTarget.value = entry.attendance;
    editForm.status  = entry.attendance.status;
    editForm.notes   = entry.attendance.notes ?? '';
    editForm.clearErrors();
    showEdit.value   = true;
};
const submitEdit = () => {
    editForm.patch(route('guru.attendance.update', editTarget.value.id), {
        onSuccess: () => { showEdit.value = false; },
    });
};

// ── Delete ─────────────────────────────────────────────────────────────────────
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

            <!-- Header + Month Nav -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Absensi Saya</h2>
                    <p class="text-sm text-slate-500">Kehadiran harian dihitung sebagai uang transport.</p>
                </div>
                <div class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <button @click="prevMonth"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
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

            <!-- Today Card -->
            <div v-if="isCurrentMonth && todayEntry && !todayEntry.is_weekend"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 px-5 py-4">
                    <div class="flex items-center gap-3.5">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500">
                            <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Hari Ini</p>
                            <p class="text-sm font-bold text-slate-900">
                                {{ todayEntry.day_name }}, {{ todayEntry.day }} {{ monthNames[currentMonth] }} {{ currentYear }}
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <template v-if="todayEntry.attendance">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold"
                                :class="statusConfig[todayEntry.attendance.status]?.badge">
                                <span class="size-2 rounded-full" :class="statusConfig[todayEntry.attendance.status]?.dot"></span>
                                {{ statusConfig[todayEntry.attendance.status]?.label }}
                            </span>
                            <button @click="openEdit(todayEntry)"
                                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                Edit
                            </button>
                        </template>
                        <button v-else @click="openAdd(todayEntry.date)"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 transition-colors">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Check In Sekarang
                        </button>
                    </div>
                </div>
                <div v-if="todayEntry.attendance?.notes"
                    class="border-t border-slate-100 bg-slate-50 px-5 py-2.5">
                    <p class="text-xs text-slate-500">
                        <span class="font-semibold text-slate-600">Keterangan:</span>
                        {{ todayEntry.attendance.notes }}
                    </p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-2 sm:gap-3">
                <div v-for="(cfg, key) in statusConfig" :key="key"
                    class="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white py-4 shadow-sm">
                    <span class="mb-1.5 size-2 rounded-full" :class="cfg.dot"></span>
                    <span class="text-2xl font-bold leading-none text-slate-800 tabular-nums">{{ summary[key] }}</span>
                    <span class="mt-1 text-xs font-medium text-slate-500">{{ cfg.label }}</span>
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
                    :class="filterStatus === opt.value
                        ? 'bg-emerald-500 text-white'
                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                    {{ opt.label }}
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <p class="text-sm font-semibold text-slate-700">Tidak ada data untuk filter ini</p>
            </div>

            <!-- List -->
            <div v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <!-- Desktop header -->
                <div class="hidden border-b border-slate-100 bg-slate-50/80 px-5 py-3 sm:grid"
                    style="grid-template-columns: 2.5rem 1fr 7rem 1fr 7rem">
                    <div></div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal</div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Keterangan</div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 text-right">Aksi</div>
                </div>

                <ul class="divide-y divide-slate-100">
                    <li v-for="entry in paginated" :key="entry.date"
                        class="px-4 py-3 transition-colors sm:px-5"
                        :class="{
                            'bg-slate-50/50': entry.is_weekend,
                            'bg-emerald-50/30': entry.date === todayStr && !entry.is_weekend,
                        }">

                        <!-- Mobile -->
                        <div class="sm:hidden">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold"
                                        :class="entry.date === todayStr
                                            ? 'bg-emerald-500 text-white'
                                            : entry.is_weekend ? 'bg-slate-100 text-slate-400' : 'bg-slate-100 text-slate-600'">
                                        {{ entry.day }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium"
                                            :class="entry.is_weekend ? 'text-slate-400' : 'text-slate-700'">
                                            {{ entry.day_name }}
                                            <span v-if="entry.date === todayStr"
                                                class="ml-1 text-xs font-semibold text-emerald-600">Hari ini</span>
                                        </p>
                                        <span v-if="entry.attendance"
                                            class="mt-0.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="statusConfig[entry.attendance.status]?.badge">
                                            <span class="size-1.5 rounded-full"
                                                :class="statusConfig[entry.attendance.status]?.dot"></span>
                                            {{ statusConfig[entry.attendance.status]?.label }}
                                        </span>
                                        <span v-else-if="entry.is_weekend" class="text-xs text-slate-400">Libur</span>
                                        <span v-else class="text-xs italic text-slate-400">Belum diisi</span>
                                    </div>
                                </div>
                                <div v-if="!entry.is_weekend" class="flex shrink-0 gap-1.5">
                                    <button v-if="!entry.attendance && entry.date <= todayStr"
                                        @click="openAdd(entry.date)"
                                        class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-600">
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
                            <p v-if="entry.attendance?.notes"
                                class="mt-2 pl-12 text-xs text-slate-500">{{ entry.attendance.notes }}</p>
                        </div>

                        <!-- Desktop -->
                        <div class="hidden items-center gap-4 sm:grid"
                            style="grid-template-columns: 2.5rem 1fr 7rem 1fr 7rem">
                            <div class="flex size-8 items-center justify-center rounded-lg text-xs font-bold"
                                :class="entry.date === todayStr
                                    ? 'bg-emerald-500 text-white'
                                    : entry.is_weekend ? 'bg-slate-100 text-slate-400' : 'bg-slate-100 text-slate-600'">
                                {{ entry.day }}
                            </div>
                            <div>
                                <p class="text-sm font-medium"
                                    :class="entry.is_weekend ? 'text-slate-400' : 'text-slate-700'">
                                    {{ entry.day_name }}
                                    <span v-if="entry.date === todayStr"
                                        class="ml-1 text-xs font-semibold text-emerald-600">· Hari ini</span>
                                </p>
                                <p class="text-xs text-slate-400">{{ entry.date }}</p>
                            </div>
                            <div>
                                <span v-if="entry.attendance"
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="statusConfig[entry.attendance.status]?.badge">
                                    <span class="size-1.5 rounded-full"
                                        :class="statusConfig[entry.attendance.status]?.dot"></span>
                                    {{ statusConfig[entry.attendance.status]?.label }}
                                </span>
                                <span v-else-if="entry.is_weekend" class="text-xs text-slate-400">Libur</span>
                                <span v-else class="text-xs text-slate-400">—</span>
                            </div>
                            <div class="truncate text-xs text-slate-500">{{ entry.attendance?.notes ?? '' }}</div>
                            <div class="flex items-center justify-end gap-1.5">
                                <button v-if="!entry.is_weekend && !entry.attendance && entry.date <= todayStr"
                                    @click="openAdd(entry.date)"
                                    class="rounded-lg bg-emerald-500 px-3 py-1 text-xs font-semibold text-white transition-colors hover:bg-emerald-600">
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

        <!-- ───────────────────────── Modal Input Absensi ───────────────────────── -->
        <Modal :show="showAdd" @close="showAdd = false" max-width="sm">
            <div class="p-6">
                <!-- Header modal -->
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                        <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Input Absensi</h3>
                        <p class="text-xs text-slate-500">{{ addForm.date }}</p>
                    </div>
                </div>

                <!-- Verifikasi Lokasi (hanya untuk hari ini + sekolah punya koordinat) -->
                <div v-if="isToday && schoolConfigured"
                    class="mb-5 rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                    <p class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Verifikasi Lokasi</p>

                    <!-- Checking -->
                    <div v-if="geoStatus === 'checking'" class="flex items-center gap-2.5">
                        <svg class="size-4 shrink-0 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-sm text-slate-500">Mengambil lokasi GPS...</span>
                    </div>

                    <!-- Dalam jangkauan -->
                    <div v-else-if="geoStatus === 'success' && isInRange"
                        class="flex items-center gap-2.5">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="size-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-emerald-700">Dalam jangkauan sekolah</p>
                            <p class="text-xs text-slate-500">
                                {{ fmtDistance(geoDistance) }} dari sekolah &middot; batas {{ fmtDistance(schoolRadius) }}
                            </p>
                        </div>
                    </div>

                    <!-- Di luar jangkauan -->
                    <div v-else-if="geoStatus === 'success' && !isInRange"
                        class="flex items-center gap-2.5">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-red-100">
                            <svg class="size-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-red-600">Di luar jangkauan sekolah</p>
                            <p class="text-xs text-slate-500">
                                {{ fmtDistance(geoDistance) }} dari sekolah &middot; batas {{ fmtDistance(schoolRadius) }}
                            </p>
                        </div>
                        <button @click="checkGeo"
                            class="shrink-0 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50">
                            Coba lagi
                        </button>
                    </div>

                    <!-- Error -->
                    <div v-else-if="geoStatus === 'error'" class="flex items-start gap-2.5">
                        <div class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-amber-100">
                            <svg class="size-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-amber-700">Gagal mengambil lokasi</p>
                            <p class="text-xs text-slate-500">{{ geoError }}</p>
                        </div>
                        <button @click="checkGeo"
                            class="mt-0.5 shrink-0 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50">
                            Coba lagi
                        </button>
                    </div>

                    <!-- Browser tidak support -->
                    <div v-else-if="geoStatus === 'unsupported'" class="flex items-center gap-2.5">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-amber-100">
                            <svg class="size-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-amber-700">Browser tidak mendukung geolokasi.</p>
                    </div>

                    <!-- Idle -->
                    <div v-else class="flex items-center justify-between">
                        <p class="text-sm text-slate-500">Lokasi belum diverifikasi.</p>
                        <button @click="checkGeo"
                            class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-600">
                            Ambil Lokasi
                        </button>
                    </div>
                </div>

                <!-- Info hari lalu -->
                <div v-else-if="!isToday"
                    class="mb-4 flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2.5 text-xs text-blue-600">
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                    </svg>
                    Absensi hari lalu — verifikasi lokasi tidak diperlukan.
                </div>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Status Kehadiran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="(cfg, val) in statusConfig" :key="val"
                                class="flex items-center gap-2.5 rounded-lg border p-2.5 transition-colors"
                                :class="[
                                    hadirDisabled && val === 'hadir'
                                        ? 'cursor-not-allowed opacity-50 border-slate-200'
                                        : 'cursor-pointer',
                                    addForm.status === val
                                        ? 'border-emerald-400 bg-emerald-50'
                                        : 'border-slate-200 hover:bg-slate-50',
                                ]">
                                <input type="radio" v-model="addForm.status" :value="val" class="sr-only"
                                    :disabled="hadirDisabled && val === 'hadir'"/>
                                <span class="size-2.5 rounded-full" :class="cfg.dot"></span>
                                <span class="text-sm font-medium text-slate-700">{{ cfg.label }}</span>
                                <span v-if="val === 'hadir' && isToday && schoolConfigured && geoStatus === 'success' && !isInRange"
                                    class="ml-auto text-[10px] font-semibold text-red-400">Diluar area</span>
                            </label>
                        </div>
                        <p v-if="hadirDisabled && addForm.status === 'hadir'"
                            class="mt-1.5 text-xs text-red-500">
                            Status Hadir hanya bisa dipilih jika berada di area sekolah.
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Keterangan
                            <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input v-model="addForm.notes" type="text"
                            placeholder="Contoh: izin keperluan keluarga"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"/>
                        <p v-if="addForm.errors.notes" class="mt-1 text-xs text-red-500">{{ addForm.errors.notes }}</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showAdd = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="addForm.processing || !canSubmitAdd"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:opacity-50">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ───────────────────────── Modal Edit ───────────────────────────────── -->
        <Modal :show="showEdit" @close="showEdit = false" max-width="sm">
            <div class="p-6">
                <h3 class="mb-4 text-base font-bold text-slate-900">Edit Absensi</h3>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Status Kehadiran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="(cfg, val) in statusConfig" :key="val"
                                class="flex cursor-pointer items-center gap-2.5 rounded-lg border p-2.5 transition-colors"
                                :class="editForm.status === val
                                    ? 'border-emerald-400 bg-emerald-50'
                                    : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" v-model="editForm.status" :value="val" class="sr-only"/>
                                <span class="size-2.5 rounded-full" :class="cfg.dot"></span>
                                <span class="text-sm font-medium text-slate-700">{{ cfg.label }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Keterangan
                            <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input v-model="editForm.notes" type="text"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"/>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showEdit = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="editForm.processing"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:opacity-50">
                            Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ───────────────────────── Modal Hapus ──────────────────────────────── -->
        <Modal :show="!!deleteTarget" @close="deleteTarget = null" max-width="sm">
            <div class="p-6">
                <h3 class="mb-2 text-base font-bold text-slate-900">Hapus Absensi?</h3>
                <p class="mb-5 text-sm text-slate-600">
                    Absensi tanggal <strong>{{ deleteTarget?.date }}</strong> akan dihapus permanen.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="deleteTarget = null"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button @click="submitDelete" :disabled="deleteForm.processing"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600 disabled:opacity-50">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
