<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import BackButton from '@/Components/BackButton.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subject: { type: Object, required: true },
});

// ── Edit form ─────────────────────────────────────────────────────────────────
const editForm = useForm({
    name:  props.subject.name,
    grade: props.subject.grade,
});

const submitEdit = () => {
    editForm.put(route('operator.subjects.update', props.subject.id));
};

// ── Delete ────────────────────────────────────────────────────────────────────
const showDelete = ref(false);
const deleteForm  = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.subjects.destroy', props.subject.id), {
        onSuccess: () => router.visit(route('operator.subjects.index')),
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="subject.name" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/operator/subjects" class="transition-[color] duration-150 hover:text-slate-700">Mata Pelajaran</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ subject.name }}</span>
            </div>
        </template>

        <div class="mx-auto max-w-md space-y-5">
            <BackButton href="/operator/subjects" />

            <!-- Header card -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-amber-50">
                        <svg class="size-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-balance text-base font-bold text-slate-900">{{ subject.name }}</h2>
                        <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                            Kelas {{ subject.grade }}
                        </span>
                    </div>
                </div>
                <button
                    @click="showDelete = true"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition-[background-color,border-color] duration-150 hover:bg-red-50"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Hapus
                </button>
            </div>

            <!-- Edit form -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Edit Mata Pelajaran</h3>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4 p-5">
                    <div>
                        <label for="e-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Nama Mata Pelajaran <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="e-name"
                            v-model="editForm.name"
                            type="text"
                            :class="[
                                'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                editForm.errors.name ? 'border-red-400' : 'border-slate-200',
                            ]"
                        />
                        <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                    </div>

                    <div>
                        <label for="e-grade" class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Tingkat Kelas <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="e-grade"
                            v-model="editForm.grade"
                            :class="[
                                'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                editForm.errors.grade ? 'border-red-400' : 'border-slate-200',
                            ]"
                        >
                            <option v-for="g in [1,2,3,4,5,6]" :key="g" :value="g">Kelas {{ g }}</option>
                        </select>
                        <p v-if="editForm.errors.grade" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.grade }}</p>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                        >
                            <svg v-if="editForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- ── Delete Confirm ──────────────────────────────────────────────────── -->
        <Modal :show="showDelete" max-width="sm" @close="showDelete = false">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Mata Pelajaran</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus <span class="font-semibold text-slate-700">{{ subject.name }}</span>?
                    Data komponen nilai yang terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="showDelete = false"
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
