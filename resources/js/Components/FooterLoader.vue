<template>
  <div v-if="visible" class="footer-loader">
    <div class="footer-loader-bar">
      <div
        class="footer-loader-progress"
        :style="{ width: progress + '%' }"
      ></div>
    </div>
    <div class="footer-loader-info">
      <span class="footer-loader-label">{{ label }}</span>
      <span class="footer-loader-percent">{{ progress }}%</span>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  progress: {
    type: Number,
    default: 0,
    validator: (v) => v >= 0 && v <= 100,
  },
  label: {
    type: String,
    default: 'Processing...',
  },
})
</script>

<style scoped>
.footer-loader {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: var(--color-bg-secondary);
  border-top: 1px solid var(--color-border);
  padding: 0.5rem 1rem;
  z-index: 1000;
}

.footer-loader-bar {
  height: 4px;
  background: var(--color-bg-tertiary);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 0.375rem;
}

.footer-loader-progress {
  height: 100%;
  background: linear-gradient(90deg, var(--color-primary), var(--color-accent-blue));
  transition: width 0.3s ease;
  border-radius: 2px;
}

.footer-loader-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
}

.footer-loader-label {
  color: var(--color-text-muted);
}

.footer-loader-percent {
  color: var(--color-primary);
  font-weight: 500;
}
</style>
