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
        icon:  'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    },
    {
        label: 'Siswa Aktif',
        value: props.stats.students,
        href:  '/operator/students',
        color: 'text-sky-600',
        bg:    'bg-sky-50',
        ring:  'ring-sky-100',
        icon:  'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    },
    {
        label: 'Kelas Aktif',
        value: props.stats.classrooms,
        href:  '/operator/classrooms',
        color: 'text-emerald-600',
        bg:    'bg-emerald-50',
        ring:  'ring-emerald-100',
        icon:  'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    },
    {
        label: 'Mata Pelajaran',
        value: props.stats.subjects,
        href:  '/operator/subjects',
        color: 'text-amber-600',
        bg:    'bg-amber-50',
        ring:  'ring-amber-100',
        icon:  'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    },
]);

const chartData = computed(() => ({
    labels: props.studentsByGrade.map(g => g.grade),
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

const quickLinks = [
    { label: 'Kelola Tahun Ajaran', href: '/operator/academic-years',        color: 'bg-violet-50 text-violet-700', dot: 'bg-violet-400' },
    { label: 'Kelola Guru',         href: '/operator/teachers',              color: 'bg-sky-50 text-sky-700',       dot: 'bg-sky-400' },
    { label: 'Kelola Siswa',        href: '/operator/students',              color: 'bg-emerald-50 text-emerald-700', dot: 'bg-emerald-400' },
    { label: 'Atur Kelas',          href: '/operator/classrooms',            color: 'bg-amber-50 text-amber-700',   dot: 'bg-amber-400' },
    { label: 'Komponen Nilai',      href: '/operator/assessment-components', color: 'bg-pink-50 text-pink-700',     dot: 'bg-pink-400' },
    { label: 'Jam Pelajaran Guru',  href: '/operator/teaching-hours',        color: 'bg-indigo-50 text-indigo-700', dot: 'bg-indigo-400' },
    { label: 'Setting Sekolah',     href: '/operator/school-settings',       color: 'bg-slate-100 text-slate-700',  dot: 'bg-slate-400' },
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Operator" />

        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="size-11 shrink-0 overflow-hidden rounded-2xl shadow-sm">
                        <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="size-full object-cover" />
                        <div v-else class="flex size-full items-center justify-center bg-gradient-to-br from-violet-500 to-indigo-600 text-sm font-bold text-white">
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
                <span class="hidden sm:inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">
                    Operator
                </span>
            </div>

            <!-- Pending year alert -->
            <div
                v-if="pendingYear"
                class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4"
            >
                <svg class="mt-0.5 size-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <p class="text-sm text-amber-800">
                    Tahun ajaran <span class="font-semibold">{{ pendingYear.name }}</span> menunggu persetujuan Kepala Madrasah.
                </p>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Link
                    v-for="card in statCards"
                    :key="card.label"
                    :href="card.href"
                    class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-shadow duration-150 hover:shadow-md"
                >
                    <div class="mb-3 inline-flex size-10 items-center justify-center rounded-xl ring-4" :class="[card.bg, card.ring]">
                        <svg class="size-5" :class="card.color" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                        </svg>
                    </div>
                    <p class="tabular-nums text-2xl font-bold text-slate-900">{{ card.value }}</p>
                    <p class="mt-0.5 text-xs font-medium text-slate-400">{{ card.label }}</p>
                </Link>
            </div>

            <!-- Chart + Quick links -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                <!-- Bar chart -->
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Sebaran Siswa per Kelas</h3>
                            <p class="text-xs text-slate-400">Siswa aktif tahun ajaran ini</p>
                        </div>
                    </div>
                    <div class="h-52">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Quick links -->
                <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-50 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-800">Aksi Cepat</h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <Link
                            v-for="link in quickLinks"
                            :key="link.label"
                            :href="link.href"
                            class="flex items-center gap-3 px-5 py-3 transition-colors duration-150 hover:bg-slate-50"
                        >
                            <span class="size-1.5 rounded-full" :class="link.dot"></span>
                            <span class="flex-1 text-sm text-slate-600">{{ link.label }}</span>
                            <svg class="size-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>
