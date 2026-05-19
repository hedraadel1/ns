<template>
  <div class="app-layout">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Navigation Rail (Left Pane) -->
    <NxNavRail v-if="!isMobile" />

    <!-- Hub Sidebar (Middle Pane) -->
    <HubSidebar v-if="!isMobile" />

    <!-- Main Workspace (Right Pane) -->
    <main
      id="main-content"
      class="workspace"
      :class="{ 'full-width': isMobile }"
    >
      <!-- Workspace Header -->
      <header class="workspace-header">
        <div class="header-left">
          <Breadcrumbs />
        </div>
        <div class="header-right">
          <NxTokenMeter />
        </div>
      </header>

      <!-- Status Bar -->
      <NxStatusBar v-if="!isMobile" />

      <!-- Router View with Transition -->
      <div class="workspace-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </main>

    <!-- Mobile Footer (Bottom Tab Bar) -->
    <MobileFooter v-if="isMobile" />

    <!-- Command Bar (Cmd+K) -->
    <NxCommandBar ref="commandBarRef" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import NxNavRail from './Components/NxNavRail.vue';
import HubSidebar from './Components/HubSidebar.vue';
import MobileFooter from './Components/MobileFooter.vue';
import NxCommandBar from './Components/NxCommandBar.vue';
import NxStatusBar from './Components/NxStatusBar.vue';
import Breadcrumbs from './Components/Breadcrumbs.vue';
import NxTokenMeter from './Components/NxTokenMeter.vue';

const route = useRoute();
const commandBarRef = ref(null);

// Mobile detection
const isMobile = ref(false);

function checkMobile() {
  isMobile.value = window.innerWidth < 768;
}

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>

<style scoped>
.app-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: rgba(22, 27, 34, 0.7);
}

.workspace {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-width: 0;
}

.workspace.full-width {
  width: 100%;
}

.workspace-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  background: rgba(22, 27, 34, 0.9);
  min-height: 56px;
}

.header-left {
  flex: 1;
  min-width: 0;
}

.header-right {
  flex-shrink: 0;
}

.workspace-content {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
}

/* Skip link for accessibility */
.skip-link {
  position: absolute;
  top: -40px;
  left: 0;
  background: #007AFF;
  color: white;
  padding: 8px 16px;
  z-index: 9999;
  transition: top 150ms ease;
}

.skip-link:focus {
  top: 0;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 150ms ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
