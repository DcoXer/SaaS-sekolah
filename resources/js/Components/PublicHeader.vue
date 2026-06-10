<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import PwaInstall from '@/Components/PwaInstall.vue';

const props = defineProps({
    school:         { type: Object,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
    activePage:     { type: String,  default: '' },
    ppdbActive:     { type: Boolean, default: false },
});

const scrolled   = ref(false);
const mobileOpen = ref(false);
const onScroll   = () => { scrolled.value = window.scrollY > 60; };

onMounted(() => window.addEventListener('scroll', onScroll));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

const close = () => { mobileOpen.value = false; };

const navClass = (page) => [
    'rounded-lg px-3.5 py-2 text-sm font-medium transition-colors',
    props.activePage === page
        ? 'bg-primary-700 text-white'
        : 'text-slate-600 hover:bg-primary-50 hover:text-primary-700',
];
const mobileNavClass = (page) => [
    'rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-colors',
    props.activePage === page
        ? 'bg-primary-700 text-white'
        : 'text-slate-700 hover:bg-primary-50 hover:text-primary-700',
];
</script>

<template>
    <!-- Top info bar -->
    <div class="hidden bg-primary-900 md:block">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-2 text-xs text-primary-200">
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
            <span v-if="school?.npsn" class="text-primary-300">NPSN: {{ school.npsn }}</span>
        </div>
    </div>

    <!-- Navbar -->
    <header class="sticky top-0 z-50 border-b transition-shadow duration-300"
        :class="scrolled ? 'border-slate-200 bg-white shadow-md' : 'border-slate-100 bg-white'">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">

            <!-- Logo + Nama -->
            <Link :href="route('welcome')" class="flex min-w-0 shrink items-center gap-3">
                <img v-if="school?.logo" :src="school.logo" alt="Logo" class="size-10 shrink-0 rounded-lg object-contain" />
                <div v-else class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-700 shadow-sm">
                    <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <div class="min-w-0 text-left leading-tight">
                    <p class="truncate text-sm font-bold leading-none text-slate-800">{{ school?.name ?? 'Nama Sekolah' }}</p>
                    <p v-if="school?.tagline" class="mt-0.5 truncate text-[11px] font-medium text-primary-700">{{ school.tagline }}</p>
                </div>
            </Link>

            <!-- Desktop Nav -->
            <nav class="hidden items-center gap-1 md:flex">
                <Link :href="route('welcome')" :class="navClass('')">Beranda</Link>
                <Link :href="route('tentang')" :class="navClass('tentang')">Tentang</Link>
                <Link :href="route('ekskul')"  :class="navClass('ekskul')">Ekskul</Link>
                <Link :href="route('galeri')"  :class="navClass('galeri')">Galeri</Link>
                <Link :href="route('berita.index')" :class="navClass('berita')">Berita</Link>
                <a href="/#kontak" :class="navClass('kontak')">Kontak</a>
                <Link v-if="ppdbActive" :href="route('ppdb.index')"
                    class="ml-1 inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-2 text-sm font-semibold text-white transition-colors hover:bg-amber-400"
                    :class="activePage === 'ppdb' ? 'bg-amber-600' : ''">
                    PPDB
                </Link>
            </nav>

            <!-- CTA + Hamburger -->
            <div class="flex shrink-0 items-center gap-2">
                <Link v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute"
                    class="hidden items-center gap-1.5 rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-600 sm:inline-flex">
                    Dashboard
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
                <Link v-else-if="canLogin" :href="route('login')"
                    class="hidden items-center gap-1.5 rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-600 sm:inline-flex">
                    Masuk
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
                <button @click="mobileOpen = !mobileOpen"
                    class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 md:hidden"
                    :aria-expanded="mobileOpen">
                    <!-- Hamburger → X dengan animasi rotate -->
                    <svg class="size-5 transition-transform duration-300" :class="mobileOpen ? 'rotate-90' : ''"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu dengan animasi slide-down -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-3"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-3"
        >
            <div v-if="mobileOpen" class="border-t border-slate-100 bg-white px-4 pb-4 pt-2 md:hidden">
                <div class="flex flex-col gap-0.5">
                    <Link :href="route('welcome')"      :class="mobileNavClass('')"       @click="close">Beranda</Link>
                    <Link :href="route('tentang')"      :class="mobileNavClass('tentang')" @click="close">Tentang</Link>
                    <Link :href="route('ekskul')"       :class="mobileNavClass('ekskul')"  @click="close">Ekskul</Link>
                    <Link :href="route('galeri')"       :class="mobileNavClass('galeri')"  @click="close">Galeri</Link>
                    <Link :href="route('berita.index')" :class="mobileNavClass('berita')"  @click="close">Berita</Link>
                    <a href="/#kontak"                  :class="mobileNavClass('kontak')"  @click="close">Kontak</a>
                    <Link v-if="ppdbActive" :href="route('ppdb.index')" @click="close"
                        class="mt-1 rounded-lg bg-amber-500 px-3 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-amber-400">
                        Daftar PPDB Sekarang
                    </Link>
                    <div class="mt-2 border-t border-slate-100 pt-2">
                        <Link v-if="isLoggedIn && dashboardRoute" :href="dashboardRoute" @click="close"
                            class="flex items-center justify-center gap-1.5 rounded-lg bg-primary-700 px-3 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-600">
                            Dashboard
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                        <Link v-else-if="canLogin" :href="route('login')" @click="close"
                            class="flex items-center justify-center gap-1.5 rounded-lg bg-primary-700 px-3 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-600">
                            Masuk
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>
    </header>

    <PwaInstall />
</template>
