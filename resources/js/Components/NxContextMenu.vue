<template>
  <div v-if="visible" class="nx-context-menu-overlay" @click.self="closeMenu">
    <div class="nx-context-menu" :style="menuStyle" ref="menu" role="menu">
      <button
        v-for="item in items"
        :key="item.value || item.label"
        type="button"
        class="context-menu-item"
        @click="selectItem(item)
        "
      >
        <span>{{ item.label }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  x: {
    type: Number,
    default: 0,
  },
  y: {
    type: Number,
    default: 0,
  },
  items: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['select', 'close'])

const menuStyle = computed(() => ({
  left: `${props.x}px`,
  top: `${props.y}px`,
}))

function closeMenu() {
  emit('close')
}

function selectItem(item) {
  emit('select', item)
  closeMenu()
}

watch(
  () => props.visible,
  (value) => {
    if (value && 'vibrate' in navigator) {
      navigator.vibrate(15)
    }
  }
)
</script>

<style scoped>
.nx-context-menu-overlay {
  position: fixed;
  inset: 0;
  z-index: 70;
  background: transparent;
}

.nx-context-menu {
  position: absolute;
  min-width: 180px;
  background: rgba(15, 23, 42, 0.98);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 18px;
  box-shadow: 0 20px 58px rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(20px);
  padding: 0.5rem;
  display: grid;
  gap: 0.35rem;
}

.context-menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.75rem;
  background: transparent;
  border: none;
  border-radius: 12px;
  padding: 0.85rem 1rem;
  color: #e2e8f0;
  font-size: 0.95rem;
  text-align: left;
  cursor: pointer;
  transition: background 150ms ease;
}

.context-menu-item:hover {
  background: rgba(255, 255, 255, 0.08);
}
</style>
