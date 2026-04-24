<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const user     = computed(() => usePage().props.auth.user);
const initials = computed(() =>
    user.value.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
);

// ── Avatar ────────────────────────────────────────────────────────────────────
const avatarInput   = ref(null);
const avatarPreview = ref(null);

const avatarForm = useForm({ avatar: null });

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const submitAvatar = () => {
    avatarForm.post(route('profile.avatar'), {
        forceFormData: true,
        onSuccess: () => {
            avatarPreview.value = null;
            avatarForm.reset();
        },
    });
};

// ── Profile info ──────────────────────────────────────────────────────────────
const profileForm = useForm({
    name:  user.value.name,
    email: user.value.email,
});

const submitProfile = () => profileForm.patch(route('profile.update'));

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
            <h1 class="text-sm font-semibold text-slate-700">Profil Saya</h1>
        </template>

        <div class="mx-auto max-w-2xl space-y-6">

            <!-- ── Avatar Card ── -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-800">Foto Profil</h2>
                    <p class="mt-0.5 text-xs text-slate-400">JPG, PNG, atau WebP. Maks. 2 MB.</p>
                </div>
                <div class="px-5 py-5">
                    <div class="flex items-center gap-5">

                        <!-- Avatar preview -->
                        <div class="relative shrink-0">
                            <div class="size-20 overflow-hidden rounded-2xl ring-4 ring-slate-100">
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
                            <!-- Camera icon button -->
                            <button
                                type="button"
                                @click="avatarInput.click()"
                                class="absolute -bottom-1 -right-1 flex size-7 items-center justify-center rounded-full bg-white shadow-md ring-2 ring-slate-100 transition-colors hover:bg-slate-50"
                            >
                                <svg class="size-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ user.name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ user.email }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
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
                                    class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-indigo-700 disabled:opacity-60"
                                >
                                    {{ avatarForm.processing ? 'Menyimpan...' : 'Simpan Foto' }}
                                </button>
                            </div>
                            <p v-if="avatarForm.errors.avatar" class="mt-1.5 text-xs text-red-500">
                                {{ avatarForm.errors.avatar }}
                            </p>
                        </div>
                    </div>

                    <input
                        ref="avatarInput"
                        type="file"
                        accept="image/jpg,image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="onAvatarChange"
                    />
                </div>
            </div>

            <!-- ── Informasi Akun ── -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-800">Informasi Akun</h2>
                </div>
                <form @submit.prevent="submitProfile" class="space-y-4 px-5 py-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">Nama Lengkap</label>
                        <input
                            v-model="profileForm.name"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        />
                        <p v-if="profileForm.errors.name" class="mt-1 text-xs text-red-500">{{ profileForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">Email</label>
                        <input
                            v-model="profileForm.email"
                            type="email"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        />
                        <p v-if="profileForm.errors.email" class="mt-1 text-xs text-red-500">{{ profileForm.errors.email }}</p>
                    </div>
                    <div class="flex justify-end pt-1">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── Ubah Password ── -->
            <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-50 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-800">Ubah Password</h2>
                </div>
                <form @submit.prevent="submitPassword" class="space-y-4 px-5 py-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">Password Saat Ini</label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">Password Baru</label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">Konfirmasi Password Baru</label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        />
                    </div>
                    <div class="flex justify-end pt-1">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {{ passwordForm.processing ? 'Menyimpan...' : 'Ubah Password' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AppLayout>
</template>
