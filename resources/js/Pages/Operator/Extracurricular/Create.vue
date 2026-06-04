<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const imagePreview = ref(null);

const form = useForm({
    name:        '',
    description: '',
    coach:       '',
    schedule:    '',
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
    });
};

const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';
</script>

<template>
    <AppLayout>
        <Head title="Tambah Ekskul" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.extracurriculars.index')" class="hover:text-slate-700">Ekstrakulikuler</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tambah Ekskul</span>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <!-- Header -->
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-bold text-slate-800">Tambah Ekskul Baru</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Isi data ekstrakulikuler yang akan ditampilkan di halaman profil sekolah.</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5 px-6 py-5">

                    <!-- Nama -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Nama Ekskul <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Pramuka"
                            :class="INPUT_CLS"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Deskripsi</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Deskripsi singkat ekskul..."
                            :class="INPUT_CLS + ' resize-none'"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Pelatih & Jadwal -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Pelatih</label>
                            <input
                                v-model="form.coach"
                                type="text"
                                placeholder="Nama pelatih"
                                :class="INPUT_CLS"
                            />
                            <p v-if="form.errors.coach" class="mt-1 text-xs text-red-500">{{ form.errors.coach }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Jadwal Latihan</label>
                            <input
                                v-model="form.schedule"
                                type="text"
                                placeholder="Contoh: Setiap Selasa & Kamis, 14.00–16.00"
                                :class="INPUT_CLS"
                            />
                            <p v-if="form.errors.schedule" class="mt-1 text-xs text-red-500">{{ form.errors.schedule }}</p>
                        </div>
                    </div>

                    <!-- Foto -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Foto Ekskul</label>
                        <div class="flex items-start gap-4">
                            <!-- Preview -->
                            <div class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img v-if="imagePreview" :src="imagePreview" class="size-full object-cover" alt="Preview foto" />
                                <svg v-else class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <!-- Upload trigger -->
                            <div class="mt-1 space-y-1.5">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Pilih Foto
                                    <input type="file" accept="image/jpg,image/jpeg,image/png,image/webp" class="hidden" @change="onImageChange" />
                                </label>
                                <p class="text-xs text-slate-400">JPG, PNG, WebP — maks. 2MB</p>
                            </div>
                        </div>
                        <p v-if="form.errors.image" class="mt-1.5 text-xs text-red-500">{{ form.errors.image }}</p>
                    </div>

                    <!-- Urutan & Status -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan Tampil</label>
                            <input
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                :class="INPUT_CLS"
                            />
                            <p v-if="form.errors.sort_order" class="mt-1 text-xs text-red-500">{{ form.errors.sort_order }}</p>
                        </div>
                        <div class="flex items-end pb-2.5">
                            <label class="flex cursor-pointer items-center gap-2.5">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="size-4 rounded border-slate-300 accent-emerald-500"
                                />
                                <span class="text-sm text-slate-700">Aktif (tampil di halaman profil)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <Link
                            :href="route('operator.extracurriculars.index')"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-600 disabled:opacity-60"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Ekskul' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
