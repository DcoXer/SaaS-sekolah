<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    school:         { type: Object,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
    activePage:     { type: String,  default: '' },
    ppdbActive:     { type: Boolean, default: false },
});

const scrolled    = ref(false);
const mobileOpen  = ref(false);
const onScroll    = () => { scrolled.value = window.scrollY > 60; };

onMounted(() => window.addEventListener('scroll', onScroll));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

const navClass    = (page) => [
    'rounded-lg px-3.5 py-2 text-sm font-medium transition-colors',
    props.activePage === page
        ? 'bg-green-700 text-white'
        : 'text-slate-600 hover:bg-green-50 hover:text-green-700',
];
const mobileNavClass = (page) => [
    'rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-colors',
    props.activePage === page
        ? 'bg-green-700 text-white'
        : 'text-slate-700 hover:bg-green-50 hover:text-green-700',
];
</script>

<template>
    <!-- Top info bar -->
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

    <!-- Navbar -->
    <header class="sticky top-0 z-50 border-b transition-shadow duration-300"
        :class="scrolled ? 'border-slate-200 bg-white shadow-md' : 'border-slate-100 bg-white'">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-6">

            <!-- Logo + Nama -->
            <Link :href="route('welcome')" class="flex shrink-0 items-center gap-3">
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
            </Link>

            <!-- Desktop Nav -->
            <nav class="hidden items-center gap-1 md:flex">
                <Link :href="route('welcome')" :class="navClass('')">Beranda</Link>
                <Link :href="route('tentang')" :class="navClass('tentang')">Tentang</Link>
                <Link :href="route('ekskul')"  :class="navClass('ekskul')">Ekskul</Link>
                <Link :href="route('galeri')"  :class="navClass('galeri')">Galeri</Link>
                <a href="/#kontak" :class="navClass('kontak')">Kontak</a>
                <Link v-if="ppdbActive" :href="route('ppdb.index')"
                    class="ml-1 inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-sm font-semibold text-white transition-colors hover:bg-amber-400"
                    :class="activePage === 'ppdb' ? 'bg-amber-600' : ''">
                    PPDB
                </Link>
            </nav>

            <!-- CTA + Hamburger -->
            <div class="flex items-center gap-2">
                <Link v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute"
                    class="hidden items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-600 sm:inline-flex">
                    Dashboard
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
                <Link v-else-if="canLogin" :href="route('login')"
                    class="hidden items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-600 sm:inline-flex">
                    Masuk
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
                <button @click="mobileOpen = !mobileOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden">
                    <svg v-if="!mobileOpen" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    <svg v-else class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div v-if="mobileOpen" class="border-t border-slate-100 bg-white px-6 py-3 md:hidden">
            <div class="flex flex-col gap-1">
                <Link :href="route('welcome')" :class="mobileNavClass('')">Beranda</Link>
                <Link :href="route('tentang')" :class="mobileNavClass('tentang')">Tentang</Link>
                <Link :href="route('ekskul')"  :class="mobileNavClass('ekskul')">Ekskul</Link>
                <Link :href="route('galeri')"  :class="mobileNavClass('galeri')">Galeri</Link>
                <a href="/#kontak" :class="mobileNavClass('kontak')">Kontak</a>
                <Link v-if="ppdbActive" :href="route('ppdb.index')"
                    class="rounded-lg bg-amber-500 px-3 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-amber-400">
                    Daftar PPDB Sekarang
                </Link>
                <div class="mt-2 border-t border-slate-100 pt-2">
                    <Link v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute"
                        class="block rounded-lg bg-green-700 px-3 py-2.5 text-center text-sm font-semibold text-white">
                        Dashboard
                    </Link>
                    <Link v-else-if="canLogin" :href="route('login')"
                        class="block rounded-lg bg-green-700 px-3 py-2.5 text-center text-sm font-semibold text-white">
                        Masuk
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>
