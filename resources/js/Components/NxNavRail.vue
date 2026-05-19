<template>
  <nav
    class="nx-nav-rail"
    :class="{ collapsed }"
    :style="{ width: collapsed ? '80px' : '240px' }"
  >
    <!-- Top Section: Hub Icons -->
    <div class="nav-section top-section">
      <div class="nav-items">
        <button
          v-for="hub in hubs"
          :key="hub.id"
          class="nav-item"
          :class="{ active: activeHub === hub.id }"
          @click="navigateToHub(hub.id)"
          :title="hub.label"
        >
          <component :is="hub.icon" class="nav-icon" />
          <span class="nav-label">{{ hub.label }}</span>
        </button>
      </div>
    </div>

    <!-- Bottom Section: User & Settings -->
    <div class="nav-section bottom-section">
      <button class="nav-item" title="Settings">
        <Settings class="nav-icon" />
        <span class="nav-label">Settings</span>
      </button>

      <button class="nav-item collapse-toggle" @click="toggleCollapsed" title="Toggle Navigation">
        <ChevronLeft v-if="!collapsed" class="nav-icon" />
        <ChevronRight v-else class="nav-icon" />
        <span class="nav-label">{{ collapsed ? 'Expand' : 'Collapse' }}</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
  MessageSquare,
  Bot,
  Brain,
  Users,
  Workflow,
  Settings,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();

// State
const collapsed = ref(false);

// Hub definitions
const hubs = [
  { id: 'nexus', label: 'Nexus', icon: MessageSquare },
  { id: 'agents', label: 'Agents', icon: Bot },
  { id: 'memory', label: 'Memory', icon: Brain },
  { id: 'contacts', label: 'Contacts', icon: Users },
  { id: 'workflows', label: 'Workflows', icon: Workflow },
  { id: 'settings', label: 'Settings', icon: Settings },
];

// Computed
const activeHub = computed(() => {
  const path = route.path;
  if (path.includes('/agents')) return 'agents';
  if (path.includes('/memory')) return 'memory';
  if (path.includes('/contacts')) return 'contacts';
  if (path.includes('/workflows')) return 'workflows';
  if (path.includes('/settings')) return 'settings';
  return 'nexus';
});

// Methods
const toggleCollapsed = () => {
  collapsed.value = !collapsed.value;
  localStorage.setItem('nexus-nav-rail-collapsed', String(collapsed.value));
};

const navigateToHub = (hubId) => {
  router.push({ name: hubId });
};

// Lifecycle
onMounted(() => {
  const saved = localStorage.getItem('nexus-nav-rail-collapsed');
  if (saved !== null) {
    collapsed.value = saved === 'true';
  }
});
</script>

<style scoped>
.nx-nav-rail {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: rgba(22, 27, 34, 0.7);
  backdrop-filter: blur(12px);
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  transition: width 250ms cubic-bezier(0.4, 0, 0.2, 1);
  flex-shrink: 0;
  overflow: hidden;
}

.nav-section {
  padding: 12px;
}

.top-section {
  flex: 1;
}

.nav-items {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px 12px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  transition: all 150ms ease;
  text-align: left;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.9);
}

.nav-item.active {
  background: rgba(0, 122, 255, 0.05);
  color: #007AFF;
  border-left: 3px solid #007AFF;
}

.nav-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.nav-label {
  font-size: 14px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 1;
  transition: opacity 150ms ease, width 150ms ease;
}

.nx-nav-rail.collapsed .nav-label {
  opacity: 0;
  width: 0;
  overflow: hidden;
}

.bottom-section {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 12px;
}

.collapse-toggle {
  margin-top: 4px;
}

/* RTL Support */
[dir='rtl'] .nx-nav-rail {
  border-right: none;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
}

[dir='rtl'] .nav-item.active {
  border-left: none;
  border-right: 3px solid #007AFF;
}
</style>