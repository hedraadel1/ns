<template>
    <button class="nx-queue-pill" :class="colorClass" @click="$emit('click')">
        {{ count }}
    </button>
</template>

<script setup>
defineProps({
    count: { type: Number, default: 0 },
    hasFailures: { type: Boolean, default: false },
});

defineEmits(['click']);

const colorClass = computed(() => {
    if (props.count === 0) return 'muted';
    if (props.hasFailures) return 'error';
    return 'active';
});
</script>

<style scoped>
.nx-queue-pill {
    background: rgba(22, 27, 34, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 9999px;
    padding: 2px 10px;
    font-family: 'JetBrains Mono', monospace;
    font-variant-numeric: tabular-nums;
    font-size: 12px;
    min-height: 24px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.nx-queue-pill.muted {
    color: rgba(255, 255, 255, 0.4);
}

.nx-queue-pill.active {
    color: #007AFF;
}

.nx-queue-pill.error {
    color: #EF4444;
}

.nx-queue-pill:hover {
    background: rgba(255, 255, 255, 0.05);
}
</style>
