<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    data: { type: [Object, Array], required: true },
});

let el = null;

onMounted(() => {
    el = document.createElement('script');
    el.type = 'application/ld+json';
    el.textContent = JSON.stringify(props.data);
    document.head.appendChild(el);
});

onUnmounted(() => el?.remove());
</script>

<template></template>
