<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, default: null },
});

const goBack = () => window.history.back();
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-slate-500 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
    >
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </Link>
    <button
        v-else
        type="button"
        @click="goBack"
        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-slate-500 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
    >
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </button>
</template>
