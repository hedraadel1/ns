<template>
    <div class="nx-job-rail" :class="{ active }" :style="railStyle" />
</template>

<script setup>
defineProps({
    progress: { type: Number, default: 0 },
    active: { type: Boolean, default: false },
});

const railStyle = computed(() => ({
    '--progress': `${props.progress}%`,
    width: props.active ? '100%' : '0%',
}));
</script>

<style scoped>
.nx-job-rail {
    position: fixed;
    top: 0;
    left: 0;
    height: 2px;
    background: linear-gradient(90deg, #007AFF, #007AFF var(--progress), transparent var(--progress));
    transition: width 300ms ease, opacity 400ms ease;
    z-index: 100;
    opacity: 0;
}

.nx-job-rail.active {
    opacity: 1;
    box-shadow: 2px 0 8px #007AFF;
}
</style>
