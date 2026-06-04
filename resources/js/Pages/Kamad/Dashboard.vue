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
        badge:   props.pending.years > 0 ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-400',
        border:  props.pending.years > 0 ? 'border-violet-200' : 'border-slate-100',
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
        badge:   props.pending.letters > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-400',
        border:  props.pending.letters > 0 ? 'border-rose-200' : 'border-slate-100',
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
        badge:   props.pending.reports > 0 ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-400',
        border:  props.pending.reports > 0 ? 'border-sky-200' : 'border-slate-100',
        icon:    'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
    },
]);
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Kamad" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="size-11 shrink-0 overflow-hidden rounded-2xl shadow-sm">
                        <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="size-full object-cover" />
                        <div v-else class="flex size-full items-center justify-center bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white">
                            {{ initials }}
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ greeting }}, {{ user.name.split(' ')[0] }}
                        </h2>
                        <p class="text-xs text-slate-400">
                            {{ activeYear ? activeYear.name : 'Belum ada tahun ajaran aktif' }}
                        </p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">
                    Kepala Madrasah
                </span>
            </div>

            <!-- Summary banner -->
            <div
                v-if="totalPending > 0"
                class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 p-5 text-white shadow-sm"
            >
                <div>
                    <p class="text-sm font-medium text-indigo-100">Perlu Perhatian</p>
                    <p class="text-2xl font-bold tabular-nums">{{ totalPending }} item</p>
                    <p class="text-xs text-indigo-200">menunggu tindakan Anda</p>
                </div>
                <div class="flex size-14 items-center justify-center rounded-2xl bg-white/20">
                    <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>
            </div>

            <!-- All clear -->
            <div
                v-else
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
            >
                <svg class="size-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-emerald-800">Tidak ada item yang memerlukan persetujuan saat ini.</p>
            </div>

            <!-- Pending cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Link
                    v-for="card in pendingCards"
                    :key="card.label"
                    :href="card.href"
                    class="rounded-2xl border bg-white p-5 shadow-sm transition-shadow duration-150 hover:shadow-md"
                    :class="card.border"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div class="inline-flex size-10 items-center justify-center rounded-xl ring-4" :class="[card.bg, card.ring]">
                            <svg class="size-5" :class="card.color" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                            </svg>
                        </div>
                        <span
                            class="inline-flex size-8 items-center justify-center rounded-full text-sm font-bold tabular-nums"
                            :class="card.badge"
                        >
                            {{ card.count }}
                        </span>
                    </div>
                    <p class="text-sm font-semibold text-slate-800">{{ card.label }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ card.sub }}</p>
                </Link>
            </div>

            <!-- Setting shortcut -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Pengaturan</h3>
                </div>
                <Link
                    href="/kamad/school-settings"
                    class="flex items-center justify-between px-5 py-4 transition-colors duration-150 hover:bg-slate-50"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-slate-100">
                            <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Data Sekolah</p>
                            <p class="text-xs text-slate-400">Nama, logo, dan informasi sekolah</p>
                        </div>
                    </div>
                    <svg class="size-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </Link>
            </div>

        </div>
    </AppLayout>
</template>
