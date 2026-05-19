<template>
    <div class="nx-connection-dot" :class="stateClass" :title="tooltip" />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    state: {
        type: String,
        required: true,
        validator: (v) => ['connecting', 'connected', 'disconnected', 'error'].includes(v),
    },
});

const stateClass = computed(() => `is-${props.state}`);

const tooltip = computed(() => {
    switch (props.state) {
        case 'connecting': return 'Reconnecting…';
        case 'connected': return 'Connected to Reverb';
        case 'disconnected': return 'Disconnected';
        case 'error': return 'Connection error';
        default: return '';
    }
});
</script>

<style scoped>
.nx-connection-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.is-connecting {
    background: #F59E0B;
    animation: pulse 1.5s ease-in-out infinite;
}

.is-connected {
    background: #10B981;
    animation: breathe 3s ease-in-out infinite;
}

.is-disconnected {
    background: #EF4444;
}

.is-error {
    background: #EF4444;
    animation: jitter 100ms linear infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

@keyframes breathe {
    0%, 100% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.15); opacity: 1; }
}

@keyframes jitter {
    0% { transform: translateX(-1px); }
    25% { transform: translateX(1px); }
    50% { transform: translateX(-1px); }
    75% { transform: translateX(1px); }
    100% { transform: translateX(-1px); }
}
</style>
