<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    post:           { type: Object,  required: true },
    school:         { type: Object,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
});
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
        />

        <!-- Cover image full width -->
        <div v-if="post.cover_image" class="relative h-72 overflow-hidden bg-slate-900 lg:h-96">
            <img :src="post.cover_image" :alt="post.title" class="size-full object-cover opacity-80" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"/>
        </div>

        <!-- Article container -->
        <div class="mx-auto max-w-3xl px-6 py-10">

            <!-- Breadcrumb -->
            <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('welcome')" class="hover:text-green-700 transition-colors">Beranda</Link>
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                <Link :href="route('berita.index')" class="hover:text-green-700 transition-colors">Berita & Pengumuman</Link>
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                <span class="line-clamp-1 text-slate-700 font-medium">{{ post.title }}</span>
            </nav>

            <!-- Meta -->
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <span :class="post.category === 'pengumuman' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'"
                    class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide">
                    {{ post.category === 'pengumuman' ? 'Pengumuman' : 'Berita' }}
                </span>
                <span v-if="post.published_at" class="text-sm text-slate-400">{{ post.published_at }}</span>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-extrabold leading-tight text-slate-900 lg:text-3xl">{{ post.title }}</h1>

            <!-- Excerpt -->
            <p v-if="post.excerpt" class="mt-4 text-base leading-relaxed text-slate-500 italic border-l-4 border-green-400 pl-4">{{ post.excerpt }}</p>

            <!-- Divider -->
            <div class="my-8 h-px bg-slate-100"/>

            <!-- Content -->
            <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed whitespace-pre-wrap">{{ post.content }}</div>

            <!-- Back link -->
            <div class="mt-12 border-t border-slate-100 pt-8">
                <Link :href="route('berita.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 hover:text-green-700">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Kembali ke Berita & Pengumuman
                </Link>
            </div>
        </div>

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
