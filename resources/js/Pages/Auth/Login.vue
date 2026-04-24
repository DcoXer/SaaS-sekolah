<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status:           { type: String },
});

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const showPassword = ref(false);

const highlights = [
    'Kelola nilai & raport digital per semester',
    'Tagihan SPP otomatis & pembayaran online',
    'Persuratan dengan alur persetujuan digital',
];

const iconPaths = {
    building: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    check:    'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    eye:      'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    eyeOff:   'M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88',
    mail:     'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    lock:     'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
    arrow:    'M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3',
    spinner:  'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
};
</script>

<template>
    <Head title="Masuk" />

    <div class="flex min-h-dvh" style="font-family: 'Plus Jakarta Sans', sans-serif;">

        <!-- ── LEFT PANEL ──────────────────────────────────────────────────────── -->
        <div class="relative hidden flex-col justify-between bg-[#0D1B2A] p-12 lg:flex lg:w-[45%]">

            <!-- Logo -->
            <div class="flex items-center gap-2.5">
                <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-500 shadow-sm">
                    <svg class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.building" />
                    </svg>
                </div>
                <span class="text-base font-semibold text-white">SiManSek</span>
            </div>

            <!-- Center -->
            <div>
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5">
                    <span class="size-1.5 rounded-full bg-emerald-400" />
                    <span class="text-xs font-semibold text-emerald-400">Sistem Aktif</span>
                </div>

                <h1 class="text-balance text-3xl font-bold leading-tight text-white xl:text-4xl">
                    Manajemen sekolah<br>
                    <span class="text-emerald-400">lebih mudah.</span>
                </h1>

                <p class="mt-4 text-pretty text-sm leading-relaxed text-white/40">
                    Platform digital untuk SD/MI — dari nilai harian hingga raport, tagihan, dan surat resmi.
                </p>

                <ul class="mt-8 space-y-3.5">
                    <li
                        v-for="item in highlights" :key="item"
                        class="flex items-start gap-3 text-sm text-white/60"
                    >
                        <svg class="mt-0.5 size-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.check" />
                        </svg>
                        {{ item }}
                    </li>
                </ul>
            </div>

            <p class="text-xs text-white/20">&copy; {{ new Date().getFullYear() }} SiManSek</p>
        </div>

        <!-- ── RIGHT PANEL — Form ──────────────────────────────────────────────── -->
        <div class="flex flex-1 flex-col items-center justify-center bg-slate-50 px-6 py-12">

            <!-- Mobile logo -->
            <div class="mb-8 flex items-center gap-2.5 lg:hidden">
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-500 shadow-sm">
                    <svg class="size-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.building" />
                    </svg>
                </div>
                <span class="text-base font-semibold text-slate-900">SiManSek</span>
            </div>

            <div class="w-full max-w-sm">

                <div class="mb-8">
                    <h2 class="text-balance text-2xl font-bold text-slate-900">Selamat datang</h2>
                    <p class="mt-1.5 text-pretty text-sm text-slate-500">Masuk dengan akun yang diberikan operator.</p>
                </div>

                <!-- Status -->
                <div
                    v-if="status"
                    class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-600">
                            Email
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center">
                                <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.mail" />
                                </svg>
                            </span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="nama@sekolah.sch.id"
                                :class="[
                                    'w-full rounded-xl border bg-white py-3 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    form.errors.email ? 'border-red-400 focus:border-red-400 focus:ring-red-400/20' : 'border-slate-200',
                                ]"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label for="password" class="text-xs font-semibold text-slate-600">Password</label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs text-emerald-600 hover:underline"
                            >
                                Lupa password?
                            </Link>
                        </div>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center">
                                <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.lock" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                :class="[
                                    'w-full rounded-xl border bg-white py-3 pl-10 pr-11 text-sm text-slate-800 placeholder-slate-300 outline-none transition-[border-color,box-shadow] duration-150',
                                    'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20',
                                    form.errors.password ? 'border-red-400 focus:border-red-400 focus:ring-red-400/20' : 'border-slate-200',
                                ]"
                            />
                            <button
                                type="button"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-3.5 flex items-center text-slate-400 transition-[color] duration-150 hover:text-slate-600"
                                tabindex="-1"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="showPassword ? iconPaths.eyeOff : iconPaths.eye" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember -->
                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="size-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400/30"
                        />
                        <span class="text-sm text-slate-600">Ingat saya</span>
                    </label>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 py-3 text-sm font-semibold text-white shadow-md transition-[background-color] duration-150 hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg
                            v-if="form.processing"
                            class="size-4 animate-spin"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths.spinner" />
                        </svg>
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <Link href="/" class="text-xs text-slate-400 transition-[color] duration-150 hover:text-slate-600">
                        ← Kembali ke halaman utama
                    </Link>
                </div>
            </div>
        </div>

    </div>
</template>
