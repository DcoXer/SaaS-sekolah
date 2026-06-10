<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const user = computed(() => usePage().props.auth.user);
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

// ── Avatar ────────────────────────────────────────────────────────────────────
const avatarInput   = ref(null);
const avatarPreview = ref(null);
const avatarForm    = useForm({ avatar: null });

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar   = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const submitAvatar = () => {
    avatarForm.post(route('profile.avatar'), {
        forceFormData: true,
        onSuccess: () => { avatarPreview.value = null; avatarForm.reset(); },
    });
};

// ── Profile info ──────────────────────────────────────────────────────────────
const profileForm = useForm({ name: user.value.name, email: user.value.email });
const submitProfile = () => profileForm.patch(route('profile.update'));

// ── Signature ─────────────────────────────────────────────────────────────────
const sigInput   = ref(null);
const sigPreview = ref(null);
const sigForm    = useForm({ signature: null });

const onSigChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    sigForm.signature = file;
    sigPreview.value  = URL.createObjectURL(file);
};

const submitSignature = () => {
    sigForm.post(route('profile.signature'), {
        forceFormData: true,
        onSuccess: () => { sigPreview.value = null; sigForm.reset(); },
    });
};

const deleteSignature = () => {
    if (!confirm('Hapus tanda tangan?')) return;
    sigForm.delete(route('profile.signature.delete'), {
        onSuccess: () => { sigPreview.value = null; },
    });
};

// ── Password ──────────────────────────────────────────────────────────────────
const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});
const submitPassword = () => {
    passwordForm.put(route('profile.password'), {
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Profil Saya" />

        <template #title>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span class="font-semibold text-slate-700">Profil Saya</span>
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-5">

            <!-- Heading -->
            <div>
                <h2 class="text-lg font-bold text-slate-900">Profil Saya</h2>
                <p class="text-sm text-slate-500">Kelola informasi akun dan keamanan.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[300px,1fr] lg:items-start">

                <!-- ── Kolom Kiri ─────────────────────────────────────────── -->
                <div class="space-y-4 lg:sticky lg:top-6">

                    <!-- Avatar card -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <!-- Gradient banner -->
                        <div class="h-20 bg-gradient-to-br from-violet-500 to-indigo-600"></div>

                        <div class="px-5 pb-5">
                            <!-- Avatar -->
                            <div class="relative -mt-10 mb-4">
                                <div class="size-20 overflow-hidden rounded-2xl ring-4 ring-white shadow-sm">
                                    <img
                                        v-if="avatarPreview || user.avatar_url"
                                        :src="avatarPreview || user.avatar_url"
                                        alt="Foto profil"
                                        class="size-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex size-full items-center justify-center bg-gradient-to-br from-violet-500 to-indigo-600 text-xl font-bold text-white"
                                    >
                                        {{ initials }}
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="avatarInput.click()"
                                    class="absolute bottom-0 right-0 flex size-7 items-center justify-center rounded-full bg-white shadow-md ring-2 ring-slate-100 transition-colors hover:bg-slate-50"
                                >
                                    <svg class="size-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                </button>
                            </div>

                            <p class="text-sm font-bold text-slate-900 truncate">{{ user.name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ user.email }}</p>

                            <!-- Upload actions -->
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="avatarInput.click()"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50"
                                >
                                    Pilih Foto
                                </button>
                                <button
                                    v-if="avatarForm.avatar"
                                    type="button"
                                    :disabled="avatarForm.processing"
                                    @click="submitAvatar"
                                    class="rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-primary-600 disabled:opacity-60"
                                >
                                    {{ avatarForm.processing ? 'Menyimpan...' : 'Simpan Foto' }}
                                </button>
                            </div>
                            <p v-if="avatarForm.errors.avatar" class="mt-1.5 text-xs text-red-500">
                                {{ avatarForm.errors.avatar }}
                            </p>

                            <input
                                ref="avatarInput"
                                type="file"
                                accept="image/jpg,image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="onAvatarChange"
                            />
                        </div>
                    </div>

                    <!-- Tanda Tangan card -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <h3 class="text-sm font-semibold text-slate-800">Tanda Tangan</h3>
                            <p class="mt-0.5 text-xs text-slate-400">Untuk slip honor & dokumen resmi.</p>
                        </div>
                        <div class="px-5 py-4">
                            <!-- Preview box -->
                            <div class="flex h-24 w-full items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-slate-50">
                                <img
                                    v-if="sigPreview || user.signature_url"
                                    :src="sigPreview || user.signature_url"
                                    alt="Tanda tangan"
                                    class="h-full w-full object-contain p-2"
                                />
                                <div v-else class="flex flex-col items-center gap-1 text-slate-300">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    <span class="text-[11px]">Belum ada TTD</span>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="sigInput.click()"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50"
                                >
                                    Pilih File
                                </button>
                                <button
                                    v-if="sigForm.signature"
                                    type="button"
                                    :disabled="sigForm.processing"
                                    @click="submitSignature"
                                    class="rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-primary-600 disabled:opacity-60"
                                >
                                    {{ sigForm.processing ? 'Menyimpan...' : 'Simpan TTD' }}
                                </button>
                                <button
                                    v-if="user.signature_url && !sigForm.signature"
                                    type="button"
                                    @click="deleteSignature"
                                    class="rounded-lg border border-red-100 px-3 py-1.5 text-xs font-medium text-red-500 transition-colors hover:bg-red-50"
                                >
                                    Hapus TTD
                                </button>
                            </div>
                            <p v-if="sigForm.errors.signature" class="mt-1.5 text-xs text-red-500">
                                {{ sigForm.errors.signature }}
                            </p>

                            <input ref="sigInput" type="file" accept="image/jpg,image/jpeg,image/png,image/webp" class="hidden" @change="onSigChange" />
                        </div>
                    </div>

                </div>

                <!-- ── Kolom Kanan ────────────────────────────────────────── -->
                <div class="space-y-4">

                    <!-- Informasi Akun -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <h3 class="text-sm font-semibold text-slate-800">Informasi Akun</h3>
                            <p class="mt-0.5 text-xs text-slate-400">Perbarui nama dan alamat email akun.</p>
                        </div>
                        <form @submit.prevent="submitProfile" class="space-y-4 px-5 py-5">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        profileForm.errors.name ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="profileForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ profileForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="profileForm.email"
                                    type="email"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        profileForm.errors.email ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="profileForm.errors.email" class="mt-1.5 text-xs text-red-500">{{ profileForm.errors.email }}</p>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                                <span v-if="profileForm.recentlySuccessful" class="text-xs text-primary-600 font-medium">Tersimpan!</span>
                                <span v-else></span>
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                                >
                                    <svg v-if="profileForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Ubah Password -->
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <h3 class="text-sm font-semibold text-slate-800">Ubah Password</h3>
                            <p class="mt-0.5 text-xs text-slate-400">Gunakan password yang kuat dan unik.</p>
                        </div>
                        <form @submit.prevent="submitPassword" class="space-y-4 px-5 py-5">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Password Saat Ini <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    autocomplete="current-password"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        passwordForm.errors.current_password ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="passwordForm.errors.current_password" class="mt-1.5 text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Password Baru <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="passwordForm.password"
                                    type="password"
                                    autocomplete="new-password"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        passwordForm.errors.password ? 'border-red-400' : 'border-slate-200',
                                    ]"
                                />
                                <p v-if="passwordForm.errors.password" class="mt-1.5 text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    :class="[
                                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition-[border-color,box-shadow] duration-150',
                                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20',
                                        'border-slate-200',
                                    ]"
                                />
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                                <span v-if="passwordForm.recentlySuccessful" class="text-xs text-primary-600 font-medium">Password berhasil diubah!</span>
                                <span v-else></span>
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-[background-color] duration-150 hover:bg-primary-600 disabled:opacity-60"
                                >
                                    <svg v-if="passwordForm.processing" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    {{ passwordForm.processing ? 'Menyimpan...' : 'Ubah Password' }}
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
