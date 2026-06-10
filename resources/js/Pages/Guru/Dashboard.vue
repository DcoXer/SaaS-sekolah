<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeYear:        { type: String, default: null },
    stats:             { type: Object, required: true },
    assignments:       { type: Array,  default: () => [] },
    attendanceSummary: { type: Object, default: () => ({ hadir: 0, izin: 0, sakit: 0, alpha: 0, total: 0 }) },
    latestPosts:       { type: Array,  default: () => [] },
    recentAttendance:  { type: Array,  default: () => [] },
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

const classroomAccents = [
    { bg: 'bg-violet-500', light: 'bg-violet-50', text: 'text-violet-700', ring: 'ring-violet-200', badge: 'bg-violet-100 text-violet-700' },
    { bg: 'bg-sky-500',    light: 'bg-sky-50',    text: 'text-sky-700',    ring: 'ring-sky-200',    badge: 'bg-sky-100 text-sky-700' },
    { bg: 'bg-primary-500',light: 'bg-primary-50',text: 'text-primary-700',ring: 'ring-primary-200',badge: 'bg-primary-100 text-primary-700' },
    { bg: 'bg-amber-500',  light: 'bg-amber-50',  text: 'text-amber-700',  ring: 'ring-amber-200',  badge: 'bg-amber-100 text-amber-700' },
    { bg: 'bg-pink-500',   light: 'bg-pink-50',   text: 'text-pink-700',   ring: 'ring-pink-200',   badge: 'bg-pink-100 text-pink-700' },
    { bg: 'bg-cyan-500',   light: 'bg-cyan-50',   text: 'text-cyan-700',   ring: 'ring-cyan-200',   badge: 'bg-cyan-100 text-cyan-700' },
];

const attendanceConfig = {
    hadir: { label: 'Hadir',  color: 'text-primary-700', bg: 'bg-primary-500', light: 'bg-primary-50',  dot: 'bg-primary-500' },
    izin:  { label: 'Izin',   color: 'text-sky-700',     bg: 'bg-sky-500',     light: 'bg-sky-50',      dot: 'bg-sky-400' },
    sakit: { label: 'Sakit',  color: 'text-amber-700',   bg: 'bg-amber-500',   light: 'bg-amber-50',    dot: 'bg-amber-400' },
    alpha: { label: 'Alpha',  color: 'text-red-700',     bg: 'bg-red-500',     light: 'bg-red-50',      dot: 'bg-red-500' },
};

const hadirPct = computed(() => {
    const t = props.attendanceSummary.total;
    return t > 0 ? Math.round((props.attendanceSummary.hadir / t) * 100) : 0;
});

const categoryLabel = { berita: 'Berita', pengumuman: 'Pengumuman' };
const categoryBadge = {
    berita:     'bg-sky-100 text-sky-700',
    pengumuman: 'bg-amber-100 text-amber-700',
};

function shortDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
</script>

<template>
    <Head title="Dashboard Guru" />

    <AppLayout>
        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-5 pb-8">

            <!-- ── Welcome banner ─────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 p-6 shadow-lg shadow-orange-200">
                <!-- Decorative blobs -->
                <div class="pointer-events-none absolute -right-8 -top-8 size-40 rounded-full bg-white/10 blur-2xl" />
                <div class="pointer-events-none absolute -bottom-6 left-1/2 size-32 rounded-full bg-white/10 blur-xl" />

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
                        <p class="text-sm font-medium text-orange-100">{{ greeting }} 👋</p>
                        <h2 class="truncate text-xl font-extrabold text-white leading-tight">{{ user.name }}</h2>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white backdrop-blur-sm">
                                <span class="size-1.5 rounded-full bg-primary-300"></span>
                                Guru
                            </span>
                            <span v-if="activeYear" class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-orange-50 backdrop-blur-sm">
                                {{ activeYear }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="relative mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white/15 p-3.5 backdrop-blur-sm">
                        <p class="tabular-nums text-2xl font-extrabold text-white">{{ stats.classrooms }}</p>
                        <p class="mt-0.5 text-xs font-medium text-orange-100">Kelas Diajar</p>
                    </div>
                    <div class="rounded-2xl bg-white/15 p-3.5 backdrop-blur-sm">
                        <p class="tabular-nums text-2xl font-extrabold text-white">{{ stats.subjects }}</p>
                        <p class="mt-0.5 text-xs font-medium text-orange-100">Mata Pelajaran</p>
                    </div>
                </div>
            </div>

            <!-- ── Quick actions ───────────────────────────────────────────── -->
            <div class="grid grid-cols-3 gap-3">
                <Link href="/guru/assessments"
                    class="group flex flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-amber-50 ring-4 ring-amber-100 transition-colors group-hover:bg-amber-100">
                        <svg class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <span class="text-center text-xs font-semibold text-slate-700 leading-tight">Input Nilai</span>
                </Link>
                <Link href="/guru/report-cards"
                    class="group flex flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-sky-50 ring-4 ring-sky-100 transition-colors group-hover:bg-sky-100">
                        <svg class="size-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                    <span class="text-center text-xs font-semibold text-slate-700 leading-tight">Raport</span>
                </Link>
                <Link href="/guru/attendance"
                    class="group flex flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-primary-50 ring-4 ring-primary-100 transition-colors group-hover:bg-primary-100">
                        <svg class="size-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-center text-xs font-semibold text-slate-700 leading-tight">Absensi</span>
                </Link>
            </div>

            <!-- ── Absensi bulan ini ───────────────────────────────────────── -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-50 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Absensi Bulan Ini</h3>
                        <p class="text-xs text-slate-400">{{ new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) }}</p>
                    </div>
                    <div v-if="attendanceSummary.total > 0" class="flex items-center gap-1.5">
                        <span class="text-lg font-extrabold" :class="hadirPct >= 80 ? 'text-primary-600' : hadirPct >= 60 ? 'text-amber-600' : 'text-red-600'">
                            {{ hadirPct }}%
                        </span>
                        <span class="text-xs text-slate-400">hadir</span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-0 sm:divide-x sm:divide-slate-50">
                    <div v-for="(cfg, key) in attendanceConfig" :key="key" class="flex flex-col items-center py-4 gap-1">
                        <span class="tabular-nums text-2xl font-extrabold" :class="cfg.color">
                            {{ attendanceSummary[key] ?? 0 }}
                        </span>
                        <span class="text-[11px] font-semibold text-slate-400">{{ cfg.label }}</span>
                    </div>
                </div>

                <!-- Progress bar -->
                <div v-if="attendanceSummary.total > 0" class="flex gap-px px-5 pb-4 pt-1">
                    <div v-if="attendanceSummary.hadir" class="h-1.5 rounded-l-full bg-primary-500 transition-all"
                        :style="{ width: ((attendanceSummary.hadir / attendanceSummary.total) * 100) + '%' }" />
                    <div v-if="attendanceSummary.izin" class="h-1.5 bg-sky-400 transition-all"
                        :style="{ width: ((attendanceSummary.izin / attendanceSummary.total) * 100) + '%' }" />
                    <div v-if="attendanceSummary.sakit" class="h-1.5 bg-amber-400 transition-all"
                        :style="{ width: ((attendanceSummary.sakit / attendanceSummary.total) * 100) + '%' }" />
                    <div v-if="attendanceSummary.alpha" class="h-1.5 rounded-r-full bg-red-500 transition-all"
                        :style="{ width: ((attendanceSummary.alpha / attendanceSummary.total) * 100) + '%' }" />
                    <div v-if="hadirPct === 0" class="h-1.5 w-full rounded-full bg-slate-100" />
                </div>

                <!-- Timeline 7 hari terakhir -->
                <div class="border-t border-slate-50 px-5 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">7 Hari Terakhir</p>
                    <p v-if="recentAttendance.length === 0" class="text-xs text-slate-400">Belum ada data absensi.</p>
                    <div v-else class="flex items-end gap-1.5">
                        <div
                            v-for="rec in [...recentAttendance].reverse()"
                            :key="rec.date"
                            class="group flex flex-1 flex-col items-center gap-1.5"
                        >
                            <div
                                class="relative w-full rounded-lg transition-all"
                                :class="[attendanceConfig[rec.status]?.light ?? 'bg-slate-100',
                                         rec.status === 'hadir' ? 'h-10' : rec.status === 'alpha' ? 'h-8' : 'h-7']"
                            >
                                <div class="absolute inset-x-0 bottom-0 rounded-lg"
                                    :class="[attendanceConfig[rec.status]?.bg ?? 'bg-slate-300', 'h-1.5']" />
                            </div>
                            <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap">
                                {{ shortDate(rec.date) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Penugasan ───────────────────────────────────────────────── -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-50 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Penugasan Tahun Ini</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500">
                        {{ Object.keys(byClassroom).length }} kelas
                    </span>
                </div>

                <div v-if="assignments.length === 0" class="px-5 py-10 text-center">
                    <div class="mx-auto mb-3 flex size-11 items-center justify-center rounded-2xl bg-slate-100">
                        <svg class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">Belum ada penugasan</p>
                    <p class="mt-1 text-xs text-slate-400">Hubungi operator untuk mengatur penugasan.</p>
                </div>

                <div v-else class="divide-y divide-slate-50">
                    <div
                        v-for="(subjects, classroom, i) in byClassroom"
                        :key="classroom"
                        class="flex items-start gap-3.5 px-5 py-4"
                    >
                        <!-- Color strip + number -->
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold ring-4 shadow-sm"
                            :class="[classroomAccents[i % classroomAccents.length].light,
                                     classroomAccents[i % classroomAccents.length].text,
                                     classroomAccents[i % classroomAccents.length].ring]">
                            {{ classroom.replace(/[^0-9]/g, '') || classroom[0] }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-800">{{ classroom }}</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="s in subjects" :key="s"
                                    class="rounded-lg px-2.5 py-1 text-[11px] font-semibold"
                                    :class="classroomAccents[i % classroomAccents.length].badge"
                                >
                                    {{ s }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Berita & Pengumuman ─────────────────────────────────────── -->
            <div v-if="latestPosts.length > 0" class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-50 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Berita & Pengumuman</h3>
                    <Link href="/berita" class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors">
                        Lihat semua →
                    </Link>
                </div>
                <div class="divide-y divide-slate-50">
                    <Link
                        v-for="post in latestPosts" :key="post.id"
                        :href="`/berita/${post.slug}`"
                        class="group flex items-start gap-3.5 px-5 py-4 transition-colors hover:bg-slate-50/70"
                    >
                        <!-- Icon accent -->
                        <div class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl"
                            :class="post.category === 'pengumuman' ? 'bg-amber-50' : 'bg-sky-50'">
                            <svg v-if="post.category === 'pengumuman'" class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <svg v-else class="size-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                    :class="categoryBadge[post.category] ?? 'bg-slate-100 text-slate-600'">
                                    {{ categoryLabel[post.category] ?? post.category }}
                                </span>
                                <span class="text-[11px] text-slate-400">{{ post.published_at }}</span>
                            </div>
                            <p class="text-sm font-semibold text-slate-800 leading-snug group-hover:text-amber-700 transition-colors">
                                {{ post.title }}
                            </p>
                            <p v-if="post.excerpt" class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-slate-500">
                                {{ post.excerpt }}
                            </p>
                        </div>
                        <svg class="mt-1 size-4 shrink-0 text-slate-300 transition-transform group-hover:translate-x-0.5 group-hover:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
