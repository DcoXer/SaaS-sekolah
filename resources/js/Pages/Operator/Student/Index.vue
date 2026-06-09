<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, inject } from 'vue';

const addToast = inject('addToast');

const props = defineProps({
    students: { type: Array, required: true },
});

// ── Bulk generate accounts ────────────────────────────────────────────────────
const showBulkGenerate    = ref(false);
const bulkGenerating      = ref(false);
const withoutAccountCount = computed(() => props.students.filter(s => !s.user).length);

const submitBulkGenerate = async () => {
    bulkGenerating.value = true;
    try {
        const res = await fetch(route('operator.students.bulk-generate-accounts'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        });

        const contentType = res.headers.get('Content-Type') ?? '';

        if (contentType.includes('application/json')) {
            showBulkGenerate.value = false;
            addToast?.('Semua siswa sudah memiliki akun.', 'success');
        } else {
            const blob = await res.blob();
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = 'akun_wali_murid.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showBulkGenerate.value = false;
            router.reload({ only: ['students'] });
        }
    } catch {
        addToast?.('Terjadi kesalahan. Silakan coba lagi.', 'error');
    } finally {
        bulkGenerating.value = false;
    }
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.students.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Search & Filter ───────────────────────────────────────────────────────────
const search       = ref('');
const filterGrade  = ref('');
const filterStatus = ref('');

const filtered = computed(() => {
    let list = props.students;
    if (filterGrade.value)  list = list.filter(s => String(s.grade) === filterGrade.value);
    if (filterStatus.value) list = list.filter(s => s.status === filterStatus.value);
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(s =>
            s.name.toLowerCase().includes(q) ||
            (s.nisn ?? '').toLowerCase().includes(q) ||
            (s.nis ?? '').toLowerCase().includes(q) ||
            (s.user?.name && s.user.name.toLowerCase().includes(q))
        );
    }
    return list;
});

const PER_PAGE    = 15;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch([search, filterGrade, filterStatus], () => { currentPage.value = 1; });

// ── Select Options ────────────────────────────────────────────────────────────
const gradeOptions = [1,2,3,4,5,6].map(g => ({ value: g, label: `Kelas ${g}` }));

// ── Helpers ───────────────────────────────────────────────────────────────────
const genderConfig = {
    L: { label: 'L', badge: 'bg-sky-50 text-sky-700 ring-sky-200' },
    P: { label: 'P', badge: 'bg-pink-50 text-pink-700 ring-pink-200' },
};

const statusConfig = {
    active: { label: 'Aktif',   badge: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
    alumni: { label: 'Alumni',  badge: 'bg-sky-50 text-sky-700 ring-sky-200' },
    mutasi: { label: 'Mutasi',  badge: 'bg-amber-50 text-amber-700 ring-amber-200' },
};

const initials = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
</script>

<template>
    <AppLayout>
        <Head title="Data Siswa" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Siswa</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Data Siswa</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Kelola data siswa beserta akun wali murid. Penempatan kelas diatur dari halaman detail.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <Link
                        :href="route('operator.students.export.form')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-[background-color,border-color] duration-150 hover:border-slate-300 hover:bg-slate-50"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export
                    </Link>
                    <Link
                        :href="route('operator.students.import.form')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-[background-color,border-color] duration-150 hover:border-slate-300 hover:bg-slate-50"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 7.5m0 0l4.5 4.5M12 7.5V21" />
                        </svg>
                        Import
                    </Link>
                    <Link
                        :href="route('operator.students.create')"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah
                    </Link>
                </div>
            </div>

            <!-- Banner: siswa tanpa akun -->
            <div
                v-if="withoutAccountCount > 0"
                class="flex items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <div class="flex items-center gap-3">
                    <svg class="size-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm text-amber-800">
                        <span class="font-semibold">{{ withoutAccountCount }} siswa</span> belum punya akun wali murid.
                    </p>
                </div>
                <button
                    @click="showBulkGenerate = true"
                    class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-amber-600"
                >
                    Generate Semua Akun
                </button>
            </div>

            <!-- Search & Filter -->
            <div v-if="students.length > 0" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama, NISN/NIS, wali murid..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
                    />
                </div>
                <FilterSelect
                    v-model="filterGrade"
                    :options="[
                        { value: '', label: 'Semua Kelas' },
                        { value: '1', label: 'Kelas 1' },
                        { value: '2', label: 'Kelas 2' },
                        { value: '3', label: 'Kelas 3' },
                        { value: '4', label: 'Kelas 4' },
                        { value: '5', label: 'Kelas 5' },
                        { value: '6', label: 'Kelas 6' },
                    ]"
                />
                <FilterSelect
                    v-model="filterStatus"
                    :options="[
                        { value: '', label: 'Semua Status' },
                        { value: 'active', label: 'Aktif' },
                        { value: 'alumni', label: 'Alumni' },
                        { value: 'mutasi', label: 'Mutasi' },
                    ]"
                />
            </div>

            <!-- Empty state -->
            <div
                v-if="students.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada data siswa</p>
                <p class="mt-1 text-xs text-slate-400">Tambah siswa untuk mulai mengelola kelas dan tagihan.</p>
                <Link
                    :href="route('operator.students.create')"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Siswa
                </Link>
            </div>

            <!-- No results -->
            <div
                v-else-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau hapus filter.</p>
                <button @click="search = ''; filterGrade = ''; filterStatus = ''" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
            </div>

            <template v-else>

            <!-- Mobile card list -->
            <div class="sm:hidden space-y-2">
                <div
                    v-for="student in paginated"
                    :key="student.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="flex items-start justify-between p-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-semibold text-sky-700">
                                {{ initials(student.name) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ student.name }}</p>
                                <p class="tabular-nums text-xs text-slate-400">
                                    NISN {{ student.nisn ?? '—' }}<span v-if="student.nis"> • NIS {{ student.nis }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1 ml-2">
                            <Link :href="route('operator.students.show', student.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Lihat detail siswa">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </Link>
                            <button @click="deleteTarget = student" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Hapus siswa">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-4 py-2.5">
                        <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">Kelas {{ student.grade }}</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1" :class="genderConfig[student.gender]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'">{{ genderConfig[student.gender]?.label ?? student.gender }}</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1" :class="statusConfig[student.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'">{{ statusConfig[student.status]?.label ?? student.status }}</span>
                        <span v-if="student.user" class="text-xs text-slate-400">Wali: {{ student.user.name }}</span>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Siswa</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">NISN</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tingkat</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Kelamin</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Wali Murid</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Status</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="student in paginated" :key="student.id" class="transition-[background-color] duration-150 hover:bg-slate-50">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-semibold text-sky-700">{{ initials(student.name) }}</div>
                                    <span class="text-sm font-semibold text-slate-800">{{ student.name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="tabular-nums text-sm text-slate-700">{{ student.nisn ?? 'â€”' }}</div>
                                <div v-if="student.nis" class="tabular-nums text-xs text-slate-400">NIS {{ student.nis }}</div>
                            </td>
                            <td class="px-5 py-3.5"><span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">Kelas {{ student.grade }}</span></td>
                            <td class="px-5 py-3.5"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1" :class="genderConfig[student.gender]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'">{{ genderConfig[student.gender]?.label ?? student.gender }}</span></td>
                            <td class="px-5 py-3.5">
                                <span v-if="student.user" class="text-sm text-slate-700">{{ student.user.name }}</span>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>
                            <td class="px-5 py-3.5"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="statusConfig[student.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'">{{ statusConfig[student.status]?.label ?? student.status }}</span></td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('operator.students.show', student.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700" aria-label="Lihat detail siswa">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <button @click="deleteTarget = student" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500" aria-label="Hapus siswa">
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
                label="siswa"
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
                <h3 class="text-balance text-base font-bold text-slate-900">Generate Akun Wali Murid</h3>
                <p class="mt-1.5 text-pretty text-sm text-slate-500">
                    Sistem akan otomatis membuat akun untuk
                    <span class="font-semibold text-slate-700">{{ withoutAccountCount }} siswa</span>
                    yang belum punya akun wali murid.
                </p>
                <ul class="mt-3 space-y-1 text-xs text-slate-500">
                    <li class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Email: dari NISN/NIS siswa + <span class="font-mono">@siswa.sekolah.id</span>
                    </li>
                    <li class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Password: di-generate acak (bisa diubah di detail siswa)
                    </li>
                    <li class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Siswa yang sudah punya akun tidak akan terpengaruh
                    </li>
                </ul>
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
                    :disabled="bulkGenerating"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-amber-600 disabled:opacity-60"
                >
                    <svg v-if="bulkGenerating" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ bulkGenerating ? 'Memproses...' : 'Ya, Generate & Download' }}
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
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Siswa</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Akun wali murid dan seluruh data terkait juga akan ikut terhapus.
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
