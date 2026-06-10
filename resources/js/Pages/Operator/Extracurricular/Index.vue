<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    extracurriculars: { type: Array, default: () => [] },
});

// ── delete ────────────────────────────────────────────────────────────────────
const deleteId = ref(null);

const confirmDelete = (id) => { deleteId.value = id; };

const doDelete = () => {
    router.delete(route('operator.extracurriculars.destroy', deleteId.value), {
        onSuccess: () => { deleteId.value = null; },
    });
};
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
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Ekstrakulikuler</h2>
                    <p class="text-sm text-slate-500">Kelola daftar ekskul yang ditampilkan di halaman profil sekolah.</p>
                </div>
                <Link
                    :href="route('operator.extracurriculars.create')"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Ekskul
                </Link>
            </div>

            <!-- Mobile card list -->
            <div class="sm:hidden space-y-2">
                <div v-if="extracurriculars.length === 0" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center">
                    <p class="text-sm font-semibold text-slate-700">Belum ada ekskul</p>
                </div>
                <div
                    v-for="ekskul in extracurriculars"
                    :key="ekskul.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="flex items-start justify-between p-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="size-10 shrink-0 overflow-hidden rounded-lg border border-slate-100 bg-slate-50">
                                <img v-if="ekskul.image" :src="`/storage/${ekskul.image}`" class="size-full object-cover" />
                                <div v-else class="flex size-full items-center justify-center">
                                    <svg class="size-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ ekskul.name }}</p>
                                <span :class="ekskul.is_active ? 'bg-primary-50 text-primary-700' : 'bg-slate-100 text-slate-500'" class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold">
                                    {{ ekskul.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1 ml-2">
                            <Link :href="route('operator.extracurriculars.show', ekskul.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Lihat detail">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </Link>
                            <Link :href="route('operator.extracurriculars.edit', ekskul.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Edit ekskul">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                            </Link>
                            <button @click="confirmDelete(ekskul.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Hapus ekskul">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </div>
                    </div>
                    <div v-if="ekskul.description" class="border-t border-slate-100 px-4 py-2.5">
                        <p class="line-clamp-2 text-xs text-slate-400">{{ ekskul.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left">
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Foto</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500">Deskripsi</th>
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
                            <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ ekskul.description ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span :class="ekskul.is_active ? 'bg-primary-50 text-primary-700' : 'bg-slate-100 text-slate-500'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                    {{ ekskul.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('operator.extracurriculars.show', ekskul.id)" class="rounded-lg border border-primary-200 px-2.5 py-1.5 text-xs font-semibold text-primary-600 hover:bg-primary-50">Detail</Link>
                                    <Link :href="route('operator.extracurriculars.edit', ekskul.id)" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</Link>
                                    <button @click="confirmDelete(ekskul.id)" class="rounded-lg border border-red-100 px-2.5 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
