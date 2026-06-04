<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import BackButton from '@/Components/BackButton.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, inject } from 'vue';

const addToast = inject('addToast');

const props = defineProps({
    student:       { type: Object, required: true },
    academicYears: { type: Array,  required: true },
    classrooms:    { type: Array,  required: true },
});

// ── Edit form ─────────────────────────────────────────────────────────────────
const editForm = useForm({
    nisn:          props.student.nisn,
    nik:           props.student.nik          ?? '',
    nis:           props.student.nis,
    name:          props.student.name,
    gender:        props.student.gender,
    grade:         props.student.grade,
    birth_place:   props.student.birth_place  ?? '',
    birth_date:    props.student.birth_date   ?? '',
    address:       props.student.address      ?? '',
    father_name:   props.student.father_name  ?? '',
    mother_name:   props.student.mother_name  ?? '',
    guardian_name: props.student.guardian_name ?? '',
    parent_name:   props.student.user?.name   ?? '',
    password:      '',
});

const submitEdit = () => {
    editForm.put(route('operator.students.update', props.student.id), {
        onSuccess: () => addToast?.('Data siswa berhasil diperbarui.', 'success'),
    });
};

// ── Assign classroom ──────────────────────────────────────────────────────────
// ── Delete ────────────────────────────────────────────────────────────────────
const showDelete = ref(false);
const deleteForm  = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.students.destroy', props.student.id), {
        onSuccess: () => router.visit(route('operator.students.index')),
    });
};

const gradeOptions = [
    { value: 1, label: 'Kelas 1' },
    { value: 2, label: 'Kelas 2' },
    { value: 3, label: 'Kelas 3' },
    { value: 4, label: 'Kelas 4' },
    { value: 5, label: 'Kelas 5' },
    { value: 6, label: 'Kelas 6' },
];

// ── Helpers ───────────────────────────────────────────────────────────────────
const initials = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);

const genderConfig = {
    L: { label: 'Laki-laki', badge: 'bg-sky-50 text-sky-700 ring-sky-200' },
    P: { label: 'Perempuan', badge: 'bg-pink-50 text-pink-700 ring-pink-200' },
};

const statusConfig = {
    active: { label: 'Aktif',   badge: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
    alumni: { label: 'Alumni',  badge: 'bg-sky-50 text-sky-700 ring-sky-200' },
    mutasi: { label: 'Mutasi',  badge: 'bg-amber-50 text-amber-700 ring-amber-200' },
};

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
    : '—';
</script>

<template>
    <AppLayout>
        <Head :title="student.name" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/operator/students" class="transition-[color] duration-150 hover:text-slate-700">Siswa</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ student.name }}</span>
            </div>
        </template>

        <div class="mx-auto max-w-2xl space-y-5">
            <BackButton href="/operator/students" />

            <!-- Header card -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 items-center justify-center rounded-full bg-sky-100 text-lg font-bold text-sky-700">
                        {{ initials(student.name) }}
                    </div>
                    <div>
                        <h2 class="text-balance text-base font-bold text-slate-900">{{ student.name }}</h2>
                        <p class="tabular-nums text-sm text-slate-500">
                            NISN: {{ student.nisn ?? 'â€”' }}<span v-if="student.nis"> â€¢ NIS: {{ student.nis }}</span>
                        </p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                :class="genderConfig[student.gender]?.badge"
                            >
                                {{ genderConfig[student.gender]?.label }}
                            </span>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                :class="statusConfig[student.status]?.badge"
                            >
                                {{ statusConfig[student.status]?.label }}
                            </span>
                            <span v-if="student.birth_date" class="text-xs text-slate-400">
                                {{ formatDate(student.birth_date) }}
                            </span>
                        </div>
                    </div>
                </div>
                <button
                    @click="showDelete = true"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition-[background-color,border-color] duration-150 hover:bg-red-50"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Hapus
                </button>
            </div>

            <!-- Info card: data lengkap -->
            <div v-if="student.father_name || student.mother_name || student.guardian_name || student.nik || student.birth_place"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Data Keluarga</h3>
                </div>
                <dl class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                    <div v-if="student.father_name" class="flex flex-col px-5 py-3">
                        <dt class="text-xs text-slate-400">Ayah Kandung</dt>
                        <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ student.father_name }}</dd>
                    </div>
                    <div v-if="student.mother_name" class="flex flex-col px-5 py-3">
                        <dt class="text-xs text-slate-400">Ibu Kandung</dt>
                        <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ student.mother_name }}</dd>
                    </div>
                    <div v-if="student.guardian_name" class="flex flex-col px-5 py-3">
                        <dt class="text-xs text-slate-400">Wali</dt>
                        <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ student.guardian_name }}</dd>
                    </div>
                    <div v-if="student.nik" class="flex flex-col px-5 py-3">
                        <dt class="text-xs text-slate-400">NIK</dt>
                        <dd class="mt-0.5 font-mono text-sm font-medium text-slate-800">{{ student.nik }}</dd>
                    </div>
                    <div v-if="student.birth_place" class="flex flex-col px-5 py-3">
                        <dt class="text-xs text-slate-400">Tempat Lahir</dt>
                        <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ student.birth_place }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Edit form -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Edit Data Siswa</h3>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-5 p-5">

                    <!-- Data Siswa -->
                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Data Siswa</p>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="e-nisn" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    NISN <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="e-nisn"
                                    v-model="editForm.nisn"
                                    type="text"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                        editForm.errors.nisn ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.nisn" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.nisn }}</p>
                            </div>
                            <div>
                                <label for="e-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="e-name"
                                    v-model="editForm.name"
                                    type="text"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                        editForm.errors.name ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                            </div>
                        </div>

                        <div>
                            <label for="e-nis" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                NIS <span class="text-slate-400">(opsional)</span>
                            </label>
                            <input
                                id="e-nis"
                                v-model="editForm.nis"
                                type="text"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    editForm.errors.nis ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="editForm.errors.nis" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.nis }}</p>
                        </div>

                        <!-- Grade + Tanggal Lahir -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="e-grade" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Tingkat Kelas <span class="text-red-500">*</span>
                                </label>
                                <FilterSelect
                                    v-model="editForm.grade"
                                    :options="gradeOptions"
                                    :has-error="!!editForm.errors.grade"
                                    block
                                />
                                <p v-if="editForm.errors.grade" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.grade }}</p>
                            </div>
                            <div>
                                <label for="e-birth" class="mb-1.5 block text-xs font-semibold text-slate-600">Tanggal Lahir</label>
                                <input
                                    id="e-birth"
                                    v-model="editForm.birth_date"
                                    type="date"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                        editForm.errors.birth_date ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.birth_date" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.birth_date }}</p>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <label
                                    v-for="opt in [{ value: 'L', label: 'Laki-laki' }, { value: 'P', label: 'Perempuan' }]"
                                    :key="opt.value"
                                    :class="[
                                        'flex flex-1 cursor-pointer items-center justify-center rounded-lg border px-3 py-2.5 text-xs font-medium transition-[border-color,background-color] duration-150',
                                        editForm.gender === opt.value
                                            ? 'border-emerald-400 bg-emerald-50 text-emerald-700'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50',
                                    ]"
                                >
                                    <input type="radio" :value="opt.value" v-model="editForm.gender" class="sr-only" />
                                    {{ opt.label }}
                                </label>
                            </div>
                            <p v-if="editForm.errors.gender" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.gender }}</p>
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label for="e-address" class="mb-1.5 block text-xs font-semibold text-slate-600">Alamat</label>
                            <textarea
                                id="e-address"
                                v-model="editForm.address"
                                rows="2"
                                :class="[
                                    'w-full resize-none rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    editForm.errors.address ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="editForm.errors.address" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.address }}</p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-100" />

                    <!-- Data Keluarga -->
                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Data Keluarga</p>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="e-father" class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Ayah Kandung</label>
                                <input
                                    id="e-father"
                                    v-model="editForm.father_name"
                                    type="text"
                                    placeholder="Nama lengkap ayah"
                                    :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.father_name ? 'border-red-400' : 'border-slate-200']"
                                />
                            </div>
                            <div>
                                <label for="e-mother" class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Ibu Kandung</label>
                                <input
                                    id="e-mother"
                                    v-model="editForm.mother_name"
                                    type="text"
                                    placeholder="Nama lengkap ibu"
                                    :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.mother_name ? 'border-red-400' : 'border-slate-200']"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="e-guardian" class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Wali</label>
                            <input
                                id="e-guardian"
                                v-model="editForm.guardian_name"
                                type="text"
                                placeholder="Nama wali (kosongkan jika sama dengan ayah/ibu)"
                                :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.guardian_name ? 'border-red-400' : 'border-slate-200']"
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="e-nik" class="mb-1.5 block text-xs font-semibold text-slate-600">NIK</label>
                                <input
                                    id="e-nik"
                                    v-model="editForm.nik"
                                    type="text"
                                    placeholder="16 digit NIK"
                                    :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.nik ? 'border-red-400' : 'border-slate-200']"
                                />
                                <p v-if="editForm.errors.nik" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.nik }}</p>
                            </div>
                            <div>
                                <label for="e-birthplace" class="mb-1.5 block text-xs font-semibold text-slate-600">Tempat Lahir</label>
                                <input
                                    id="e-birthplace"
                                    v-model="editForm.birth_place"
                                    type="text"
                                    placeholder="Kota tempat lahir"
                                    :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.birth_place ? 'border-red-400' : 'border-slate-200']"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-100" />

                    <!-- Akun Wali Murid -->
                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Akun Wali Murid</p>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="e-parent" class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Wali Murid</label>
                                <input
                                    id="e-parent"
                                    v-model="editForm.parent_name"
                                    type="text"
                                    :placeholder="student.user ? '' : 'Belum ada akun'"
                                    :disabled="!student.user"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                        !student.user ? 'cursor-not-allowed bg-slate-50 text-slate-400' : '',
                                        editForm.errors.parent_name ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.parent_name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.parent_name }}</p>
                            </div>
                            <div>
                                <label for="e-pass" class="mb-1.5 block text-xs font-semibold text-slate-600">Password Baru</label>
                                <input
                                    id="e-pass"
                                    v-model="editForm.password"
                                    type="password"
                                    placeholder="Kosongkan jika tidak diubah"
                                    autocomplete="new-password"
                                    :disabled="!student.user"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                        !student.user ? 'cursor-not-allowed bg-slate-50' : '',
                                        editForm.errors.password ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="editForm.errors.password" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.password }}</p>
                            </div>
                        </div>

                        <p v-if="!student.user" class="text-xs text-slate-400">
                            Akun wali murid belum dibuat. Hapus siswa ini dan tambah ulang dengan mengisi data wali murid untuk membuat akunnya.
                        </p>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                        >
                            <svg v-if="editForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>

                </form>
            </div>

            <!-- Riwayat Kelas -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Riwayat Kelas</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Penempatan/mutasi kelas dikelola dari menu <Link href="/operator/classrooms" class="font-semibold text-emerald-600 hover:underline">Kelas</Link>.
                    </p>
                </div>

                <div v-if="student.classrooms?.length > 0">
                    <ul class="divide-y divide-slate-100">
                        <li
                            v-for="classroom in student.classrooms"
                            :key="classroom.id"
                            class="flex items-center justify-between px-5 py-3"
                        >
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ classroom.name }}</p>
                                <p class="text-xs text-slate-400">{{ classroom.academic_year?.name }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                                Kelas {{ classroom.grade }}
                            </span>
                        </li>
                    </ul>
                </div>
                <div v-else class="px-5 py-6 text-center">
                    <p class="text-sm text-slate-400">Belum pernah ditempatkan di kelas manapun.</p>
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
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Siswa</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ student.name }}</span>?
                    Akun wali murid dan seluruh data terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="showDelete = false"
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
