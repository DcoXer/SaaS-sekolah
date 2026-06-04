<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    posts:   { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({}) },
});

// ── Filters ───────────────────────────────────────────────────────────────────
const search   = ref(props.filters.search   ?? '');
const category = ref(props.filters.category ?? '');
const status   = ref(props.filters.status   ?? '');

let filterTimer = null;
const applyFilters = () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        router.get(route('operator.school-posts.index'), {
            search:   search.value   || undefined,
            category: category.value || undefined,
            status:   status.value   || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
};

watch([search, category, status], applyFilters);

// ── Modal state ───────────────────────────────────────────────────────────────
const showModal  = ref(false);
const editTarget = ref(null);
const deleteId   = ref(null);
const coverPreview = ref(null);

const blankForm = () => ({
    title:        '',
    excerpt:      '',
    content:      '',
    cover_image:  null,
    category:     'berita',
    is_published: false,
});

const form = useForm(blankForm());

const openCreate = () => {
    editTarget.value  = null;
    coverPreview.value = null;
    form.reset();
    Object.assign(form, blankForm());
    showModal.value = true;
};

const openEdit = (post) => {
    editTarget.value  = post;
    coverPreview.value = post.cover_image_url ?? null;
    form.reset();
    form.title        = post.title;
    form.excerpt      = post.excerpt ?? '';
    form.content      = post.content;
    form.category     = post.category;
    form.is_published = post.is_published;
    form.cover_image  = null;
    showModal.value   = true;
};

const closeModal = () => {
    showModal.value   = false;
    editTarget.value  = null;
    coverPreview.value = null;
    form.reset();
};

// ── Cover image preview ───────────────────────────────────────────────────────
const onCoverChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.cover_image   = file;
    coverPreview.value = URL.createObjectURL(file);
};

// ── Submit ────────────────────────────────────────────────────────────────────
const submitForm = () => {
    if (editTarget.value) {
        form.transform(data => {
            if (!data.cover_image) delete data.cover_image;
            return data;
        }).put(route('operator.school-posts.update', editTarget.value.id), {
            forceFormData: true,
            onSuccess:     () => closeModal(),
        });
    } else {
        form.post(route('operator.school-posts.store'), {
            forceFormData: true,
            onSuccess:     () => closeModal(),
        });
    }
};

// ── Delete ────────────────────────────────────────────────────────────────────
const doDelete = () => {
    router.delete(route('operator.school-posts.destroy', deleteId.value), {
        onSuccess: () => { deleteId.value = null; },
    });
};

// ── Toggle publish ────────────────────────────────────────────────────────────
const togglePublish = (post) => {
    router.patch(route('operator.school-posts.toggle-publish', post.id));
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const INPUT_CLS  = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';
const categoryOptions = [
    { value: 'berita',     label: 'Berita' },
    { value: 'pengumuman', label: 'Pengumuman' },
];
</script>

<template>
    <AppLayout>
        <Head title="Berita & Pengumuman" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Berita & Pengumuman</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Heading + CTA -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Berita & Pengumuman</h2>
                    <p class="text-sm text-slate-500">Kelola konten berita dan pengumuman yang ditampilkan di halaman publik.</p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 transition-colors"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tulis Post
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari judul..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
                    />
                </div>
                <FilterSelect
                    v-model="category"
                    :options="[
                        { value: '', label: 'Semua Kategori' },
                        { value: 'berita', label: 'Berita' },
                        { value: 'pengumuman', label: 'Pengumuman' },
                    ]"
                />
                <FilterSelect
                    v-model="status"
                    :options="[
                        { value: '', label: 'Semua Status' },
                        { value: 'published', label: 'Published' },
                        { value: 'draft', label: 'Draft' },
                    ]"
                />
            </div>

            <!-- Grid posts -->
            <div v-if="posts.data.length" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="post in posts.data" :key="post.id"
                    class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md"
                >
                    <!-- Cover -->
                    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                        <img
                            v-if="post.cover_image_url"
                            :src="post.cover_image_url"
                            :alt="post.title"
                            class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                        <div v-else class="flex size-full items-center justify-center">
                            <svg class="size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="flex flex-1 flex-col p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <span :class="post.category === 'pengumuman' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700'"
                                class="rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide">
                                {{ post.category === 'pengumuman' ? 'Pengumuman' : 'Berita' }}
                            </span>
                            <span :class="post.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                class="rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide">
                                {{ post.is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <h3 class="line-clamp-2 text-sm font-bold text-slate-800 leading-snug">{{ post.title }}</h3>
                        <p v-if="post.excerpt" class="mt-1.5 line-clamp-2 flex-1 text-xs leading-relaxed text-slate-500">{{ post.excerpt }}</p>
                        <p v-if="post.published_at" class="mt-2 text-xs text-slate-400">{{ post.published_at }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-1 border-t border-slate-100 px-4 py-2.5">
                        <!-- Toggle publish -->
                        <button
                            @click="togglePublish(post)"
                            :title="post.is_published ? 'Sembunyikan' : 'Publikasikan'"
                            :class="post.is_published ? 'text-emerald-500 hover:text-emerald-700' : 'text-slate-400 hover:text-slate-600'"
                            class="flex size-8 items-center justify-center rounded-lg transition-colors hover:bg-slate-50"
                        >
                            <!-- Eye open (published) -->
                            <svg v-if="post.is_published" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <!-- Eye slash (draft) -->
                            <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                        <!-- Edit -->
                        <button
                            @click="openEdit(post)"
                            title="Edit"
                            class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-50 hover:text-slate-600"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                            </svg>
                        </button>
                        <!-- Delete -->
                        <button
                            @click="deleteId = post.id"
                            title="Hapus"
                            class="flex size-8 items-center justify-center rounded-lg text-red-400 transition-colors hover:bg-red-50 hover:text-red-600"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border border-slate-200 bg-white p-12 text-center">
                <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5z"/>
                </svg>
                <p class="text-sm text-slate-400">Belum ada post. Klik "Tulis Post" untuk memulai.</p>
            </div>

            <!-- Pagination -->
            <div v-if="posts.links && posts.links.length > 3" class="flex flex-wrap justify-center gap-1">
                <component
                    :is="link.url ? 'a' : 'span'"
                    v-for="link in posts.links" :key="link.label"
                    :href="link.url ?? undefined"
                    v-html="link.label"
                    class="inline-flex min-w-[2rem] items-center justify-center rounded-lg px-3 py-1.5 text-sm transition-colors"
                    :class="link.active
                        ? 'bg-emerald-500 font-semibold text-white'
                        : link.url
                            ? 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                            : 'cursor-not-allowed border border-slate-100 bg-white text-slate-300'"
                />
            </div>

        </div>

        <!-- Modal Create / Edit -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 pt-10" @click.self="closeModal">
                <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <h3 class="text-base font-bold text-slate-800">{{ editTarget ? 'Edit Post' : 'Tulis Post Baru' }}</h3>
                        <button @click="closeModal" class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <form @submit.prevent="submitForm" class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">

                        <!-- Title -->
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Judul <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" placeholder="Judul berita atau pengumuman" :class="INPUT_CLS" />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Kategori <span class="text-red-500">*</span></label>
                            <FilterSelect v-model="form.category" :options="categoryOptions" block :hasError="!!form.errors.category" />
                            <p v-if="form.errors.category" class="mt-1 text-xs text-red-500">{{ form.errors.category }}</p>
                        </div>

                        <!-- Published -->
                        <div class="flex items-center gap-3 pt-6">
                            <input id="is_published" v-model="form.is_published" type="checkbox" class="size-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400" />
                            <label for="is_published" class="text-sm font-medium text-slate-700 cursor-pointer">Langsung publikasikan</label>
                        </div>

                        <!-- Excerpt -->
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Ringkasan (opsional)</label>
                            <textarea v-model="form.excerpt" rows="2" placeholder="Ringkasan singkat yang ditampilkan di list berita..." :class="INPUT_CLS" class="resize-none" />
                            <p v-if="form.errors.excerpt" class="mt-1 text-xs text-red-500">{{ form.errors.excerpt }}</p>
                        </div>

                        <!-- Content -->
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Isi Konten <span class="text-red-500">*</span></label>
                            <textarea v-model="form.content" rows="8" placeholder="Tulis isi berita atau pengumuman di sini..." :class="INPUT_CLS" class="resize-y" />
                            <p v-if="form.errors.content" class="mt-1 text-xs text-red-500">{{ form.errors.content }}</p>
                        </div>

                        <!-- Cover image -->
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-xs font-semibold text-slate-600">Foto Cover (opsional, maks 2 MB)</label>
                            <div class="flex items-start gap-4">
                                <div class="size-24 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shrink-0">
                                    <img v-if="coverPreview" :src="coverPreview" class="size-full object-cover" />
                                    <div v-else class="flex size-full items-center justify-center">
                                        <svg class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                                        </svg>
                                    </div>
                                </div>
                                <label class="mt-2 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    Pilih Gambar
                                    <input type="file" accept="image/*" class="hidden" @change="onCoverChange" />
                                </label>
                            </div>
                            <p v-if="form.errors.cover_image" class="mt-1 text-xs text-red-500">{{ form.errors.cover_image }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2 sm:col-span-2">
                            <button type="button" @click="closeModal" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                            <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-60">
                                {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Simpan Perubahan' : 'Buat Post') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Modal Konfirmasi Hapus -->
        <Teleport to="body">
            <div v-if="deleteId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="deleteId = null">
                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-base font-bold text-slate-800">Hapus Post?</h3>
                    <p class="mt-2 text-sm text-slate-500">Post ini akan dihapus permanen beserta foto cover-nya.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button @click="deleteId = null" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button @click="doDelete" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Hapus</button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>
