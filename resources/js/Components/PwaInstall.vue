<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const show        = ref(false);
const installing  = ref(false);
let   deferredPrompt = null;

const DISMISS_KEY = 'pwa_install_dismissed';

const onBeforeInstall = (e) => {
    e.preventDefault();
    if (localStorage.getItem(DISMISS_KEY)) return;
    deferredPrompt = e;
    show.value = true;
};

const install = async () => {
    if (!deferredPrompt) return;
    installing.value = true;
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    if (outcome === 'accepted') {
        show.value = false;
    }
    installing.value  = false;
    deferredPrompt    = null;
};

const dismiss = () => {
    show.value = false;
    localStorage.setItem(DISMISS_KEY, '1');
};

onMounted(()  => window.addEventListener('beforeinstallprompt', onBeforeInstall));
onUnmounted(() => window.removeEventListener('beforeinstallprompt', onBeforeInstall));
</script>

<template>
    <Transition
        enter-from-class="opacity-0 translate-y-4"
        enter-active-class="transition-[transform,opacity] duration-300 ease-out"
        leave-to-class="opacity-0 translate-y-4"
        leave-active-class="transition-[transform,opacity] duration-200 ease-in"
    >
        <div
            v-if="show"
            class="fixed bottom-4 left-4 right-4 z-50 sm:left-auto sm:right-5 sm:w-80"
        >
            <div class="flex items-center gap-3 rounded-2xl border border-primary-100 bg-white p-4 shadow-xl ring-1 ring-black/5">
                <!-- Icon -->
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-500 shadow-sm">
                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>

                <!-- Text -->
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800">Install Aplikasi</p>
                    <p class="mt-0.5 text-xs text-slate-500">Akses lebih cepat dari layar utama</p>
                </div>

                <!-- Actions -->
                <div class="flex shrink-0 items-center gap-1.5">
                    <button
                        @click="install"
                        :disabled="installing"
                        class="rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-primary-600 disabled:opacity-60"
                    >
                        {{ installing ? 'Menginstall...' : 'Install' }}
                    </button>
                    <button
                        @click="dismiss"
                        class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
