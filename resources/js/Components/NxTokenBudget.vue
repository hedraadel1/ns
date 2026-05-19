<template>
    <svg class="nx-token-budget" viewBox="0 0 24 24" @click="$emit('click')">
        <circle class="bg-ring" cx="12" cy="12" r="10" />
        <circle class="fill-ring" cx="12" cy="12" r="10" :style="ringStyle" />
        <text x="12" y="16" text-anchor="middle" class="ring-text">{{ percentage }}%</text>
    </svg>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    used: { type: Number, default: 0 },
    budget: { type: Number, default: 6000 },
});

defineEmits(['click']);

const percentage = computed(() => Math.round((props.used / props.budget) * 100));

const ringColor = computed(() => {
    const p = props.used / props.budget;
    if (p < 0.7) return '#007AFF';
    if (p < 0.9) return '#F59E0B';
    return '#EF4444';
});

const circumference = 2 * Math.PI * 10; // ~62.83

const ringStyle = computed(() => ({
    stroke: ringColor.value,
    strokeDashoffset: circumference - (percentage.value / 100) * circumference,
}));
</script>

<style scoped>
.nx-token-budget {
    width: 24px;
    height: 24px;
    cursor: pointer;
    transform: rotate(-90deg);
}

.bg-ring {
    fill: none;
    stroke: rgba(255, 255, 255, 0.1);
    stroke-width: 2;
}

.fill-ring {
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    transition: stroke-dashoffset 300ms ease, stroke 300ms ease;
}

.ring-text {
    font-size: 7px;
    fill: white;
    font-family: 'JetBrains Mono', monospace;
    transform: rotate(90deg);
    transform-origin: center;
}
</style>
