<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    setting: { type: Object, default: null },
});

const page = usePage();
const assetBase = computed(() => page.props.ziggy?.url ?? '');

const form = useForm({
    name:           props.setting?.name           ?? '',
    tagline:        props.setting?.tagline        ?? '',
    npsn:           props.setting?.npsn           ?? '',
    principal_name: props.setting?.principal_name ?? '',
    principal_nip:  props.setting?.principal_nip  ?? '',
    address:        props.setting?.address        ?? '',
    phone:          props.setting?.phone          ?? '',
    email:          props.setting?.email          ?? '',
    website:        props.setting?.website        ?? '',
    description:    props.setting?.description    ?? '',
    vision:         props.setting?.vision         ?? '',
    mission:        props.setting?.mission        ?? '',
    history:        props.setting?.history        ?? '',
    logo:           null,
    stamp:          null,
});

// ── Logo preview ──────────────────────────────────────────────────────────────
const logoPreview  = ref(props.setting?.logo  ? `/storage/${props.setting.logo}`  : null);
const stampPreview = ref(props.setting?.stamp ? `/storage/${props.setting.stamp}` : null);

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
};

const onStampChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.stamp = file;
    stampPreview.value = URL.createObjectURL(file);
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
};

const removeStamp = () => {
    form.stamp = null;
    stampPreview.value = null;
};

const submit = () => {
    form.post(route('operator.school-settings.save'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Setting Sekolah" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Setting Sekolah</span>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-6">

            <!-- Heading -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Setting Sekolah</h2>
                    <p class="text-sm text-slate-500">Informasi dasar sekolah yang digunakan di raport, surat, dan dokumen lainnya.</p>
                </div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                >
                    <svg v-if="form.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>

            <!-- Info Sekolah -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Informasi Sekolah</h3>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <!-- Nama Sekolah -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Nama Sekolah <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: MI Nurul Iman"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.name ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <!-- Tagline -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tagline / Motto</label>
                        <input
                            v-model="form.tagline"
                            type="text"
                            placeholder="Contoh: Cerdas, Berkarakter, Berprestasi"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.tagline ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.tagline" class="mt-1.5 text-xs text-red-500">{{ form.errors.tagline }}</p>
                    </div>
                    <!-- NPSN -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">NPSN</label>
                        <input
                            v-model="form.npsn"
                            type="text"
                            placeholder="Contoh: 12345678"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.npsn ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.npsn" class="mt-1.5 text-xs text-red-500">{{ form.errors.npsn }}</p>
                    </div>
                    <!-- Telepon -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Telepon</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="Contoh: 021-12345678"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.phone ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-500">{{ form.errors.phone }}</p>
                    </div>
                    <!-- Email -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Contoh: info@sekolah.sch.id"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.email ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>
                    <!-- Website -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Website</label>
                        <input
                            v-model="form.website"
                            type="url"
                            placeholder="Contoh: https://sekolah.sch.id"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.website ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.website" class="mt-1.5 text-xs text-red-500">{{ form.errors.website }}</p>
                    </div>
                    <!-- Alamat -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Alamat <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.address"
                            rows="3"
                            placeholder="Alamat lengkap sekolah..."
                            :class="['w-full resize-none rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.address ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.address" class="mt-1.5 text-xs text-red-500">{{ form.errors.address }}</p>
                    </div>
                </div>
            </div>

            <!-- Kepala Sekolah -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Kepala Madrasah</h3>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.principal_name"
                            type="text"
                            placeholder="Nama lengkap kepala madrasah"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.principal_name ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.principal_name" class="mt-1.5 text-xs text-red-500">{{ form.errors.principal_name }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">NIP</label>
                        <input
                            v-model="form.principal_nip"
                            type="text"
                            placeholder="NIP kepala madrasah"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.principal_nip ? 'border-red-400' : 'border-slate-200']"
                        />
                        <p v-if="form.errors.principal_nip" class="mt-1.5 text-xs text-red-500">{{ form.errors.principal_nip }}</p>
                    </div>
                </div>
            </div>

            <!-- Tentang Sekolah -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Tentang Sekolah</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Ditampilkan di halaman profil sekolah (publik).</p>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5">
                    <!-- Deskripsi -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Deskripsi Singkat</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Gambaran umum sekolah..."
                            :class="['w-full resize-none rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.description ? 'border-red-400' : 'border-slate-200']"
                        />
                    </div>
                    <!-- Visi -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Visi</label>
                        <textarea
                            v-model="form.vision"
                            rows="3"
                            placeholder="Visi sekolah..."
                            :class="['w-full resize-none rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.vision ? 'border-red-400' : 'border-slate-200']"
                        />
                    </div>
                    <!-- Misi -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Misi</label>
                        <textarea
                            v-model="form.mission"
                            rows="4"
                            placeholder="Misi sekolah (bisa beberapa poin)..."
                            :class="['w-full resize-none rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.mission ? 'border-red-400' : 'border-slate-200']"
                        />
                    </div>
                    <!-- Sejarah -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Sejarah Singkat</label>
                        <textarea
                            v-model="form.history"
                            rows="4"
                            placeholder="Sejarah berdirinya sekolah..."
                            :class="['w-full resize-none rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', form.errors.history ? 'border-red-400' : 'border-slate-200']"
                        />
                    </div>
                </div>
            </div>

            <!-- Logo & Stempel -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Logo & Stempel</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Format JPG/PNG, maks. 2MB masing-masing.</p>
                </div>
                <div class="grid grid-cols-1 gap-6 px-6 py-5 sm:grid-cols-2">

                    <!-- Logo -->
                    <div>
                        <label class="mb-2 block text-xs font-semibold text-slate-600">Logo Sekolah</label>
                        <div class="flex items-start gap-4">
                            <!-- Preview -->
                            <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="size-full object-contain p-1" />
                                <svg v-else class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <!-- Controls -->
                            <div class="flex flex-col gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Upload Logo
                                    <input type="file" accept="image/jpg,image/jpeg,image/png" class="sr-only" @change="onLogoChange" />
                                </label>
                                <button v-if="logoPreview" type="button" @click="removeLogo"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 transition-[background-color] duration-150 hover:bg-red-50">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                        <p v-if="form.errors.logo" class="mt-1.5 text-xs text-red-500">{{ form.errors.logo }}</p>
                    </div>

                    <!-- Stempel -->
                    <div>
                        <label class="mb-2 block text-xs font-semibold text-slate-600">Stempel Sekolah</label>
                        <div class="flex items-start gap-4">
                            <!-- Preview -->
                            <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img v-if="stampPreview" :src="stampPreview" alt="Stempel" class="size-full object-contain p-1" />
                                <svg v-else class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                            <!-- Controls -->
                            <div class="flex flex-col gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Upload Stempel
                                    <input type="file" accept="image/jpg,image/jpeg,image/png" class="sr-only" @change="onStampChange" />
                                </label>
                                <button v-if="stampPreview" type="button" @click="removeStamp"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 transition-[background-color] duration-150 hover:bg-red-50">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                        <p v-if="form.errors.stamp" class="mt-1.5 text-xs text-red-500">{{ form.errors.stamp }}</p>
                    </div>

                </div>
            </div>

        </form>
    </AppLayout>
</template>
