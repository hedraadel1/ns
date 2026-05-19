<template>
  <div class="connection-status" :class="statusClass">
    <span class="status-dot"></span>
    <span class="status-text">{{ statusText }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useEchoStore } from '../stores/useEchoStore';

const echoStore = useEchoStore();

const statusText = computed(() => {
  if (echoStore.error) return 'Disconnected';
  if (echoStore.reconnecting) return 'Reconnecting';
  if (echoStore.connected) return 'Connected';
  return 'Offline';
});

const statusClass = computed(() => {
  if (echoStore.error) return 'status-disconnected';
  if (echoStore.reconnecting) return 'status-reconnecting';
  if (echoStore.connected) return 'status-connected';
  return 'status-offline';
});
</script>

<style scoped>
.connection-status {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #f8fafc;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.status-dot {
  width: 0.625rem;
  height: 0.625rem;
  border-radius: 9999px;
  background: #94a3b8;
  box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.08);
}

.status-connected .status-dot {
  background: #22c55e;
}

.status-reconnecting .status-dot {
  background: #f59e0b;
}

.status-disconnected .status-dot {
  background: #ef4444;
}

.status-offline .status-dot {
  background: #94a3b8;
}
</style>
