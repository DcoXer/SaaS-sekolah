<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import JsonLd from '@/Components/JsonLd.vue';

const props = defineProps({
    setting:        { type: Object,  default: null },
    school:         { type: Object,  default: null },
    stats:          { type: Object,  default: null },
    serverDate:     { type: String,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
});

const page             = usePage();
const flash            = computed(() => page.props.flash ?? {});
const registeredNumber = computed(() => flash.value.registered_number ?? null);

const baseUrl = page.props.ziggy?.url ?? '';
const jsonLd  = computed(() => ({
    '@context':    'https://schema.org',
    '@type':       'WebPage',
    '@id':         `${baseUrl}/ppdb#webpage`,
    'name':        `PPDB — ${props.school?.name ?? ''}`,
    'description': `Pendaftaran Peserta Didik Baru ${props.school?.name ?? ''}. Daftarkan putra-putri Anda sekarang.`,
    'url':         `${baseUrl}/ppdb`,
    'isPartOf':    { '@id': `${baseUrl}/#website` },
    'about':       { '@id': `${baseUrl}/#school` },
    'breadcrumb': {
        '@type': 'BreadcrumbList',
        'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Beranda', 'item': baseUrl },
            { '@type': 'ListItem', 'position': 2, 'name': 'PPDB',    'item': `${baseUrl}/ppdb` },
        ],
    },
}));

const isOpen = computed(() => {
    if (!props.setting?.is_open) return false;
    const now = props.serverDate;
    return now >= props.setting.registration_start && now <= props.setting.registration_end;
});
</script>

<template>
    <Head :title="`PPDB — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="`Pendaftaran Peserta Didik Baru (PPDB) ${school?.name ?? 'sekolah kami'}. Daftarkan putra-putri Anda sekarang.`">
        <meta head-key="og:title" property="og:title" :content="`PPDB — ${school?.name ?? ''}`">
        <meta head-key="og:description" property="og:description" :content="`Penerimaan Peserta Didik Baru ${school?.name ?? ''}. Informasi pendaftaran, syarat, dan jadwal PPDB.`">
        <meta head-key="og:type" property="og:type" content="website">
        <meta v-if="school?.logo" head-key="og:image" property="og:image" :content="school.logo">
        <meta v-if="school?.logo" head-key="twitter:image" name="twitter:image" :content="school.logo">
        <meta head-key="twitter:title" name="twitter:title" :content="`PPDB — ${school?.name ?? ''}`">
        <meta head-key="twitter:description" name="twitter:description" :content="`Pendaftaran Peserta Didik Baru ${school?.name ?? ''}. Daftarkan putra-putri Anda sekarang.`">
    </Head>
    <JsonLd :data="jsonLd" />

    <div class="min-h-screen overflow-x-hidden bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader :school="school" :can-login="canLogin" :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute" active-page="ppdb" />

        <!-- Hero Section -->
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-700 via-amber-600 to-yellow-500 py-20">
            <div class="absolute -right-24 -top-24 size-80 rounded-full bg-white/5"/>
            <div class="absolute -bottom-16 -left-16 size-64 rounded-full bg-white/5"/>
            <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(0deg,transparent,transparent 40px,white 40px,white 41px),repeating-linear-gradient(90deg,transparent,transparent 40px,white 40px,white 41px)"/>

            <div class="relative mx-auto max-w-5xl px-6 text-center">
                <div v-reveal>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-white backdrop-blur-sm">
                        <span class="size-1.5 animate-pulse rounded-full" :class="isOpen ? 'bg-primary-300' : 'bg-red-300'"/>
                        {{ isOpen ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' }}
                    </span>
                    <h1 class="mt-4 text-4xl font-extrabold text-white lg:text-5xl">
                        {{ setting?.title ?? 'Penerimaan Peserta Didik Baru' }}
                    </h1>
                    <p class="mt-3 text-base text-amber-100">{{ school?.name }} membuka pendaftaran siswa baru</p>
                </div>

                <!-- Info Pills -->
                <div v-if="setting" v-reveal="{ delay: 120 }" class="mx-auto mt-8 flex flex-wrap justify-center gap-3">
                    <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm">
                        <svg class="size-4 text-amber-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        {{ new Date(setting.registration_start).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                        –
                        {{ new Date(setting.registration_end).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                    </div>
                    <div v-if="setting.quota" class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm">
                        <svg class="size-4 text-amber-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                        Kuota {{ setting.quota }} siswa
                    </div>
                    <div v-if="stats" class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm">
                        <svg class="size-4 text-primary-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ stats.accepted }} sudah diterima
                    </div>
                </div>

                <!-- CTA -->
                <div v-reveal="{ delay: 200 }" class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <Link v-if="isOpen" :href="route('ppdb.create')"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-sm font-bold text-amber-700 shadow-xl transition-all hover:bg-amber-50 active:scale-95">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Daftar Sekarang
                    </Link>
                    <Link :href="route('ppdb.check')"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-8 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cek Status Pendaftaran
                    </Link>
                </div>
            </div>
        </div>

        <!-- Success Card setelah berhasil mendaftar -->
        <div v-if="registeredNumber" class="border-b border-primary-100 bg-primary-50 px-6 py-8">
            <div class="mx-auto max-w-2xl text-center">
                <!-- Icon check -->
                <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-primary-100 ring-8 ring-primary-50">
                    <svg class="size-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </div>
                <h2 class="text-xl font-extrabold text-primary-800">Pendaftaran Berhasil Dikirim!</h2>
                <p class="mt-1.5 text-sm text-primary-700">Simpan nomor pendaftaran berikut untuk memantau status pendaftaran.</p>

                <!-- Nomor Pendaftaran -->
                <div class="mx-auto mt-5 w-fit rounded-2xl border-2 border-primary-300 bg-white px-8 py-5 shadow-sm">
                    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-slate-400">Nomor Pendaftaran</p>
                    <p class="font-mono text-2xl font-black tracking-widest text-slate-900">{{ registeredNumber }}</p>
                </div>

                <p class="mt-4 text-xs text-primary-600">Screenshot atau catat nomor ini — dibutuhkan untuk cek status pendaftaran.</p>

                <div class="mt-5 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <Link :href="`${route('ppdb.check')}?no=${registeredNumber}`"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-6 py-2.5 text-sm font-bold text-white shadow transition-all hover:bg-primary-500 active:scale-95">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cek Status Pendaftaran
                    </Link>
                    <Link :href="route('ppdb.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-primary-200 bg-white px-6 py-2.5 text-sm font-semibold text-primary-700 transition-colors hover:bg-primary-50">
                        Kembali ke Halaman PPDB
                    </Link>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="mx-auto max-w-5xl space-y-12 px-6 py-14">

            <div v-if="!setting" v-reveal class="rounded-2xl border-2 border-dashed border-slate-200 py-24 text-center">
                <svg class="mx-auto mb-3 size-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                <p class="text-base font-semibold text-slate-500">Informasi PPDB belum tersedia</p>
                <p class="mt-1 text-sm text-slate-400">Silakan hubungi sekolah untuk informasi lebih lanjut.</p>
            </div>

            <template v-else>
                <!-- Deskripsi -->
                <div v-if="setting.description" v-reveal>
                    <div class="mb-4 flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-amber-500"/>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Informasi PPDB</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-8">
                        <p class="whitespace-pre-line text-base leading-relaxed text-slate-700">{{ setting.description }}</p>
                    </div>
                </div>

                <!-- Timeline -->
                <div v-reveal>
                    <div class="mb-6 flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-amber-500"/>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Jadwal</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-100">
                                <svg class="size-5 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Pendaftaran</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ new Date(setting.registration_start).toLocaleDateString('id-ID', { day:'numeric', month:'long' }) }}
                                    – {{ new Date(setting.registration_end).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                                </p>
                            </div>
                        </div>
                        <div v-if="setting.announcement_date" class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                                <svg class="size-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Pengumuman</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ new Date(setting.announcement_date).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100">
                                <svg class="size-5 text-sky-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Kuota</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ setting.quota }} siswa</p>
                                <p v-if="stats" class="text-xs text-slate-400">{{ stats.accepted }} sudah diterima</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alur Pendaftaran -->
                <div v-reveal>
                    <div class="mb-6 flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-amber-500"/>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Alur Pendaftaran</p>
                    </div>

                    <!-- Desktop: horizontal stepper — Mobile: vertical list -->
                    <div class="hidden sm:block">
                        <div class="relative">
                            <!-- Garis penghubung -->
                            <div class="absolute left-0 right-0 top-8 h-0.5 bg-gradient-to-r from-amber-200 via-amber-400 to-primary-400" style="margin: 0 5%"/>

                            <div class="relative grid grid-cols-6 gap-2">
                                <div v-for="(step, i) in [
                                    { no:1, color:'amber',  bg:'bg-amber-500',   ring:'ring-amber-100',   icon:'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125',      title:'Isi Formulir',         desc:'Lengkapi data calon siswa secara online melalui formulir pendaftaran.' },
                                    { no:2, color:'amber',  bg:'bg-amber-500',   ring:'ring-amber-100',   icon:'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',                                                         title:'Upload Berkas',        desc:'Unggah foto, fotokopi KK, dan akta kelahiran dalam satu langkah.' },
                                    { no:3, color:'sky',    bg:'bg-sky-500',     ring:'ring-sky-100',     icon:'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',                                                                                    title:'Pantau Status',        desc:'Gunakan nomor pendaftaran untuk memantau status verifikasi berkas.' },
                                    { no:4, color:'violet', bg:'bg-violet-500',  ring:'ring-violet-100',  icon:'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                   title:'Pengumuman',           desc:'Sekolah mengumumkan hasil seleksi sesuai tanggal yang telah ditetapkan.' },
                                    { no:5, color:'emerald',bg:'bg-primary-500', ring:'ring-primary-100', icon:'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z', title:'Bayar Uang Pangkal (DP)', desc:'Siswa yang diterima wajib membayar DP ke sekolah sebagai konfirmasi pendaftaran.' },
                                    { no:6, color:'emerald',bg:'bg-primary-600', ring:'ring-primary-100', icon:'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',                title:'Resmi Terdaftar',      desc:'Selamat! Siswa resmi terdaftar dan akan masuk kelas 1 di tahun ajaran baru.' },
                                ]" :key="i" class="flex flex-col items-center text-center">
                                    <!-- Bubble -->
                                    <div class="relative z-10 mb-4 flex size-16 items-center justify-center rounded-full ring-4"
                                        :class="[step.bg, step.ring]">
                                        <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon"/>
                                        </svg>
                                        <span class="absolute -right-1 -top-1 flex size-5 items-center justify-center rounded-full bg-white text-[10px] font-black shadow"
                                            :class="`text-${step.color}-600`">{{ step.no }}</span>
                                    </div>
                                    <p class="text-xs font-bold leading-snug text-slate-800">{{ step.title }}</p>
                                    <p class="mt-1 text-[11px] leading-relaxed text-slate-400">{{ step.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile: vertical -->
                    <div class="sm:hidden space-y-0">
                        <div v-for="(step, i) in [
                            { no:1, color:'amber',   bg:'bg-amber-500',   border:'border-amber-200',  textColor:'text-amber-700',  badgeBg:'bg-amber-50',   icon:'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125',      title:'Isi Formulir Online',      desc:'Lengkapi data calon siswa melalui formulir pendaftaran online.' },
                            { no:2, color:'amber',   bg:'bg-amber-500',   border:'border-amber-200',  textColor:'text-amber-700',  badgeBg:'bg-amber-50',   icon:'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',                                                         title:'Upload Berkas Pendukung',  desc:'Unggah foto siswa, fotokopi KK, dan akta kelahiran.' },
                            { no:3, color:'sky',     bg:'bg-sky-500',     border:'border-sky-200',    textColor:'text-sky-700',    badgeBg:'bg-sky-50',     icon:'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',                                                                                    title:'Pantau Status Pendaftaran', desc:'Gunakan nomor pendaftaran untuk cek status verifikasi berkas.' },
                            { no:4, color:'violet',  bg:'bg-violet-500',  border:'border-violet-200', textColor:'text-violet-700', badgeBg:'bg-violet-50',  icon:'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                   title:'Pengumuman Hasil Seleksi',  desc:'Hasil seleksi diumumkan sesuai tanggal yang telah ditetapkan.' },
                            { no:5, color:'emerald', bg:'bg-primary-500', border:'border-primary-200',textColor:'text-primary-700',badgeBg:'bg-primary-50', icon:'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z', title:'Bayar Uang Pangkal (DP)',   desc:'Siswa yang diterima wajib membayar DP ke sekolah sebagai konfirmasi pendaftaran.' },
                            { no:6, color:'emerald', bg:'bg-primary-600', border:'border-primary-300',textColor:'text-primary-800',badgeBg:'bg-primary-50', icon:'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5', title:'Resmi Terdaftar',           desc:'Siswa resmi terdaftar dan akan masuk kelas 1 di tahun ajaran baru.' },
                        ]" :key="i" class="flex gap-4">
                            <!-- Garis kiri -->
                            <div class="flex flex-col items-center">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-full text-white" :class="step.bg">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon"/>
                                    </svg>
                                </div>
                                <div v-if="i < 5" class="mt-1 w-0.5 flex-1 bg-slate-200" style="min-height:2rem"/>
                            </div>
                            <!-- Konten -->
                            <div class="pb-6 pt-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold" :class="[step.badgeBg, step.textColor]">
                                        Langkah {{ step.no }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm font-bold text-slate-800">{{ step.title }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ step.desc }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan DP -->
                    <div class="mt-6 flex items-start gap-3 rounded-2xl border border-primary-200 bg-primary-50 p-5">
                        <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-100">
                            <svg class="size-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-primary-800">Tentang Pembayaran Uang Pangkal (DP)</p>
                            <p class="mt-1 text-xs leading-relaxed text-primary-700">
                                Setelah dinyatakan diterima, orang tua/wali wajib melakukan pembayaran uang pangkal (DP) sebagai konfirmasi bahwa siswa akan benar-benar mendaftar.
                                Pembayaran dilakukan langsung ke bagian keuangan sekolah atau melalui sistem pembayaran online yang akan diberitahukan saat pengumuman.
                                <strong>Siswa yang tidak membayar DP dalam batas waktu yang ditentukan dianggap mengundurkan diri.</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Persyaratan -->
                <div v-if="setting.requirements" v-reveal>
                    <div class="mb-4 flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-amber-500"/>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Persyaratan</p>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-8 shadow-sm">
                        <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-amber-500 to-yellow-400"/>
                        <p class="whitespace-pre-line pl-2 text-sm leading-relaxed text-slate-700">{{ setting.requirements }}</p>
                    </div>
                </div>

                <!-- Pendaftaran ditutup -->
                <div v-if="!isOpen" v-reveal class="rounded-2xl bg-slate-50 p-8 text-center">
                    <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <p class="font-semibold text-slate-600">Pendaftaran belum/sudah dibuka</p>
                    <p class="mt-1 text-sm text-slate-400">Pantau terus informasi PPDB di halaman ini atau hubungi sekolah.</p>
                    <Link :href="route('ppdb.check')"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cek Status Pendaftaran
                    </Link>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-slate-50 py-6">
            <p class="text-center text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name }}</p>
        </footer>

    </div>
</template>
