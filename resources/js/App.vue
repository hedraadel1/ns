<template>
  <div class="app-shell">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <NxTopBar @open-search="openCommandBar" @toggle-sidebar="sidebarOpen = !sidebarOpen" :sidebar-open="sidebarOpen" />

    <div class="app-layout">
      <NxNavRail v-if="!isMobile" />
      <HubSidebar v-if="!isMobile && sidebarOpen" />

      <main id="main-content" class="workspace" :class="{ 'full-width': isMobile }">
        <header class="workspace-header">
          <div class="header-left">
            <Breadcrumbs />
          </div>
          <div class="header-right">
            <NxTokenMeter :current-tokens="tokenUsage" :max-tokens="tokenBudget" />
          </div>
        </header>

        <NxStatusBar v-if="!isMobile" />

        <div
          class="workspace-content"
          :style="workspaceContentStyle"
          @touchstart.passive="onWorkspaceTouchStart"
          @touchmove.passive="onWorkspaceTouchMove"
          @touchend.passive="onWorkspaceTouchEnd"
        >
          <router-view v-slot="{ Component }">
            <transition name="page-slide" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </main>
    </div>

    <MobileFooter v-if="isMobile" @open-search="openCommandBar" @toggle-voice="toggleVoiceMode" />
    <NxOfflineBanner
      :online="online"
      :queued-count="queueCount"
      :status="status"
      @replay="replayQueue"
    />
    <NxFab :secondary-actions="fabActions" @select="handleFabSelect" />
    <NxLiveRegion />
    <NxCelebration :trigger="celebrationKey" :intensity="celebrationIntensity" />
    <NxCommandBar ref="commandBar" />
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import NxNavRail from './Components/NxNavRail.vue';
import HubSidebar from './Components/HubSidebar.vue';
import NxStatusBar from './Components/NxStatusBar.vue';
import NxTokenMeter from './Components/NxTokenMeter.vue';
import MobileFooter from './Components/MobileFooter.vue';
import Breadcrumbs from './Components/Breadcrumbs.vue';
import NxTopBar from './Components/NxTopBar.vue';
import NxCommandBar from './Components/NxCommandBar.vue';
import NxFab from './Components/NxFab.vue';
import NxOfflineBanner from './Components/NxOfflineBanner.vue';
import NxLiveRegion from './Components/NxLiveRegion.vue';
import NxCelebration from './Components/NxCelebration.vue';
import { useSystem } from './stores/useSystem';
import { useHaptic } from './composables/useHaptic';
import { useOfflineQueue } from './composables/useOfflineQueue';

const commandBar = ref(null);
const isMobile = ref(false);
const sidebarOpen = ref(true);
const system = useSystem();
const router = useRouter();
const route = useRoute();
const { online, queueCount, status, replayQueue } = useOfflineQueue();
const { success } = useHaptic();

const tokenUsage = ref(system.tokenUsed);
const tokenBudget = ref(system.tokenBudget);
const celebrationKey = ref(0);
const celebrationIntensity = ref(0.7);
const swipeDistance = ref(0);
const swipeStart = ref({ x: 0, y: 0, time: 0 });
const swipeActive = ref(false);
const swipeThreshold = 120;
const swipeVelocityThreshold = 0.35;

const fabActions = computed(() => {
  switch (route.name) {
    case 'contacts':
      return [
        { value: 'refresh', label: 'Refresh Contacts', icon: '↻' },
        { value: 'new-contact', label: 'New Contact', icon: '+' },
        { value: 'more', label: 'More actions', icon: '⋯' },
      ]
    case 'memory':
      return [
        { value: 'refresh', label: 'Refresh Memory', icon: '↻' },
        { value: 'browse', label: 'Browse all', icon: '📂' },
        { value: 'filters', label: 'Filters', icon: '⚙️' },
      ]
    case 'workflows':
      return [
        { value: 'refresh', label: 'Refresh tasks', icon: '↻' },
        { value: 'retry', label: 'Retry tasks', icon: '⟳' },
        { value: 'help', label: 'Task help', icon: '❔' },
      ]
    default:
      return [
        { value: 'search', label: 'Open search', icon: '🔎' },
        { value: 'home', label: 'Go home', icon: '🏠' },
      ]
  }
})

const workspaceContentStyle = computed(() => ({
  transform: swipeDistance.value ? `translateX(${swipeDistance.value}px)` : undefined,
}))

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

const applyTheme = () => {
  system.setTheme(system.theme || 'dark');
};

const openCommandBar = () => {
  commandBar.value?.open();
};

const toggleVoiceMode = () => {
  openCommandBar();
};

const canNavigateBack = () => {
  return route.name !== 'nexus' && window.history.length > 1;
};

const onWorkspaceTouchStart = (event) => {
  if (!isMobile.value) return;
  const touch = event.touches[0];
  if (touch.clientX > 50 || !canNavigateBack()) return;

  swipeStart.value = {
    x: touch.clientX,
    y: touch.clientY,
    time: Date.now(),
  };
  swipeActive.value = true;
};

const onWorkspaceTouchMove = (event) => {
  if (!swipeActive.value) return;
  const touch = event.touches[0];
  const deltaX = touch.clientX - swipeStart.value.x;
  const deltaY = touch.clientY - swipeStart.value.y;
  if (deltaX <= 0 || Math.abs(deltaY) > 40) {
    swipeDistance.value = 0;
    return;
  }

  swipeDistance.value = Math.min(deltaX, 260);
};

const onWorkspaceTouchEnd = () => {
  if (!swipeActive.value) return;
  swipeActive.value = false;
  const elapsed = Math.max(Date.now() - swipeStart.value.time, 1);
  const velocity = swipeDistance.value / elapsed;

  if (swipeDistance.value >= swipeThreshold || velocity >= swipeVelocityThreshold) {
    success();
    router.back();
  }

  swipeDistance.value = 0;
};

const handleFabSelect = (action) => {
  const event = new CustomEvent('nx-fab-action', {
    detail: action,
  });
  window.dispatchEvent(event);
};

const handleCelebrationEvent = (event) => {
  celebrationKey.value += 1;
  celebrationIntensity.value = Math.min(1, Math.max(0.25, event?.detail?.intensity ?? 0.7));
};

onMounted(() => {
  applyTheme();
  checkMobile();
  window.addEventListener('resize', checkMobile);
  window.addEventListener('nx-celebration', handleCelebrationEvent);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
  window.removeEventListener('nx-celebration', handleCelebrationEvent);
});
</script>

<style scoped>
.app-shell {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #0a0a0a;
}

.app-layout {
  display: flex;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

.workspace {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.workspace.full-width {
  width: 100%;
}

.workspace-header {
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  border-block-end: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(22, 27, 34, 0.7);
  backdrop-filter: blur(12px);
  flex-shrink: 0;
}

.header-left,
.header-right {
  display: flex;
  align-items: center;
}

.workspace-content {
  flex: 1;
  overflow: hidden;
}

.skip-link {
  position: absolute;
  top: -40px;
  inset-inline-start: 6px;
  background: #007AFF;
  color: white;
  padding: 8px 12px;
  border-radius: 4px;
  text-decoration: none;
  z-index: 1000;
  transition: top 150ms ease;
}

.skip-link:focus {
  top: 6px;
}

.page-slide-enter-active,
.page-slide-leave-active {
  transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1), opacity 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.page-slide-enter-from {
  transform: translateY(12px);
  opacity: 0;
}

.page-slide-enter-to {
  transform: translateY(0);
  opacity: 1;
}

.page-slide-leave-from {
  transform: translateY(0);
  opacity: 1;
}

.page-slide-leave-to {
  transform: translateY(-8px);
  opacity: 0;
}
</style>
