<template>
  <div class="nx-fab">
    <div class="fab-actions" :class="{ open: expanded }">
      <button
        v-for="(action, index) in secondaryActions"
        :key="action.value || index"
        type="button"
        class="fab-secondary"
        :style="getActionStyle(index)"
        @click="onAction(action)">
        <span>{{ action.icon || '•' }}</span>
        <span class="fab-label">{{ action.label }}</span>
      </button>
    </div>

    <button class="fab-main" type="button" @click="toggleFab" :aria-expanded="expanded">
      <span v-if="expanded">×</span>
      <span v-else>{{ mainIcon }}</span>
    </button>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  mainIcon: {
    type: String,
    default: '+',
  },
  secondaryActions: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['select'])
const expanded = ref(false)

const visibleActions = computed(() => props.secondaryActions || [])

function toggleFab() {
  expanded.value = !expanded.value
}

function onAction(action) {
  emit('select', action)
  expanded.value = false
}

function getActionStyle(index) {
  return {
    transform: expanded.value ? `translateY(-${(index + 1) * 70}px)` : 'translateY(0)',
    opacity: expanded.value ? 1 : 0,
    pointerEvents: expanded.value ? 'auto' : 'none',
  }
}
</script>

<style scoped>
.nx-fab {
  position: fixed;
  right: 20px;
  bottom: 20px;
  z-index: 70;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fab-main {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(180deg, #10b981, #047857);
  color: white;
  font-size: 1.6rem;
  font-weight: 700;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.fab-actions {
  position: absolute;
  right: 0;
  bottom: 0;
  display: grid;
  gap: 0.7rem;
  align-items: end;
}

.fab-secondary {
  min-width: 160px;
  padding: 0.85rem 1rem;
  border-radius: 9999px;
  border: none;
  background: rgba(15, 23, 42, 0.95);
  color: #f8fafc;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.75rem;
  align-items: center;
  box-shadow: 0 18px 30px rgba(15, 23, 42, 0.25);
  opacity: 0;
  transition: transform 180ms ease, opacity 180ms ease;
  cursor: pointer;
}

.fab-label {
  font-size: 0.95rem;
  font-weight: 600;
}
</style>
