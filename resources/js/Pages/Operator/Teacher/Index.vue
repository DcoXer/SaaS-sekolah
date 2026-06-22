<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, inject } from 'vue';

const addToast = inject('addToast', () => {});

const props = defineProps({
    teachers: { type: Array, required: true },
});

// ── Search & Filter ───────────────────────────────────────────────────────────
const search     = ref('');
const filterType = ref('');

const filtered = computed(() => {
    let list = props.teachers;
    if (filterType.value) list = list.filter(t => t.type === filterType.value);
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(t =>
            t.user?.name?.toLowerCase().includes(q) ||
            t.user?.email?.toLowerCase().includes(q) ||
            (t.nip && t.nip.toLowerCase().includes(q))
        );
    }
    return list;
});

// ── Sort ──────────────────────────────────────────────────────────────────────
const sortKey = ref('name');
const sortDir = ref('asc');

const toggleSort = (key) => {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDir.value = 'asc';
    }
    currentPage.value = 1;
};

const sorted = computed(() => {
    const list = [...filtered.value];
    const dir  = sortDir.value === 'asc' ? 1 : -1;
    return list.sort((a, b) => {
        let va = '', vb = '';
        if (sortKey.value === 'name') { va = a.user?.name ?? ''; vb = b.user?.name ?? ''; }
        else if (sortKey.value === 'nip')  { va = a.nip ?? ''; vb = b.nip ?? ''; }
        return va.localeCompare(vb, 'id') * dir;
    });
});

// ── Pagination ────────────────────────────────────────────────────────────────
const PER_PAGE   = 10;
const currentPage = ref(1);

const totalPages = computed(() => Math.ceil(sorted.value.length / PER_PAGE));
const paginated  = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return sorted.value.slice(start, start + PER_PAGE);
});

// Reset to page 1 when filter/search changes
watch([search, filterType], () => { currentPage.value = 1; });

// ── Bulk generate accounts ────────────────────────────────────────────────────
const showBulkGenerate = ref(false);
const placeholderCount = computed(() =>
    props.teachers.filter(t => t.user?.email?.endsWith('@sekolah.local')).length
);

const submitBulkGenerate = () => {
    showBulkGenerate.value = false;

    const form     = document.createElement('form');
    form.method    = 'POST';
    form.action    = route('operator.teachers.bulk-generate-accounts');
    form.style.display = 'none';

    const csrf     = document.createElement('input');
    csrf.type      = 'hidden';
    csrf.name      = '_token';
    csrf.value     = document.head.querySelector('meta[name="csrf-token"]')?.content ?? '';

    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.teachers.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const initials   = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
const genderConfig = {
    L: { label: 'Laki-laki',  badge: 'bg-sky-50 text-sky-700 ring-sky-200' },
    P: { label: 'Perempuan',  badge: 'bg-pink-50 text-pink-700 ring-pink-200' },
};
const typeConfig = {
    guru_kelas:  { label: 'Guru Kelas',  badge: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
    guru_bidang: { label: 'Guru Bidang', badge: 'bg-violet-50 text-violet-700 ring-violet-200' },
};
const positionConfig = {
    wakamad_kesiswaan: { label: 'Wakamad Kesiswaan', badge: 'bg-orange-50 text-orange-700 ring-orange-200' },
    wakamad_kurikulum: { label: 'Wakamad Kurikulum',  badge: 'bg-teal-50 text-teal-700 ring-teal-200' },
};
</script>

<template>
    <AppLayout>
        <Head title="Data Guru" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Guru</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Data Guru</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Kelola data guru beserta akun login mereka.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-if="teachers.length > 0"
                        @click="showBulkGenerate = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700 shadow-sm transition-[background-color,border-color] duration-150 hover:border-amber-300 hover:bg-amber-100"
                    >
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <span class="hidden sm:inline">Generate Akun</span>
                    </button>
                    <Link
                        :href="route('operator.teachers.export.form')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-[background-color,border-color] duration-150 hover:border-slate-300 hover:bg-slate-50"
                    >
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span class="hidden sm:inline">Export</span>
                    </Link>
                    <Link
                        :href="route('operator.teachers.import.form')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-[background-color,border-color] duration-150 hover:border-slate-300 hover:bg-slate-50"
                    >
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 7.5m0 0l4.5 4.5M12 7.5V21" />
                        </svg>
                        <span class="hidden sm:inline">Import</span>
                    </Link>
                    <Link
                        :href="route('operator.teachers.create')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600"
                    >
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah
                    </Link>
                </div>
            </div>

            <!-- Banner: guru dengan email placeholder -->
            <div
                v-if="placeholderCount > 0"
                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <div class="flex items-center gap-3">
                    <svg class="size-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm text-amber-800">
                        <span class="font-semibold">{{ placeholderCount }} guru</span> masih pakai email sementara dari import.
                        Email akan di-fix otomatis saat generate.
                    </p>
                </div>
                <button
                    @click="showBulkGenerate = true"
                    class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-amber-600"
                >
                    Generate & Download
                </button>
            </div>

            <!-- Search & Filter -->
            <div v-if="teachers.length > 0" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama, email, NIP..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-400/20"
                    />
                </div>
                <div class="h-5 w-px bg-slate-200"/>
                <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
                    <button
                        v-for="opt in [{ value: '', label: 'Semua' }, { value: 'guru_kelas', label: 'Guru Kelas' }, { value: 'guru_bidang', label: 'Guru Bidang' }]"
                        :key="opt.value"
                        @click="filterType = opt.value"
                        :class="filterType === opt.value
                            ? 'bg-white text-slate-800 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700'"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150"
                    >
                        {{ opt.label }}
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="teachers.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada data guru</p>
                <p class="mt-1 text-xs text-slate-400">Tambah guru untuk mulai mengatur data mengajar.</p>
                <Link
                    :href="route('operator.teachers.create')"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Guru
                </Link>
            </div>

            <!-- No results -->
            <div
                v-else-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau hapus filter.</p>
                <button @click="search = ''; filterType = ''" class="mt-3 text-xs font-semibold text-primary-600 hover:underline">Reset pencarian</button>
            </div>

            <template v-else>

            <!-- Mobile card list -->
            <div class="sm:hidden space-y-2">
                <div
                    v-for="teacher in paginated"
                    :key="teacher.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="flex items-start justify-between p-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700">
                                {{ initials(teacher.user.name) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ teacher.user.name }}</p>
                                <p class="truncate text-xs text-slate-400">{{ teacher.user.email }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1 ml-2">
                            <Link
                                :href="route('operator.teachers.show', teacher.id)"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                aria-label="Lihat detail guru"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </Link>
                            <button
                                @click="deleteTarget = teacher"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500"
                                aria-label="Hapus guru"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-4 py-2.5">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                            :class="typeConfig[teacher.type]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                        >{{ typeConfig[teacher.type]?.label ?? teacher.type }}</span>
                        <span v-if="teacher.position"
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                            :class="positionConfig[teacher.position]?.badge"
                        >{{ positionConfig[teacher.position]?.label }}</span>
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                            :class="genderConfig[teacher.gender]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                        >{{ genderConfig[teacher.gender]?.label ?? teacher.gender }}</span>
                        <span v-if="teacher.nip" class="text-xs text-slate-400">NIP: {{ teacher.nip }}</span>
                        <span v-if="teacher.phone" class="text-xs text-slate-400">{{ teacher.phone }}</span>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">
                                <button @click="toggleSort('name')" class="inline-flex items-center gap-1 hover:text-slate-800 transition-colors duration-150">
                                    Guru
                                    <span class="flex flex-col leading-none">
                                        <svg class="size-2.5 transition-colors" :class="sortKey === 'name' && sortDir === 'asc' ? 'text-primary-500' : 'text-slate-300'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 0L10 6H0z"/></svg>
                                        <svg class="size-2.5 transition-colors" :class="sortKey === 'name' && sortDir === 'desc' ? 'text-primary-500' : 'text-slate-300'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 6L0 0h10z"/></svg>
                                    </span>
                                </button>
                            </th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tipe</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">
                                <button @click="toggleSort('nip')" class="inline-flex items-center gap-1 hover:text-slate-800 transition-colors duration-150">
                                    NIP
                                    <span class="flex flex-col leading-none">
                                        <svg class="size-2.5 transition-colors" :class="sortKey === 'nip' && sortDir === 'asc' ? 'text-primary-500' : 'text-slate-300'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 0L10 6H0z"/></svg>
                                        <svg class="size-2.5 transition-colors" :class="sortKey === 'nip' && sortDir === 'desc' ? 'text-primary-500' : 'text-slate-300'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 6L0 0h10z"/></svg>
                                    </span>
                                </button>
                            </th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Kelamin</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Telepon</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="teacher in paginated"
                            :key="teacher.id"
                            class="transition-[background-color] duration-150 hover:bg-slate-50"
                        >
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700">
                                        {{ initials(teacher.user.name) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ teacher.user.name }}</p>
                                        <p class="text-xs text-slate-400">{{ teacher.user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="typeConfig[teacher.type]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'">
                                        {{ typeConfig[teacher.type]?.label ?? teacher.type }}
                                    </span>
                                    <span v-if="teacher.position" class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="positionConfig[teacher.position]?.badge">
                                        {{ positionConfig[teacher.position]?.label }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5"><span class="tabular-nums text-sm text-slate-600">{{ teacher.nip ?? '—' }}</span></td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="genderConfig[teacher.gender]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'">
                                    {{ genderConfig[teacher.gender]?.label ?? teacher.gender }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5"><span class="tabular-nums text-sm text-slate-600">{{ teacher.phone ?? '—' }}</span></td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('operator.teachers.show', teacher.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700" aria-label="Lihat detail guru">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <button @click="deleteTarget = teacher" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500" aria-label="Hapus guru">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="totalPages > 1"
                :current-page="currentPage"
                :total-pages="totalPages"
                :total="filtered.length"
                :per-page="PER_PAGE"
                label="guru"
                @update:current-page="currentPage = $event"
            />

            </template>

        </div>

        <!-- ── Bulk Generate Confirm ──────────────────────────────────────────── -->
        <Modal :show="showBulkGenerate" max-width="sm" @close="showBulkGenerate = false">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-amber-100">
                    <svg class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Generate & Download Akun Guru</h3>
                <p class="mt-1.5 text-pretty text-sm text-slate-500">
                    Password semua <span class="font-semibold text-slate-700">{{ teachers.length }} guru</span> akan di-reset dan file CSV berisi kredensial baru akan otomatis terunduh.
                </p>
                <ul class="mt-3 space-y-1 text-xs text-slate-500">
                    <li class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Password baru di-generate acak untuk semua guru
                    </li>
                    <li v-if="placeholderCount > 0" class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ placeholderCount }} email sementara (<span class="font-mono">@sekolah.local</span>) akan di-fix ke <span class="font-mono">@guru.sekolah.id</span>
                    </li>
                    <li class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Hasil langsung terunduh sebagai <span class="font-mono">akun_guru.csv</span>
                    </li>
                </ul>
                <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700">
                    Guru yang sedang login akan perlu login ulang dengan password baru.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="showBulkGenerate = false"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                >
                    Batal
                </button>
                <button
                    @click="submitBulkGenerate"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-amber-600"
                >
                    Ya, Generate & Download
                </button>
            </div>
        </Modal>

        <!-- ── Delete Confirm ──────────────────────────────────────────────────── -->
        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Guru</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ deleteTarget?.user?.name }}</span>?
                    Akun login guru ini juga akan ikut terhapus. Tindakan ini tidak bisa dibatalkan.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="deleteTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                >
                    Batal
                </button>
                <button
                    @click="submitDelete"
                    :disabled="deleteForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60"
                >
                    <svg v-if="deleteForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
