<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';

const form = useForm({
    nisn:         '',
    nis:          '',
    name:         '',
    gender:       '',
    grade:        '',
    birth_date:   '',
    address:      '',
    parent_phone: '',
    parent_name:  '',
    email:        '',
    password:     '',
});

const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const gradeOptions = [1, 2, 3, 4, 5, 6].map(g => ({ value: String(g), label: `Kelas ${g}` }));

const submit = () => {
    form.post(route('operator.students.store'), { onError: () => {} });
};
</script>

<template>
    <AppLayout>
        <Head title="Tambah Siswa" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.students.index')" class="transition-colors hover:text-slate-700">Siswa</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tambah Siswa</span>
            </div>
        </template>

        <div class="mx-auto max-w-2xl space-y-6">

            <!-- Page heading -->
            <div>
                <h2 class="text-balance text-lg font-bold text-slate-900">Tambah Siswa</h2>
                <p class="text-pretty text-sm text-slate-500">Isi data siswa dan akun wali murid di bawah ini.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- ── Kartu: Data Siswa ──────────────────────────────────────── -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Data Siswa</p>
                    </div>
                    <div class="space-y-4 px-6 py-5">

                        <!-- NISN + NIS -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="nisn" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    NISN <span class="font-normal text-slate-400">(opsional)</span>
                                </label>
                                <input
                                    id="nisn"
                                    v-model="form.nisn"
                                    type="text"
                                    placeholder="Nomor Induk Siswa Nasional"
                                    :class="[INPUT_CLS, form.errors.nisn ? '!border-red-400' : '']"
                                />
                                <p v-if="form.errors.nisn" class="mt-1 text-xs text-red-500">{{ form.errors.nisn }}</p>
                            </div>
                            <div>
                                <label for="nis" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    NIS <span class="font-normal text-slate-400">(opsional)</span>
                                </label>
                                <input
                                    id="nis"
                                    v-model="form.nis"
                                    type="text"
                                    placeholder="Nomor Induk Siswa internal"
                                    :class="[INPUT_CLS, form.errors.nis ? '!border-red-400' : '']"
                                />
                                <p v-if="form.errors.nis" class="mt-1 text-xs text-red-500">{{ form.errors.nis }}</p>
                            </div>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Nama lengkap siswa"
                                :class="[INPUT_CLS, form.errors.name ? '!border-red-400' : '']"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Jenis Kelamin + Tingkat Kelas -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <FilterSelect
                                    v-model="form.gender"
                                    :options="genderOptions"
                                    block
                                    :has-error="!!form.errors.gender"
                                />
                                <p v-if="form.errors.gender" class="mt-1 text-xs text-red-500">{{ form.errors.gender }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Tingkat Kelas <span class="text-red-500">*</span>
                                </label>
                                <FilterSelect
                                    v-model="form.grade"
                                    :options="gradeOptions"
                                    block
                                    :has-error="!!form.errors.grade"
                                />
                                <p v-if="form.errors.grade" class="mt-1 text-xs text-red-500">{{ form.errors.grade }}</p>
                            </div>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label for="birth_date" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="birth_date"
                                v-model="form.birth_date"
                                type="date"
                                :class="[INPUT_CLS, form.errors.birth_date ? '!border-red-400' : '']"
                            />
                            <p v-if="form.errors.birth_date" class="mt-1 text-xs text-red-500">{{ form.errors.birth_date }}</p>
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label for="address" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Alamat <span class="font-normal text-slate-400">(opsional)</span>
                            </label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                placeholder="Alamat lengkap siswa"
                                :class="[INPUT_CLS, 'resize-none', form.errors.address ? '!border-red-400' : '']"
                            />
                            <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
                        </div>

                    </div>
                </div>

                <!-- ── Kartu: Akun Wali Murid ─────────────────────────────────── -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Akun Wali Murid</p>
                        <p class="mt-0.5 text-xs text-slate-400">Isi email dan password untuk membuat akun login wali murid.</p>
                    </div>
                    <div class="space-y-4 px-6 py-5">

                        <!-- Nama Wali + No. HP -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="parent_name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Nama Wali Murid <span class="font-normal text-slate-400">(opsional)</span>
                                </label>
                                <input
                                    id="parent_name"
                                    v-model="form.parent_name"
                                    type="text"
                                    placeholder="Nama orang tua/wali"
                                    :class="[INPUT_CLS, form.errors.parent_name ? '!border-red-400' : '']"
                                />
                                <p v-if="form.errors.parent_name" class="mt-1 text-xs text-red-500">{{ form.errors.parent_name }}</p>
                            </div>
                            <div>
                                <label for="parent_phone" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    No. HP Wali <span class="font-normal text-slate-400">(opsional)</span>
                                </label>
                                <input
                                    id="parent_phone"
                                    v-model="form.parent_phone"
                                    type="text"
                                    placeholder="08xxxxxxxxxx"
                                    :class="[INPUT_CLS, form.errors.parent_phone ? '!border-red-400' : '']"
                                />
                                <p v-if="form.errors.parent_phone" class="mt-1 text-xs text-red-500">{{ form.errors.parent_phone }}</p>
                            </div>
                        </div>

                        <!-- Email + Password -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Email Login <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="email@contoh.com"
                                    autocomplete="off"
                                    :class="[INPUT_CLS, form.errors.email ? '!border-red-400' : '']"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label for="password" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Min. 8 karakter"
                                    autocomplete="new-password"
                                    :class="[INPUT_CLS, form.errors.password ? '!border-red-400' : '']"
                                />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── Tombol Aksi ─────────────────────────────────────────────── -->
                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('operator.students.index')"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>

            </form>

        </div>
    </AppLayout>
</template>
