<template>
    <div v-if="percent > 60" class="nx-memory-pressure" :class="colorClass">
        {{ percent }}%
    </div>
</template>

<script setup>
defineProps({
    percent: { type: Number, default: 0 },
});

const colorClass = computed(() => {
    if (props.percent > 80) return 'critical';
    return 'warning';
});
</script>

<style scoped>
.nx-memory-pressure {
    background: rgba(22, 27, 34, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 9999px;
    padding: 2px 8px;
    font-size: 11px;
    font-family: 'JetBrains Mono', monospace;
    font-variant-numeric: tabular-nums;
}

.warning {
    color: #F59E0B;
    border-color: rgba(245, 158, 11, 0.3);
}

.critical {
    color: #EF4444;
    border-color: rgba(239, 68, 68, 0.3);
    animation: pulse-red 2s ease-in-out infinite;
}

@keyframes pulse-red {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    50% { box-shadow: 0 0 8px 2px rgba(239, 68, 68, 0.4); }
}
</style>
