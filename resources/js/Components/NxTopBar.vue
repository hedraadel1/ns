<template>
  <div>
    <!-- Page Loading Progress Bar -->
    <div
      v-if="system.pageLoading || system.pageLoadingProgress > 0"
      class="nx-page-progress"
      :style="{ width: `${progress}%`, opacity: progressOpacity }"
    />

    <!-- Navigation Top Bar -->
    <header class="nx-top-bar">
      <div class="top-bar-left">
        <button class="sidebar-toggle" @click="toggleSidebar" title="Toggle sidebar">
          <Menu class="action-icon" />
        </button>
        <span class="brand-label">Nexus</span>
        <span class="top-bar-separator">•</span>
        <span class="top-bar-context">{{ currentBreadcrumb }}</span>
      </div>
      <div class="top-bar-actions">
        <button class="top-bar-action" @click="openCommandBar">
          <Search class="action-icon" />
          <span>Search</span>
        </button>
        <button class="top-bar-action" @click="handleLogout" title="Logout">
          <LogOut class="action-icon" />
          <span>Logout</span>
        </button>
      </div>
    </header>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSystem } from '../stores/useSystem';
import { useAuthStore } from '../stores/useAuthStore';
import { Search, Menu, LogOut } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const system = useSystem();
const authStore = useAuthStore();
const emit = defineEmits(['open-search', 'toggle-sidebar']);
const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: true,
  },
});

const currentBreadcrumb = computed(() => route.meta?.breadcrumb || 'Workspace');
const progress = ref(0);
const progressOpacity = ref(1);
let crawlTimer = null;
let fadeTimer = null;

// Auto-crawl progress when loading
watch(
  () => system.pageLoading,
  (isLoading) => {
    if (isLoading) {
      // Start loading: immediately jump to 30%
      progress.value = 30;
      progressOpacity.value = 1;
      clearInterval(crawlTimer);

      // Crawl to 90% over time
      crawlTimer = setInterval(() => {
        if (progress.value < 90) {
          progress.value += Math.random() * 30;
        }
      }, 500);
    } else {
      // Complete: jump to 100% then fade
      clearInterval(crawlTimer);
      progress.value = 100;

      fadeTimer = setTimeout(() => {
        progressOpacity.value = 0;
        setTimeout(() => {
          progress.value = 0;
          progressOpacity.value = 1;
        }, 300);
      }, 500);
    }
  }
);

// Also sync to manual progress updates
watch(
  () => system.pageLoadingProgress,
  (prog) => {
    if (prog > progress.value) {
      progress.value = prog;
    }
  }
);

const openCommandBar = () => emit('open-search');

const toggleSidebar = () => emit('toggle-sidebar');

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

onMounted(() => {
  // Handle route changes
  route;  // Re-evaluate when route changes
});

onBeforeUnmount(() => {
  clearInterval(crawlTimer);
  clearTimeout(fadeTimer);
});
</script>

<style scoped>
.nx-page-progress {
  position: fixed;
  top: 0;
  left: 0;
  height: 3px;
  background: linear-gradient(90deg, #60a5fa, #818cf8);
  z-index: 1000;
  transition: opacity 300ms ease, width 200ms ease;
  box-shadow: 0 0 8px rgba(96, 165, 250, 0.6);
}

.nx-top-bar {
  position: sticky;
  top: 3px;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 20px;
  backdrop-filter: blur(16px);
  background: rgba(22, 27, 34, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.top-bar-left {
  display: flex;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.85);
  font-size: 13px;
}

.brand-label {
  font-weight: 700;
  color: #ffffff;
}

.top-bar-separator {
  color: rgba(255, 255, 255, 0.3);
}

.top-bar-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.top-bar-action,
.sidebar-toggle {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
  transition: background 150ms ease;
  font-size: 13px;
}

.top-bar-action:hover,
.sidebar-toggle:hover {
  background: rgba(255, 255, 255, 0.12);
}

.action-icon {
  width: 16px;
  height: 16px;
}
</style>
