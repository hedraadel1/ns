<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="visible" class="global-loader-overlay">
        <div class="global-loader-content">
          <Loader :size="size" :label="label" />
          <p v-if="message" class="global-loader-message">{{ message }}</p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { defineProps } from 'vue'
import Loader from './Loader.vue'

defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: 'Loading...',
  },
  message: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'lg',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
})
</script>

<style scoped>
.global-loader-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(10, 10, 10, 0.85);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.global-loader-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 2rem;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
}

.global-loader-message {
  margin: 0;
  font-size: 0.875rem;
  color: var(--color-text-muted);
}

/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
