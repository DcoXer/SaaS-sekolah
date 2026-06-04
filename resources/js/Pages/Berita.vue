<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    posts:          { type: Object,  default: () => ({ data: [], links: [] }) },
    school:         { type: Object,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
});

const baseUrl = usePage().props.ziggy?.url ?? '';
</script>

<template>
    <Head :title="`Berita & Pengumuman — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="`Berita dan pengumuman terbaru dari ${school?.name ?? 'sekolah kami'}.`">
        <meta head-key="og:title" property="og:title" :content="`Berita & Pengumuman — ${school?.name ?? ''}`">
        <meta head-key="og:description" property="og:description" :content="`Berita dan pengumuman terbaru dari ${school?.name ?? ''}.`">
        <meta head-key="og:type" property="og:type" content="website">
        <meta v-if="school?.logo" head-key="og:image" property="og:image" :content="school.logo">
    </Head>

    <div class="min-h-screen bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader
            :school="school"
            :can-login="canLogin"
            :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute"
            active-page="berita"
        />

        <!-- Hero kecil -->
        <section class="border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white py-12">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-3">
                    <Link :href="route('welcome')" class="hover:text-green-700 transition-colors">Beranda</Link>
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    <span class="text-slate-700 font-medium">Berita & Pengumuman</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 lg:text-4xl">Berita & Pengumuman</h1>
                <p class="mt-2 text-slate-500">Informasi terbaru seputar kegiatan dan pengumuman sekolah.</p>
            </div>
        </section>

        <!-- List posts -->
        <section class="py-14">
            <div class="mx-auto max-w-6xl px-6">

                <div v-if="posts.data.length" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="post in posts.data" :key="post.id"
                        :href="route('berita.show', post.slug)"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <!-- Cover -->
                        <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                            <img
                                v-if="post.cover_image"
                                :src="post.cover_image"
                                :alt="post.title"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                            <div v-else class="flex size-full items-center justify-center bg-gradient-to-br from-green-50 to-slate-100">
                                <svg class="size-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3 flex items-center gap-2">
                                <span :class="post.category === 'pengumuman' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'"
                                    class="rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide">
                                    {{ post.category === 'pengumuman' ? 'Pengumuman' : 'Berita' }}
                                </span>
                                <span v-if="post.published_at" class="text-xs text-slate-400">{{ post.published_at }}</span>
                            </div>
                            <h2 class="line-clamp-2 text-base font-bold leading-snug text-slate-800 group-hover:text-green-700 transition-colors">{{ post.title }}</h2>
                            <p v-if="post.excerpt" class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-slate-500">{{ post.excerpt }}</p>
                            <span class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-green-700">
                                Baca selengkapnya
                                <svg class="size-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Empty -->
                <div v-else class="py-24 text-center">
                    <svg class="mx-auto mb-4 size-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-3 1.5h.008v.008H10.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM10.5 7.5h.008v.008H10.5V7.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3 9.75A.75.75 0 013.75 9h16.5a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 17.25v-7.5z"/>
                    </svg>
                    <p class="text-slate-400">Belum ada berita atau pengumuman yang dipublikasikan.</p>
                </div>

                <!-- Pagination -->
                <div v-if="posts.links && posts.links.length > 3" class="mt-10 flex flex-wrap justify-center gap-1">
                    <component
                        :is="link.url ? 'a' : 'span'"
                        v-for="link in posts.links" :key="link.label"
                        :href="link.url ?? undefined"
                        v-html="link.label"
                        class="inline-flex min-w-[2.5rem] items-center justify-center rounded-xl px-3.5 py-2 text-sm transition-colors"
                        :class="link.active
                            ? 'bg-green-700 font-semibold text-white'
                            : link.url
                                ? 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                : 'cursor-not-allowed border border-slate-100 bg-white text-slate-300'"
                    />
                </div>
            </div>
        </section>

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
