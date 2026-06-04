<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeYear: { type: Object, default: null },
    pending:    { type: Object, required: true },
});

const user     = computed(() => usePage().props.auth.user);
const hour     = new Date().getHours();
const greeting = hour < 12 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 19 ? 'Selamat sore' : 'Selamat malam';
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

const totalPending = computed(() =>
    (props.pending.years || 0) + (props.pending.letters || 0) + (props.pending.reports || 0)
);

const pendingCards = computed(() => [
    {
        label:   'Tahun Ajaran',
        sub:     'Menunggu disetujui',
        count:   props.pending.years,
        href:    '/kamad/academic-years',
        active:  props.pending.years > 0,
        color:   'text-violet-600',
        bg:      'bg-violet-50',
        ring:    'ring-violet-100',
        badgeBg: props.pending.years > 0 ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-400',
        border:  props.pending.years > 0 ? 'border-violet-200' : 'border-slate-100',
        pulse:   props.pending.years > 0,
        icon:    'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    },
    {
        label:   'Surat Masuk',
        sub:     'Menunggu disetujui',
        count:   props.pending.letters,
        href:    '/kamad/letters',
        active:  props.pending.letters > 0,
        color:   'text-rose-600',
        bg:      'bg-rose-50',
        ring:    'ring-rose-100',
        badgeBg: props.pending.letters > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-400',
        border:  props.pending.letters > 0 ? 'border-rose-200' : 'border-slate-100',
        pulse:   props.pending.letters > 0,
        icon:    'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    },
    {
        label:   'Raport',
        sub:     'Draft menunggu di-publish',
        count:   props.pending.reports,
        href:    '/kamad/report-cards',
        active:  props.pending.reports > 0,
        color:   'text-sky-600',
        bg:      'bg-sky-50',
        ring:    'ring-sky-100',
        badgeBg: props.pending.reports > 0 ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-400',
        border:  props.pending.reports > 0 ? 'border-sky-200' : 'border-slate-100',
        pulse:   props.pending.reports > 0,
        icon:    'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
    },
]);

const quickNav = [
    {
        label: 'Tahun Ajaran',
        href:  '/kamad/academic-years',
        bg:    'bg-violet-50',
        ring:  'ring-violet-100',
        color: 'text-violet-600',
        hover: 'group-hover:bg-violet-100',
        icon:  'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    },
    {
        label: 'Raport',
        href:  '/kamad/report-cards',
        bg:    'bg-sky-50',
        ring:  'ring-sky-100',
        color: 'text-sky-600',
        hover: 'group-hover:bg-sky-100',
        icon:  'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
    },
    {
        label: 'Surat',
        href:  '/kamad/letters',
        bg:    'bg-rose-50',
        ring:  'ring-rose-100',
        color: 'text-rose-600',
        hover: 'group-hover:bg-rose-100',
        icon:  'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    },
    {
        label: 'Honor Guru',
        href:  '/kamad/teacher-honorariums',
        bg:    'bg-amber-50',
        ring:  'ring-amber-100',
        color: 'text-amber-600',
        hover: 'group-hover:bg-amber-100',
        icon:  'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    },
    {
        label: 'Rekap Absensi',
        href:  '/kamad/attendances',
        bg:    'bg-emerald-50',
        ring:  'ring-emerald-100',
        color: 'text-emerald-600',
        hover: 'group-hover:bg-emerald-100',
        icon:  'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label: 'PPDB',
        href:  '/kamad/ppdb',
        bg:    'bg-indigo-50',
        ring:  'ring-indigo-100',
        color: 'text-indigo-600',
        hover: 'group-hover:bg-indigo-100',
        icon:  'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
    },
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Kamad" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-5 pb-8">

            <!-- ── Welcome banner ─────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 p-6 shadow-lg shadow-indigo-200">
                <!-- Decorative blobs -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10 blur-2xl" />
                <div class="pointer-events-none absolute -bottom-6 left-1/3 size-32 rounded-full bg-white/10 blur-xl" />
                <div class="pointer-events-none absolute right-1/4 bottom-0 size-24 rounded-full bg-purple-400/20 blur-lg" />

                <div class="relative flex items-center gap-4">
                    <!-- Avatar -->
                    <div class="size-14 shrink-0 overflow-hidden rounded-2xl ring-2 ring-white/40 shadow-md">
                        <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="size-full object-cover" />
                        <div v-else class="flex size-full items-center justify-center bg-white/20 text-base font-bold text-white backdrop-blur-sm">
                            {{ initials }}
                        </div>
                    </div>
                    <!-- Text -->
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-indigo-200">{{ greeting }} 👋</p>
                        <h2 class="truncate text-xl font-extrabold text-white leading-tight">{{ user.name }}</h2>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white backdrop-blur-sm">
                                <span class="size-1.5 rounded-full bg-emerald-300"></span>
                                Kepala Madrasah
                            </span>
                            <span v-if="activeYear" class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-indigo-100 backdrop-blur-sm">
                                {{ activeYear.name }}
                            </span>
                            <span v-else class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium text-indigo-200 backdrop-blur-sm">
                                Belum ada tahun ajaran aktif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pending summary strip -->
                <div class="relative mt-5">
                    <div v-if="totalPending > 0" class="grid grid-cols-3 gap-3">
                        <div
                            v-for="card in pendingCards"
                            :key="card.label"
                            class="rounded-2xl bg-white/15 p-3.5 backdrop-blur-sm"
                        >
                            <div class="flex items-center justify-between">
                                <p class="tabular-nums text-2xl font-extrabold text-white">{{ card.count }}</p>
                                <span v-if="card.pulse" class="relative flex size-2">
                                    <span class="animate-ping absolute inline-flex size-full rounded-full bg-rose-300 opacity-75"></span>
                                    <span class="relative inline-flex size-2 rounded-full bg-rose-400"></span>
                                </span>
                            </div>
                            <p class="mt-0.5 text-[11px] font-semibold text-indigo-100 leading-tight">{{ card.label }}</p>
                        </div>
                    </div>
                    <div v-else class="flex items-center gap-3 rounded-2xl bg-white/15 p-3.5 backdrop-blur-sm">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-400/30">
                            <svg class="size-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-white">Semua beres! Tidak ada item tertunda.</p>
                    </div>
                </div>
            </div>

            <!-- ── Pending cards ───────────────────────────────────────────── -->
            <div v-if="totalPending > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Link
                    v-for="card in pendingCards"
                    :key="card.label"
                    :href="card.href"
                    class="group rounded-2xl border bg-white p-5 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md"
                    :class="card.border"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div class="relative inline-flex size-11 items-center justify-center rounded-2xl ring-4 transition-colors" :class="[card.bg, card.ring]">
                            <svg class="size-5 transition-transform group-hover:scale-110" :class="card.color" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                            </svg>
                            <!-- Pulse dot for active -->
                            <span v-if="card.pulse" class="absolute -right-0.5 -top-0.5 flex size-3">
                                <span class="animate-ping absolute inline-flex size-full rounded-full opacity-75"
                                    :class="card.active ? 'bg-rose-400' : 'bg-slate-300'"></span>
                                <span class="relative inline-flex size-3 rounded-full"
                                    :class="card.active ? 'bg-rose-500' : 'bg-slate-300'"></span>
                            </span>
                        </div>
                        <span
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-sm font-bold tabular-nums transition-transform group-hover:scale-110"
                            :class="card.badgeBg"
                        >
                            {{ card.count }}
                        </span>
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ card.label }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ card.sub }}</p>
                    <div class="mt-3 flex items-center gap-1 text-xs font-semibold transition-colors" :class="card.active ? card.color : 'text-slate-300'">
                        <span>Lihat detail</span>
                        <svg class="size-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </Link>
            </div>

            <!-- ── All clear card (when no pending) ──────────────────────── -->
            <div v-if="totalPending === 0" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Link
                    v-for="card in pendingCards"
                    :key="card.label"
                    :href="card.href"
                    class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div class="inline-flex size-11 items-center justify-center rounded-2xl ring-4" :class="[card.bg, card.ring]">
                            <svg class="size-5" :class="card.color" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                            </svg>
                        </div>
                        <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-slate-100 px-2 text-sm font-bold tabular-nums text-slate-400">
                            0
                        </span>
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ card.label }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ card.sub }}</p>
                    <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-emerald-500">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Semua beres</span>
                    </div>
                </Link>
            </div>

            <!-- ── Quick navigation ───────────────────────────────────────── -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Navigasi Cepat</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Akses fitur utama</p>
                </div>
                <div class="grid grid-cols-3 gap-3 p-4 sm:grid-cols-6">
                    <Link
                        v-for="nav in quickNav"
                        :key="nav.label"
                        :href="nav.href"
                        class="group flex flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div class="flex size-10 items-center justify-center rounded-2xl ring-4 transition-colors"
                            :class="[nav.bg, nav.ring, nav.hover]">
                            <svg class="size-5 transition-transform group-hover:scale-110" :class="nav.color" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="nav.icon" />
                            </svg>
                        </div>
                        <span class="text-center text-[11px] font-semibold text-slate-700 leading-tight">{{ nav.label }}</span>
                    </Link>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
