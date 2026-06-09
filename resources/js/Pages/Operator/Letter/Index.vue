<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    letters:     { type: Array, required: true },
    letterTypes: { type: Array, required: true },
    templates:   { type: Array, required: true },
});

// ── Helpers ───────────────────────────────────────────────────────────────────
const statusLabel = {
    draft:            'Draft',
    waiting_approval: 'Menunggu Persetujuan',
    approved:         'Disetujui',
    rejected:         'Ditolak',
    published:        'Diterbitkan',
};
const statusColor = {
    draft:            'bg-slate-100 text-slate-600',
    waiting_approval: 'bg-amber-100 text-amber-700',
    approved:         'bg-emerald-100 text-emerald-700',
    rejected:         'bg-red-100 text-red-700',
    published:        'bg-blue-100 text-blue-700',
};
const categoryLabel = { keterangan: 'Keterangan', pemberitahuan: 'Pemberitahuan' };
const categoryColor = {
    keterangan:    'bg-blue-50 text-blue-600',
    pemberitahuan: 'bg-violet-50 text-violet-600',
};

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// ── Search & Filter ───────────────────────────────────────────────────────────
const search         = ref('');
const filterStatus   = ref('');
const filterCategory = ref('');

const filtered = computed(() => {
    let list = props.letters;
    if (filterStatus.value)   list = list.filter(l => l.status === filterStatus.value);
    if (filterCategory.value) list = list.filter(l => l.category === filterCategory.value);
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(l =>
            (l.letter_template?.letter_type?.name && l.letter_template.letter_type.name.toLowerCase().includes(q)) ||
            (l.student?.name && l.student.name.toLowerCase().includes(q))
        );
    }
    return list;
});

const PER_PAGE    = 15;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch([search, filterStatus, filterCategory], () => { currentPage.value = 1; });

// ── Pemberitahuan templates only ──────────────────────────────────────────────
const notifTemplates = computed(() =>
    props.templates.filter(t => t.letter_type?.category === 'pemberitahuan')
);

// ── Create Notification ───────────────────────────────────────────────────────
const showCreate = ref(false);

const createForm = useForm({
    letter_template_id: '',
    content:            '',
    target_grade:       '',
});

const selectedTemplate = computed(() =>
    notifTemplates.value.find(t => t.id == createForm.letter_template_id)
);

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
};

const onTemplateChange = () => {
    if (selectedTemplate.value) {
        createForm.content = selectedTemplate.value.content;
    }
};

const submitCreate = () => {
    createForm.post(route('operator.letters.store-notification'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};

// ── Submit for approval ───────────────────────────────────────────────────────
const submitTarget = ref(null);
const submitForm   = useForm({});

const confirmSubmit = (letter) => { submitTarget.value = letter; };
const doSubmit = () => {
    submitForm.patch(route('operator.letters.submit', submitTarget.value.id), {
        onSuccess: () => { submitTarget.value = null; },
    });
};

// ── View letter content ───────────────────────────────────────────────────────
const viewTarget = ref(null);

// ── Create modal select options ───────────────────────────────────────────────
const templateOptions = computed(() =>
    notifTemplates.value.map(t => ({ value: t.id, label: t.name }))
);
const targetGradeOptions = [
    { value: '', label: 'Semua Kelas' },
    ...([1, 2, 3, 4, 5, 6].map(g => ({ value: g, label: `Kelas ${g}` }))),
];
</script>

<template>
    <AppLayout>
        <Head title="Surat" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Surat</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Surat</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Kelola surat keterangan dan kirim surat pemberitahuan kepada wali murid.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span class="hidden sm:inline">Kirim Pemberitahuan</span>
                    <span class="sm:hidden">Kirim</span>
                </button>
            </div>

            <!-- Search & Filter -->
            <div v-if="letters.length > 0" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari jenis surat, nama siswa..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
                    />
                </div>
                <FilterSelect
                    v-model="filterCategory"
                    :options="[
                        { value: '', label: 'Semua Kategori' },
                        { value: 'keterangan', label: 'Keterangan' },
                        { value: 'pemberitahuan', label: 'Pemberitahuan' },
                    ]"
                />
                <FilterSelect
                    v-model="filterStatus"
                    :options="[
                        { value: '', label: 'Semua Status' },
                        { value: 'draft', label: 'Draft' },
                        { value: 'waiting_approval', label: 'Menunggu Persetujuan' },
                        { value: 'approved', label: 'Disetujui' },
                        { value: 'rejected', label: 'Ditolak' },
                        { value: 'published', label: 'Diterbitkan' },
                    ]"
                />
            </div>

            <!-- Empty state -->
            <div
                v-if="letters.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada surat</p>
                <p class="mt-1 text-xs text-slate-400">Surat keterangan dari wali murid akan muncul di sini.</p>
            </div>

            <!-- No results -->
            <div
                v-else-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau hapus filter.</p>
                <button @click="search = ''; filterStatus = ''; filterCategory = ''" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
            </div>

            <!-- Letters list -->
            <template v-else>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <ul class="divide-y divide-slate-100">
                    <li
                        v-for="letter in paginated"
                        :key="letter.id"
                        class="flex items-start gap-4 px-5 py-4 transition-[background-color] duration-150 hover:bg-slate-50"
                    >
                        <!-- Icon -->
                        <div :class="['mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg', categoryColor[letter.category]]">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path v-if="letter.category === 'pemberitahuan'" stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>

                        <!-- Info -->
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-slate-800">
                                    {{ letter.letter_template?.letter_type?.name ?? '—' }}
                                </span>
                                <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', statusColor[letter.status]]">
                                    {{ statusLabel[letter.status] }}
                                </span>
                                <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', categoryColor[letter.category]]">
                                    {{ categoryLabel[letter.category] }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-400">
                                <span v-if="letter.student">
                                    Siswa: <span class="font-medium text-slate-600">{{ letter.student.name }}</span>
                                </span>
                                <span v-if="letter.target_grade">
                                    Target: <span class="font-medium text-slate-600">Kelas {{ letter.target_grade }}</span>
                                </span>
                                <span>
                                    Dibuat: {{ formatDate(letter.created_at) }}
                                </span>
                                <span v-if="letter.published_at">
                                    Dikirim: {{ formatDate(letter.published_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                @click="viewTarget = letter"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                                aria-label="Lihat konten"
                                title="Lihat konten"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                            <button
                                v-if="letter.status === 'draft'"
                                @click="confirmSubmit(letter)"
                                class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition-[background-color] duration-150 hover:bg-amber-200"
                                title="Ajukan ke Kamad"
                            >
                                Ajukan
                            </button>
                            <a
                                v-if="letter.status === 'approved'"
                                :href="route('operator.letters.pdf', letter.id)"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-500 px-2.5 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600"
                                title="Unduh PDF"
                                aria-label="Unduh PDF surat"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                PDF
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
            <Pagination
                v-if="totalPages > 1"
                :current-page="currentPage"
                :total-pages="totalPages"
                :total="filtered.length"
                :per-page="PER_PAGE"
                label="surat"
                @update:current-page="currentPage = $event"
            />
            </template>

        </div>

        <!-- ── Create Notification Modal ──────────────────────────────────────── -->
        <Modal :show="showCreate" max-width="2xl" @close="showCreate = false">
            <form @submit.prevent="submitCreate">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Kirim Surat Pemberitahuan</h3>
                    <button type="button" aria-label="Tutup modal" @click="showCreate = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Template -->
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Template Surat <span class="text-red-500">*</span></label>
                            <FilterSelect
                                v-model="createForm.letter_template_id"
                                :options="[{ value: '', label: 'Pilih template' }, ...templateOptions]"
                                :has-error="!!createForm.errors.letter_template_id"
                                @change="onTemplateChange"
                                block
                            />
                            <p v-if="createForm.errors.letter_template_id" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.letter_template_id }}</p>
                        </div>
                        <!-- Target grade -->
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Target Kelas (opsional)</label>
                            <FilterSelect
                                v-model="createForm.target_grade"
                                :options="targetGradeOptions"
                                block
                            />
                        </div>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Konten Surat <span class="text-red-500">*</span></label>
                        <textarea v-model="createForm.content" rows="12"
                            placeholder="Pilih template terlebih dahulu, atau tulis langsung..."
                            :class="['w-full resize-y rounded-lg border bg-white px-3.5 py-2.5 font-mono text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.content ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.content" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.content }}</p>
                    </div>

                    <div class="rounded-lg bg-blue-50 px-4 py-3 text-xs text-blue-700">
                        Surat pemberitahuan akan langsung <span class="font-semibold">diterbitkan</span> dan dikirim ke wali murid sesuai kelas target.
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="showCreate = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="createForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60">
                        <svg v-if="createForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ createForm.processing ? 'Mengirim...' : 'Kirim Sekarang' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Submit for Approval Confirm ────────────────────────────────────── -->
        <Modal :show="!!submitTarget" max-width="sm" @close="submitTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-amber-100">
                    <svg class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Ajukan ke Kamad</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    Surat ini akan diajukan untuk persetujuan Kepala Madrasah. Status akan berubah menjadi
                    <span class="font-semibold text-amber-700">Menunggu Persetujuan</span>.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="submitTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                    Batal
                </button>
                <button @click="doSubmit" :disabled="submitForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-amber-600 disabled:opacity-60">
                    <svg v-if="submitForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ submitForm.processing ? 'Mengajukan...' : 'Ya, Ajukan' }}
                </button>
            </div>
        </Modal>

        <!-- ── View Letter Content ────────────────────────────────────────────── -->
        <Modal :show="!!viewTarget" max-width="2xl" @close="viewTarget = null">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ viewTarget?.letter_template?.letter_type?.name ?? 'Surat' }}</h3>
                    <div class="mt-0.5 flex items-center gap-2">
                        <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', statusColor[viewTarget?.status]]">
                            {{ statusLabel[viewTarget?.status] }}
                        </span>
                        <span v-if="viewTarget?.student" class="text-xs text-slate-400">
                            Siswa: {{ viewTarget.student.name }}
                        </span>
                    </div>
                </div>
                <button type="button" aria-label="Tutup modal" @click="viewTarget = null"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <pre class="whitespace-pre-wrap rounded-lg bg-slate-50 p-4 font-mono text-sm text-slate-700">{{ viewTarget?.content }}</pre>
                <div v-if="viewTarget?.rejection_note" class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="text-xs font-semibold text-red-700">Alasan Penolakan:</p>
                    <p class="mt-0.5 text-sm text-red-600">{{ viewTarget.rejection_note }}</p>
                </div>
            </div>
            <div class="flex items-center justify-end border-t border-slate-100 px-6 py-4">
                <button type="button" @click="viewTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                    Tutup
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
