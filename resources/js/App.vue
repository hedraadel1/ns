<template>
  <div class="app-layout">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Navigation Rail -->
    <NxNavRail v-if="!isMobile" />

    <!-- Main Workspace -->
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
          <transition name="page-slide" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </main>

    <!-- Mobile Footer -->
    <MobileFooter v-if="isMobile" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import NxNavRail from './Components/NxNavRail.vue';
import NxStatusBar from './Components/NxStatusBar.vue';
import NxTokenMeter from './Components/NxTokenMeter.vue';
import MobileFooter from './Components/MobileFooter.vue';
import Breadcrumbs from './Components/Breadcrumbs.vue';

const route = useRoute();

// Mobile detection
const isMobile = ref(false);

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
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
.app-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: #0a0a0a;
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
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(22, 27, 34, 0.7);
  backdrop-filter: blur(12px);
  flex-shrink: 0;
}

.header-left {
  display: flex;
  align-items: center;
}

.header-right {
  display: flex;
  align-items: center;
}

.workspace-content {
  flex: 1;
  overflow: hidden;
}

/* Skip link for accessibility */
.skip-link {
  position: absolute;
  top: -40px;
  left: 6px;
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

/* Page slide transition */
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
