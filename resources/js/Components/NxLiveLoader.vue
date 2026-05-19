<template>
  <div class="nx-live-loader" :class="{ expanded }">
    <button class="loader-pill" @click="toggle">
      <span class="pulse" />
      <span class="status-text">{{ statusText }}</span>
      <NxIcon :icon="expanded ? ChevronUpIcon : ChevronDownIcon" :size="14" />
    </button>
    <div v-if="expanded" class="log-feed">
      <div v-for="(log, i) in logs" :key="i" class="log-line">
        <span class="timestamp">{{ log.timestamp }}</span>
        <span class="message">{{ log.message }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import NxIcon from './NxIcon.vue';
import { ChevronUp as ChevronUpIcon, ChevronDown as ChevronDownIcon } from 'lucide-vue-next';

const props = defineProps({
  taskId: {
    type: String,
    default: '',
  },
  status: {
    type: String,
    default: 'loading',
  },
});

const emit = defineEmits(['expand', 'collapse']);

const expanded = ref(false);
const logs = ref([]);

const statusText = computed(() => {
  switch (props.status) {
    case 'loading': return 'Processing...';
    case 'success': return 'Complete';
    case 'error': return 'Failed';
    default: return props.status;
  }
});

function toggle() {
  expanded.value = !expanded.value;
  emit(expanded.value ? 'expand' : 'collapse');
}

let channel = null;

onMounted(() => {
  if (window.Echo && props.taskId) {
    channel = window.Echo.private(`tasks.${props.taskId}`);
    channel.listen('TaskCheckpoint', (e) => {
      logs.value.push({
        timestamp: new Date().toLocaleTimeString(),
        message: e.message || JSON.stringify(e),
      });
    });
  }
});

onUnmounted(() => {
  if (channel) {
    channel.stopListening('TaskCheckpoint');
    window.Echo.leaveChannel(`tasks.${props.taskId}`);
  }
});
</script>

<style scoped>
.nx-live-loader {
  display: inline-flex;
  flex-direction: column;
}

.loader-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px;
  background: rgba(22, 27, 34, 0.7);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 9999px;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.8);
  font-size: 12px;
  transition: all 0.2s ease;
}

.loader-pill:hover {
  background: rgba(22, 27, 34, 0.9);
  border-color: rgba(255, 255, 255, 0.2);
}

.pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #007AFF;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.8); }
}

.log-feed {
  max-height: 200px;
  overflow-y: auto;
  font-family: 'JetBrains Mono', monospace;
  font-size: 11px;
  background: rgba(0, 0, 0, 0.3);
  margin-top: 8px;
  border-radius: 8px;
  padding: 8px;
}

.log-line {
  padding: 2px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.7);
}

.timestamp {
  color: rgba(255, 255, 255, 0.4);
  margin-right: 8px;
}
</style>
