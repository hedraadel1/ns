<template>
    <Transition name="slide-down">
        <div v-if="visible" class="nx-rate-limit-banner">
            <span class="message">
                <AlertTriangleIcon :size="16" />
                {{ provider }} rate limited. Resets in {{ countdown }}.
            </span>
            <button class="switch-btn" @click="$emit('switch-provider')">Switch Provider</button>
            <button class="dismiss-btn" @click="$emit('dismiss')">
                <XIcon :size="14" />
            </button>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { AlertTriangle as AlertTriangleIcon, X as XIcon } from 'lucide-vue-next';

const props = defineProps({
    provider: { type: String, default: '' },
    resetAt: { type: Date, default: () => new Date(Date.now() + 3600000) },
    visible: { type: Boolean, default: false },
});

defineEmits(['dismiss', 'switch-provider']);

const countdown = ref('00:00');
let timer = null;

const updateCountdown = () => {
    const now = new Date();
    const diff = props.resetAt - now;
    if (diff <= 0) {
        countdown.value = '00:00';
        return;
    }
    const mins = Math.floor(diff / 60000);
    const secs = Math.floor((diff % 60000) / 1000);
    countdown.value = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

onMounted(() => {
    updateCountdown();
    timer = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<style scoped>
.nx-rate-limit-banner {
    height: 36px;
    background: rgba(245, 158, 11, 0.15);
    border-bottom: 1px solid rgba(245, 158, 11, 0.3);
    color: #F59E0B;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 0 16px;
    font-size: 13px;
    animation: shake 5s ease-in-out infinite;
}

.message {
    display: flex;
    align-items: center;
    gap: 8px;
}

.switch-btn {
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: #F59E0B;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.switch-btn:hover {
    background: rgba(245, 158, 11, 0.3);
}

.dismiss-btn {
    background: none;
    border: none;
    color: #F59E0B;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.dismiss-btn:hover {
    background: rgba(245, 158, 11, 0.2);
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 250ms ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}
</style>
