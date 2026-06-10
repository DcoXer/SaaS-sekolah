<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classrooms:      { type: Array,  required: true },
    activeYear:      { type: Object, default: null },
    reportCardStats: { type: Object, default: () => ({}) },
});

// ── Semester tab ──────────────────────────────────────────────────────────────
const semester = ref(1);

// ── Group classrooms by grade ─────────────────────────────────────────────────
const gradeLabel = (g) => `Kelas ${g}`;

const classroomsByGrade = computed(() => {
    if (!props.activeYear) return {};
    const active = props.classrooms.filter(c => c.academic_year_id === props.activeYear.id);
    return active.reduce((acc, c) => {
        (acc[c.grade] ??= []).push(c);
        return acc;
    }, {});
});

// ── Helpers stats ─────────────────────────────────────────────────────────────
const getStats = (classroomId) => {
    const bySemester = props.reportCardStats[classroomId]?.[semester.value] ?? {};
    return {
        draft:            bySemester['draft']            ?? 0,
        waiting_approval: bySemester['waiting_approval'] ?? 0,
        approved:         bySemester['approved']         ?? 0,
        total:            Object.values(bySemester).reduce((s, n) => s + n, 0),
    };
};

// ── Generate ──────────────────────────────────────────────────────────────────
const generateForm = useForm({ semester: 1 });

const handleGenerate = (classroom) => {
    generateForm.semester = semester.value;
    generateForm.post(route('kamad.report-cards.generate', classroom.id), {
        preserveScroll: true,
    });
};

// ── Approve All ───────────────────────────────────────────────────────────────
const showApproveConfirm = ref(false);
const selectedClassroom  = ref(null);
const approveAllForm     = useForm({ semester: 1 });

const openApproveConfirm = (classroom) => {
    selectedClassroom.value  = classroom;
    approveAllForm.semester  = semester.value;
    showApproveConfirm.value = true;
};

const confirmApproveAll = () => {
    approveAllForm.semester = semester.value;
    approveAllForm.patch(route('kamad.report-cards.approve-all', selectedClassroom.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showApproveConfirm.value = false;
            selectedClassroom.value  = null;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Raport" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Kamad</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Raport</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Page heading + semester tabs -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Raport</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Generate dan setujui raport per kelas per semester.
                    </p>
                </div>

                <!-- Semester tab -->
                <div class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                    <button
                        v-for="s in [1, 2]"
                        :key="s"
                        @click="semester = s"
                        class="rounded-md px-4 py-1.5 text-sm font-semibold transition-[background-color,color] duration-150"
                        :class="semester === s
                            ? 'bg-slate-800 text-white'
                            : 'text-slate-500 hover:text-slate-700'"
                    >
                        Semester {{ s }}
                    </button>
                </div>
            </div>

            <!-- No active year -->
            <div
                v-if="!activeYear"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
                <p class="mt-1 text-xs text-slate-400">Aktifkan tahun ajaran terlebih dahulu.</p>
            </div>

            <!-- No classrooms -->
            <div
                v-else-if="Object.keys(classroomsByGrade).length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada kelas</p>
                <p class="mt-1 text-xs text-slate-400">Operator belum membuat rombel untuk tahun ajaran ini.</p>
            </div>

            <!-- Classrooms grouped by grade -->
            <template v-else>
                <div
                    v-for="(rooms, grade) in classroomsByGrade"
                    :key="grade"
                    class="space-y-3"
                >
                    <!-- Grade label -->
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        {{ gradeLabel(grade) }}
                    </h3>

                    <!-- Mobile cards -->
                    <div class="sm:hidden space-y-2">
                        <div v-for="classroom in rooms" :key="classroom.id" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <div class="p-4">
                                <p class="text-sm font-semibold text-slate-800">{{ classroom.name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">Wali: {{ classroom.homeroom_teacher?.user?.name ?? '—' }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <template v-if="getStats(classroom.id).total === 0">
                                        <span class="text-xs text-slate-400">Belum di-generate</span>
                                    </template>
                                    <template v-else>
                                        <span v-if="getStats(classroom.id).approved > 0" class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700">
                                            <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                            {{ getStats(classroom.id).approved }} disetujui
                                        </span>
                                        <span v-if="getStats(classroom.id).waiting_approval > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                            <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/></svg>
                                            {{ getStats(classroom.id).waiting_approval }} menunggu
                                        </span>
                                        <span v-if="getStats(classroom.id).draft > 0" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">{{ getStats(classroom.id).draft }} draft</span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 border-t border-slate-100 px-4 py-3">
                                <button @click="handleGenerate(classroom)" :disabled="generateForm.processing" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60">
                                    <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    Generate
                                </button>
                                <button @click="openApproveConfirm(classroom)" :disabled="getStats(classroom.id).waiting_approval === 0" class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-600 disabled:cursor-not-allowed disabled:opacity-40">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    Setujui Semua
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop table -->
                    <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Rombel</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Wali Kelas</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Status Raport</th>
                                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="classroom in rooms" :key="classroom.id" class="transition-[background-color] duration-150 hover:bg-slate-50">
                                    <td class="px-5 py-4"><span class="text-sm font-semibold text-slate-800">{{ classroom.name }}</span></td>
                                    <td class="px-5 py-4"><span class="text-sm text-slate-600">{{ classroom.homeroom_teacher?.user?.name ?? '—' }}</span></td>
                                    <td class="px-5 py-4">
                                        <template v-if="getStats(classroom.id).total === 0">
                                            <span class="text-xs text-slate-400">Belum di-generate</span>
                                        </template>
                                        <template v-else>
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span v-if="getStats(classroom.id).approved > 0" class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700">
                                                    <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                                    {{ getStats(classroom.id).approved }} disetujui
                                                </span>
                                                <span v-if="getStats(classroom.id).waiting_approval > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                                    <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/></svg>
                                                    {{ getStats(classroom.id).waiting_approval }} menunggu
                                                </span>
                                                <span v-if="getStats(classroom.id).draft > 0" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">{{ getStats(classroom.id).draft }} draft</span>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="handleGenerate(classroom)" :disabled="generateForm.processing" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-[background-color] duration-150 hover:bg-slate-50 disabled:opacity-60">
                                                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                                Generate
                                            </button>
                                            <button @click="openApproveConfirm(classroom)" :disabled="getStats(classroom.id).waiting_approval === 0" class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-violet-600 disabled:cursor-not-allowed disabled:opacity-40">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                Setujui Semua
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

        </div>

        <!-- ── Approve All Confirm Modal ──────────────────────────────────────── -->
        <Modal :show="showApproveConfirm" max-width="sm" @close="showApproveConfirm = false">
            <div class="px-6 py-5">

                <div class="mb-4 flex items-start gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-violet-100">
                        <svg class="size-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Setujui Semua Raport?</h3>
                        <p class="mt-1 text-pretty text-sm text-slate-500">
                            <strong class="text-amber-600">{{ selectedClassroom ? getStats(selectedClassroom.id).waiting_approval : 0 }} raport</strong>
                            kelas <strong class="text-slate-700">{{ selectedClassroom?.name }}</strong>
                            Semester {{ semester }} yang menunggu persetujuan akan disetujui.
                            Guru dapat mengekspor PDF dan wali murid dapat melihat raport.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="showApproveConfirm = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="approveAllForm.processing"
                        @click="confirmApproveAll"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-violet-600 disabled:opacity-60"
                    >
                        <svg
                            v-if="approveAllForm.processing"
                            class="size-4 animate-spin"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ approveAllForm.processing ? 'Memproses...' : 'Ya, Setujui' }}
                    </button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
