<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const coverPreview = ref(null);

const form = useForm({
    title:        '',
    excerpt:      '',
    content:      '',
    cover_image:  null,
    category:     'berita',
    is_published: false,
});

const onCoverChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.cover_image   = file;
    coverPreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.post(route('operator.school-posts.store'), { forceFormData: true });
};

const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';

const categoryOptions = [
    { value: 'berita',     label: 'Berita' },
    { value: 'pengumuman', label: 'Pengumuman' },
];
</script>

<template>
    <AppLayout>
        <Head title="Tulis Post" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.school-posts.index')" class="hover:text-slate-700">Berita &amp; Pengumuman</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tulis Post</span>
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

            <!-- Heading -->
            <div>
                <h2 class="text-lg font-bold text-slate-900">Tulis Post Baru</h2>
                <p class="mt-1 text-sm text-slate-500">Buat berita atau pengumuman baru untuk ditampilkan di halaman publik.</p>
            </div>

            <!-- Form card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Title -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Judul <span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text" placeholder="Judul berita atau pengumuman" :class="INPUT_CLS" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <!-- Category + Published row -->
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
                            <div class="mt-2">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    Pilih Gambar
                                    <input type="file" accept="image/*" class="hidden" @change="onCoverChange" />
                                </label>
                                <p v-if="coverPreview" class="mt-2 text-xs text-slate-400">Gambar dipilih. Upload saat klik "Buat Post".</p>
                            </div>
                        </div>
                        <p v-if="form.errors.cover_image" class="mt-1.5 text-xs text-red-500">{{ form.errors.cover_image }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <Link
                            :href="route('operator.school-posts.index')"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-60 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Buat Post' }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AppLayout>
</template>
