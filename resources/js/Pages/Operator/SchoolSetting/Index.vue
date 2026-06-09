<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    setting:    { type: Object, default: null },
    heroPhotos: { type: Object, default: () => ({}) },
});

const activeTab = ref('informasi');

const tabs = [
    { key: 'informasi', label: 'Informasi' },
    { key: 'profil',    label: 'Profil' },
    { key: 'lokasi',    label: 'Lokasi & Absensi' },
    { key: 'media',     label: 'Media' },
];

const form = useForm({
    name:               props.setting?.name               ?? '',
    tagline:            props.setting?.tagline            ?? '',
    npsn:               props.setting?.npsn               ?? '',
    principal_name:     props.setting?.principal_name     ?? '',
    principal_nip:      props.setting?.principal_nip      ?? '',
    address:            props.setting?.address            ?? '',
    phone:              props.setting?.phone              ?? '',
    email:              props.setting?.email              ?? '',
    website:            props.setting?.website            ?? '',
    latitude:           props.setting?.latitude           ?? '',
    longitude:          props.setting?.longitude          ?? '',
    attendance_radius:  props.setting?.attendance_radius  ?? 100,
    description:        props.setting?.description        ?? '',
    vision:             props.setting?.vision             ?? '',
    mission:            props.setting?.mission            ?? '',
    history:            props.setting?.history            ?? '',
    logo:               null,
    stamp:              null,
});

// Error badge per tab
const tabErrors = computed(() => ({
    informasi: ['name','tagline','npsn','principal_name','principal_nip','address','phone','email','website'].some(k => form.errors[k]),
    profil:    ['description','vision','mission','history'].some(k => form.errors[k]),
    lokasi:    ['latitude','longitude','attendance_radius'].some(k => form.errors[k]),
    media:     ['logo','stamp'].some(k => form.errors[k]),
}));

// ── Logo & Stempel preview ────────────────────────────────────────────────────
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
const removeLogo  = () => { form.logo  = null; logoPreview.value  = null; };
const removeStamp = () => { form.stamp = null; stampPreview.value = null; };

// ── Hero photos (multi-upload per page) ───────────────────────────────────────
const heroPages = [
    { key: 'welcome', label: 'Beranda' },
    { key: 'tentang', label: 'Tentang Kami' },
    { key: 'galeri',  label: 'Galeri' },
    { key: 'ekskul',  label: 'Ekstrakulikuler' },
];

const uploadingPage = ref(null);

const uploadHero = (page, e) => {
    const file = e.target.files[0];
    if (!file) return;
    uploadingPage.value = page;
    const data = new FormData();
    data.append('page', page);
    data.append('photo', file);
    router.post(route('operator.hero-photos.store'), data, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => { uploadingPage.value = null; },
    });
    e.target.value = '';
};

const deleteHero = (id) => {
    router.delete(route('operator.hero-photos.destroy', id), { preserveScroll: true });
};

const submit = () => {
    form.post(route('operator.school-settings.save'), { forceFormData: true });
};

// shared input class
const inp = (err) => [
    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
    err ? 'border-red-400' : 'border-slate-200',
];
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

        <form @submit.prevent="submit" class="space-y-5">

            <!-- ── Heading + Save ────────────────────────────────────────── -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Setting Sekolah</h2>
                    <p class="text-sm text-slate-500">Kelola informasi, profil, lokasi, dan media sekolah.</p>
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

            <!-- ── Tab Nav ───────────────────────────────────────────────── -->
            <div class="flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-100 p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    @click="activeTab = tab.key"
                    :class="[
                        'relative flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition-all duration-150',
                        activeTab === tab.key
                            ? 'bg-white text-slate-800 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700',
                    ]"
                >
                    {{ tab.label }}
                    <!-- Error dot -->
                    <span v-if="tabErrors[tab.key]" class="absolute right-2 top-2 size-1.5 rounded-full bg-red-500"/>
                </button>
            </div>

            <!-- ── Tab: Informasi ────────────────────────────────────────── -->
            <div v-show="activeTab === 'informasi'" class="space-y-5">

                <!-- Info Sekolah -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Informasi Sekolah</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Sekolah <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Contoh: MI Nurul Iman" :class="inp(form.errors.name)" />
                            <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tagline / Motto</label>
                            <input v-model="form.tagline" type="text" placeholder="Contoh: Cerdas, Berkarakter, Berprestasi" :class="inp(form.errors.tagline)" />
                            <p v-if="form.errors.tagline" class="mt-1.5 text-xs text-red-500">{{ form.errors.tagline }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">NPSN</label>
                            <input v-model="form.npsn" type="text" placeholder="Contoh: 12345678" :class="inp(form.errors.npsn)" />
                            <p v-if="form.errors.npsn" class="mt-1.5 text-xs text-red-500">{{ form.errors.npsn }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Telepon</label>
                            <input v-model="form.phone" type="text" placeholder="Contoh: 021-12345678" :class="inp(form.errors.phone)" />
                            <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-500">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Email</label>
                            <input v-model="form.email" type="email" placeholder="Contoh: info@sekolah.sch.id" :class="inp(form.errors.email)" />
                            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Website</label>
                            <input v-model="form.website" type="url" placeholder="Contoh: https://sekolah.sch.id" :class="inp(form.errors.website)" />
                            <p v-if="form.errors.website" class="mt-1.5 text-xs text-red-500">{{ form.errors.website }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Alamat <span class="text-red-500">*</span></label>
                            <textarea v-model="form.address" rows="3" placeholder="Alamat lengkap sekolah..." :class="['resize-none', ...inp(form.errors.address)]" />
                            <p v-if="form.errors.address" class="mt-1.5 text-xs text-red-500">{{ form.errors.address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Kepala Madrasah -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Kepala Madrasah</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama <span class="text-red-500">*</span></label>
                            <input v-model="form.principal_name" type="text" placeholder="Nama lengkap kepala madrasah" :class="inp(form.errors.principal_name)" />
                            <p v-if="form.errors.principal_name" class="mt-1.5 text-xs text-red-500">{{ form.errors.principal_name }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">NIP</label>
                            <input v-model="form.principal_nip" type="text" placeholder="NIP kepala madrasah" :class="inp(form.errors.principal_nip)" />
                            <p v-if="form.errors.principal_nip" class="mt-1.5 text-xs text-red-500">{{ form.errors.principal_nip }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Tab: Profil ───────────────────────────────────────────── -->
            <div v-show="activeTab === 'profil'" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Profil Sekolah</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Ditampilkan di halaman Tentang Kami (publik).</p>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Deskripsi Singkat</label>
                        <textarea v-model="form.description" rows="3" placeholder="Gambaran umum sekolah..." :class="['resize-none', ...inp(form.errors.description)]" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Visi</label>
                        <textarea v-model="form.vision" rows="3" placeholder="Visi sekolah..." :class="['resize-none', ...inp(form.errors.vision)]" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Misi</label>
                        <textarea v-model="form.mission" rows="4" placeholder="Misi sekolah (bisa beberapa poin, pisah baris)..." :class="['resize-none', ...inp(form.errors.mission)]" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Sejarah Singkat</label>
                        <textarea v-model="form.history" rows="4" placeholder="Sejarah berdirinya sekolah..." :class="['resize-none', ...inp(form.errors.history)]" />
                    </div>
                </div>
            </div>

            <!-- ── Tab: Lokasi & Absensi ─────────────────────────────────── -->
            <div v-show="activeTab === 'lokasi'" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Lokasi Sekolah</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Koordinat GPS untuk validasi lokasi absensi guru. Kosongkan jika tidak digunakan.</p>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Latitude</label>
                        <input v-model="form.latitude" type="number" step="0.0000001" placeholder="Contoh: -6.2088" :class="inp(form.errors.latitude)" />
                        <p v-if="form.errors.latitude" class="mt-1.5 text-xs text-red-500">{{ form.errors.latitude }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Longitude</label>
                        <input v-model="form.longitude" type="number" step="0.0000001" placeholder="Contoh: 106.8456" :class="inp(form.errors.longitude)" />
                        <p v-if="form.errors.longitude" class="mt-1.5 text-xs text-red-500">{{ form.errors.longitude }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Radius Absensi (meter)</label>
                        <input v-model="form.attendance_radius" type="number" min="10" max="5000" placeholder="Contoh: 100" :class="inp(form.errors.attendance_radius)" />
                        <p class="mt-1 text-xs text-slate-400">Jarak maksimal agar absensi "Hadir" diterima.</p>
                        <p v-if="form.errors.attendance_radius" class="mt-1 text-xs text-red-500">{{ form.errors.attendance_radius }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Tab: Media ────────────────────────────────────────────── -->
            <div v-show="activeTab === 'media'" class="space-y-5">

                <!-- Logo & Stempel -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Logo & Stempel</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Format JPG/PNG, maks. 2MB. Logo juga dipakai sebagai ikon web & PWA.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-6 px-6 py-5 sm:grid-cols-2">

                        <!-- Logo -->
                        <div>
                            <label class="mb-2 block text-xs font-semibold text-slate-600">Logo Sekolah</label>
                            <div class="flex items-start gap-4">
                                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="size-full object-contain p-1" />
                                    <svg v-else class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>
                                        Upload Logo
                                        <input type="file" accept="image/jpg,image/jpeg,image/png" class="hidden" @change="onLogoChange" />
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
                                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <img v-if="stampPreview" :src="stampPreview" alt="Stempel" class="size-full object-contain p-1" />
                                    <svg v-else class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>
                                        Upload Stempel
                                        <input type="file" accept="image/jpg,image/jpeg,image/png" class="hidden" @change="onStampChange" />
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

                <!-- Foto Hero (multi-upload per page) -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Foto Hero Halaman Publik</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Background foto di bagian atas tiap halaman. Bisa tambah beberapa foto per halaman — akan berganti otomatis tiap 5 detik. Format JPG/PNG/WebP, maks. 3MB. Jika kosong, tampil gradien hijau default.</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="page in heroPages" :key="page.key" class="px-6 py-5">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold text-slate-700">{{ page.label }}</span>
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition-[background-color] duration-150 hover:bg-emerald-100">
                                    <svg v-if="uploadingPage === page.key" class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    <svg v-else class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    {{ uploadingPage === page.key ? 'Mengupload...' : 'Tambah Foto' }}
                                    <input
                                        type="file"
                                        accept="image/jpg,image/jpeg,image/png,image/webp"
                                        class="hidden"
                                        :disabled="uploadingPage === page.key"
                                        @change="uploadHero(page.key, $event)"
                                    />
                                </label>
                            </div>

                            <!-- Thumbnail row -->
                            <div v-if="heroPhotos[page.key]?.length" class="flex gap-3 overflow-x-auto pb-2">
                                <div
                                    v-for="photo in heroPhotos[page.key]"
                                    :key="photo.id"
                                    class="relative shrink-0 h-24 w-36 overflow-hidden rounded-lg border border-slate-200 bg-slate-100"
                                >
                                    <img :src="photo.file_url" alt="Hero" class="size-full object-cover" />
                                    <button
                                        type="button"
                                        @click="deleteHero(photo.id)"
                                        class="absolute right-1 top-1 flex size-5 items-center justify-center rounded-full bg-red-500 text-white shadow transition-[background-color] duration-150 hover:bg-red-600"
                                        title="Hapus foto"
                                    >
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-xs text-slate-400 italic">Belum ada foto — tampil gradien hijau default.</p>
                        </div>
                    </div>
                </div>

            </div>

        </form>
    </AppLayout>
</template>
