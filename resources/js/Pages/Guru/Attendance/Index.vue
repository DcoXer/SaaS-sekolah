<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';

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
const dayNames   = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

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

const now            = new Date();
const isCurrentMonth = computed(() =>
    props.currentMonth === now.getMonth() + 1 && props.currentYear === now.getFullYear()
);
const todayStr = now.toISOString().slice(0, 10);

const todayEntry = computed(() => props.calendar.find(e => e.date === todayStr) ?? null);

// ── Calendar Grid ──────────────────────────────────────────────────────────────
// Hitung offset awal bulan (0=Min, 1=Sen, ...)
const firstDayOffset = computed(() => {
    const d = new Date(props.currentYear, props.currentMonth - 1, 1);
    return d.getDay(); // 0 = Sunday
});
const calendarCells = computed(() => {
    const cells = Array(firstDayOffset.value).fill(null); // leading blanks
    return cells.concat(props.calendar);
});

// ── Status Config ──────────────────────────────────────────────────────────────
const statusConfig = {
    hadir: { label: 'Hadir', badge: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', dot: 'bg-emerald-500', cell: 'bg-emerald-500', light: 'bg-emerald-50 ring-emerald-300' },
    izin:  { label: 'Izin',  badge: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',             dot: 'bg-sky-500',     cell: 'bg-sky-500',     light: 'bg-sky-50 ring-sky-300'       },
    sakit: { label: 'Sakit', badge: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',       dot: 'bg-amber-500',   cell: 'bg-amber-500',   light: 'bg-amber-50 ring-amber-300'   },
    alpha: { label: 'Alpha', badge: 'bg-red-50 text-red-700 ring-1 ring-red-200',             dot: 'bg-red-500',     cell: 'bg-red-500',     light: 'bg-red-50 ring-red-300'       },
};

const hadirPct = computed(() => {
    const t = props.summary.total;
    return t > 0 ? Math.round((props.summary.hadir / t) * 100) : 0;
});

// ── Geolocation — schoolConfigured harus sebelum onMounted ────────────────────
const schoolConfigured = computed(() => props.schoolLat !== null && props.schoolLng !== null);

// ── Location Permission Warning ────────────────────────────────────────────────
// 'unknown' | 'prompt' | 'denied' | 'granted'
const locationPermission = ref('unknown');

onMounted(async () => {
    if (!schoolConfigured.value) return;
    if (!navigator.permissions) { locationPermission.value = 'prompt'; return; }
    try {
        const result = await navigator.permissions.query({ name: 'geolocation' });
        locationPermission.value = result.state;
        result.onchange = () => { locationPermission.value = result.state; };
    } catch {
        locationPermission.value = 'prompt';
    }
});

const showLocationWarning = computed(() =>
    schoolConfigured.value && locationPermission.value !== 'granted'
);

async function requestLocation() {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(
        () => { locationPermission.value = 'granted'; },
        () => { locationPermission.value = 'denied'; },
        { timeout: 10000 }
    );
}

// ── Geolocation ────────────────────────────────────────────────────────────────
const geoStatus   = ref('idle');
const geoDistance = ref(null);
const geoLat      = ref(null);
const geoLng      = ref(null);
const geoError    = ref('');

const isInRange = computed(() => {
    if (!schoolConfigured.value) return true;
    if (geoStatus.value !== 'success') return false;
    return geoDistance.value <= props.schoolRadius;
});

function haversine(lat1, lon1, lat2, lon2) {
    const R  = 6371000, toR = Math.PI / 180;
    const φ1 = lat1 * toR, φ2 = lat2 * toR;
    const Δφ = (lat2 - lat1) * toR, Δλ = (lon2 - lon1) * toR;
    const a  = Math.sin(Δφ / 2) ** 2 + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function checkGeo() {
    if (!schoolConfigured.value) return;
    if (!navigator.geolocation) { geoStatus.value = 'unsupported'; return; }
    geoStatus.value = 'checking'; geoDistance.value = null; geoLat.value = null; geoLng.value = null;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            geoLat.value      = pos.coords.latitude;
            geoLng.value      = pos.coords.longitude;
            geoDistance.value = Math.round(haversine(pos.coords.latitude, pos.coords.longitude, props.schoolLat, props.schoolLng));
            geoStatus.value   = 'success';
        },
        (err) => {
            geoError.value  = err.code === 1 ? 'Akses lokasi ditolak. Izinkan di browser.'
                            : err.code === 2 ? 'Lokasi tidak dapat ditentukan.'
                            : 'Timeout saat mengambil lokasi.';
            geoStatus.value = 'error';
        },
        { timeout: 10000, maximumAge: 0, enableHighAccuracy: true }
    );
}

function fmtDist(m) {
    if (m === null || m === undefined) return '';
    return m >= 1000 ? `${(m / 1000).toFixed(1)} km` : `${m} m`;
}

// ── Add Modal ──────────────────────────────────────────────────────────────────
const showAdd = ref(false);
const isToday = ref(false);
const addForm = useForm({ date: '', status: 'hadir', notes: '', latitude: null, longitude: null });

const openAdd = (date) => {
    addForm.date = date; addForm.status = 'hadir'; addForm.notes = '';
    addForm.latitude = null; addForm.longitude = null; addForm.clearErrors();
    isToday.value = (date === todayStr);
    geoStatus.value = 'idle'; geoDistance.value = null; geoLat.value = null; geoLng.value = null;
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
        onSuccess: () => { deleteTarget.value = null; showEdit.value = false; },
    });
};

// ── Cell click ─────────────────────────────────────────────────────────────────
function handleCellClick(entry) {
    if (!entry || entry.is_weekend) return;
    if (entry.date > todayStr) return; // future
    if (entry.attendance) openEdit(entry);
    else openAdd(entry.date);
}
</script>

<template>
    <Head title="Absensi Saya" />

    <AppLayout>
        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Guru</span><span>/</span>
                <span class="font-semibold text-slate-700">Absensi</span>
            </div>
        </template>

        <div class="space-y-5 pb-8">

            <!-- ── Summary banner ─────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 p-5 shadow-lg shadow-emerald-200">
                <div class="pointer-events-none absolute -right-6 -top-6 size-36 rounded-full bg-white/10 blur-2xl" />
                <div class="pointer-events-none absolute -bottom-4 left-1/3 size-24 rounded-full bg-white/10 blur-xl" />

                <!-- Month nav -->
                <div class="relative mb-4 flex items-center justify-between">
                    <button @click="prevMonth"
                        class="flex size-8 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/30">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <div class="text-center">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-100">Absensi Saya</p>
                        <h2 class="text-lg font-extrabold text-white">{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
                    </div>
                    <button @click="nextMonth" :disabled="isCurrentMonth"
                        class="flex size-8 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/30 disabled:opacity-30">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>

                <!-- Stats -->
                <div class="relative grid grid-cols-4 gap-2">
                    <div class="flex flex-col items-center gap-0.5 rounded-2xl bg-white/15 py-3 backdrop-blur-sm">
                        <span class="tabular-nums text-xl font-extrabold text-white">{{ summary.hadir }}</span>
                        <span class="text-[11px] font-semibold text-emerald-100">Hadir</span>
                    </div>
                    <div class="flex flex-col items-center gap-0.5 rounded-2xl bg-white/15 py-3 backdrop-blur-sm">
                        <span class="tabular-nums text-xl font-extrabold text-white">{{ summary.izin }}</span>
                        <span class="text-[11px] font-semibold text-emerald-100">Izin</span>
                    </div>
                    <div class="flex flex-col items-center gap-0.5 rounded-2xl bg-white/15 py-3 backdrop-blur-sm">
                        <span class="tabular-nums text-xl font-extrabold text-white">{{ summary.sakit }}</span>
                        <span class="text-[11px] font-semibold text-emerald-100">Sakit</span>
                    </div>
                    <div class="flex flex-col items-center gap-0.5 rounded-2xl bg-white/15 py-3 backdrop-blur-sm">
                        <span class="tabular-nums text-xl font-extrabold text-white">{{ summary.alpha }}</span>
                        <span class="text-[11px] font-semibold text-emerald-100">Alpha</span>
                    </div>
                </div>

                <!-- Progress bar -->
                <div v-if="summary.total > 0" class="relative mt-3">
                    <div class="flex h-1.5 overflow-hidden rounded-full bg-white/20">
                        <div class="h-full bg-white transition-all" :style="{ width: hadirPct + '%' }" />
                    </div>
                    <p class="mt-1.5 text-right text-xs font-semibold text-emerald-100">
                        {{ hadirPct }}% kehadiran · {{ summary.total }} hari tercatat
                    </p>
                </div>
            </div>

            <!-- ── Location warning ───────────────────────────────────────── -->
            <Transition
                enter-from-class="opacity-0 -translate-y-2"
                enter-active-class="transition duration-300"
                leave-to-class="opacity-0 -translate-y-2"
                leave-active-class="transition duration-200"
            >
                <div v-if="showLocationWarning"
                    class="overflow-hidden rounded-2xl border shadow-sm"
                    :class="locationPermission === 'denied'
                        ? 'border-red-200 bg-red-50'
                        : 'border-amber-200 bg-amber-50'">
                    <div class="flex items-start gap-3.5 px-5 py-4">
                        <!-- Icon -->
                        <div class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl"
                            :class="locationPermission === 'denied' ? 'bg-red-100' : 'bg-amber-100'">
                            <svg v-if="locationPermission === 'denied'" class="size-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            <svg v-else class="size-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                        </div>
                        <!-- Text -->
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold"
                                :class="locationPermission === 'denied' ? 'text-red-700' : 'text-amber-700'">
                                {{ locationPermission === 'denied'
                                    ? 'Akses lokasi ditolak'
                                    : 'Izinkan akses lokasi' }}
                            </p>
                            <p class="mt-0.5 text-xs leading-relaxed"
                                :class="locationPermission === 'denied' ? 'text-red-600' : 'text-amber-600'">
                                {{ locationPermission === 'denied'
                                    ? 'Absensi dengan status Hadir memerlukan verifikasi lokasi. Buka pengaturan browser dan izinkan akses lokasi untuk situs ini.'
                                    : 'Absensi dengan status Hadir memerlukan verifikasi lokasi agar sistem dapat memastikan kamu berada di area sekolah.' }}
                            </p>
                        </div>
                        <!-- CTA -->
                        <button v-if="locationPermission !== 'denied'"
                            @click="requestLocation"
                            class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600 active:scale-95">
                            Izinkan
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- ── Today CTA ───────────────────────────────────────────────── -->
            <div v-if="isCurrentMonth && todayEntry && !todayEntry.is_weekend"
                class="overflow-hidden rounded-2xl border bg-white shadow-sm"
                :class="todayEntry.attendance ? 'border-slate-100' : 'border-emerald-200 shadow-emerald-100'">
                <div class="flex items-center gap-4 px-5 py-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl"
                        :class="todayEntry.attendance
                            ? (statusConfig[todayEntry.attendance.status]?.light ?? 'bg-slate-100')
                            : 'bg-emerald-50 ring-4 ring-emerald-100'">
                        <svg v-if="!todayEntry.attendance" class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <span v-else class="size-3 rounded-full" :class="statusConfig[todayEntry.attendance.status]?.dot"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider"
                            :class="todayEntry.attendance ? 'text-slate-400' : 'text-emerald-600'">Hari Ini</p>
                        <p class="text-sm font-bold text-slate-900">
                            {{ todayEntry.day_name }}, {{ todayEntry.day }} {{ monthNames[currentMonth] }}
                        </p>
                        <p v-if="todayEntry.attendance" class="text-xs text-slate-400 mt-0.5">
                            {{ todayEntry.attendance.notes || statusConfig[todayEntry.attendance.status]?.label }}
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <template v-if="todayEntry.attendance">
                            <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                                :class="statusConfig[todayEntry.attendance.status]?.badge">
                                <span class="size-1.5 rounded-full" :class="statusConfig[todayEntry.attendance.status]?.dot"></span>
                                {{ statusConfig[todayEntry.attendance.status]?.label }}
                            </span>
                            <button @click="openEdit(todayEntry)"
                                class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                Edit
                            </button>
                        </template>
                        <button v-else @click="openAdd(todayEntry.date)"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600 active:scale-95">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Check In
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Calendar grid ──────────────────────────────────────────── -->
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <!-- Day headers -->
                <div class="grid grid-cols-7 border-b border-slate-100">
                    <div v-for="d in dayNames" :key="d"
                        class="py-2.5 text-center text-[11px] font-bold uppercase tracking-wide"
                        :class="d === 'Min' || d === 'Sab' ? 'text-red-400' : 'text-slate-400'">
                        {{ d }}
                    </div>
                </div>

                <!-- Cells -->
                <div class="grid grid-cols-7 gap-px bg-slate-100">
                    <!-- Leading blanks -->
                    <div v-for="n in firstDayOffset" :key="'blank-' + n" class="bg-white" />

                    <!-- Day cells -->
                    <div
                        v-for="entry in calendar" :key="entry.date"
                        class="relative flex min-h-[3.75rem] flex-col items-center pt-2.5 bg-white transition-colors"
                        :class="[
                            entry.is_weekend ? 'bg-slate-50/60' : '',
                            entry.date === todayStr ? 'bg-emerald-50/40' : '',
                            !entry.is_weekend && entry.date <= todayStr ? 'cursor-pointer hover:bg-slate-50' : '',
                        ]"
                        @click="handleCellClick(entry)"
                    >
                        <!-- Day number -->
                        <span class="flex size-7 items-center justify-center rounded-full text-xs font-bold transition-colors"
                            :class="entry.date === todayStr
                                ? 'bg-emerald-500 text-white shadow-sm'
                                : entry.is_weekend
                                    ? 'text-red-300'
                                    : entry.date > todayStr
                                        ? 'text-slate-300'
                                        : 'text-slate-700'">
                            {{ entry.day }}
                        </span>

                        <!-- Status dot / badge -->
                        <div class="mt-1.5 flex flex-col items-center gap-1">
                            <template v-if="entry.attendance">
                                <span class="size-2 rounded-full"
                                    :class="statusConfig[entry.attendance.status]?.dot ?? 'bg-slate-300'" />
                                <span class="hidden text-[9px] font-bold sm:block"
                                    :class="{
                                        'text-emerald-600': entry.attendance.status === 'hadir',
                                        'text-sky-600':     entry.attendance.status === 'izin',
                                        'text-amber-600':   entry.attendance.status === 'sakit',
                                        'text-red-500':     entry.attendance.status === 'alpha',
                                    }">
                                    {{ statusConfig[entry.attendance.status]?.label }}
                                </span>
                            </template>
                            <span v-else-if="!entry.is_weekend && entry.date < todayStr"
                                class="size-2 rounded-full bg-slate-200" />
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 border-t border-slate-50 px-5 py-3">
                    <div v-for="(cfg, key) in statusConfig" :key="key" class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full" :class="cfg.dot" />
                        <span class="text-xs text-slate-500">{{ cfg.label }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-slate-200" />
                        <span class="text-xs text-slate-400">Belum diisi</span>
                    </div>
                    <p class="ml-auto text-[11px] text-slate-400">Klik tanggal untuk input/edit</p>
                </div>
            </div>

            <!-- ── Riwayat list ────────────────────────────────────────────── -->
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Riwayat Kehadiran</h3>
                </div>

                <ul class="divide-y divide-slate-50">
                    <template v-for="entry in [...calendar].reverse()" :key="entry.date">
                        <li v-if="!entry.is_weekend && entry.date <= todayStr"
                            class="group flex items-center gap-3.5 px-5 py-3 transition-colors"
                            :class="entry.date === todayStr ? 'bg-emerald-50/50' : 'hover:bg-slate-50/70'"
                        >
                            <!-- Day badge -->
                            <div class="flex size-9 shrink-0 flex-col items-center justify-center rounded-xl text-center"
                                :class="entry.date === todayStr ? 'bg-emerald-500 shadow-sm shadow-emerald-200' : 'bg-slate-100'">
                                <span class="text-xs font-extrabold leading-none"
                                    :class="entry.date === todayStr ? 'text-white' : 'text-slate-700'">
                                    {{ entry.day }}
                                </span>
                                <span class="text-[9px] font-semibold"
                                    :class="entry.date === todayStr ? 'text-emerald-100' : 'text-slate-400'">
                                    {{ entry.day_name.slice(0, 3) }}
                                </span>
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-800">{{ entry.day_name }}</span>
                                    <span v-if="entry.date === todayStr"
                                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                        Hari ini
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400">{{ entry.date }}</p>
                                <p v-if="entry.attendance?.notes" class="mt-0.5 text-xs text-slate-500 italic">
                                    "{{ entry.attendance.notes }}"
                                </p>
                            </div>

                            <!-- Status -->
                            <div class="flex shrink-0 items-center gap-2">
                                <span v-if="entry.attendance"
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="statusConfig[entry.attendance.status]?.badge">
                                    <span class="size-1.5 rounded-full" :class="statusConfig[entry.attendance.status]?.dot"></span>
                                    {{ statusConfig[entry.attendance.status]?.label }}
                                </span>
                                <span v-else class="text-xs text-slate-300 italic">Belum diisi</span>
                            </div>

                            <!-- Actions -->
                            <div class="flex shrink-0 gap-1.5 opacity-0 transition-opacity group-hover:opacity-100">
                                <button v-if="!entry.attendance" @click="openAdd(entry.date)"
                                    class="rounded-lg bg-emerald-500 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600">
                                    Input
                                </button>
                                <template v-if="entry.attendance">
                                    <button @click="openEdit(entry)"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                        Edit
                                    </button>
                                    <button @click="deleteTarget = entry"
                                        class="rounded-lg border border-red-100 px-2.5 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </template>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

        </div>

        <!-- ═══════════════════════ Modal Input Absensi ═══════════════════════════ -->
        <Modal :show="showAdd" @close="showAdd = false" max-width="sm">
            <div class="overflow-hidden rounded-2xl">
                <!-- Modal header -->
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">Input Absensi</p>
                    <h3 class="mt-0.5 text-lg font-extrabold text-white">
                        {{ addForm.date ? new Date(addForm.date + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '' }}
                    </h3>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Geolocation -->
                    <div v-if="isToday && schoolConfigured"
                        class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                        <p class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Verifikasi Lokasi</p>

                        <div v-if="geoStatus === 'checking'" class="flex items-center gap-2.5">
                            <svg class="size-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span class="text-sm text-slate-500">Mengambil lokasi GPS...</span>
                        </div>

                        <div v-else-if="geoStatus === 'success' && isInRange" class="flex items-center gap-2.5">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="size-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-emerald-700">Dalam jangkauan sekolah</p>
                                <p class="text-xs text-slate-500">{{ fmtDist(geoDistance) }} dari sekolah · batas {{ fmtDist(schoolRadius) }}</p>
                            </div>
                        </div>

                        <div v-else-if="geoStatus === 'success' && !isInRange" class="flex items-center gap-2.5">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                                <svg class="size-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-red-600">Di luar jangkauan sekolah</p>
                                <p class="text-xs text-slate-500">{{ fmtDist(geoDistance) }} dari sekolah · batas {{ fmtDist(schoolRadius) }}</p>
                            </div>
                            <button @click="checkGeo"
                                class="shrink-0 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Coba lagi
                            </button>
                        </div>

                        <div v-else-if="geoStatus === 'error'" class="flex items-start gap-2.5">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100">
                                <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-amber-700">Gagal mengambil lokasi</p>
                                <p class="text-xs text-slate-500">{{ geoError }}</p>
                            </div>
                            <button @click="checkGeo"
                                class="mt-0.5 shrink-0 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Coba lagi
                            </button>
                        </div>

                        <div v-else-if="geoStatus === 'unsupported'" class="flex items-center gap-2.5">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100">
                                <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-amber-700">Browser tidak mendukung geolokasi.</p>
                        </div>

                        <div v-else class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Lokasi belum diverifikasi.</p>
                            <button @click="checkGeo"
                                class="rounded-xl bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600">
                                Ambil Lokasi
                            </button>
                        </div>
                    </div>

                    <!-- Info hari lalu -->
                    <div v-else-if="!isToday"
                        class="flex items-center gap-2 rounded-xl bg-sky-50 px-4 py-3 text-xs text-sky-600 ring-1 ring-sky-200">
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                        </svg>
                        <span class="font-medium">Absensi hari lalu — verifikasi lokasi tidak diperlukan.</span>
                    </div>

                    <!-- Status radio -->
                    <div>
                        <label class="mb-2.5 block text-sm font-bold text-slate-700">Status Kehadiran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="(cfg, val) in statusConfig" :key="val"
                                class="flex items-center gap-3 rounded-xl border-2 p-3 transition-all"
                                :class="[
                                    hadirDisabled && val === 'hadir'
                                        ? 'cursor-not-allowed opacity-40 border-slate-100'
                                        : 'cursor-pointer',
                                    addForm.status === val
                                        ? 'border-emerald-400 bg-emerald-50/70 shadow-sm'
                                        : 'border-slate-150 hover:border-slate-200 hover:bg-slate-50',
                                ]">
                                <input type="radio" v-model="addForm.status" :value="val" class="sr-only"
                                    :disabled="hadirDisabled && val === 'hadir'"/>
                                <span class="size-3 rounded-full shadow-sm" :class="cfg.dot"></span>
                                <span class="text-sm font-semibold text-slate-700">{{ cfg.label }}</span>
                                <span v-if="val === 'hadir' && hadirDisabled"
                                    class="ml-auto text-[10px] font-bold text-red-400">Diluar area</span>
                            </label>
                        </div>
                        <p v-if="hadirDisabled && addForm.status === 'hadir'"
                            class="mt-1.5 text-xs text-red-500">
                            Status Hadir hanya bisa dipilih jika berada di area sekolah.
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700">
                            Keterangan
                            <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input v-model="addForm.notes" type="text"
                            placeholder="Contoh: izin keperluan keluarga"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"/>
                        <p v-if="addForm.errors.notes" class="mt-1 text-xs text-red-500">{{ addForm.errors.notes }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-1">
                        <button type="button" @click="showAdd = false"
                            class="flex-1 rounded-xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button @click="submitAdd" :disabled="addForm.processing || !canSubmitAdd"
                            class="flex-1 rounded-xl bg-emerald-500 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-600 disabled:opacity-50">
                            {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- ═══════════════════════ Modal Edit ═══════════════════════════════════ -->
        <Modal :show="showEdit" @close="showEdit = false" max-width="sm">
            <div class="overflow-hidden rounded-2xl">
                <div class="bg-gradient-to-r from-sky-500 to-blue-500 px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-sky-100">Edit Absensi</p>
                    <h3 class="mt-0.5 text-lg font-extrabold text-white">
                        {{ editTarget?.date ? new Date(editTarget.date + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) : '' }}
                    </h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="mb-2.5 block text-sm font-bold text-slate-700">Status Kehadiran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="(cfg, val) in statusConfig" :key="val"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 transition-all"
                                :class="editForm.status === val
                                    ? 'border-sky-400 bg-sky-50/70 shadow-sm'
                                    : 'border-slate-150 hover:border-slate-200 hover:bg-slate-50'">
                                <input type="radio" v-model="editForm.status" :value="val" class="sr-only"/>
                                <span class="size-3 rounded-full shadow-sm" :class="cfg.dot"></span>
                                <span class="text-sm font-semibold text-slate-700">{{ cfg.label }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-700">
                            Keterangan <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input v-model="editForm.notes" type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-2 focus:ring-sky-400/20"/>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button type="button" @click="showEdit = false"
                            class="flex-1 rounded-xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button @click="() => { deleteTarget = { attendance: editTarget }; showEdit = false; }"
                            class="rounded-xl border border-red-100 px-4 py-2.5 text-sm font-semibold text-red-500 transition hover:bg-red-50">
                            Hapus
                        </button>
                        <button @click="submitEdit" :disabled="editForm.processing"
                            class="flex-1 rounded-xl bg-sky-500 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-sky-600 disabled:opacity-50">
                            {{ editForm.processing ? 'Menyimpan...' : 'Perbarui' }}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- ═══════════════════════ Modal Hapus ══════════════════════════════════ -->
        <Modal :show="!!deleteTarget" @close="deleteTarget = null" max-width="sm">
            <div class="p-6">
                <div class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-red-100">
                    <svg class="size-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Hapus Absensi?</h3>
                <p class="mt-1 mb-6 text-sm text-slate-500">
                    Data absensi tanggal
                    <strong class="text-slate-700">{{ deleteTarget?.attendance?.date ?? deleteTarget?.date }}</strong>
                    akan dihapus permanen.
                </p>
                <div class="flex gap-2">
                    <button @click="deleteTarget = null"
                        class="flex-1 rounded-xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button @click="submitDelete" :disabled="deleteForm.processing"
                        class="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-600 disabled:opacity-50">
                        {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
