<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    assignments: { type: Array, required: true },
});

// ── Semester tab ──────────────────────────────────────────────────────────────
const semester = ref(1);

// ── Group assignments by classroom ────────────────────────────────────────────
const byClassroom = computed(() => {
    return props.assignments.reduce((acc, a) => {
        const key = a.classroom?.id;
        if (!key) return acc;
        (acc[key] ??= { classroom: a.classroom, subjects: [] }).subjects.push(a);
        return acc;
    }, {});
});

// ── Helper: group components per subject by KI ────────────────────────────────
const groupByKi = (components, sem) => {
    const filtered = components.filter(c => c.semester === sem);
    return {
        ki3:    filtered.filter(c => c.ki === 'ki3'),
        ki4:    filtered.filter(c => c.ki === 'ki4'),
        others: filtered.filter(c => !c.ki),
    };
};

// ── Check if any subject in a classroom has non-KI components ─────────────────
const hasOthers = (subjects, sem) =>
    subjects.some(a => groupByKi(a.components, sem).others.length > 0);

// ── Classroom gradient palette ────────────────────────────────────────────────
const classroomPalette = [
    { ring: 'ring-violet-200', bg: 'bg-violet-50',  badge: 'bg-violet-500',  text: 'text-violet-700',  label: 'text-violet-800',  accent: 'bg-violet-100' },
    { ring: 'ring-sky-200',    bg: 'bg-sky-50',     badge: 'bg-sky-500',     text: 'text-sky-700',     label: 'text-sky-800',     accent: 'bg-sky-100' },
    { ring: 'ring-primary-200',bg: 'bg-primary-50', badge: 'bg-primary-500', text: 'text-primary-700', label: 'text-primary-800', accent: 'bg-primary-100' },
    { ring: 'ring-amber-200',  bg: 'bg-amber-50',   badge: 'bg-amber-500',   text: 'text-amber-700',   label: 'text-amber-800',   accent: 'bg-amber-100' },
    { ring: 'ring-rose-200',   bg: 'bg-rose-50',    badge: 'bg-rose-500',    text: 'text-rose-700',    label: 'text-rose-800',    accent: 'bg-rose-100' },
    { ring: 'ring-indigo-200', bg: 'bg-indigo-50',  badge: 'bg-indigo-500',  text: 'text-indigo-700',  label: 'text-indigo-800',  accent: 'bg-indigo-100' },
];

const getPalette = (index) => classroomPalette[index % classroomPalette.length];
</script>

<template>
    <AppLayout>
        <Head title="Input Nilai" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Guru</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Input Nilai</span>
            </div>
        </template>

        <div class="space-y-6">

            <!-- ── Page banner ──────────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-orange-600 px-6 py-5 shadow-md">
                <!-- decorative circles -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-6 right-20 size-24 rounded-full bg-white/10"></div>

                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-white/20">
                                <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-white">Input Nilai</h2>
                        </div>
                        <p class="mt-1 text-sm text-orange-100">
                            Pilih komponen nilai untuk mulai menginput penilaian siswa.
                        </p>
                    </div>

                    <!-- Semester pill toggle -->
                    <div class="inline-flex shrink-0 rounded-xl bg-white/15 p-1 backdrop-blur-sm">
                        <button
                            v-for="s in [1, 2]"
                            :key="s"
                            @click="semester = s"
                            class="rounded-lg px-5 py-2 text-sm font-semibold transition-all duration-200"
                            :class="semester === s
                                ? 'bg-white text-orange-600 shadow-sm'
                                : 'text-white/80 hover:text-white hover:bg-white/10'"
                        >
                            Semester {{ s }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ──────────────────────────────────────────────── -->
            <div
                v-if="assignments.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center shadow-sm"
            >
                <div class="mb-4 flex size-16 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="size-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Belum ada penugasan</p>
                <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-400">
                    Anda belum di-assign ke kelas manapun. Hubungi operator untuk pengaturan penugasan.
                </p>
            </div>

            <!-- ── Classrooms ───────────────────────────────────────────────── -->
            <template v-else>
                <div
                    v-for="(group, idx) in byClassroom"
                    :key="group.classroom?.id"
                    class="space-y-3"
                >
                    <!-- Classroom header -->
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-sm"
                            :class="getPalette(idx).badge"
                        >
                            {{ group.classroom?.grade ?? '?' }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">{{ group.classroom?.name }}</h3>
                            <p class="text-xs text-slate-400">{{ group.classroom?.academic_year?.name ?? '' }}</p>
                        </div>
                        <div class="ml-auto hidden sm:flex">
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="[getPalette(idx).accent, getPalette(idx).text]"
                            >
                                {{ group.subjects.length }} mata pelajaran
                            </span>
                        </div>
                    </div>

                    <!-- ── MOBILE: card per subject ─────────────────────────── -->
                    <div class="space-y-2.5 sm:hidden">
                        <div
                            v-for="assignment in group.subjects"
                            :key="assignment.id"
                            class="overflow-hidden rounded-2xl border bg-white shadow-sm"
                            :class="getPalette(idx).ring + ' ring-1'"
                        >
                            <!-- Subject header -->
                            <div
                                class="flex items-center gap-2.5 border-b px-4 py-3"
                                :class="getPalette(idx).accent"
                            >
                                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg text-white shadow-sm" :class="getPalette(idx).badge">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold" :class="getPalette(idx).label">{{ assignment.subject?.name }}</span>
                            </div>

                            <!-- KI sections -->
                            <div class="divide-y divide-slate-100">
                                <!-- KI 3 -->
                                <div
                                    v-if="groupByKi(assignment.components, semester).ki3.length > 0"
                                    class="px-4 py-3.5"
                                >
                                    <div class="mb-2.5 flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                            <span class="size-1.5 rounded-full bg-blue-500"></span>
                                            Pengetahuan — KI 3
                                        </span>
                                    </div>
                                    <div class="space-y-2">
                                        <Link
                                            v-for="component in groupByKi(assignment.components, semester).ki3"
                                            :key="component.id"
                                            :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                            class="group flex items-center justify-between rounded-xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-4 py-2.5 transition-all duration-150 hover:border-blue-200 hover:from-blue-100 hover:shadow-sm active:scale-[0.99]"
                                        >
                                            <span class="text-sm font-medium text-blue-800">{{ component.name }}</span>
                                            <div class="flex items-center gap-2.5">
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-600">
                                                    {{ component.weight }}%
                                                </span>
                                                <svg class="size-4 text-blue-300 transition-transform duration-150 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </div>
                                        </Link>
                                    </div>
                                </div>

                                <!-- KI 4 -->
                                <div
                                    v-if="groupByKi(assignment.components, semester).ki4.length > 0"
                                    class="px-4 py-3.5"
                                >
                                    <div class="mb-2.5 flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-700">
                                            <span class="size-1.5 rounded-full bg-teal-500"></span>
                                            Keterampilan — KI 4
                                        </span>
                                    </div>
                                    <div class="space-y-2">
                                        <Link
                                            v-for="component in groupByKi(assignment.components, semester).ki4"
                                            :key="component.id"
                                            :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                            class="group flex items-center justify-between rounded-xl border border-teal-100 bg-gradient-to-r from-teal-50 to-white px-4 py-2.5 transition-all duration-150 hover:border-teal-200 hover:from-teal-100 hover:shadow-sm active:scale-[0.99]"
                                        >
                                            <span class="text-sm font-medium text-teal-800">{{ component.name }}</span>
                                            <div class="flex items-center gap-2.5">
                                                <span class="inline-flex items-center rounded-full bg-teal-100 px-2 py-0.5 text-xs font-bold text-teal-600">
                                                    {{ component.weight }}%
                                                </span>
                                                <svg class="size-4 text-teal-300 transition-transform duration-150 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </div>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Lainnya -->
                                <div
                                    v-if="groupByKi(assignment.components, semester).others.length > 0"
                                    class="px-4 py-3.5"
                                >
                                    <div class="mb-2.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                            <span class="size-1.5 rounded-full bg-slate-400"></span>
                                            Lainnya
                                        </span>
                                    </div>
                                    <div class="space-y-2">
                                        <Link
                                            v-for="component in groupByKi(assignment.components, semester).others"
                                            :key="component.id"
                                            :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                            class="group flex items-center justify-between rounded-xl border border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 py-2.5 transition-all duration-150 hover:border-slate-200 hover:from-slate-100 hover:shadow-sm active:scale-[0.99]"
                                        >
                                            <span class="text-sm font-medium text-slate-700">{{ component.name }}</span>
                                            <svg class="size-4 text-slate-300 transition-transform duration-150 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Empty -->
                                <div
                                    v-if="assignment.components?.filter(c => c.semester === semester).length === 0"
                                    class="px-4 py-5 text-center"
                                >
                                    <p class="text-xs italic text-slate-400">Belum ada komponen nilai untuk semester ini.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── DESKTOP: table ───────────────────────────────────── -->
                    <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:block">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-slate-50/80">
                                    <th class="w-10 px-4 py-3.5 text-left text-xs font-semibold text-slate-400">#</th>
                                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600">Mata Pelajaran</th>
                                    <th class="px-4 py-3.5 text-left">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            <span class="size-1.5 rounded-full bg-blue-500"></span>
                                            Pengetahuan (KI 3)
                                        </span>
                                    </th>
                                    <th class="px-4 py-3.5 text-left">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-2.5 py-1 text-xs font-semibold text-teal-700">
                                            <span class="size-1.5 rounded-full bg-teal-500"></span>
                                            Keterampilan (KI 4)
                                        </span>
                                    </th>
                                    <th
                                        v-if="hasOthers(group.subjects, semester)"
                                        class="px-4 py-3.5 text-left"
                                    >
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            <span class="size-1.5 rounded-full bg-slate-400"></span>
                                            Lainnya
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="(assignment, i) in group.subjects"
                                    :key="assignment.id"
                                    class="group align-top transition-colors duration-150 hover:bg-slate-50/60"
                                >
                                    <td class="px-4 py-4 text-xs font-medium text-slate-300 tabular-nums">{{ i + 1 }}</td>
                                    <td class="px-4 py-4">
                                        <span class="text-sm font-semibold text-slate-800">{{ assignment.subject?.name }}</span>
                                    </td>

                                    <!-- KI 3 -->
                                    <td class="px-4 py-4">
                                        <span v-if="groupByKi(assignment.components, semester).ki3.length === 0" class="text-xs italic text-slate-300">—</span>
                                        <div v-else class="flex flex-wrap gap-1.5">
                                            <Link
                                                v-for="component in groupByKi(assignment.components, semester).ki3"
                                                :key="component.id"
                                                :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                                class="group/chip inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 shadow-sm transition-all duration-150 hover:border-blue-300 hover:bg-blue-100 hover:shadow"
                                            >
                                                {{ component.name }}
                                                <span class="rounded-full bg-blue-200 px-1.5 py-0.5 text-xs font-bold text-blue-700 transition-colors group-hover/chip:bg-blue-300">
                                                    {{ component.weight }}%
                                                </span>
                                            </Link>
                                        </div>
                                    </td>

                                    <!-- KI 4 -->
                                    <td class="px-4 py-4">
                                        <span v-if="groupByKi(assignment.components, semester).ki4.length === 0" class="text-xs italic text-slate-300">—</span>
                                        <div v-else class="flex flex-wrap gap-1.5">
                                            <Link
                                                v-for="component in groupByKi(assignment.components, semester).ki4"
                                                :key="component.id"
                                                :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                                class="group/chip inline-flex items-center gap-1.5 rounded-xl border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-700 shadow-sm transition-all duration-150 hover:border-teal-300 hover:bg-teal-100 hover:shadow"
                                            >
                                                {{ component.name }}
                                                <span class="rounded-full bg-teal-200 px-1.5 py-0.5 text-xs font-bold text-teal-700 transition-colors group-hover/chip:bg-teal-300">
                                                    {{ component.weight }}%
                                                </span>
                                            </Link>
                                        </div>
                                    </td>

                                    <!-- Lainnya -->
                                    <td v-if="hasOthers(group.subjects, semester)" class="px-4 py-4">
                                        <span v-if="groupByKi(assignment.components, semester).others.length === 0" class="text-xs italic text-slate-300">—</span>
                                        <div v-else class="flex flex-wrap gap-1.5">
                                            <Link
                                                v-for="component in groupByKi(assignment.components, semester).others"
                                                :key="component.id"
                                                :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                                class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600 shadow-sm transition-all duration-150 hover:border-slate-300 hover:bg-slate-100 hover:shadow"
                                            >
                                                {{ component.name }}
                                            </Link>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty semester state -->
                                <tr v-if="group.subjects.every(a => a.components?.filter(c => c.semester === semester).length === 0)">
                                    <td
                                        :colspan="hasOthers(group.subjects, semester) ? 5 : 4"
                                        class="px-4 py-10 text-center"
                                    >
                                        <p class="text-sm italic text-slate-400">Belum ada komponen nilai untuk semester ini.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </template>

        </div>
    </AppLayout>
</template>
