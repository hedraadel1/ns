<template>
  <div class="live-chat-stream" role="status" aria-live="polite" v-if="visible">
    <div class="stream-header">
      <span class="stream-label">Live response stream</span>
      <span class="stream-state" v-if="isStreaming">Receiving tokens…</span>
      <span class="stream-state" v-else>Idle</span>
    </div>
    <pre class="stream-output">{{ streamingContent || 'Waiting for incoming tokens...' }}</pre>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, watch, computed } from 'vue';
import { useEcho } from '../composables/useEcho';
import { useEchoStore } from '../stores/useEchoStore';
import { useLiveChat } from '../composables/useLiveChat';

const props = defineProps({
  conversationId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['token-streamed', 'message-completed', 'message-sent', 'connected', 'error']);

const echoStore = useEchoStore();
const liveChat = useLiveChat();
const { isAvailable, subscribePrivate, leaveChannel, withPollingFallback } = useEcho();
let channel = null;

const visible = computed(() => liveChat.isStreaming || liveChat.streamingContent.length > 0);

function leaveChannelWithEcho() {
  if (channel) {
    channel.stopListening('.App\\Events\\TokenStreamed');
    channel.stopListening('.App\\Events\\MessageCompleted');
    channel.stopListening('.App\\Events\\MessageSent');
  }

  leaveChannel(`conversation.${props.conversationId}`);
  channel = null;
  echoStore.reset();
  liveChat.clearStream();
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
        '.App\\Events\\TokenStreamed': (event) => {
          liveChat.appendToken(event.token ?? '');
          emit('token-streamed', event);
        },
        '.App\\Events\\MessageCompleted': (event) => {
          liveChat.completeStream(event.final_message ?? liveChat.streamingContent);
          emit('message-completed', event);
        },
        '.App\\Events\\MessageSent': (event) => emit('message-sent', event),
      },
      () => {
        echoStore.setConnectionStatus('connected');
        emit('connected');
      }
    ),
    () => {
      echoStore.setError('WebSocket fallback active');
      return null;
    }
  );

  if (!channel) {
    echoStore.setConnectionStatus('connected');
    emit('connected');
  }
}

onMounted(() => {
  subscribe();
});

onUnmounted(() => {
  leaveChannelWithEcho();
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
  width: 100%;
  padding: 1rem;
  border-radius: 1rem;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.18);
  color: #f8fafc;
  font-family: 'JetBrains Mono', monospace;
}

.stream-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  gap: 1rem;
}

.stream-label {
  font-weight: 600;
  letter-spacing: 0.02em;
}

.stream-state {
  font-size: 0.85rem;
  color: #94a3b8;
}

.stream-output {
  white-space: pre-wrap;
  word-break: break-word;
  margin: 0;
  min-height: 5rem;
}
</style>
