<template>
  <div
    class="nx-live-region"
    role="status"
    :aria-live="politeness"
    aria-atomic="true"
    v-if="message"
  >
    {{ message }}
  </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useNotificationStore } from '../stores/useNotificationStore';

const notifications = useNotificationStore();
const message = computed(() => notifications.liveMessage || '');
const politeness = computed(() => notifications.livePoliteness || 'polite');

watch(message, (value) => {
  if (!value) {
    return;
  }

  window.setTimeout(() => {
    notifications.clearLiveMessage();
  }, 1800);
});
</script>

<style scoped>
.nx-live-region {
  position: absolute;
  width: 1px;
  height: 1px;
  margin: -1px;
  border: 0;
  padding: 0;
  overflow: hidden;
  clip: rect(0 0 0 0);
  clip-path: inset(50%);
  white-space: nowrap;
}
</style>
