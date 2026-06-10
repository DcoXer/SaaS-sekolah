<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import JsonLd from '@/Components/JsonLd.vue';

const props = defineProps({
    extracurricular: { type: Object,  required: true },
    school:          { type: Object,  default: null },
    canLogin:        { type: Boolean, default: true },
    isLoggedIn:      { type: Boolean, default: false },
    dashboardRoute:  { type: String,  default: null },
    ppdbActive:      { type: Boolean, default: false },
});

const levelOptions = {
    kecamatan:     { label: 'Kecamatan',     cls: 'bg-slate-100 text-slate-600' },
    kabupaten:     { label: 'Kabupaten',     cls: 'bg-blue-100 text-blue-700' },
    kota:          { label: 'Kota',          cls: 'bg-blue-100 text-blue-700' },
    provinsi:      { label: 'Provinsi',      cls: 'bg-violet-100 text-violet-700' },
    nasional:      { label: 'Nasional',      cls: 'bg-amber-100 text-amber-700' },
    internasional: { label: 'Internasional', cls: 'bg-primary-100 text-primary-700' },
};

const levelLabel = (val) => levelOptions[val]?.label ?? val;
const levelCls   = (val) => levelOptions[val]?.cls   ?? 'bg-slate-100 text-slate-600';

// ── Rank helpers ──────────────────────────────────────────────────────────────
const rankTier = (rank) => {
    if (!rank) return 'other';
    const r = rank.toLowerCase();
    if (/juara\s*(1|i\b|pertama)|emas/.test(r)) return 'gold';
    if (/juara\s*(2|ii\b|kedua)|perak/.test(r))  return 'silver';
    if (/juara\s*(3|iii\b|ketiga)|perunggu/.test(r)) return 'bronze';
    return 'other';
};

const rankCardCls = (rank) => ({
    gold:   'border-amber-200 from-amber-50',
    silver: 'border-slate-200 from-slate-100',
    bronze: 'border-orange-200 from-orange-50',
    other:  'border-sky-100 from-sky-50',
}[rankTier(rank)]);

const rankBadgeCls = (rank) => ({
    gold:   'bg-amber-100 text-amber-700 ring-1 ring-amber-300',
    silver: 'bg-slate-100 text-slate-600 ring-1 ring-slate-300',
    bronze: 'bg-orange-100 text-orange-700 ring-1 ring-orange-300',
    other:  'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
}[rankTier(rank)]);

const medalIconCls = (rank) => ({
    gold:   'bg-amber-100 text-amber-500',
    silver: 'bg-slate-100 text-slate-500',
    bronze: 'bg-orange-100 text-orange-500',
    other:  'bg-sky-100 text-sky-500',
}[rankTier(rank)]);

// ── Slideshow ─────────────────────────────────────────────────────────────────
const slides = computed(() => props.extracurricular.photos ?? []);
const hasSlides = computed(() => slides.value.length > 0);
const currentSlide = ref(0);
let timer = null;

const goTo = (i) => { currentSlide.value = i; };
const next = () => { currentSlide.value = (currentSlide.value + 1) % slides.value.length; };

const startTimer = () => { if (slides.value.length > 1) timer = setInterval(next, 4500); };
const stopTimer  = () => { clearInterval(timer); };

onMounted(startTimer);
onUnmounted(stopTimer);

// Group achievements by year (desc)
const achievementsByYear = computed(() => {
    const map = new Map();
    for (const a of props.extracurricular.achievements) {
        if (!map.has(a.year)) map.set(a.year, []);
        map.get(a.year).push(a);
    }
    return [...map.entries()].sort((a, b) => b[0] - a[0]);
});

const baseUrl = usePage().props.ziggy?.url ?? '';
const jsonLd = computed(() => ({
    '@context':   'https://schema.org',
    '@type':      'SportsOrganization',
    'name':       props.extracurricular.name,
    'description': props.extracurricular.description ?? undefined,
    'url':        `${baseUrl}/ekskul/${props.extracurricular.id}`,
    'image':      props.extracurricular.photos?.[0]?.url ?? props.extracurricular.image ?? undefined,
    'parentOrganization': props.school ? { '@type': 'School', 'name': props.school.name } : undefined,
    'breadcrumb': {
        '@type': 'BreadcrumbList',
        'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Beranda',         'item': baseUrl },
            { '@type': 'ListItem', 'position': 2, 'name': 'Ekstrakulikuler', 'item': `${baseUrl}/ekskul` },
            { '@type': 'ListItem', 'position': 3, 'name': props.extracurricular.name },
        ],
    },
}));
</script>

<template>
    <Head :title="`${extracurricular.name} — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="extracurricular.description ?? `Ekstrakurikuler ${extracurricular.name} di ${school?.name ?? 'sekolah kami'}.`">
        <meta head-key="og:title" property="og:title" :content="`${extracurricular.name} — ${school?.name ?? ''}`">
        <meta head-key="og:description" property="og:description" :content="extracurricular.description ?? `Ekstrakurikuler ${extracurricular.name}.`">
        <meta head-key="og:type" property="og:type" content="website">
        <meta v-if="extracurricular.image" head-key="og:image" property="og:image" :content="extracurricular.image">
        <meta v-if="extracurricular.image" head-key="twitter:image" name="twitter:image" :content="extracurricular.image">
        <meta head-key="twitter:title" name="twitter:title" :content="`${extracurricular.name} — ${school?.name ?? ''}`">
    </Head>
    <JsonLd :data="jsonLd"/>

    <div class="min-h-screen overflow-x-hidden bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader :school="school" :can-login="canLogin" :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute" active-page="ekskul" :ppdb-active="ppdbActive"/>

        <!-- ── Hero Slideshow ──────────────────────────────────────────────── -->
        <div class="relative h-72 overflow-hidden sm:h-[26rem] bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700">

            <!-- Slides -->
            <template v-if="hasSlides">
                <transition-group name="slide-fade" tag="div">
                    <img
                        v-for="(photo, i) in slides"
                        v-show="currentSlide === i"
                        :key="photo.id"
                        :src="photo.url"
                        :alt="extracurricular.name"
                        class="absolute inset-0 size-full object-cover"
                    />
                </transition-group>
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-black/10"/>
            </template>
            <template v-else>
                <div class="absolute inset-0 overflow-hidden rounded-none pointer-events-none">
                    <div class="absolute -right-24 -top-24 size-80 rounded-full bg-white/5"/>
                    <div class="absolute -bottom-16 -left-16 size-64 rounded-full bg-white/5"/>
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"/>
            </template>

            <!-- Back button -->
            <div class="absolute inset-x-0 top-0 mx-auto max-w-5xl px-6 pt-5">
                <Link :href="route('ekskul')"
                    class="inline-flex items-center gap-1.5 rounded-full bg-black/30 px-3.5 py-1.5 text-xs font-semibold text-white/90 backdrop-blur-sm transition hover:bg-black/50">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali
                </Link>
            </div>

            <!-- Title area -->
            <div class="absolute inset-x-0 bottom-0 mx-auto max-w-5xl px-6 pb-8">
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-400/40 bg-amber-400/10 px-3.5 py-1 text-xs font-semibold tracking-wide text-amber-300">
                    <span class="size-1.5 rounded-full bg-amber-400"/>
                    Ekstrakurikuler
                </span>
                <h1 class="mt-2 text-3xl font-extrabold text-white drop-shadow sm:text-4xl lg:text-5xl">
                    {{ extracurricular.name }}
                </h1>
            </div>

            <!-- Dot indicators -->
            <div v-if="slides.length > 1" class="absolute bottom-4 right-6 flex items-center gap-1.5">
                <button
                    v-for="(_, i) in slides" :key="i"
                    @click="goTo(i); stopTimer(); startTimer()"
                    class="size-2 rounded-full transition-all duration-300"
                    :class="currentSlide === i ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/70'"
                />
            </div>
        </div>

        <!-- ── Info Strip ──────────────────────────────────────────────────── -->
        <div v-if="extracurricular.coach || extracurricular.schedule || extracurricular.achievements.length"
            class="border-b border-slate-100 bg-white shadow-sm">
            <div class="mx-auto max-w-5xl px-6 py-4">
                <div class="flex flex-wrap items-center justify-between gap-y-3">

                    <div v-if="extracurricular.coach" class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-50">
                            <svg class="size-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pelatih</p>
                            <p class="text-sm font-bold text-slate-800">{{ extracurricular.coach }}</p>
                        </div>
                    </div>

                    <div v-if="extracurricular.schedule" class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-sky-50">
                            <svg class="size-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Jadwal Latihan</p>
                            <p class="text-sm font-bold text-slate-800">{{ extracurricular.schedule }}</p>
                        </div>
                    </div>

                    <div v-if="extracurricular.achievements.length" class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-50">
                            <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Prestasi</p>
                            <p class="text-sm font-bold text-amber-700">{{ extracurricular.achievements.length }} Penghargaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Main Content ────────────────────────────────────────────────── -->
        <div class="mx-auto max-w-5xl px-6 py-14 space-y-16">

            <!-- Description -->
            <section v-if="extracurricular.description">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-600">
                        <svg class="size-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-800">Tentang Ekskul</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent"/>
                </div>
                <div class="rounded-2xl border border-primary-100 bg-gradient-to-br from-primary-50 to-white p-6 sm:p-8">
                    <p class="text-base leading-relaxed text-slate-700">{{ extracurricular.description }}</p>
                </div>
            </section>

            <!-- Achievements -->
            <section v-if="extracurricular.achievements.length">
                <div class="mb-8 flex items-center gap-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-500">
                        <svg class="size-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-800">Prestasi</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent"/>
                </div>

                <div class="space-y-10">
                    <div v-for="[year, list] in achievementsByYear" :key="year">

                        <!-- Year label -->
                        <div class="mb-5 flex items-center gap-4">
                            <div class="flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 shadow">
                                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75"/>
                                </svg>
                                <span class="text-sm font-extrabold tracking-wider text-white">{{ year }}</span>
                            </div>
                            <div class="h-px flex-1 bg-slate-100"/>
                        </div>

                        <!-- Achievement cards grid -->
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="a in list" :key="a.id"
                                class="group relative overflow-hidden rounded-2xl border bg-gradient-to-br to-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md"
                                :class="rankCardCls(a.rank)"
                            >
                                <!-- Decorative rank stripe -->
                                <div class="absolute left-0 top-0 h-full w-1 rounded-l-2xl"
                                    :class="{
                                        'bg-amber-400': rankTier(a.rank) === 'gold',
                                        'bg-slate-400': rankTier(a.rank) === 'silver',
                                        'bg-orange-400': rankTier(a.rank) === 'bronze',
                                        'bg-sky-400': rankTier(a.rank) === 'other',
                                    }"/>

                                <div class="flex items-start gap-4 pl-2">
                                    <!-- Medal icon -->
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-105"
                                        :class="medalIconCls(a.rank)">
                                        <!-- Trophy for gold, medal for rest -->
                                        <svg v-if="rankTier(a.rank) === 'gold'" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                                        </svg>
                                        <svg v-else class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                        </svg>
                                    </div>

                                    <!-- Text -->
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold leading-snug text-slate-800">{{ a.title }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold" :class="rankBadgeCls(a.rank)">
                                                {{ a.rank }}
                                            </span>
                                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="levelCls(a.level)">
                                                {{ levelLabel(a.level) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Empty state -->
            <div v-if="!extracurricular.description && !extracurricular.achievements.length"
                class="rounded-2xl border-2 border-dashed border-slate-200 py-20 text-center">
                <svg class="mx-auto mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                <p class="font-semibold text-slate-400">Informasi detail belum tersedia.</p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-slate-50 py-6">
            <p class="text-center text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name }}</p>
        </footer>
    </div>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 1s ease;
    position: absolute;
    inset: 0;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
}
</style>
