<template>
  <nav class="mobile-footer">
    <button
      v-for="tab in tabs"
      :key="tab.id"
      :class="['footer-tab', { active: activeTab === tab.id }]"
      @click="onTabClick(tab.id)"
    >
      <span class="footer-icon">{{ tab.icon }}</span>
      <span class="footer-label">{{ tab.label }}</span>
    </button>
  </nav>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  activeTab: {
    type: String,
    default: 'dashboard',
  },
})

defineEmits(['tab-change'])

const tabs = [
  { id: 'dashboard', label: 'Home', icon: '🏠' },
  { id: 'chat', label: 'Chat', icon: '💬' },
  { id: 'contacts', label: 'Contacts', icon: '👥' },
  { id: 'agents', label: 'Agents', icon: '🤖' },
  { id: 'workflows', label: 'Workflows', icon: '⚡' },
  { id: 'settings', label: 'Settings', icon: '⚙️' },
]

function onTabClick(tabId) {
  if (props.activeTab !== tabId) {
    props.$emit('tab-change', tabId)
  }
}
</script>

<style scoped>
.mobile-footer {
  display: none;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: var(--color-bg-secondary);
  border-top: 1px solid var(--color-border);
  padding: 0 0.5rem;
  padding-bottom: env(safe-area-inset-bottom, 0);
  z-index: 100;
}

.footer-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  flex: 1;
  background: none;
  border: none;
  color: var(--color-text-muted);
  font-size: 0.625rem;
  cursor: pointer;
  transition: color var(--transition-fast);
  padding: 0.5rem;
  min-height: 44px;
}

.footer-tab:active {
  transform: scale(0.95);
}

.footer-tab.active {
  color: var(--color-primary);
}

.footer-icon {
  font-size: 1.25rem;
}

.footer-label {
  white-space: nowrap;
}

@media (max-width: 768px) {
  .mobile-footer {
    display: flex;
  }
}
</style>
