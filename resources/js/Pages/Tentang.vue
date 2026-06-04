<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import JsonLd from '@/Components/JsonLd.vue';

const props = defineProps({
    school:         { type: Object,  default: null },
    stats:          { type: Object,  default: () => ({}) },
    structure:      { type: Object,  default: () => ({}) },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
    ppdbActive:     { type: Boolean, default: false },
    heroPhotos:     { type: Array,   default: () => [] },
});

const heroPhotosList = computed(() => props.heroPhotos ?? []);
const heroBgIndex    = ref(0);
const heroBgUrl      = computed(() => heroPhotosList.value[heroBgIndex.value] ?? null);

let heroBgTimer = null;
onMounted(() => {
    if (heroPhotosList.value.length > 1) {
        heroBgTimer = setInterval(() => {
            heroBgIndex.value = (heroBgIndex.value + 1) % heroPhotosList.value.length;
        }, 5000);
    }
});
onUnmounted(() => clearInterval(heroBgTimer));

const initials = (name) => name?.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase() ?? '?';

const missionLines = computed(() =>
    (props.school?.mission ?? '').split('\n').filter(l => l.trim())
);

const baseUrl = usePage().props.ziggy?.url ?? '';
const jsonLd = computed(() => ({
    '@context':  'https://schema.org',
    '@type':     'AboutPage',
    '@id':       `${baseUrl}/tentang#webpage`,
    'name':      `Tentang Kami — ${props.school?.name ?? ''}`,
    'description': props.school?.description || `Profil, visi, misi, dan sejarah ${props.school?.name ?? ''}`,
    'url':       `${baseUrl}/tentang`,
    'isPartOf':  { '@id': `${baseUrl}/#website` },
    'about':     { '@id': `${baseUrl}/#school` },
    'breadcrumb': {
        '@type': 'BreadcrumbList',
        'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Beranda',     'item': baseUrl },
            { '@type': 'ListItem', 'position': 2, 'name': 'Tentang Kami','item': `${baseUrl}/tentang` },
        ],
    },
}));
</script>

<template>
    <Head :title="`Tentang Kami — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="`Profil, visi, misi, dan sejarah ${school?.name ?? 'sekolah kami'}.`">
        <meta head-key="og:title" property="og:title" :content="`Tentang Kami — ${school?.name ?? ''}`">
        <meta head-key="og:description" property="og:description" :content="school?.description ?? ''">
        <meta head-key="og:type" property="og:type" content="website">
        <meta v-if="school?.logo" head-key="og:image" property="og:image" :content="school.logo">
        <meta v-if="school?.logo" head-key="twitter:image" name="twitter:image" :content="school.logo">
        <meta head-key="twitter:title" name="twitter:title" :content="`Tentang Kami — ${school?.name ?? ''}`">
        <meta head-key="twitter:description" name="twitter:description" :content="school?.description ?? ''">
    </Head>
    <JsonLd :data="jsonLd" />

    <div class="min-h-screen bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader
            :school="school"
            :can-login="canLogin"
            :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute"
            :ppdb-active="ppdbActive"
            active-page="tentang"
        />

        <!-- ── Hero ──────────────────────────────────────────────────────── -->
        <div
            class="relative overflow-hidden transition-all duration-1000"
            :class="heroBgUrl ? '' : 'bg-gradient-to-br from-green-900 via-green-800 to-green-700'"
            :style="heroBgUrl ? `background-image:url('${heroBgUrl}');background-size:cover;background-position:center` : ''"
        >
            <!-- Overlay gelap saat ada foto -->
            <div v-if="heroBgUrl" class="absolute inset-0 bg-black/55"/>
            <!-- Dekorasi lingkaran (hanya saat tidak ada foto) -->
            <div v-if="!heroBgUrl" class="absolute -right-32 -top-32 size-96 rounded-full bg-white/5"/>
            <div v-if="!heroBgUrl" class="absolute -bottom-20 -left-20 size-72 rounded-full bg-white/5"/>

            <div class="relative mx-auto max-w-6xl px-6 py-20">
                <div class="flex flex-col items-center gap-6 text-center lg:flex-row lg:items-center lg:text-left lg:gap-12">

                    <!-- Logo besar -->
                    <div class="shrink-0">
                        <img v-if="school?.logo" :src="school.logo" alt="Logo"
                            class="size-28 rounded-3xl object-contain ring-4 ring-white/20 shadow-2xl lg:size-36"/>
                        <div v-else class="flex size-28 items-center justify-center rounded-3xl bg-white/10 ring-4 ring-white/20 backdrop-blur-sm lg:size-36">
                            <svg class="size-14 text-white/60" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Teks -->
                    <div>
                        <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-400/10 px-4 py-1.5">
                            <span class="size-1.5 rounded-full bg-amber-400"/>
                            <span class="text-xs font-semibold tracking-wide text-amber-300">Profil Sekolah</span>
                        </div>
                        <h1 class="text-3xl font-extrabold text-white lg:text-5xl">
                            {{ school?.name ?? 'Nama Sekolah' }}
                        </h1>
                        <p v-if="school?.tagline" class="mt-3 text-base font-medium italic text-white/70 lg:text-lg">
                            "{{ school.tagline }}"
                        </p>
                        <p v-if="school?.npsn" class="mt-2 text-sm text-green-300">NPSN: {{ school.npsn }}</p>
                    </div>
                </div>

                <!-- Stats bar -->
                <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div v-for="(s, i) in [
                        { value: stats.students  || '–', label: 'Siswa Aktif',        icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
                        { value: stats.teachers  || '–', label: 'Tenaga Pendidik',    icon: 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5' },
                        { value: stats.extracurriculars || '–', label: 'Ekstrakulikuler', icon: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z' },
                        { value: stats.since     || '–', label: 'Tahun Berdiri',     icon: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5' },
                    ]" :key="s.label"
                        v-reveal="{ delay: i * 80 }"
                        class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-4 backdrop-blur-sm"
                    >
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="s.icon"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xl font-extrabold text-white leading-none">{{ s.value }}</p>
                            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-white/50">{{ s.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Konten ─────────────────────────────────────────────────────── -->
        <div class="mx-auto max-w-6xl space-y-16 px-6 py-16">

            <!-- Deskripsi Singkat -->
            <div v-if="school?.description" v-reveal>
                <div class="mb-6 flex items-center gap-4">
                    <div class="h-1 w-10 rounded-full bg-green-600"/>
                    <p class="text-xs font-bold uppercase tracking-widest text-green-600">Tentang Kami</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-8 shadow-sm">
                    <p class="text-base leading-relaxed text-slate-700 whitespace-pre-line">{{ school.description }}</p>
                </div>
            </div>

            <!-- Kepala Sekolah -->
            <div v-if="school?.principal_name" v-reveal>
                <div class="mb-6 flex items-center gap-4">
                    <div class="h-1 w-10 rounded-full bg-green-600"/>
                    <p class="text-xs font-bold uppercase tracking-widest text-green-600">Pimpinan</p>
                </div>
                <div class="flex flex-col items-center gap-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:flex-row sm:items-center">
                    <!-- Avatar -->
                    <div class="flex size-24 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-green-700 to-green-600 shadow-md">
                        <svg class="size-12 text-white/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-green-600">Kepala Madrasah</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-slate-900">{{ school.principal_name }}</h3>
                        <p v-if="school.principal_nip" class="mt-1 text-sm text-slate-500">NIP. {{ school.principal_nip }}</p>
                        <p v-if="school?.address" class="mt-2 flex items-center gap-1.5 text-sm text-slate-500">
                            <svg class="size-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            {{ school.address }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sejarah -->
            <div v-if="school?.history" v-reveal>
                <div class="mb-6 flex items-center gap-4">
                    <div class="h-1 w-10 rounded-full bg-green-600"/>
                    <p class="text-xs font-bold uppercase tracking-widest text-green-600">Sejarah</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <!-- Accent kiri -->
                    <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-green-600 to-green-300"/>
                    <div class="p-8 pl-10">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-green-700 shadow">
                                <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900">Sejarah Singkat</h3>
                        </div>
                        <p class="text-sm leading-relaxed text-slate-600 whitespace-pre-line">{{ school.history }}</p>
                    </div>
                </div>
            </div>

            <!-- Visi & Misi -->
            <div v-if="school?.vision || school?.mission">
                <div v-reveal class="mb-6 flex items-center gap-4">
                    <div class="h-1 w-10 rounded-full bg-green-600"/>
                    <p class="text-xs font-bold uppercase tracking-widest text-green-600">Visi & Misi</p>
                </div>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    <!-- Visi -->
                    <div v-if="school?.vision" v-reveal="{ from: 'left' }" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-800 to-green-600 p-8 shadow-md">
                        <div class="absolute -right-8 -top-8 size-40 rounded-full bg-white/5"/>
                        <div class="absolute -bottom-10 -left-6 size-32 rounded-full bg-white/5"/>
                        <div class="relative">
                            <div class="mb-5 inline-flex size-12 items-center justify-center rounded-2xl bg-white/20">
                                <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="mb-4 text-2xl font-extrabold text-white">Visi</h3>
                            <p class="text-sm leading-relaxed text-white/90">{{ school.vision }}</p>
                        </div>
                    </div>

                    <!-- Misi -->
                    <div v-if="school?.mission" v-reveal="{ from: 'right', delay: 100 }" class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                        <div class="mb-5 inline-flex size-12 items-center justify-center rounded-2xl bg-amber-100">
                            <svg class="size-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                            </svg>
                        </div>
                        <h3 class="mb-5 text-2xl font-extrabold text-slate-900">Misi</h3>
                        <ul class="space-y-3">
                            <li v-for="(line, i) in missionLines" :key="i" class="flex items-start gap-3 text-sm text-slate-700 leading-relaxed">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-[11px] font-extrabold text-green-700">{{ i + 1 }}</span>
                                {{ line }}
                            </li>
                            <li v-if="!missionLines.length" class="text-sm text-slate-400 italic">Belum diisi.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Kontak -->
            <div v-if="school?.address || school?.phone || school?.email || school?.website">
                <div v-reveal class="mb-6 flex items-center gap-4">
                    <div class="h-1 w-10 rounded-full bg-green-600"/>
                    <p class="text-xs font-bold uppercase tracking-widest text-green-600">Kontak</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    <div v-if="school?.address" v-reveal="{ delay: 80 }" class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-green-100">
                            <svg class="size-5 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Alamat</p>
                            <p class="mt-1 text-sm text-slate-700 leading-relaxed">{{ school.address }}</p>
                        </div>
                    </div>

                    <div v-if="school?.phone" v-reveal="{ delay: 160 }" class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100">
                            <svg class="size-5 text-sky-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Telepon</p>
                            <a :href="`tel:${school.phone}`" class="mt-1 block text-sm font-semibold text-slate-700 hover:text-green-700 transition-colors">{{ school.phone }}</a>
                        </div>
                    </div>

                    <div v-if="school?.email" v-reveal="{ delay: 240 }" class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                            <svg class="size-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Email</p>
                            <a :href="`mailto:${school.email}`" class="mt-1 block truncate text-sm font-semibold text-slate-700 hover:text-green-700 transition-colors">{{ school.email }}</a>
                        </div>
                    </div>

                    <div v-if="school?.website" v-reveal="{ delay: 320 }" class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-100">
                            <svg class="size-5 text-violet-700" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Website</p>
                            <a :href="school.website" target="_blank" rel="noopener noreferrer"
                                class="mt-1 block truncate text-sm font-semibold text-slate-700 hover:text-green-700 transition-colors">
                                {{ school.website }}
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!school"
                class="rounded-2xl border-2 border-dashed border-slate-200 py-24 text-center"
            >
                <svg class="mx-auto mb-3 size-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                <p class="text-sm text-slate-400">Informasi sekolah belum diisi oleh operator.</p>
            </div>

        </div>

        <!-- ── Struktur Organisasi ────────────────────────────────────────── -->
        <div class="bg-slate-50 py-16">
            <div class="mx-auto max-w-6xl px-6">

                <div v-reveal class="mb-12 text-center">
                    <p class="text-xs font-bold uppercase tracking-widest text-green-600">Sumber Daya Manusia</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-slate-900 lg:text-3xl">Struktur Organisasi</h2>
                    <p class="mt-2 text-sm text-slate-500">Tenaga pendidik dan kependidikan {{ school?.name }}</p>
                </div>

                <!-- Tier 1: Kepala Madrasah -->
                <div v-reveal="{ from: 'scale' }" class="mb-6 flex justify-center">
                    <div class="relative w-60">
                        <div class="flex flex-col items-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-400 px-6 py-6 text-center shadow-lg">
                            <div class="flex size-16 items-center justify-center rounded-full bg-white/25 text-xl font-extrabold text-white ring-4 ring-white/30">
                                {{ initials(school?.principal_name ?? 'KM') }}
                            </div>
                            <p class="mt-3 text-base font-extrabold text-white leading-snug">{{ school?.principal_name ?? '—' }}</p>
                            <p v-if="school?.principal_nip" class="mt-1 text-xs text-white/70">NIP. {{ school.principal_nip }}</p>
                            <span class="mt-3 rounded-full bg-white/20 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-white">Kepala Madrasah</span>
                        </div>
                        <!-- connector line down -->
                        <div class="absolute -bottom-6 left-1/2 h-6 w-0.5 -translate-x-1/2 bg-slate-300"/>
                    </div>
                </div>

                <!-- Tier 2: Wakamad -->
                <div v-if="structure.wakamad?.length" class="mb-6">
                    <div class="relative mb-6 flex justify-center">
                        <div class="h-0.5 w-2/5 bg-slate-300"/>
                    </div>
                    <div class="flex flex-wrap justify-center gap-4">
                        <div v-for="(t, i) in structure.wakamad" :key="'wk-' + t.id"
                            v-reveal="{ delay: i * 80 }"
                            class="relative flex w-52 flex-col items-center rounded-2xl border border-orange-200 bg-gradient-to-b from-orange-50 to-white px-5 py-5 text-center shadow-sm">
                            <div class="flex size-14 items-center justify-center rounded-full bg-orange-100 text-sm font-extrabold text-orange-700 ring-2 ring-orange-200">
                                <img v-if="t.photo" :src="t.photo" :alt="t.name" class="size-full rounded-full object-cover"/>
                                <span v-else>{{ initials(t.name) }}</span>
                            </div>
                            <p class="mt-3 text-sm font-bold text-slate-800 leading-snug">{{ t.name }}</p>
                            <p v-if="t.nip" class="text-xs text-slate-400">NIP. {{ t.nip }}</p>
                            <span class="mt-2.5 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide"
                                :class="t.position === 'wakamad_kesiswaan'
                                    ? 'bg-orange-100 text-orange-700'
                                    : 'bg-teal-100 text-teal-700'">
                                {{ t.position === 'wakamad_kesiswaan' ? 'Wakamad Kesiswaan' : 'Wakamad Kurikulum' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tier 3: TU Keuangan + Operator -->
                <div v-if="structure.tu_keuangan?.length || structure.operators?.length" class="mb-6">
                    <!-- horizontal connector -->
                    <div class="relative mb-6 flex justify-center">
                        <div class="h-0.5 w-1/2 bg-slate-300"/>
                    </div>
                    <div class="flex flex-wrap justify-center gap-4">
                        <div v-for="(u, i) in structure.tu_keuangan" :key="'tu-' + u.id"
                            v-reveal="{ delay: i * 80 }"
                            class="flex w-44 flex-col items-center rounded-2xl border border-slate-200 bg-white px-4 py-5 text-center shadow-sm">
                            <div class="flex size-12 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700 ring-2 ring-emerald-200">
                                <img v-if="u.photo" :src="u.photo" :alt="u.name" class="size-full rounded-full object-cover"/>
                                <span v-else>{{ initials(u.name) }}</span>
                            </div>
                            <p class="mt-2.5 text-sm font-bold text-slate-800 leading-snug">{{ u.name }}</p>
                            <span class="mt-2 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-600">TU Keuangan</span>
                        </div>
                        <div v-for="(u, i) in structure.operators" :key="'op-' + u.id"
                            v-reveal="{ delay: i * 80 }"
                            class="flex w-44 flex-col items-center rounded-2xl border border-slate-200 bg-white px-4 py-5 text-center shadow-sm">
                            <div class="flex size-12 items-center justify-center rounded-full bg-violet-100 text-sm font-extrabold text-violet-700 ring-2 ring-violet-200">
                                <img v-if="u.photo" :src="u.photo" :alt="u.name" class="size-full rounded-full object-cover"/>
                                <span v-else>{{ initials(u.name) }}</span>
                            </div>
                            <p class="mt-2.5 text-sm font-bold text-slate-800 leading-snug">{{ u.name }}</p>
                            <span class="mt-2 rounded-full bg-violet-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-violet-600">Operator</span>
                        </div>
                    </div>
                </div>

                <!-- Connector ke tier guru -->
                <div v-if="structure.guru_kelas?.length || structure.guru_bidang?.length"
                    class="mb-8 flex justify-center">
                    <div class="h-6 w-0.5 bg-slate-300"/>
                </div>

                <!-- Tier 3: Guru -->
                <div v-if="structure.guru_kelas?.length || structure.guru_bidang?.length"
                    class="grid grid-cols-1 gap-8 lg:grid-cols-2">

                    <!-- Guru Kelas -->
                    <div v-if="structure.guru_kelas?.length"
                        v-reveal="{ from: 'left' }"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-sky-50 px-5 py-4">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-sky-600 shadow-sm">
                                <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800">Guru Kelas</h3>
                                <p class="text-xs text-slate-400">{{ structure.guru_kelas.length }} orang · Kelas 1–3</p>
                            </div>
                        </div>
                        <ul class="divide-y divide-slate-50">
                            <li v-for="(t, i) in structure.guru_kelas" :key="t.id"
                                class="flex items-center gap-3 px-5 py-3.5">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-extrabold text-sky-700">
                                    <img v-if="t.photo" :src="t.photo" :alt="t.name" class="size-full rounded-full object-cover"/>
                                    <span v-else>{{ initials(t.name) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ t.name }}</p>
                                    <p v-if="t.nip" class="text-xs text-slate-400">NIP. {{ t.nip }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-slate-300">{{ String(i + 1).padStart(2, '0') }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Guru Bidang -->
                    <div v-if="structure.guru_bidang?.length"
                        v-reveal="{ from: 'right', delay: 100 }"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-indigo-50 px-5 py-4">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
                                <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800">Guru Bidang Studi</h3>
                                <p class="text-xs text-slate-400">{{ structure.guru_bidang.length }} orang · Kelas 4–6</p>
                            </div>
                        </div>
                        <ul class="divide-y divide-slate-50">
                            <li v-for="(t, i) in structure.guru_bidang" :key="t.id"
                                class="flex items-center gap-3 px-5 py-3.5">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-extrabold text-indigo-700">
                                    <img v-if="t.photo" :src="t.photo" :alt="t.name" class="size-full rounded-full object-cover"/>
                                    <span v-else>{{ initials(t.name) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ t.name }}</p>
                                    <p v-if="t.nip" class="text-xs text-slate-400">NIP. {{ t.nip }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-slate-300">{{ String(i + 1).padStart(2, '0') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Empty state struktur -->
                <div v-if="!structure.guru_kelas?.length && !structure.guru_bidang?.length && !structure.tu_keuangan?.length && !structure.operators?.length"
                    class="rounded-2xl border-2 border-dashed border-slate-200 py-16 text-center">
                    <p class="text-sm text-slate-400">Data struktur organisasi belum tersedia.</p>
                </div>

            </div>
        </div>

        <!-- ── Footer ─────────────────────────────────────────────────────── -->
        <footer class="border-t border-slate-100 bg-slate-50 py-6">
            <div class="mx-auto max-w-6xl px-6 text-center">
                <p class="text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name ?? 'Sekolah' }}
                    <span v-if="school?.npsn"> · NPSN {{ school.npsn }}</span>
                </p>
            </div>
        </footer>
    </div>
</template>
