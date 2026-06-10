<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import JsonLd from '@/Components/JsonLd.vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    canLogin:         { type: Boolean, default: true },
    isLoggedIn:       { type: Boolean, default: false },
    dashboardRoute:   { type: String,  default: null },
    school:           { type: Object,  default: null },
    extracurriculars: { type: Array,   default: () => [] },
    galleries:        { type: Array,   default: () => [] },
    stats:            { type: Object,  default: () => ({}) },
    ppdbActive:       { type: Boolean, default: false },
    latestPosts:      { type: Array,   default: () => [] },
    heroPhotos:       { type: Array,   default: () => [] },
});

// ── smooth scroll helper (dipakai di hero CTA)

// ── hero slideshow
const PLACEHOLDER_SLIDES = [
    { url: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1600&q=80', caption: 'Lingkungan Belajar Kondusif' },
    { url: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=1600&q=80', caption: 'Pendidikan Berkualitas' },
    { url: 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=1600&q=80', caption: 'Generasi Berprestasi' },
    { url: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1600&q=80', caption: 'Belajar dengan Semangat' },
];

const heroSlides = computed(() => {
    const photos = props.galleries.filter(g => g.type === 'photo' && g.file_url);
    if (photos.length >= 2) return photos.map(p => ({ url: p.file_url, caption: p.caption ?? '' }));
    if (props.heroPhotos.length > 0) return props.heroPhotos.map(url => ({ url, caption: '' }));
    return PLACEHOLDER_SLIDES;
});

const currentSlide  = ref(0);
const transitioning = ref(false);
let   timer         = null;

const goTo = (idx) => {
    if (transitioning.value) return;
    transitioning.value = true;
    currentSlide.value  = (idx + heroSlides.value.length) % heroSlides.value.length;
    setTimeout(() => { transitioning.value = false; }, 800);
    resetTimer();
};
const next = () => goTo(currentSlide.value + 1);
const prev = () => goTo(currentSlide.value - 1);

const resetTimer = () => {
    clearInterval(timer);
    timer = setInterval(next, 5000);
};

const scrollTo = (id) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// ── lightbox state
const lightbox = ref(null);

// ── youtube helpers 
const ytId    = (url) => { if (!url) return null; const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([^&\n?#]+)/); return m?.[1] ?? null; };
const ytThumb = (url) => { const id = ytId(url); return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : null; };
const ytEmbed = (url) => { const id = ytId(url); return id ? `https://www.youtube.com/embed/${id}` : null; };

// ── misi split
const missionLines = computed(() => (props.school?.mission ?? '').split('\n').filter(l => l.trim()));

// ── ekskul color map
const ekskulColors = [
    'bg-primary-100 text-primary-700',
    'bg-amber-100 text-amber-700',
    'bg-sky-100 text-sky-700',
    'bg-violet-100 text-violet-700',
    'bg-rose-100 text-rose-700',
    'bg-orange-100 text-orange-700',
    'bg-teal-100 text-teal-700',
    'bg-indigo-100 text-indigo-700',
];

onMounted(() => { resetTimer(); });
onUnmounted(() => { clearInterval(timer); });

const baseUrl = usePage().props.ziggy?.url ?? '';
const jsonLd = computed(() => ({
    '@context':  'https://schema.org',
    '@type':     'WebPage',
    '@id':       `${baseUrl}/#webpage`,
    'name':      props.school?.name ?? '',
    'description': props.school?.tagline || props.school?.description || '',
    'url':       baseUrl,
    'isPartOf':  { '@id': `${baseUrl}/#website` },
    'about':     { '@id': `${baseUrl}/#school` },
    'breadcrumb': {
        '@type': 'BreadcrumbList',
        'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Beranda', 'item': baseUrl },
        ],
    },
}));
</script>

<template>
    <Head :title="school?.name ?? 'Beranda'">
        <meta head-key="description" name="description" :content="school?.tagline || school?.description || ''">
        <meta head-key="og:title" property="og:title" :content="school?.name ?? ''">
        <meta head-key="og:description" property="og:description" :content="school?.tagline || school?.description || ''">
        <meta head-key="og:type" property="og:type" content="website">
        <meta v-if="school?.logo" head-key="og:image" property="og:image" :content="school.logo">
        <meta v-if="school?.logo" head-key="twitter:image" name="twitter:image" :content="school.logo">
        <meta head-key="twitter:title" name="twitter:title" :content="school?.name ?? ''">
        <meta head-key="twitter:description" name="twitter:description" :content="school?.tagline || school?.description || ''">
    </Head>
    <JsonLd :data="jsonLd" />

    <div class="min-h-screen overflow-x-clip bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <!-- Header -->
        <PublicHeader
            :school="school"
            :can-login="canLogin"
            :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute"
            :ppdb-active="ppdbActive"
            active-page=""
        />

        <!-- Hero Section -->
        <section id="beranda" class="relative flex h-screen min-h-[600px] flex-col overflow-hidden">

            <!-- Slide backgrounds (cross-fade) -->
            <div class="absolute inset-0">
                <div
                    v-for="(slide, i) in heroSlides" :key="i"
                    class="absolute inset-0 bg-center bg-cover transition-opacity duration-1000"
                    :style="{ backgroundImage: 'url(' + slide.url + ')' }"
                    :class="i === currentSlide ? 'opacity-100' : 'opacity-0'"
                />
                <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/30 to-black/75"/>
                <div class="absolute inset-0 bg-primary-950/30"/>
            </div>

            <!-- Main content (vertically centered) -->
            <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 text-center">

                <!-- Logo -->
                <div v-reveal="{ from: 'scale', delay: 0 }" class="mb-6">
                    <img
                        v-if="school && school.logo"
                        :src="school.logo"
                        alt="Logo"
                        class="mx-auto size-24 rounded-2xl object-contain drop-shadow-2xl ring-4 ring-white/20 lg:size-28"
                    />
                    <div v-else class="mx-auto flex size-24 items-center justify-center rounded-2xl bg-white/10 ring-4 ring-white/20 backdrop-blur-sm lg:size-28">
                        <svg class="size-12 text-white/70" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                    </div>
                </div>

                <!-- Badge -->
                <div v-reveal="{ delay: 100 }" class="mb-4 inline-flex items-center gap-2 rounded-full border border-amber-400/50 bg-amber-400/10 px-4 py-1.5 backdrop-blur-sm">
                    <span class="size-1.5 animate-pulse rounded-full bg-amber-400"/>
                    <span class="text-xs font-semibold tracking-wide text-amber-300">Madrasah Ibtidaiyah · Terakreditasi A</span>
                </div>

                <!-- School name -->
                <h1 v-reveal="{ delay: 200 }" class="text-balance text-4xl font-extrabold leading-tight text-white drop-shadow-lg lg:text-6xl xl:text-7xl">
                    {{ school ? school.name : 'Nama Sekolah' }}
                </h1>

                <!-- Tagline -->
                <p v-if="school && school.tagline" v-reveal="{ delay: 300 }" class="mt-4 text-base font-medium italic text-white/80 lg:text-xl">
                    "{{ school.tagline }}"
                </p>

                <!-- Slide caption -->
                <p class="mt-2 min-h-[20px] text-sm text-white/40">
                    {{ heroSlides[currentSlide] ? heroSlides[currentSlide].caption : '' }}
                </p>

                <!-- CTA buttons -->
                <div v-reveal="{ delay: 400 }" class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:flex-wrap">
                    <button
                        @click="scrollTo('tentang')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-primary-800 shadow-xl transition-all active:scale-95 hover:bg-primary-50 sm:w-auto"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                        Profil Sekolah
                    </button>
                    <Link
                        v-if="!isLoggedIn && canLogin" :href="route('login')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition-all active:scale-95 hover:bg-white/20 sm:w-auto"
                    >
                        Portal Siswa &amp; Guru
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                    <Link
                        v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition-all active:scale-95 hover:bg-white/20 sm:w-auto"
                    >
                        Buka Dashboard
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>

                <!-- Dots + arrows -->
                <div v-reveal="{ delay: 500 }" class="mt-10 flex items-center gap-4">
                    <button @click="prev" class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-sm transition-colors hover:bg-white/25">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <div class="flex items-center gap-2">
                        <button
                            v-for="(_, i) in heroSlides" :key="i"
                            @click="goTo(i)"
                            :class="i === currentSlide ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/60'"
                            class="h-2 rounded-full transition-all duration-300"
                        />
                    </div>
                    <button @click="next" class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-sm transition-colors hover:bg-white/25">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>
            </div>

            <!-- Stats bar (pinned to bottom) -->
            <div class="relative z-10 grid grid-cols-2 divide-x divide-white/10 border-t border-white/10 bg-black/40 backdrop-blur-md lg:grid-cols-4">
                <div v-for="s in [
                    { value: stats.students  || '-', label: 'Siswa Aktif' },
                    { value: stats.teachers  || '-', label: 'Tenaga Pendidik' },
                    { value: stats.extracurriculars || '-', label: 'Ekstrakulikuler' },
                    { value: stats.since     || '-', label: 'Tahun Berdiri' },
                ]" :key="s.label"
                    class="flex flex-col items-center justify-center py-4 text-center"
                >
                    <span class="text-xl font-extrabold text-white lg:text-2xl">{{ s.value }}</span>
                    <span class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-white/50">{{ s.label }}</span>
                </div>
            </div>
        </section>


        <!-- PPDB Banner -->
        <section v-if="ppdbActive" class="relative overflow-hidden bg-gradient-to-r from-amber-500 via-amber-500 to-yellow-400 py-10">
            <div class="pointer-events-none absolute -right-10 -top-10 size-52 rounded-full bg-white/10"/>
            <div class="pointer-events-none absolute -bottom-8 -left-6 size-40 rounded-full bg-amber-600/30"/>
            <div class="relative mx-auto max-w-6xl px-6">
                <div v-reveal class="flex flex-col items-center justify-between gap-6 text-center sm:flex-row sm:text-left">
                    <div class="flex items-center gap-5">
                        <div class="hidden size-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 sm:flex">
                            <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-amber-100">Penerimaan Peserta Didik Baru</p>
                            <h2 class="mt-0.5 text-2xl font-extrabold text-white">PPDB Sekarang Dibuka!</h2>
                            <p class="mt-1 text-sm text-amber-100/80">Daftarkan putra-putri Anda sekarang. Tempat terbatas.</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                        <Link :href="route('ppdb.index')"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-amber-700 shadow-lg transition-all hover:bg-amber-50 hover:shadow-xl active:scale-95">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Daftar PPDB
                        </Link>
                        <Link :href="route('ppdb.check')"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/40 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">
                            Cek Status
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tentang Sekolah -->
        <section id="tentang" class="bg-white py-24">
            <div class="mx-auto max-w-6xl px-6">
                <!-- Label -->
                <div v-reveal class="mb-14">
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-primary-700">
                        <span class="size-1.5 rounded-full bg-primary-500"/>
                        Tentang Kami
                    </span>
                </div>

                <div class="flex flex-col gap-16 lg:flex-row lg:items-start lg:gap-20">
                    <!-- Kiri: konten -->
                    <div v-reveal="{ from: 'left' }" class="min-w-0 flex-1">
                        <h2 class="text-4xl font-extrabold leading-tight text-slate-900 lg:text-5xl">
                            Mengenal<br/>
                            <span class="text-primary-700">{{ school?.name ?? 'Sekolah Kami' }}</span>
                        </h2>

                        <p v-if="school?.description" class="mt-6 max-w-xl text-base leading-relaxed text-slate-500">
                            {{ school.description }}
                        </p>
                        <p v-else-if="school?.tagline" class="mt-6 max-w-xl text-lg italic leading-relaxed text-slate-500">
                            "{{ school.tagline }}"
                        </p>

                        <div class="mt-10 space-y-5">
                            <div v-if="school?.vision" class="group flex items-start gap-4">
                                <div class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-100 transition-colors group-hover:bg-primary-700">
                                    <svg class="size-4 text-primary-700 transition-colors group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-widest text-primary-600">Visi</p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-700">{{ school.vision.length > 100 ? school.vision.slice(0, 100) + '…' : school.vision }}</p>
                                </div>
                            </div>
                            <div v-if="school?.mission" class="group flex items-start gap-4">
                                <div class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 transition-colors group-hover:bg-amber-500">
                                    <svg class="size-4 text-amber-600 transition-colors group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Misi</p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-700">Membimbing siswa berprestasi secara akademik dan berkarakter mulia</p>
                                </div>
                            </div>
                            <div v-if="school?.principal_name" class="group flex items-start gap-4">
                                <div class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl bg-sky-100 transition-colors group-hover:bg-sky-500">
                                    <svg class="size-4 text-sky-600 transition-colors group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-widest text-sky-600">Kepala Madrasah</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ school.principal_name }}</p>
                                    <p v-if="school.principal_nip" class="text-xs text-slate-500">NIP {{ school.principal_nip }}</p>
                                </div>
                            </div>
                        </div>

                        <Link :href="route('tentang')"
                            class="mt-10 inline-flex items-center gap-2 rounded-xl bg-primary-700 px-6 py-3.5 text-sm font-bold text-white shadow-md transition-all hover:bg-primary-600 hover:shadow-lg active:scale-95">
                            Profil Lengkap Sekolah
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                    </div>

                    <!-- Kanan: foto sekolah -->
                    <div v-reveal="{ from: 'right', delay: 150 }" class="lg:w-96">
                        <div class="relative overflow-hidden rounded-3xl bg-slate-100 shadow-xl" style="aspect-ratio: 4/5;">
                            <!-- Foto dari galeri (prioritas foto, bukan video) -->
                            <img
                                v-if="galleries.find(g => g.type === 'photo' && g.file_url)"
                                :src="galleries.find(g => g.type === 'photo' && g.file_url).file_url"
                                alt="Foto Sekolah"
                                class="size-full object-cover"
                            />
                            <!-- Fallback: placeholder gradient -->
                            <div v-else class="flex size-full flex-col items-center justify-center bg-gradient-to-br from-primary-100 via-primary-50 to-slate-100">
                                <svg class="size-20 text-primary-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                <p class="mt-4 text-sm font-medium text-primary-400">{{ school?.name ?? 'Foto Sekolah' }}</p>
                            </div>
                            <!-- Overlay badge nama sekolah di bawah -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                                <div class="flex items-center gap-3">
                                    <img v-if="school?.logo" :src="school.logo" alt="Logo" class="size-9 shrink-0 rounded-lg object-contain ring-2 ring-white/30"/>
                                    <div>
                                        <p class="text-sm font-bold leading-snug text-white">{{ school?.name ?? 'Nama Sekolah' }}</p>
                                        <p v-if="school?.npsn" class="text-xs text-white/60">NPSN {{ school.npsn }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ekskul -->
        <section id="ekskul" class="bg-primary-950 py-24">
            <div class="mx-auto max-w-6xl px-6">

                <div v-reveal class="mb-14 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-primary-400">Kegiatan Siswa</p>
                        <h2 class="mt-2 text-3xl font-extrabold text-white lg:text-4xl">Ekstrakulikuler</h2>
                    </div>
                    <Link v-if="extracurriculars.length > 4" :href="route('ekskul')"
                        class="hidden shrink-0 items-center gap-1.5 rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-white/60 transition-all hover:border-white/30 hover:text-white sm:inline-flex">
                        Lihat Semua
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>

                <!-- Bento grid -->
                <div v-if="extracurriculars.length" class="grid grid-cols-2 gap-4 lg:grid-cols-3 lg:grid-rows-2">

                    <!-- Featured (kiri, span 2 baris di desktop) -->
                    <div
                        v-if="extracurriculars[0]"
                        v-reveal="{ delay: 0 }"
                        class="group relative col-span-2 overflow-hidden rounded-3xl bg-slate-800 lg:col-span-1 lg:row-span-2"
                        style="min-height: 280px;"
                    >
                        <img
                            v-if="extracurriculars[0].image"
                            :src="extracurriculars[0].image"
                            :alt="extracurriculars[0].name"
                            class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div v-else class="absolute inset-0 flex items-center justify-center text-8xl font-black text-white/5 select-none">
                            {{ extracurriculars[0].name[0] }}
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"/>
                        <div class="absolute bottom-0 left-0 right-0 p-6">
                            <span class="inline-block rounded-full bg-primary-500/20 px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary-400 backdrop-blur-sm">Unggulan</span>
                            <h3 class="mt-2 text-xl font-extrabold text-white">{{ extracurriculars[0].name }}</h3>
                            <p v-if="extracurriculars[0].description" class="mt-1.5 line-clamp-2 text-sm text-white/60">{{ extracurriculars[0].description }}</p>
                        </div>
                    </div>

                    <!-- Card-card kecil -->
                    <div
                        v-for="(ekskul, i) in extracurriculars.slice(1, 5)" :key="ekskul.id"
                        v-reveal="{ delay: (i + 1) * 80 }"
                        class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 transition-all duration-200 hover:border-white/10 hover:bg-white/10"
                        style="min-height: 130px;"
                    >
                        <img
                            v-if="ekskul.image"
                            :src="ekskul.image"
                            :alt="ekskul.name"
                            class="absolute inset-0 size-full object-cover opacity-25 transition-opacity duration-300 group-hover:opacity-40"
                        />
                        <div class="absolute inset-0 flex flex-col justify-end p-5">
                            <div :class="ekskulColors[(i + 1) % ekskulColors.length].split(' ')[0]" class="mb-2 size-2 rounded-full"/>
                            <h3 class="text-sm font-bold leading-snug text-white">{{ ekskul.name }}</h3>
                            <p v-if="ekskul.description" class="mt-1 line-clamp-1 text-xs text-white/40">{{ ekskul.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="rounded-2xl border-2 border-dashed border-white/10 py-16 text-center">
                    <p class="text-sm text-white/30">Belum ada data ekskul.</p>
                </div>

                <div v-if="extracurriculars.length > 4" class="mt-8 text-center sm:hidden">
                    <Link :href="route('ekskul')"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-5 py-2.5 text-sm font-semibold text-white/60 transition-colors hover:border-white/30 hover:text-white">
                        Lihat Semua Ekstrakulikuler ({{ extracurriculars.length }})
                    </Link>
                </div>
            </div>
        </section>

        <!-- Galeri -->
        <section id="galeri" class="bg-white py-24">
            <div class="mx-auto max-w-6xl px-6">

                <div v-reveal class="mb-14 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-primary-600">Dokumentasi</p>
                        <h2 class="mt-2 text-3xl font-extrabold text-slate-900 lg:text-4xl">Galeri</h2>
                    </div>
                    <Link v-if="galleries.length > 6" :href="route('galeri')"
                        class="hidden shrink-0 items-center gap-1.5 text-sm font-semibold text-primary-700 hover:text-primary-600 transition-colors sm:flex">
                        Lihat Semua
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>

                <!-- Masonry grid (preview: 6 item) -->
                <div v-if="galleries.length" class="columns-2 gap-4 sm:columns-3">
                    <div
                        v-for="(item, i) in galleries.slice(0, 6)" :key="item.id"
                        v-reveal="{ delay: (i % 3) * 70 }"
                        class="group mb-4 cursor-pointer break-inside-avoid overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 shadow-sm transition-shadow hover:shadow-md"
                        @click="lightbox = item"
                    >
                        <div class="relative overflow-hidden">
                            <img
                                v-if="item.type === 'photo' && item.file_url"
                                :src="item.file_url"
                                class="w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                            <div v-else-if="item.type === 'video'">
                                <img
                                    v-if="ytThumb(item.video_url)"
                                    :src="ytThumb(item.video_url)"
                                    class="w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                />
                                <div class="absolute inset-0 flex items-center justify-center bg-black/25 transition-colors group-hover:bg-black/40">
                                    <div class="flex size-14 items-center justify-center rounded-full bg-white/95 shadow-xl">
                                        <svg class="size-7 translate-x-0.5 text-primary-700" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="item.caption" class="px-4 py-2.5">
                            <p class="text-xs font-medium text-slate-500">{{ item.caption }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="rounded-2xl border-2 border-dashed border-slate-200 py-16 text-center">
                    <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                    <p class="text-sm text-slate-400">Belum ada foto atau video.</p>
                </div>

                <div v-if="galleries.length > 6" class="mt-8 text-center">
                    <Link :href="route('galeri')"
                        class="inline-flex items-center gap-2 rounded-xl border border-primary-200 bg-white px-5 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition-colors hover:bg-primary-50">
                        Lihat Semua Galeri ({{ galleries.length }})
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Berita & Pengumuman Terbaru -->
        <section v-if="latestPosts.length" id="berita" class="bg-slate-50 py-24">
            <div class="mx-auto max-w-6xl px-6">

                <div v-reveal class="mb-14 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-primary-600">Informasi Sekolah</p>
                        <h2 class="mt-2 text-3xl font-extrabold text-slate-900 lg:text-4xl">Berita & Pengumuman</h2>
                    </div>
                    <Link :href="route('berita.index')"
                        class="hidden shrink-0 items-center gap-1.5 text-sm font-semibold text-primary-700 hover:text-primary-600 transition-colors sm:flex">
                        Lihat Semua
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>

                <!-- Featured post -->
                <Link v-if="latestPosts[0]"
                    v-reveal
                    :href="route('berita.show', latestPosts[0].slug)"
                    class="group mb-5 flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm transition-all duration-300 hover:shadow-xl sm:flex-row"
                >
                    <div class="aspect-[16/9] shrink-0 overflow-hidden bg-slate-100 sm:aspect-auto sm:w-2/5">
                        <img
                            v-if="latestPosts[0].cover_image"
                            :src="latestPosts[0].cover_image"
                            :alt="latestPosts[0].title"
                            class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div v-else class="flex size-full items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200">
                            <svg class="size-16 text-primary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5z"/></svg>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col justify-center p-7 lg:p-10">
                        <div class="mb-4 flex items-center gap-3">
                            <span :class="latestPosts[0].category === 'pengumuman' ? 'bg-amber-100 text-amber-700' : 'bg-primary-100 text-primary-700'"
                                class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide">
                                {{ latestPosts[0].category === 'pengumuman' ? 'Pengumuman' : 'Berita' }}
                            </span>
                            <span class="text-xs text-slate-400">{{ latestPosts[0].published_at }}</span>
                        </div>
                        <h3 class="text-xl font-extrabold leading-snug text-slate-800 transition-colors group-hover:text-primary-700 lg:text-2xl">
                            {{ latestPosts[0].title }}
                        </h3>
                        <p v-if="latestPosts[0].excerpt" class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-500">{{ latestPosts[0].excerpt }}</p>
                        <span class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-primary-700">
                            Baca selengkapnya
                            <svg class="size-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </span>
                    </div>
                </Link>

                <!-- Post lainnya: compact rows -->
                <div v-if="latestPosts.length > 1" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Link
                        v-for="(post, i) in latestPosts.slice(1)" :key="post.id"
                        :href="route('berita.show', post.slug)"
                        v-reveal="{ delay: i * 80 }"
                        class="group flex gap-4 overflow-hidden rounded-2xl bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md"
                    >
                        <div class="size-20 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                            <img
                                v-if="post.cover_image"
                                :src="post.cover_image"
                                :alt="post.title"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                            <div v-else :class="post.category === 'pengumuman' ? 'bg-amber-50' : 'bg-primary-50'" class="flex size-full items-center justify-center">
                                <svg class="size-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5z"/></svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1.5 flex items-center gap-2">
                                <span :class="post.category === 'pengumuman' ? 'bg-amber-100 text-amber-700' : 'bg-primary-100 text-primary-700'"
                                    class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">
                                    {{ post.category === 'pengumuman' ? 'Pengumuman' : 'Berita' }}
                                </span>
                                <span class="text-xs text-slate-400">{{ post.published_at }}</span>
                            </div>
                            <h3 class="line-clamp-2 text-sm font-bold leading-snug text-slate-800 transition-colors group-hover:text-primary-700">{{ post.title }}</h3>
                        </div>
                    </Link>
                </div>

                <div class="mt-8 text-center sm:hidden">
                    <Link :href="route('berita.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-primary-200 bg-white px-5 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition-colors hover:bg-primary-50">
                        Lihat Semua Berita & Pengumuman
                    </Link>
                </div>
            </div>
        </section>

        <!-- Kontak -->
        <section id="kontak" class="bg-primary-950 py-20">
            <div class="mx-auto max-w-6xl px-6">
                <div class="grid gap-16 lg:grid-cols-2 lg:items-center">

                    <!-- Kiri: heading + CTA -->
                    <div v-reveal>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-400">Hubungi Kami</p>
                        <h2 class="mt-4 text-5xl font-extrabold leading-none text-white lg:text-6xl">
                            Kontak<br/>
                            <span class="text-primary-600">&amp; Lokasi</span>
                        </h2>
                        <p class="mt-6 max-w-xs text-sm leading-relaxed text-white/40">
                            Kami terbuka untuk pertanyaan dan kunjungan. Hubungi kami kapan saja.
                        </p>
                        <div class="mt-10">
                            <Link v-if="!isLoggedIn && canLogin" :href="route('login')"
                                class="inline-flex items-center gap-2.5 rounded-2xl bg-amber-400 px-7 py-4 text-sm font-bold text-primary-900 shadow-lg transition-all hover:bg-amber-300 active:scale-95">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                Masuk ke Portal Akademik
                            </Link>
                            <Link v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute"
                                class="inline-flex items-center gap-2.5 rounded-2xl bg-amber-400 px-7 py-4 text-sm font-bold text-primary-900 shadow-lg transition-all hover:bg-amber-300 active:scale-95">
                                Buka Dashboard
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Kanan: info list, left-border accent, no cards -->
                    <div v-reveal="{ delay: 150 }" class="space-y-8">

                        <div v-if="school?.address" class="border-l-2 border-primary-600 pl-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-white/30">Alamat</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-white/80">{{ school.address }}</p>
                        </div>

                        <div v-if="school?.principal_name" class="border-l-2 border-amber-500 pl-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-white/30">Kepala Sekolah</p>
                            <p class="mt-1.5 text-sm font-semibold text-white">{{ school.principal_name }}</p>
                            <p v-if="school.principal_nip" class="mt-0.5 text-xs text-white/30">NIP {{ school.principal_nip }}</p>
                        </div>

                        <div v-if="school?.phone" class="border-l-2 border-sky-500 pl-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-white/30">Telepon</p>
                            <a :href="`tel:${school.phone}`" class="mt-1.5 block text-sm font-semibold text-white transition-colors hover:text-amber-400">{{ school.phone }}</a>
                        </div>

                        <div v-if="school?.email" class="border-l-2 border-violet-500 pl-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-white/30">Email</p>
                            <a :href="`mailto:${school.email}`" class="mt-1.5 block text-sm font-semibold text-white transition-colors hover:text-amber-400">{{ school.email }}</a>
                        </div>

                        <div v-if="school?.website" class="border-l-2 border-rose-500 pl-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-white/30">Website</p>
                            <a :href="school.website" target="_blank" rel="noopener noreferrer" class="mt-1.5 block text-sm font-semibold text-white transition-colors hover:text-amber-400">{{ school.website }}</a>
                        </div>

                        <p class="pt-2 text-xs text-white/20">Butuh akun? Hubungi operator sekolah.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-primary-950 py-4">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-col items-center gap-3 text-center sm:flex-row sm:justify-between sm:text-left">
                    <div class="flex items-center gap-2.5">
                        <img v-if="school?.logo" :src="school.logo" alt="Logo" class="size-6 rounded object-contain opacity-70"/>
                        <span class="text-xs font-semibold text-primary-400">{{ school?.name ?? 'Sistem Manajemen Sekolah' }}</span>
                        <span v-if="school?.npsn" class="text-xs text-primary-700">· NPSN {{ school.npsn }}</span>
                    </div>
                    <p class="text-xs text-primary-700">
                        &copy; {{ new Date().getFullYear() }} · Hak cipta dilindungi.
                    </p>
                </div>
            </div>
        </footer>

        <!-- Lightbox -->
        <Teleport to="body">
            <div
                v-if="lightbox"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 p-4"
                @click.self="lightbox = null"
            >
                <button @click="lightbox = null"
                    class="absolute right-5 top-5 flex size-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <img
                    v-if="lightbox.type === 'photo'"
                    :src="lightbox.file_url"
                    class="max-h-[88vh] max-w-full rounded-2xl object-contain shadow-2xl"
                />

                <div v-else class="w-full max-w-4xl">
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl" style="padding-top:56.25%">
                        <iframe
                            v-if="ytEmbed(lightbox.video_url)"
                            :src="ytEmbed(lightbox.video_url) + '?autoplay=1'"
                            class="absolute inset-0 size-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        />
                    </div>
                    <p v-if="lightbox.caption" class="mt-3 text-center text-sm text-white/50">{{ lightbox.caption }}</p>
                </div>
            </div>
        </Teleport>

    </div>
</template>
