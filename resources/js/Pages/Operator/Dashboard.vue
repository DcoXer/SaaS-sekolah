<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

const props = defineProps({
    stats:           { type: Object, required: true },
    studentsByGrade: { type: Array,  default: () => [] },
    activeYear:      { type: Object, default: null },
    pendingYear:     { type: Object, default: null },
});

const user     = computed(() => usePage().props.auth.user);
const hour     = new Date().getHours();
const greeting = hour < 12 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 19 ? 'Selamat sore' : 'Selamat malam';
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

const statCards = computed(() => [
    {
        label: 'Total Guru',
        value: props.stats.teachers,
        href:  '/operator/teachers',
        color: 'text-violet-600',
        bg:    'bg-violet-50',
        ring:  'ring-violet-100',
        hover: 'group-hover:bg-violet-100',
        icon:  'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    },
    {
        label: 'Siswa Aktif',
        value: props.stats.students,
        href:  '/operator/students',
        color: 'text-sky-600',
        bg:    'bg-sky-50',
        ring:  'ring-sky-100',
        hover: 'group-hover:bg-sky-100',
        icon:  'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    },
    {
        label: 'Kelas Aktif',
        value: props.stats.classrooms,
        href:  '/operator/classrooms',
        color: 'text-primary-600',
        bg:    'bg-primary-50',
        ring:  'ring-primary-100',
        hover: 'group-hover:bg-primary-100',
        icon:  'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    },
    {
        label: 'Mata Pelajaran',
        value: props.stats.subjects,
        href:  '/operator/subjects',
        color: 'text-amber-600',
        bg:    'bg-amber-50',
        ring:  'ring-amber-100',
        hover: 'group-hover:bg-amber-100',
        icon:  'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    },
]);

const chartData = computed(() => ({
    labels: props.studentsByGrade.map(g => `Kelas ${g.grade}`),
    datasets: [{
        label: 'Siswa',
        data:  props.studentsByGrade.map(g => g.total),
        backgroundColor: ['#818cf8', '#a78bfa', '#38bdf8', '#34d399', '#fbbf24', '#f87171'],
        borderRadius: 8,
        borderSkipped: false,
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: { label: ctx => ` ${ctx.parsed.y} siswa` },
        },
    },
    scales: {
        x: {
            grid:   { display: false },
            border: { display: false },
            ticks:  { font: { size: 12 }, color: '#94a3b8' },
        },
        y: {
            grid:   { color: '#f1f5f9' },
            border: { display: false },
            ticks:  { stepSize: 5, font: { size: 11 }, color: '#94a3b8' },
        },
    },
};

const quickActions = [
    {
        label: 'Tahun Ajaran',
        href:  '/operator/academic-years',
        bg:    'bg-violet-50',
        ring:  'ring-violet-100',
        color: 'text-violet-600',
        hover: 'group-hover:bg-violet-100',
        icon:  'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    },
    {
        label: 'Kelola Guru',
        href:  '/operator/teachers',
        bg:    'bg-sky-50',
        ring:  'ring-sky-100',
        color: 'text-sky-600',
        hover: 'group-hover:bg-sky-100',
        icon:  'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    },
    {
        label: 'Kelola Siswa',
        href:  '/operator/students',
        bg:    'bg-primary-50',
        ring:  'ring-primary-100',
        color: 'text-primary-600',
        hover: 'group-hover:bg-primary-100',
        icon:  'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    },
    {
        label: 'Atur Kelas',
        href:  '/operator/classrooms',
        bg:    'bg-amber-50',
        ring:  'ring-amber-100',
        color: 'text-amber-600',
        hover: 'group-hover:bg-amber-100',
        icon:  'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    },
    {
        label: 'Komponen Nilai',
        href:  '/operator/assessment-components',
        bg:    'bg-pink-50',
        ring:  'ring-pink-100',
        color: 'text-pink-600',
        hover: 'group-hover:bg-pink-100',
        icon:  'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605',
    },
    {
        label: 'Setting Sekolah',
        href:  '/operator/school-settings',
        bg:    'bg-indigo-50',
        ring:  'ring-indigo-100',
        color: 'text-indigo-600',
        hover: 'group-hover:bg-indigo-100',
        icon:  'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
        label: 'Jam Pelajaran',
        href:  '/operator/teaching-hours',
        bg:    'bg-rose-50',
        ring:  'ring-rose-100',
        color: 'text-rose-600',
        hover: 'group-hover:bg-rose-100',
        icon:  'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    },
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Operator" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-5 pb-8">

            <!-- ── Welcome banner ─────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 p-6 shadow-lg shadow-violet-200">
                <!-- Decorative blobs -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10 blur-2xl" />
                <div class="pointer-events-none absolute -bottom-6 left-1/3 size-32 rounded-full bg-white/10 blur-xl" />
                <div class="pointer-events-none absolute right-1/4 bottom-0 size-24 rounded-full bg-indigo-400/20 blur-lg" />

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
                        <p class="text-sm font-medium text-violet-200">{{ greeting }} 👋</p>
                        <h2 class="truncate text-xl font-extrabold text-white leading-tight">{{ user.name }}</h2>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white backdrop-blur-sm">
                                <span class="size-1.5 rounded-full bg-primary-300"></span>
                                Operator
                            </span>
                            <span v-if="activeYear" class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-violet-100 backdrop-blur-sm">
                                {{ activeYear.name }}
                            </span>
                            <span v-else class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium text-violet-200 backdrop-blur-sm">
                                Belum ada tahun ajaran aktif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats strip -->
                <div class="relative mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div v-for="card in statCards" :key="card.label" class="rounded-2xl bg-white/15 p-3.5 backdrop-blur-sm">
                        <p class="tabular-nums text-2xl font-extrabold text-white">{{ card.value }}</p>
                        <p class="mt-0.5 text-[11px] font-semibold text-violet-100 leading-tight">{{ card.label }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Pending year alert ──────────────────────────────────────── -->
            <div
                v-if="pendingYear"
                class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-4 shadow-sm"
            >
                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-amber-100">
                    <svg class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-amber-900">Menunggu Persetujuan Kamad</p>
                    <p class="mt-0.5 text-xs text-amber-700">
                        Tahun ajaran <span class="font-semibold">{{ pendingYear.name }}</span> belum disetujui Kepala Madrasah.
                    </p>
                </div>
                <Link
                    href="/operator/academic-years"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-amber-600 hover:shadow-md"
                >
                    <span>Lihat</span>
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </Link>
            </div>

            <!-- ── Stat cards ──────────────────────────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Link
                    v-for="card in statCards"
                    :key="card.label"
                    :href="card.href"
                    class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="mb-3 inline-flex size-11 items-center justify-center rounded-2xl ring-4 transition-colors"
                        :class="[card.bg, card.ring, card.hover]">
                        <svg class="size-5 transition-transform group-hover:scale-110" :class="card.color" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                        </svg>
                    </div>
                    <p class="tabular-nums text-2xl font-extrabold text-slate-900">{{ card.value }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-400">{{ card.label }}</p>
                </Link>
            </div>

            <!-- ── Chart + Quick actions ───────────────────────────────────── -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                <!-- Bar chart -->
                <div class="rounded-2xl border border-slate-100 bg-white shadow-sm lg:col-span-2 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-50 px-5 py-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Sebaran Siswa per Kelas</h3>
                            <p class="mt-0.5 text-xs text-slate-400">Siswa aktif tahun ajaran ini</p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-xl bg-violet-50 ring-4 ring-violet-100">
                            <svg class="size-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="h-52">
                            <Bar :data="chartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-50 px-5 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Aksi Cepat</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Kelola data sekolah</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 p-3 sm:grid-cols-4 lg:grid-cols-3">
                        <Link
                            v-for="action in quickActions"
                            :key="action.label"
                            :href="action.href"
                            class="group flex flex-col items-center gap-1.5 rounded-xl border border-slate-100 bg-white p-2.5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <div class="flex size-9 items-center justify-center rounded-xl ring-4 transition-colors"
                                :class="[action.bg, action.ring, action.hover]">
                                <svg class="size-4 transition-transform group-hover:scale-110" :class="action.color" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="action.icon" />
                                </svg>
                            </div>
                            <span class="text-center text-[10px] font-semibold text-slate-700 leading-tight">{{ action.label }}</span>
                        </Link>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>
