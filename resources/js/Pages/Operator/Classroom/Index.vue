<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterSelect from '@/Components/FilterSelect.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    classrooms:    { type: Array, required: true },
    academicYears: { type: Array, required: true },
});

// ── Create ────────────────────────────────────────────────────────────────────
const showCreate = ref(false);

const createForm = useForm({
    academic_year_id: '',
    name:             '',
    grade:            '',
});

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
};

const submitCreate = () => {
    createForm.post(route('operator.classrooms.store'), {
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};

// ── Delete 
const deleteTarget = ref(null);
const deleteForm   = useForm({});

const submitDelete = () => {
    deleteForm.delete(route('operator.classrooms.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null; },
    });
};

// ── Search & Filter ───────────────────────────────────────────────────────────
const search      = ref('');
const filterGrade = ref('');

const filtered = computed(() => {
    let list = props.classrooms;
    if (filterGrade.value) list = list.filter(c => String(c.grade) === filterGrade.value);
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(c =>
            c.name.toLowerCase().includes(q) ||
            (c.homeroom_teacher?.user?.name && c.homeroom_teacher.user.name.toLowerCase().includes(q)) ||
            (c.academic_year?.name && c.academic_year.name.toLowerCase().includes(q))
        );
    }
    return list;
});

const PER_PAGE    = 12;
const currentPage = ref(1);
const totalPages  = computed(() => Math.ceil(filtered.value.length / PER_PAGE));
const paginated   = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});
watch([search, filterGrade], () => { currentPage.value = 1; });

// ── Helpers ───────────────────────────────────────────────────────────────────
const gradeLabel = (grade) => `Kelas ${grade}`;
const activeYears = props.academicYears.filter(y => y.status === 'active' || y.status === 'pending');

const yearOptions = computed(() => props.academicYears.map(y => ({ value: y.id, label: y.name })));
const gradeOptions = [1,2,3,4,5,6].map(g => ({ value: g, label: `Kelas ${g}` }));
</script>

<template>
    <AppLayout>
        <Head title="Data Kelas" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Operator</span>
                <span>/</span>
                <span class="font-semibold text-slate-700">Kelas</span>
            </div>
        </template>

        <div class="space-y-4">

            <!-- Heading -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-balance text-lg font-bold text-slate-900">Data Kelas</h2>
                    <p class="text-pretty text-sm text-slate-500">
                        Kelola kelas per tahun ajaran. Wali kelas diatur dari halaman detail.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </button>
            </div>

            <!-- Search & Filter -->
            <div v-if="classrooms.length > 0" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama kelas, wali kelas..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
                    />
                </div>
                <div class="h-5 w-px bg-slate-200"/>
                <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
                    <button
                        v-for="opt in [{ value: '', label: 'Semua' }, ...([1,2,3,4,5,6].map(g => ({ value: String(g), label: `Kelas ${g}` })))]"
                        :key="opt.value"
                        @click="filterGrade = opt.value"
                        :class="filterGrade === opt.value
                            ? 'bg-white text-slate-800 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700'"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150"
                    >
                        {{ opt.label }}
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="classrooms.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center"
            >
                <svg class="mb-3 size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada kelas</p>
                <p class="mt-1 text-xs text-slate-400">Buat kelas untuk tahun ajaran yang sudah ada.</p>
                <button
                    @click="openCreate"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kelas
                </button>
            </div>

            <!-- No results -->
            <div
                v-else-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center"
            >
                <p class="text-sm font-semibold text-slate-700">Tidak ada hasil</p>
                <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci atau hapus filter.</p>
                <button @click="search = ''; filterGrade = ''" class="mt-3 text-xs font-semibold text-emerald-600 hover:underline">Reset pencarian</button>
            </div>

            <template v-else>

            <!-- Mobile card list -->
            <div class="sm:hidden space-y-2">
                <div
                    v-for="classroom in paginated"
                    :key="classroom.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="flex items-start justify-between p-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-sm font-semibold text-slate-800">{{ classroom.name }}</span>
                                <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">{{ gradeLabel(classroom.grade) }}</span>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-400">{{ classroom.academic_year?.name ?? '—' }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Wali:
                                <span v-if="classroom.homeroom_teacher" class="font-medium text-slate-700">{{ classroom.homeroom_teacher.user.name }}</span>
                                <span v-else class="text-slate-400">Belum ditentukan</span>
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1 ml-2">
                            <Link :href="route('operator.classrooms.show', classroom.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Lihat detail kelas">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </Link>
                            <button @click="deleteTarget = classroom" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Hapus kelas">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden sm:block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Kelas</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tingkat</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Tahun Ajaran</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500">Wali Kelas</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="classroom in paginated" :key="classroom.id" class="transition-[background-color] duration-150 hover:bg-slate-50">
                            <td class="px-5 py-3.5"><span class="text-sm font-semibold text-slate-800">{{ classroom.name }}</span></td>
                            <td class="px-5 py-3.5"><span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">{{ gradeLabel(classroom.grade) }}</span></td>
                            <td class="px-5 py-3.5"><span class="text-sm text-slate-600">{{ classroom.academic_year?.name ?? '—' }}</span></td>
                            <td class="px-5 py-3.5">
                                <span v-if="classroom.homeroom_teacher" class="text-sm text-slate-700">{{ classroom.homeroom_teacher.user.name }}</span>
                                <span v-else class="text-sm text-slate-400">Belum ditentukan</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('operator.classrooms.show', classroom.id)" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-700" aria-label="Lihat detail kelas">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <button @click="deleteTarget = classroom" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-red-50 hover:text-red-500" aria-label="Hapus kelas">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="totalPages > 1"
                :current-page="currentPage"
                :total-pages="totalPages"
                :total="filtered.length"
                :per-page="PER_PAGE"
                label="kelas"
                @update:current-page="currentPage = $event"
            />

            </template>

        </div>

        <!-- Create Modal  -->
        <Modal :show="showCreate" max-width="sm" @close="showCreate = false">
            <form @submit.prevent="submitCreate">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-balance text-base font-bold text-slate-900">Tambah Kelas</h3>
                    <button
                        type="button"
                        aria-label="Tutup modal"
                        @click="showCreate = false"
                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 transition-[background-color,color] duration-150 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">

                    <!-- Tahun Ajaran -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Tahun Ajaran <span class="text-red-500">*</span>
                        </label>
                        <FilterSelect
                            v-model="createForm.academic_year_id"
                            :options="yearOptions"
                            block
                            :has-error="!!createForm.errors.academic_year_id"
                        />
                        <p v-if="createForm.errors.academic_year_id" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.academic_year_id }}</p>
                    </div>

                    <!-- Nama + Tingkat -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="c-name" class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Nama Kelas <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="c-name"
                                v-model="createForm.name"
                                type="text"
                                placeholder="Contoh: 1A"
                                :class="[
                                    'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    createForm.errors.name ? 'border-red-400' : 'border-slate-200',
                                ]"
                            />
                            <p v-if="createForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                Tingkat <span class="text-red-500">*</span>
                            </label>
                            <FilterSelect
                                v-model="createForm.grade"
                                :options="gradeOptions"
                                block
                                :has-error="!!createForm.errors.grade"
                            />
                            <p v-if="createForm.errors.grade" class="mt-1.5 text-xs text-red-500">{{ createForm.errors.grade }}</p>
                        </div>
                    </div>

                    <!-- Info note -->
                    <div class="flex items-start gap-2 rounded-lg bg-slate-50 px-3.5 py-3">
                        <svg class="mt-0.5 size-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <p class="text-pretty text-xs text-slate-500">
                            Wali kelas dapat diatur setelah kelas dibuat melalui halaman detail.
                        </p>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                    <button
                        type="button"
                        @click="showCreate = false"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-emerald-600 disabled:opacity-60"
                    >
                        <svg v-if="createForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ── Delete Confirm ──────────────────────────────────────────────────── -->
        <Modal :show="!!deleteTarget" max-width="sm" @close="deleteTarget = null">
            <div class="px-6 py-5">
                <div class="mb-4 flex size-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="size-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-balance text-base font-bold text-slate-900">Hapus Kelas</h3>
                <p class="text-pretty mt-1.5 text-sm text-slate-500">
                    Yakin hapus kelas <span class="font-semibold text-slate-700">{{ deleteTarget?.name }}</span>?
                    Data siswa yang terdaftar di kelas ini juga akan terputus.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="deleteTarget = null"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition-[background-color] duration-150 hover:bg-slate-100"
                >
                    Batal
                </button>
                <button
                    @click="submitDelete"
                    :disabled="deleteForm.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-red-600 disabled:opacity-60"
                >
                    <svg v-if="deleteForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                </button>
            </div>
        </Modal>

    </AppLayout>
</template>
