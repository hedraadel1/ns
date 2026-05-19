<template>
  <nav class="mobile-footer" v-if="isMobile">
    <div class="voice-orb-container">
      <button class="voice-orb" @click="toggleVoiceMode" title="Voice Input">
        <Mic class="orb-icon" />
      </button>
    </div>
    <div class="footer-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        class="footer-tab"
        :class="{ active: activeTab === tab.id }"
        @click="onTabClick(tab.id)"
      >
        <component :is="tab.icon" class="footer-icon" />
        <span class="footer-label">{{ tab.label }}</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  Home,
  Brain,
  Users,
  ListTodo,
  Search,
  Mic,
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

const isMobile = ref(false);
const activeTab = computed(() => {
  const path = route.path;
  if (path.includes('/memory')) return 'memory';
  if (path.includes('/contacts')) return 'contacts';
  if (path.includes('/tasks') || path.includes('/workflows')) return 'tasks';
  if (path.includes('/search')) return 'search';
  return 'dashboard';
});

const tabs = [
  { id: 'dashboard', label: 'Home', icon: Home },
  { id: 'memory', label: 'Memory', icon: Brain },
  { id: 'contacts', label: 'Contacts', icon: Users },
  { id: 'tasks', label: 'Tasks', icon: ListTodo },
  { id: 'search', label: 'Search', icon: Search },
];

const onTabClick = (tabId) => {
  if (activeTab.value !== tabId) {
    router.push({ name: tabId });
  }
};

const toggleVoiceMode = () => {
  // Voice dictation mode toggle
  console.log('Voice mode toggled');
};

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
.mobile-footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 64px;
  background: rgba(22, 27, 34, 0.85);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-bottom: env(safe-area-inset-bottom, 0);
  z-index: 50;
}

.voice-orb-container {
  position: absolute;
  top: -28px;
  left: 50%;
  transform: translateX(-50%);
}

.voice-orb {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366F1, #007AFF);
  border: 2px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  transition: transform 150ms ease;
}

.voice-orb:hover {
  transform: scale(1.05);
}

.orb-icon {
  width: 24px;
  height: 24px;
  color: white;
}

.footer-tabs {
  display: flex;
  width: 100%;
  height: 100%;
  padding-top: 28px;
}

.footer-tab {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.5);
  font-size: 11px;
  cursor: pointer;
  transition: color 150ms ease;
  min-height: 44px;
  padding: 4px;
}

.footer-tab:active {
  transform: scale(0.95);
}

.footer-tab.active {
  color: #007AFF;
}

.footer-icon {
  width: 20px;
  height: 20px;
}

.footer-label {
  white-space: nowrap;
}

@media (min-width: 768px) {
  .mobile-footer {
    display: none;
  }
}
</style>
