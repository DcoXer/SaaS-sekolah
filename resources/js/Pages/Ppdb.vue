<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    setting:        { type: Object,  default: null },
    school:         { type: Object,  default: null },
    stats:          { type: Object,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
});

const page  = usePage();
const flash = computed(() => page.props.flash ?? {});

const showForm = ref(false);

// ── Wilayah API 
const API = 'https://www.emsifa.com/api-wilayah-indonesia/api';

const provinces = ref([]);
const regencies = ref([]);
const districts = ref([]);
const villages  = ref([]);

const selectedProvince = ref('');
const selectedRegency  = ref('');
const selectedDistrict = ref('');

const loadingRegency  = ref(false);
const loadingDistrict = ref(false);
const loadingVillage  = ref(false);

const fetchProvinces = async () => {
    const res = await fetch(`${API}/provinces.json`);
    provinces.value = await res.json();
};
fetchProvinces();

watch(selectedProvince, async (id) => {
    form.province = provinces.value.find(p => p.id === id)?.name ?? '';
    form.regency  = ''; form.district = ''; form.village = '';
    selectedRegency.value = ''; selectedDistrict.value = '';
    regencies.value = []; districts.value = []; villages.value = [];
    if (!id) return;
    loadingRegency.value = true;
    const res = await fetch(`${API}/regencies/${id}.json`);
    regencies.value = await res.json();
    loadingRegency.value = false;
});

watch(selectedRegency, async (id) => {
    form.regency  = regencies.value.find(r => r.id === id)?.name ?? '';
    form.district = ''; form.village = '';
    selectedDistrict.value = '';
    districts.value = []; villages.value = [];
    if (!id) return;
    loadingDistrict.value = true;
    const res = await fetch(`${API}/districts/${id}.json`);
    districts.value = await res.json();
    loadingDistrict.value = false;
});

watch(selectedDistrict, async (id) => {
    form.district = districts.value.find(d => d.id === id)?.name ?? '';
    form.village  = '';
    villages.value = [];
    if (!id) return;
    loadingVillage.value = true;
    const res = await fetch(`${API}/villages/${id}.json`);
    villages.value = await res.json();
    loadingVillage.value = false;
});

// ── Form 
const form = useForm({
    // Data siswa
    full_name:       '',
    nik_siswa:       '',
    no_kk:           '',
    birth_place:     '',
    birth_date:      '',
    gender:          '',
    religion:        '',
    previous_school: '',
    // Alamat
    province:        '',
    regency:         '',
    district:        '',
    village:         '',
    address:         '',
    // Data ayah
    father_name:     '',
    father_nik:      '',
    father_phone:    '',
    // Data ibu
    mother_name:     '',
    mother_nik:      '',
    mother_phone:    '',
    // Kontak
    parent_phone:    '',
    parent_email:    '',
    // Dokumen
    photo:           null,
    document_kk:     null,
    document_akta:   null,
});

const submit = () => {
    form.post(route('ppdb.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            selectedProvince.value = '';
            selectedRegency.value  = '';
            selectedDistrict.value = '';
            showForm.value = false;
        },
    });
};

const isOpen = computed(() => props.setting?.is_open && (() => {
    if (!props.setting) return false;
    const now = new Date().toISOString().slice(0, 10);
    return now >= props.setting.registration_start && now <= props.setting.registration_end;
})());

// ── Helpers 
const inputClass = (field) => [
    'w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-colors focus:border-amber-400 focus:ring-2 focus:ring-amber-100',
    form.errors[field] ? 'border-red-300 bg-red-50' : 'border-slate-200',
];
const selectClass = (field) => [
    'w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-colors focus:border-amber-400 focus:ring-2 focus:ring-amber-100 bg-white',
    form.errors[field] ? 'border-red-300 bg-red-50' : 'border-slate-200',
];
const selectWilayahClass = (field, disabled) => [
    'w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-colors bg-white',
    disabled
        ? 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60'
        : 'focus:border-amber-400 focus:ring-2 focus:ring-amber-100 cursor-pointer',
    !disabled && form.errors[field] ? 'border-red-300 bg-red-50' : 'border-slate-200',
];
</script>

<template>
    <Head :title="`PPDB — ${school?.name ?? 'Sekolah'}`" />

    <div class="min-h-screen overflow-x-hidden bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader :school="school" :can-login="canLogin" :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute" active-page="ppdb" />

        <!-- Hero Section -->
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-700 via-amber-600 to-yellow-500 py-20">
            <div class="absolute -right-24 -top-24 size-80 rounded-full bg-white/5"/>
            <div class="absolute -bottom-16 -left-16 size-64 rounded-full bg-white/5"/>
            <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(0deg,transparent,transparent 40px,white 40px,white 41px),repeating-linear-gradient(90deg,transparent,transparent 40px,white 40px,white 41px)"/>

            <div class="relative mx-auto max-w-5xl px-6 text-center">
                <div v-reveal>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-white backdrop-blur-sm">
                        <span class="size-1.5 animate-pulse rounded-full" :class="isOpen ? 'bg-green-300' : 'bg-red-300'"/>
                        {{ isOpen ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' }}
                    </span>
                    <h1 class="mt-4 text-4xl font-extrabold text-white lg:text-5xl">
                        {{ setting?.title ?? 'Penerimaan Peserta Didik Baru' }}
                    </h1>
                    <p class="mt-3 text-base text-amber-100">{{ school?.name }} membuka pendaftaran siswa baru</p>
                </div>

                <!-- Info Pills -->
                <div v-if="setting" v-reveal="{ delay: 120 }" class="mx-auto mt-8 flex flex-wrap justify-center gap-3">
                    <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm">
                        <svg class="size-4 text-amber-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        {{ new Date(setting.registration_start).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                        –
                        {{ new Date(setting.registration_end).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                    </div>
                    <div v-if="setting.quota" class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm">
                        <svg class="size-4 text-amber-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                        Kuota {{ setting.quota }} siswa
                    </div>
                    <div v-if="stats" class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm">
                        <svg class="size-4 text-green-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ stats.accepted }} sudah diterima
                    </div>
                </div>

                <!-- CTA -->
                <div v-reveal="{ delay: 200 }" class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <button v-if="isOpen" @click="showForm = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-sm font-bold text-amber-700 shadow-xl transition-all hover:bg-amber-50 active:scale-95">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Daftar Sekarang
                    </button>
                    <Link :href="route('ppdb.check')"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-8 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cek Status Pendaftaran
                    </Link>
                </div>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="flash.success" class="border-b border-green-200 bg-green-50 px-6 py-4">
            <div class="mx-auto flex max-w-5xl items-start gap-3">
                <svg class="mt-0.5 size-5 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                <p class="text-sm font-medium text-green-700">{{ flash.success }}</p>
            </div>
        </div>

        <!-- Content -->
        <div class="mx-auto max-w-5xl space-y-12 px-6 py-14">

            <div v-if="!setting" v-reveal class="rounded-2xl border-2 border-dashed border-slate-200 py-24 text-center">
                <svg class="mx-auto mb-3 size-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                <p class="text-base font-semibold text-slate-500">Informasi PPDB belum tersedia</p>
                <p class="mt-1 text-sm text-slate-400">Silakan hubungi sekolah untuk informasi lebih lanjut.</p>
            </div>

            <template v-else>
                <!-- Deskripsi -->
                <div v-if="setting.description" v-reveal>
                    <div class="mb-4 flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-amber-500"/>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Informasi PPDB</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-8">
                        <p class="whitespace-pre-line text-base leading-relaxed text-slate-700">{{ setting.description }}</p>
                    </div>
                </div>

                <!-- Timeline -->
                <div v-reveal>
                    <div class="mb-6 flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-amber-500"/>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Jadwal</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-green-100">
                                <svg class="size-5 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Pendaftaran</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ new Date(setting.registration_start).toLocaleDateString('id-ID', { day:'numeric', month:'long' }) }}
                                    – {{ new Date(setting.registration_end).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                                </p>
                            </div>
                        </div>
                        <div v-if="setting.announcement_date" class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                                <svg class="size-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Pengumuman</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ new Date(setting.announcement_date).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100">
                                <svg class="size-5 text-sky-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Kuota</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ setting.quota }} siswa</p>
                                <p v-if="stats" class="text-xs text-slate-400">{{ stats.accepted }} sudah diterima</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Persyaratan -->
                <div v-if="setting.requirements" v-reveal>
                    <div class="mb-4 flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-amber-500"/>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Persyaratan</p>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-8 shadow-sm">
                        <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-amber-500 to-yellow-400"/>
                        <p class="whitespace-pre-line pl-2 text-sm leading-relaxed text-slate-700">{{ setting.requirements }}</p>
                    </div>
                </div>

                <!-- Pendaftaran ditutup -->
                <div v-if="!isOpen" v-reveal class="rounded-2xl bg-slate-50 p-8 text-center">
                    <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <p class="font-semibold text-slate-600">Pendaftaran belum/sudah dibuka</p>
                    <p class="mt-1 text-sm text-slate-400">Pantau terus informasi PPDB di halaman ini atau hubungi sekolah.</p>
                    <Link :href="route('ppdb.check')"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cek Status Pendaftaran
                    </Link>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-slate-50 py-6">
            <p class="text-center text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name }}</p>
        </footer>

        <!-- Modal Form Pendaftaran -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150">
                <div v-if="showForm"
                    class="fixed inset-0 z-[100] flex items-end justify-center overflow-y-auto bg-black/60 p-4 sm:items-center"
                    @click.self="showForm = false">
                    <Transition enter-from-class="opacity-0 translate-y-8" enter-active-class="transition-all duration-300"
                        leave-to-class="opacity-0 translate-y-4" leave-active-class="transition-all duration-200">
                        <div v-if="showForm" class="relative my-4 w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">

                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 bg-amber-50 px-6 py-5">
                                <div>
                                    <h2 class="text-lg font-extrabold text-slate-900">Form Pendaftaran PPDB</h2>
                                    <p class="text-xs text-slate-500">{{ setting?.title }}</p>
                                </div>
                                <button @click="showForm = false"
                                    class="flex size-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submit" class="max-h-[75vh] overflow-y-auto">
                                <div class="space-y-8 p-6">

                                    <!-- Error global -->
                                    <div v-if="form.errors.form" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                                        {{ form.errors.form }}
                                    </div>

                                    <!-- Data Calon Siswa -->
                                    <fieldset>
                                        <legend class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-600">
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">1</span>
                                            Data Calon Siswa
                                        </legend>
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div class="sm:col-span-2">
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                                <input v-model="form.full_name" type="text" placeholder="Sesuai akta lahir" :class="inputClass('full_name')"/>
                                                <p v-if="form.errors.full_name" class="mt-1 text-xs text-red-500">{{ form.errors.full_name }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">NIK Siswa</label>
                                                <input v-model="form.nik_siswa" type="text" maxlength="16" placeholder="16 digit NIK" :class="inputClass('nik_siswa')"/>
                                                <p v-if="form.errors.nik_siswa" class="mt-1 text-xs text-red-500">{{ form.errors.nik_siswa }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. Kartu Keluarga <span class="text-red-500">*</span></label>
                                                <input v-model="form.no_kk" type="text" maxlength="16" placeholder="16 digit No. KK" :class="inputClass('no_kk')"/>
                                                <p v-if="form.errors.no_kk" class="mt-1 text-xs text-red-500">{{ form.errors.no_kk }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tempat Lahir <span class="text-red-500">*</span></label>
                                                <input v-model="form.birth_place" type="text" placeholder="Kota kelahiran" :class="inputClass('birth_place')"/>
                                                <p v-if="form.errors.birth_place" class="mt-1 text-xs text-red-500">{{ form.errors.birth_place }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tanggal Lahir <span class="text-red-500">*</span></label>
                                                <input v-model="form.birth_date" type="date" :class="inputClass('birth_date')"/>
                                                <p v-if="form.errors.birth_date" class="mt-1 text-xs text-red-500">{{ form.errors.birth_date }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                                                <select v-model="form.gender" :class="selectClass('gender')">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="male">Laki-laki</option>
                                                    <option value="female">Perempuan</option>
                                                </select>
                                                <p v-if="form.errors.gender" class="mt-1 text-xs text-red-500">{{ form.errors.gender }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Agama</label>
                                                <select v-model="form.religion" :class="selectClass('religion')">
                                                    <option value="">-- Pilih --</option>
                                                    <option>Islam</option><option>Kristen</option><option>Katolik</option>
                                                    <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Asal TK/RA</label>
                                                <input v-model="form.previous_school" type="text" placeholder="Nama sekolah asal" :class="inputClass('previous_school')"/>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- Alamat -->
                                    <fieldset>
                                        <legend class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-600">
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">2</span>
                                            Alamat Tempat Tinggal
                                        </legend>
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Provinsi <span class="text-red-500">*</span></label>
                                                <select v-model="selectedProvince" :class="selectClass('province')">
                                                    <option value="">-- Pilih Provinsi --</option>
                                                    <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                                                </select>
                                                <p v-if="form.errors.province" class="mt-1 text-xs text-red-500">{{ form.errors.province }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kabupaten / Kota <span class="text-red-500">*</span></label>
                                                <select v-model="selectedRegency" :disabled="!selectedProvince || loadingRegency" :class="selectClass('regency')">
                                                    <option value="">{{ loadingRegency ? 'Memuat...' : '-- Pilih Kab/Kota --' }}</option>
                                                    <option v-for="r in regencies" :key="r.id" :value="r.id">{{ r.name }}</option>
                                                </select>
                                                <p v-if="form.errors.regency" class="mt-1 text-xs text-red-500">{{ form.errors.regency }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kecamatan <span class="text-red-500">*</span></label>
                                                <select v-model="selectedDistrict" :disabled="!selectedRegency || loadingDistrict" :class="selectClass('district')">
                                                    <option value="">{{ loadingDistrict ? 'Memuat...' : '-- Pilih Kecamatan --' }}</option>
                                                    <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                                                </select>
                                                <p v-if="form.errors.district" class="mt-1 text-xs text-red-500">{{ form.errors.district }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kelurahan / Desa <span class="text-red-500">*</span></label>
                                                <select v-model="form.village" :disabled="!selectedDistrict || loadingVillage" :class="selectClass('village')">
                                                    <option value="">{{ loadingVillage ? 'Memuat...' : '-- Pilih Kelurahan --' }}</option>
                                                    <option v-for="v in villages" :key="v.id" :value="v.name">{{ v.name }}</option>
                                                </select>
                                                <p v-if="form.errors.village" class="mt-1 text-xs text-red-500">{{ form.errors.village }}</p>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Detail Alamat (Jalan / RT / RW / No. Rumah) <span class="text-red-500">*</span></label>
                                                <textarea v-model="form.address" rows="2" placeholder="Contoh: Jl. Mawar No. 12, RT 03/RW 05" :class="[...inputClass('address'), 'resize-none']"/>
                                                <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- Data Ayah -->
                                    <fieldset>
                                        <legend class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-600">
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">3</span>
                                            Data Ayah
                                        </legend>
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                            <div class="sm:col-span-2">
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Ayah <span class="text-red-500">*</span></label>
                                                <input v-model="form.father_name" type="text" :class="inputClass('father_name')"/>
                                                <p v-if="form.errors.father_name" class="mt-1 text-xs text-red-500">{{ form.errors.father_name }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. HP Ayah</label>
                                                <input v-model="form.father_phone" type="tel" placeholder="08xx-xxxx-xxxx" :class="inputClass('father_phone')"/>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">NIK Ayah <span class="text-red-500">*</span></label>
                                                <input v-model="form.father_nik" type="text" maxlength="16" placeholder="16 digit NIK" :class="inputClass('father_nik')"/>
                                                <p v-if="form.errors.father_nik" class="mt-1 text-xs text-red-500">{{ form.errors.father_nik }}</p>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- ④ Data Ibu -->
                                    <fieldset>
                                        <legend class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-600">
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">4</span>
                                            Data Ibu
                                        </legend>
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                            <div class="sm:col-span-2">
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Ibu <span class="text-red-500">*</span></label>
                                                <input v-model="form.mother_name" type="text" :class="inputClass('mother_name')"/>
                                                <p v-if="form.errors.mother_name" class="mt-1 text-xs text-red-500">{{ form.errors.mother_name }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. HP Ibu</label>
                                                <input v-model="form.mother_phone" type="tel" placeholder="08xx-xxxx-xxxx" :class="inputClass('mother_phone')"/>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">NIK Ibu <span class="text-red-500">*</span></label>
                                                <input v-model="form.mother_nik" type="text" maxlength="16" placeholder="16 digit NIK" :class="inputClass('mother_nik')"/>
                                                <p v-if="form.errors.mother_nik" class="mt-1 text-xs text-red-500">{{ form.errors.mother_nik }}</p>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- ⑤ Kontak -->
                                    <fieldset>
                                        <legend class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-600">
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">5</span>
                                            Kontak Utama
                                        </legend>
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. WhatsApp Aktif <span class="text-red-500">*</span></label>
                                                <input v-model="form.parent_phone" type="tel" placeholder="08xx-xxxx-xxxx" :class="inputClass('parent_phone')"/>
                                                <p v-if="form.errors.parent_phone" class="mt-1 text-xs text-red-500">{{ form.errors.parent_phone }}</p>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Email Kontak</label>
                                                <input v-model="form.parent_email" type="email" :class="inputClass('parent_email')"/>
                                                <p v-if="form.errors.parent_email" class="mt-1 text-xs text-red-500">{{ form.errors.parent_email }}</p>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- ⑥ Dokumen -->
                                    <fieldset>
                                        <legend class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-600">
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">6</span>
                                            Dokumen
                                            <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-600">Wajib diunggah</span>
                                        </legend>
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                            <div v-for="doc in [
                                                { field: 'photo',         label: 'Foto Siswa',     accept: 'image/*',               hint: 'JPG/PNG, maks 2MB' },
                                                { field: 'document_kk',   label: 'Kartu Keluarga', accept: '.pdf,image/*',           hint: 'PDF/Gambar, maks 5MB' },
                                                { field: 'document_akta', label: 'Akta Lahir',     accept: '.pdf,image/*',           hint: 'PDF/Gambar, maks 5MB' },
                                            ]" :key="doc.field">
                                                <div class="rounded-xl border-2 border-dashed p-4 transition-colors"
                                                    :class="form.errors[doc.field] ? 'border-red-300 bg-red-50' : 'border-slate-200 hover:border-amber-300'">
                                                    <label class="mb-2 block text-xs font-semibold text-slate-700">
                                                        {{ doc.label }} <span class="text-red-500">*</span>
                                                    </label>
                                                    <input type="file" :accept="doc.accept"
                                                        @change="form[doc.field] = $event.target.files[0]"
                                                        class="w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-amber-700 cursor-pointer"/>
                                                    <p class="mt-1.5 text-[10px] text-slate-400">{{ doc.hint }}</p>
                                                    <p v-if="form.errors[doc.field]" class="mt-1 text-xs text-red-500">{{ form.errors[doc.field] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>

                                <!-- Footer modal -->
                                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                                    <button type="button" @click="showForm = false"
                                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="form.processing"
                                        class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-amber-400 disabled:opacity-60 active:scale-95">
                                        <svg v-if="form.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                        {{ form.processing ? 'Mengirim...' : 'Kirim Pendaftaran' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
