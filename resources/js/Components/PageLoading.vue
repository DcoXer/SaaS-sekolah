<script setup>
/**
 * PageLoading — Reusable loading indicator.
 *
 * Usage examples:
 *   <PageLoading />                              inline spinner (medium)
 *   <PageLoading size="sm" />                    small inline spinner
 *   <PageLoading variant="dots" />               bouncing dots
 *   <PageLoading overlay />                      full-screen dark overlay
 *   <PageLoading overlay text="Menyimpan..." />  overlay + custom text
 *   <PageLoading :show="form.processing" overlay /> toggle visibility
 *
 * Props:
 *   show     Boolean  — visibility toggle (default true)
 *   overlay  Boolean  — render as fullscreen overlay
 *   variant  String   — 'spinner' | 'dots' | 'pulse'
 *   size     String   — 'sm' | 'md' | 'lg'
 *   text     String   — optional label below the animation
 *   color    String   — 'indigo' | 'white' | 'slate'  (auto when overlay)
 */

const props = defineProps({
    show:    { type: Boolean, default: true },
    overlay: { type: Boolean, default: false },
    variant: { type: String,  default: 'spinner' },   // spinner | dots | pulse
    size:    { type: String,  default: 'md' },         // sm | md | lg
    text:    { type: String,  default: '' },
    color:   { type: String,  default: '' },           // auto if empty
});

// Size map
const sizePx = { sm: 20, md: 32, lg: 48 };
const sizeClass = {
    spinner: { sm: 'size-5', md: 'size-8', lg: 'size-12' },
    dots:    { sm: 'size-1.5', md: 'size-2.5', lg: 'size-4' },
    pulse:   { sm: 'size-8',  md: 'size-14', lg: 'size-20' },
};
const textClass = { sm: 'text-xs', md: 'text-sm', lg: 'text-base' };

// Resolved color: when overlay default to white, else indigo
const resolvedColor = props.color || (props.overlay ? 'white' : 'indigo');

const spinnerColor = {
    indigo: 'border-indigo-600 border-t-transparent',
    white:  'border-white/80 border-t-transparent',
    slate:  'border-slate-400 border-t-transparent',
};
const dotColor = {
    indigo: 'bg-indigo-500',
    white:  'bg-white/90',
    slate:  'bg-slate-400',
};
const pulseColor = {
    indigo: 'bg-indigo-100 text-indigo-500',
    white:  'bg-white/20 text-white',
    slate:  'bg-slate-100 text-slate-400',
};
const labelColor = {
    indigo: 'text-slate-500',
    white:  'text-white/80',
    slate:  'text-slate-400',
};
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
    >
        <div v-if="show">

            <!-- ── Overlay mode ── -->
            <div v-if="overlay"
                class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-4">

                    <!-- Spinner -->
                    <div v-if="variant === 'spinner'"
                        :class="[sizeClass.spinner[size], spinnerColor[resolvedColor]]"
                        class="animate-spin rounded-full border-4">
                    </div>

                    <!-- Dots -->
                    <div v-else-if="variant === 'dots'" class="flex items-center gap-1.5">
                        <div v-for="i in 3" :key="i"
                            :class="[sizeClass.dots[size], dotColor[resolvedColor]]"
                            :style="{ animationDelay: `${(i - 1) * 160}ms` }"
                            class="animate-bounce rounded-full">
                        </div>
                    </div>

                    <!-- Pulse icon -->
                    <div v-else-if="variant === 'pulse'"
                        :class="[sizeClass.pulse[size], pulseColor[resolvedColor]]"
                        class="flex animate-pulse items-center justify-center rounded-2xl">
                        <svg class="size-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </div>

                    <p v-if="text" :class="[textClass[size], 'font-medium text-white/90']">{{ text }}</p>
                </div>
            </div>

            <!-- ── Inline mode ── -->
            <div v-else class="flex flex-col items-center justify-center gap-2">

                <!-- Spinner -->
                <div v-if="variant === 'spinner'"
                    :class="[sizeClass.spinner[size], spinnerColor[resolvedColor]]"
                    class="animate-spin rounded-full border-4">
                </div>

                <!-- Dots -->
                <div v-else-if="variant === 'dots'" class="flex items-center gap-1.5">
                    <div v-for="i in 3" :key="i"
                        :class="[sizeClass.dots[size], dotColor[resolvedColor]]"
                        :style="{ animationDelay: `${(i - 1) * 160}ms` }"
                        class="animate-bounce rounded-full">
                    </div>
                </div>

                <!-- Pulse icon -->
                <div v-else-if="variant === 'pulse'"
                    :class="[sizeClass.pulse[size], pulseColor[resolvedColor]]"
                    class="flex animate-pulse items-center justify-center rounded-2xl">
                    <svg class="size-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                    </svg>
                </div>

                <p v-if="text" :class="[textClass[size], labelColor[resolvedColor]]">{{ text }}</p>
            </div>

        </div>
    </Transition>
</template>
