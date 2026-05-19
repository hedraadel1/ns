import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useEchoStore = defineStore('echo', () => {
  const connected = ref(false);
  const reconnecting = ref(false);
  const error = ref(null);
  const channelName = ref(null);

  function reset() {
    connected.value = false;
    reconnecting.value = false;
    error.value = null;
    channelName.value = null;
  }

  function setConnected() {
    connected.value = true;
    reconnecting.value = false;
    error.value = null;
  }

  function setReconnecting() {
    connected.value = false;
    reconnecting.value = true;
  }

  function setError(message) {
    connected.value = false;
    reconnecting.value = false;
    error.value = message;
  }

  function setChannel(name) {
    channelName.value = name;
  }

  return {
    connected,
    reconnecting,
    error,
    channelName,
    reset,
    setConnected,
    setReconnecting,
    setError,
    setChannel,
  };
});
