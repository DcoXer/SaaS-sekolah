<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: { default: '' },
    options:    { type: Array,    default: () => [] }, // [{ value, label }]
    block:      { type: Boolean,  default: false },    // full-width trigger
    disabled:   { type: Boolean,  default: false },
    hasError:   { type: Boolean,  default: false },
});
const emit = defineEmits(['update:modelValue', 'change']);

const open = ref(false);
const el   = ref(null);

const selected = computed(() => props.options.find(o => String(o.value) === String(props.modelValue)));

const select = (val) => {
    if (props.disabled) return;
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
            @click="!disabled && (open = !open)"
            :disabled="disabled"
            class="flex items-center gap-2 rounded-xl border bg-slate-50 py-2 pl-3.5 pr-3 text-sm outline-none transition-[border-color,box-shadow]"
            :class="[
                disabled  ? 'cursor-not-allowed opacity-50 border-slate-200 text-slate-400'
                : hasError ? 'border-red-400 bg-white text-slate-800 ring-2 ring-red-400/20'
                : open     ? 'border-primary-400 bg-white ring-2 ring-primary-400/20 text-slate-800'
                :             'border-slate-200 text-slate-600 hover:border-slate-300',
                block ? 'w-full justify-between' : '',
            ]"
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
                class="absolute left-0 top-full z-50 mt-1.5 min-w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg ring-1 ring-black/5"
            >
                <div class="max-h-60 overflow-y-auto py-1">
                    <button
                        v-for="opt in options"
                        :key="opt.value"
                        type="button"
                        @click="select(opt.value)"
                        class="flex w-full items-center gap-2.5 px-3.5 py-2 text-sm transition-colors duration-100"
                        :class="String(modelValue) === String(opt.value)
                            ? 'bg-primary-50 text-primary-700 font-semibold'
                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800'"
                    >
                        <span class="size-1.5 shrink-0 rounded-full transition-colors"
                            :class="String(modelValue) === String(opt.value) ? 'bg-primary-500' : 'bg-transparent'"/>
                        {{ opt.label }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
