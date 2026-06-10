<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20';

const typeOptions = [
    { value: 'guru_kelas',  label: 'Guru Kelas',  desc: 'Kelas 1–3, mengajar semua mapel' },
    { value: 'guru_bidang', label: 'Guru Bidang',  desc: 'Kelas 4–6, mapel spesifik' },
];

const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const form = useForm({
    name:     '',
    email:    '',
    password: '',
    type:     '',
    nip:      '',
    gender:   '',
    phone:    '',
});

const showPassword = ref(false);

const slug = (str) => str.toLowerCase().replace(/\s+/g, '.').replace(/[^a-z0-9.]/g, '');

const generateEmail = () => {
    const base = form.nip ? form.nip.trim() : slug(form.name || 'guru');
    form.email = `${base}@guru.sekolah.id`;
};

const PASS_CHARS = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
const generatePassword = () => {
    let pwd = '';
    for (let i = 0; i < 10; i++) pwd += PASS_CHARS[Math.floor(Math.random() * PASS_CHARS.length)];
    form.password = pwd;
    showPassword.value = true;
};

const submit = () => {
    form.post(route('operator.teachers.store'), { onError: () => {} });
};
</script>

<template>
    <AppLayout>
        <Head title="Tambah Guru" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.teachers.index')" class="hover:text-slate-700">Guru</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tambah Guru</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div>
                <h2 class="text-balance text-lg font-bold text-slate-900">Tambah Guru</h2>
                <p class="text-pretty text-sm text-slate-500">Isi data di bawah untuk mendaftarkan guru baru beserta akun login-nya.</p>
            </div>

            <!-- Form Card -->
            <div class="mx-auto max-w-2xl">
                <form @submit.prevent="submit" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="space-y-5 px-6 py-6">

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Nama lengkap guru"
                                autocomplete="off"
                                :class="[INPUT_CLS, form.errors.name ? 'border-red-400' : '']"
                            />
                            <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email + Password -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <div class="mb-1.5 flex items-center justify-between">
                                    <label for="email" class="text-xs font-semibold text-slate-600">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <button type="button" @click="generateEmail" class="text-xs font-medium text-amber-600 hover:text-amber-700 underline cursor-pointer">
                                        ⚡ Generate
                                    </button>
                                </div>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="email@sekolah.ac.id"
                                    autocomplete="off"
                                    :class="[INPUT_CLS, form.errors.email ? 'border-red-400' : '']"
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <div class="mb-1.5 flex items-center justify-between">
                                    <label for="password" class="text-xs font-semibold text-slate-600">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <button type="button" @click="generatePassword" class="text-xs font-medium text-amber-600 hover:text-amber-700 underline cursor-pointer">
                                        ⚡ Generate
                                    </button>
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="Min. 8 karakter"
                                    autocomplete="new-password"
                                    :class="[INPUT_CLS, form.errors.password ? 'border-red-400' : '']"
                                />
                                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
                            </div>
                        </div>

                        <!-- Tipe Guru (radio cards) -->
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
                                        form.type === opt.value
                                            ? 'border-primary-400 bg-primary-50'
                                            : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                                    ]"
                                >
                                    <input type="radio" :value="opt.value" v-model="form.type" class="sr-only" />
                                    <span :class="['text-sm font-semibold', form.type === opt.value ? 'text-primary-700' : 'text-slate-700']">
                                        {{ opt.label }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ opt.desc }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.type" class="mt-1.5 text-xs text-red-500">{{ form.errors.type }}</p>
                        </div>

                        <!-- NIP + Telepon -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="nip" class="mb-1.5 block text-xs font-semibold text-slate-600">NIP</label>
                                <input
                                    id="nip"
                                    v-model="form.nip"
                                    type="text"
                                    placeholder="Nomor Induk Pegawai"
                                    :class="[INPUT_CLS, form.errors.nip ? 'border-red-400' : '']"
                                />
                                <p v-if="form.errors.nip" class="mt-1.5 text-xs text-red-500">{{ form.errors.nip }}</p>
                            </div>
                            <div>
                                <label for="phone" class="mb-1.5 block text-xs font-semibold text-slate-600">Telepon</label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="08xxxxxxxxxx"
                                    :class="[INPUT_CLS, form.errors.phone ? 'border-red-400' : '']"
                                />
                                <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-500">{{ form.errors.phone }}</p>
                            </div>
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <FilterSelect
                                v-model="form.gender"
                                :options="genderOptions"
                                :has-error="!!form.errors.gender"
                                :block="true"
                            />
                            <p v-if="form.errors.gender" class="mt-1.5 text-xs text-red-500">{{ form.errors.gender }}</p>
                        </div>

                    </div>

                    <!-- Footer actions -->
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                        <Link
                            :href="route('operator.teachers.index')"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                        >
                            <svg v-if="form.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AppLayout>
</template>
