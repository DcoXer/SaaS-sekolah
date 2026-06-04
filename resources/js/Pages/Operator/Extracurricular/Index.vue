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
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Ekstrakulikuler</h2>
                    <p class="text-sm text-slate-500">Kelola daftar ekskul yang ditampilkan di halaman profil sekolah.</p>
                </div>
                <Link
                    :href="route('operator.extracurriculars.create')"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Ekskul
                </Link>
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
                                    <Link :href="route('operator.extracurriculars.show', ekskul.id)" class="rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Detail</Link>
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
