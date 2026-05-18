<template>
  <div class="hover-effects">
    <slot></slot>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  lift: {
    type: Boolean,
    default: true,
  },
  glow: {
    type: Boolean,
    default: true,
  },
  scale: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.hover-effects {
  display: contents;
}

.hover-effects :deep(.hover-lift) {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-effects :deep(.hover-lift:hover) {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.hover-effects :deep(.hover-glow) {
  transition: box-shadow 0.2s ease;
}

.hover-effects :deep(.hover-glow:hover) {
  box-shadow: var(--shadow-glow);
}

.hover-effects :deep(.hover-scale) {
  transition: transform 0.2s ease;
}

.hover-effects :deep(.hover-scale:hover) {
  transform: scale(1.02);
}

.hover-effects :deep(.hover-combo) {
  transition: all 0.2s ease;
}

.hover-effects :deep(.hover-combo:hover) {
  transform: translateY(-2px) scale(1.01);
  box-shadow: var(--shadow-md), var(--shadow-glow);
}
</style>
