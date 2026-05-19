<template>
    <div class="nx-ai-pulse" :class="stateClass" :style="computedStyle" />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    state: {
        type: String,
        required: true,
        validator: (v) => ['idle', 'thinking', 'speaking', 'error'].includes(v),
    },
    size: { type: Number, default: 24 },
    amplitude: { type: Number, default: 0 },
});

const stateClass = computed(() => `is-${props.state}`);

const computedStyle = computed(() => ({
    width: `${props.size}px`,
    height: `${props.size}px`,
}));
</script>

<style scoped>
.nx-ai-pulse {
    border-radius: 50%;
    background: rgba(22, 27, 34, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.is-idle {
    animation: breathe 4s ease-in-out infinite;
    opacity: 0.6;
}

.is-thinking {
    background: conic-gradient(from 0deg, #6366F1, #8B5CF6, #6366F1);
    animation: rotate 1s linear infinite;
}

.is-speaking {
    animation: speak 0.5s ease-in-out infinite alternate;
}

.is-error {
    background: #EF4444;
    animation: jitter 100ms linear infinite;
}

@keyframes breathe {
    0%, 100% { transform: scale(1); opacity: 0.4; }
    50% { transform: scale(1.05); opacity: 0.7; }
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes speak {
    from { transform: scale(1); }
    to { transform: scale(1.1); }
}

@keyframes jitter {
    0% { transform: translateX(-2px); }
    25% { transform: translateX(2px); }
    50% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
    100% { transform: translateX(-2px); }
}
</style>
