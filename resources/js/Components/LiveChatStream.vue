<template>
  <div class="live-chat-stream" aria-hidden="true" />
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { useEcho } from '../composables/useEcho';
import { useEchoStore } from '../stores/useEchoStore';

const props = defineProps({
  conversationId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['token-streamed', 'message-completed', 'message-sent', 'connected', 'error']);

const echoStore = useEchoStore();
const { isAvailable, subscribePrivate, leaveChannel, withPollingFallback } = useEcho();
let channel = null;

function leaveChannelWithEcho() {
  if (channel) {
    channel.stopListening('.App\\Events\\TokenStreamed');
    channel.stopListening('.App\\Events\\MessageCompleted');
    channel.stopListening('.App\\Events\\MessageSent');
  }

  leaveChannel(`conversation.${props.conversationId}`);
  channel = null;
  echoStore.reset();
}

function subscribe() {
  if (!isAvailable() || !props.conversationId) {
    echoStore.setError('WebSocket unavailable');
    return;
  }

  leaveChannelWithEcho();

  const channelName = `conversation.${props.conversationId}`;
  echoStore.setChannel(channelName);

  channel = withPollingFallback(
    () => subscribePrivate(
      channelName,
      {
        '.App\\Events\\TokenStreamed': (event) => emit('token-streamed', event),
        '.App\\Events\\MessageCompleted': (event) => emit('message-completed', event),
        '.App\\Events\\MessageSent': (event) => emit('message-sent', event),
      },
      () => {
        echoStore.setConnected();
        emit('connected');
      }
    ),
    () => {
      echoStore.setError('WebSocket fallback active');
      return null;
    }
  );

  if (!channel) {
    echoStore.setConnected();
    emit('connected');
  }
}

onMounted(() => {
  subscribe();
});

onUnmounted(() => {
  leaveChannel();
});

watch(
  () => props.conversationId,
  () => {
    subscribe();
  }
);
</script>

<style scoped>
.live-chat-stream {
  display: none;
}
</style>
