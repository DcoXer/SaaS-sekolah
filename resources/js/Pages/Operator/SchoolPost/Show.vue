<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    post: { type: Object, required: true },
});

// ── Upload foto ───────────────────────────────────────────────────────────────
const fileInput = ref(null);
const uploading = ref(false);

const onPhotoSelected = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const form = useForm({ image: file });
    uploading.value = true;
    form.post(route('operator.school-posts.images.store', props.post.id), {
        forceFormData: true,
        onFinish: () => {
            uploading.value = false;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

// ── Delete foto ───────────────────────────────────────────────────────────────
const deleteImageId = ref(null);

const doDeleteImage = () => {
    router.delete(route('operator.school-posts.images.destroy', { post: props.post.id, image: deleteImageId.value }), {
        onSuccess: () => { deleteImageId.value = null; },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Foto — ${post.title}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('operator.school-posts.index')" class="hover:text-slate-700 transition-colors">Berita & Pengumuman</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700 line-clamp-1">{{ post.title }}</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Info post -->
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <!-- Cover thumbnail -->
                        <div class="size-16 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                            <img v-if="post.cover_image_url" :src="post.cover_image_url" class="size-full object-cover" />
                            <div v-else class="flex size-full items-center justify-center">
                                <svg class="size-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span :class="post.category === 'pengumuman' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700'"
                                    class="rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide">
                                    {{ post.category === 'pengumuman' ? 'Pengumuman' : 'Berita' }}
                                </span>
                                <span :class="post.is_published ? 'bg-primary-100 text-primary-700' : 'bg-slate-100 text-slate-500'"
                                    class="rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide">
                                    {{ post.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </div>
                            <h2 class="text-sm font-bold text-slate-800 leading-snug">{{ post.title }}</h2>
                            <p v-if="post.published_at" class="mt-0.5 text-xs text-slate-400">{{ post.published_at }}</p>
                        </div>
                    </div>
                    <Link :href="route('operator.school-posts.index')"
                        class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                        </svg>
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Foto konten -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Foto Konten</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Foto-foto yang ditampilkan sebagai galeri di halaman detail berita.</p>
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary-500 px-3.5 py-2 text-xs font-semibold text-white hover:bg-primary-600 transition-colors"
                        :class="uploading ? 'opacity-60 pointer-events-none' : ''">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        {{ uploading ? 'Mengunggah...' : 'Upload Foto' }}
                        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onPhotoSelected" :disabled="uploading" />
                    </label>
                </div>

                <!-- Grid foto -->
                <div v-if="post.images.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    <div v-for="img in post.images" :key="img.id"
                        class="group relative aspect-video overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                        <img :src="img.url" class="size-full object-cover transition-transform duration-200 group-hover:scale-105" />
                        <!-- Overlay hapus -->
                        <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all duration-200 group-hover:bg-black/40">
                            <button
                                @click="deleteImageId = img.id"
                                class="scale-75 rounded-full bg-red-500 p-2 text-white opacity-0 transition-all duration-200 group-hover:scale-100 group-hover:opacity-100 hover:bg-red-600">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Upload slot -->
                    <label class="flex aspect-video cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-primary-400 hover:bg-primary-50"
                        :class="uploading ? 'opacity-60 pointer-events-none' : ''">
                        <svg class="size-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span class="text-xs font-medium text-slate-400">Tambah Foto</span>
                        <input type="file" accept="image/*" class="hidden" @change="onPhotoSelected" :disabled="uploading" />
                    </label>
                </div>

                <!-- Empty state -->
                <div v-else class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 py-14 text-center">
                    <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                    </svg>
                    <p class="text-sm font-semibold text-slate-400">Belum ada foto konten</p>
                    <p class="mt-1 text-xs text-slate-400">Klik "Upload Foto" untuk menambahkan gambar ke artikel ini.</p>
                </div>
            </div>
        </div>

        <!-- Konfirmasi hapus foto -->
        <Teleport to="body">
            <div v-if="deleteImageId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="deleteImageId = null">
                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-base font-bold text-slate-800">Hapus Foto?</h3>
                    <p class="mt-2 text-sm text-slate-500">Foto ini akan dihapus permanen dari artikel.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button @click="deleteImageId = null" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button @click="doDeleteImage" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Hapus</button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>
