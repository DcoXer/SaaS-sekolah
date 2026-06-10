<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    templates:             { type: Array,  required: true },
    letterTypes:           { type: Array,  required: true },
    availablePlaceholders: { type: Object, required: true },
});

const categoryLabel = { keterangan: 'Keterangan', pemberitahuan: 'Pemberitahuan' };
const categoryColor = {
    keterangan:    'bg-blue-100 text-blue-700',
    pemberitahuan: 'bg-violet-100 text-violet-700',
};

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.letter-templates.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Grouped by letter type ────────────────────────────────────────────────────
const grouped = computed(() => {
    const map = {};
    for (const t of props.templates) {
        const key = t.letter_type_id;
        if (!map[key]) map[key] = { letterType: t.letter_type, items: [] };
        map[key].items.push(t);
    }
    return Object.values(map);
});
</script>

<template>
    <AppLayout>
        <Head title="Template Surat" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Template Surat</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Template Surat</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Buat template surat dengan placeholder yang akan diisi otomatis oleh sistem.
                    </p>
                </div>
                <Link
                    :href="route('operator.letter-templates.create')"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-primary-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </Link>
            </div>

            <!-- Empty state -->
            <div
                v-if="templates.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada template surat</p>
                <p class="mt-1 text-xs text-slate-400">Buat template surat untuk memulai persuratan.</p>
                <Link
                    :href="route('operator.letter-templates.create')"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Template
                </Link>
            </div>

            <!-- Grouped by letter type -->
            <div v-else class="space-y-4">
                <div
                    v-for="group in grouped"
                    :key="group.letterType?.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-5 py-3">
                        <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold', categoryColor[group.letterType?.category]]">
                            {{ categoryLabel[group.letterType?.category] }}
                        </span>
                        <span class="text-sm font-semibold text-slate-700">{{ group.letterType?.name }}</span>
                        <span class="tabular-nums text-xs text-slate-400">{{ group.items.length }} template</span>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        <li
                            v-for="tmpl in group.items"
                            :key="tmpl.id"
                            class="flex items-start justify-between px-5 py-3 transition-[background-color] duration-150 hover:bg-slate-50"
                        >
                            <div class="flex-1 pr-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-800">{{ tmpl.name }}</span>
                                    <span v-if="!tmpl.is_active"
                                        class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">
                                        Nonaktif
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400 line-clamp-2">{{ tmpl.content }}</p>
                                <div v-if="tmpl.available_placeholders?.length > 0" class="mt-1.5 flex flex-wrap gap-1">
                                    <span
                                        v-for="ph in tmpl.available_placeholders"
                                        :key="ph"
                                        class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-500"
                                    >{{ ph }}</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <Link
                                    :href="route('operator.letter-templates.edit', tmpl.id)"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </Link>
                                <button
                                    @click="deleteTarget = tmpl"
                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- ── Delete Confirm ──────────────────────────────────────────────────── -->
        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Hapus Template</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    Yakin hapus template <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Surat yang sudah dibuat menggunakan template ini tidak akan terpengaruh.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="deleteTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100">
                    Batal
                </button>
                <button @click="submitDelete" :disabled="deleteForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60">
                    <svg v-if="deleteForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
