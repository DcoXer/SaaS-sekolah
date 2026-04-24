<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    academicYears: { type: Array, required: true },
});

// ── Modal ────────────────────────────────────────────────────────────────────
const showModal = ref(false);

const form = useForm({
    name:       '',
    start_date: '',
    end_date:   '',
});

const submit = () => {
    form.post(route('operator.academic-years.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const openModal = () => {
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

// ── Helpers ──────────────────────────────────────────────────────────────────
const statusConfig = {
    pending: { label: 'Menunggu Persetujuan', badge: 'bg-amber-100 text-amber-700 ring-amber-200' },
    active:  { label: 'Aktif',                badge: 'bg-emerald-100 text-emerald-700 ring-emerald-200' },
    closed:  { label: 'Ditutup',              badge: 'bg-slate-100 text-slate-500 ring-slate-200' },
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <AppLayout>
        <Head title="Tahun Ajaran" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Tahun Ajaran</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Page heading -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Tahun Ajaran</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Buat tahun ajaran baru. Aktivasi dilakukan oleh Kepala Madrasah.
                    </p>
                </div>
                <button
                    @click="openModal"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </button>
            </div>

            <!-- Empty state -->
            <div
                v-if="academicYears.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada tahun ajaran</p>
                <p class="mt-1 text-xs text-slate-400">Buat tahun ajaran pertama untuk memulai.</p>
                <button
                    @click="openModal"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Tahun Ajaran
                </button>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Nama</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Mulai</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Selesai</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="year in academicYears"
                            :key="year.id"
                            class="transition-[background-color] duration-150 hover:bg-slate-50"
                        >
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-slate-800">{{ year.name }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="tabular-nums text-sm text-slate-600">{{ formatDate(year.start_date) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="tabular-nums text-sm text-slate-600">{{ formatDate(year.end_date) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                    :class="statusConfig[year.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                >
                                    {{ statusConfig[year.status]?.label ?? year.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- ── Create Modal ────────────────────────────────────────────────────── -->
        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-balance text-base font-bold text-slate-900">Tambah Tahun Ajaran</h3>
                    <button
                        type="button"
                        aria-label="Tutup modal"
                        @click="showModal = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="space-y-4 px-6 py-5">

                    <!-- Nama -->
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Nama Tahun Ajaran <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: 2024/2025"
                            maxlength="20"
                            :class="[
                                'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                form.errors.name ? 'border-red-400' : 'border-slate-200',
                            ]"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="start_date" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Tanggal Mulai <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    form.errors.start_date ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="form.errors.start_date" class="mt-1.5 text-xs text-red-500">{{ form.errors.start_date }}</p>
                        </div>
                        <div>
                            <label for="end_date" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Tanggal Selesai <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                                :min="form.start_date || undefined"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    form.errors.end_date ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="form.errors.end_date" class="mt-1.5 text-xs text-red-500">{{ form.errors.end_date }}</p>
                        </div>
                    </div>

                    <!-- Info note -->
                    <div class="flex items-start gap-2 rounded-lg bg-amber-50 px-3.5 py-3">
                        <svg class="mt-0.5 size-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <p class="text-pretty text-xs text-amber-700">
                            Setelah dibuat, tahun ajaran akan berstatus <strong>Menunggu Persetujuan</strong> hingga disetujui oleh Kepala Madrasah.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                    >
                        <svg
                            v-if="form.processing"
                            class="size-4 animate-spin"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

    </AppLayout>
</template>
