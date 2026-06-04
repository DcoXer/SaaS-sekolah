<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSelect from '@/Components/FilterSelect.vue';

const props = defineProps({
    setting:       { type: Object, default: null },
    registrations: { type: Object, default: null },
    stats:         { type: Object, default: null },
    filters:       { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilter = () => {
    router.get(route('kamad.ppdb.index'), { search: search.value, status: status.value }, { preserveState: true });
};

const statusColor = {
    pending:    'bg-amber-100 text-amber-700',
    accepted:   'bg-green-100 text-green-700',
    rejected:   'bg-red-100 text-red-700',
    waitlisted: 'bg-sky-100 text-sky-700',
};
const statusLabel = {
    pending:    'Menunggu',
    accepted:   'Diterima',
    rejected:   'Ditolak',
    waitlisted: 'Daftar Tunggu',
};

const detail = ref(null);
</script>

<template>
    <Head title="PPDB" />
    <AppLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">PPDB</h1>
                <p class="mt-0.5 text-sm text-slate-500">Rekap penerimaan peserta didik baru</p>
            </div>

            <div v-if="!setting" class="rounded-2xl border-2 border-dashed border-slate-200 py-20 text-center">
                <p class="text-slate-400">Pengaturan PPDB belum dibuat oleh operator.</p>
            </div>

            <template v-else>
                <!-- Setting info + Stats -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="setting.is_open ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'">
                                <span class="size-1.5 rounded-full" :class="setting.is_open ? 'bg-green-500 animate-pulse' : 'bg-slate-400'"/>
                                {{ setting.is_open ? 'Dibuka' : 'Ditutup' }}
                            </span>
                        </div>
                        <h2 class="mt-2 font-extrabold text-slate-900">{{ setting.title }}</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ new Date(setting.registration_start).toLocaleDateString('id-ID') }}
                            — {{ new Date(setting.registration_end).toLocaleDateString('id-ID') }}
                            · Kuota {{ setting.quota }} siswa
                        </p>
                    </div>
                    <div v-for="s in [
                        { label: 'Total Pendaftar', value: stats?.total ?? 0,      color: 'text-slate-900' },
                        { label: 'Diterima',        value: stats?.accepted ?? 0,   color: 'text-green-700' },
                    ]" :key="s.label"
                        class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-6 shadow-sm text-center">
                        <span class="text-3xl font-extrabold" :class="s.color">{{ s.value }}</span>
                        <span class="mt-1 text-xs font-semibold text-slate-500">{{ s.label }}</span>
                    </div>
                </div>

                <!-- Stats detail -->
                <div v-if="stats" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div v-for="s in [
                        { label: 'Menunggu',     value: stats.pending,    class: 'bg-amber-50 border-amber-200 text-amber-700' },
                        { label: 'Diterima',     value: stats.accepted,   class: 'bg-green-50 border-green-200 text-green-700' },
                        { label: 'Ditolak',      value: stats.rejected,   class: 'bg-red-50 border-red-200 text-red-700' },
                        { label: 'Daftar Tunggu',value: stats.waitlisted, class: 'bg-sky-50 border-sky-200 text-sky-700' },
                    ]" :key="s.label"
                        class="flex flex-col items-center justify-center rounded-2xl border py-5 text-center"
                        :class="s.class">
                        <span class="text-2xl font-extrabold">{{ s.value }}</span>
                        <span class="mt-0.5 text-xs font-semibold">{{ s.label }}</span>
                    </div>
                </div>

                <!-- Filter -->
                <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                    <div class="relative flex-1 min-w-[180px]">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/>
                        </svg>
                        <input v-model="search" type="search" placeholder="Cari nama, nomor..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition-[border-color,box-shadow] focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-400/20"
                            @keyup.enter="applyFilter"/>
                    </div>
                    <FilterSelect
                        v-model="status"
                        @change="applyFilter"
                        :options="[
                            { value: '', label: 'Semua Status' },
                            { value: 'pending', label: 'Menunggu' },
                            { value: 'accepted', label: 'Diterima' },
                            { value: 'rejected', label: 'Ditolak' },
                            { value: 'waitlisted', label: 'Daftar Tunggu' },
                        ]"
                    />
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">No. Daftar</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Nama</th>
                                    <th class="hidden px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 sm:table-cell">Orang Tua</th>
                                    <th class="hidden px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 md:table-cell">Tgl. Daftar</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-if="!registrations?.data?.length">
                                    <td colspan="6" class="py-12 text-center text-sm text-slate-400">Belum ada pendaftar.</td>
                                </tr>
                                <tr v-for="reg in registrations?.data" :key="reg.id" class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ reg.registration_number }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800">{{ reg.full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ reg.gender === 'male' ? 'L' : 'P' }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 sm:table-cell">
                                        <p class="text-slate-700">{{ reg.parent_name }}</p>
                                        <p class="text-xs text-slate-400">{{ reg.parent_phone }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 text-xs text-slate-400 md:table-cell">
                                        {{ new Date(reg.created_at).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusColor[reg.status]">
                                            {{ statusLabel[reg.status] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button @click="detail = reg"
                                            class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors hover:bg-slate-50">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="registrations?.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
                        <p class="text-xs text-slate-400">{{ registrations.from }}–{{ registrations.to }} dari {{ registrations.total }}</p>
                        <div class="flex gap-1">
                            <a v-for="link in registrations.links" :key="link.label"
                                :href="link.url ?? '#'"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                :class="link.active ? 'bg-green-700 text-white' : link.url ? 'border border-slate-200 text-slate-600 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ── Detail modal ───────────────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150">
                <div v-if="detail"
                    class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-black/60 p-4"
                    @click.self="detail = null">
                    <div class="my-4 w-full max-w-xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                            <div>
                                <h2 class="font-extrabold text-slate-900">{{ detail.full_name }}</h2>
                                <span class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColor[detail.status]">
                                    {{ statusLabel[detail.status] }}
                                </span>
                            </div>
                            <button @click="detail = null"
                                class="flex size-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="max-h-[70vh] overflow-y-auto p-6">
                            <dl class="grid grid-cols-2 gap-4 text-sm">
                                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">No. Daftar</dt><dd class="mt-0.5 font-mono text-xs">{{ detail.registration_number }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Jenis Kelamin</dt><dd class="mt-0.5">{{ detail.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">TTL</dt><dd class="mt-0.5">{{ detail.birth_place }}, {{ new Date(detail.birth_date).toLocaleDateString('id-ID') }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Agama</dt><dd class="mt-0.5">{{ detail.religion ?? '-' }}</dd></div>
                                <div class="col-span-2"><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Alamat</dt><dd class="mt-0.5">{{ detail.address }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Orang Tua</dt><dd class="mt-0.5">{{ detail.parent_name }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">No. HP</dt><dd class="mt-0.5">{{ detail.parent_phone }}</dd></div>
                                <div v-if="detail.notes" class="col-span-2"><dt class="text-xs font-bold uppercase tracking-wide text-red-400">Catatan</dt><dd class="mt-0.5 text-slate-700">{{ detail.notes }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
