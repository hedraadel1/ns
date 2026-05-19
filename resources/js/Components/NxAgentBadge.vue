<template>
    <button class="nx-agent-badge" @click="$emit('click')">
        <NxAiPulse :state="pulseState" :size="12" />
        <span class="count">{{ count }}</span>
    </button>
</template>

<script setup>
import { computed } from 'vue';
import NxAiPulse from './NxAiPulse.vue';

defineProps({
    count: { type: Number, default: 0 },
});

defineEmits(['click']);

const pulseState = computed(() => (props.count > 0 ? 'thinking' : 'idle'));
</script>

<style scoped>
.nx-agent-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: rgba(22, 27, 34, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 9999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: var(--color-text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
}

.nx-agent-badge:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--color-text-primary);
}

.count {
    font-variant-numeric: tabular-nums;
}
</style>
