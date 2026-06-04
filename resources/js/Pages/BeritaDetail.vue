<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    post:           { type: Object,  required: true },
    school:         { type: Object,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
    ppdbActive:     { type: Boolean, default: false },
});

// Lightbox
const lightboxSrc = ref(null);
</script>

<template>
    <Head :title="`${post.title} — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="post.excerpt || post.title">
        <meta head-key="og:title" property="og:title" :content="`${post.title} — ${school?.name ?? ''}`">
        <meta head-key="og:description" property="og:description" :content="post.excerpt || post.title">
        <meta head-key="og:type" property="og:type" content="article">
        <meta v-if="post.cover_image" head-key="og:image" property="og:image" :content="post.cover_image">
    </Head>

    <div class="min-h-screen bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader
            :school="school"
            :can-login="canLogin"
            :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute"
            active-page="berita"
            :ppdb-active="ppdbActive"
        />

        <!-- ── Hero ───────────────────────────────────────────────────────── -->
        <div class="relative overflow-hidden"
            :class="post.cover_image ? 'h-72 sm:h-96 bg-slate-900' : 'h-64 sm:h-80 bg-gradient-to-br from-green-900 via-green-800 to-green-700'">

            <!-- Cover image -->
            <img v-if="post.cover_image"
                :src="post.cover_image" :alt="post.title"
                class="absolute inset-0 size-full object-cover opacity-70"/>

            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-black/10"/>

            <!-- Decorative circles (no cover) -->
            <template v-if="!post.cover_image">
                <div class="absolute -right-20 -top-20 size-72 rounded-full bg-white/5 pointer-events-none"/>
                <div class="absolute -bottom-12 -left-12 size-56 rounded-full bg-white/5 pointer-events-none"/>
            </template>

            <!-- Back button -->
            <div class="absolute inset-x-0 top-0 mx-auto max-w-3xl px-6 pt-5">
                <Link :href="route('berita.index')"
                    class="inline-flex items-center gap-1.5 rounded-full bg-black/30 px-3.5 py-1.5 text-xs font-semibold text-white/90 backdrop-blur-sm transition hover:bg-black/50">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Berita & Pengumuman
                </Link>
            </div>

            <!-- Title content -->
            <div class="absolute inset-x-0 bottom-0 mx-auto max-w-3xl px-6 pb-8">
                <span :class="post.category === 'pengumuman' ? 'border-amber-400/40 bg-amber-400/10 text-amber-300' : 'border-green-400/40 bg-green-400/10 text-green-300'"
                    class="inline-flex w-fit items-center gap-2 rounded-full border px-3.5 py-1 text-xs font-semibold tracking-wide">
                    <span class="size-1.5 rounded-full"
                        :class="post.category === 'pengumuman' ? 'bg-amber-400' : 'bg-green-400'"/>
                    {{ post.category === 'pengumuman' ? 'Pengumuman' : 'Berita' }}
                </span>
                <h1 class="mt-2 text-2xl font-extrabold leading-tight text-white drop-shadow sm:text-3xl lg:text-4xl">
                    {{ post.title }}
                </h1>
                <p v-if="post.published_at" class="mt-2 text-sm text-white/60">{{ post.published_at }}</p>
            </div>
        </div>

        <!-- ── Article ─────────────────────────────────────────────────────── -->
        <div class="mx-auto max-w-3xl px-6 py-12">

            <!-- Excerpt -->
            <blockquote v-if="post.excerpt"
                class="mb-10 rounded-2xl border border-green-100 bg-green-50 px-6 py-5">
                <p class="text-base font-medium italic leading-relaxed text-green-800">{{ post.excerpt }}</p>
            </blockquote>

            <!-- Content -->
            <div class="article-body" v-html="post.content"/>

            <!-- Galeri Foto Konten -->
            <div v-if="post.images && post.images.length" class="mt-12">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-800">
                        <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-800">Galeri Foto</h3>
                    <div class="h-px flex-1 bg-slate-100"/>
                </div>
                <div class="grid gap-2.5" :class="post.images.length === 1 ? 'grid-cols-1' : 'grid-cols-2 sm:grid-cols-3'">
                    <button
                        v-for="(url, i) in post.images" :key="i"
                        @click="lightboxSrc = url"
                        class="group relative aspect-video overflow-hidden rounded-xl bg-slate-100 focus:outline-none">
                        <img :src="url" :alt="`Foto ${i + 1}`" class="size-full object-cover transition-transform duration-300 group-hover:scale-105" />
                        <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all duration-200 group-hover:bg-black/30">
                            <svg class="size-7 text-white opacity-0 drop-shadow transition-opacity duration-200 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803zM10.5 7.5v6m3-3h-6"/>
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Back link -->
            <div class="mt-14 border-t border-slate-100 pt-8">
                <Link :href="route('berita.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 hover:text-green-700">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Berita & Pengumuman
                </Link>
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <div v-if="lightboxSrc"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                @click.self="lightboxSrc = null">
                <button @click="lightboxSrc = null"
                    class="absolute right-4 top-4 flex size-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <img :src="lightboxSrc" class="max-h-[90vh] max-w-full rounded-xl object-contain shadow-2xl" />
            </div>
        </Teleport>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-green-950 py-6">
            <div class="mx-auto max-w-6xl px-6 text-center">
                <p class="text-xs text-green-500">
                    &copy; {{ new Date().getFullYear() }} {{ school?.name ?? 'Sistem Manajemen Sekolah' }}
                    <span v-if="school?.npsn"> · NPSN {{ school.npsn }}</span>
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.article-body {
    color: #374151;
    font-size: 1rem;
    line-height: 1.8;
}
.article-body :deep(p) {
    margin-bottom: 1.25rem;
}
.article-body :deep(h2) {
    font-size: 1.25rem;
    font-weight: 800;
    color: #111827;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}
.article-body :deep(h3) {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}
.article-body :deep(strong) {
    font-weight: 700;
    color: #111827;
}
.article-body :deep(em) {
    font-style: italic;
    color: #4b5563;
}
.article-body :deep(ul),
.article-body :deep(ol) {
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
}
.article-body :deep(ul) {
    list-style-type: disc;
}
.article-body :deep(ol) {
    list-style-type: decimal;
}
.article-body :deep(li) {
    margin-bottom: 0.4rem;
}
.article-body :deep(a) {
    color: #16a34a;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.article-body :deep(blockquote) {
    border-left: 4px solid #16a34a;
    padding-left: 1rem;
    color: #6b7280;
    font-style: italic;
    margin: 1.5rem 0;
}
</style>
