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

            <!-- Info bar -->
            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Kelas</span>
                    <span class="text-sm font-semibold text-slate-800">{{ classroom.name }}</span>
                </div>
                <span class="text-slate-200">·</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Mapel</span>
                    <span class="text-sm font-semibold text-slate-800">{{ assessmentComponent.subject?.name }}</span>
                </div>
                <span class="text-slate-200">·</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Semester</span>
                    <span class="text-sm font-semibold text-slate-800">{{ assessmentComponent.semester }}</span>
                </div>
                <span class="text-slate-200">·</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Tipe</span>
                    <span class="text-sm font-semibold text-slate-800">{{ typeLabel[assessmentComponent.type] }}</span>
                </div>
                <span v-if="isNumeric" class="text-slate-200">·</span>
                <span v-if="assessmentComponent.ki" class="text-slate-200">·</span>
                <div v-if="assessmentComponent.ki" class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Aspek</span>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="assessmentComponent.ki === 'ki3'
                            ? 'bg-blue-100 text-blue-700'
                            : 'bg-teal-100 text-teal-700'"
                    >
                        {{ kiLabel[assessmentComponent.ki] }}
                    </span>
                </div>
                <span v-if="isNumeric" class="text-slate-200">·</span>
                <div v-if="isNumeric" class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Bobot</span>
                    <span class="text-sm font-semibold text-slate-800">{{ assessmentComponent.weight }}%</span>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="classroom.students.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Belum ada siswa di kelas ini</p>
                <p class="mt-1 text-xs text-slate-400">Operator belum mengisi rombel ini.</p>
            </div>

            <!-- Score input form -->
            <form v-else @submit.prevent="submit">

                <!-- Mobile: card per student -->
                <div class="space-y-3 sm:hidden">
                    <div
                        v-for="(entry, i) in form.scores"
                        :key="entry.student_id"
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <p class="text-sm font-semibold text-slate-800">{{ classroom.students[i]?.name }}</p>
                        <p class="mb-3 mt-0.5 text-xs text-slate-400">
                            <span v-if="isNumeric">Nilai (0–{{ assessmentComponent.max_score ?? 100 }})</span>
                            <span v-else-if="isPredicate">Predikat</span>
                            <span v-else>Narasi</span>
                        </p>
                        <!-- Numeric -->
                        <input
                            v-if="isNumeric"
                            v-model.number="entry.score"
                            type="number"
                            min="0"
                            :max="assessmentComponent.max_score ?? 100"
                            placeholder="—"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                        />
                        <!-- Predicate -->
                        <div v-else-if="isPredicate" class="flex gap-2">
                            <button
                                v-for="p in predicateOptions"
                                :key="p"
                                type="button"
                                @click="entry.predicate = entry.predicate === p ? null : p"
                                class="size-10 rounded-lg text-sm font-bold transition-[background-color,color,border-color] duration-150"
                                :class="entry.predicate === p
                                    ? 'bg-slate-800 text-white border border-slate-800'
                                    : 'border border-slate-200 text-slate-500 hover:border-slate-400'"
                            >{{ p }}</button>
                        </div>
                        <!-- Narrative -->
                        <textarea
                            v-else
                            v-model="entry.narrative"
                            rows="2"
                            placeholder="Tulis catatan narasi..."
                            class="w-full resize-none rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                        />
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 w-8">#</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Nama Siswa</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">
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
                                class="transition-[background-color] duration-150 hover:bg-slate-50"
                            >
                                <td class="px-5 py-3.5 text-xs text-slate-400 tabular-nums">{{ i + 1 }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="text-sm font-medium text-slate-800">
                                        {{ classroom.students[i]?.name }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <!-- Numeric -->
                                    <input
                                        v-if="isNumeric"
                                        v-model.number="entry.score"
                                        type="number"
                                        min="0"
                                        :max="assessmentComponent.max_score ?? 100"
                                        placeholder="—"
                                        class="w-24 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                                    />
                                    <!-- Predicate -->
                                    <div v-else-if="isPredicate" class="flex gap-2">
                                        <button
                                            v-for="p in predicateOptions"
                                            :key="p"
                                            type="button"
                                            @click="entry.predicate = entry.predicate === p ? null : p"
                                            class="size-8 rounded-lg text-xs font-bold transition-[background-color,color,border-color] duration-150"
                                            :class="entry.predicate === p
                                                ? 'bg-slate-800 text-white border border-slate-800'
                                                : 'border border-slate-200 text-slate-500 hover:border-slate-400'"
                                        >{{ p }}</button>
                                    </div>
                                    <!-- Narrative -->
                                    <textarea
                                        v-else
                                        v-model="entry.narrative"
                                        rows="2"
                                        placeholder="Tulis catatan narasi..."
                                        class="w-full resize-none rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Save button -->
                <div class="mt-4 flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                    >
                        <svg
                            v-if="form.processing"
                            class="size-4 animate-spin"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                    </button>
                </div>
            </form>

        </div>
    </AppLayout>
</template>
