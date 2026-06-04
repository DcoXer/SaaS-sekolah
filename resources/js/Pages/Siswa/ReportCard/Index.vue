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

        <div class="space-y-6">

            <!-- Gradient Banner Header -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-violet-700 to-purple-800 px-6 py-8 shadow-lg shadow-violet-200">
                <!-- Decorative circles -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/5"></div>
                <div class="pointer-events-none absolute -bottom-6 right-16 size-24 rounded-full bg-white/5"></div>
                <div class="pointer-events-none absolute bottom-4 -left-4 size-16 rounded-full bg-white/5"></div>

                <div class="relative flex items-center gap-4">
                    <!-- Icon -->
                    <div class="flex size-14 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 shadow-inner ring-1 ring-white/20">
                        <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                    <!-- Text -->
                    <div>
                        <h2 class="text-xl font-bold text-white">Raport Saya</h2>
                        <p class="mt-0.5 text-sm text-violet-200">
                            {{ activeYear ? activeYear.name : 'Belum ada tahun ajaran aktif' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- No active year -->
            <div
                v-if="!activeYear"
                class="flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center shadow-sm"
            >
                <div class="flex size-14 items-center justify-center rounded-full bg-slate-100">
                    <svg class="size-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Tidak ada tahun ajaran aktif</p>
                    <p class="mt-1 text-xs text-slate-400">Hubungi operator sekolah untuk informasi lebih lanjut</p>
                </div>
            </div>

            <!-- Semester cards -->
            <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div
                    v-for="s in semesters"
                    :key="s"
                    class="group relative overflow-hidden rounded-2xl shadow-sm transition-all duration-200"
                    :class="hasSemester(s)
                        ? 'border border-violet-200 bg-white hover:-translate-y-1 hover:shadow-lg hover:shadow-violet-100'
                        : 'border border-dashed border-slate-300 bg-slate-50/70'"
                >
                    <!-- Available: top accent bar -->
                    <div
                        v-if="hasSemester(s)"
                        class="h-1 w-full bg-gradient-to-r from-violet-500 to-purple-500"
                    ></div>

                    <div class="px-6 py-5">
                        <!-- Top row: icon + badge -->
                        <div class="mb-5 flex items-start justify-between">
                            <!-- Icon -->
                            <div
                                class="flex size-12 items-center justify-center rounded-xl shadow-sm"
                                :class="hasSemester(s) ? 'bg-gradient-to-br from-violet-500 to-purple-600 shadow-violet-200' : 'bg-slate-200'"
                            >
                                <svg
                                    v-if="hasSemester(s)"
                                    class="size-6 text-white"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                                <!-- Clock/waiting icon -->
                                <svg
                                    v-else
                                    class="size-6 text-slate-400"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <!-- Status badge -->
                            <span
                                v-if="hasSemester(s)"
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"
                            >
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                Tersedia
                            </span>
                            <span v-else class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-400 ring-1 ring-slate-200">
                                <span class="size-1.5 rounded-full bg-slate-300"></span>
                                Menunggu
                            </span>
                        </div>

                        <!-- Title -->
                        <p class="text-lg font-bold" :class="hasSemester(s) ? 'text-slate-900' : 'text-slate-500'">
                            Semester {{ s }}
                        </p>
                        <p class="mt-0.5 text-sm" :class="hasSemester(s) ? 'text-slate-500' : 'text-slate-400'">
                            {{ activeYear?.name }}
                        </p>

                        <!-- Available: CTA button -->
                        <div v-if="hasSemester(s)" class="mt-5">
                            <Link
                                :href="route('siswa.report-cards.show', s)"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-violet-200 transition-all duration-150 hover:from-violet-700 hover:to-purple-700 hover:shadow-md hover:shadow-violet-300 active:scale-[0.98]"
                            >
                                Lihat Raport
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </Link>
                        </div>

                        <!-- Not available -->
                        <div v-else class="mt-5">
                            <p class="text-center text-xs text-slate-400">Raport belum dipublish oleh guru</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
