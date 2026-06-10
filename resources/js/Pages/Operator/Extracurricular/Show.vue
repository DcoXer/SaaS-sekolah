<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    extracurricular: { type: Object, required: true },
});

// ── Level options ─────────────────────────────────────────────────────────────
const levelOptions = [
    { value: 'kecamatan',     label: 'Kecamatan' },
    { value: 'kabupaten',     label: 'Kabupaten' },
    { value: 'kota',          label: 'Kota' },
    { value: 'provinsi',      label: 'Provinsi' },
    { value: 'nasional',      label: 'Nasional' },
    { value: 'internasional', label: 'Internasional' },
];

const levelLabel = (val) => levelOptions.find(o => o.value === val)?.label ?? val;

const levelBadge = (level) => ({
    kecamatan:     'bg-slate-100 text-slate-600',
    kabupaten:     'bg-blue-100 text-blue-700',
    kota:          'bg-blue-100 text-blue-700',
    provinsi:      'bg-violet-100 text-violet-700',
    nasional:      'bg-amber-100 text-amber-700',
    internasional: 'bg-primary-100 text-primary-700',
})[level] ?? 'bg-slate-100 text-slate-600';

// ── Edit ekskul ───────────────────────────────────────────────────────────────
const showEditEkskul  = ref(false);
const editImagePreview = ref(props.extracurricular.image
    ? `/storage/${props.extracurricular.image}`
    : null);

const editForm = useForm({
    name:        props.extracurricular.name,
    description: props.extracurricular.description ?? '',
    coach:       props.extracurricular.coach ?? '',
    schedule:    props.extracurricular.schedule ?? '',
    image:       null,
    is_active:   props.extracurricular.is_active,
    sort_order:  props.extracurricular.sort_order,
});

const onEditImageChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    editForm.image = file;
    editImagePreview.value = URL.createObjectURL(file);
};

const submitEdit = () => {
    editForm.transform(data => ({ ...data, _method: 'PUT' }))
        .post(route('operator.extracurriculars.update', props.extracurricular.id), {
            forceFormData: true,
            onSuccess: () => { showEditEkskul.value = false; },
        });
};

// ── Add achievement ───────────────────────────────────────────────────────────
const showAddAchievement = ref(false);

const addForm = useForm({
    title:      '',
    year:       new Date().getFullYear(),
    level:      'nasional',
    rank:       '',
    sort_order: 0,
});

const submitAddAchievement = () => {
    addForm.post(route('operator.extracurriculars.achievements.store', props.extracurricular.id), {
        onSuccess: () => {
            addForm.reset();
            addForm.year  = new Date().getFullYear();
            addForm.level = 'nasional';
            showAddAchievement.value = false;
        },
    });
};

// ── Edit achievement ──────────────────────────────────────────────────────────
const editAchievementTarget = ref(null);

const editAchievementForm = useForm({
    title:      '',
    year:       new Date().getFullYear(),
    level:      'nasional',
    rank:       '',
    sort_order: 0,
});

const openEditAchievement = (a) => {
    editAchievementTarget.value  = a;
    editAchievementForm.title      = a.title;
    editAchievementForm.year       = a.year;
    editAchievementForm.level      = a.level;
    editAchievementForm.rank       = a.rank;
    editAchievementForm.sort_order = a.sort_order;
};

const submitEditAchievement = () => {
    editAchievementForm.patch(
        route('operator.extracurriculars.achievements.update', {
            extracurricular: props.extracurricular.id,
            achievement:     editAchievementTarget.value.id,
        }),
        { onSuccess: () => { editAchievementTarget.value = null; } }
    );
};

// ── Delete achievement ────────────────────────────────────────────────────────
const deleteAchievementId = ref(null);

const confirmDeleteAchievement = (id) => { deleteAchievementId.value = id; };

const doDeleteAchievement = () => {
    router.delete(
        route('operator.extracurriculars.achievements.destroy', {
            extracurricular: props.extracurricular.id,
            achievement:     deleteAchievementId.value,
        }),
        { onSuccess: () => { deleteAchievementId.value = null; } }
    );
};

// ── Upload foto ───────────────────────────────────────────────────────────────
const photoForm   = useForm({ photo: null });
const photoInput  = ref(null);
const deletePhotoId = ref(null);

const onPhotoSelected = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    photoForm.photo = file;
    photoForm.post(route('operator.extracurriculars.photos.store', props.extracurricular.id), {
        forceFormData: true,
        onSuccess: () => {
            photoForm.reset();
            if (photoInput.value) photoInput.value.value = '';
        },
    });
};

const doDeletePhoto = () => {
    router.delete(
        route('operator.extracurriculars.photos.destroy', {
            extracurricular: props.extracurricular.id,
            photo:           deletePhotoId.value,
        }),
        { onSuccess: () => { deletePhotoId.value = null; } }
    );
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20';
</script>

<template>
    <AppLayout>
        <Head :title="`Ekskul — ${extracurricular.name}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.extracurriculars.index')" class="hover:text-slate-700">Ekstrakulikuler</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ extracurricular.name }}</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- ── Section A: Info Ekskul ─────────────────────────────────── -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-bold text-slate-800">Informasi Ekskul</h2>
                    <div class="flex items-center gap-2">
                        <a :href="route('ekskul.show', extracurricular.id)" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                            Lihat Halaman Publik
                        </a>
                        <button @click="showEditEkskul = true"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-primary-600">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                            </svg>
                            Edit
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-6 p-6 sm:flex-row">
                    <!-- Image -->
                    <div class="shrink-0">
                        <div class="size-40 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">
                            <img v-if="extracurricular.image" :src="`/storage/${extracurricular.image}`"
                                :alt="extracurricular.name" class="size-full object-cover"/>
                            <div v-else class="flex size-full items-center justify-center">
                                <svg class="size-14 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-xl font-extrabold text-slate-900">{{ extracurricular.name }}</h3>
                                <span :class="extracurricular.is_active ? 'bg-primary-50 text-primary-700' : 'bg-slate-100 text-slate-500'"
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                    {{ extracurricular.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <p v-if="extracurricular.description" class="mt-2 leading-relaxed text-slate-600">{{ extracurricular.description }}</p>
                            <p v-else class="mt-2 text-sm italic text-slate-400">Belum ada deskripsi.</p>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <div v-if="extracurricular.coach" class="flex items-center gap-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-amber-50">
                                    <svg class="size-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-400">Pelatih</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ extracurricular.coach }}</p>
                                </div>
                            </div>
                            <div v-else class="flex items-center gap-2 opacity-40">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-slate-100">
                                    <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-400">Pelatih</p>
                                    <p class="text-sm text-slate-400 italic">Belum diisi</p>
                                </div>
                            </div>

                            <div v-if="extracurricular.schedule" class="flex items-center gap-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-sky-50">
                                    <svg class="size-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-400">Jadwal Latihan</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ extracurricular.schedule }}</p>
                                </div>
                            </div>
                            <div v-else class="flex items-center gap-2 opacity-40">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-slate-100">
                                    <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-400">Jadwal Latihan</p>
                                    <p class="text-sm text-slate-400 italic">Belum diisi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Section B: Foto Banner ─────────────────────────────────── -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Foto Banner</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Foto-foto ini akan ditampilkan sebagai slideshow di halaman publik</p>
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-primary-600">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Tambah Foto
                        <input ref="photoInput" type="file" accept="image/jpeg,image/png,image/webp"
                            class="sr-only" @change="onPhotoSelected"/>
                    </label>
                </div>

                <div class="p-6">
                    <!-- Grid foto -->
                    <div v-if="extracurricular.photos?.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        <div v-for="photo in extracurricular.photos" :key="photo.id"
                            class="group relative aspect-video overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                            <img :src="`/storage/${photo.path}`" :alt="extracurricular.name"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"/>
                            <button @click="deletePhotoId = photo.id"
                                class="absolute right-1.5 top-1.5 flex size-7 items-center justify-center rounded-lg bg-red-500/90 text-white opacity-0 transition-opacity duration-150 group-hover:opacity-100 hover:bg-red-600">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="mb-3 flex size-12 items-center justify-center rounded-2xl bg-slate-100">
                            <svg class="size-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-600">Belum ada foto</p>
                        <p class="mt-1 text-xs text-slate-400">Klik "Tambah Foto" untuk mengunggah foto banner</p>
                    </div>

                    <!-- Loading indicator -->
                    <div v-if="photoForm.processing" class="mt-3 flex items-center gap-2 text-xs text-primary-600">
                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        Mengunggah...
                    </div>
                </div>
            </div>

            <!-- ── Konfirmasi hapus foto ────────────────────────────────────── -->
            <Modal v-if="deletePhotoId !== null" :show="true" @close="deletePhotoId = null" max-width="sm">
                <div class="p-6">
                    <div class="mb-4 flex size-11 items-center justify-center rounded-full bg-red-100">
                        <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Hapus Foto?</h3>
                    <p class="mt-1.5 text-sm text-slate-500">Foto ini akan dihapus permanen dari slideshow.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button @click="deletePhotoId = null"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                        <button @click="doDeletePhoto"
                            class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Hapus</button>
                    </div>
                </div>
            </Modal>

            <!-- ── Section C: Prestasi ────────────────────────────────────── -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Prestasi</h2>
                        <p class="text-xs text-slate-400">{{ extracurricular.achievements.length }} prestasi tercatat</p>
                    </div>
                    <button @click="showAddAchievement = true"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-amber-600">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Tambah Prestasi
                    </button>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left">
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Prestasi</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Tahun</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 hidden sm:table-cell">Tingkat</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Juara</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="extracurricular.achievements.length === 0">
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada prestasi dicatat.</td>
                        </tr>
                        <tr v-for="a in extracurricular.achievements" :key="a.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ a.title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ a.year }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span :class="levelBadge(a.level)"
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize">
                                    {{ levelLabel(a.level) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-amber-700">{{ a.rank }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditAchievement(a)"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                        Edit
                                    </button>
                                    <button @click="confirmDeleteAchievement(a.id)"
                                        class="rounded-lg border border-red-100 px-2.5 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Modal Edit Ekskul ─────────────────────────────────────────── -->
        <div v-if="showEditEkskul" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showEditEkskul = false">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Edit Ekskul</h3>
                    <button @click="showEditEkskul = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama <span class="text-red-500">*</span></label>
                        <input v-model="editForm.name" type="text" :class="INPUT_CLS"/>
                        <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Deskripsi</label>
                        <textarea v-model="editForm.description" rows="3" :class="INPUT_CLS + ' resize-none'"/>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Pelatih</label>
                        <input v-model="editForm.coach" type="text" placeholder="Nama pelatih" :class="INPUT_CLS"/>
                        <p v-if="editForm.errors.coach" class="mt-1 text-xs text-red-500">{{ editForm.errors.coach }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Jadwal Latihan</label>
                        <input v-model="editForm.schedule" type="text" placeholder="Contoh: Setiap Selasa & Kamis, 14.00–16.00" :class="INPUT_CLS"/>
                        <p v-if="editForm.errors.schedule" class="mt-1 text-xs text-red-500">{{ editForm.errors.schedule }}</p>
                    </div>
                    <!-- Foto -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Foto</label>
                        <div class="flex items-start gap-3">
                            <div class="size-16 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                <img v-if="editImagePreview" :src="editImagePreview" class="size-full object-cover"/>
                                <div v-else class="flex size-full items-center justify-center">
                                    <svg class="size-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                                    </svg>
                                </div>
                            </div>
                            <label class="mt-1 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Ganti Foto
                                <input type="file" accept="image/*" class="hidden" @change="onEditImageChange"/>
                            </label>
                        </div>
                        <p v-if="editForm.errors.image" class="mt-1 text-xs text-red-500">{{ editForm.errors.image }}</p>
                    </div>
                    <!-- Urutan & Status -->
                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan</label>
                            <input v-model.number="editForm.sort_order" type="number" min="0" :class="INPUT_CLS"/>
                        </div>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" v-model="editForm.is_active" class="size-4 rounded border-slate-300 accent-primary-500"/>
                            <span class="text-sm text-slate-700">Aktif</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-2">
                        <button type="button" @click="showEditEkskul = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="editForm.processing"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60">
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Modal Tambah Prestasi ──────────────────────────────────────── -->
        <div v-if="showAddAchievement" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showAddAchievement = false">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Tambah Prestasi</h3>
                    <button @click="showAddAchievement = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitAddAchievement" class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Judul Prestasi <span class="text-red-500">*</span></label>
                        <input v-model="addForm.title" type="text" placeholder="Contoh: Juara Umum Pramuka Tingkat Nasional" :class="INPUT_CLS"/>
                        <p v-if="addForm.errors.title" class="mt-1 text-xs text-red-500">{{ addForm.errors.title }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tahun <span class="text-red-500">*</span></label>
                        <input v-model.number="addForm.year" type="number" min="1900" max="2100" :class="INPUT_CLS"/>
                        <p v-if="addForm.errors.year" class="mt-1 text-xs text-red-500">{{ addForm.errors.year }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Juara / Peringkat <span class="text-red-500">*</span></label>
                        <input v-model="addForm.rank" type="text" placeholder="Contoh: Juara 1" :class="INPUT_CLS"/>
                        <p v-if="addForm.errors.rank" class="mt-1 text-xs text-red-500">{{ addForm.errors.rank }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tingkat <span class="text-red-500">*</span></label>
                        <FilterSelect v-model="addForm.level" :options="levelOptions" :block="true"/>
                        <p v-if="addForm.errors.level" class="mt-1 text-xs text-red-500">{{ addForm.errors.level }}</p>
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-2">
                        <button type="button" @click="showAddAchievement = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="addForm.processing"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-60">
                            {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Modal Edit Prestasi ────────────────────────────────────────── -->
        <div v-if="editAchievementTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="editAchievementTarget = null">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Edit Prestasi</h3>
                    <button @click="editAchievementTarget = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitEditAchievement" class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Judul Prestasi <span class="text-red-500">*</span></label>
                        <input v-model="editAchievementForm.title" type="text" :class="INPUT_CLS"/>
                        <p v-if="editAchievementForm.errors.title" class="mt-1 text-xs text-red-500">{{ editAchievementForm.errors.title }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tahun <span class="text-red-500">*</span></label>
                        <input v-model.number="editAchievementForm.year" type="number" min="1900" max="2100" :class="INPUT_CLS"/>
                        <p v-if="editAchievementForm.errors.year" class="mt-1 text-xs text-red-500">{{ editAchievementForm.errors.year }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Juara / Peringkat <span class="text-red-500">*</span></label>
                        <input v-model="editAchievementForm.rank" type="text" :class="INPUT_CLS"/>
                        <p v-if="editAchievementForm.errors.rank" class="mt-1 text-xs text-red-500">{{ editAchievementForm.errors.rank }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tingkat <span class="text-red-500">*</span></label>
                        <FilterSelect v-model="editAchievementForm.level" :options="levelOptions" :block="true"/>
                        <p v-if="editAchievementForm.errors.level" class="mt-1 text-xs text-red-500">{{ editAchievementForm.errors.level }}</p>
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-2">
                        <button type="button" @click="editAchievementTarget = null"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="editAchievementForm.processing"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60">
                            {{ editAchievementForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Modal Hapus Prestasi ───────────────────────────────────────── -->
        <div v-if="deleteAchievementId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="deleteAchievementId = null">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-bold text-slate-800">Hapus Prestasi?</h3>
                <p class="mt-2 text-sm text-slate-500">Data prestasi ini akan dihapus permanen.</p>
                <div class="mt-5 flex justify-end gap-2">
                    <button @click="deleteAchievementId = null"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button @click="doDeleteAchievement"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">
                        Hapus
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
