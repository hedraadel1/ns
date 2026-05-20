<template>
  <section class="nx-theme-switcher glass hoverable">
    <h3>Theme</h3>
    <div class="theme-options">
      <button
        type="button"
        :class="{ active: theme === 'dark' }"
        @click="setTheme('dark')"
      >
        Dark
      </button>
      <button
        type="button"
        :class="{ active: theme === 'light' }"
        @click="setTheme('light')"
      >
        Light
      </button>
      <button
        type="button"
        :class="{ active: theme === 'high-contrast' }"
        @click="setTheme('high-contrast')"
      >
        High Contrast
      </button>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { useSystem } from '../stores/useSystem';

const system = useSystem();
const theme = computed(() => system.theme);

function setTheme(value) {
  system.setTheme(value);
}
</script>

<style scoped>
.nx-theme-switcher {
  display: grid;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
}

.theme-options {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.theme-options button {
  flex: 1;
  min-width: 100px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--color-text-primary);
  padding: 0.8rem 1rem;
  border-radius: 9999px;
  cursor: pointer;
  transition: transform 180ms ease, background-color 180ms ease, border-color 180ms ease;
}

.theme-options button:hover:not(.active) {
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.12);
}

.theme-options button.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: #ffffff;
}
</style>
