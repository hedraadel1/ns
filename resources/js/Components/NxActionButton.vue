<template>
  <button
    class="nx-action-button"
    :class="[variant, { loading, disabled, [optimisticState]: optimisticState }]"
    :disabled="disabled || loading"
    @click="handleClick"
  >
    <span v-if="loading" class="loading-spinner">
      <slot name="loading">
        <NxLiveLoader :taskId="null" :status="'loading'" />
      </slot>
    </span>
    <span v-else class="button-content">
      <slot />
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue';
import NxLiveLoader from './NxLiveLoader.vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'danger', 'ghost'].includes(v),
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
    default: null,
    validator: (v) => v === null || ['pending', 'success', 'error'].includes(v),
  },
});

const emit = defineEmits(['click', 'update:optimisticState']);

function handleClick(event) {
  if (props.disabled || props.loading) return;
  emit('click', event);
}
</script>

<style scoped>
.nx-action-button {
  min-height: 44px;
  min-width: 44px;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 150ms ease, opacity 150ms ease, box-shadow 150ms ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: none;
  background: #007AFF;
  color: white;
  transform: translateZ(0);
}

.nx-action-button:hover:not(:disabled) {
  opacity: 0.95;
  transform: scale(0.98);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.16);
}

.nx-action-button:active:not(:disabled) {
  transform: scale(0.96);
}

.nx-action-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.nx-action-button.secondary {
  background: transparent;
  color: #007AFF;
  border: 1px solid rgba(0, 122, 255, 0.3);
}

.nx-action-button.danger {
  background: #EF4444;
  color: white;
}

.nx-action-button.ghost {
  background: transparent;
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.7));
}

.nx-action-button.pending {
  opacity: 0.7;
}

.nx-action-button.success {
  background: #10B981 !important;
  color: white;
}

.nx-action-button.error {
  background: #EF4444 !important;
  color: white;
  animation: shake 100ms;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}

.loading-spinner {
  display: inline-flex;
  align-items: center;
}
</style>
