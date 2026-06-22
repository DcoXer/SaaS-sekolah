<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import BackButton from '@/Components/BackButton.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, inject } from 'vue';

const addToast = inject('addToast', () => {});

const props = defineProps({
    teacher: { type: Object, required: true },
});

// ── Edit form ─────────────────────────────────────────────────────────────────
const editForm = useForm({
    name:     props.teacher.user?.name ?? '',
    email:    props.teacher.user?.email ?? '',
    password: '',
    type:     props.teacher.type,
    position: props.teacher.position ?? '',
    nip:      props.teacher.nip ?? '',
    gender:   props.teacher.gender,
    phone:    props.teacher.phone ?? '',
});

const submitEdit = () => {
    editForm.put(route('operator.teachers.update', props.teacher.id), {
        onSuccess: () => addToast?.('Data guru berhasil diperbarui.', 'success'),
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const showDelete = ref(false);
const deleteForm  = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.teachers.destroy', props.teacher.id), {
        onSuccess: () => router.visit(route('operator.teachers.index')),
    });
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const initials = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);

const genderConfig = {
    L: { label: 'Laki-laki',  badge: 'bg-sky-50 text-sky-700 ring-sky-200' },
    P: { label: 'Perempuan',  badge: 'bg-pink-50 text-pink-700 ring-pink-200' },
};
const typeConfig = {
    guru_kelas:  { label: 'Guru Kelas',  badge: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
    guru_bidang: { label: 'Guru Bidang', badge: 'bg-violet-50 text-violet-700 ring-violet-200' },
};
const typeOptions = [
    { value: 'guru_kelas',  label: 'Guru Kelas',  desc: 'Kelas 1–3, mengajar semua mapel' },
    { value: 'guru_bidang', label: 'Guru Bidang',  desc: 'Kelas 4–6, mapel spesifik' },
];
const positionOptions = [
    { value: '',                    label: 'Tidak Ada Jabatan Struktural' },
    { value: 'wakamad_kesiswaan',   label: 'Wakamad Kesiswaan' },
    { value: 'wakamad_kurikulum',   label: 'Wakamad Kurikulum' },
];
const positionConfig = {
    wakamad_kesiswaan: { label: 'Wakamad Kesiswaan', badge: 'bg-orange-50 text-orange-700 ring-orange-200' },
    wakamad_kurikulum: { label: 'Wakamad Kurikulum',  badge: 'bg-teal-50 text-teal-700 ring-teal-200' },
};
</script>

<template>
    <AppLayout>
        <Head :title="teacher.user.name" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/operator/teachers" class="transition-[color] duration-150 hover:text-slate-700">Guru</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ teacher.user.name }}</span>
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-5">
            <BackButton href="/operator/teachers" />

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[300px,1fr] lg:items-start">

                <!-- ── Kolom Kiri ──────────────────────────────────────────── -->
                <div class="space-y-4 lg:sticky lg:top-6">

                    <!-- Profile card -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <!-- Banner -->
                        <div class="h-20 bg-gradient-to-br from-primary-400 to-teal-500" />
                        <div class="px-5 pb-5">
                            <div class="-mt-8 mb-3 flex items-end justify-between">
                                <div class="flex size-16 items-center justify-center rounded-full border-4 border-white bg-primary-100 text-xl font-bold text-primary-700 shadow">
                                    {{ initials(teacher.user.name) }}
                                </div>
                                <button
                                    @click="showDelete = true"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition-[background-color,border-color] duration-150 hover:bg-red-50"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Hapus
                                </button>
                            </div>

                            <h2 class="text-base font-bold text-slate-900">{{ teacher.user.name }}</h2>
                            <p class="mt-0.5 text-xs text-slate-400 break-all">{{ teacher.user.email }}</p>

                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                    :class="typeConfig[teacher.type]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                >
                                    {{ typeConfig[teacher.type]?.label ?? teacher.type }}
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                    :class="genderConfig[teacher.gender]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                >
                                    {{ genderConfig[teacher.gender]?.label ?? teacher.gender }}
                                </span>
                                <span v-if="teacher.position"
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                    :class="positionConfig[teacher.position]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                >
                                    {{ positionConfig[teacher.position]?.label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Kepegawaian -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Info Kepegawaian</h3>
                        </div>
                        <dl class="divide-y divide-slate-100">
                            <div class="flex items-start justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-400 shrink-0 w-24">NIP</dt>
                                <dd class="font-mono text-xs font-medium text-slate-700 text-right">{{ teacher.nip || '—' }}</dd>
                            </div>
                            <div class="flex items-start justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-400 shrink-0 w-24">Tipe</dt>
                                <dd class="text-xs font-medium text-slate-700 text-right">{{ typeConfig[teacher.type]?.label ?? teacher.type }}</dd>
                            </div>
                            <div class="flex items-start justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-400 shrink-0 w-24">Jabatan</dt>
                                <dd class="text-xs font-medium text-slate-700 text-right">
                                    {{ teacher.position ? positionConfig[teacher.position]?.label : 'Tidak ada' }}
                                </dd>
                            </div>
                            <div class="flex items-start justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-400 shrink-0 w-24">Telepon</dt>
                                <dd class="text-xs font-medium text-slate-700 text-right">{{ teacher.phone || '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Akun Login -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Akun Login</h3>
                        </div>
                        <dl class="divide-y divide-slate-100">
                            <div class="flex items-start justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-400 shrink-0 w-20">Email</dt>
                                <dd class="text-xs font-medium text-slate-700 text-right break-all">{{ teacher.user.email }}</dd>
                            </div>
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <dt class="text-xs text-slate-400">Status</dt>
                                <dd>
                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700 ring-1 ring-primary-200">
                                        Aktif
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                </div>

                <!-- ── Kolom Kanan: Edit Form ───────────────────────────────── -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-800">Edit Data Guru</h3>
                    </div>

                    <form @submit.prevent="submitEdit" class="space-y-4 p-5">

                        <!-- Nama -->
                        <div>
                            <label for="e-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="e-name"
                                v-model="editForm.name"
                                type="text"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                    editForm.errors.name ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                        </div>

                        <!-- Email + Password -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="e-email" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="e-email"
                                    v-model="editForm.email"
                                    type="email"
                                    autocomplete="off"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        editForm.errors.email ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.email" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.email }}</p>
                            </div>
                            <div>
                                <label for="e-password" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Password Baru
                                </label>
                                <input
                                    id="e-password"
                                    v-model="editForm.password"
                                    type="password"
                                    placeholder="Kosongkan jika tidak diubah"
                                    autocomplete="new-password"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        editForm.errors.password ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.password" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.password }}</p>
                            </div>
                        </div>

                        <!-- NIP + Telepon -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="e-nip" class="mb-1.5 block text-xs font-semibold text-slate-600">NIP</label>
                                <input
                                    id="e-nip"
                                    v-model="editForm.nip"
                                    type="text"
                                    placeholder="Nomor Induk Pegawai"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        editForm.errors.nip ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.nip" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.nip }}</p>
                            </div>
                            <div>
                                <label for="e-phone" class="mb-1.5 block text-xs font-semibold text-slate-600">Telepon</label>
                                <input
                                    id="e-phone"
                                    v-model="editForm.phone"
                                    type="text"
                                    placeholder="08xxxxxxxxxx"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        editForm.errors.phone ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.phone" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.phone }}</p>
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
                                        editForm.type === opt.value
                                            ? 'border-primary-400 bg-primary-50'
                                            : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                                    ]"
                                >
                                    <input type="radio" :value="opt.value" v-model="editForm.type" class="sr-only" />
                                    <span :class="['text-sm font-semibold', editForm.type === opt.value ? 'text-primary-700' : 'text-slate-700']">
                                        {{ opt.label }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ opt.desc }}</span>
                                </label>
                            </div>
                            <p v-if="editForm.errors.type" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.type }}</p>
                        </div>

                        <!-- Jabatan Struktural -->
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Jabatan Struktural</label>
                            <FilterSelect v-model="editForm.position" :options="positionOptions" block :hasError="!!editForm.errors.position" />
                            <p v-if="editForm.errors.position" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.position }}</p>
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
                                        editForm.gender === opt.value
                                            ? 'border-primary-400 bg-primary-50 text-primary-700'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50',
                                    ]"
                                >
                                    <input type="radio" :value="opt.value" v-model="editForm.gender" class="sr-only" />
                                    <span class="text-sm font-medium">{{ opt.label }}</span>
                                </label>
                            </div>
                            <p v-if="editForm.errors.gender" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.gender }}</p>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end border-t border-slate-100 pt-4">
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                            >
                                <svg v-if="editForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>

        <!-- ── Delete Confirm ──────────────────────────────────────────────────── -->
        <Modal :show="showDelete" max-width="sm" @close="showDelete = false">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Guru</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ teacher.user.name }}</span>?
                    Akun login guru ini juga akan ikut terhapus. Tindakan ini tidak bisa dibatalkan.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="showDelete = false" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
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
