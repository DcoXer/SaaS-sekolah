<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const INPUT_CLS = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20';

const props = defineProps({
    letterTypes:           { type: Array,  required: true },
    availablePlaceholders: { type: Object, required: true },
});

const letterTypeOptions = computed(() =>
    props.letterTypes.map(t => ({ value: t.id, label: t.name }))
);

const form = useForm({
    letter_type_id:         '',
    name:                   '',
    content:                '',
    available_placeholders: Object.keys(props.availablePlaceholders),
});

const insertPlaceholder = (placeholder) => {
    form.content = (form.content ?? '') + placeholder;
};

const submit = () => {
    form.post(route('operator.letter-templates.store'), { onError: () => {} });
};
</script>

<template>
    <AppLayout>
        <Head title="Buat Template Surat" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <Link :href="route('operator.letter-templates.index')" class="hover:text-slate-700">Template Surat</Link>
                <span>/</span>
                <span class="font-semibold text-slate-700">Buat Template</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div>
                <h2 class="text-balance text-lg font-bold text-slate-900">Buat Template Surat</h2>
                <p class="text-pretty text-sm text-slate-500">
                    Buat template baru dengan placeholder yang akan diisi otomatis oleh sistem.
                </p>
            </div>

            <!-- Form Card -->
            <div class="mx-auto max-w-3xl">
                <form @submit.prevent="submit" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="space-y-5 px-6 py-6">

                        <!-- Jenis Surat + Nama Template -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Jenis Surat <span class="text-red-500">*</span>
                                </label>
                                <FilterSelect
                                    v-model="form.letter_type_id"
                                    :options="letterTypeOptions"
                                    :has-error="!!form.errors.letter_type_id"
                                    :block="true"
                                />
                                <p v-if="form.errors.letter_type_id" class="mt-1.5 text-xs text-red-500">{{ form.errors.letter_type_id }}</p>
                            </div>
                            <div>
                                <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Nama Template <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Template Aktif Siswa"
                                    autocomplete="off"
                                    :class="[INPUT_CLS, form.errors.name ? 'border-red-400' : '']"
                                />
                                <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>
                        </div>

                        <!-- Isi Template -->
                        <div>
                            <div class="mb-2 flex items-start justify-between gap-4">
                                <label for="content" class="block text-xs font-semibold text-slate-600">
                                    Isi Template <span class="text-red-500">*</span>
                                </label>
                            </div>

                            <!-- Placeholder helper chips -->
                            <div class="mb-2 rounded-lg border border-slate-100 bg-slate-50 p-3">
                                <p class="mb-2 text-xs font-semibold text-slate-500">
                                    Placeholder tersedia — klik untuk menyisipkan ke konten:
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        v-for="(label, ph) in availablePlaceholders"
                                        :key="ph"
                                        type="button"
                                        @click="insertPlaceholder(ph)"
                                        :title="label"
                                        class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-mono text-slate-600 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 transition-colors"
                                    >
                                        {{ ph }}
                                    </button>
                                </div>
                            </div>

                            <textarea
                                id="content"
                                v-model="form.content"
                                rows="15"
                                placeholder="Ketik konten surat di sini. Gunakan placeholder di atas untuk data dinamis..."
                                :class="['resize-y font-mono', INPUT_CLS, form.errors.content ? 'border-red-400' : '']"
                            />
                            <p v-if="form.errors.content" class="mt-1.5 text-xs text-red-500">{{ form.errors.content }}</p>
                        </div>

                    </div>

                    <!-- Footer actions -->
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                        <Link
                            :href="route('operator.letter-templates.index')"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                        >
                            <svg v-if="form.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Template' }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AppLayout>
</template>
