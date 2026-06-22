<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import BackButton from '@/Components/BackButton.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classroom:      { type: Object, required: true },
    teachers:       { type: Array,  required: true },
    allSubjects:    { type: Array,  required: true },
    peerClassrooms: { type: Array,  default: () => [] },
});

const isLower = props.classroom.grade <= 3;
const isUpper = props.classroom.grade >= 4;

// Subjects already in this classroom (from teacherSubjects)
const classroomSubjectIds = computed(() =>
    (props.classroom.teacher_subjects ?? []).map(ts => ts.subject_id)
);

// Subjects NOT yet in this classroom
const availableSubjectsToAdd = computed(() =>
    props.allSubjects.filter(s => !classroomSubjectIds.value.includes(s.id))
);

// ── Edit form ─────────────────────────────────────────────────────────────────
const editForm = useForm({
    name:  props.classroom.name,
    grade: props.classroom.grade,
});

const submitEdit = () => {
    editForm.put(route('operator.classrooms.update', props.classroom.id));
};

// ── Assign Guru Kelas (grade 1-3) ─────────────────────────────────────────────
const showAssignGuruKelas    = ref(false);
const availableGuruKelas     = ref([]);
const loadingGuruKelas       = ref(false);
const assignGuruKelasForm    = useForm({ teacher_id: '' });

const openAssignGuruKelas = async () => {
    loadingGuruKelas.value = true;
    showAssignGuruKelas.value = true;
    assignGuruKelasForm.reset();
    try {
        const res = await fetch(route('operator.classrooms.available-teachers', props.classroom.id));
        if (!res.ok) throw new Error(res.statusText);
        const data = await res.json();
        availableGuruKelas.value = data.teachers ?? [];
    } catch {
        availableGuruKelas.value = [];
    } finally {
        loadingGuruKelas.value = false;
    }
};

const submitAssignGuruKelas = () => {
    assignGuruKelasForm.post(route('operator.classrooms.assign-guru-kelas', props.classroom.id), {
        onSuccess: () => { showAssignGuruKelas.value = false; },
    });
};

// ── Assign Wali Kelas (grade 4-6) ─────────────────────────────────────────────
const showAssignWaliKelas    = ref(false);
const availableWaliKelas     = ref([]);
const loadingWaliKelas       = ref(false);
const assignWaliKelasForm    = useForm({ teacher_id: '' });

const openAssignWaliKelas = async () => {
    loadingWaliKelas.value = true;
    showAssignWaliKelas.value = true;
    assignWaliKelasForm.reset();
    try {
        const res = await fetch(route('operator.classrooms.available-teachers', props.classroom.id));
        if (!res.ok) throw new Error(res.statusText);
        const data = await res.json();
        availableWaliKelas.value = data.wali_kelas ?? [];
    } catch {
        availableWaliKelas.value = [];
    } finally {
        loadingWaliKelas.value = false;
    }
};

const submitAssignWaliKelas = () => {
    assignWaliKelasForm.post(route('operator.classrooms.assign-wali-kelas', props.classroom.id), {
        onSuccess: () => { showAssignWaliKelas.value = false; },
    });
};

// ── Add Subject (checklist modal) ─────────────────────────────────────────────
const showAddSubject     = ref(false);
const selectedSubjectId  = ref('');
const addSubjectForm     = useForm({ subject_id: '' });

const openAddSubject = () => {
    selectedSubjectId.value = '';
    addSubjectForm.reset();
    showAddSubject.value = true;
};

const submitAddSubject = () => {
    addSubjectForm.subject_id = selectedSubjectId.value;
    addSubjectForm.post(route('operator.classrooms.subjects.add', props.classroom.id), {
        onSuccess: () => { showAddSubject.value = false; },
    });
};

// ── Remove Subject ────────────────────────────────────────────────────────────
const removeSubjectTarget = ref(null);
const removeSubjectForm   = useForm({});

const submitRemoveSubject = () => {
    removeSubjectForm.delete(
        route('operator.classrooms.subjects.remove', [props.classroom.id, removeSubjectTarget.value.subject_id]),
        { onSuccess: () => { removeSubjectTarget.value = null; } }
    );
};

// ── Assign Teacher per Subject (grade 4-6) ────────────────────────────────────
const showAssignTeacher   = ref(false);
const assignTeacherTarget = ref(null); // the teacher_subject record
const assignTeacherForm   = useForm({ teacher_id: '' });

const guruBidangTeachers = computed(() =>
    props.teachers.filter(t => t.type === 'guru_bidang')
);

const guruBidangOptions = computed(() =>
    guruBidangTeachers.value.map(t => ({ value: String(t.id), label: t.user.name }))
);

const openAssignTeacher = (ts) => {
    assignTeacherTarget.value = ts;
    assignTeacherForm.teacher_id = ts.teacher_id ? String(ts.teacher_id) : '';
    assignTeacherForm.clearErrors();
    showAssignTeacher.value = true;
};

const submitAssignTeacher = () => {
    assignTeacherForm.patch(
        route('operator.classrooms.subjects.assign-teacher', [props.classroom.id, assignTeacherTarget.value.subject_id]),
        { onSuccess: () => { showAssignTeacher.value = false; } }
    );
};

// ── Assign Siswa ──────────────────────────────────────────────────────────────
const showAssignSiswa        = ref(false);
const availableSiswa         = ref([]);
const loadingSiswa           = ref(false);
const selectedStudentIds     = ref([]);
const assignSiswaForm        = useForm({ student_ids: [] });

const openAssignSiswa = async () => {
    loadingSiswa.value = true;
    showAssignSiswa.value = true;
    selectedStudentIds.value = [];
    try {
        const res = await fetch(route('operator.classrooms.available-students', props.classroom.id));
        if (!res.ok) throw new Error(res.statusText);
        availableSiswa.value = await res.json();
    } catch {
        availableSiswa.value = [];
    } finally {
        loadingSiswa.value = false;
    }
};

const toggleStudent = (id) => {
    const idx = selectedStudentIds.value.indexOf(id);
    if (idx === -1) selectedStudentIds.value.push(id);
    else selectedStudentIds.value.splice(idx, 1);
};

const submitAssignSiswa = () => {
    assignSiswaForm.student_ids = selectedStudentIds.value;
    assignSiswaForm.post(route('operator.classrooms.assign-students', props.classroom.id), {
        onSuccess: () => { showAssignSiswa.value = false; },
    });
};

// ── Manage existing students (bulk move / remove) ─────────────────────────────
const selectedInClass = ref([]);
const showMoveStudents = ref(false);
const showRemoveStudents = ref(false);

const moveStudentsForm = useForm({
    student_ids: [],
    target_classroom_id: '',
});

const removeStudentsForm = useForm({
    student_ids: [],
});

const toggleInClass = (studentId) => {
    const id = Number(studentId);
    selectedInClass.value = selectedInClass.value.includes(id)
        ? selectedInClass.value.filter(x => x !== id)
        : [...selectedInClass.value, id];
};

const clearInClassSelection = () => { selectedInClass.value = []; };

const openMoveStudents = () => {
    moveStudentsForm.reset();
    moveStudentsForm.clearErrors();
    moveStudentsForm.target_classroom_id = '';
    showMoveStudents.value = true;
};

const submitMoveStudents = () => {
    moveStudentsForm.student_ids = selectedInClass.value;
    moveStudentsForm.patch(route('operator.classrooms.move-students', props.classroom.id), {
        onSuccess: () => {
            showMoveStudents.value = false;
            clearInClassSelection();
        },
    });
};

const openRemoveStudents = () => {
    removeStudentsForm.reset();
    removeStudentsForm.clearErrors();
    showRemoveStudents.value = true;
};

const submitRemoveStudents = () => {
    router.post(route('operator.classrooms.remove-students', props.classroom.id), {
        _method: 'DELETE',
        student_ids: selectedInClass.value,
    }, {
        onSuccess: () => {
            showRemoveStudents.value = false;
            clearInClassSelection();
        },
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const showDelete = ref(false);
const deleteForm = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.classrooms.destroy', props.classroom.id), {
        onSuccess: () => router.visit(route('operator.classrooms.index')),
    });
};

// ── Select Options ────────────────────────────────────────────────────────────
const gradeOptions = [1,2,3,4,5,6].map(g => ({ value: g, label: `Kelas ${g}` }));

const peerClassroomOptions = computed(() =>
    props.peerClassrooms.map(c => ({ value: c.id, label: c.name }))
);
</script>

<template>
    <AppLayout>
        <Head :title="classroom.name" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/operator/classrooms" class="transition-[color] duration-150 hover:text-slate-700">Kelas</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ classroom.name }}</span>
            </div>
        </template>

        <div class="mx-auto max-w-6xl space-y-5">
            <BackButton href="/operator/classrooms" />

            <div class="space-y-5 lg:grid lg:grid-cols-12 lg:gap-5 lg:space-y-0">

            <!-- Header card -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-12">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 items-center justify-center rounded-xl bg-violet-100">
                        <svg class="size-7 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-balance text-base font-bold text-slate-900">{{ classroom.name }}</h2>
                        <p class="text-pretty text-sm text-slate-500">{{ classroom.academic_year?.name }}</p>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                                Kelas {{ classroom.grade }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <span class="tabular-nums">{{ classroom.students?.length ?? 0 }} siswa</span>
                            </span>
                        </div>
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

            <!-- Main: Students -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-7">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Siswa</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Daftar siswa yang terdaftar di kelas ini.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-if="selectedInClass.length > 0" class="hidden sm:inline text-xs text-slate-500">
                            {{ selectedInClass.length }} dipilih
                        </span>
                        <button
                            type="button"
                            @click="openMoveStudents"
                            :disabled="selectedInClass.length === 0 || peerClassrooms.length === 0"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-[background-color,border-color] duration-150 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Pindahkan
                        </button>
                        <button
                            type="button"
                            @click="openRemoveStudents"
                            :disabled="selectedInClass.length === 0"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 transition-[background-color,border-color] duration-150 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Keluarkan
                        </button>
                        <button
                            type="button"
                            @click="openAssignSiswa"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah
                        </button>
                    </div>
                </div>

                <div v-if="classroom.students?.length > 0">
                    <ul class="divide-y divide-slate-100">
                        <li
                            v-for="student in classroom.students"
                            :key="student.id"
                            class="flex items-center gap-3 px-5 py-3 transition-[background-color] duration-150 hover:bg-slate-50"
                        >
                            <button
                                type="button"
                                @click="toggleInClass(student.id)"
                                class="flex size-5 shrink-0 items-center justify-center rounded border border-slate-300 bg-white"
                                :class="selectedInClass.includes(student.id) ? 'border-primary-500 bg-primary-500' : ''"
                                :aria-label="selectedInClass.includes(student.id) ? 'Batalkan pilihan' : 'Pilih siswa'"
                            >
                                <svg v-if="selectedInClass.includes(student.id)" class="size-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </button>
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-semibold text-sky-700">
                                {{ student.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ student.name }}</p>
                                <p class="tabular-nums truncate text-xs text-slate-400">
                                    NISN {{ student.nisn ?? '—' }}<span v-if="student.nis"> • NIS {{ student.nis }}</span>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div v-else class="px-5 py-5 text-center">
                    <p class="text-sm text-slate-400">Belum ada siswa di kelas ini.</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5 lg:col-span-5">

            <!-- Edit form card -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Edit Data Kelas</h3>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4 p-5">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="e-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Nama Kelas <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="e-name"
                                v-model="editForm.name"
                                type="text"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                    editForm.errors.name ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="editForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Tingkat <span class="text-red-500">*</span>
                            </label>
                            <FilterSelect
                                v-model="editForm.grade"
                                :options="gradeOptions"
                                block
                                :has-error="!!editForm.errors.grade"
                            />
                            <p v-if="editForm.errors.grade" class="mt-1.5 text-xs text-red-500">{{ editForm.errors.grade }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                        >
                            <svg v-if="editForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- ══ GRADE 1-3: Guru Kelas ════════════════════════════════════════ -->
            <div v-if="isLower" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Guru Kelas</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Guru kelas mengajar semua mata pelajaran di kelas ini.</p>
                    </div>
                    <button
                        @click="openAssignGuruKelas"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Ganti
                    </button>
                </div>

                <div class="px-5 py-4">
                    <div v-if="classroom.homeroom_teacher" class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700">
                            {{ classroom.homeroom_teacher.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ classroom.homeroom_teacher.user.name }}</p>
                            <p class="text-xs text-slate-400">Guru Kelas</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-400">Belum ada guru kelas yang di-assign.</p>
                </div>
            </div>

            <!-- ══ GRADE 4-6: Wali Kelas ════════════════════════════════════════ -->
            <div v-if="isUpper" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Wali Kelas</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Guru bidang yang bertugas sebagai wali kelas.</p>
                    </div>
                    <button
                        @click="openAssignWaliKelas"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Ganti
                    </button>
                </div>

                <div class="px-5 py-4">
                    <div v-if="classroom.homeroom_teacher" class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700">
                            {{ classroom.homeroom_teacher.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ classroom.homeroom_teacher.user.name }}</p>
                            <p class="text-xs text-slate-400">Wali Kelas</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-400">Belum ada wali kelas yang di-assign.</p>
                </div>
            </div>

            <!-- ══ Mata Pelajaran (both grades) ═══════════════════════════════════ -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Mata Pelajaran</h3>
                        <p class="mt-0.5 text-xs text-slate-400">
                            <template v-if="isLower">Guru kelas mengajar semua mapel di bawah ini.</template>
                            <template v-else>Mapel yang diajarkan di kelas ini beserta gurunya.</template>
                        </p>
                    </div>
                    <button
                        v-if="availableSubjectsToAdd.length > 0"
                        @click="openAddSubject"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah
                    </button>
                </div>

                <!-- Grade 1-3: simple list, no teacher column -->
                <template v-if="isLower">
                    <div v-if="classroom.teacher_subjects?.length > 0">
                        <ul class="divide-y divide-slate-100">
                            <li
                                v-for="ts in classroom.teacher_subjects"
                                :key="ts.id"
                                class="flex items-center justify-between px-5 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-amber-50">
                                        <svg class="size-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ ts.subject?.name }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ ts.teacher?.user?.name ?? (classroom.homeroom_teacher ? classroom.homeroom_teacher.user.name : 'Belum ada guru') }}
                                            <span class="text-slate-300"> • Wali Kelas</span>
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="removeSubjectTarget = ts"
                                    class="inline-flex size-7 items-center justify-center rounded-lg text-slate-300 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500"
                                    aria-label="Hapus mapel dari kelas"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div v-else class="px-5 py-5 text-center">
                        <p class="text-sm text-slate-400">Belum ada mata pelajaran di kelas ini.</p>
                        <p class="mt-1 text-xs text-slate-300">Klik "Tambah" untuk memasukkan mapel.</p>
                    </div>
                </template>

                <!-- Grade 4-6: list with teacher per subject -->
                <template v-else>
                    <div v-if="classroom.teacher_subjects?.length > 0">
                        <ul class="divide-y divide-slate-100">
                            <li
                                v-for="ts in classroom.teacher_subjects"
                                :key="ts.id"
                                class="flex items-center justify-between px-5 py-3"
                            >
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-amber-50">
                                        <svg class="size-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-800">{{ ts.subject?.name }}</p>
                                        <p
                                            class="truncate text-xs"
                                            :class="ts.teacher ? 'text-slate-400' : 'text-amber-500'"
                                        >
                                            {{ ts.teacher?.user?.name ?? 'Belum ditugaskan' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1 pl-2">
                                    <button
                                        @click="openAssignTeacher(ts)"
                                        class="inline-flex size-7 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                                        aria-label="Tugaskan guru"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="removeSubjectTarget = ts"
                                        class="inline-flex size-7 items-center justify-center rounded-lg text-slate-300 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500"
                                        aria-label="Hapus mapel dari kelas"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div v-else class="px-5 py-5 text-center">
                        <p class="text-sm text-slate-400">Belum ada mata pelajaran di kelas ini.</p>
                        <p class="mt-1 text-xs text-slate-300">Klik "Tambah" untuk memasukkan mapel.</p>
                    </div>
                </template>
            </div>

            </div> <!-- /sidebar -->

            </div> <!-- /grid -->
        </div>


        <!-- ══ Modal: Assign Guru Kelas ════════════════════════════════════════ -->
        <Modal :show="showAssignGuruKelas" max-width="sm" @close="showAssignGuruKelas = false">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-bold text-slate-900">Pilih Guru Kelas</h3>
                <button
                    type="button"
                    @click="showAssignGuruKelas = false"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <div v-if="loadingGuruKelas" class="py-6 text-center text-sm text-slate-400">Memuat data...</div>
                <div v-else-if="availableGuruKelas.length === 0" class="py-6 text-center text-sm text-slate-400">
                    Tidak ada guru kelas yang tersedia.
                </div>
                <div v-else class="space-y-2">
                    <label
                        v-for="teacher in availableGuruKelas"
                        :key="teacher.id"
                        :class="[
                            'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 transition-[border-color,background-color] duration-150',
                            assignGuruKelasForm.teacher_id === String(teacher.id)
                                ? 'border-primary-400 bg-primary-50'
                                : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                        ]"
                    >
                        <input type="radio" :value="String(teacher.id)" v-model="assignGuruKelasForm.teacher_id" class="sr-only" />
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700">
                            {{ teacher.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                        </div>
                        <span class="text-sm font-medium text-slate-800">{{ teacher.user.name }}</span>
                    </label>
                </div>
                <p v-if="assignGuruKelasForm.errors.teacher_id" class="mt-2 text-xs text-red-500">{{ assignGuruKelasForm.errors.teacher_id }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="showAssignGuruKelas = false" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button
                    @click="submitAssignGuruKelas"
                    :disabled="assignGuruKelasForm.processing || !assignGuruKelasForm.teacher_id"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60"
                >
                    <svg v-if="assignGuruKelasForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ assignGuruKelasForm.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </Modal>

        <!-- ══ Modal: Assign Wali Kelas ════════════════════════════════════════ -->
        <Modal :show="showAssignWaliKelas" max-width="sm" @close="showAssignWaliKelas = false">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-bold text-slate-900">Pilih Wali Kelas</h3>
                <button
                    type="button"
                    @click="showAssignWaliKelas = false"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <div v-if="loadingWaliKelas" class="py-6 text-center text-sm text-slate-400">Memuat data...</div>
                <div v-else-if="availableWaliKelas.length === 0" class="py-6 text-center text-sm text-slate-400">
                    Tidak ada guru bidang yang tersedia sebagai wali kelas.
                </div>
                <div v-else class="space-y-2">
                    <label
                        v-for="teacher in availableWaliKelas"
                        :key="teacher.id"
                        :class="[
                            'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 transition-[border-color,background-color] duration-150',
                            assignWaliKelasForm.teacher_id === String(teacher.id)
                                ? 'border-primary-400 bg-primary-50'
                                : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                        ]"
                    >
                        <input type="radio" :value="String(teacher.id)" v-model="assignWaliKelasForm.teacher_id" class="sr-only" />
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">
                            {{ teacher.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                        </div>
                        <span class="text-sm font-medium text-slate-800">{{ teacher.user.name }}</span>
                    </label>
                </div>
                <p v-if="assignWaliKelasForm.errors.teacher_id" class="mt-2 text-xs text-red-500">{{ assignWaliKelasForm.errors.teacher_id }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="showAssignWaliKelas = false" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button
                    @click="submitAssignWaliKelas"
                    :disabled="assignWaliKelasForm.processing || !assignWaliKelasForm.teacher_id"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60"
                >
                    <svg v-if="assignWaliKelasForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ assignWaliKelasForm.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </Modal>

        <!-- ══ Modal: Tambah Mapel ═════════════════════════════════════════════ -->
        <Modal :show="showAddSubject" max-width="sm" @close="showAddSubject = false">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-bold text-slate-900">Tambah Mata Pelajaran</h3>
                <button
                    type="button"
                    @click="showAddSubject = false"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <p class="mb-3 text-xs text-slate-500">Pilih mata pelajaran yang akan ditambahkan ke kelas ini:</p>
                <div class="max-h-64 space-y-1.5 overflow-y-auto">
                    <label
                        v-for="subject in availableSubjectsToAdd"
                        :key="subject.id"
                        :class="[
                            'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-2.5 transition-[border-color,background-color] duration-150',
                            selectedSubjectId === String(subject.id)
                                ? 'border-primary-400 bg-primary-50'
                                : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                        ]"
                    >
                        <input type="radio" :value="String(subject.id)" v-model="selectedSubjectId" class="sr-only" />
                        <div
                            :class="[
                                'flex size-4 shrink-0 items-center justify-center rounded-full border-2 transition-[border-color,background-color] duration-150',
                                selectedSubjectId === String(subject.id) ? 'border-primary-500 bg-primary-500' : 'border-slate-300',
                            ]"
                        >
                            <div v-if="selectedSubjectId === String(subject.id)" class="size-1.5 rounded-full bg-white"></div>
                        </div>
                        <span class="text-sm font-medium text-slate-800">{{ subject.name }}</span>
                    </label>
                </div>
                <p v-if="addSubjectForm.errors.subject_id" class="mt-2 text-xs text-red-500">{{ addSubjectForm.errors.subject_id }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="showAddSubject = false" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button
                    @click="submitAddSubject"
                    :disabled="addSubjectForm.processing || !selectedSubjectId"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60"
                >
                    <svg v-if="addSubjectForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ addSubjectForm.processing ? 'Menyimpan...' : 'Tambahkan' }}
                </button>
            </div>
        </Modal>

        <!-- ══ Modal: Tugaskan Guru ke Mapel (grade 4-6) ═══════════════════════ -->
        <Modal :show="showAssignTeacher" max-width="sm" @close="showAssignTeacher = false">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tugaskan Guru</h3>
                    <p class="mt-0.5 text-xs text-slate-500">{{ assignTeacherTarget?.subject?.name }}</p>
                </div>
                <button
                    type="button"
                    @click="showAssignTeacher = false"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <div class="space-y-2">
                    <label
                        v-for="teacher in guruBidangTeachers"
                        :key="teacher.id"
                        :class="[
                            'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 transition-[border-color,background-color] duration-150',
                            assignTeacherForm.teacher_id === String(teacher.id)
                                ? 'border-primary-400 bg-primary-50'
                                : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                        ]"
                    >
                        <input type="radio" :value="String(teacher.id)" v-model="assignTeacherForm.teacher_id" class="sr-only" />
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">
                            {{ teacher.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                        </div>
                        <span class="text-sm font-medium text-slate-800">{{ teacher.user.name }}</span>
                    </label>
                </div>
                <p v-if="assignTeacherForm.errors.teacher_id" class="mt-2 text-xs text-red-500">{{ assignTeacherForm.errors.teacher_id }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="showAssignTeacher = false" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button
                    @click="submitAssignTeacher"
                    :disabled="assignTeacherForm.processing || !assignTeacherForm.teacher_id"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60"
                >
                    <svg v-if="assignTeacherForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ assignTeacherForm.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </Modal>

        <!-- ══ Modal: Hapus Mapel dari Kelas ═══════════════════════════════════ -->
        <Modal :show="!!removeSubjectTarget" max-width="sm" @close="removeSubjectTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Mata Pelajaran</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Hapus <span class="font-semibold text-slate-700">{{ removeSubjectTarget?.subject?.name }}</span> dari kelas ini?
                    Data nilai komponen yang terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="removeSubjectTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                >
                    Batal
                </button>
                <button
                    @click="submitRemoveSubject"
                    :disabled="removeSubjectForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-60"
                >
                    <svg v-if="removeSubjectForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ removeSubjectForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

        <!-- ══ Modal: Assign Siswa ═════════════════════════════════════════════ -->
        <Modal :show="showAssignSiswa" max-width="sm" @close="showAssignSiswa = false">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-bold text-slate-900">Tambah Siswa ke Kelas</h3>
                <button
                    type="button"
                    @click="showAssignSiswa = false"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <div v-if="loadingSiswa" class="py-6 text-center text-sm text-slate-400">Memuat data...</div>
                <div v-else-if="availableSiswa.length === 0" class="py-6 text-center text-sm text-slate-400">
                    Tidak ada siswa tingkat {{ classroom.grade }} yang belum masuk rombel.
                </div>
                <div v-else class="max-h-72 space-y-1.5 overflow-y-auto">
                    <label
                        v-for="student in availableSiswa"
                        :key="student.id"
                        :class="[
                            'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-2.5 transition-[border-color,background-color] duration-150',
                            selectedStudentIds.includes(student.id)
                                ? 'border-primary-400 bg-primary-50'
                                : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50',
                        ]"
                    >
                        <input
                            type="checkbox"
                            :value="student.id"
                            :checked="selectedStudentIds.includes(student.id)"
                            @change="toggleStudent(student.id)"
                            class="sr-only"
                        />
                        <div
                            :class="[
                                'flex size-4 shrink-0 items-center justify-center rounded border-2 transition-[border-color,background-color] duration-150',
                                selectedStudentIds.includes(student.id)
                                    ? 'border-primary-500 bg-primary-500'
                                    : 'border-slate-300',
                            ]"
                        >
                            <svg v-if="selectedStudentIds.includes(student.id)" class="size-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ student.name }}</p>
                            <p class="tabular-nums text-xs text-slate-400">
                                NISN {{ student.nisn ?? '—' }}<span v-if="student.nis"> • NIS {{ student.nis }}</span>
                            </p>
                        </div>
                    </label>
                </div>
                <p v-if="assignSiswaForm.errors.student_ids" class="mt-2 text-xs text-red-500">{{ assignSiswaForm.errors.student_ids }}</p>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-6 py-4">
                <span v-if="selectedStudentIds.length > 0" class="text-xs text-slate-500">
                    {{ selectedStudentIds.length }} siswa dipilih
                </span>
                <span v-else class="text-xs text-slate-400">Pilih siswa yang akan ditambahkan</span>
                <div class="flex gap-3">
                    <button type="button" @click="showAssignSiswa = false" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button
                        @click="submitAssignSiswa"
                        :disabled="assignSiswaForm.processing || selectedStudentIds.length === 0"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60"
                    >
                        <svg v-if="assignSiswaForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ assignSiswaForm.processing ? 'Menyimpan...' : 'Tambahkan' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- ══ Modal: Pindahkan Siswa ══════════════════════════════════════════ -->
        <Modal :show="showMoveStudents" max-width="sm" @close="showMoveStudents = false">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-bold text-slate-900">Pindahkan Siswa</h3>
                <button
                    type="button"
                    @click="showMoveStudents = false"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Tutup modal"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitMoveStudents" class="space-y-4 px-6 py-5">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-800">{{ selectedInClass.length }} siswa dipilih</p>
                    <p class="mt-0.5 text-xs text-slate-500">Pindahkan ke rombel lain (tingkat yang sama) di tahun ajaran aktif.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Kelas Tujuan <span class="text-red-500">*</span>
                    </label>
                    <FilterSelect
                        v-model="moveStudentsForm.target_classroom_id"
                        :options="peerClassroomOptions"
                        block
                        :has-error="!!moveStudentsForm.errors.target_classroom_id"
                    />
                    <p v-if="moveStudentsForm.errors.target_classroom_id" class="mt-1.5 text-xs text-red-500">{{ moveStudentsForm.errors.target_classroom_id }}</p>
                </div>

                <p v-if="moveStudentsForm.errors.student_ids" class="text-xs text-red-500">{{ moveStudentsForm.errors.student_ids }}</p>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        @click="showMoveStudents = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="moveStudentsForm.processing || !moveStudentsForm.target_classroom_id"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60"
                    >
                        <svg v-if="moveStudentsForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ moveStudentsForm.processing ? 'Memindahkan...' : 'Pindahkan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ══ Modal: Keluarkan Siswa ══════════════════════════════════════════ -->
        <Modal :show="showRemoveStudents" max-width="sm" @close="showRemoveStudents = false">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Keluarkan Siswa dari Rombel</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Keluarkan {{ selectedInClass.length }} siswa dari rombel <span class="font-semibold text-slate-700">{{ classroom.name }}</span>?
                    Ini hanya memutus relasi rombel di tahun ajaran aktif.
                </p>
            </div>

            <p v-if="removeStudentsForm.errors.student_ids" class="px-6 text-xs text-red-500">{{ removeStudentsForm.errors.student_ids }}</p>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="showRemoveStudents = false"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                >
                    Batal
                </button>
                <button
                    @click="submitRemoveStudents"
                    :disabled="removeStudentsForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60"
                >
                    <svg v-if="removeStudentsForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ removeStudentsForm.processing ? 'Mengeluarkan...' : 'Ya, Keluarkan' }}
                </button>
            </div>
        </Modal>

        <!-- ══ Modal: Hapus Kelas ══════════════════════════════════════════════ -->
        <Modal :show="showDelete" max-width="sm" @close="showDelete = false">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Kelas</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus kelas <span class="font-semibold text-slate-700">{{ classroom.name }}</span>?
                    Data siswa yang terdaftar di kelas ini juga akan terputus.
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
