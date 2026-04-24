<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    canLogin:         { type: Boolean, default: true },
    isLoggedIn:       { type: Boolean, default: false },
    dashboardRoute:   { type: String,  default: null },
    school:           { type: Object,  default: null },
    extracurriculars: { type: Array,   default: () => [] },
    galleries:        { type: Array,   default: () => [] },
    stats:            { type: Object,  default: () => ({}) },
    ppdbActive:       { type: Boolean, default: false },
});

// ── navbar scroll state 
const scrolled   = ref(false);
const mobileOpen = ref(false);
const onScroll   = () => { scrolled.value = window.scrollY > 60; };

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
    mobileOpen.value = false;
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
    'bg-green-100 text-green-700',
    'bg-amber-100 text-amber-700',
    'bg-sky-100 text-sky-700',
    'bg-violet-100 text-violet-700',
    'bg-rose-100 text-rose-700',
    'bg-orange-100 text-orange-700',
    'bg-teal-100 text-teal-700',
    'bg-indigo-100 text-indigo-700',
];

onMounted(() => {
    window.addEventListener('scroll', onScroll);
    resetTimer();
});
onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    clearInterval(timer);
});
</script>

<template>
    <Head :title="school?.name ?? 'Profil Sekolah'" />

    <div class="min-h-screen overflow-x-hidden bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <!-- Top Info Bar -->
        <div class="hidden bg-green-900 md:block">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-2 text-xs text-green-200">
                <div class="flex items-center gap-5">
                    <span v-if="school?.phone" class="flex items-center gap-1.5">
                        <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                        {{ school.phone }}
                    </span>
                    <span v-if="school?.email" class="flex items-center gap-1.5">
                        <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        {{ school.email }}
                    </span>
                </div>
                <span v-if="school?.npsn" class="text-green-300">NPSN: {{ school.npsn }}</span>
            </div>
        </div>

        <!-- Header / Navbar -->
        <header
            class="sticky top-0 z-50 border-b transition-shadow duration-300"
            :class="scrolled ? 'border-slate-200 bg-white shadow-md' : 'border-slate-100 bg-white'"
        >
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-6">
                <!-- Logo + Nama -->
                <button @click="scrollTo('beranda')" class="flex shrink-0 items-center gap-3">
                    <img v-if="school?.logo" :src="school.logo" alt="Logo" class="size-10 rounded-lg object-contain" />
                    <div v-else class="flex size-10 items-center justify-center rounded-lg bg-green-700 shadow-sm">
                        <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                    </div>
                    <div class="text-left leading-tight">
                        <p class="text-sm font-bold text-slate-800 leading-none">{{ school?.name ?? 'Nama Sekolah' }}</p>
                        <p v-if="school?.tagline" class="mt-0.5 text-[11px] text-green-700 font-medium">{{ school.tagline }}</p>
                    </div>
                </button>

                <!-- Desktop Nav -->
                <nav class="hidden items-center gap-1 md:flex">
                    <button @click="scrollTo('beranda')" class="rounded-lg px-3.5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-green-50 hover:text-green-700">Beranda</button>
                    <Link :href="route('tentang')" class="rounded-lg px-3.5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-green-50 hover:text-green-700">Tentang</Link>
                    <Link :href="route('ekskul')"  class="rounded-lg px-3.5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-green-50 hover:text-green-700">Ekskul</Link>
                    <Link :href="route('galeri')"  class="rounded-lg px-3.5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-green-50 hover:text-green-700">Galeri</Link>
                    <button @click="scrollTo('kontak')" class="rounded-lg px-3.5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-green-50 hover:text-green-700">Kontak</button>
                    <Link v-if="ppdbActive" :href="route('ppdb.index')"
                        class="ml-1 inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-sm font-semibold text-white transition-colors hover:bg-amber-400">
                        PPDB
                    </Link>
                </nav>

                <!-- CTA + Mobile toggle -->
                <div class="flex items-center gap-2">
                    <Link
                        v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute"
                        class="hidden items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-600 sm:inline-flex"
                    >
                        Dashboard
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                    <Link
                        v-else-if="canLogin" :href="route('login')"
                        class="hidden items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-600 sm:inline-flex"
                    >
                        Masuk
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                    <!-- Mobile hamburger -->
                    <button @click="mobileOpen = !mobileOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden">
                        <svg v-if="!mobileOpen" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <svg v-else class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileOpen" class="border-t border-slate-100 bg-white px-6 py-3 md:hidden">
                <div class="flex flex-col gap-1">
                    <button @click="scrollTo('beranda')" class="rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-green-50 hover:text-green-700">Beranda</button>
                    <Link :href="route('tentang')" class="rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-green-50 hover:text-green-700">Tentang</Link>
                    <Link :href="route('ekskul')"  class="rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-green-50 hover:text-green-700">Ekskul</Link>
                    <Link :href="route('galeri')"  class="rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-green-50 hover:text-green-700">Galeri</Link>
                    <button @click="scrollTo('kontak')" class="rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-green-50 hover:text-green-700">Kontak</button>
                    <Link v-if="ppdbActive" :href="route('ppdb.index')"
                        class="rounded-lg bg-amber-500 px-3 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-amber-400">
                        PPDB — Daftar Sekarang
                    </Link>
                </div>
            </div>
        </header>

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
                <div class="absolute inset-0 bg-green-950/30"/>
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
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-green-800 shadow-xl transition-all active:scale-95 hover:bg-green-50 sm:w-auto"
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
        <section v-if="ppdbActive" class="bg-gradient-to-r from-amber-600 to-yellow-500 py-10">
            <div class="mx-auto max-w-6xl px-6">
                <div v-reveal class="flex flex-col items-center justify-between gap-6 text-center sm:flex-row sm:text-left">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-100">Penerimaan Peserta Didik Baru</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-white">PPDB Sekarang Dibuka!</h2>
                        <p class="mt-1 text-sm text-amber-100">Daftarkan putra-putri Anda sekarang. Tempat terbatas.</p>
                    </div>
                    <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                        <Link :href="route('ppdb.index')"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-amber-700 shadow transition-all hover:bg-amber-50 active:scale-95">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Daftar PPDB
                        </Link>
                        <Link :href="route('ppdb.check')"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95">
                            Cek Status
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tentang Sekolah -->
        <section id="tentang" class="bg-white py-20">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-col gap-12 lg:flex-row lg:items-center lg:gap-16">

                    <!-- Kolom kiri: teks -->
                    <div v-reveal="{ from: 'left' }" class="flex-1">
                        <p class="text-xs font-bold uppercase tracking-widest text-green-600">Tentang Kami</p>
                        <h2 class="mt-2 text-3xl font-extrabold leading-tight text-slate-900 lg:text-4xl">
                            Mengenal<br class="hidden lg:block"/>
                            <span class="text-green-700">{{ school?.name ?? 'Sekolah Kami' }}</span>
                        </h2>

                        <p v-if="school?.description" class="mt-5 line-clamp-4 text-base leading-relaxed text-slate-500">
                            {{ school.description }}
                        </p>
                        <p v-else-if="school?.tagline" class="mt-5 text-base leading-relaxed text-slate-500 italic">
                            "{{ school.tagline }}"
                        </p>

                        <!-- Feature list -->
                        <ul class="mt-8 space-y-3">
                            <li v-if="school?.vision" class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                                    <svg class="size-3.5 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <span class="text-sm text-slate-600"><span class="font-semibold text-slate-800">Visi</span> — {{ school.vision.length > 80 ? school.vision.slice(0, 80) + '…' : school.vision }}</span>
                            </li>
                            <li v-if="school?.mission" class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                                    <svg class="size-3.5 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <span class="text-sm text-slate-600"><span class="font-semibold text-slate-800">Misi</span> terdefinisi untuk membimbing siswa berprestasi</span>
                            </li>
                            <li v-if="school?.history" class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                                    <svg class="size-3.5 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <span class="text-sm text-slate-600"><span class="font-semibold text-slate-800">Sejarah</span> panjang membangun generasi berkualitas</span>
                            </li>
                            <li v-if="school?.principal_name" class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                                    <svg class="size-3.5 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <span class="text-sm text-slate-600">Dipimpin oleh <span class="font-semibold text-slate-800">{{ school.principal_name }}</span></span>
                            </li>
                        </ul>

                        <Link :href="route('tentang')"
                            class="mt-8 inline-flex items-center gap-2 rounded-xl bg-green-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-green-600">
                            Profil Lengkap Sekolah
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                    </div>

                    <!-- Kolom kanan: identity card -->
                    <div v-reveal="{ from: 'right', delay: 150 }" class="flex shrink-0 justify-center lg:w-80">
                        <div class="relative w-full max-w-sm">
                            <!-- Card utama -->
                            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-800 to-green-600 p-8 text-center shadow-2xl">
                                <!-- Dekorasi lingkaran -->
                                <div class="absolute -right-10 -top-10 size-40 rounded-full bg-white/5"/>
                                <div class="absolute -bottom-12 -left-8 size-48 rounded-full bg-white/5"/>

                                <!-- Logo -->
                                <div class="relative mb-5 inline-flex">
                                    <img v-if="school?.logo" :src="school.logo" alt="Logo"
                                        class="size-24 rounded-2xl object-contain ring-4 ring-white/20 shadow-xl"/>
                                    <div v-else class="flex size-24 items-center justify-center rounded-2xl bg-white/15 ring-4 ring-white/20">
                                        <svg class="size-12 text-white/60" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Nama sekolah -->
                                <h3 class="relative text-lg font-extrabold leading-snug text-white">
                                    {{ school?.name ?? 'Nama Sekolah' }}
                                </h3>
                                <p v-if="school?.tagline" class="relative mt-1.5 text-xs font-medium italic text-white/60">
                                    "{{ school.tagline }}"
                                </p>
                                <div v-if="school?.npsn" class="relative mt-4 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">
                                    NPSN {{ school.npsn }}
                                </div>

                                <!-- Separator -->
                                <div class="relative my-5 h-px w-full bg-white/15"/>

                                <!-- Stats mini -->
                                <div class="relative grid grid-cols-3 gap-3">
                                    <div v-for="s in [
                                        { value: stats.students || '–',  label: 'Siswa' },
                                        { value: stats.teachers || '–',  label: 'Guru' },
                                        { value: stats.since    || '–',  label: 'Berdiri' },
                                    ]" :key="s.label" class="flex flex-col items-center">
                                        <span class="text-xl font-extrabold text-white">{{ s.value }}</span>
                                        <span class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-white/50">{{ s.label }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Shadow card bawah (depth effect) -->
                            <div class="absolute -bottom-3 left-4 right-4 -z-10 h-full rounded-3xl bg-green-300/30 blur-sm"/>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Ekskul -->
        <section id="ekskul" class="bg-slate-50 py-20">
            <div class="mx-auto max-w-6xl px-6">

                <div v-reveal class="mb-12 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-green-600"/>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-green-600">Kegiatan Siswa</p>
                            <h2 class="mt-0.5 text-2xl font-extrabold text-slate-900 lg:text-3xl">Ekstrakulikuler</h2>
                        </div>
                    </div>
                    <Link v-if="extracurriculars.length > 4" :href="route('ekskul')"
                        class="hidden shrink-0 items-center gap-1.5 text-sm font-semibold text-green-700 hover:text-green-600 transition-colors sm:flex">
                        Lihat Semua
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>

                <!-- Grid ekskul (preview: 4 item) -->
                <div v-if="extracurriculars.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <div
                        v-for="(ekskul, i) in extracurriculars.slice(0, 4)" :key="ekskul.id"
                        v-reveal="{ delay: i * 80 }"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="aspect-[4/3] overflow-hidden">
                            <img
                                v-if="ekskul.image"
                                :src="ekskul.image"
                                :alt="ekskul.name"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                            <div v-else :class="ekskulColors[i % ekskulColors.length]"
                                class="flex size-full items-center justify-center text-4xl font-black opacity-30 select-none"
                            >
                                {{ ekskul.name[0] }}
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-slate-800 text-sm">{{ ekskul.name }}</h3>
                            <p v-if="ekskul.description" class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ ekskul.description }}</p>
                        </div>
                        <div :class="['h-0.5 w-0 group-hover:w-full transition-all duration-300', ekskulColors[i % ekskulColors.length].split(' ')[0].replace('bg-', 'bg-') ]"/>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="rounded-2xl border-2 border-dashed border-slate-200 py-16 text-center">
                    <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                    <p class="text-sm text-slate-400">Belum ada data ekskul.</p>
                </div>

                <!-- Lihat semua button (mobile + overflow) -->
                <div v-if="extracurriculars.length > 4" class="mt-8 text-center">
                    <Link :href="route('ekskul')"
                        class="inline-flex items-center gap-2 rounded-xl border border-green-200 bg-white px-5 py-2.5 text-sm font-semibold text-green-700 shadow-sm transition-colors hover:bg-green-50">
                        Lihat Semua Ekstrakulikuler ({{ extracurriculars.length }})
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Galeri -->
        <section id="galeri" class="bg-white py-20">
            <div class="mx-auto max-w-6xl px-6">

                <div v-reveal class="mb-12 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-10 rounded-full bg-green-600"/>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-green-600">Dokumentasi</p>
                            <h2 class="mt-0.5 text-2xl font-extrabold text-slate-900 lg:text-3xl">Galeri</h2>
                        </div>
                    </div>
                    <Link v-if="galleries.length > 6" :href="route('galeri')"
                        class="hidden shrink-0 items-center gap-1.5 text-sm font-semibold text-green-700 hover:text-green-600 transition-colors sm:flex">
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
                                        <svg class="size-7 translate-x-0.5 text-green-700" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
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

                <!-- Lihat semua button -->
                <div v-if="galleries.length > 6" class="mt-8 text-center">
                    <Link :href="route('galeri')"
                        class="inline-flex items-center gap-2 rounded-xl border border-green-200 bg-white px-5 py-2.5 text-sm font-semibold text-green-700 shadow-sm transition-colors hover:bg-green-50">
                        Lihat Semua Galeri ({{ galleries.length }})
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Kontak -->
        <section id="kontak" class="bg-green-900 py-20">
            <div class="mx-auto max-w-6xl px-6">

                <div v-reveal class="mb-12 flex items-center gap-4">
                    <div class="h-1 w-10 rounded-full bg-amber-400"/>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-400">Hubungi Kami</p>
                        <h2 class="mt-0.5 text-2xl font-extrabold text-white lg:text-3xl">Kontak & Lokasi</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Info kontak -->
                    <div class="lg:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div v-if="school?.principal_name" v-reveal="{ delay: 80 }" class="flex items-start gap-4 rounded-2xl bg-white/5 p-5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/20">
                                <svg class="size-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-green-400">Kepala Sekolah</p>
                                <p class="mt-0.5 text-sm font-semibold text-white">{{ school.principal_name }}</p>
                                <p v-if="school.principal_nip" class="text-xs text-white/40">NIP {{ school.principal_nip }}</p>
                            </div>
                        </div>

                        <div v-if="school?.address" v-reveal="{ delay: 160 }" class="flex items-start gap-4 rounded-2xl bg-white/5 p-5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-green-500/20">
                                <svg class="size-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-green-400">Alamat</p>
                                <p class="mt-0.5 text-sm text-white/80 leading-relaxed">{{ school.address }}</p>
                            </div>
                        </div>

                        <div v-if="school?.phone" v-reveal="{ delay: 240 }" class="flex items-start gap-4 rounded-2xl bg-white/5 p-5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/20">
                                <svg class="size-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-green-400">Telepon</p>
                                <a :href="`tel:${school.phone}`" class="mt-0.5 text-sm font-semibold text-white hover:text-amber-400 transition-colors">{{ school.phone }}</a>
                            </div>
                        </div>

                        <div v-if="school?.email" v-reveal="{ delay: 320 }" class="flex items-start gap-4 rounded-2xl bg-white/5 p-5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20">
                                <svg class="size-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-green-400">Email</p>
                                <a :href="`mailto:${school.email}`" class="mt-0.5 text-sm font-semibold text-white hover:text-amber-400 transition-colors">{{ school.email }}</a>
                            </div>
                        </div>

                        <div v-if="school?.website" v-reveal="{ delay: 400 }" class="flex items-start gap-4 rounded-2xl bg-white/5 p-5 sm:col-span-2">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/20">
                                <svg class="size-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-green-400">Website</p>
                                <a :href="school.website" target="_blank" rel="noopener noreferrer" class="mt-0.5 text-sm font-semibold text-white hover:text-amber-400 transition-colors">{{ school.website }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- Login card -->
                    <div v-reveal="{ from: 'right', delay: 200 }">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center backdrop-blur-sm">
                            <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-green-700">
                                <svg class="size-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            </div>
                            <h3 class="text-base font-bold text-white">Portal Akademik</h3>
                            <p class="mt-2 text-xs leading-relaxed text-white/50">Akses nilai, tagihan, surat, dan laporan sekolah.</p>
                            <Link
                                v-if="!isLoggedIn && canLogin" :href="route('login')"
                                class="mt-5 block w-full rounded-xl bg-amber-400 py-3 text-sm font-bold text-green-900 shadow transition-colors hover:bg-amber-300"
                            >
                                Masuk ke Sistem
                            </Link>
                            <Link
                                v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute"
                                class="mt-5 block w-full rounded-xl bg-amber-400 py-3 text-sm font-bold text-green-900 shadow transition-colors hover:bg-amber-300"
                            >
                                Buka Dashboard
                            </Link>
                            <p class="mt-3 text-xs text-white/30">Butuh akun? Hubungi operator sekolah.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-green-950 py-6">
            <div class="mx-auto max-w-6xl px-6 text-center">
                <p class="text-xs text-green-500">
                    &copy; {{ new Date().getFullYear() }} {{ school?.name ?? 'Sistem Manajemen Sekolah' }}
                    <span v-if="school?.npsn"> · NPSN {{ school.npsn }}</span>
                    · Hak cipta dilindungi undang-undang.
                </p>
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
