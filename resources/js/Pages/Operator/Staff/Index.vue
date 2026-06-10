<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    staff: { type: Array, required: true },
});

const search = ref('');

const filtered = computed(() => {
    const q = search.value.toLowerCase();
    if (!q) return props.staff;
    return props.staff.filter(s =>
        s.name.toLowerCase().includes(q) ||
        s.email.toLowerCase().includes(q)
    );
});

watch(search, () => {});

// ── Role helpers ──────────────────────────────────────────────────────────────
const roleLabel = {
    kamad:       'Kepala Madrasah',
    tu_keuangan: 'TU Keuangan',
};

const roleBadge = {
    kamad:       'bg-primary-50 text-primary-700 ring-primary-200',
    tu_keuangan: 'bg-sky-50 text-sky-700 ring-sky-200',
};

const roleOptions = [
    { value: 'kamad',       label: 'Kepala Madrasah' },
    { value: 'tu_keuangan', label: 'TU Keuangan' },
];

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    : '—';

// ── Password generation ───────────────────────────────────────────────────────
const PASS_CHARS = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
const makePassword = () => {
    let pwd = '';
    for (let i = 0; i < 10; i++) pwd += PASS_CHARS[Math.floor(Math.random() * PASS_CHARS.length)];
    return pwd;
};
const slugStr = (str) => str.toLowerCase().replace(/\s+/g, '.').replace(/[^a-z0-9.]/g, '');

// ── Create modal ──────────────────────────────────────────────────────────────
const showCreate = ref(false);
const showCreatePass = ref(false);
const createForm = useForm({
    name:     '',
    email:    '',
    password: '',
    role:     '',
});

const generateCreateEmail = () => {
    const base = slugStr(createForm.name || 'staff');
    const suffix = createForm.role ? `.${createForm.role.replace('_', '')}` : '';
    createForm.email = `${base}${suffix}@sekolah.id`;
};

const generateCreatePassword = () => {
    createForm.password = makePassword();
    showCreatePass.value = true;
};

const submitCreate = () => {
    createForm.post(route('operator.staff.store'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
            showCreatePass.value = false;
        },
    });
};

const closeCreate = () => {
    showCreate.value = false;
    createForm.reset();
    createForm.clearErrors();
    showCreatePass.value = false;
};

// ── Edit modal ────────────────────────────────────────────────────────────────
const editTarget = ref(null);
const showEditPass = ref(false);
const editForm = useForm({
    name:     '',
    email:    '',
    password: '',
});

const openEdit = (staff) => {
    editTarget.value = staff;
    editForm.name     = staff.name;
    editForm.email    = staff.email;
    editForm.password = '';
    showEditPass.value = false;
    editForm.clearErrors();
};

const generateEditPassword = () => {
    editForm.password = makePassword();
    showEditPass.value = true;
};

const submitEdit = () => {
    editForm.put(route('operator.staff.update', editTarget.value.id), {
        onSuccess: () => {
            editTarget.value = null;
        },
    });
};

const closeEdit = () => {
    editTarget.value = null;
    editForm.reset();
    editForm.clearErrors();
};

// ── Delete modal ──────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.staff.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Kelola Staff" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Kelola Staff</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Kelola Staff</h2>
                    <p class="text-pretty text-sm text-slate-500">Akun Kepala Madrasah dan TU Keuangan.</p>
                </div>
                <button
                    @click="showCreate = true"
                    class="inline-flex w-fit items-center gap-1.5 rounded-lg bg-primary-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600"
                >
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Staff
                </button>
            </div>

            <!-- Filter bar -->
            <div class="flex items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama atau email..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-400/20"
                    />
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="staff.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada akun staff</p>
                <p class="mt-1 text-xs text-slate-400">Klik "Tambah Staff" untuk membuat akun baru.</p>
                <button
                    @click="showCreate = true"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Staff
                </button>
            </div>

            <!-- No results -->
            <div
                v-else-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci pencarian.</p>
                <button @click="search = ''" class="mt-3 text-xs font-semibold text-primary-600 hover:underline">Reset pencarian</button>
            </div>

            <template v-else>

                <!-- Mobile card list -->
                <div class="sm:hidden space-y-2">
                    <div
                        v-for="s in filtered"
                        :key="s.id"
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="flex items-start justify-between p-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                    :class="s.role === 'kamad' ? 'bg-primary-100 text-primary-700' : 'bg-sky-100 text-sky-700'"
                                >
                                    {{ s.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ s.name }}</p>
                                    <p class="truncate text-xs text-slate-400">{{ s.email }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-1 ml-2">
                                <button
                                    @click="openEdit(s)"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                    aria-label="Edit staff"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteTarget = s"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500"
                                    aria-label="Hapus staff"
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
                                :class="roleBadge[s.role] ?? 'bg-slate-50 text-slate-600 ring-slate-200'"
                            >
                                {{ roleLabel[s.role] ?? s.role }}
                            </span>
                            <span class="text-xs text-slate-400">Dibuat {{ formatDate(s.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Staff</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Role</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Dibuat</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="s in filtered" :key="s.id" class="transition-[background-color] duration-150 hover:bg-slate-50">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                            :class="s.role === 'kamad' ? 'bg-primary-100 text-primary-700' : 'bg-sky-100 text-sky-700'"
                                        >
                                            {{ s.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ s.name }}</p>
                                            <p class="text-xs text-slate-400">{{ s.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                        :class="roleBadge[s.role] ?? 'bg-slate-50 text-slate-600 ring-slate-200'"
                                    >
                                        {{ roleLabel[s.role] ?? s.role }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-500">{{ formatDate(s.created_at) }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            @click="openEdit(s)"
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                                            aria-label="Edit staff"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteTarget = s"
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500"
                                            aria-label="Hapus staff"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </template>

        </div>

        <!-- ── Create Modal ──────────────────────────────────────────────────── -->
        <Modal :show="showCreate" max-width="sm" @close="closeCreate">
            <div class="px-6 py-5">
                <h3 class="text-base font-bold text-slate-900">Tambah Akun Staff</h3>
                <p class="mt-1 text-sm text-slate-500">Buat akun untuk Kepala Madrasah atau TU Keuangan.</p>
            </div>
            <form @submit.prevent="submitCreate" class="space-y-4 px-6 pb-2">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input
                        v-model="createForm.name"
                        type="text"
                        placeholder="Nama lengkap"
                        :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20', createForm.errors.name ? 'border-red-400' : 'border-slate-200']"
                    />
                    <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-500">{{ createForm.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Role <span class="text-red-500">*</span></label>
                    <FilterSelect
                        v-model="createForm.role"
                        :options="roleOptions"
                        :has-error="!!createForm.errors.role"
                        block
                    />
                    <p v-if="createForm.errors.role" class="mt-1 text-xs text-red-500">{{ createForm.errors.role }}</p>
                </div>
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-600">Email <span class="text-red-500">*</span></label>
                        <button type="button" @click="generateCreateEmail" class="text-xs font-medium text-amber-600 hover:text-amber-700 underline cursor-pointer">⚡ Generate</button>
                    </div>
                    <input
                        v-model="createForm.email"
                        type="email"
                        placeholder="email@sekolah.id"
                        autocomplete="off"
                        :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20', createForm.errors.email ? 'border-red-400' : 'border-slate-200']"
                    />
                    <p v-if="createForm.errors.email" class="mt-1 text-xs text-red-500">{{ createForm.errors.email }}</p>
                </div>
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-600">Password <span class="text-red-500">*</span></label>
                        <button type="button" @click="generateCreatePassword" class="text-xs font-medium text-amber-600 hover:text-amber-700 underline cursor-pointer">⚡ Generate</button>
                    </div>
                    <input
                        v-model="createForm.password"
                        :type="showCreatePass ? 'text' : 'password'"
                        placeholder="Min. 8 karakter"
                        autocomplete="new-password"
                        :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20', createForm.errors.password ? 'border-red-400' : 'border-slate-200']"
                    />
                    <p v-if="createForm.errors.password" class="mt-1 text-xs text-red-500">{{ createForm.errors.password }}</p>
                </div>
            </form>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="closeCreate" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">Batal</button>
                <button
                    @click="submitCreate"
                    :disabled="createForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                >
                    <svg v-if="createForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </Modal>

        <!-- ── Edit Modal ────────────────────────────────────────────────────── -->
        <Modal :show="!!editTarget" max-width="sm" @close="closeEdit">
            <div class="px-6 py-5">
                <h3 class="text-base font-bold text-slate-900">Edit Akun Staff</h3>
                <p class="mt-1 text-sm text-slate-500">Perbarui data akun <span class="font-semibold text-slate-700">{{ editTarget?.name }}</span>.</p>
            </div>
            <form @submit.prevent="submitEdit" class="space-y-4 px-6 pb-2">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input
                        v-model="editForm.name"
                        type="text"
                        placeholder="Nama lengkap"
                        :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20', editForm.errors.name ? 'border-red-400' : 'border-slate-200']"
                    />
                    <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-500">{{ editForm.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Email <span class="text-red-500">*</span></label>
                    <input
                        v-model="editForm.email"
                        type="email"
                        placeholder="email@sekolah.id"
                        autocomplete="off"
                        :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20', editForm.errors.email ? 'border-red-400' : 'border-slate-200']"
                    />
                    <p v-if="editForm.errors.email" class="mt-1 text-xs text-red-500">{{ editForm.errors.email }}</p>
                </div>
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-600">Password Baru <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <button type="button" @click="generateEditPassword" class="text-xs font-medium text-amber-600 hover:text-amber-700 underline cursor-pointer">⚡ Generate</button>
                    </div>
                    <input
                        v-model="editForm.password"
                        :type="showEditPass ? 'text' : 'password'"
                        placeholder="Kosongkan jika tidak diubah"
                        autocomplete="new-password"
                        :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20', editForm.errors.password ? 'border-red-400' : 'border-slate-200']"
                    />
                    <p v-if="editForm.errors.password" class="mt-1 text-xs text-red-500">{{ editForm.errors.password }}</p>
                </div>
            </form>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="closeEdit" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">Batal</button>
                <button
                    @click="submitEdit"
                    :disabled="editForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                >
                    <svg v-if="editForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
            </div>
        </Modal>

        <!-- ── Delete Confirm ────────────────────────────────────────────────── -->
        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Akun Staff</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus akun <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Tindakan ini tidak dapat dibatalkan.
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
