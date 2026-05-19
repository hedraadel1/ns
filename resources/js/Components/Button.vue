<template>
  <button
    :class="[
      'btn',
      `btn-${variant}`,
      `btn-${size}`,
      optimistic ? `btn-optimistic-${optimisticState}` : '',
      { 'btn-loading': loading },
      { 'btn-disabled': disabled || loading },
    ]"
    :disabled="disabled || loading"
    @click="handleClick"
  >
    <span v-if="loading" class="btn-spinner">
      <slot name="loading"></slot>
    </span>
    <template v-else>
      <slot></slot>
    </template>
  </button>
</template>

<script setup>
import { computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'ghost', 'danger'].includes(v),
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  optimistic: {
    type: Boolean,
    default: false,
  },
  optimisticState: {
    type: String,
    default: 'pending',
    validator: (value) => ['pending', 'success', 'error'].includes(value),
  },
})

const emit = defineEmits(['click', 'update:optimisticState'])

const optimisticState = computed({
  get: () => props.optimisticState,
  set: (value) => emit('update:optimisticState', value),
})

function handleClick(e) {
  if (!props.disabled && !props.loading) {
    emit('click', e)
  }
}
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  min-width: 44px;
  gap: 0.5rem;
  font-weight: 600;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 180ms ease;
  border: 1px solid transparent;
  white-space: nowrap;
  padding: 0.75rem 1rem;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Variants */
.btn-primary {
  background: #007aff;
  color: #ffffff;
  border-color: #007aff;
}

.btn-primary:hover:not(:disabled) {
  background: #0064db;
  border-color: #0064db;
}

.btn-secondary {
  background: rgba(255,255,255,0.06);
  color: #e2e8f0;
  border-color: rgba(148,163,184,0.18);
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(255,255,255,0.1);
  border-color: rgba(148,163,184,0.3);
}

.btn-ghost {
  background: transparent;
  color: #cbd5e1;
  border-color: transparent;
}

.btn-ghost:hover:not(:disabled) {
  background: rgba(255,255,255,0.05);
  color: #ffffff;
}

.btn-danger {
  background: #ef4444;
  color: #ffffff;
  border-color: #ef4444;
}

.btn-danger:hover:not(:disabled) {
  opacity: 0.95;
}

.btn-optimistic-success {
  animation: optimistic-success 1.2s ease forwards;
}

.btn-optimistic-error {
  background: #ef4444 !important;
  border-color: #ef4444 !important;
  animation: shake 0.4s ease;
}

.btn-optimistic-pending {
  opacity: 0.95;
}

/* Sizes */
.btn-sm {
  padding: 0.5rem 0.9rem;
  font-size: 0.8125rem;
}

.btn-md {
  padding: 0.75rem 1.1rem;
  font-size: 0.9375rem;
}

.btn-lg {
  padding: 0.9rem 1.4rem;
  font-size: 1rem;
}

/* Loading Spinner */
.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes optimistic-success {
  0%, 100% {
    background: #007aff;
  }
  50% {
    background: #10b981;
  }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-4px); }
  40%, 80% { transform: translateX(4px); }
}
</style>
