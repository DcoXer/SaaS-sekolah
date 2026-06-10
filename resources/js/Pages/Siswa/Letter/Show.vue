<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    letter: { type: Object, required: true },
});

const statusConfig = {
    draft:            { label: 'Draft',                badge: 'bg-slate-100 text-slate-500' },
    waiting_approval: { label: 'Menunggu Persetujuan', badge: 'bg-amber-100 text-amber-700' },
    approved:         { label: 'Disetujui',            badge: 'bg-emerald-100 text-emerald-700' },
    rejected:         { label: 'Ditolak',              badge: 'bg-red-100 text-red-700' },
};

const formatDate = (val) => {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Surat — ${letter.letter_template?.letter_type?.name ?? 'Detail'}`" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link href="/siswa/letters" class="hover:text-slate-700">Surat</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Detail</span>
            </div>
        </template>

        <div class="space-y-5">
            <BackButton href="/siswa/letters" />

            <!-- Status + info bar -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-base font-bold text-slate-900">
                                {{ letter.letter_template?.letter_type?.name ?? '—' }}
                            </p>
                            <p class="mt-0.5 text-sm text-slate-500">
                                Diajukan {{ formatDate(letter.created_at) }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusConfig[letter.status]?.badge ?? 'bg-slate-100 text-slate-500'"
                        >
                            {{ statusConfig[letter.status]?.label ?? letter.status }}
                        </span>
                    </div>
                </div>

                <!-- Approved info -->
                <div v-if="letter.status === 'approved'" class="border-b border-slate-100 bg-primary-50 px-6 py-3">
                    <p class="text-xs text-primary-700">
                        Disetujui oleh <strong>{{ letter.approved_by?.name ?? '—' }}</strong>
                        pada {{ formatDate(letter.approved_at) }}
                    </p>
                </div>

                <!-- Rejected info -->
                <div v-if="letter.status === 'rejected'" class="border-b border-slate-100 bg-red-50 px-6 py-3">
                    <p class="text-xs font-semibold text-red-700">Alasan Penolakan</p>
                    <p class="mt-0.5 text-sm text-red-600">{{ letter.rejection_note ?? '—' }}</p>
                </div>
            </div>

            <!-- Isi surat -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <h3 class="text-sm font-semibold text-slate-700">Isi Surat</h3>
                </div>
                <div class="px-6 py-5">
                    <div
                        class="prose prose-sm max-w-none whitespace-pre-wrap text-sm text-slate-700"
                        v-html="letter.content ?? '—'"
                    />
                </div>
            </div>

            <!-- Approved: download + verifikasi -->
            <div
                v-if="letter.status === 'approved' && letter.barcode_code"
                class="overflow-hidden rounded-xl border border-primary-200 bg-primary-50"
            >
                <div class="flex items-center justify-between gap-3 px-4 py-3.5">
                    <div class="flex items-center gap-3">
                        <svg class="size-5 shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-primary-800">Surat disetujui — siap diunduh</p>
                            <p class="mt-0.5 font-mono text-xs text-primary-600">{{ letter.barcode_code }}</p>
                        </div>
                    </div>
                    <a
                        :href="route('siswa.letters.pdf', letter.id)"
                        target="_blank"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-600 px-3.5 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-700"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Unduh PDF
                    </a>
                </div>
            </div>

            <!-- Back button -->
            <div>
                <Link
                    href="/siswa/letters"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-700"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Kembali
                </Link>
            </div>

        </div>
    </AppLayout>
</template>
