<template>
  <div v-if="showBanner" class="nx-offline-banner" role="status" aria-live="polite">
    <div class="banner-body">
      <p class="banner-label">
        <span v-if="!online">Offline</span>
        <span v-else-if="status === 'replaying'">Replaying queued actions</span>
        <span v-else>Online</span>
      </p>
      <p class="banner-message">
        <span v-if="!online">Changes will be queued while offline.</span>
        <span v-else-if="queuedCount > 0">{{ queuedCount }} queued action<span v-if="queuedCount !== 1">s</span></span>
        <span v-else>Network restored.</span>
      </p>
    </div>
    <button
      v-if="online && queuedCount > 0"
      type="button"
      class="replay-button"
      @click="replayQueue"
    >
      Sync now
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  online: { type: Boolean, default: true },
  queuedCount: { type: Number, default: 0 },
  status: { type: String, default: 'idle' },
});

const emit = defineEmits(['replay']);

const showBanner = computed(() => !props.online || props.status === 'replaying' || props.queuedCount > 0);

function replayQueue() {
  emit('replay');
}
</script>

<style scoped>
.nx-offline-banner {
  position: sticky;
  top: var(--topbar-height, 56px);
  z-index: 90;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.85rem 1rem;
  background: rgba(255, 182, 66, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.25);
  border-radius: 0 0 0.85rem 0.85rem;
  color: #fffbeb;
  backdrop-filter: blur(12px);
}

.banner-body {
  display: grid;
  gap: 0.125rem;
}

.banner-label {
  font-weight: 700;
  color: #f59e0b;
}

.banner-message {
  font-size: 0.95rem;
  color: #f8fafc;
}

.replay-button {
  border: none;
  padding: 0.65rem 1rem;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  cursor: pointer;
  transition: transform 180ms ease, background-color 180ms ease;
}

.replay-button:hover {
  background: rgba(255, 255, 255, 0.18);
  transform: translateY(-1px);
}
</style>
