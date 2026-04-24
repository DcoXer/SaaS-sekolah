<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    reportCards: { type: Object, required: true }, // { 1: reportCard, 2: reportCard }
    activeYear:  { type: Object, default: null },
});

const semesters = [1, 2];
const hasSemester = (s) => !!props.reportCards[s];
</script>

<template>
    <AppLayout>
        <Head title="Raport" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Siswa</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Raport</span>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Heading -->
            <div>
                <h2 class="text-balance text-lg font-bold text-slate-900">Raport Saya</h2>
                <p class="text-pretty text-sm text-slate-500">
                    {{ activeYear ? `Tahun ajaran: ${activeYear.name}` : 'Belum ada tahun ajaran aktif.' }}
                </p>
            </div>

            <!-- No active year -->
            <div
                v-if="!activeYear"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
            </div>

            <!-- Semester cards -->
            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    v-for="s in semesters"
                    :key="s"
                    class="overflow-hidden rounded-xl border shadow-sm transition-[border-color] duration-150"
                    :class="hasSemester(s) ? 'border-slate-200 bg-white hover:border-slate-300' : 'border-dashed border-slate-200 bg-slate-50'"
                >
                    <div class="px-6 py-5">
                        <div class="mb-4 flex items-start justify-between">
                            <div class="flex size-10 items-center justify-center rounded-full bg-violet-100">
                                <svg class="size-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                            </div>
                            <span
                                v-if="hasSemester(s)"
                                class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"
                            >
                                Tersedia
                            </span>
                            <span v-else class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-400 ring-1 ring-slate-200">
                                Belum ada
                            </span>
                        </div>

                        <p class="text-base font-bold text-slate-900">Semester {{ s }}</p>
                        <p class="mt-0.5 text-sm text-slate-500">{{ activeYear?.name }}</p>
                    </div>

                    <div class="border-t border-slate-100 px-6 py-4">
                        <Link
                            v-if="hasSemester(s)"
                            :href="route('siswa.report-cards.show', s)"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-violet-600 hover:text-violet-700"
                        >
                            Lihat Raport
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                        <span v-else class="text-sm text-slate-400">Raport belum dipublish</span>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
