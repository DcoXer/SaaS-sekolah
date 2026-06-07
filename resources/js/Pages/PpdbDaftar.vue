<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    setting:        { type: Object,  default: null },
    school:         { type: Object,  default: null },
    serverDate:     { type: String,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
});

const isOpen = computed(() => {
    if (!props.setting?.is_open) return false;
    return props.serverDate >= props.setting.registration_start
        && props.serverDate <= props.setting.registration_end;
});

// ── Steps
const TOTAL_STEPS = 5;
const currentStep = ref(1);

const stepMeta = [
    { no: 1, label: 'Data Siswa',   icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z' },
    { no: 2, label: 'Alamat',       icon: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z' },
    { no: 3, label: 'Orang Tua',    icon: 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z' },
    { no: 4, label: 'Kontak & Dok', icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' },
    { no: 5, label: 'Review',       icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
];

// Fields yang wajib per step untuk validasi lokal sebelum lanjut
const requiredByStep = {
    1: ['full_name', 'no_kk', 'birth_place', 'birth_date', 'gender'],
    2: ['province', 'regency', 'district', 'village', 'address'],
    3: ['father_name', 'father_nik', 'mother_name', 'mother_nik'],
    4: ['parent_phone'],
};

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

watch(selectedProvince, async (id, _, onCleanup) => {
    form.province = provinces.value.find(p => p.id === id)?.name ?? '';
    form.regency  = ''; form.district = ''; form.village = '';
    selectedRegency.value = ''; selectedDistrict.value = '';
    regencies.value = []; districts.value = []; villages.value = [];
    if (!id) return;
    const controller = new AbortController();
    onCleanup(() => controller.abort());
    loadingRegency.value = true;
    try {
        const res = await fetch(`${API}/regencies/${id}.json`, { signal: controller.signal });
        regencies.value = await res.json();
    } catch (e) {
        if (e.name !== 'AbortError') console.error(e);
    } finally { loadingRegency.value = false; }
});

watch(selectedRegency, async (id, _, onCleanup) => {
    form.regency  = regencies.value.find(r => r.id === id)?.name ?? '';
    form.district = ''; form.village = '';
    selectedDistrict.value = '';
    districts.value = []; villages.value = [];
    if (!id) return;
    const controller = new AbortController();
    onCleanup(() => controller.abort());
    loadingDistrict.value = true;
    try {
        const res = await fetch(`${API}/districts/${id}.json`, { signal: controller.signal });
        districts.value = await res.json();
    } catch (e) {
        if (e.name !== 'AbortError') console.error(e);
    } finally { loadingDistrict.value = false; }
});

watch(selectedDistrict, async (id, _, onCleanup) => {
    form.district = districts.value.find(d => d.id === id)?.name ?? '';
    form.village  = '';
    villages.value = [];
    if (!id) return;
    const controller = new AbortController();
    onCleanup(() => controller.abort());
    loadingVillage.value = true;
    try {
        const res = await fetch(`${API}/villages/${id}.json`, { signal: controller.signal });
        villages.value = await res.json();
    } catch (e) {
        if (e.name !== 'AbortError') console.error(e);
    } finally { loadingVillage.value = false; }
});

// ── Form
const form = useForm({
    full_name:       '',
    nik_siswa:       '',
    no_kk:           '',
    birth_place:     '',
    birth_date:      '',
    gender:          '',
    religion:        '',
    previous_school: '',
    province:        '',
    regency:         '',
    district:        '',
    village:         '',
    address:         '',
    father_name:     '',
    father_nik:      '',
    father_phone:    '',
    mother_name:     '',
    mother_nik:      '',
    mother_phone:    '',
    parent_phone:    '',
    parent_email:    '',
    photo:           null,
    document_kk:     null,
    document_akta:   null,
});

// Validasi lokal per step sebelum lanjut (UX biar error muncul lebih awal)
const localErrors = ref({});

const validateStep = (step) => {
    const errors = {};
    const required = requiredByStep[step] ?? [];
    for (const f of required) {
        if (!form[f]) errors[f] = 'Wajib diisi.';
    }
    if (step === 4) {
        if (!form.photo)         errors.photo         = 'Foto siswa wajib diunggah.';
        if (!form.document_kk)   errors.document_kk   = 'Kartu Keluarga wajib diunggah.';
        if (!form.document_akta) errors.document_akta = 'Akta lahir wajib diunggah.';
    }
    localErrors.value = errors;
    return Object.keys(errors).length === 0;
};

const nextStep = () => {
    if (!validateStep(currentStep.value)) return;
    if (currentStep.value < TOTAL_STEPS) currentStep.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const prevStep = () => {
    localErrors.value = {};
    if (currentStep.value > 1) currentStep.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const goToStep = (step) => {
    localErrors.value = {};
    currentStep.value = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const submit = () => {
    form.post(route('ppdb.store'), { forceFormData: true });
};

// ── Helpers untuk review
const genderLabel = computed(() => form.gender === 'male' ? 'Laki-laki' : form.gender === 'female' ? 'Perempuan' : '-');
const formatDate  = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
const fullAddress = computed(() => [form.village, form.district, form.regency, form.province].filter(Boolean).join(', '));

// Error helper: gabung server errors + local errors
const err = (f) => form.errors[f] || localErrors.value[f];

// ── File upload
const fileNames  = ref({ photo: null, document_kk: null, document_akta: null });
const fileErrors = ref({ photo: null, document_kk: null, document_akta: null });
const fileLimits = { photo: 2, document_kk: 5, document_akta: 5 };

const handleFileChange = (field, event) => {
    const file = event.target.files[0];
    fileNames.value[field]  = null;
    fileErrors.value[field] = null;
    form[field] = null;
    if (!file) return;
    const maxBytes = fileLimits[field] * 1024 * 1024;
    if (file.size > maxBytes) {
        fileErrors.value[field] = `Ukuran file (${(file.size/1024/1024).toFixed(1)}MB) melebihi batas ${fileLimits[field]}MB.`;
        event.target.value = '';
        return;
    }
    fileNames.value[field] = file.name;
    form[field] = file;
    // Clear local error jika sudah ada file
    if (localErrors.value[field]) delete localErrors.value[field];
};

const inputCls = (f) => [
    'w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-colors focus:border-amber-400 focus:ring-2 focus:ring-amber-100',
    err(f) ? 'border-red-300 bg-red-50' : 'border-slate-200',
];
const selectCls = (f) => [
    'w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-colors bg-white focus:border-amber-400 focus:ring-2 focus:ring-amber-100',
    err(f) ? 'border-red-300 bg-red-50' : 'border-slate-200',
];
const wilayahCls = (f, disabled) => [
    'w-full rounded-xl border px-4 py-2.5 text-sm outline-none bg-white transition-colors',
    disabled
        ? 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60'
        : 'focus:border-amber-400 focus:ring-2 focus:ring-amber-100 cursor-pointer',
    !disabled && err(f) ? 'border-red-300 bg-red-50' : '',
];
</script>

<template>
    <Head :title="`Form Pendaftaran PPDB — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="`Form pendaftaran online PPDB ${school?.name ?? 'sekolah kami'}.`">
    </Head>

    <div class="min-h-screen bg-slate-50 font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader :school="school" :can-login="canLogin" :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute" active-page="ppdb" />

        <!-- Page header -->
        <div class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-2xl px-6 py-6">
                <nav class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                    <Link :href="route('ppdb.index')" class="transition-colors hover:text-amber-600">PPDB</Link>
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    <span class="font-medium text-slate-600">Form Pendaftaran</span>
                </nav>
                <h1 class="text-2xl font-extrabold text-slate-900">Form Pendaftaran PPDB</h1>
                <p v-if="setting?.title" class="mt-1 text-sm text-slate-500">{{ setting.title }}</p>
            </div>
        </div>

        <!-- Pendaftaran ditutup -->
        <div v-if="!isOpen" class="mx-auto max-w-2xl px-6 py-20 text-center">
            <div class="mx-auto mb-5 flex size-16 items-center justify-center rounded-2xl bg-slate-100">
                <svg class="size-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <p class="text-lg font-bold text-slate-700">Pendaftaran belum/sudah dibuka</p>
            <p class="mt-2 text-sm text-slate-500">Kembali ke halaman PPDB untuk melihat jadwal pendaftaran.</p>
            <Link :href="route('ppdb.index')"
                class="mt-6 inline-flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-bold text-white shadow transition-all hover:bg-amber-400 active:scale-95">
                Kembali ke PPDB
            </Link>
        </div>

        <!-- Wizard -->
        <div v-else class="mx-auto max-w-2xl px-6 py-10">

            <!-- Step Indicator -->
            <div class="mb-8">
                <div class="flex items-center">
                    <template v-for="(step, i) in stepMeta" :key="step.no">
                        <!-- Step bubble -->
                        <div class="flex flex-col items-center">
                            <div class="flex size-10 items-center justify-center rounded-full border-2 transition-all duration-300"
                                :class="currentStep > step.no
                                    ? 'border-amber-500 bg-amber-500 text-white'
                                    : currentStep === step.no
                                        ? 'border-amber-500 bg-white text-amber-600'
                                        : 'border-slate-200 bg-white text-slate-300'">
                                <!-- Completed: centang -->
                                <svg v-if="currentStep > step.no" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                <!-- Active / upcoming: icon -->
                                <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 hidden text-[10px] font-semibold sm:block transition-colors"
                                :class="currentStep >= step.no ? 'text-amber-600' : 'text-slate-300'">
                                {{ step.label }}
                            </span>
                        </div>
                        <!-- Connector line -->
                        <div v-if="i < stepMeta.length - 1"
                            class="mb-4 h-0.5 flex-1 transition-all duration-500"
                            :class="currentStep > step.no ? 'bg-amber-500' : 'bg-slate-200'"/>
                    </template>
                </div>
                <p class="mt-3 text-center text-xs text-slate-400">Langkah {{ currentStep }} dari {{ TOTAL_STEPS }}</p>
            </div>

            <!-- Error server global -->
            <div v-if="form.errors.form" class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5">
                <svg class="mt-0.5 size-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <p class="text-sm text-red-600">{{ form.errors.form }}</p>
            </div>

            <form @submit.prevent="submit">

                <!-- ══ STEP 1: Data Calon Siswa ══ -->
                <Transition name="slide" mode="out-in">
                <div v-if="currentStep === 1" key="step1"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-amber-50 px-6 py-4">
                        <div class="flex size-8 items-center justify-center rounded-full bg-amber-500">
                            <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Data Calon Siswa</h2>
                            <p class="text-xs text-slate-400">Isi sesuai dokumen resmi (KK / Akta Lahir)</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input v-model="form.full_name" type="text" placeholder="Sesuai akta kelahiran" :class="inputCls('full_name')"/>
                            <p v-if="err('full_name')" class="mt-1 text-xs text-red-500">{{ err('full_name') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">NIK Siswa</label>
                            <input v-model="form.nik_siswa" type="text" maxlength="16" placeholder="16 digit NIK" :class="inputCls('nik_siswa')"/>
                            <p v-if="err('nik_siswa')" class="mt-1 text-xs text-red-500">{{ err('nik_siswa') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. Kartu Keluarga <span class="text-red-500">*</span></label>
                            <input v-model="form.no_kk" type="text" maxlength="16" placeholder="16 digit No. KK" :class="inputCls('no_kk')"/>
                            <p v-if="err('no_kk')" class="mt-1 text-xs text-red-500">{{ err('no_kk') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tempat Lahir <span class="text-red-500">*</span></label>
                            <input v-model="form.birth_place" type="text" placeholder="Kota kelahiran" :class="inputCls('birth_place')"/>
                            <p v-if="err('birth_place')" class="mt-1 text-xs text-red-500">{{ err('birth_place') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input v-model="form.birth_date" type="date" :class="inputCls('birth_date')"/>
                            <p v-if="err('birth_date')" class="mt-1 text-xs text-red-500">{{ err('birth_date') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select v-model="form.gender" :class="selectCls('gender')">
                                <option value="">-- Pilih --</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                            <p v-if="err('gender')" class="mt-1 text-xs text-red-500">{{ err('gender') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Agama</label>
                            <select v-model="form.religion" :class="selectCls('religion')">
                                <option value="">-- Pilih --</option>
                                <option>Islam</option><option>Kristen</option><option>Katolik</option>
                                <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Asal TK / RA</label>
                            <input v-model="form.previous_school" type="text" placeholder="Nama sekolah asal (opsional)" :class="inputCls('previous_school')"/>
                        </div>
                    </div>
                </div>
                </Transition>

                <!-- ══ STEP 2: Alamat ══ -->
                <Transition name="slide" mode="out-in">
                <div v-if="currentStep === 2" key="step2"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-amber-50 px-6 py-4">
                        <div class="flex size-8 items-center justify-center rounded-full bg-amber-500">
                            <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Alamat Tempat Tinggal</h2>
                            <p class="text-xs text-slate-400">Alamat lengkap sesuai Kartu Keluarga</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Provinsi <span class="text-red-500">*</span></label>
                            <select v-model="selectedProvince" :class="selectCls('province')">
                                <option value="">-- Pilih Provinsi --</option>
                                <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <p v-if="err('province')" class="mt-1 text-xs text-red-500">{{ err('province') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kabupaten / Kota <span class="text-red-500">*</span></label>
                            <select v-model="selectedRegency" :disabled="!selectedProvince || loadingRegency" :class="wilayahCls('regency', !selectedProvince || loadingRegency)">
                                <option value="">{{ loadingRegency ? 'Memuat...' : '-- Pilih Kab/Kota --' }}</option>
                                <option v-for="r in regencies" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                            <p v-if="err('regency')" class="mt-1 text-xs text-red-500">{{ err('regency') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kecamatan <span class="text-red-500">*</span></label>
                            <select v-model="selectedDistrict" :disabled="!selectedRegency || loadingDistrict" :class="wilayahCls('district', !selectedRegency || loadingDistrict)">
                                <option value="">{{ loadingDistrict ? 'Memuat...' : '-- Pilih Kecamatan --' }}</option>
                                <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                            <p v-if="err('district')" class="mt-1 text-xs text-red-500">{{ err('district') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kelurahan / Desa <span class="text-red-500">*</span></label>
                            <select v-model="form.village" :disabled="!selectedDistrict || loadingVillage" :class="wilayahCls('village', !selectedDistrict || loadingVillage)">
                                <option value="">{{ loadingVillage ? 'Memuat...' : '-- Pilih Kelurahan --' }}</option>
                                <option v-for="v in villages" :key="v.id" :value="v.name">{{ v.name }}</option>
                            </select>
                            <p v-if="err('village')" class="mt-1 text-xs text-red-500">{{ err('village') }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Detail Alamat (Jalan / RT / RW / No. Rumah) <span class="text-red-500">*</span></label>
                            <textarea v-model="form.address" rows="3" placeholder="Contoh: Jl. Mawar No. 12, RT 03/RW 05" :class="[...inputCls('address'), 'resize-none']"/>
                            <p v-if="err('address')" class="mt-1 text-xs text-red-500">{{ err('address') }}</p>
                        </div>
                    </div>
                </div>
                </Transition>

                <!-- ══ STEP 3: Data Orang Tua ══ -->
                <Transition name="slide" mode="out-in">
                <div v-if="currentStep === 3" key="step3"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-amber-50 px-6 py-4">
                        <div class="flex size-8 items-center justify-center rounded-full bg-amber-500">
                            <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Data Orang Tua</h2>
                            <p class="text-xs text-slate-400">NIK sesuai KTP masing-masing</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Ayah -->
                        <div>
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                                <span class="h-px flex-1 bg-slate-100"/>
                                Ayah
                                <span class="h-px flex-1 bg-slate-100"/>
                            </p>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Ayah <span class="text-red-500">*</span></label>
                                    <input v-model="form.father_name" type="text" :class="inputCls('father_name')"/>
                                    <p v-if="err('father_name')" class="mt-1 text-xs text-red-500">{{ err('father_name') }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. HP Ayah</label>
                                    <input v-model="form.father_phone" type="tel" placeholder="08xx-xxxx-xxxx" :class="inputCls('father_phone')"/>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">NIK Ayah <span class="text-red-500">*</span></label>
                                    <input v-model="form.father_nik" type="text" maxlength="16" placeholder="16 digit NIK" :class="inputCls('father_nik')"/>
                                    <p v-if="err('father_nik')" class="mt-1 text-xs text-red-500">{{ err('father_nik') }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Ibu -->
                        <div>
                            <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                                <span class="h-px flex-1 bg-slate-100"/>
                                Ibu
                                <span class="h-px flex-1 bg-slate-100"/>
                            </p>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Ibu <span class="text-red-500">*</span></label>
                                    <input v-model="form.mother_name" type="text" :class="inputCls('mother_name')"/>
                                    <p v-if="err('mother_name')" class="mt-1 text-xs text-red-500">{{ err('mother_name') }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. HP Ibu</label>
                                    <input v-model="form.mother_phone" type="tel" placeholder="08xx-xxxx-xxxx" :class="inputCls('mother_phone')"/>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">NIK Ibu <span class="text-red-500">*</span></label>
                                    <input v-model="form.mother_nik" type="text" maxlength="16" placeholder="16 digit NIK" :class="inputCls('mother_nik')"/>
                                    <p v-if="err('mother_nik')" class="mt-1 text-xs text-red-500">{{ err('mother_nik') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </Transition>

                <!-- ══ STEP 4: Kontak & Dokumen ══ -->
                <Transition name="slide" mode="out-in">
                <div v-if="currentStep === 4" key="step4"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-amber-50 px-6 py-4">
                        <div class="flex size-8 items-center justify-center rounded-full bg-amber-500">
                            <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Kontak &amp; Dokumen</h2>
                            <p class="text-xs text-slate-400">Kontak aktif &amp; upload berkas persyaratan</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Kontak -->
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. WhatsApp Aktif <span class="text-red-500">*</span></label>
                                <input v-model="form.parent_phone" type="tel" placeholder="08xx-xxxx-xxxx" :class="inputCls('parent_phone')"/>
                                <p v-if="err('parent_phone')" class="mt-1 text-xs text-red-500">{{ err('parent_phone') }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Email Kontak <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                                <input v-model="form.parent_email" type="email" placeholder="email@contoh.com" :class="inputCls('parent_email')"/>
                                <p v-if="err('parent_email')" class="mt-1 text-xs text-red-500">{{ err('parent_email') }}</p>
                            </div>
                        </div>

                        <!-- Upload dokumen -->
                        <div>
                            <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">
                                Upload Berkas
                                <span class="ml-1.5 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-600 normal-case tracking-normal">Wajib diunggah</span>
                            </p>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <label v-for="doc in [
                                    { field: 'photo',         label: 'Foto Siswa',     accept: 'image/*',      hint: 'JPG / PNG', size: '2MB' },
                                    { field: 'document_kk',   label: 'Kartu Keluarga', accept: '.pdf,image/*', hint: 'PDF / Gambar', size: '5MB' },
                                    { field: 'document_akta', label: 'Akta Lahir',     accept: '.pdf,image/*', hint: 'PDF / Gambar', size: '5MB' },
                                ]" :key="doc.field"
                                    class="group relative flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed p-5 text-center transition-all"
                                    :class="fileErrors[doc.field] || err(doc.field)
                                        ? 'border-red-300 bg-red-50'
                                        : fileNames[doc.field]
                                            ? 'border-emerald-400 bg-emerald-50'
                                            : 'border-slate-200 hover:border-amber-400 hover:bg-amber-50'">
                                    <input type="file" :accept="doc.accept" class="sr-only"
                                        @change="handleFileChange(doc.field, $event)"/>

                                    <!-- Uploaded state -->
                                    <template v-if="fileNames[doc.field]">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100">
                                            <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-emerald-700">{{ doc.label }}</p>
                                            <p class="mt-0.5 text-[10px] text-emerald-600 break-all line-clamp-2">{{ fileNames[doc.field] }}</p>
                                            <p class="mt-1 text-[10px] text-emerald-500 underline">Ganti file</p>
                                        </div>
                                    </template>

                                    <!-- Empty state -->
                                    <template v-else>
                                        <div class="flex size-10 items-center justify-center rounded-full"
                                            :class="fileErrors[doc.field] || err(doc.field) ? 'bg-red-100' : 'bg-slate-100 group-hover:bg-amber-100'">
                                            <svg class="size-5 transition-colors"
                                                :class="fileErrors[doc.field] || err(doc.field) ? 'text-red-400' : 'text-slate-400 group-hover:text-amber-500'"
                                                fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-700">{{ doc.label }} <span class="text-red-500">*</span></p>
                                            <p class="mt-0.5 text-[10px] text-slate-400">{{ doc.hint }}, maks {{ doc.size }}</p>
                                        </div>
                                    </template>

                                    <p v-if="fileErrors[doc.field]" class="text-xs text-red-500">{{ fileErrors[doc.field] }}</p>
                                    <p v-else-if="err(doc.field)" class="text-xs text-red-500">{{ err(doc.field) }}</p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                </Transition>

                <!-- ══ STEP 5: Review ══ -->
                <Transition name="slide" mode="out-in">
                <div v-if="currentStep === 5" key="step5" class="space-y-4">

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                        <p class="text-sm font-semibold text-amber-800">Periksa kembali data Anda sebelum mengirim.</p>
                        <p class="mt-0.5 text-xs text-amber-700">Klik <strong>Edit</strong> pada setiap bagian jika ada yang perlu diperbaiki.</p>
                    </div>

                    <!-- Data Siswa -->
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Data Calon Siswa</p>
                            <button type="button" @click="goToStep(1)"
                                class="flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                Edit
                            </button>
                        </div>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 p-5 sm:grid-cols-3 text-sm">
                            <div class="col-span-2 sm:col-span-3">
                                <dt class="text-xs text-slate-400">Nama Lengkap</dt>
                                <dd class="font-semibold text-slate-800">{{ form.full_name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">NIK Siswa</dt>
                                <dd class="font-medium text-slate-700">{{ form.nik_siswa || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">No. KK</dt>
                                <dd class="font-medium text-slate-700">{{ form.no_kk || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Jenis Kelamin</dt>
                                <dd class="font-medium text-slate-700">{{ genderLabel }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Tempat Lahir</dt>
                                <dd class="font-medium text-slate-700">{{ form.birth_place || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Tanggal Lahir</dt>
                                <dd class="font-medium text-slate-700">{{ formatDate(form.birth_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Agama</dt>
                                <dd class="font-medium text-slate-700">{{ form.religion || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Asal TK / RA</dt>
                                <dd class="font-medium text-slate-700">{{ form.previous_school || '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Alamat -->
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Alamat Tempat Tinggal</p>
                            <button type="button" @click="goToStep(2)"
                                class="flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                Edit
                            </button>
                        </div>
                        <dl class="grid grid-cols-1 gap-y-3 p-5 text-sm">
                            <div>
                                <dt class="text-xs text-slate-400">Wilayah</dt>
                                <dd class="font-medium text-slate-700">{{ fullAddress || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Detail Alamat</dt>
                                <dd class="font-medium text-slate-700">{{ form.address || '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Orang Tua -->
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Data Orang Tua</p>
                            <button type="button" @click="goToStep(3)"
                                class="flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                Edit
                            </button>
                        </div>
                        <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                            <dl class="grid grid-cols-1 gap-y-3 p-5 text-sm">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Ayah</p>
                                <div>
                                    <dt class="text-xs text-slate-400">Nama</dt>
                                    <dd class="font-medium text-slate-700">{{ form.father_name || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">NIK</dt>
                                    <dd class="font-medium text-slate-700">{{ form.father_nik || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">No. HP</dt>
                                    <dd class="font-medium text-slate-700">{{ form.father_phone || '-' }}</dd>
                                </div>
                            </dl>
                            <dl class="grid grid-cols-1 gap-y-3 p-5 text-sm">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Ibu</p>
                                <div>
                                    <dt class="text-xs text-slate-400">Nama</dt>
                                    <dd class="font-medium text-slate-700">{{ form.mother_name || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">NIK</dt>
                                    <dd class="font-medium text-slate-700">{{ form.mother_nik || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">No. HP</dt>
                                    <dd class="font-medium text-slate-700">{{ form.mother_phone || '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Kontak & Dokumen -->
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Kontak &amp; Dokumen</p>
                            <button type="button" @click="goToStep(4)"
                                class="flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                Edit
                            </button>
                        </div>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 p-5 text-sm">
                            <div>
                                <dt class="text-xs text-slate-400">No. WhatsApp</dt>
                                <dd class="font-medium text-slate-700">{{ form.parent_phone || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Email</dt>
                                <dd class="font-medium text-slate-700">{{ form.parent_email || '-' }}</dd>
                            </div>
                        </dl>
                        <div class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100">
                            <div v-for="doc in [
                                { field: 'photo',         label: 'Foto Siswa' },
                                { field: 'document_kk',   label: 'Kartu Keluarga' },
                                { field: 'document_akta', label: 'Akta Lahir' },
                            ]" :key="doc.field" class="flex flex-col items-center gap-1.5 p-4 text-center">
                                <div class="flex size-8 items-center justify-center rounded-full"
                                    :class="fileNames[doc.field] ? 'bg-emerald-100' : 'bg-red-100'">
                                    <svg class="size-4" :class="fileNames[doc.field] ? 'text-emerald-600' : 'text-red-400'"
                                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path v-if="fileNames[doc.field]" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                    </svg>
                                </div>
                                <p class="text-[10px] font-semibold text-slate-600">{{ doc.label }}</p>
                                <p class="text-[10px]" :class="fileNames[doc.field] ? 'text-emerald-600' : 'text-red-500'">
                                    {{ fileNames[doc.field] ? 'Terunggah' : 'Belum ada' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                </Transition>

                <!-- Navigasi -->
                <div class="mt-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <button v-if="currentStep > 1" type="button" @click="prevStep"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 active:scale-95">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Sebelumnya
                    </button>
                    <Link v-else :href="route('ppdb.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </Link>

                    <!-- Next (step 1–4) -->
                    <button v-if="currentStep < TOTAL_STEPS" type="button" @click="nextStep"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-bold text-white shadow transition-all hover:bg-amber-400 active:scale-95">
                        Selanjutnya
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>

                    <!-- Submit (step 5 review) -->
                    <button v-else type="submit" :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-8 py-2.5 text-sm font-bold text-white shadow transition-all hover:bg-emerald-500 disabled:opacity-60 active:scale-95">
                        <svg v-if="form.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        {{ form.processing ? 'Mengirim...' : 'Kirim Pendaftaran' }}
                    </button>
                </div>
            </form>
        </div>

        <footer class="border-t border-slate-200 bg-white py-6">
            <p class="text-center text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name }}</p>
        </footer>
    </div>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.2s ease;
}
.slide-enter-from {
    opacity: 0;
    transform: translateX(16px);
}
.slide-leave-to {
    opacity: 0;
    transform: translateX(-16px);
}
</style>
