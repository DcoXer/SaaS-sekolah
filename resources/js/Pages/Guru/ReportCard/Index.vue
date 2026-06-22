<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    reportCards: { type: Array,  required: true },
    classroom:   { type: Object, default: null },
    activeYear:  { type: Object, default: null },
    semester:    { type: Number, default: 1 },
});

// ── Status config ─────────────────────────────────────────────────────────────
const statusConfig = {
    draft:            { label: 'Draft',            badge: 'bg-slate-100 text-slate-600 ring-slate-200' },
    waiting_approval: { label: 'Menunggu Kamad',   badge: 'bg-amber-100 text-amber-700 ring-amber-200' },
    approved:         { label: 'Disetujui',        badge: 'bg-emerald-100 text-emerald-700 ring-emerald-200' },
};

// ── Semester tab ──────────────────────────────────────────────────────────────
const switchSemester = (sem) => {
    router.get(route('guru.report-cards.index'), { semester: sem }, {
        preserveState: false,
        replace: true,
    });
};

// ── Search & filter ───────────────────────────────────────────────────────────
const search       = ref('');
const filterStatus = ref('');

const filtered = computed(() => {
    let data = props.reportCards;

    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        data = data.filter(c => c.student?.name?.toLowerCase().includes(q));
    }

    if (filterStatus.value) {
        data = data.filter(c => c.status === filterStatus.value);
    }

    return data;
});

// ── Pagination ────────────────────────────────────────────────────────────────
const PER_PAGE   = 15;
const currentPage = ref(1);

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / PER_PAGE)));

// Reset page saat filter berubah
const resetPage = () => { currentPage.value = 1; };

const paginated = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

// ── Notes modal ───────────────────────────────────────────────────────────────
const showModal    = ref(false);
const selectedCard = ref(null);

const notesForm = useForm({
    homeroom_notes: '',
});

const openNotes = (card) => {
    selectedCard.value = card;
    notesForm.homeroom_notes = card.notes?.homeroom_notes ?? '';
    notesForm.clearErrors();
    showModal.value = true;
};

const submitNotes = () => {
    notesForm.patch(route('guru.report-cards.update-notes', selectedCard.value.id), {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Raport" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Guru</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Raport</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Page heading -->
            <div>
                <h2 class="text-lg font-bold text-slate-900">Raport</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    <span v-if="classroom">
                        Kelas wali: <strong class="text-slate-700">{{ classroom.name }}</strong>
                        · {{ activeYear?.name }}
                    </span>
                    <span v-else>Anda tidak menjadi wali kelas di tahun ajaran aktif.</span>
                </p>
            </div>

            <!-- No active year / no classroom -->
            <div
                v-if="!activeYear || !classroom"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">
                    {{ !activeYear ? 'Tidak ada tahun ajaran aktif' : 'Bukan wali kelas' }}
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    {{ !activeYear ? 'Aktivasi tahun ajaran terlebih dahulu.' : 'Anda tidak ditugaskan sebagai wali kelas saat ini.' }}
                </p>
            </div>

            <template v-else>

                <!-- ── Semester tabs ──────────────────────────────────────────── -->
                <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm w-fit">
                    <button
                        v-for="sem in [1, 2]"
                        :key="sem"
                        @click="switchSemester(sem)"
                        class="rounded-lg px-4 py-1.5 text-sm font-semibold transition-[background-color,color] duration-150"
                        :class="semester === sem
                            ? 'bg-primary-500 text-white shadow-sm'
                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'"
                    >
                        Semester {{ sem }}
                    </button>
                </div>

                <!-- ── Toolbar: search + filter ───────────────────────────────── -->
                <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                    <!-- Search -->
                    <div class="relative flex-1 min-w-[180px]">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input
                            v-model="search"
                            @input="resetPage"
                            type="search"
                            placeholder="Cari nama siswa..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-400/20"
                        />
                    </div>

                    <div class="h-5 w-px bg-slate-200"/>

                    <!-- Status filter -->
                    <FilterSelect
                        v-model="filterStatus"
                        @change="resetPage"
                        :options="[
                            { value: '', label: 'Semua Status' },
                            { value: 'draft', label: 'Draft' },
                            { value: 'waiting_approval', label: 'Menunggu Kamad' },
                            { value: 'approved', label: 'Disetujui' },
                        ]"
                    />

                    <!-- Result count -->
                    <span class="shrink-0 text-sm text-slate-400">{{ filtered.length }} raport</span>
                </div>

                <!-- No report cards yet -->
                <div
                    v-if="reportCards.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-14 text-center"
                >
                    <p class="text-sm font-semibold text-slate-700">Raport belum digenerate</p>
                    <p class="mt-1 text-xs text-slate-400">Minta Kamad untuk generate raport kelas ini.</p>
                </div>

                <!-- No filter result -->
                <div
                    v-else-if="filtered.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-14 text-center"
                >
                    <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                    <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau filter status.</p>
                </div>

                <template v-else>

                    <!-- mobile cards (< sm) -->
                    <div class="space-y-3 sm:hidden">
                        <div
                            v-for="(card, i) in paginated"
                            :key="card.id"
                            class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ card.student?.name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">Semester {{ card.semester }}</p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                    :class="statusConfig[card.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                >
                                    {{ statusConfig[card.status]?.label ?? card.status }}
                                </span>
                            </div>
                            <p v-if="card.notes?.homeroom_notes" class="mt-2 text-xs italic text-slate-500 line-clamp-2">
                                "{{ card.notes.homeroom_notes }}"
                            </p>
                            <p v-else class="mt-2 text-xs italic text-slate-400">Belum ada catatan wali kelas</p>
                            <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                                <button
                                    @click="openNotes(card)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition-[background-color] duration-150 hover:bg-slate-50 active:bg-slate-100"
                                >
                                    <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                    Catatan
                                </button>
                                <a
                                    :href="route('guru.report-cards.export', card.id)"
                                    target="_blank"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-primary-200 bg-primary-50 px-3 py-2 text-xs font-semibold text-primary-700 shadow-sm transition-[background-color] duration-150 hover:bg-primary-100 active:bg-primary-200"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Export PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- desktop table (>= sm) -->
                    <div class="hidden sm:block overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-slate-500 lg:px-5 w-8">#</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-slate-500 lg:px-5">Nama Siswa</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-slate-500 lg:px-5">Sem.</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-slate-500 lg:px-5">Status</th>
                                    <th class="hidden px-5 py-3.5 text-left text-xs font-semibold text-slate-500 lg:table-cell">Catatan Wali Kelas</th>
                                    <th class="px-3 py-3.5 text-right text-xs font-semibold text-slate-500 lg:px-5 w-24 lg:w-auto">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="(card, i) in paginated"
                                    :key="card.id"
                                    class="transition-[background-color] duration-150 hover:bg-slate-50"
                                >
                                    <td class="px-3 py-4 text-xs text-slate-400 tabular-nums lg:px-5">
                                        {{ (currentPage - 1) * PER_PAGE + i + 1 }}
                                    </td>
                                    <td class="px-3 py-4 lg:px-5">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-slate-800">{{ card.student?.name }}</p>
                                            <p v-if="card.notes?.homeroom_notes" class="mt-0.5 truncate text-xs italic text-slate-400 lg:hidden">
                                                "{{ card.notes.homeroom_notes }}"
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 lg:px-5">
                                        <span class="text-sm text-slate-600">{{ card.semester }}</span>
                                    </td>
                                    <td class="px-3 py-4 lg:px-5">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                            :class="statusConfig[card.status]?.badge ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                        >
                                            {{ statusConfig[card.status]?.label ?? card.status }}
                                        </span>
                                    </td>
                                    <td class="hidden px-5 py-4 max-w-xs lg:table-cell">
                                        <span class="text-pretty text-sm text-slate-500 line-clamp-2">
                                            {{ card.notes?.homeroom_notes ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 lg:px-5">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Catatan — icon only sm–lg, full on lg+ -->
                                            <button
                                                @click="openNotes(card)"
                                                title="Catatan"
                                                class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition-[background-color] duration-150 hover:bg-slate-50 size-8 lg:size-auto lg:gap-1.5 lg:px-3 lg:py-1.5 lg:text-xs lg:font-semibold"
                                            >
                                                <svg class="shrink-0 size-4 text-slate-400 lg:size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                                <span class="hidden lg:inline">Catatan</span>
                                            </button>
                                            <!-- Export PDF — icon only sm–lg, full on lg+ -->
                                            <a
                                                :href="route('guru.report-cards.export', card.id)"
                                                target="_blank"
                                                title="Export PDF"
                                                class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-primary-200 bg-primary-50 text-primary-700 shadow-sm transition-[background-color] duration-150 hover:bg-primary-100 size-8 lg:size-auto lg:gap-1.5 lg:px-3 lg:py-1.5 lg:text-xs lg:font-semibold"
                                            >
                                                <svg class="shrink-0 size-4 lg:size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                                <span class="hidden lg:inline">Export PDF</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── Pagination ────────────────────────────────────────── -->
                    <div
                        v-if="totalPages > 1"
                        class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm"
                    >
                        <!-- Info -->
                        <p class="text-xs text-slate-500">
                            {{ (currentPage - 1) * PER_PAGE + 1 }}–{{ Math.min(currentPage * PER_PAGE, filtered.length) }}
                            dari <strong class="text-slate-700">{{ filtered.length }}</strong> raport
                        </p>

                        <!-- Controls -->
                        <div class="flex items-center gap-1">
                            <!-- Prev -->
                            <button
                                @click="prevPage"
                                :disabled="currentPage === 1"
                                class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition-[background-color,opacity] duration-150 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                                aria-label="Halaman sebelumnya"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Page numbers -->
                            <template v-for="page in totalPages" :key="page">
                                <button
                                    v-if="page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1"
                                    @click="currentPage = page"
                                    class="flex size-8 items-center justify-center rounded-lg text-xs font-semibold transition-[background-color,color] duration-150"
                                    :class="currentPage === page
                                        ? 'bg-primary-500 text-white shadow-sm'
                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                >
                                    {{ page }}
                                </button>
                                <span
                                    v-else-if="page === currentPage - 2 || page === currentPage + 2"
                                    class="flex size-8 items-center justify-center text-xs text-slate-400"
                                >…</span>
                            </template>

                            <!-- Next -->
                            <button
                                @click="nextPage"
                                :disabled="currentPage === totalPages"
                                class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition-[background-color,opacity] duration-150 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                                aria-label="Halaman berikutnya"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </template>
            </template>

        </div>

        <!-- ── Notes Modal ────────────────────────────────────────────────────── -->
        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form @submit.prevent="submitNotes">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-slate-900">Catatan Raport</h3>
                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ selectedCard?.student?.name }}</p>
                    </div>
                    <button type="button" @click="showModal = false"
                        class="ml-3 flex size-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-4 py-5 sm:px-6">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Catatan Wali Kelas</label>
                        <textarea
                            v-model="notesForm.homeroom_notes"
                            rows="4"
                            placeholder="Tulis catatan wali kelas..."
                            class="w-full resize-none rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20"
                        />
                        <p v-if="notesForm.errors.homeroom_notes" class="mt-1 text-xs text-red-500">
                            {{ notesForm.errors.homeroom_notes }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Catatan Kepala Madrasah
                            <span class="ml-1 font-normal text-slate-400">(diisi oleh Kamad)</span>
                        </label>
                        <textarea
                            :value="selectedCard?.notes?.principal_notes ?? ''"
                            rows="3"
                            placeholder="Diisi oleh Kepala Madrasah..."
                            readonly
                            disabled
                            class="w-full cursor-not-allowed resize-none rounded-lg border border-slate-100 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-400 placeholder-slate-300 outline-none"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-4 py-4 sm:px-6">
                    <button type="button" @click="showModal = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="notesForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60">
                        <svg v-if="notesForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ notesForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

    </AppLayout>
</template>
