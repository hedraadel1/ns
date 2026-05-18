<template>
  <nav class="sidebar" :class="{ open: sidebarOpen }">
    <div class="sidebar-header">
      <h1 class="logo">Nexus</h1>
      <button class="close-btn" @click="$emit('toggle-sidebar')">✕</button>
    </div>

    <div class="nav-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="['nav-tab', { active: activeTab === tab.id }]"
        @click="onTabClick(tab.id)"
        :title="tab.label"
      >
        <span class="tab-icon">{{ tab.icon }}</span>
        <span class="tab-label">{{ tab.label }}</span>
      </button>
    </div>

    <div class="sidebar-footer">
      <div class="user-info">
        <div class="avatar">A</div>
        <div class="user-details">
          <span class="user-name">Admin</span>
          <span class="user-role">Administrator</span>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  activeTab: {
    type: String,
    default: 'dashboard',
  },
  sidebarOpen: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['tab-change', 'toggle-sidebar'])

const tabs = [
  { id: 'dashboard', label: 'Dashboard', icon: '📊' },
  { id: 'chat', label: 'Chat', icon: '💬' },
  { id: 'contacts', label: 'Contacts', icon: '👥' },
  { id: 'agents', label: 'Agents', icon: '🤖' },
  { id: 'workflows', label: 'Workflows', icon: '⚡' },
  { id: 'settings', label: 'Settings', icon: '⚙️' },
]

function onTabClick(tabId) {
  if (props.activeTab !== tabId) {
    emit('tab-change', tabId)
  }
}
</script>

<style scoped>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 260px;
  height: 100vh;
  background: #111;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  z-index: 50;
  transition: transform 0.3s ease;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  font-size: 1.25rem;
  font-weight: bold;
  color: #4ade80;
  margin: 0;
}

.close-btn {
  display: none;
  background: none;
  border: none;
  color: #888;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.25rem;
}

.nav-tabs {
  flex: 1;
  padding: 0.75rem 0;
  overflow-y: auto;
}

.nav-tab {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1.25rem;
  background: none;
  border: none;
  color: #888;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.nav-tab:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
}

.nav-tab.active {
  background: rgba(74, 222, 128, 0.1);
  color: #4ade80;
  border-right: 2px solid #4ade80;
}

.tab-icon {
  font-size: 1.125rem;
  width: 24px;
  text-align: center;
}

.tab-label {
  flex: 1;
}

.sidebar-footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(74, 222, 128, 0.2);
  color: #4ade80;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: #fff;
}

.user-role {
  font-size: 0.75rem;
  color: #888;
}

/* Mobile */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .close-btn {
    display: block;
  }
}
</style>
