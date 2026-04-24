<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

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
            t.user.name.toLowerCase().includes(q) ||
            t.user.email.toLowerCase().includes(q) ||
            (t.nip && t.nip.toLowerCase().includes(q))
        );
    }
    return list;
});

// ── Pagination ────────────────────────────────────────────────────────────────
const PER_PAGE   = 10;
const currentPage = ref(1);

const totalPages = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated  = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});

// Reset to page 1 when filter/search changes
watch([search, filterType], () => { currentPage.value = 1; });

// ── Create ────────────────────────────────────────────────────────────────────
const showCreate = ref(false);

const createForm = useForm({
    name:     '',
    email:    '',
    password: '',
    type:     '',
    nip:      '',
    gender:   '',
    phone:    '',
});

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
};

const submitCreate = () => {
    createForm.post(route('operator.teachers.store'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
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
const typeOptions = [
    { value: 'guru_kelas',  label: 'Guru Kelas',  desc: 'Kelas 1–3, mengajar semua mapel' },
    { value: 'guru_bidang', label: 'Guru Bidang',  desc: 'Kelas 4–6, mapel spesifik' },
];
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
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Data Guru</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Kelola data guru beserta akun login mereka.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </button>
            </div>

            <!-- Search & Filter -->
            <div v-if="teachers.length > 0" class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 min-w-48">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama, email, NIP..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                    />
                </div>
                <div class="flex items-center gap-1.5">
                    <button
                        v-for="opt in [{ value: '', label: 'Semua' }, { value: 'guru_kelas', label: 'Guru Kelas' }, { value: 'guru_bidang', label: 'Guru Bidang' }]"
                        :key="opt.value"
                        @click="filterType = opt.value"
                        :class="filterType === opt.value
                            ? 'bg-emerald-500 text-white'
                            : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                        class="rounded-lg px-3 py-2 text-xs font-semibold transition-[background-color,color] duration-150"
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
                <button
                    @click="openCreate"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Guru
                </button>
            </div>

            <!-- No results -->
            <div
                v-else-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau hapus filter.</p>
                <button @click="search = ''; filterType = ''" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
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
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700">
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
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Guru</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tipe</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">NIP</th>
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
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700">
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

        <!-- ── Create Modal ────────────────────────────────────────────────────── -->
        <Modal :show="showCreate" max-width="md" @close="showCreate = false">
            <form @submit.prevent="submitCreate">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-balance text-base font-bold text-slate-900">Tambah Guru</h3>
                    <button
                        type="button"
                        aria-label="Tutup modal"
                        @click="showCreate = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">

                    <!-- Nama -->
                    <div>
                        <label for="c-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="c-name"
                            v-model="createForm.name"
                            type="text"
                            placeholder="Nama lengkap guru"
                            autocomplete="off"
                            :class="[
                                'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                createForm.errors.name ? 'border-red-400' : 'border-slate-200',
                            ]"
                        />
                        <p v-if="createForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.name }}</p>
                    </div>

                    <!-- Email + Password -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="c-email" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="c-email"
                                v-model="createForm.email"
                                type="email"
                                placeholder="email@sekolah.ac.id"
                                autocomplete="off"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    createForm.errors.email ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="createForm.errors.email" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.email }}</p>
                        </div>
                        <div>
                            <label for="c-password" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="c-password"
                                v-model="createForm.password"
                                type="password"
                                placeholder="Min. 8 karakter"
                                autocomplete="new-password"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    createForm.errors.password ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="createForm.errors.password" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.password }}</p>
                        </div>
                    </div>

                    <!-- Tipe Guru -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Tipe Guru <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label
                                v-for="opt in typeOptions"
                                :key="opt.value"
                                :class="[
                                    'flex cursor-pointer flex-col gap-0.5 rounded-lg border px-3.5 py-2.5 transition-[border-color,background-color] duration-150',
                                    createForm.type === opt.value
                                        ? 'border-emerald-400 bg-emerald-50'
                                        : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                                ]"
                            >
                                <input type="radio" :value="opt.value" v-model="createForm.type" class="sr-only" />
                                <span :class="['text-sm font-semibold', createForm.type === opt.value ? 'text-emerald-700' : 'text-slate-700']">
                                    {{ opt.label }}
                                </span>
                                <span class="text-xs text-slate-400">{{ opt.desc }}</span>
                            </label>
                        </div>
                        <p v-if="createForm.errors.type" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.type }}</p>
                    </div>

                    <!-- NIP + Telepon -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="c-nip" class="mb-1.5 block text-xs font-semibold text-slate-600">NIP</label>
                            <input
                                id="c-nip"
                                v-model="createForm.nip"
                                type="text"
                                placeholder="Nomor Induk Pegawai"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    createForm.errors.nip ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="createForm.errors.nip" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.nip }}</p>
                        </div>
                        <div>
                            <label for="c-phone" class="mb-1.5 block text-xs font-semibold text-slate-600">Telepon</label>
                            <input
                                id="c-phone"
                                v-model="createForm.phone"
                                type="text"
                                placeholder="08xxxxxxxxxx"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    createForm.errors.phone ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="createForm.errors.phone" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.phone }}</p>
                        </div>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3">
                            <label
                                v-for="opt in [{ value: 'L', label: 'Laki-laki' }, { value: 'P', label: 'Perempuan' }]"
                                :key="opt.value"
                                :class="[
                                    'flex flex-1 cursor-pointer items-center gap-2.5 rounded-lg border px-3.5 py-2.5 text-sm transition-[border-color,background-color] duration-150',
                                    createForm.gender === opt.value
                                        ? 'border-emerald-400 bg-emerald-50 text-emerald-700'
                                        : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50',
                                ]"
                            >
                                <input
                                    type="radio"
                                    :value="opt.value"
                                    v-model="createForm.gender"
                                    class="sr-only"
                                />
                                <span class="text-sm font-medium">{{ opt.label }}</span>
                            </label>
                        </div>
                        <p v-if="createForm.errors.gender" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.gender }}</p>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button
                        type="button"
                        @click="showCreate = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                    >
                        <svg v-if="createForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
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
