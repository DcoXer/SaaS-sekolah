<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeYear:       { type: String, default: null },
    student:          { type: Object, default: null },
    stats:            { type: Object, required: true },
    unpaidInvoices:   { type: Array,  default: () => [] },
    latestPosts:      { type: Array,  default: () => [] },
    reportCardStatus: { type: Object, default: null },
    classroom:        { type: String, default: null },
});

const user     = computed(() => usePage().props.auth.user);
const hour     = new Date().getHours();
const greeting = hour < 12 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 19 ? 'Selamat sore' : 'Selamat malam';
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

const formatRupiah = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

const totalUnpaid = computed(() =>
    props.unpaidInvoices.reduce((sum, inv) => sum + inv.amount, 0)
);

const invoiceStatusConfig = {
    unpaid:  { label: 'Belum Bayar',    badge: 'bg-red-100 text-red-700',     dot: 'bg-red-500' },
    partial: { label: 'Bayar Sebagian', badge: 'bg-amber-100 text-amber-700', dot: 'bg-amber-500' },
};

const reportCardBadge = {
    draft:     { label: 'Belum Terbit', badge: 'bg-slate-100 text-slate-500',     icon: 'clock' },
    published: { label: 'Sudah Terbit', badge: 'bg-primary-100 text-primary-700', icon: 'check' },
};

const categoryLabel = { berita: 'Berita', pengumuman: 'Pengumuman' };
const categoryBadge = {
    berita:     'bg-sky-100 text-sky-700',
    pengumuman: 'bg-amber-100 text-amber-700',
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #title>
            <h1 class="text-sm font-semibold text-slate-700">Dashboard</h1>
        </template>

        <div class="space-y-5 pb-8">

            <!-- ── Welcome banner ─────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-500 via-teal-500 to-cyan-600 p-6 shadow-lg shadow-primary-200">
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
                        <p class="text-sm font-medium text-teal-100">{{ greeting }} 👋</p>
                        <h2 class="truncate text-xl font-extrabold text-white leading-tight">
                            {{ student?.name ?? user.name }}
                        </h2>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white backdrop-blur-sm">
                                <span class="size-1.5 rounded-full bg-primary-300"></span>
                                Wali Murid
                            </span>
                            <span v-if="activeYear" class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-teal-50 backdrop-blur-sm">
                                {{ activeYear }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Student info strip -->
                <div v-if="student" class="relative mt-5 grid grid-cols-3 gap-2">
                    <div class="rounded-2xl bg-white/15 px-3 py-2.5 backdrop-blur-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-100">NIS</p>
                        <p class="mt-0.5 truncate text-sm font-bold text-white">{{ student.nis ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/15 px-3 py-2.5 backdrop-blur-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-100">NISN</p>
                        <p class="mt-0.5 truncate text-sm font-bold text-white">{{ student.nisn ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/15 px-3 py-2.5 backdrop-blur-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-100">Kelas</p>
                        <p class="mt-0.5 truncate text-sm font-bold text-white">{{ classroom ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Akun belum terhubung ────────────────────────────────────── -->
            <div v-if="!student"
                class="flex items-start gap-3.5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                    <svg class="size-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-800">Akun belum terhubung ke data siswa</p>
                    <p class="mt-0.5 text-xs text-amber-600">Hubungi operator sekolah untuk menghubungkan akun Anda.</p>
                </div>
            </div>

            <template v-else>

                <!-- ── Quick actions ─────────────────────────────────────────── -->
                <div class="grid grid-cols-3 gap-3">
                    <Link href="/siswa/report-cards"
                        class="group flex flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex size-11 items-center justify-center rounded-2xl bg-primary-50 ring-4 ring-primary-100 transition-colors group-hover:bg-primary-100">
                            <svg class="size-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                            </svg>
                        </div>
                        <span class="text-center text-xs font-semibold text-slate-700 leading-tight">Nilai & Raport</span>
                    </Link>
                    <Link href="/siswa/invoices"
                        class="group flex flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                        <div class="relative flex size-11 items-center justify-center rounded-2xl bg-rose-50 ring-4 ring-rose-100 transition-colors group-hover:bg-rose-100">
                            <svg class="size-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                            <span v-if="unpaidInvoices.length > 0"
                                class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-extrabold text-white shadow">
                                {{ unpaidInvoices.length }}
                            </span>
                        </div>
                        <span class="text-center text-xs font-semibold text-slate-700 leading-tight">Tagihan</span>
                    </Link>
                    <Link href="/siswa/letters"
                        class="group flex flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex size-11 items-center justify-center rounded-2xl bg-sky-50 ring-4 ring-sky-100 transition-colors group-hover:bg-sky-100">
                            <svg class="size-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <span class="text-center text-xs font-semibold text-slate-700 leading-tight">Surat</span>
                    </Link>
                </div>

                <!-- ── Raport status card ──────────────────────────────────── -->
                <div v-if="reportCardStatus"
                    class="flex items-center gap-4 rounded-2xl border bg-white px-5 py-4 shadow-sm"
                    :class="reportCardStatus.status === 'published' ? 'border-primary-200' : 'border-slate-100'">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl"
                        :class="reportCardStatus.status === 'published' ? 'bg-primary-50 ring-4 ring-primary-100' : 'bg-slate-50 ring-4 ring-slate-100'">
                        <svg v-if="reportCardStatus.status === 'published'" class="size-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <svg v-else class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-slate-400">Raport Semester {{ reportCardStatus.semester }}</p>
                        <p class="text-sm font-bold"
                            :class="reportCardStatus.status === 'published' ? 'text-primary-700' : 'text-slate-600'">
                            {{ reportCardBadge[reportCardStatus.status]?.label ?? reportCardStatus.status }}
                        </p>
                    </div>
                    <Link v-if="reportCardStatus.status === 'published'"
                        href="/siswa/report-cards"
                        class="shrink-0 rounded-xl bg-primary-500 px-4 py-2 text-xs font-bold text-white shadow-sm shadow-primary-100 transition hover:bg-primary-600">
                        Lihat Raport
                    </Link>
                </div>

                <!-- ── Tagihan banner ─────────────────────────────────────── -->
                <div v-if="unpaidInvoices.length > 0"
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-500 via-rose-500 to-pink-600 p-5 shadow-lg shadow-rose-200">
                    <div class="pointer-events-none absolute -right-6 -top-6 size-32 rounded-full bg-white/10 blur-xl" />
                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex size-2 animate-pulse rounded-full bg-red-200"></span>
                                <p class="text-xs font-bold uppercase tracking-widest text-red-100">Tagihan Belum Lunas</p>
                            </div>
                            <p class="tabular-nums mt-2 text-3xl font-extrabold text-white">{{ formatRupiah(totalUnpaid) }}</p>
                            <p class="mt-1 text-xs text-red-200">{{ unpaidInvoices.length }} tagihan · segera selesaikan</p>
                        </div>
                        <Link href="/siswa/invoices"
                            class="shrink-0 rounded-2xl bg-white px-4 py-2.5 text-xs font-extrabold text-red-600 shadow-sm transition hover:bg-red-50 active:scale-95">
                            Bayar →
                        </Link>
                    </div>

                    <!-- Invoice list inline -->
                    <div class="relative mt-4 space-y-2">
                        <div v-for="inv in unpaidInvoices.slice(0, 3)" :key="inv.id"
                            class="flex items-center gap-3 rounded-2xl bg-white/15 px-3.5 py-2.5 backdrop-blur-sm">
                            <span class="size-2 shrink-0 rounded-full"
                                :class="inv.status === 'partial' ? 'bg-amber-300' : 'bg-red-300'" />
                            <span class="min-w-0 flex-1 truncate text-xs font-semibold text-white">{{ inv.payment_type }}</span>
                            <span class="shrink-0 text-xs font-bold text-white/80 tabular-nums">{{ formatRupiah(inv.amount) }}</span>
                        </div>
                        <p v-if="unpaidInvoices.length > 3"
                            class="text-center text-xs text-red-200">
                            +{{ unpaidInvoices.length - 3 }} tagihan lainnya
                        </p>
                    </div>
                </div>

                <!-- ── Semua lunas ─────────────────────────────────────────── -->
                <div v-else
                    class="flex items-center gap-3.5 rounded-2xl border border-primary-200 bg-primary-50 px-5 py-4 shadow-sm">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-primary-100">
                        <svg class="size-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-primary-800">Semua tagihan sudah lunas</p>
                        <p class="text-xs text-primary-600">Tidak ada tagihan yang perlu dibayarkan saat ini.</p>
                    </div>
                </div>

                <!-- ── Berita & Pengumuman ─────────────────────────────────── -->
                <div v-if="latestPosts.length > 0" class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-50 px-5 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Berita & Pengumuman</h3>
                        <Link href="/berita" class="text-xs font-semibold text-teal-600 hover:text-teal-700 transition-colors">
                            Lihat semua →
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <Link
                            v-for="post in latestPosts" :key="post.id"
                            :href="`/berita/${post.slug}`"
                            class="group flex items-start gap-3.5 px-5 py-4 transition-colors hover:bg-slate-50/70"
                        >
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
                                <p class="text-sm font-semibold text-slate-800 leading-snug group-hover:text-teal-700 transition-colors">
                                    {{ post.title }}
                                </p>
                                <p v-if="post.excerpt" class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-slate-500">
                                    {{ post.excerpt }}
                                </p>
                            </div>
                            <svg class="mt-1 size-4 shrink-0 text-slate-300 transition-transform group-hover:translate-x-0.5 group-hover:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>
                </div>

            </template>

        </div>
    </AppLayout>
</template>
