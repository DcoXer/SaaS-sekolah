<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    reportCard: { type: Object, required: true },
    reportData: { type: Array,  required: true },
    activeYear: { type: Object, default: null },
});

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const predicateBadge = {
    A: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
    B: 'bg-sky-100 text-sky-700 ring-sky-200',
    C: 'bg-amber-100 text-amber-700 ring-amber-200',
    D: 'bg-red-100 text-red-700 ring-red-200',
};
</script>

<template>
    <AppLayout>
        <Head :title="`Raport Semester ${reportCard.semester}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/siswa/report-cards" class="hover:text-slate-700">Raport</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Semester {{ reportCard.semester }}</span>
            </div>
        </template>

        <div class="space-y-5">
            <BackButton href="/siswa/report-cards" />

            <!-- Info bar -->
            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Kelas</span>
                    <span class="text-sm font-semibold text-slate-800">{{ reportCard.classroom?.name ?? '—' }}</span>
                </div>
                <span class="text-slate-200">·</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Semester</span>
                    <span class="text-sm font-semibold text-slate-800">{{ reportCard.semester }}</span>
                </div>
                <span class="text-slate-200">·</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Tahun Ajaran</span>
                    <span class="text-sm font-semibold text-slate-800">{{ activeYear?.name ?? '—' }}</span>
                </div>
            </div>

            <!-- Nilai per mapel -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <h3 class="text-sm font-semibold text-slate-700">Nilai Mata Pelajaran</h3>
                </div>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 w-8">#</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Mata Pelajaran</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Nilai</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Predikat</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(item, i) in reportData"
                            :key="item.subject?.id"
                            class="transition-[background-color] duration-150 hover:bg-slate-50"
                        >
                            <td class="px-5 py-3.5 text-xs text-slate-400 tabular-nums">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm font-medium text-slate-800">{{ item.subject?.name }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="tabular-nums text-sm font-bold text-slate-800">
                                    {{ item.score ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    v-if="item.predicate"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1"
                                    :class="predicateBadge[item.predicate] ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                >
                                    {{ item.predicate }}
                                </span>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>
                            <td class="px-5 py-3.5 max-w-xs">
                                <span
                                    v-if="item.narratives?.length"
                                    class="text-pretty text-sm text-slate-600"
                                >
                                    {{ item.narratives[0]?.narrative ?? '—' }}
                                </span>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Catatan Wali Kelas & Kamad -->
            <div
                v-if="reportCard.notes"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
            >
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan Wali Kelas</p>
                    <p class="text-pretty text-sm text-slate-700">
                        {{ reportCard.notes.homeroom_notes || '—' }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Catatan Kepala Madrasah</p>
                    <p class="text-pretty text-sm text-slate-700">
                        {{ reportCard.notes.principal_notes || '—' }}
                    </p>
                </div>
            </div>

            <!-- Published at -->
            <p class="text-xs text-slate-400 text-right">
                Dipublish pada {{ formatDate(reportCard.published_at) }}
            </p>

        </div>
    </AppLayout>
</template>
