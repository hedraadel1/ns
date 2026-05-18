<template>
  <header class="mobile-header">
    <button class="menu-btn" @click="$emit('menu-click')">
      <span class="hamburger"></span>
    </button>
    <h1 class="mobile-title">{{ title }}</h1>
    <div class="header-actions">
      <slot name="actions"></slot>
    </div>
  </header>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

defineProps({
  title: {
    type: String,
    default: 'Nexus',
  },
})

defineEmits(['menu-click'])
</script>

<style scoped>
.mobile-header {
  display: none;
  position: sticky;
  top: 0;
  left: 0;
  right: 0;
  height: var(--topbar-height);
  background: var(--color-bg-secondary);
  border-bottom: 1px solid var(--color-border);
  padding: 0 1rem;
  align-items: center;
  justify-content: space-between;
  z-index: 100;
}

.menu-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.hamburger {
  display: block;
  width: 20px;
  height: 2px;
  background: var(--color-text-primary);
  position: relative;
}

.hamburger::before,
.hamburger::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 2px;
  background: var(--color-text-primary);
  left: 0;
}

.hamburger::before { top: -6px; }
.hamburger::after { top: 6px; }

.mobile-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

@media (max-width: 768px) {
  .mobile-header {
    display: flex;
  }
}
</style>
