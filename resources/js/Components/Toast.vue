<template>
  <Teleport to="body">
    <div class="toast-container">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="['toast', `toast-${toast.type}`]"
          @click="dismiss(toast.id)"
        >
          <span class="toast-icon">{{ getIcon(toast.type) }}</span>
          <div class="toast-content">
            <p v-if="toast.title" class="toast-title">{{ toast.title }}</p>
            <p class="toast-message">{{ toast.message }}</p>
          </div>
          <button class="toast-close" @click.stop="dismiss(toast.id)">✕</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const toasts = ref([])
let nextId = 1
let timers = new Map()

const emit = defineEmits(['toast'])

const icons = {
  success: '✓',
  error: '✕',
  warning: '⚠',
  info: 'ℹ',
}

function getIcon(type) {
  return icons[type] || icons.info
}

function addToast(toast) {
  const id = nextId++
  const newToast = {
    id,
    type: 'info',
    duration: 5000,
    ...toast,
  }
  toasts.value.push(newToast)

  if (newToast.duration > 0) {
    const timer = setTimeout(() => dismiss(id), newToast.duration)
    timers.set(id, timer)
  }

  emit('toast', newToast)
  return id
}

function dismiss(id) {
  const index = toasts.value.findIndex((t) => t.id === id)
  if (index !== -1) {
    toasts.value.splice(index, 1)
    const timer = timers.get(id)
    if (timer) {
      clearTimeout(timer)
      timers.delete(id)
    }
  }
}

function success(message, title = '') {
  return addToast({ type: 'success', message, title })
}

function error(message, title = '') {
  return addToast({ type: 'error', message, title })
}

function warning(message, title = '') {
  return addToast({ type: 'warning', message, title })
}

function info(message, title = '') {
  return addToast({ type: 'info', message, title })
}

// Expose methods globally
onMounted(() => {
  window.$toast = { success, error, warning, info, dismiss }
})

onUnmounted(() => {
  delete window.$toast
  timers.forEach((timer) => clearTimeout(timer))
  timers.clear()
})
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-width: 400px;
  width: 100%;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  pointer-events: auto;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.toast:hover {
  transform: translateX(-4px);
  box-shadow: var(--shadow-lg), var(--shadow-glow);
}

.toast-icon {
  font-size: 1.125rem;
  flex-shrink: 0;
}

.toast-success .toast-icon { color: var(--color-success); }
.toast-error .toast-icon { color: var(--color-error); }
.toast-warning .toast-icon { color: var(--color-warning); }
.toast-info .toast-icon { color: var(--color-info); }

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-title {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.toast-message {
  margin: 0;
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 0;
  font-size: 0.875rem;
  line-height: 1;
  flex-shrink: 0;
}

.toast-close:hover {
  color: var(--color-text-primary);
}

/* Toast Transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}

@media (max-width: 480px) {
  .toast-container {
    left: 1rem;
    right: 1rem;
    max-width: none;
  }
}
</style>
