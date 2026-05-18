<template>
  <div class="app-shell" :class="{ 'sidebar-open': sidebarOpen }">
    <!-- Sidebar Navigation -->
    <Navigation
      :active-tab="activeTab"
      :sidebar-open="sidebarOpen"
      @tab-change="onTabChange"
      @toggle-sidebar="sidebarOpen = !sidebarOpen"
    />

    <!-- Main Content Area -->
    <main class="main-content">
      <!-- Top Bar -->
      <header class="top-bar">
        <button class="menu-toggle" @click="sidebarOpen = !sidebarOpen">
          <span class="hamburger"></span>
        </button>
        <Breadcrumbs :items="breadcrumbs" />
        <div class="top-bar-actions">
          <button class="icon-btn" @click="toggleTheme" title="Toggle theme">
            {{ isDark ? '☀️' : '🌙' }}
          </button>
        </div>
      </header>

      <!-- Tab Content -->
      <div class="tab-content">
        <TabSystem :active-tab="activeTab" @tab-change="onTabChange">
          <!-- Dashboard Tab -->
          <template #dashboard>
            <DashboardView />
          </template>

          <!-- Chat Tab -->
          <template #chat>
            <ChatInterface />
          </template>

          <!-- Contacts Tab -->
          <template #contacts>
            <ContactsView />
          </template>

          <!-- Agents Tab -->
          <template #agents>
            <AgentsView />
          </template>

          <!-- Workflows Tab -->
          <template #workflows>
            <WorkflowsView />
          </template>

          <!-- Settings Tab -->
          <template #settings>
            <SettingsView />
          </template>
        </TabSystem>
      </div>
    </main>

    <!-- Mobile Overlay -->
    <div
      v-if="sidebarOpen"
      class="sidebar-overlay"
      @click="sidebarOpen = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Navigation from './Components/Navigation.vue'
import TabSystem from './Components/TabSystem.vue'
import DashboardView from './Pages/DashboardView.vue'
import ChatInterface from './Pages/ChatInterface.vue'
import ContactsView from './Pages/ContactsView.vue'
import AgentsView from './Pages/AgentsView.vue'
import WorkflowsView from './Pages/WorkflowsView.vue'
import SettingsView from './Pages/SettingsView.vue'

const sidebarOpen = ref(false)
const activeTab = ref('dashboard')
const isDark = ref(true)

const breadcrumbs = computed(() => {
  const labels = {
    dashboard: 'Dashboard',
    chat: 'Chat',
    contacts: 'Contacts',
    agents: 'Agents',
    workflows: 'Workflows',
    settings: 'Settings',
  }
  return [
    { label: 'Home', href: '#' },
    { label: labels[activeTab.value] || 'Dashboard', active: true },
  ]
})

function onTabChange(tab) {
  activeTab.value = tab
  // Close sidebar on mobile after selection
  if (window.innerWidth < 768) {
    sidebarOpen.value = false
  }
}

function toggleTheme() {
  isDark.value = !isDark.value
  document.documentElement.setAttribute('data-theme', isDark.value ? 'dark' : 'light')
}

onMounted(() => {
  // Check system preference
  if (window.matchMedia('(prefers-color-scheme: light)').matches) {
    isDark.value = false
    document.documentElement.setAttribute('data-theme', 'light')
  }
})
</script>

<style scoped>
.app-shell {
  display: flex;
  min-height: 100vh;
  background: #0a0a0a;
  color: #fff;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  margin-left: 260px;
  transition: margin-left 0.3s ease;
}

.app-shell:not(.sidebar-open) .main-content {
  margin-left: 0;
}

.top-bar {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  position: sticky;
  top: 0;
  z-index: 50;
}

.menu-toggle {
  display: none;
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
}

.hamburger {
  display: block;
  width: 20px;
  height: 2px;
  background: #fff;
  position: relative;
}

.hamburger::before,
.hamburger::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 2px;
  background: #fff;
  left: 0;
}

.hamburger::before { top: -6px; }
.hamburger::after { top: 6px; }

.top-bar-actions {
  margin-left: auto;
  display: flex;
  gap: 0.5rem;
}

.icon-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  font-size: 1.25rem;
}

.tab-content {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
}

.sidebar-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 40;
}

/* Responsive */
@media (max-width: 768px) {
  .menu-toggle {
    display: block;
  }

  .main-content {
    margin-left: 0 !important;
  }

  .sidebar-overlay {
    display: block;
  }

  .tab-content {
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .top-bar {
    padding: 0.5rem 1rem;
  }

  .tab-content {
    padding: 0.75rem;
  }
}
</style>
