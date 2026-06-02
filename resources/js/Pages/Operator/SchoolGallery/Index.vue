<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    galleries: { type: Array, default: () => [] },
});

// ── tab: photo / video ────────────────────────────────────────────────────────
const activeTab = ref('photo');

// ── form foto ─────────────────────────────────────────────────────────────────
const photoForm = useForm({
    file:       null,
    video_url:  null,
    caption:    '',
    sort_order: 0,
});

const photoPreview = ref(null);

const onPhotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    photoForm.file = file;
    photoPreview.value = URL.createObjectURL(file);
};

const submitPhoto = () => {
    photoForm.post(route('operator.school-galleries.store'), {
        forceFormData: true,
        onSuccess: () => {
            photoForm.reset();
            photoPreview.value = null;
        },
    });
};

// ── form video ────────────────────────────────────────────────────────────────
const videoForm = useForm({
    file:       null,
    video_url:  '',
    caption:    '',
    sort_order: 0,
});

const submitVideo = () => {
    videoForm.post(route('operator.school-galleries.store'), {
        onSuccess: () => { videoForm.reset(); },
    });
};

// ── delete ────────────────────────────────────────────────────────────────────
const deleteId = ref(null);

const doDelete = () => {
    router.delete(route('operator.school-galleries.destroy', deleteId.value), {
        onSuccess: () => { deleteId.value = null; },
    });
};

// ── youtube embed helper ──────────────────────────────────────────────────────
const getYtId = (url) => {
    if (!url) return null;
    const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([^&\n?#]+)/);
    return m ? m[1] : null;
};

const getYtThumb = (url) => {
    const id = getYtId(url);
    return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : null;
};

const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';
</script>

<template>
    <AppLayout>
        <Head title="Galeri Sekolah" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Galeri Sekolah</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Heading -->
            <div>
                <h2 class="text-lg font-bold text-slate-900">Galeri Sekolah</h2>
                <p class="text-sm text-slate-500">Upload foto atau tambahkan video YouTube untuk ditampilkan di halaman profil sekolah.</p>
            </div>

            <!-- Form Tambah -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Tambah Item</h3>
                </div>

                <!-- Tab -->
                <div class="flex border-b border-slate-100">
                    <button
                        @click="activeTab = 'photo'"
                        :class="activeTab === 'photo' ? 'border-b-2 border-emerald-500 text-emerald-600' : 'text-slate-400 hover:text-slate-600'"
                        class="px-6 py-3 text-sm font-semibold transition-colors"
                    >Foto</button>
                    <button
                        @click="activeTab = 'video'"
                        :class="activeTab === 'video' ? 'border-b-2 border-emerald-500 text-emerald-600' : 'text-slate-400 hover:text-slate-600'"
                        class="px-6 py-3 text-sm font-semibold transition-colors"
                    >Video YouTube</button>
                </div>

                <!-- Form Foto -->
                <form v-if="activeTab === 'photo'" @submit.prevent="submitPhoto" class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-xs font-semibold text-slate-600">Upload Foto <span class="text-red-500">*</span></label>
                        <div class="flex items-start gap-4">
                            <div class="size-24 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img v-if="photoPreview" :src="photoPreview" class="size-full object-cover" />
                                <div v-else class="flex size-full items-center justify-center">
                                    <svg class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                            </div>
                            <label class="mt-2 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                Pilih Foto
                                <input type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
                            </label>
                        </div>
                        <p v-if="photoForm.errors.file" class="mt-1 text-xs text-red-500">{{ photoForm.errors.file }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Keterangan</label>
                        <input v-model="photoForm.caption" type="text" placeholder="Contoh: Upacara HUT RI 2024" :class="INPUT_CLS" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan</label>
                        <input v-model.number="photoForm.sort_order" type="number" min="0" :class="INPUT_CLS" />
                    </div>
                    <div class="flex justify-end sm:col-span-2">
                        <button type="submit" :disabled="photoForm.processing" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-60">
                            {{ photoForm.processing ? 'Mengupload...' : 'Tambah Foto' }}
                        </button>
                    </div>
                </form>

                <!-- Form Video -->
                <form v-if="activeTab === 'video'" @submit.prevent="submitVideo" class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">URL YouTube <span class="text-red-500">*</span></label>
                        <input v-model="videoForm.video_url" type="url" placeholder="https://www.youtube.com/watch?v=..." :class="INPUT_CLS" />
                        <p v-if="videoForm.errors.video_url" class="mt-1 text-xs text-red-500">{{ videoForm.errors.video_url }}</p>
                        <!-- Preview thumbnail -->
                        <div v-if="getYtThumb(videoForm.video_url)" class="mt-3">
                            <img :src="getYtThumb(videoForm.video_url)" class="h-32 rounded-lg object-cover" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Keterangan</label>
                        <input v-model="videoForm.caption" type="text" placeholder="Contoh: Pentas Seni 2024" :class="INPUT_CLS" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan</label>
                        <input v-model.number="videoForm.sort_order" type="number" min="0" :class="INPUT_CLS" />
                    </div>
                    <div class="flex justify-end sm:col-span-2">
                        <button type="submit" :disabled="videoForm.processing" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-60">
                            {{ videoForm.processing ? 'Menyimpan...' : 'Tambah Video' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Galeri -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <div v-if="galleries.length === 0" class="col-span-full rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                    Belum ada foto atau video.
                </div>
                <div
                    v-for="item in galleries" :key="item.id"
                    class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
                >
                    <!-- Foto -->
                    <img
                        v-if="item.type === 'photo' && item.file_path"
                        :src="`/storage/${item.file_path}`"
                        class="aspect-square w-full object-cover"
                    />
                    <!-- Video thumbnail -->
                    <div v-else-if="item.type === 'video'" class="relative">
                        <img
                            v-if="getYtThumb(item.video_url)"
                            :src="getYtThumb(item.video_url)"
                            class="aspect-square w-full object-cover"
                        />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="flex size-10 items-center justify-center rounded-full bg-black/60 text-white">
                                <svg class="size-5 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Caption -->
                    <div v-if="item.caption" class="px-3 py-2 text-xs text-slate-600 truncate">{{ item.caption }}</div>

                    <!-- Badge type -->
                    <div class="absolute left-2 top-2">
                        <span :class="item.type === 'video' ? 'bg-rose-500' : 'bg-sky-500'" class="rounded-full px-2 py-0.5 text-[10px] font-bold text-white uppercase">
                            {{ item.type === 'video' ? 'Video' : 'Foto' }}
                        </span>
                    </div>

                    <!-- Hapus -->
                    <button
                        @click="deleteId = item.id"
                        class="absolute right-2 top-2 flex size-7 items-center justify-center rounded-full bg-white/90 text-red-500 opacity-0 shadow transition-opacity group-hover:opacity-100"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div v-if="deleteId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="deleteId = null">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-bold text-slate-800">Hapus Item?</h3>
                <p class="mt-2 text-sm text-slate-500">Item galeri akan dihapus permanen.</p>
                <div class="mt-5 flex justify-end gap-2">
                    <button @click="deleteId = null" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button @click="doDelete" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Hapus</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
