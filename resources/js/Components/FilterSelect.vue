<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: { default: '' },
    options:    { type: Array,  default: () => [] }, // [{ value, label }]
});
const emit = defineEmits(['update:modelValue', 'change']);

const open = ref(false);
const el   = ref(null);

const selected = computed(() => props.options.find(o => String(o.value) === String(props.modelValue)));

const select = (val) => {
    emit('update:modelValue', val);
    emit('change', val);
    open.value = false;
};

const handleOutside = (e) => {
    if (open.value && el.value && !el.value.contains(e.target)) open.value = false;
};
onMounted(()  => document.addEventListener('mousedown', handleOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleOutside));
</script>

<template>
    <div ref="el" class="relative">
        <!-- Trigger button -->
        <button
            type="button"
            @click="open = !open"
            class="flex items-center gap-2 rounded-xl border bg-slate-50 py-2 pl-3.5 pr-3 text-sm outline-none transition-[border-color,box-shadow] hover:border-slate-300"
            :class="open
                ? 'border-emerald-400 bg-white ring-2 ring-emerald-400/20 text-slate-800'
                : 'border-slate-200 text-slate-600'"
        >
            <span class="whitespace-nowrap">{{ selected ? selected.label : (options[0]?.label ?? 'Pilih') }}</span>
            <svg
                :class="['size-3.5 text-slate-400 transition-transform duration-200', open ? 'rotate-180' : '']"
                fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>

        <!-- Dropdown panel -->
        <Transition
            enter-from-class="opacity-0 scale-95"
            enter-active-class="transition-[transform,opacity] duration-150 ease-out origin-top"
            leave-to-class="opacity-0 scale-95"
            leave-active-class="transition-[transform,opacity] duration-100 ease-in origin-top"
        >
            <div
                v-if="open"
                class="absolute left-0 top-full z-40 mt-1.5 min-w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg ring-1 ring-black/5"
            >
                <div class="py-1">
                    <button
                        v-for="opt in options"
                        :key="opt.value"
                        type="button"
                        @click="select(opt.value)"
                        class="flex w-full items-center gap-2.5 px-3.5 py-2 text-sm transition-colors duration-100"
                        :class="String(modelValue) === String(opt.value)
                            ? 'bg-emerald-50 text-emerald-700 font-semibold'
                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800'"
                    >
                        <span class="size-1.5 shrink-0 rounded-full transition-colors"
                            :class="String(modelValue) === String(opt.value) ? 'bg-emerald-500' : 'bg-transparent'"/>
                        {{ opt.label }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
