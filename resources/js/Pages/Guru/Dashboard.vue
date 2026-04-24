<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeYear:  { type: String, default: null },
    stats:       { type: Object, required: true },
    assignments: { type: Array,  default: () => [] },
});

const user     = computed(() => usePage().props.auth.user);
const hour     = new Date().getHours();
const greeting = hour < 12 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 19 ? 'Selamat sore' : 'Selamat malam';
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

const byClassroom = computed(() => {
    const map = {};
    for (const a of props.assignments) {
        if (!map[a.classroom_name]) map[a.classroom_name] = [];
        map[a.classroom_name].push(a.subject_name);
    }
    return map;
});

const classroomColors = [
    'bg-violet-50 text-violet-700 ring-violet-100',
    'bg-sky-50 text-sky-700 ring-sky-100',
    'bg-emerald-50 text-emerald-700 ring-emerald-100',
    'bg-amber-50 text-amber-700 ring-amber-100',
    'bg-pink-50 text-pink-700 ring-pink-100',
    'bg-cyan-50 text-cyan-700 ring-cyan-100',
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Guru" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-sm font-bold text-white shadow-sm">
                        {{ initials }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ greeting }}, {{ user.name.split(' ')[0] }}
                        </h2>
                        <p class="text-xs text-slate-400">
                            {{ activeYear ?? 'Belum ada tahun ajaran aktif' }}
                        </p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                    Guru
                </span>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="mb-3 inline-flex size-10 items-center justify-center rounded-xl bg-amber-50 ring-4 ring-amber-100">
                        <svg class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <p class="tabular-nums text-2xl font-bold text-slate-900">{{ stats.classrooms }}</p>
                    <p class="mt-0.5 text-xs font-medium text-slate-400">Kelas Diajar</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="mb-3 inline-flex size-10 items-center justify-center rounded-xl bg-sky-50 ring-4 ring-sky-100">
                        <svg class="size-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <p class="tabular-nums text-2xl font-bold text-slate-900">{{ stats.subjects }}</p>
                    <p class="mt-0.5 text-xs font-medium text-slate-400">Mata Pelajaran</p>
                </div>
            </div>

            <!-- No assignment -->
            <div
                v-if="assignments.length === 0"
                class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center"
            >
                <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl bg-slate-200">
                    <svg class="size-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-600">Belum ada penugasan</p>
                <p class="mt-1 text-xs text-slate-400">Hubungi operator untuk mengatur penugasan kelas dan mata pelajaran.</p>
            </div>

            <!-- Assignments -->
            <div v-else class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Penugasan Tahun Ini</h3>
                </div>
                <div class="divide-y divide-slate-50">
                    <div
                        v-for="(subjects, classroom, i) in byClassroom"
                        :key="classroom"
                        class="flex items-start gap-4 px-5 py-4"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold ring-4"
                            :class="classroomColors[i % classroomColors.length]"
                        >
                            {{ classroom.replace(/[^0-9]/g, '') || classroom[0] }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-800">{{ classroom }}</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="s in subjects"
                                    :key="s"
                                    class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                                >
                                    {{ s }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="grid grid-cols-2 gap-3">
                <Link
                    href="/guru/assessments"
                    class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-shadow duration-150 hover:shadow-md"
                >
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-amber-50 ring-4 ring-amber-100">
                        <svg class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-800">Input Nilai</p>
                    <p class="mt-0.5 text-xs text-slate-400">Isi nilai per komponen</p>
                </Link>
                <Link
                    href="/guru/report-cards"
                    class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-shadow duration-150 hover:shadow-md"
                >
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-sky-50 ring-4 ring-sky-100">
                        <svg class="size-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-800">Raport</p>
                    <p class="mt-0.5 text-xs text-slate-400">Rekap & catatan raport</p>
                </Link>
            </div>

        </div>
    </AppLayout>
</template>
