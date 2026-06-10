<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subjects: { type: Array, required: true },
});

// ── Create ────────────────────────────────────────────────────────────────────
const showCreate = ref(false);
const createForm = useForm({ name: '' });

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
};

const submitCreate = () => {
    createForm.post(route('operator.subjects.store'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};

// ── Edit ──────────────────────────────────────────────────────────────────────
const editTarget = ref(null);
const editForm   = useForm({ name: '' });

const openEdit = (subject) => {
    editTarget.value = subject;
    editForm.name    = subject.name;
    editForm.clearErrors();
};

const submitEdit = () => {
    editForm.put(route('operator.subjects.update', editTarget.value.id), {
        onSuccess: () => { editTarget.value = null; },
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.subjects.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Mata Pelajaran" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Mata Pelajaran</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Mata Pelajaran</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Kelola daftar mata pelajaran. Mapel akan ditambahkan ke kelas di halaman detail kelas.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </button>
            </div>

            <!-- Empty state -->
            <div
                v-if="subjects.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada mata pelajaran</p>
                <p class="mt-1 text-xs text-slate-400">Tambah mata pelajaran terlebih dahulu, lalu masukkan ke kelas.</p>
                <button
                    @click="openCreate"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Mata Pelajaran
                </button>
            </div>

            <!-- Subject list -->
            <div v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <span class="tabular-nums text-xs text-slate-500">{{ subjects.length }} mata pelajaran</span>
                </div>
                <ul class="divide-y divide-slate-100">
                    <li
                        v-for="subject in subjects"
                        :key="subject.id"
                        class="flex items-center justify-between px-5 py-3 transition-[background-color] duration-150 hover:bg-slate-50"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                                <svg class="size-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-800">{{ subject.name }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                @click="openEdit(subject)"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                                aria-label="Edit mata pelajaran"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </button>
                            <button
                                @click="deleteTarget = subject"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500"
                                aria-label="Hapus mata pelajaran"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </li>
                </ul>
            </div>

        </div>

        <!-- ── Create Modal ────────────────────────────────────────────────────── -->
        <Modal :show="showCreate" max-width="sm" @close="showCreate = false">
            <form @submit.prevent="submitCreate">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-balance text-base font-bold text-slate-900">Tambah Mata Pelajaran</h3>
                    <button
                        type="button"
                        aria-label="Tutup modal"
                        @click="showCreate = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <label for="c-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Nama Mata Pelajaran <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="c-name"
                        v-model="createForm.name"
                        type="text"
                        placeholder="Contoh: Matematika"
                        :class="[
                            'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                            'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                            createForm.errors.name ? 'border-red-400' : 'border-slate-200',
                        ]"
                    />
                    <p v-if="createForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.name }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button
                        type="button"
                        @click="showCreate = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                    >
                        <svg v-if="createForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Edit Modal ──────────────────────────────────────────────────────── -->
        <Modal :show="!!editTarget" max-width="sm" @close="editTarget = null">
            <form @submit.prevent="submitEdit">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-balance text-base font-bold text-slate-900">Edit Mata Pelajaran</h3>
                    <button
                        type="button"
                        aria-label="Tutup modal"
                        @click="editTarget = null"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <label for="e-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Nama Mata Pelajaran <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="e-name"
                        v-model="editForm.name"
                        type="text"
                        :class="[
                            'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                            'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                            editForm.errors.name ? 'border-red-400' : 'border-slate-200',
                        ]"
                    />
                    <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button
                        type="button"
                        @click="editTarget = null"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                    >
                        <svg v-if="editForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ editForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Delete Confirm ──────────────────────────────────────────────────── -->
        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Mata Pelajaran</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Data komponen nilai yang terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="deleteTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                >
                    Batal
                </button>
                <button
                    @click="submitDelete"
                    :disabled="deleteForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60"
                >
                    <svg v-if="deleteForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
