<template>
  <div class="tab-system">
    <div class="tab-headers">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="['tab-header', { active: activeTab === tab.id }]"
        @click="selectTab(tab.id)"
      >
        <span class="tab-icon">{{ tab.icon }}</span>
        <span class="tab-title">{{ tab.label }}</span>
      </button>
    </div>

    <div class="tab-panels">
      <slot></slot>
    </div>
  </div>
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
  { id: 'dashboard', label: 'Dashboard', icon: '📊' },
  { id: 'chat', label: 'Chat', icon: '💬' },
  { id: 'contacts', label: 'Contacts', icon: '👥' },
  { id: 'agents', label: 'Agents', icon: '🤖' },
  { id: 'workflows', label: 'Workflows', icon: '⚡' },
  { id: 'settings', label: 'Settings', icon: '⚙️' },
]

function selectTab(tabId) {
  if (props.activeTab !== tabId) {
    props.$emit('tab-change', tabId)
  }
}
</script>

<style scoped>
.tab-system {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.tab-headers {
  display: flex;
  gap: 0.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 1.5rem;
  overflow-x: auto;
  scrollbar-width: none;
}

.tab-headers::-webkit-scrollbar {
  display: none;
}

.tab-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  color: #888;
  font-size: 0.875rem;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}

.tab-header:hover {
  color: #fff;
  border-bottom-color: rgba(255, 255, 255, 0.2);
}

.tab-header.active {
  color: #4ade80;
  border-bottom-color: #4ade80;
}

.tab-icon {
  font-size: 1rem;
}

.tab-panels {
  flex: 1;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .tab-headers {
    gap: 0;
  }

  .tab-header {
    flex: 1;
    justify-content: center;
    padding: 0.75rem 0.5rem;
    font-size: 0.75rem;
  }

  .tab-title {
    display: none;
  }

  .tab-icon {
    font-size: 1.25rem;
  }
}
</style>
