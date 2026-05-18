<template>
  <div :class="['loader', `loader-${size}`, { 'loader-inline': inline }]">
    <div class="loader-spinner"></div>
    <span v-if="label" class="loader-label">{{ label }}</span>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  label: {
    type: String,
    default: '',
  },
  inline: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem;
}

.loader-inline {
  flex-direction: row;
  padding: 0;
}

.loader-spinner {
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.loader-sm .loader-spinner {
  width: 20px;
  height: 20px;
  border-width: 2px;
}

.loader-md .loader-spinner {
  width: 32px;
  height: 32px;
  border-width: 3px;
}

.loader-lg .loader-spinner {
  width: 48px;
  height: 48px;
  border-width: 4px;
}

.loader-label {
  font-size: 0.875rem;
  color: var(--color-text-muted);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
