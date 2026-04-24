<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    components:   { type: Array,  required: true },
    academicYears:{ type: Array,  required: true },
    classrooms:   { type: Array,  required: true },
    subjects:     { type: Array,  required: true },
});

// ── Filter ────────────────────────────────────────────────────────────────────
const selectedClassroom = ref(
    new URLSearchParams(window.location.search).get('classroom_id') || (props.classrooms[0]?.id ?? '')
);
const selectedSemester = ref(
    Number(new URLSearchParams(window.location.search).get('semester') || 1)
);

const applyFilter = () => {
    router.get(
        route('operator.assessment-components.index'),
        { classroom_id: selectedClassroom.value, semester: selectedSemester.value },
        { preserveState: true, replace: true }
    );
};

watch([selectedClassroom, selectedSemester], applyFilter);

// ── Group by subject ──────────────────────────────────────────────────────────
const grouped = computed(() => {
    const map = {};
    for (const c of props.components) {
        const key = c.subject_id;
        if (!map[key]) map[key] = { subject: c.subject, items: [] };
        map[key].items.push(c);
    }
    return Object.values(map);
});

// ── Active year (first active) ────────────────────────────────────────────────
const activeYear = computed(() => props.academicYears.find(y => y.status === 'active'));

const currentClassroom = computed(() => props.classrooms.find(c => c.id == selectedClassroom.value));

// ── Filtered subjects (by classroom grade) ────────────────────────────────────
const filteredSubjects = computed(() => {
    if (!currentClassroom.value) return props.subjects;
    return props.subjects.filter(s => s.grade === currentClassroom.value.grade);
});

// ── Create ────────────────────────────────────────────────────────────────────
const showCreate = ref(false);

const createForm = useForm({
    academic_year_id: activeYear.value?.id ?? '',
    classroom_id:     selectedClassroom.value,
    subject_id:       '',
    name:             '',
    type:             'numeric',
    ki:               'ki3',
    weight:           '',
    min_score:        0,
    max_score:        100,
    order:            0,
    semester:         selectedSemester.value,
});

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    createForm.academic_year_id = activeYear.value?.id ?? '';
    createForm.classroom_id     = selectedClassroom.value;
    createForm.semester         = selectedSemester.value;
    createForm.type             = 'numeric';
    createForm.ki               = 'ki3';
    createForm.min_score        = 0;
    createForm.max_score        = 100;
    createForm.order            = 0;
    showCreate.value = true;
};

const submitCreate = () => {
    createForm.post(route('operator.assessment-components.store'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};

// ── Edit ──────────────────────────────────────────────────────────────────────
const editTarget = ref(null);

const editForm = useForm({
    name:      '',
    type:      'numeric',
    ki:        'ki3',
    weight:    '',
    min_score: 0,
    max_score: 100,
    order:     0,
});

const openEdit = (comp) => {
    editTarget.value = comp;
    editForm.name      = comp.name;
    editForm.type      = comp.type;
    editForm.ki        = comp.ki ?? 'ki3';
    editForm.weight    = comp.weight ?? '';
    editForm.min_score = comp.min_score ?? 0;
    editForm.max_score = comp.max_score ?? 100;
    editForm.order     = comp.order ?? 0;
    editForm.clearErrors();
};

const submitEdit = () => {
    editForm.put(route('operator.assessment-components.update', editTarget.value.id), {
        onSuccess: () => { editTarget.value = null; },
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.assessment-components.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const typeLabel = { numeric: 'Numerik', predicate: 'Predikat', narrative: 'Narasi' };
const typeColor = {
    numeric:   'bg-blue-100 text-blue-700',
    predicate: 'bg-violet-100 text-violet-700',
    narrative: 'bg-amber-100 text-amber-700',
};
const kiLabel = { ki3: 'KI 3', ki4: 'KI 4' };
const kiColor = { ki3: 'bg-sky-100 text-sky-700', ki4: 'bg-teal-100 text-teal-700' };
</script>

<template>
    <AppLayout>
        <Head title="Komponen Nilai" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Komponen Nilai</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Komponen Penilaian</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Konfigurasi komponen nilai per kelas dan mata pelajaran.
                    </p>
                </div>
                <button
                    v-if="activeYear && currentClassroom"
                    @click="openCreate"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </button>
            </div>

            <!-- Filter bar -->
            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500">Kelas:</label>
                    <select
                        v-model="selectedClassroom"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                    >
                        <option v-for="cls in classrooms" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500">Semester:</label>
                    <select
                        v-model="selectedSemester"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                    >
                        <option :value="1">Semester 1</option>
                        <option :value="2">Semester 2</option>
                    </select>
                </div>
            </div>

            <!-- No active year -->
            <div
                v-if="!activeYear"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
                <p class="mt-1 text-xs text-slate-400">Aktifkan tahun ajaran terlebih dahulu.</p>
            </div>

            <!-- Empty state -->
            <div
                v-else-if="grouped.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada komponen penilaian</p>
                <p class="mt-1 text-xs text-slate-400">Tambah komponen nilai untuk kelas dan semester ini.</p>
                <button
                    @click="openCreate"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Komponen
                </button>
            </div>

            <!-- Grouped by subject -->
            <div v-else class="space-y-4">
                <div
                    v-for="group in grouped"
                    :key="group.subject.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <!-- Subject header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-violet-50">
                                <svg class="size-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-slate-800">{{ group.subject.name }}</span>
                            <span class="tabular-nums text-xs text-slate-400">{{ group.items.length }} komponen</span>
                        </div>
                    </div>

                    <!-- Component rows -->
                    <ul class="divide-y divide-slate-100">
                        <li
                            v-for="comp in group.items"
                            :key="comp.id"
                            class="flex items-center justify-between px-5 py-3 transition-[background-color] duration-150 hover:bg-slate-50"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-slate-800">{{ comp.name }}</span>
                                        <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', typeColor[comp.type]]">
                                            {{ typeLabel[comp.type] }}
                                        </span>
                                        <span v-if="comp.ki" :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', kiColor[comp.ki]]">
                                            {{ kiLabel[comp.ki] }}
                                        </span>
                                    </div>
                                    <div class="mt-0.5 flex items-center gap-3 text-xs text-slate-400">
                                        <span v-if="comp.type === 'numeric'">Bobot: <span class="font-semibold text-slate-600">{{ comp.weight }}%</span></span>
                                        <span>Rentang: {{ comp.min_score }}–{{ comp.max_score }}</span>
                                        <span>Urutan: {{ comp.order }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    @click="openEdit(comp)"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                                    aria-label="Edit komponen"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteTarget = comp"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500"
                                    aria-label="Hapus komponen"
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

        </div>

        <!-- ── Create Modal ────────────────────────────────────────────────────── -->
        <Modal :show="showCreate" max-width="lg" @close="showCreate = false">
            <form @submit.prevent="submitCreate">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Tambah Komponen Penilaian</h3>
                    <button type="button" @click="showCreate = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <!-- Mata pelajaran -->
                    <div class="col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select v-model="createForm.subject_id"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.subject_id ? 'border-red-400' : 'border-slate-200']">
                            <option value="" disabled>Pilih mata pelajaran</option>
                            <option v-for="s in filteredSubjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <p v-if="createForm.errors.subject_id" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.subject_id }}</p>
                    </div>

                    <!-- Nama komponen -->
                    <div class="col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Komponen <span class="text-red-500">*</span></label>
                        <input v-model="createForm.name" type="text" placeholder="Contoh: Ulangan Harian 1"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.name ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.name }}</p>
                    </div>

                    <!-- Tipe + KI -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tipe <span class="text-red-500">*</span></label>
                        <select v-model="createForm.type"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', createForm.errors.type ? 'border-red-400' : 'border-slate-200']">
                            <option value="numeric">Numerik</option>
                            <option value="predicate">Predikat</option>
                            <option value="narrative">Narasi</option>
                        </select>
                        <p v-if="createForm.errors.type" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.type }}</p>
                    </div>

                    <!-- KI (hanya untuk numeric) -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Aspek Penilaian
                            <span v-if="createForm.type === 'numeric'" class="text-red-500">*</span>
                        </label>
                        <select v-model="createForm.ki"
                            :disabled="createForm.type !== 'numeric'"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 disabled:bg-slate-50 disabled:text-slate-400', createForm.errors.ki ? 'border-red-400' : 'border-slate-200']">
                            <option value="ki3">Pengetahuan (KI 3)</option>
                            <option value="ki4">Keterampilan (KI 4)</option>
                        </select>
                        <p v-if="createForm.errors.ki" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.ki }}</p>
                    </div>

                    <!-- Bobot (only if numeric) -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Bobot (%)
                            <span v-if="createForm.type === 'numeric'" class="text-red-500">*</span>
                        </label>
                        <input v-model.number="createForm.weight" type="number" min="1" max="100"
                            :disabled="createForm.type !== 'numeric'"
                            :placeholder="createForm.type !== 'numeric' ? 'Tidak dipakai' : '0-100'"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 disabled:bg-slate-50 disabled:text-slate-400', createForm.errors.weight ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="createForm.errors.weight" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.weight }}</p>
                    </div>

                    <!-- Min score -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nilai Minimum</label>
                        <input v-model.number="createForm.min_score" type="number" min="0" max="100"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', 'border-slate-200']" />
                    </div>

                    <!-- Max score -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nilai Maksimum</label>
                        <input v-model.number="createForm.max_score" type="number" min="0" max="100"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', 'border-slate-200']" />
                    </div>

                    <!-- Order -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan Tampil</label>
                        <input v-model.number="createForm.order" type="number" min="0"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', 'border-slate-200']" />
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
                        {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Edit Modal ──────────────────────────────────────────────────────── -->
        <Modal :show="!!editTarget" max-width="lg" @close="editTarget = null">
            <form @submit.prevent="submitEdit">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">Edit Komponen Penilaian</h3>
                    <button type="button" @click="editTarget = null"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                    <!-- Nama -->
                    <div class="col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Komponen <span class="text-red-500">*</span></label>
                        <input v-model="editForm.name" type="text"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.name ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                    </div>

                    <!-- Tipe -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tipe <span class="text-red-500">*</span></label>
                        <select v-model="editForm.type"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20', editForm.errors.type ? 'border-red-400' : 'border-slate-200']">
                            <option value="numeric">Numerik</option>
                            <option value="predicate">Predikat</option>
                            <option value="narrative">Narasi</option>
                        </select>
                    </div>

                    <!-- KI -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Aspek Penilaian</label>
                        <select v-model="editForm.ki"
                            :disabled="editForm.type !== 'numeric'"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 disabled:bg-slate-50 disabled:text-slate-400', 'border-slate-200']">
                            <option value="ki3">Pengetahuan (KI 3)</option>
                            <option value="ki4">Keterampilan (KI 4)</option>
                        </select>
                    </div>

                    <!-- Bobot -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Bobot (%)
                            <span v-if="editForm.type === 'numeric'" class="text-red-500">*</span>
                        </label>
                        <input v-model.number="editForm.weight" type="number" min="1" max="100"
                            :disabled="editForm.type !== 'numeric'"
                            :class="['w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 disabled:bg-slate-50 disabled:text-slate-400', editForm.errors.weight ? 'border-red-400' : 'border-slate-200']" />
                        <p v-if="editForm.errors.weight" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.weight }}</p>
                    </div>

                    <!-- Min / Max score -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nilai Minimum</label>
                        <input v-model.number="editForm.min_score" type="number" min="0" max="100"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nilai Maksimum</label>
                        <input v-model.number="editForm.max_score" type="number" min="0" max="100"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" />
                    </div>

                    <!-- Order -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Urutan Tampil</label>
                        <input v-model.number="editForm.order" type="number" min="0"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" @click="editTarget = null"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="editForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60">
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
                <h3 class="text-base font-bold text-slate-900">Hapus Komponen</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    Yakin hapus komponen <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Data nilai siswa yang terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="deleteTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                    Batal
                </button>
                <button @click="submitDelete" :disabled="deleteForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60">
                    <svg v-if="deleteForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
