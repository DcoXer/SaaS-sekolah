<script setup>
defineProps({
    currentPage: { type: Number, required: true },
    totalPages:  { type: Number, required: true },
    total:       { type: Number, required: true },
    perPage:     { type: Number, required: true },
    label:       { type: String, default: 'data' },
});

const emit = defineEmits(['update:currentPage']);

const visiblePages = (current, total) => {
    const pages = [];
    for (let p = 1; p <= total; p++) {
        if (p === 1 || p === total || Math.abs(p - current) <= 1) pages.push(p);
        else if (p === current - 2 || p === current + 2) pages.push('…');
    }
    return pages;
};
</script>

<template>
    <div
        v-if="totalPages > 1"
        class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm"
    >
        <p class="text-xs text-slate-500">
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, total) }}
            dari <strong class="text-slate-700">{{ total }}</strong> {{ label }}
        </p>

        <div class="flex items-center gap-1">
            <button
                @click="emit('update:currentPage', currentPage - 1)"
                :disabled="currentPage === 1"
                aria-label="Halaman sebelumnya"
                class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition-[background-color,opacity] duration-150 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>

            <template v-for="p in visiblePages(currentPage, totalPages)" :key="p">
                <button
                    v-if="p !== '…'"
                    @click="emit('update:currentPage', p)"
                    class="flex size-8 items-center justify-center rounded-lg text-xs font-semibold transition-[background-color,color] duration-150"
                    :class="currentPage === p
                        ? 'bg-primary-500 text-white shadow-sm'
                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                >
                    {{ p }}
                </button>
                <span v-else class="flex size-8 items-center justify-center text-xs text-slate-400">…</span>
            </template>

            <button
                @click="emit('update:currentPage', currentPage + 1)"
                :disabled="currentPage === totalPages"
                aria-label="Halaman berikutnya"
                class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition-[background-color,opacity] duration-150 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </div>
</template>
