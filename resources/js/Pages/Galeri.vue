<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import JsonLd from '@/Components/JsonLd.vue';

const props = defineProps({
    school:         { type: Object,  default: null },
    galleries:      { type: Array,   default: () => [] },
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

const activeTab = ref('all');
const lightbox  = ref(null);
const lbIndex   = ref(0);

const tabs = [
    { value: 'all',   label: 'Semua',  count: computed(() => props.galleries.length) },
    { value: 'photo', label: 'Foto',   count: computed(() => props.galleries.filter(g => g.type === 'photo').length) },
    { value: 'video', label: 'Video',  count: computed(() => props.galleries.filter(g => g.type === 'video').length) },
];

const filtered = computed(() => {
    if (activeTab.value === 'all') return props.galleries;
    return props.galleries.filter(g => g.type === activeTab.value);
});

const ytId    = (url) => { if (!url) return null; const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([^&\n?#]+)/); return m?.[1] ?? null; };
const ytThumb = (url) => { const id = ytId(url); return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : null; };
const ytEmbed = (url) => { const id = ytId(url); return id ? `https://www.youtube.com/embed/${id}` : null; };

const openLightbox = (item) => {
    lbIndex.value = filtered.value.findIndex(g => g.id === item.id);
    lightbox.value = item;
};

const lbPrev = () => {
    const arr = filtered.value;
    lbIndex.value = (lbIndex.value - 1 + arr.length) % arr.length;
    lightbox.value = arr[lbIndex.value];
};
const lbNext = () => {
    const arr = filtered.value;
    lbIndex.value = (lbIndex.value + 1) % arr.length;
    lightbox.value = arr[lbIndex.value];
};

const baseUrl = usePage().props.ziggy?.url ?? '';
const jsonLd = computed(() => ({
    '@context':  'https://schema.org',
    '@type':     'CollectionPage',
    '@id':       `${baseUrl}/galeri#webpage`,
    'name':      `Galeri — ${props.school?.name ?? ''}`,
    'description': `Galeri foto dan video kegiatan ${props.school?.name ?? ''}.`,
    'url':       `${baseUrl}/galeri`,
    'isPartOf':  { '@id': `${baseUrl}/#website` },
    'about':     { '@id': `${baseUrl}/#school` },
    'breadcrumb': {
        '@type': 'BreadcrumbList',
        'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Beranda', 'item': baseUrl },
            { '@type': 'ListItem', 'position': 2, 'name': 'Galeri',  'item': `${baseUrl}/galeri` },
        ],
    },
}));
</script>

<template>
    <Head :title="`Galeri — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="`Galeri foto dan video kegiatan ${school?.name ?? 'sekolah kami'}. Lihat dokumentasi berbagai kegiatan dan momen berharga.`">
        <meta head-key="og:title" property="og:title" :content="`Galeri — ${school?.name ?? ''}`">
        <meta head-key="og:description" property="og:description" :content="`Koleksi foto dan video kegiatan ${school?.name ?? ''}.`">
        <meta head-key="og:type" property="og:type" content="website">
        <meta v-if="school?.logo" head-key="og:image" property="og:image" :content="school.logo">
        <meta v-if="school?.logo" head-key="twitter:image" name="twitter:image" :content="school.logo">
        <meta head-key="twitter:title" name="twitter:title" :content="`Galeri — ${school?.name ?? ''}`">
        <meta head-key="twitter:description" name="twitter:description" :content="`Galeri foto dan video kegiatan ${school?.name ?? ''}.`">
    </Head>
    <JsonLd :data="jsonLd" />

    <div class="min-h-screen overflow-x-hidden bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader
            :school="school"
            :can-login="canLogin"
            :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute"
            :ppdb-active="ppdbActive"
            active-page="galeri"
        />

        <!-- ── Hero ────────────────────────────────────────────────────── -->
        <div
            class="relative overflow-hidden py-20 transition-all duration-1000"
            :class="heroBgUrl ? '' : 'bg-gradient-to-br from-green-900 via-green-800 to-green-700'"
            :style="heroBgUrl ? `background-image:url('${heroBgUrl}');background-size:cover;background-position:center` : ''"
        >
            <!-- Overlay gelap saat ada foto -->
            <div v-if="heroBgUrl" class="absolute inset-0 bg-black/55"/>
            <!-- Dekorasi (hanya saat tidak ada foto) -->
            <div v-if="!heroBgUrl" class="absolute -right-24 -top-24 size-80 rounded-full bg-white/5"/>
            <div v-if="!heroBgUrl" class="absolute -bottom-16 -left-16 size-64 rounded-full bg-white/5"/>
            <!-- Grid dekorasi -->
            <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(0deg,transparent,transparent 40px,white 40px,white 41px),repeating-linear-gradient(90deg,transparent,transparent 40px,white 40px,white 41px)"/>

            <div class="relative mx-auto max-w-6xl px-6">
                <div v-reveal class="text-center">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-400/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-amber-300">
                        <span class="size-1.5 animate-pulse rounded-full bg-amber-400"/>
                        Dokumentasi Sekolah
                    </span>
                    <h1 class="mt-4 text-4xl font-extrabold text-white lg:text-5xl">Galeri Sekolah</h1>
                    <p class="mt-3 text-base text-green-200">
                        Kumpulan foto dan video kegiatan <span class="font-semibold text-white">{{ school?.name }}</span>
                    </p>
                </div>

                <!-- Stats pill -->
                <div v-reveal="{ delay: 120 }" class="mx-auto mt-8 flex w-fit flex-wrap justify-center gap-3">
                    <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs text-white/80 backdrop-blur-sm">
                        <svg class="size-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                        <span><strong class="text-white">{{ galleries.filter(g => g.type === 'photo').length }}</strong> Foto</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs text-white/80 backdrop-blur-sm">
                        <svg class="size-3.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                        <span><strong class="text-white">{{ galleries.filter(g => g.type === 'video').length }}</strong> Video</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Content ──────────────────────────────────────────────────── -->
        <div class="mx-auto max-w-6xl px-6 py-14">

            <!-- Tab filter -->
            <div v-reveal class="mb-10 flex items-center gap-2">
                <button
                    v-for="tab in tabs" :key="tab.value"
                    @click="activeTab = tab.value"
                    class="group relative rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200"
                    :class="activeTab === tab.value
                        ? 'bg-green-700 text-white shadow-md shadow-green-200'
                        : 'border border-slate-200 bg-white text-slate-500 hover:border-green-200 hover:bg-green-50 hover:text-green-700'"
                >
                    {{ tab.label }}
                    <span class="ml-1.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold"
                        :class="activeTab === tab.value ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-green-100 group-hover:text-green-600'">
                        {{ tab.count.value }}
                    </span>
                </button>
            </div>

            <!-- Grid -->
            <div v-if="filtered.length" class="columns-2 gap-5 sm:columns-3 lg:columns-4">
                <div
                    v-for="(item, i) in filtered" :key="item.id"
                    v-reveal="{ delay: (i % 4) * 60 }"
                    class="group mb-5 cursor-pointer break-inside-avoid overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:ring-green-200"
                    @click="openLightbox(item)"
                >
                    <div class="relative overflow-hidden">
                        <!-- Photo -->
                        <img
                            v-if="item.type === 'photo' && item.file_url"
                            :src="item.file_url"
                            :alt="item.caption ?? ''"
                            class="w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <!-- Video -->
                        <div v-else-if="item.type === 'video'" class="relative">
                            <img
                                v-if="ytThumb(item.video_url)"
                                :src="ytThumb(item.video_url)"
                                class="w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div class="absolute inset-0 flex items-center justify-center bg-black/20 transition-colors duration-300 group-hover:bg-black/40">
                                <div class="flex size-11 items-center justify-center rounded-full bg-white/95 shadow-xl transition-transform duration-200 group-hover:scale-110">
                                    <svg class="size-5 translate-x-0.5 text-green-700" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <!-- Video badge -->
                            <span class="absolute left-2.5 top-2.5 flex items-center gap-1 rounded-full bg-rose-600/90 px-2 py-1 text-[10px] font-bold text-white backdrop-blur-sm">
                                <svg class="size-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                VIDEO
                            </span>
                        </div>
                        <!-- Hover overlay -->
                        <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 p-3">
                            <span class="text-xs font-semibold text-white">Lihat →</span>
                        </div>
                    </div>
                    <div v-if="item.caption" class="px-3 py-2.5">
                        <p class="text-xs font-medium leading-relaxed text-slate-500">{{ item.caption }}</p>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-2xl border-2 border-dashed border-slate-200 py-24 text-center">
                <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                </svg>
                <p class="font-semibold text-slate-500">Belum ada item galeri</p>
                <button v-if="activeTab !== 'all'" @click="activeTab = 'all'" class="mt-2 text-sm font-semibold text-green-600 hover:underline">Tampilkan semua</button>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-slate-50 py-6">
            <div class="mx-auto max-w-6xl px-6 text-center">
                <p class="text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name ?? 'Sekolah' }}</p>
            </div>
        </footer>

        <!-- ── Lightbox ──────────────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150">
                <div v-if="lightbox"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 p-4"
                    @click.self="lightbox = null">

                    <!-- Close -->
                    <button @click="lightbox = null"
                        class="absolute right-4 top-4 z-10 flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <!-- Prev/Next (only when multiple items) -->
                    <template v-if="filtered.length > 1">
                        <button @click="lbPrev"
                            class="absolute left-3 top-1/2 z-10 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/25 sm:left-5">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                        </button>
                        <button @click="lbNext"
                            class="absolute right-3 top-1/2 z-10 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/25 sm:right-5">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </template>

                    <!-- Counter -->
                    <div v-if="filtered.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-xs text-white/60 backdrop-blur-sm">
                        {{ lbIndex + 1 }} / {{ filtered.length }}
                    </div>

                    <!-- Content -->
                    <Transition enter-from-class="opacity-0 scale-95" enter-active-class="transition-all duration-200"
                        leave-to-class="opacity-0 scale-95" leave-active-class="transition-all duration-150" mode="out-in">
                        <div :key="lightbox.id" class="flex max-h-[88vh] flex-col items-center">
                            <img
                                v-if="lightbox.type === 'photo'"
                                :src="lightbox.file_url"
                                :alt="lightbox.caption ?? ''"
                                class="max-h-[82vh] max-w-full rounded-2xl object-contain shadow-2xl"
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
                            </div>
                            <p v-if="lightbox.caption" class="mt-3 max-w-lg text-center text-sm text-white/50">{{ lightbox.caption }}</p>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
