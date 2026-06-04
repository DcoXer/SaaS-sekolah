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

const scoreColor = (score) => {
    if (!score) return 'bg-slate-200';
    if (score >= 85) return 'bg-emerald-500';
    if (score >= 70) return 'bg-sky-500';
    if (score >= 55) return 'bg-amber-500';
    return 'bg-red-500';
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

        <div class="space-y-6">
            <BackButton href="/siswa/report-cards" />

            <!-- Info bar — chip cards -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Kelas chip -->
                    <div class="flex items-center gap-2 rounded-xl bg-violet-50 px-3.5 py-2 ring-1 ring-violet-100">
                        <svg class="size-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                        <div>
                            <p class="text-[10px] font-medium uppercase tracking-wider text-violet-400">Kelas</p>
                            <p class="text-sm font-bold text-violet-800">{{ reportCard.classroom?.name ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Semester chip -->
                    <div class="flex items-center gap-2 rounded-xl bg-purple-50 px-3.5 py-2 ring-1 ring-purple-100">
                        <svg class="size-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <div>
                            <p class="text-[10px] font-medium uppercase tracking-wider text-purple-400">Semester</p>
                            <p class="text-sm font-bold text-purple-800">{{ reportCard.semester }}</p>
                        </div>
                    </div>

                    <!-- Tahun Ajaran chip -->
                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3.5 py-2 ring-1 ring-slate-200">
                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Tahun Ajaran</p>
                            <p class="text-sm font-bold text-slate-700">{{ activeYear?.name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nilai per mapel -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <h3 class="text-sm font-bold tracking-wide text-slate-700 uppercase">Nilai Mata Pelajaran</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50/60">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 w-10">#</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">Mata Pelajaran</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">Nilai</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">Predikat</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(item, i) in reportData"
                                :key="item.subject?.id"
                                class="group transition-colors duration-150 hover:bg-violet-50/40"
                            >
                                <td class="px-5 py-4 text-xs text-slate-400 tabular-nums">{{ i + 1 }}</td>

                                <td class="px-5 py-4">
                                    <span class="text-sm font-semibold text-slate-800">{{ item.subject?.name }}</span>
                                </td>

                                <!-- Score with mini progress bar -->
                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="tabular-nums text-sm font-bold text-slate-800">
                                            {{ item.score ?? '—' }}
                                        </span>
                                        <div v-if="item.score != null" class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                class="h-full rounded-full transition-all duration-500"
                                                :class="scoreColor(item.score)"
                                                :style="{ width: `${Math.min(item.score, 100)}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Predicate badge -->
                                <td class="px-5 py-4">
                                    <span
                                        v-if="item.predicate"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-sm font-extrabold ring-1"
                                        :class="predicateBadge[item.predicate] ?? 'bg-slate-100 text-slate-500 ring-slate-200'"
                                    >
                                        {{ item.predicate }}
                                    </span>
                                    <span v-else class="text-sm text-slate-400">—</span>
                                </td>

                                <td class="px-5 py-4 max-w-xs">
                                    <span
                                        v-if="item.narratives?.length"
                                        class="text-pretty text-sm leading-relaxed text-slate-600"
                                    >
                                        {{ item.narratives[0]?.narrative ?? '—' }}
                                    </span>
                                    <span v-else class="text-sm text-slate-400">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Catatan Wali Kelas & Kamad -->
            <div
                v-if="reportCard.notes"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
            >
                <!-- Catatan Wali Kelas -->
                <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-50/40 p-5 shadow-sm">
                    <!-- Colored left border accent -->
                    <div class="absolute inset-y-0 left-0 w-1 rounded-l-2xl bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
                    <div class="pl-3">
                        <div class="mb-3 flex items-center gap-2">
                            <!-- Quote icon -->
                            <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-100">
                                <svg class="size-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.192 15.757c0-.88-.23-1.618-.69-2.217-.326-.412-.768-.683-1.327-.812-.55-.128-1.07-.137-1.54-.028-.16-.95.1-1.956.76-3.022.66-1.065 1.515-1.867 2.558-2.403L9.373 5c-.8.396-1.56.898-2.26 1.505-.71.607-1.34 1.305-1.9 2.094s-.98 1.68-1.25 2.69-.346 2.04-.217 3.1c.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.94-1.365.94-2.368l.002.003zm9.124 0c0-.88-.23-1.618-.69-2.217-.326-.42-.77-.692-1.327-.817-.56-.124-1.074-.13-1.54-.022-.16-.94.09-1.95.75-3.016.66-1.066 1.515-1.867 2.558-2.403L18.49 5c-.8.396-1.56.898-2.26 1.505-.71.607-1.34 1.305-1.9 2.094s-.978 1.68-1.25 2.69-.344 2.04-.215 3.1c.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.942-1.365.942-2.368l.002.003z" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Catatan Wali Kelas</p>
                        </div>
                        <p class="text-pretty text-sm leading-relaxed text-slate-700">
                            {{ reportCard.notes.homeroom_notes || '—' }}
                        </p>
                    </div>
                </div>

                <!-- Catatan Kamad -->
                <div class="relative overflow-hidden rounded-2xl border border-violet-100 bg-violet-50/40 p-5 shadow-sm">
                    <!-- Colored left border accent -->
                    <div class="absolute inset-y-0 left-0 w-1 rounded-l-2xl bg-gradient-to-b from-violet-400 to-purple-600"></div>
                    <div class="pl-3">
                        <div class="mb-3 flex items-center gap-2">
                            <!-- Quote icon -->
                            <div class="flex size-7 items-center justify-center rounded-lg bg-violet-100">
                                <svg class="size-4 text-violet-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.192 15.757c0-.88-.23-1.618-.69-2.217-.326-.412-.768-.683-1.327-.812-.55-.128-1.07-.137-1.54-.028-.16-.95.1-1.956.76-3.022.66-1.065 1.515-1.867 2.558-2.403L9.373 5c-.8.396-1.56.898-2.26 1.505-.71.607-1.34 1.305-1.9 2.094s-.98 1.68-1.25 2.69-.346 2.04-.217 3.1c.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.94-1.365.94-2.368l.002.003zm9.124 0c0-.88-.23-1.618-.69-2.217-.326-.42-.77-.692-1.327-.817-.56-.124-1.074-.13-1.54-.022-.16-.94.09-1.95.75-3.016.66-1.066 1.515-1.867 2.558-2.403L18.49 5c-.8.396-1.56.898-2.26 1.505-.71.607-1.34 1.305-1.9 2.094s-.978 1.68-1.25 2.69-.344 2.04-.215 3.1c.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.942-1.365.942-2.368l.002.003z" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Catatan Kepala Madrasah</p>
                        </div>
                        <p class="text-pretty text-sm leading-relaxed text-slate-700">
                            {{ reportCard.notes.principal_notes || '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Published at footer -->
            <div class="flex items-center justify-end gap-2 text-xs text-slate-400">
                <svg class="size-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                </svg>
                <span>Dipublish pada {{ formatDate(reportCard.published_at) }}</span>
            </div>

        </div>
    </AppLayout>
</template>
