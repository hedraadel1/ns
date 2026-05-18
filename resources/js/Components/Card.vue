<template>
  <div :class="['card', `card-${variant}`, { 'card-hover': hover }]">
    <div v-if="$slots.header" class="card-header">
      <slot name="header"></slot>
    </div>
    <div v-if="$slots.title || title" class="card-title">
      <slot name="title">
        <h3>{{ title }}</h3>
      </slot>
    </div>
    <div class="card-body">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  title: {
    type: String,
    default: '',
  },
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'elevated', 'glass', 'bordered'].includes(v),
  },
  hover: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.card {
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 1rem;
  transition: all var(--transition-fast);
}

.card-hover:hover {
  border-color: var(--color-border-hover);
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

/* Variants */
.card-default {
  background: var(--color-bg-secondary);
}

.card-elevated {
  background: var(--color-bg-tertiary);
  box-shadow: var(--shadow-sm);
}

.card-glass {
  background: var(--color-bg-glass);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.card-bordered {
  background: transparent;
  border: 1px solid var(--color-border);
}

.card-header {
  margin-bottom: 0.75rem;
}

.card-title {
  margin-bottom: 0.75rem;
}

.card-title h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.card-body {
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  line-height: 1.5;
}

.card-footer {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--color-border);
}
</style>
