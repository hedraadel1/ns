<template>
  <div class="app-shell">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <NxTopBar @open-search="openCommandBar" />

    <div class="app-layout">
      <NxNavRail v-if="!isMobile" />
      <HubSidebar v-if="!isMobile" />

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
    <NxFab :secondary-actions="fabActions" @select="handleFabSelect" />
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
import { useSystem } from './stores/useSystem';

const commandBar = ref(null);
const isMobile = ref(false);
const system = useSystem();
const router = useRouter();
const route = useRoute();

const tokenUsage = ref(system.tokenUsed);
const tokenBudget = ref(system.tokenBudget);
const swipeDistance = ref(0);
const swipeStart = ref({ x: 0, y: 0, time: 0 });
const swipeActive = ref(false);
const swipeThreshold = 120;

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
  if (swipeDistance.value >= swipeThreshold) {
    if ('vibrate' in navigator) {
      navigator.vibrate(15)
    }
    router.back()
  }
  swipeDistance.value = 0;
};

const handleFabSelect = (action) => {
  const event = new CustomEvent('nx-fab-action', {
    detail: action,
  })
  window.dispatchEvent(event)
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
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
  transition: transform 200ms ease, opacity 200ms ease;
}

.page-slide-enter-from {
  transform: translateX(16px);
  opacity: 0;
}

.page-slide-leave-to {
  transform: translateX(-16px);
  opacity: 0;
}
</style>
