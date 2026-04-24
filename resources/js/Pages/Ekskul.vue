<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    school:           { type: Object,  default: null },
    extracurriculars: { type: Array,   default: () => [] },
    canLogin:         { type: Boolean, default: true },
    isLoggedIn:       { type: Boolean, default: false },
    dashboardRoute:   { type: String,  default: null },
    ppdbActive:       { type: Boolean, default: false },
});

const search   = ref('');
const selected = ref(null);

const filtered = computed(() => {
    if (!search.value.trim()) return props.extracurriculars;
    const q = search.value.toLowerCase();
    return props.extracurriculars.filter(e =>
        e.name.toLowerCase().includes(q) || e.description?.toLowerCase().includes(q)
    );
});

const featured = computed(() => filtered.value[0] ?? null);
const rest     = computed(() => filtered.value.slice(1));

const colors = [
    { bg: 'bg-green-100',  text: 'text-green-700',  ring: 'ring-green-200' },
    { bg: 'bg-amber-100',  text: 'text-amber-700',  ring: 'ring-amber-200' },
    { bg: 'bg-sky-100',    text: 'text-sky-700',    ring: 'ring-sky-200' },
    { bg: 'bg-violet-100', text: 'text-violet-700', ring: 'ring-violet-200' },
    { bg: 'bg-rose-100',   text: 'text-rose-700',   ring: 'ring-rose-200' },
    { bg: 'bg-teal-100',   text: 'text-teal-700',   ring: 'ring-teal-200' },
    { bg: 'bg-orange-100', text: 'text-orange-700', ring: 'ring-orange-200' },
    { bg: 'bg-indigo-100', text: 'text-indigo-700', ring: 'ring-indigo-200' },
];
const color = (i) => colors[i % colors.length];
</script>

<template>
    <Head :title="`Ekstrakulikuler — ${school?.name ?? 'Sekolah'}`" />

    <div class="min-h-screen overflow-x-hidden bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader :school="school" :can-login="canLogin" :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute" active-page="ekskul" :ppdb-active="ppdbActive"/>

        <!-- ── Hero ────────────────────────────────────────────────────── -->
        <div class="relative overflow-hidden bg-gradient-to-br from-green-900 via-green-800 to-green-700 py-20">
            <div class="absolute -right-24 -top-24 size-80 rounded-full bg-white/5"/>
            <div class="absolute -bottom-16 -left-16 size-64 rounded-full bg-white/5"/>
            <div class="relative mx-auto max-w-6xl px-6">
                <div v-reveal class="text-center">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-400/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-amber-300">
                        <span class="size-1.5 animate-pulse rounded-full bg-amber-400"/>
                        Kegiatan Siswa
                    </span>
                    <h1 class="mt-4 text-4xl font-extrabold text-white lg:text-5xl">Ekstrakulikuler</h1>
                    <p class="mt-3 text-base text-green-200">
                        {{ extracurriculars.length }} kegiatan tersedia untuk mengembangkan potensi siswa
                    </p>
                </div>

                <!-- Search -->
                <div v-reveal="{ delay: 150 }" class="mx-auto mt-8 max-w-md">
                    <div class="relative">
                        <!-- <svg class="pointer-events-none absolute left-4 top-1/2 size-4.5 -translate-y-1/2 text-white/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg> -->
                        <input v-model="search" type="search" placeholder="Cari ekstrakulikuler..."
                            class="w-full rounded-2xl border border-white/20 bg-white/10 py-3.5 pl-11 pr-4 text-sm text-white placeholder-white/40 outline-none backdrop-blur-sm focus:border-white/40 focus:bg-white/15 transition-colors"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Content ──────────────────────────────────────────────────── -->
        <div class="mx-auto max-w-6xl px-6 py-14">

            <!-- Empty -->
            <div v-if="!filtered.length" class="rounded-2xl border-2 border-dashed border-slate-200 py-24 text-center">
                <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
                <p class="font-semibold text-slate-500">{{ search ? 'Tidak ditemukan' : 'Belum ada ekskul' }}</p>
                <button v-if="search" @click="search = ''" class="mt-2 text-sm font-semibold text-green-600 hover:underline">Hapus pencarian</button>
            </div>

            <template v-else>
                <!-- Featured card -->
                <div v-if="featured" v-reveal
                    class="group mb-10 cursor-pointer overflow-hidden rounded-3xl bg-white shadow-lg ring-1 ring-slate-200 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 lg:flex"
                    @click="selected = featured">
                    <!-- Image -->
                    <div class="relative h-60 overflow-hidden lg:h-auto lg:w-1/2 xl:w-2/5">
                        <img v-if="featured.image" :src="featured.image" :alt="featured.name"
                            class="size-full object-cover transition-transform duration-500 group-hover:scale-105"/>
                        <div v-else :class="[color(0).bg, color(0).text]"
                            class="flex size-full min-h-60 items-center justify-center text-8xl font-black opacity-20 select-none">
                            {{ featured.name[0] }}
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent to-black/20 lg:bg-gradient-to-l"/>
                    </div>
                    <!-- Info -->
                    <div class="flex flex-col justify-center p-8 lg:flex-1">
                        <span class="mb-3 inline-flex w-fit items-center rounded-full bg-green-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-green-700">
                            Unggulan
                        </span>
                        <h2 class="text-2xl font-extrabold text-slate-900 lg:text-3xl">{{ featured.name }}</h2>
                        <p v-if="featured.description" class="mt-3 leading-relaxed text-slate-500">{{ featured.description }}</p>
                        <div class="mt-6 flex items-center gap-2 text-sm font-semibold text-green-700">
                            Lihat Detail
                            <svg class="size-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Grid -->
                <div v-if="rest.length" class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">
                    <div v-for="(ekskul, i) in rest" :key="ekskul.id"
                        v-reveal="{ delay: (i % 4) * 80 }"
                        class="group cursor-pointer overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl"
                        @click="selected = ekskul">
                        <!-- Image / Placeholder -->
                        <div class="relative aspect-[4/3] overflow-hidden">
                            <img v-if="ekskul.image" :src="ekskul.image" :alt="ekskul.name"
                                class="size-full object-cover transition-transform duration-500 group-hover:scale-110"/>
                            <div v-else :class="[color(i+1).bg]"
                                class="flex size-full items-center justify-center text-5xl font-black opacity-20 select-none transition-opacity duration-300 group-hover:opacity-30">
                                {{ ekskul.name[0] }}
                            </div>
                            <!-- Hover overlay -->
                            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 p-4">
                                <span class="text-sm font-bold text-white">Lihat Detail →</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-slate-800">{{ ekskul.name }}</h3>
                            <p v-if="ekskul.description" class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-400">{{ ekskul.description }}</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-slate-50 py-6">
            <p class="text-center text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name }}</p>
        </footer>

        <!-- Detail Modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150">
                <div v-if="selected" class="fixed inset-0 z-[100] flex items-end justify-center bg-black/60 p-4 sm:items-center" @click.self="selected = null">
                    <Transition enter-from-class="opacity-0 translate-y-8 scale-95" enter-active-class="transition-all duration-300"
                        leave-to-class="opacity-0 translate-y-4 scale-95" leave-active-class="transition-all duration-200">
                        <div v-if="selected" class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
                            <div class="relative aspect-video overflow-hidden bg-slate-100">
                                <img v-if="selected.image" :src="selected.image" :alt="selected.name"
                                    class="size-full object-cover"/>
                                <div v-else :class="[color(0).bg, color(0).text]"
                                    class="flex size-full items-center justify-center text-8xl font-black opacity-20 select-none"/>
                                <button @click="selected = null"
                                    class="absolute right-3 top-3 flex size-9 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm transition-colors hover:bg-black/60">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="p-6">
                                <h2 class="text-xl font-extrabold text-slate-900">{{ selected.name }}</h2>
                                <p v-if="selected.description" class="mt-3 leading-relaxed text-slate-600">{{ selected.description }}</p>
                                <p v-else class="mt-3 text-sm italic text-slate-400">Deskripsi belum tersedia.</p>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
