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

        <div class="space-y-5">

            <!-- Page heading + semester tabs -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Input Nilai</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Pilih komponen nilai untuk mulai menginput.
                    </p>
                </div>
                <div class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                    <button
                        v-for="s in [1, 2]"
                        :key="s"
                        @click="semester = s"
                        class="rounded-md px-4 py-1.5 text-sm font-semibold transition-[background-color,color] duration-150"
                        :class="semester === s ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-700'"
                    >
                        Semester {{ s }}
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="assignments.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada penugasan</p>
                <p class="mt-1 text-xs text-slate-400">Anda belum di-assign ke kelas manapun.</p>
            </div>

            <!-- Classrooms -->
            <template v-else>
                <div
                    v-for="group in byClassroom"
                    :key="group.classroom?.id"
                    class="space-y-2"
                >
                    <!-- Classroom label -->
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        {{ group.classroom?.name }}
                        <span class="normal-case font-normal text-slate-300">· {{ group.classroom?.academic_year?.name ?? '' }}</span>
                    </h3>

                    <!-- ── MOBILE: card per subject ─────────────────────────── -->
                    <div class="space-y-2 sm:hidden">
                        <div
                            v-for="assignment in group.subjects"
                            :key="assignment.id"
                            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                        >
                            <!-- Subject header -->
                            <div class="border-b border-slate-100 bg-slate-50 px-4 py-2.5">
                                <span class="text-sm font-semibold text-slate-800">{{ assignment.subject?.name }}</span>
                            </div>

                            <!-- KI sections -->
                            <div class="divide-y divide-slate-100">
                                <!-- KI 3 -->
                                <div
                                    v-if="groupByKi(assignment.components, semester).ki3.length > 0"
                                    class="px-4 py-3"
                                >
                                    <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-blue-600">
                                        <span class="inline-block size-1.5 rounded-full bg-blue-400"></span>
                                        Pengetahuan (KI 3)
                                    </p>
                                    <div class="space-y-1.5">
                                        <Link
                                            v-for="component in groupByKi(assignment.components, semester).ki3"
                                            :key="component.id"
                                            :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                            class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 transition-[background-color] duration-150 active:bg-blue-100"
                                        >
                                            <span class="text-sm text-blue-800">{{ component.name }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-blue-400">{{ component.weight }}%</span>
                                                <svg class="size-4 text-blue-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </div>
                                        </Link>
                                    </div>
                                </div>

                                <!-- KI 4 -->
                                <div
                                    v-if="groupByKi(assignment.components, semester).ki4.length > 0"
                                    class="px-4 py-3"
                                >
                                    <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-teal-600">
                                        <span class="inline-block size-1.5 rounded-full bg-teal-400"></span>
                                        Keterampilan (KI 4)
                                    </p>
                                    <div class="space-y-1.5">
                                        <Link
                                            v-for="component in groupByKi(assignment.components, semester).ki4"
                                            :key="component.id"
                                            :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                            class="flex items-center justify-between rounded-lg border border-teal-100 bg-teal-50 px-3 py-2 transition-[background-color] duration-150 active:bg-teal-100"
                                        >
                                            <span class="text-sm text-teal-800">{{ component.name }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-teal-400">{{ component.weight }}%</span>
                                                <svg class="size-4 text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </div>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Lainnya -->
                                <div
                                    v-if="groupByKi(assignment.components, semester).others.length > 0"
                                    class="px-4 py-3"
                                >
                                    <p class="mb-2 text-xs font-semibold text-slate-500">Lainnya</p>
                                    <div class="space-y-1.5">
                                        <Link
                                            v-for="component in groupByKi(assignment.components, semester).others"
                                            :key="component.id"
                                            :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                            class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 transition-[background-color] duration-150 active:bg-slate-100"
                                        >
                                            <span class="text-sm text-slate-700">{{ component.name }}</span>
                                            <svg class="size-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Empty -->
                                <div
                                    v-if="assignment.components?.filter(c => c.semester === semester).length === 0"
                                    class="px-4 py-4 text-center text-xs text-slate-400"
                                >
                                    Belum ada komponen nilai untuk semester ini.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── DESKTOP: table ───────────────────────────────────── -->
                    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm sm:block">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="w-8 px-4 py-3 text-left text-xs font-semibold text-slate-500">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Mata Pelajaran</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-blue-600">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block size-1.5 rounded-full bg-blue-400"></span>
                                            Pengetahuan (KI 3)
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-600">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block size-1.5 rounded-full bg-teal-400"></span>
                                            Keterampilan (KI 4)
                                        </div>
                                    </th>
                                    <th
                                        v-if="hasOthers(group.subjects, semester)"
                                        class="px-4 py-3 text-left text-xs font-semibold text-slate-500"
                                    >
                                        Lainnya
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="(assignment, i) in group.subjects"
                                    :key="assignment.id"
                                    class="align-top transition-[background-color] duration-150 hover:bg-slate-50/60"
                                >
                                    <td class="px-4 py-3.5 text-xs text-slate-400 tabular-nums">{{ i + 1 }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="text-sm font-medium text-slate-800">{{ assignment.subject?.name }}</span>
                                    </td>

                                    <!-- KI 3 -->
                                    <td class="px-4 py-3.5">
                                        <span v-if="groupByKi(assignment.components, semester).ki3.length === 0" class="text-xs italic text-slate-300">—</span>
                                        <div v-else class="flex flex-wrap gap-1.5">
                                            <Link
                                                v-for="component in groupByKi(assignment.components, semester).ki3"
                                                :key="component.id"
                                                :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                                class="group inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 transition-[background-color,border-color] duration-150 hover:border-blue-300 hover:bg-blue-100"
                                            >
                                                {{ component.name }}
                                                <span class="text-blue-400 group-hover:text-blue-600">{{ component.weight }}%</span>
                                            </Link>
                                        </div>
                                    </td>

                                    <!-- KI 4 -->
                                    <td class="px-4 py-3.5">
                                        <span v-if="groupByKi(assignment.components, semester).ki4.length === 0" class="text-xs italic text-slate-300">—</span>
                                        <div v-else class="flex flex-wrap gap-1.5">
                                            <Link
                                                v-for="component in groupByKi(assignment.components, semester).ki4"
                                                :key="component.id"
                                                :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                                class="group inline-flex items-center gap-1 rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-700 transition-[background-color,border-color] duration-150 hover:border-teal-300 hover:bg-teal-100"
                                            >
                                                {{ component.name }}
                                                <span class="text-teal-400 group-hover:text-teal-600">{{ component.weight }}%</span>
                                            </Link>
                                        </div>
                                    </td>

                                    <!-- Lainnya -->
                                    <td v-if="hasOthers(group.subjects, semester)" class="px-4 py-3.5">
                                        <span v-if="groupByKi(assignment.components, semester).others.length === 0" class="text-xs italic text-slate-300">—</span>
                                        <div v-else class="flex flex-wrap gap-1.5">
                                            <Link
                                                v-for="component in groupByKi(assignment.components, semester).others"
                                                :key="component.id"
                                                :href="route('guru.assessments.show', { classroom: assignment.classroom_id, assessmentComponent: component.id })"
                                                class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
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
                                        class="px-4 py-8 text-center text-xs text-slate-400"
                                    >
                                        Belum ada komponen nilai untuk semester ini.
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
