<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import JsonLd from '@/Components/JsonLd.vue';

const props = defineProps({
    result:         { type: Object,  default: null },
    invoice:        { type: Object,  default: null },
    error:          { type: String,  default: null },
    number:         { type: String,  default: '' },
    school:         { type: Object,  default: null },
    canLogin:       { type: Boolean, default: true },
    isLoggedIn:     { type: Boolean, default: false },
    dashboardRoute: { type: String,  default: null },
});

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val ?? 0);

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const input = ref(props.number ?? '');

const baseUrl = usePage().props.ziggy?.url ?? '';
const jsonLd = computed(() => ({
    '@context':  'https://schema.org',
    '@type':     'WebPage',
    '@id':       `${baseUrl}/ppdb/cek#webpage`,
    'name':      `Cek Status PPDB — ${props.school?.name ?? ''}`,
    'description': `Cek status pendaftaran PPDB ${props.school?.name ?? ''} menggunakan nomor pendaftaran Anda.`,
    'url':       `${baseUrl}/ppdb/cek`,
    'isPartOf':  { '@id': `${baseUrl}/#website` },
    'about':     { '@id': `${baseUrl}/#school` },
    'breadcrumb': {
        '@type': 'BreadcrumbList',
        'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Beranda', 'item': baseUrl },
            { '@type': 'ListItem', 'position': 2, 'name': 'PPDB',    'item': `${baseUrl}/ppdb` },
            { '@type': 'ListItem', 'position': 3, 'name': 'Cek Status', 'item': `${baseUrl}/ppdb/cek` },
        ],
    },
}));

const check = () => {
    if (!input.value.trim()) return;
    router.get(route('ppdb.check'), { no: input.value.trim() }, { preserveState: true });
};

const statusColor = {
    pending:    'bg-amber-100 text-amber-800 border-amber-200',
    accepted:   'bg-green-100 text-green-800 border-green-200',
    rejected:   'bg-red-100 text-red-800 border-red-200',
    waitlisted: 'bg-sky-100 text-sky-800 border-sky-200',
};
const statusLabel = {
    pending:    'Menunggu Verifikasi',
    accepted:   'Diterima',
    rejected:   'Tidak Diterima',
    waitlisted: 'Daftar Tunggu',
};
const statusIcon = {
    pending:    'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    accepted:   'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    rejected:   'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    waitlisted: 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};
</script>

<template>
    <Head :title="`Cek Status PPDB — ${school?.name ?? 'Sekolah'}`">
        <meta head-key="description" name="description" :content="`Cek status pendaftaran PPDB ${school?.name ?? 'sekolah kami'} menggunakan nomor pendaftaran Anda.`">
        <meta head-key="og:title" property="og:title" :content="`Cek Status PPDB — ${school?.name ?? ''}`">
        <meta head-key="og:description" property="og:description" :content="`Lacak dan cek hasil seleksi PPDB ${school?.name ?? ''} secara online.`">
        <meta head-key="og:type" property="og:type" content="website">
        <meta v-if="school?.logo" head-key="og:image" property="og:image" :content="school.logo">
        <meta v-if="school?.logo" head-key="twitter:image" name="twitter:image" :content="school.logo">
        <meta head-key="twitter:title" name="twitter:title" :content="`Cek Status PPDB — ${school?.name ?? ''}`">
        <meta head-key="twitter:description" name="twitter:description" :content="`Cek status pendaftaran PPDB ${school?.name ?? ''} menggunakan nomor pendaftaran Anda.`">
    </Head>
    <JsonLd :data="jsonLd" />

    <div class="min-h-screen bg-white font-sans antialiased" style="font-family:'Plus Jakarta Sans',sans-serif">

        <PublicHeader :school="school" :can-login="canLogin" :is-logged-in="isLoggedIn"
            :dashboard-route="dashboardRoute" active-page="ppdb" />

        <!-- Hero -->
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-700 via-amber-600 to-yellow-500 py-16">
            <div class="absolute -right-24 -top-24 size-80 rounded-full bg-white/5"/>
            <div class="absolute -bottom-16 -left-16 size-64 rounded-full bg-white/5"/>
            <div class="relative mx-auto max-w-2xl px-6 text-center">
                <div v-reveal>
                    <h1 class="text-3xl font-extrabold text-white lg:text-4xl">Cek Status Pendaftaran</h1>
                    <p class="mt-3 text-amber-100">Masukkan nomor pendaftaran PPDB untuk melihat status</p>
                </div>
                <!-- Search box -->
                <div v-reveal="{ delay: 100 }" class="mt-8">
                    <div class="flex gap-2 rounded-2xl bg-white/10 p-1.5 backdrop-blur-sm">
                        <input v-model="input" type="text" placeholder="Nomor Pendaftaran"
                            class="flex-1 rounded-xl bg-white/90 px-5 py-3 text-sm font-semibold text-slate-800 placeholder-slate-400 outline-none focus:bg-white"
                            @keyup.enter="check"/>
                        <button @click="check"
                            class="flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow transition-colors hover:bg-amber-400 active:scale-95">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            Cek
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result -->
        <div class="mx-auto max-w-2xl px-6 py-14">

            <!-- Error -->
            <div v-if="error" v-reveal class="rounded-2xl border border-red-200 bg-red-50 p-8 text-center">
                <svg class="mx-auto mb-3 size-10 text-red-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-semibold text-red-700">{{ error }}</p>
                <p class="mt-1 text-sm text-red-400">Pastikan nomor pendaftaran sudah benar.</p>
            </div>

            <!-- Result card -->
            <div v-else-if="result" v-reveal class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg">
                <!-- Status header -->
                <div class="px-8 py-6" :class="{
                    'bg-amber-50 border-b border-amber-100': result.status === 'pending',
                    'bg-green-50 border-b border-green-100': result.status === 'accepted',
                    'bg-red-50 border-b border-red-100':    result.status === 'rejected',
                    'bg-sky-50 border-b border-sky-100':    result.status === 'waitlisted',
                }">
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl"
                            :class="{
                                'bg-amber-100': result.status === 'pending',
                                'bg-green-100': result.status === 'accepted',
                                'bg-red-100':   result.status === 'rejected',
                                'bg-sky-100':   result.status === 'waitlisted',
                            }">
                            <svg class="size-7" :class="{
                                'text-amber-600': result.status === 'pending',
                                'text-green-600': result.status === 'accepted',
                                'text-red-600':   result.status === 'rejected',
                                'text-sky-600':   result.status === 'waitlisted',
                            }" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="statusIcon[result.status]"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest"
                                :class="{
                                    'text-amber-600': result.status === 'pending',
                                    'text-green-600': result.status === 'accepted',
                                    'text-red-600':   result.status === 'rejected',
                                    'text-sky-600':   result.status === 'waitlisted',
                                }">Status Pendaftaran</p>
                            <h2 class="mt-0.5 text-xl font-extrabold text-slate-900">{{ statusLabel[result.status] }}</h2>
                            <p class="mt-0.5 text-sm font-mono text-slate-500">{{ result.registration_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detail -->
                <div class="divide-y divide-slate-50 px-8 py-4">
                    <div class="grid grid-cols-2 gap-4 py-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Nama Lengkap</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ result.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Jenis Kelamin</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ result.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Tanggal Lahir</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ result.birth_place }}, {{ new Date(result.birth_date).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Nama Orang Tua</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ result.parent_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">No. HP Orang Tua</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ result.parent_phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Tanggal Daftar</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ new Date(result.created_at).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' }) }}
                            </p>
                        </div>
                    </div>

                    <!-- Catatan reject -->
                    <div v-if="result.status === 'rejected' && result.notes" class="py-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-red-400">Keterangan</p>
                        <p class="mt-1 text-sm text-slate-700">{{ result.notes }}</p>
                    </div>

                    <!-- Info tagihan DP (hanya jika diterima & ada invoice) -->
                    <div v-if="result.status === 'accepted' && invoice" class="py-4">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Tagihan Uang Masuk</p>
                        <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                            <!-- Status lunas -->
                            <div v-if="invoice.status === 'paid'" class="flex items-center gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-green-100">
                                    <svg class="size-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-green-700">Pembayaran Lunas</p>
                                    <p class="text-sm text-green-600">{{ formatRupiah(invoice.amount) }}</p>
                                </div>
                            </div>

                            <!-- Status belum/kurang bayar -->
                            <div v-else>
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Uang Masuk Sekolah</p>
                                        <p class="mt-0.5 text-2xl font-extrabold text-green-700">{{ formatRupiah(invoice.amount) }}</p>
                                        <p v-if="invoice.status === 'partial'" class="mt-1 text-sm text-amber-700">
                                            Sudah dibayar {{ formatRupiah(invoice.total_paid) }} · Sisa {{ formatRupiah(invoice.remaining_amount) }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"
                                        :class="invoice.status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">
                                        {{ invoice.status === 'partial' ? 'Kurang Bayar' : 'Belum Bayar' }}
                                    </span>
                                </div>
                                <div v-if="invoice.due_date" class="mt-3 flex items-center gap-1.5 text-sm text-slate-500">
                                    <svg class="size-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                                    </svg>
                                    Batas pembayaran: <span class="font-semibold text-slate-700">{{ formatDate(invoice.due_date) }}</span>
                                </div>
                                <div class="mt-4 rounded-xl border border-green-200 bg-white px-4 py-3 text-sm text-slate-600">
                                    <p class="font-semibold text-slate-700">Cara Pembayaran</p>
                                    <p class="mt-1">Datang ke kantor sekolah dan tunjukkan nomor pendaftaran <span class="font-mono font-bold text-slate-800">{{ result.registration_number }}</span> kepada petugas keuangan. Pembayaran dilakukan secara tunai.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info tidak ada invoice (diterima tapi uang masuk belum dikonfigurasi) -->
                    <div v-else-if="result.status === 'accepted' && !invoice" class="py-4">
                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                            <p class="font-semibold">Selamat, pendaftaran Anda diterima!</p>
                            <p class="mt-1 text-blue-600">Hubungi sekolah untuk informasi lebih lanjut mengenai proses pendaftaran.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-100 bg-slate-50 px-8 py-4 text-center">
                    <p class="text-xs text-slate-400">
                        Simpan nomor pendaftaran Anda untuk pengecekan berikutnya.
                        Hubungi sekolah jika ada pertanyaan.
                    </p>
                </div>
            </div>

            <!-- Empty state (belum cari) -->
            <div v-else-if="!number" v-reveal class="rounded-2xl border-2 border-dashed border-slate-200 py-20 text-center">
                <svg class="mx-auto mb-3 size-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <p class="font-semibold text-slate-400">Masukkan nomor pendaftaran di atas</p>
            </div>

            <div class="mt-8 text-center">
                <Link :href="route('ppdb.index')"
                    class="text-sm font-semibold text-amber-600 hover:text-amber-500 transition-colors">
                    ← Kembali ke halaman PPDB
                </Link>
            </div>
        </div>

        <footer class="border-t border-slate-100 bg-slate-50 py-6">
            <p class="text-center text-xs text-slate-400">&copy; {{ new Date().getFullYear() }} {{ school?.name }}</p>
        </footer>
    </div>
</template>
