<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const addToast = inject('addToast', () => {});

const props = defineProps({
    classroom:           { type: Object, required: true },
    assessmentComponent: { type: Object, required: true },
    scores:              { type: Array,  required: true },
});

// ── Build form from students ──────────────────────────────────────────────────
const scoreMap = Object.fromEntries(props.scores.map(s => [s.student_id, s]));

const form = useForm({
    scores: props.classroom.students.map(student => ({
        student_id: student.id,
        score:      scoreMap[student.id]?.score      ?? null,
        predicate:  scoreMap[student.id]?.predicate  ?? null,
        narrative:  scoreMap[student.id]?.narrative  ?? null,
    })),
});

const submit = () => {
    form.post(route('guru.assessments.bulk-store', props.assessmentComponent.id), {
        preserveScroll: true,
        onSuccess: () => addToast('Nilai berhasil disimpan.', 'success'),
    });
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const isNumeric   = computed(() => props.assessmentComponent.type === 'numeric');
const isPredicate = computed(() => props.assessmentComponent.type === 'predicate');
const isNarrative = computed(() => props.assessmentComponent.type === 'narrative');

const predicateOptions = ['A', 'B', 'C', 'D'];

const typeLabel = {
    numeric:   'Angka',
    predicate: 'Predikat',
    narrative: 'Narasi',
};
const kiLabel = {
    ki3: 'Pengetahuan (KI 3)',
    ki4: 'Keterampilan (KI 4)',
};

// Predicate color map
const predicateColor = {
    A: { base: 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-300 hover:bg-emerald-100', active: 'border-emerald-500 bg-emerald-500 text-white shadow-emerald-200' },
    B: { base: 'border-sky-200 bg-sky-50 text-sky-700 hover:border-sky-300 hover:bg-sky-100',                     active: 'border-sky-500 bg-sky-500 text-white shadow-sky-200' },
    C: { base: 'border-amber-200 bg-amber-50 text-amber-700 hover:border-amber-300 hover:bg-amber-100',           active: 'border-amber-500 bg-amber-500 text-white shadow-amber-200' },
    D: { base: 'border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-300 hover:bg-rose-100',               active: 'border-rose-500 bg-rose-500 text-white shadow-rose-200' },
};
</script>

<template>
    <AppLayout>
        <Head :title="`Nilai — ${assessmentComponent.name}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/guru/assessments" class="hover:text-slate-700">Input Nilai</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ assessmentComponent.name }}</span>
            </div>
        </template>

        <div class="space-y-5">
            <BackButton href="/guru/assessments" />

            <!-- ── Info card ──────────────────────────────────────────────────── -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <!-- top accent stripe -->
                <div
                    class="h-1.5 w-full"
                    :class="assessmentComponent.ki === 'ki3' ? 'bg-gradient-to-r from-blue-400 to-blue-500'
                          : assessmentComponent.ki === 'ki4' ? 'bg-gradient-to-r from-teal-400 to-teal-500'
                          : 'bg-gradient-to-r from-amber-400 to-orange-500'"
                ></div>
                <div class="flex flex-wrap gap-3 px-5 py-4">
                    <!-- Kelas -->
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs font-medium text-slate-400">Kelas</span>
                        <span class="inline-flex items-center rounded-lg bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700">
                            {{ classroom.name }}
                        </span>
                    </div>
                    <!-- Mapel -->
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs font-medium text-slate-400">Mata Pelajaran</span>
                        <span class="inline-flex items-center rounded-lg bg-sky-100 px-2.5 py-1 text-xs font-bold text-sky-700">
                            {{ assessmentComponent.subject?.name }}
                        </span>
                    </div>
                    <!-- Semester -->
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs font-medium text-slate-400">Semester</span>
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                            Semester {{ assessmentComponent.semester }}
                        </span>
                    </div>
                    <!-- Tipe -->
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs font-medium text-slate-400">Tipe</span>
                        <span
                            class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold"
                            :class="isNumeric ? 'bg-emerald-100 text-emerald-700'
                                  : isPredicate ? 'bg-amber-100 text-amber-700'
                                  : 'bg-purple-100 text-purple-700'"
                        >
                            {{ typeLabel[assessmentComponent.type] }}
                        </span>
                    </div>
                    <!-- Aspek KI -->
                    <div v-if="assessmentComponent.ki" class="flex flex-col gap-0.5">
                        <span class="text-xs font-medium text-slate-400">Aspek</span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold"
                            :class="assessmentComponent.ki === 'ki3' ? 'bg-blue-100 text-blue-700' : 'bg-teal-100 text-teal-700'"
                        >
                            <span class="size-1.5 rounded-full" :class="assessmentComponent.ki === 'ki3' ? 'bg-blue-500' : 'bg-teal-500'"></span>
                            {{ kiLabel[assessmentComponent.ki] }}
                        </span>
                    </div>
                    <!-- Bobot -->
                    <div v-if="isNumeric" class="flex flex-col gap-0.5">
                        <span class="text-xs font-medium text-slate-400">Bobot</span>
                        <span class="inline-flex items-center rounded-lg bg-orange-100 px-2.5 py-1 text-xs font-bold text-orange-700">
                            {{ assessmentComponent.weight }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ────────────────────────────────────────────────── -->
            <div
                v-if="classroom.students.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center shadow-sm"
            >
                <div class="mb-3 flex size-14 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="size-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Belum ada siswa di kelas ini</p>
                <p class="mt-1 text-xs text-slate-400">Operator belum mengisi rombel ini.</p>
            </div>

            <!-- ── Score input form ───────────────────────────────────────────── -->
            <form v-else @submit.prevent="submit">

                <!-- Mobile: card per student -->
                <div class="space-y-3 sm:hidden">
                    <div
                        v-for="(entry, i) in form.scores"
                        :key="entry.student_id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <!-- Student header -->
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-600 tabular-nums">
                                {{ i + 1 }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ classroom.students[i]?.name }}</p>
                                <p class="text-xs text-slate-400">
                                    <span v-if="isNumeric">Nilai angka · maks {{ assessmentComponent.max_score ?? 100 }}</span>
                                    <span v-else-if="isPredicate">Pilih predikat</span>
                                    <span v-else>Catatan narasi</span>
                                </p>
                            </div>
                        </div>

                        <div class="px-4 py-3.5">
                            <!-- Numeric -->
                            <div v-if="isNumeric" class="relative">
                                <input
                                    v-model.number="entry.score"
                                    type="number"
                                    min="0"
                                    :max="assessmentComponent.max_score ?? 100"
                                    placeholder="0"
                                    class="w-full rounded-xl border px-4 py-3 pr-20 text-lg font-bold text-slate-800 outline-none transition-all duration-150 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-400/30"
                                    :class="entry.score !== null && entry.score !== ''
                                        ? 'border-emerald-300 bg-emerald-50/50 focus:border-emerald-400'
                                        : 'border-slate-200 bg-white focus:border-emerald-400'"
                                />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-sm font-medium text-slate-400">/ {{ assessmentComponent.max_score ?? 100 }}</span>
                                </div>
                            </div>
                            <!-- Predicate -->
                            <div v-else-if="isPredicate" class="flex gap-2.5">
                                <button
                                    v-for="p in predicateOptions"
                                    :key="p"
                                    type="button"
                                    @click="entry.predicate = entry.predicate === p ? null : p"
                                    class="flex size-12 items-center justify-center rounded-xl border text-base font-extrabold shadow-sm transition-all duration-150 active:scale-95"
                                    :class="entry.predicate === p
                                        ? predicateColor[p].active + ' shadow-md'
                                        : predicateColor[p].base"
                                >{{ p }}</button>
                            </div>
                            <!-- Narrative -->
                            <textarea
                                v-else
                                v-model="entry.narrative"
                                rows="3"
                                placeholder="Tulis catatan narasi untuk siswa ini..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-300 outline-none transition-all duration-150 focus:border-purple-400 focus:ring-2 focus:ring-purple-400/20"
                            />
                        </div>
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:block">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="w-12 px-5 py-3.5 text-left text-xs font-semibold text-slate-400">#</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-600">Nama Siswa</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-600">
                                    <span v-if="isNumeric">Nilai (0–{{ assessmentComponent.max_score ?? 100 }})</span>
                                    <span v-else-if="isPredicate">Predikat</span>
                                    <span v-else>Narasi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(entry, i) in form.scores"
                                :key="entry.student_id"
                                class="transition-colors duration-150 hover:bg-slate-50/60"
                            >
                                <td class="px-5 py-4 text-xs font-semibold text-slate-300 tabular-nums">{{ i + 1 }}</td>
                                <td class="px-5 py-4">
                                    <span class="text-sm font-semibold text-slate-800">
                                        {{ classroom.students[i]?.name }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <!-- Numeric -->
                                    <div v-if="isNumeric" class="relative w-32">
                                        <input
                                            v-model.number="entry.score"
                                            type="number"
                                            min="0"
                                            :max="assessmentComponent.max_score ?? 100"
                                            placeholder="—"
                                            class="w-full rounded-lg border px-3 py-2 pr-10 text-sm font-semibold text-slate-800 outline-none transition-all duration-150 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-400/20"
                                            :class="entry.score !== null && entry.score !== ''
                                                ? 'border-emerald-300 bg-emerald-50/60 focus:border-emerald-400'
                                                : 'border-slate-200 bg-white focus:border-emerald-400'"
                                        />
                                        <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-xs text-slate-300">
                                            /{{ assessmentComponent.max_score ?? 100 }}
                                        </span>
                                    </div>
                                    <!-- Predicate -->
                                    <div v-else-if="isPredicate" class="flex gap-1.5">
                                        <button
                                            v-for="p in predicateOptions"
                                            :key="p"
                                            type="button"
                                            @click="entry.predicate = entry.predicate === p ? null : p"
                                            class="flex size-9 items-center justify-center rounded-lg border text-xs font-extrabold shadow-sm transition-all duration-150 hover:scale-105 active:scale-95"
                                            :class="entry.predicate === p
                                                ? predicateColor[p].active + ' shadow-md'
                                                : predicateColor[p].base"
                                        >{{ p }}</button>
                                    </div>
                                    <!-- Narrative -->
                                    <textarea
                                        v-else
                                        v-model="entry.narrative"
                                        rows="2"
                                        placeholder="Tulis catatan..."
                                        class="w-full resize-none rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-300 outline-none transition-all duration-150 focus:border-purple-400 focus:ring-2 focus:ring-purple-400/20"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ── Save button ────────────────────────────────────────────── -->
                <!-- Mobile: sticky bottom -->
                <div class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-4 backdrop-blur-sm sm:hidden">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition-all duration-150 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-emerald-500/40 disabled:opacity-60 active:scale-[0.99]"
                    >
                        <svg
                            v-if="form.processing"
                            class="size-4 animate-spin"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <svg
                            v-else
                            class="size-4"
                            fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                    </button>
                </div>
                <!-- Bottom spacer for mobile sticky bar -->
                <div class="h-24 sm:hidden"></div>

                <!-- Desktop: right-aligned button -->
                <div class="mt-5 hidden justify-end sm:flex">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-emerald-500/20 transition-all duration-150 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 disabled:opacity-60"
                    >
                        <svg
                            v-if="form.processing"
                            class="size-4 animate-spin"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <svg
                            v-else
                            class="size-4"
                            fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                    </button>
                </div>

            </form>

        </div>
    </AppLayout>
</template>
