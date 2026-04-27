<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    setting:       { type: Object, default: null },
    registrations: { type: Object, default: null },
    stats:         { type: Object, default: null },
    filters:       { type: Object, default: () => ({}) },
});

// Setting form 
const showSettingModal = ref(false);

const settingForm = useForm({
    title:              props.setting?.title ?? '',
    description:        props.setting?.description ?? '',
    requirements:       props.setting?.requirements ?? '',
    registration_start: props.setting?.registration_start?.slice(0,10) ?? '',
    registration_end:   props.setting?.registration_end?.slice(0,10) ?? '',
    announcement_date:  props.setting?.announcement_date?.slice(0,10) ?? '',
    quota:              props.setting?.quota ?? 30,
    is_open:            props.setting?.is_open ?? false,
});

const saveSetting = () => {
    settingForm.post(route('operator.ppdb.save-setting'), {
        onSuccess: () => { showSettingModal.value = false; },
    });
};

// ── Filter ────────────────────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilter = () => {
    router.get(route('operator.ppdb.index'), { search: search.value, status: status.value }, { preserveState: true });
};

// ── Actions ───────────────────────────────────────────────────────────────
const actionTarget = ref(null);
const actionType   = ref(''); // 'accept' | 'waitlist' | 'reject'
const rejectNotes  = ref('');
const rejectError  = ref('');
const processing   = ref(false);

const openAction = (reg, type) => {
    actionTarget.value = reg;
    actionType.value   = type;
    rejectNotes.value  = '';
    rejectError.value  = '';
};
const closeAction = () => { actionTarget.value = null; };

const submitAction = () => {
    if (actionType.value === 'reject') {
        if (!rejectNotes.value.trim()) { rejectError.value = 'Alasan penolakan tidak boleh kosong.'; return; }
    }
    processing.value = true;
    const id = actionTarget.value.id;
    const routeMap = {
        accept:    route('operator.ppdb.accept',   id),
        waitlist:  route('operator.ppdb.waitlist', id),
        reject:    route('operator.ppdb.reject',   id),
    };
    const data = actionType.value === 'reject' ? { notes: rejectNotes.value } : {};
    router.patch(routeMap[actionType.value], data, {
        preserveState: false,
        onFinish: () => { processing.value = false; closeAction(); },
    });
};

// ── Detail modal ──────────────────────────────────────────────────────────
const detail = ref(null);

const statusColor = {
    pending:    'bg-amber-100 text-amber-700',
    accepted:   'bg-green-100 text-green-700',
    rejected:   'bg-red-100 text-red-700',
    waitlisted: 'bg-sky-100 text-sky-700',
};
const statusLabel = {
    pending:    'Menunggu',
    accepted:   'Diterima',
    rejected:   'Ditolak',
    waitlisted: 'Daftar Tunggu',
};

</script>

<template>
    <Head title="PPDB" />
    <AppLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">PPDB</h1>
                    <p class="mt-0.5 text-sm text-slate-500">Penerimaan Peserta Didik Baru</p>
                </div>
                <button @click="showSettingModal = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ setting ? 'Edit Pengaturan' : 'Buat Pengaturan PPDB' }}
                </button>
            </div>



            <!-- Belum ada setting -->
            <div v-if="!setting" class="rounded-2xl border-2 border-dashed border-slate-200 py-20 text-center">
                <svg class="mx-auto mb-3 size-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                <p class="text-base font-semibold text-slate-500">Pengaturan PPDB belum dibuat</p>
                <button @click="showSettingModal = true"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-600">
                    Buat Sekarang
                </button>
            </div>

            <template v-else>
                <!-- Setting info + Stats -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <!-- Setting card -->
                    <div class="col-span-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="setting.is_open ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'">
                                    <span class="size-1.5 rounded-full" :class="setting.is_open ? 'bg-green-500 animate-pulse' : 'bg-slate-400'"/>
                                    {{ setting.is_open ? 'Dibuka' : 'Ditutup' }}
                                </span>
                                <h2 class="mt-2 text-lg font-extrabold text-slate-900">{{ setting.title }}</h2>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Mulai</p>
                                <p class="mt-0.5 font-medium text-slate-700">{{ new Date(setting.registration_start).toLocaleDateString('id-ID') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Selesai</p>
                                <p class="mt-0.5 font-medium text-slate-700">{{ new Date(setting.registration_end).toLocaleDateString('id-ID') }}</p>
                            </div>
                            <div v-if="setting.announcement_date">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Pengumuman</p>
                                <p class="mt-0.5 font-medium text-slate-700">{{ new Date(setting.announcement_date).toLocaleDateString('id-ID') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Kuota</p>
                                <p class="mt-0.5 font-medium text-slate-700">{{ setting.quota }} siswa</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div v-if="stats" class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:col-span-1 lg:grid-cols-2">
                        <div v-for="s in [
                            { label: 'Total',        value: stats.total,      color: 'bg-slate-100 text-slate-700' },
                            { label: 'Menunggu',     value: stats.pending,    color: 'bg-amber-100 text-amber-700' },
                            { label: 'Diterima',     value: stats.accepted,   color: 'bg-green-100 text-green-700' },
                            { label: 'Ditolak',      value: stats.rejected,   color: 'bg-red-100 text-red-700' },
                        ]" :key="s.label"
                            class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-5 shadow-sm">
                            <span class="text-2xl font-extrabold text-slate-900">{{ s.value }}</span>
                            <span class="mt-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold" :class="s.color">{{ s.label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Filter & Search -->
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        <input v-model="search" type="search" placeholder="Cari nama, nomor, telepon..."
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"
                            @keyup.enter="applyFilter"/>
                    </div>
                    <select v-model="status" @change="applyFilter"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="accepted">Diterima</option>
                        <option value="rejected">Ditolak</option>
                        <option value="waitlisted">Daftar Tunggu</option>
                    </select>
                    <button @click="applyFilter"
                        class="rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-600">
                        Filter
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">No. Daftar</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Nama Calon Siswa</th>
                                    <th class="hidden px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 sm:table-cell">Orang Tua</th>
                                    <th class="hidden px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 md:table-cell">Tgl. Daftar</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-if="!registrations?.data?.length">
                                    <td colspan="6" class="py-16 text-center text-sm text-slate-400">Belum ada pendaftar.</td>
                                </tr>
                                <tr v-for="reg in registrations?.data" :key="reg.id"
                                    class="transition-colors hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ reg.registration_number }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800">{{ reg.full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ reg.gender === 'male' ? 'L' : 'P' }} · {{ reg.birth_place }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 sm:table-cell">
                                        <p class="font-medium text-slate-700">{{ reg.parent_name }}</p>
                                        <p class="text-xs text-slate-400">{{ reg.parent_phone }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 text-xs text-slate-400 md:table-cell">
                                        {{ new Date(reg.created_at).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusColor[reg.status]">
                                            {{ statusLabel[reg.status] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <button @click="detail = reg" title="Lihat detail"
                                                class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </button>
                                            <template v-if="reg.status === 'pending' || reg.status === 'waitlisted'">
                                                <button @click="openAction(reg, 'accept')" title="Terima"
                                                    class="flex size-8 items-center justify-center rounded-lg bg-green-50 text-green-600 transition-colors hover:bg-green-100">
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                </button>
                                                <button v-if="reg.status === 'pending'" @click="openAction(reg, 'waitlist')" title="Daftar Tunggu"
                                                    class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 transition-colors hover:bg-sky-100">
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                                <button @click="openAction(reg, 'reject')" title="Tolak"
                                                    class="flex size-8 items-center justify-center rounded-lg bg-red-50 text-red-500 transition-colors hover:bg-red-100">
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="registrations?.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
                        <p class="text-xs text-slate-400">
                            {{ registrations.from }}–{{ registrations.to }} dari {{ registrations.total }}
                        </p>
                        <div class="flex gap-1">
                            <a v-for="link in registrations.links" :key="link.label"
                                :href="link.url ?? '#'"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                :class="link.active
                                    ? 'bg-green-700 text-white'
                                    : link.url ? 'border border-slate-200 text-slate-600 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ── Modal Setting ──────────────────────────────────────────────────── -->
        <Modal :show="showSettingModal" @close="showSettingModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-extrabold text-slate-900">Pengaturan PPDB</h2>
                <button @click="showSettingModal = false" class="flex size-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveSetting" class="max-h-[75vh] overflow-y-auto">
                <div class="space-y-4 p-6">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Judul PPDB <span class="text-red-500">*</span></label>
                        <input v-model="settingForm.title" type="text" placeholder="misal: PPDB Tahun Ajaran 2025/2026"
                            class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"
                            :class="settingForm.errors.title ? 'border-red-300 bg-red-50' : 'border-slate-200'"/>
                        <p v-if="settingForm.errors.title" class="mt-1 text-xs text-red-500">{{ settingForm.errors.title }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Mulai Pendaftaran <span class="text-red-500">*</span></label>
                            <input v-model="settingForm.registration_start" type="date"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"
                                :class="settingForm.errors.registration_start ? 'border-red-300 bg-red-50' : 'border-slate-200'"/>
                            <p v-if="settingForm.errors.registration_start" class="mt-1 text-xs text-red-500">{{ settingForm.errors.registration_start }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Selesai Pendaftaran <span class="text-red-500">*</span></label>
                            <input v-model="settingForm.registration_end" type="date"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"
                                :class="settingForm.errors.registration_end ? 'border-red-300 bg-red-50' : 'border-slate-200'"/>
                            <p v-if="settingForm.errors.registration_end" class="mt-1 text-xs text-red-500">{{ settingForm.errors.registration_end }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tanggal Pengumuman</label>
                            <input v-model="settingForm.announcement_date" type="date"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"/>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kuota Siswa <span class="text-red-500">*</span></label>
                            <input v-model="settingForm.quota" type="number" min="1"
                                class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"
                                :class="settingForm.errors.quota ? 'border-red-300 bg-red-50' : 'border-slate-200'"/>
                            <p v-if="settingForm.errors.quota" class="mt-1 text-xs text-red-500">{{ settingForm.errors.quota }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Deskripsi / Informasi PPDB</label>
                        <textarea v-model="settingForm.description" rows="3"
                            class="w-full resize-none rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"/>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Persyaratan Pendaftaran</label>
                        <textarea v-model="settingForm.requirements" rows="4" placeholder="Satu persyaratan per baris"
                            class="w-full resize-none rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100"/>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="settingForm.is_open = !settingForm.is_open"
                            class="relative inline-flex h-6 w-11 shrink-0 rounded-full border-2 border-transparent transition-colors"
                            :class="settingForm.is_open ? 'bg-green-600' : 'bg-slate-200'">
                            <span class="inline-block size-5 transform rounded-full bg-white shadow transition-transform"
                                :class="settingForm.is_open ? 'translate-x-5' : 'translate-x-0'"/>
                        </button>
                        <span class="text-sm font-medium text-slate-700">
                            Buka pendaftaran{{ settingForm.is_open ? ' (Aktif)' : ' (Nonaktif)' }}
                        </span>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" @click="showSettingModal = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="settingForm.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-5 py-2 text-sm font-bold text-white transition-colors hover:bg-green-600 disabled:opacity-60">
                        <svg v-if="settingForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Simpan
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Modal Aksi ────────────────────────────────────────────────────── -->
        <Modal :show="!!actionTarget" max-width="sm" @close="closeAction">
            <div class="px-6 py-5">
                <!-- Accept -->
                <template v-if="actionType === 'accept'">
                    <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-green-100">
                        <svg class="size-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Terima Pendaftaran</h3>
                    <p class="mt-1.5 text-sm text-slate-500">
                        Terima pendaftaran <span class="font-semibold text-slate-700">{{ actionTarget?.full_name }}</span>? Status akan berubah menjadi <span class="font-semibold text-green-600">Diterima</span>.
                    </p>
                </template>
                <!-- Waitlist -->
                <template v-else-if="actionType === 'waitlist'">
                    <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-sky-100">
                        <svg class="size-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Daftar Tunggu</h3>
                    <p class="mt-1.5 text-sm text-slate-500">
                        Masukkan <span class="font-semibold text-slate-700">{{ actionTarget?.full_name }}</span> ke daftar tunggu?
                    </p>
                </template>
                <!-- Reject -->
                <template v-else-if="actionType === 'reject'">
                    <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                        <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Tolak Pendaftaran</h3>
                    <p class="mt-1.5 text-sm text-slate-500">Tolak pendaftaran <span class="font-semibold text-slate-700">{{ actionTarget?.full_name }}</span>?</p>
                    <div class="mt-4">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea v-model="rejectNotes" rows="3" placeholder="Tuliskan alasan penolakan..."
                            class="w-full resize-none rounded-lg border px-3 py-2 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                            :class="rejectError ? 'border-red-300 bg-red-50' : 'border-slate-200'"/>
                        <p v-if="rejectError" class="mt-1 text-xs text-red-500">{{ rejectError }}</p>
                    </div>
                </template>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="closeAction"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                    Batal
                </button>
                <button @click="submitAction" :disabled="processing"
                    class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white transition-colors disabled:opacity-60"
                    :class="{
                        'bg-green-600 hover:bg-green-700': actionType === 'accept',
                        'bg-sky-600 hover:bg-sky-700':   actionType === 'waitlist',
                        'bg-red-500 hover:bg-red-600':   actionType === 'reject',
                    }">
                    <svg v-if="processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    <template v-if="!processing">
                        <span v-if="actionType === 'accept'">Ya, Terima</span>
                        <span v-else-if="actionType === 'waitlist'">Ya, Daftar Tunggu</span>
                        <span v-else>Ya, Tolak</span>
                    </template>
                </button>
            </div>
        </Modal>

        <!-- ── Modal Detail ───────────────────────────────────────────────────── -->
        <Modal :show="!!detail" max-width="xl" @close="detail = null">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h2 class="font-extrabold text-slate-900">{{ detail?.full_name }}</h2>
                    <span class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColor[detail?.status]">
                        {{ statusLabel[detail?.status] }}
                    </span>
                </div>
                <button @click="detail = null" class="flex size-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto p-6 space-y-6">
                <!-- Data Siswa -->
                <div>
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Data Siswa</p>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs font-bold text-slate-400">No. Daftar</dt><dd class="mt-0.5 font-mono text-xs text-slate-700">{{ detail?.registration_number }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">Jenis Kelamin</dt><dd class="mt-0.5 text-slate-700">{{ detail?.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">Tempat, Tgl. Lahir</dt><dd class="mt-0.5 text-slate-700">{{ detail?.birth_place }}, {{ detail?.birth_date ? new Date(detail.birth_date).toLocaleDateString('id-ID') : '' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">Agama</dt><dd class="mt-0.5 text-slate-700">{{ detail?.religion ?? '-' }}</dd></div>
                        <div v-if="detail?.nik_siswa"><dt class="text-xs font-bold text-slate-400">NIK Siswa</dt><dd class="mt-0.5 font-mono text-xs text-slate-700">{{ detail.nik_siswa }}</dd></div>
                        <div v-if="detail?.no_kk"><dt class="text-xs font-bold text-slate-400">No. KK</dt><dd class="mt-0.5 font-mono text-xs text-slate-700">{{ detail.no_kk }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">Asal TK/RA</dt><dd class="mt-0.5 text-slate-700">{{ detail?.previous_school ?? '-' }}</dd></div>
                    </dl>
                </div>
                <!-- Alamat -->
                <div>
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Alamat</p>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div v-if="detail?.province"><dt class="text-xs font-bold text-slate-400">Provinsi</dt><dd class="mt-0.5 text-slate-700">{{ detail.province }}</dd></div>
                        <div v-if="detail?.regency"><dt class="text-xs font-bold text-slate-400">Kab/Kota</dt><dd class="mt-0.5 text-slate-700">{{ detail.regency }}</dd></div>
                        <div v-if="detail?.district"><dt class="text-xs font-bold text-slate-400">Kecamatan</dt><dd class="mt-0.5 text-slate-700">{{ detail.district }}</dd></div>
                        <div v-if="detail?.village"><dt class="text-xs font-bold text-slate-400">Kelurahan/Desa</dt><dd class="mt-0.5 text-slate-700">{{ detail.village }}</dd></div>
                        <div class="col-span-2"><dt class="text-xs font-bold text-slate-400">Detail Alamat</dt><dd class="mt-0.5 text-slate-700">{{ detail?.address }}</dd></div>
                    </dl>
                </div>
                <!-- Data Orang Tua -->
                <div>
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Data Orang Tua</p>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs font-bold text-slate-400">Nama Ayah</dt><dd class="mt-0.5 text-slate-700">{{ detail?.father_name ?? '-' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">No. HP Ayah</dt><dd class="mt-0.5 text-slate-700">{{ detail?.father_phone ?? '-' }}</dd></div>
                        <div v-if="detail?.father_nik" class="col-span-2"><dt class="text-xs font-bold text-slate-400">NIK Ayah</dt><dd class="mt-0.5 font-mono text-xs text-slate-700">{{ detail.father_nik }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">Nama Ibu</dt><dd class="mt-0.5 text-slate-700">{{ detail?.mother_name ?? '-' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">No. HP Ibu</dt><dd class="mt-0.5 text-slate-700">{{ detail?.mother_phone ?? '-' }}</dd></div>
                        <div v-if="detail?.mother_nik" class="col-span-2"><dt class="text-xs font-bold text-slate-400">NIK Ibu</dt><dd class="mt-0.5 font-mono text-xs text-slate-700">{{ detail.mother_nik }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">No. WA Aktif</dt><dd class="mt-0.5 text-slate-700">{{ detail?.parent_phone }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-400">Email</dt><dd class="mt-0.5 text-slate-700">{{ detail?.parent_email ?? '-' }}</dd></div>
                    </dl>
                </div>
                <!-- Catatan -->
                <div v-if="detail?.notes" class="rounded-xl border border-red-100 bg-red-50 px-4 py-3">
                    <p class="text-xs font-bold text-red-400">Catatan Penolakan</p>
                    <p class="mt-1 text-sm text-slate-700">{{ detail.notes }}</p>
                </div>
                <!-- Dokumen -->
                <div>
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Dokumen</p>
                    <div class="flex flex-wrap gap-2">
                        <a v-if="detail?.photo" :href="`/storage/${detail.photo}`" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                            Foto Siswa
                        </a>
                        <a v-if="detail?.document_kk" :href="`/storage/${detail.document_kk}`" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Kartu Keluarga</a>
                        <a v-if="detail?.document_akta" :href="`/storage/${detail.document_akta}`" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Akta Lahir</a>
                        <span v-if="!detail?.photo && !detail?.document_kk && !detail?.document_akta" class="text-xs text-slate-400">Tidak ada dokumen.</span>
                    </div>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
