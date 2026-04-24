<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    extracurriculars: { type: Array, default: () => [] },
});

// ── form tambah ───────────────────────────────────────────────────────────────
const showAdd    = ref(false);
const imagePreview = ref(null);

const form = useForm({
    name:        '',
    description: '',
    image:       null,
    is_active:   true,
    sort_order:  0,
});

const onImageChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.post(route('operator.extracurriculars.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
            showAdd.value = false;
        },
    });
};

// ── edit ──────────────────────────────────────────────────────────────────────
const editTarget   = ref(null);
const editPreview  = ref(null);

const editForm = useForm({
    name:        '',
    description: '',
    image:       null,
    is_active:   true,
    sort_order:  0,
});

const openEdit = (ekskul) => {
    editTarget.value  = ekskul;
    editPreview.value = ekskul.image ?? null;
    editForm.name        = ekskul.name;
    editForm.description = ekskul.description ?? '';
    editForm.image       = null;
    editForm.is_active   = ekskul.is_active;
    editForm.sort_order  = ekskul.sort_order;
};

const onEditImageChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    editForm.image = file;
    editPreview.value = URL.createObjectURL(file);
};

const submitEdit = () => {
    editForm.transform(data => ({ ...data, _method: 'PUT' }))
        .post(route('operator.extracurriculars.update', editTarget.value.id), {
            forceFormData: true,
            onSuccess: () => { editTarget.value = null; },
        });
};

// ── delete ────────────────────────────────────────────────────────────────────
const deleteId = ref(null);

const confirmDelete = (id) => { deleteId.value = id; };

const doDelete = () => {
    router.delete(route('operator.extracurriculars.destroy', deleteId.value), {
        onSuccess: () => { deleteId.value = null; },
    });
};

// ── helpers ───────────────────────────────────────────────────────────────────
const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';
</script>

<template>
    <AppLayout>
        <Head title="Ekstrakulikuler" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Ekstrakulikuler</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Heading -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Ekstrakulikuler</h2>
                    <p class="text-sm text-slate-500">Kelola daftar ekskul yang ditampilkan di halaman profil sekolah.</p>
                </div>
                <button
                    @click="showAdd = !showAdd"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Ekskul
                </button>
            </div>

            <!-- Form Tambah -->
            <div v-if="showAdd" class="overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50 shadow-sm">
                <div class="border-b border-emerald-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-emerald-800">Tambah Ekskul Baru</h3>
                </div>
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Ekskul <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" placeholder="Contoh: Pramuka" :class="INPUT_CLS" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Deskripsi</label>
                        <textarea v-model="form.description" rows="3" placeholder="Deskripsi singkat ekskul..." :class="INPUT_CLS + ' resize-none'" />
                    </div>
                    <!-- Foto -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Foto</label>
                        <div class="flex items-start gap-4">
                            <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <img v-if="imagePreview" :src="imagePreview" class="size-full object-cover" />
                                <svg v-else class="size-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <label class="mt-1 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                Upload Foto
                                <input type="file" accept="image/*" class="sr-only" @change="onImageChange" />
                            </label>
                        </div>
                        <p v-if="form.errors.image" class="mt-1 text-xs text-red-500">{{ form.errors.image }}</p>
                    </div>
                    <!-- Urutan & Status -->
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan</label>
                            <input v-model.number="form.sort_order" type="number" min="0" :class="INPUT_CLS" />
                        </div>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" v-model="form.is_active" class="size-4 rounded border-slate-300 accent-emerald-500" />
                            <span class="text-sm text-slate-700">Aktif (tampil di halaman profil)</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-2">
                        <button type="button" @click="showAdd = false" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Ekskul -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left">
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Foto</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 hidden sm:table-cell">Deskripsi</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="extracurriculars.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">Belum ada ekskul.</td>
                        </tr>
                        <tr v-for="ekskul in extracurriculars" :key="ekskul.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="size-10 overflow-hidden rounded-lg border border-slate-100 bg-slate-50">
                                    <img v-if="ekskul.image" :src="`/storage/${ekskul.image}`" class="size-full object-cover" />
                                    <div v-else class="flex size-full items-center justify-center">
                                        <svg class="size-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                                        </svg>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ ekskul.name }}</td>
                            <td class="px-4 py-3 text-slate-500 hidden sm:table-cell max-w-xs truncate">{{ ekskul.description ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span :class="ekskul.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                    {{ ekskul.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit(ekskul)" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                    <button @click="confirmDelete(ekskul.id)" class="rounded-lg border border-red-100 px-2.5 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Edit -->
        <div v-if="editTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="editTarget = null">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">Edit Ekskul</h3>
                    <button @click="editTarget = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama <span class="text-red-500">*</span></label>
                        <input v-model="editForm.name" type="text" :class="INPUT_CLS" />
                        <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Deskripsi</label>
                        <textarea v-model="editForm.description" rows="3" :class="INPUT_CLS + ' resize-none'" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Foto</label>
                        <div class="flex items-start gap-3">
                            <div class="size-16 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                <img v-if="editPreview" :src="editPreview" class="size-full object-cover" />
                                <div v-else class="flex size-full items-center justify-center">
                                    <svg class="size-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                                    </svg>
                                </div>
                            </div>
                            <label class="mt-1 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Ganti Foto
                                <input type="file" accept="image/*" class="sr-only" @change="onEditImageChange" />
                            </label>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan</label>
                            <input v-model.number="editForm.sort_order" type="number" min="0" :class="INPUT_CLS" />
                        </div>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" v-model="editForm.is_active" class="size-4 rounded border-slate-300 accent-emerald-500" />
                            <span class="text-sm text-slate-700">Aktif</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 sm:col-span-2">
                        <button type="button" @click="editTarget = null" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="editForm.processing" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-60">
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div v-if="deleteId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="deleteId = null">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-bold text-slate-800">Hapus Ekskul?</h3>
                <p class="mt-2 text-sm text-slate-500">Data ekskul dan fotonya akan dihapus permanen.</p>
                <div class="mt-5 flex justify-end gap-2">
                    <button @click="deleteId = null" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button @click="doDelete" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Hapus</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
