<template>
  <div :class="['animated-loader', `loader-${animation}`]">
    <div v-if="animation === 'dots'" class="dots-loader">
      <span></span><span></span><span></span>
    </div>
    <div v-else-if="animation === 'pulse'" class="pulse-loader">
      <span></span><span></span><span></span>
    </div>
    <div v-else-if="animation === 'bar'" class="bar-loader">
      <span></span><span></span><span></span><span></span><span></span>
    </div>
    <div v-else-if="animation === 'ring'" class="ring-loader">
      <div class="ring"></div>
    </div>
    <div v-else class="spinner-loader">
      <div class="spinner"></div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  animation: {
    type: String,
    default: 'spinner',
    validator: (v) => ['spinner', 'dots', 'pulse', 'bar', 'ring'].includes(v),
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
})
</script>

<style scoped>
.animated-loader {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

/* Dots Loader */
.dots-loader {
  display: flex;
  gap: 0.5rem;
}

.dots-loader span {
  width: 12px;
  height: 12px;
  background: var(--color-primary);
  border-radius: 50%;
  animation: dots-bounce 1.4s infinite ease-in-out both;
}

.dots-loader span:nth-child(1) { animation-delay: -0.32s; }
.dots-loader span:nth-child(2) { animation-delay: -0.16s; }

@keyframes dots-bounce {
  0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
  40% { transform: scale(1); opacity: 1; }
}

/* Pulse Loader */
.pulse-loader {
  display: flex;
  gap: 0.5rem;
}

.pulse-loader span {
  width: 16px;
  height: 16px;
  background: var(--color-primary);
  border-radius: 50%;
  animation: pulse-scale 1.2s infinite ease-in-out both;
}

.pulse-loader span:nth-child(1) { animation-delay: -0.4s; }
.pulse-loader span:nth-child(2) { animation-delay: -0.2s; }

@keyframes pulse-scale {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
  40% { transform: scale(1); opacity: 1; }
}

/* Bar Loader */
.bar-loader {
  display: flex;
  gap: 0.25rem;
  align-items: flex-end;
  height: 24px;
}

.bar-loader span {
  width: 4px;
  background: var(--color-primary);
  border-radius: 2px;
  animation: bar-stretch 1s infinite ease-in-out;
}

.bar-loader span:nth-child(1) { height: 40%; animation-delay: 0s; }
.bar-loader span:nth-child(2) { height: 70%; animation-delay: 0.1s; }
.bar-loader span:nth-child(3) { height: 100%; animation-delay: 0.2s; }
.bar-loader span:nth-child(4) { height: 70%; animation-delay: 0.3s; }
.bar-loader span:nth-child(5) { height: 40%; animation-delay: 0.4s; }

@keyframes bar-stretch {
  0%, 100% { transform: scaleY(0.5); }
  50% { transform: scaleY(1); }
}

/* Ring Loader */
.ring-loader {
  position: relative;
  width: 48px;
  height: 48px;
}

.ring {
  width: 100%;
  height: 100%;
  border: 4px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: ring-spin 1s linear infinite;
}

@keyframes ring-spin {
  to { transform: rotate(360deg); }
}

/* Spinner Loader */
.spinner-loader {
  width: 40px;
  height: 40px;
}

.spinner {
  width: 100%;
  height: 100%;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
