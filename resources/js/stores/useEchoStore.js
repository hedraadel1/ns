import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useEchoStore = defineStore('echo', () => {
  const connectionStatus = ref('disconnected');
  const isWsAvailable = ref(false);
  const useFallback = ref(false);
  const reconnectAttempts = ref(0);
  const lastConnectionTime = ref(null);
  const missedEventIds = ref([]);
  const channelName = ref(null);
  const error = ref(null);

  const isConnected = computed(() => connectionStatus.value === 'connected' && isWsAvailable.value && !useFallback.value);
  const isReconnecting = computed(() => connectionStatus.value === 'reconnecting');
  const shouldUsePolling = computed(() => useFallback.value);

  function reset() {
    connectionStatus.value = 'disconnected';
    isWsAvailable.value = false;
    useFallback.value = false;
    reconnectAttempts.value = 0;
    lastConnectionTime.value = null;
    missedEventIds.value = [];
    channelName.value = null;
    error.value = null;
  }

  function setConnectionStatus(status, message = null) {
    connectionStatus.value = status;

    if (status === 'connected') {
      isWsAvailable.value = true;
      reconnectAttempts.value = 0;
      lastConnectionTime.value = Date.now();
      useFallback.value = false;
      error.value = null;
    } else if (status === 'reconnecting') {
      isWsAvailable.value = true;
      reconnectAttempts.value += 1;
      error.value = null;
    } else if (status === 'disconnected') {
      isWsAvailable.value = false;
    } else if (status === 'error') {
      isWsAvailable.value = false;
      if (message) {
        error.value = message;
      }
    }
  }

  function setError(message) {
    connectionStatus.value = 'error';
    isWsAvailable.value = false;
    error.value = message;
  }

  function setChannel(name) {
    channelName.value = name;
  }

  function enableFallback() {
    useFallback.value = true;
  }

  function disableFallback() {
    useFallback.value = false;
  }

  function recordMissedEvent(eventId) {
    if (!eventId) {
      return;
    }
    if (!missedEventIds.value.includes(eventId)) {
      missedEventIds.value.push(eventId);
    }
  }

  function clearMissedEvents() {
    missedEventIds.value = [];
  }

  return {
    connectionStatus,
    isWsAvailable,
    useFallback,
    reconnectAttempts,
    lastConnectionTime,
    missedEventIds,
    channelName,
    error,
    isConnected,
    isReconnecting,
    shouldUsePolling,
    reset,
    setConnectionStatus,
    setError,
    setChannel,
    enableFallback,
    disableFallback,
    recordMissedEvent,
    clearMissedEvents,
  };
});
