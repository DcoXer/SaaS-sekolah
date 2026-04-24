<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    letters:       { type: Array, required: true }, // surat keterangan milik siswa
    notifications: { type: Array, required: true }, // surat pemberitahuan
    templates:     { type: Array, required: true }, // template untuk request
});

// ── Tab ───────────────────────────────────────────────────────────────────────
const tab = ref('keterangan'); // 'keterangan' | 'notifikasi'

// ── Status config ─────────────────────────────────────────────────────────────
const statusConfig = {
    draft:            { label: 'Draft',              badge: 'bg-slate-100 text-slate-500 ring-slate-200' },
    waiting_approval: { label: 'Menunggu Persetujuan', badge: 'bg-amber-100 text-amber-700 ring-amber-200' },
    approved:         { label: 'Disetujui',           badge: 'bg-emerald-100 text-emerald-700 ring-emerald-200' },
    rejected:         { label: 'Ditolak',             badge: 'bg-red-100 text-red-700 ring-red-200' },
};

// ── Request letter modal ──────────────────────────────────────────────────────
const showRequest = ref(false);

const requestForm = useForm({
    letter_template_id: '',
});

const submitRequest = () => {
    requestForm.post(route('siswa.letters.store'), {
        onSuccess: () => {
            showRequest.value = false;
            requestForm.reset();
        },
    });
};

// ── Search ────────────────────────────────────────────────────────────────────
const searchLetter = ref('');
const filteredLetters = computed(() => {
    if (!searchLetter.value.trim()) return props.letters;
    const q = searchLetter.value.toLowerCase();
    return props.letters.filter(l => l.letter_template?.letter_type?.name?.toLowerCase().includes(q));
});

const formatDate = (val) => {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <AppLayout>
        <Head title="Surat" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Siswa</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Surat</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Heading + request button -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Surat</h2>
                    <p class="text-pretty text-sm text-slate-500">Permohonan surat keterangan dan notifikasi sekolah.</p>
                </div>
                <button
                    @click="showRequest = true"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Minta Surat
                </button>
            </div>

            <!-- Tabs -->
            <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                <button
                    @click="tab = 'keterangan'"
                    class="rounded-md px-4 py-1.5 text-sm font-semibold transition-[background-color,color] duration-150"
                    :class="tab === 'keterangan' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-700'"
                >
                    Surat Keterangan
                    <span
                        v-if="letters.length"
                        class="ml-1.5 inline-flex items-center rounded-full px-1.5 text-xs"
                        :class="tab === 'keterangan' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'"
                    >
                        {{ letters.length }}
                    </span>
                </button>
                <button
                    @click="tab = 'notifikasi'"
                    class="rounded-md px-4 py-1.5 text-sm font-semibold transition-[background-color,color] duration-150"
                    :class="tab === 'notifikasi' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-700'"
                >
                    Notifikasi
                    <span
                        v-if="notifications.length"
                        class="ml-1.5 inline-flex items-center rounded-full px-1.5 text-xs"
                        :class="tab === 'notifikasi' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'"
                    >
                        {{ notifications.length }}
                    </span>
                </button>
            </div>

            <!-- Surat Keterangan -->
            <template v-if="tab === 'keterangan'">
                <!-- Search -->
                <div v-if="letters.length > 0" class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        v-model="searchLetter"
                        type="search"
                        placeholder="Cari jenis surat..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-9 pr-3.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                    />
                </div>

                <div
                    v-if="letters.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
                >
                    <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-700">Belum ada surat</p>
                    <p class="mt-1 text-xs text-slate-400">Klik "Minta Surat" untuk mengajukan permohonan.</p>
                </div>

                <div
                    v-else-if="filteredLetters.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
                >
                    <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                    <button @click="searchLetter = ''" class="mt-2 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
                </div>

                <template v-else>

                <!-- mobile cards -->
                <div class="space-y-3 sm:hidden">
                    <div
                        v-for="letter in filteredLetters"
                        :key="letter.id"
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">
                                    {{ letter.letter_template?.letter_type?.name ?? '—' }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ formatDate(letter.created_at) }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                :class="statusConfig[letter.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                            >
                                {{ statusConfig[letter.status]?.label ?? letter.status }}
                            </span>
                        </div>
                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <Link
                                :href="route('siswa.letters.show', letter.id)"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Lihat Detail
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- desktop table -->
                <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Jenis Surat</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tanggal</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Status</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="letter in filteredLetters"
                                :key="letter.id"
                                class="transition-[background-color] duration-150 hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <span class="text-sm font-medium text-slate-800">
                                        {{ letter.letter_template?.letter_type?.name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="tabular-nums text-sm text-slate-600">{{ formatDate(letter.created_at) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                        :class="statusConfig[letter.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                    >
                                        {{ statusConfig[letter.status]?.label ?? letter.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="route('siswa.letters.show', letter.id)"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-50"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Lihat
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                </template>
            </template>

            <!-- Notifikasi -->
            <template v-else>
                <div
                    v-if="notifications.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
                >
                    <p class="text-sm font-semibold text-slate-700">Tidak ada notifikasi</p>
                    <p class="mt-1 text-xs text-slate-400">Belum ada surat pemberitahuan dari sekolah.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="rounded-xl border bg-white p-5 shadow-sm transition-[border-color] duration-150"
                        :class="notif.read_at ? 'border-slate-200' : 'border-amber-200 bg-amber-50/50'"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ notif.letter?.letter_template?.letter_type?.name ?? 'Pemberitahuan' }}
                                    </p>
                                    <span
                                        v-if="!notif.read_at"
                                        class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"
                                    >
                                        Baru
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">{{ formatDate(notif.created_at) }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-pretty text-sm text-slate-600 line-clamp-3">
                            {{ notif.letter?.content ? notif.letter.content.replace(/<[^>]*>/g, '') : '—' }}
                        </p>
                    </div>
                </div>
            </template>

        </div>

        <!-- ── Request Letter Modal ───────────────────────────────────────────── -->
        <Modal :show="showRequest" max-width="sm" @close="showRequest = false">
            <form @submit.prevent="submitRequest">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Permohonan Surat</h3>
                    <button type="button" aria-label="Tutup modal" @click="showRequest = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Jenis Surat <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="requestForm.letter_template_id"
                        :class="[
                            'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                            'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                            requestForm.errors.letter_template_id ? 'border-red-400' : 'border-slate-200',
                        ]"
                    >
                        <option value="" disabled>Pilih jenis surat...</option>
                        <option v-for="t in templates" :key="t.id" :value="t.id">
                            {{ t.letter_type?.name ?? t.name }}
                        </option>
                    </select>
                    <p v-if="requestForm.errors.letter_template_id" class="mt-1.5 text-xs text-red-500">
                        {{ requestForm.errors.letter_template_id }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="showRequest = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="requestForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60">
                        <svg v-if="requestForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ requestForm.processing ? 'Mengirim...' : 'Kirim Permohonan' }}
                    </button>
                </div>
            </form>
        </Modal>

    </AppLayout>
</template>
