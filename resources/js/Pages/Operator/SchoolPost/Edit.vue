<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    post: { type: Object, required: true },
});

// ── Form edit konten ──────────────────────────────────────────────────────────
const coverPreview = ref(props.post.cover_image_url ?? null);

const form = useForm({
    title:        props.post.title,
    excerpt:      props.post.excerpt ?? '',
    content:      props.post.content,
    cover_image:  null,
    category:     props.post.category,
    is_published: props.post.is_published,
});

const onCoverChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.cover_image   = file;
    coverPreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.transform(data => {
        if (!data.cover_image) delete data.cover_image;
        return data;
    }).put(route('operator.school-posts.update', props.post.id), { forceFormData: true });
};

const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';

const categoryOptions = [
    { value: 'berita',     label: 'Berita' },
    { value: 'pengumuman', label: 'Pengumuman' },
];

// ── Upload foto konten ────────────────────────────────────────────────────────
const fileInput  = ref(null);
const uploading  = ref(false);

const onPhotoSelected = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const imgForm = useForm({ image: file });
    uploading.value = true;
    imgForm.post(route('operator.school-posts.images.store', props.post.id), {
        forceFormData: true,
        onFinish: () => {
            uploading.value = false;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

// ── Delete foto konten ────────────────────────────────────────────────────────
const deleteImageId = ref(null);

const doDeleteImage = () => {
    router.delete(route('operator.school-posts.images.destroy', { post: props.post.id, image: deleteImageId.value }), {
        onSuccess: () => { deleteImageId.value = null; },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit — ${post.title}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.school-posts.index')" class="hover:text-slate-700 transition-colors">Berita &amp; Pengumuman</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700 line-clamp-1">Edit Post</span>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">

            <!-- Back button -->
            <div>
                <Link :href="route('operator.school-posts.index')"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Berita &amp; Pengumuman
                </Link>
            </div>

            <!-- ── Form Edit Konten ──────────────────────────────────────────── -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-bold text-slate-800">Konten Post</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Cover image lama tetap dipakai jika tidak diunggah ulang.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5 p-6">

                    <!-- Title -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Judul <span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text" placeholder="Judul berita atau pengumuman" :class="INPUT_CLS" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <!-- Category + Published -->
                    <div class="flex flex-wrap items-end gap-5">
                        <div class="flex-1 min-w-[180px]">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Kategori <span class="text-red-500">*</span></label>
                            <FilterSelect v-model="form.category" :options="categoryOptions" block :hasError="!!form.errors.category" />
                            <p v-if="form.errors.category" class="mt-1 text-xs text-red-500">{{ form.errors.category }}</p>
                        </div>
                        <div class="flex items-center gap-2.5 pb-0.5">
                            <input id="is_published" v-model="form.is_published" type="checkbox" class="size-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400" />
                            <label for="is_published" class="text-sm font-medium text-slate-700 cursor-pointer">Langsung publikasikan</label>
                        </div>
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Ringkasan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <textarea v-model="form.excerpt" rows="3" placeholder="Ringkasan singkat yang ditampilkan di list berita..." :class="INPUT_CLS" class="resize-none" />
                        <p v-if="form.errors.excerpt" class="mt-1 text-xs text-red-500">{{ form.errors.excerpt }}</p>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Isi Konten <span class="text-red-500">*</span></label>
                        <textarea v-model="form.content" rows="12" placeholder="Tulis isi berita atau pengumuman di sini..." :class="INPUT_CLS" class="resize-y" />
                        <p v-if="form.errors.content" class="mt-1 text-xs text-red-500">{{ form.errors.content }}</p>
                    </div>

                    <!-- Cover image -->
                    <div>
                        <label class="mb-2 block text-xs font-semibold text-slate-600">Foto Cover <span class="text-slate-400 font-normal">(opsional, maks 2 MB)</span></label>
                        <div class="flex items-start gap-4">
                            <div class="size-28 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img v-if="coverPreview" :src="coverPreview" class="size-full object-cover" />
                                <div v-else class="flex size-full items-center justify-center">
                                    <svg class="size-9 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 space-y-2">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    {{ coverPreview ? 'Ganti Gambar' : 'Pilih Gambar' }}
                                    <input type="file" accept="image/*" class="hidden" @change="onCoverChange" />
                                </label>
                                <p v-if="post.cover_image_url && !form.cover_image" class="text-xs text-slate-400">Cover saat ini dipertahankan.</p>
                            </div>
                        </div>
                        <p v-if="form.errors.cover_image" class="mt-1.5 text-xs text-red-500">{{ form.errors.cover_image }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <Link :href="route('operator.school-posts.index')"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-60 transition-colors">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── Foto Konten ───────────────────────────────────────────────── -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Foto Konten</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Ditampilkan sebagai galeri foto di halaman detail berita.</p>
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-emerald-500 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-600 transition-colors"
                        :class="uploading ? 'opacity-60 pointer-events-none' : ''">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        {{ uploading ? 'Mengunggah...' : 'Upload Foto' }}
                        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onPhotoSelected" :disabled="uploading" />
                    </label>
                </div>

                <div class="p-6">
                    <!-- Grid foto -->
                    <div v-if="post.images.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        <div v-for="img in post.images" :key="img.id"
                            class="group relative aspect-video overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                            <img :src="img.url" class="size-full object-cover transition-transform duration-200 group-hover:scale-105" />
                            <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all duration-200 group-hover:bg-black/40">
                                <button @click="deleteImageId = img.id"
                                    class="scale-75 rounded-full bg-red-500 p-2 text-white opacity-0 transition-all duration-200 group-hover:scale-100 group-hover:opacity-100 hover:bg-red-600">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Slot tambah -->
                        <label class="flex aspect-video cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-emerald-400 hover:bg-emerald-50"
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
                        <p class="mt-1 text-xs text-slate-400">Foto akan ditampilkan sebagai galeri di halaman berita.</p>
                    </div>
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
