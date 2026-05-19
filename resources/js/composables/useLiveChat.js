import { ref } from 'vue';

export function useLiveChat() {
  const streamingContent = ref('');
  const isStreaming = ref(false);
  const displayedTokens = ref(0);
  const startTime = ref(null);

  function startStreaming() {
    streamingContent.value = '';
    isStreaming.value = true;
    displayedTokens.value = 0;
    startTime.value = Date.now();
  }

  function appendToken(token) {
    if (!isStreaming.value) {
      startStreaming();
    }

    streamingContent.value += token ?? '';
    displayedTokens.value += 1;
  }

  function completeStream(finalMessage = null) {
    if (typeof finalMessage === 'string' && finalMessage.length > 0) {
      streamingContent.value = finalMessage;
    }
    isStreaming.value = false;
  }

  function clearStream() {
    streamingContent.value = '';
    isStreaming.value = false;
    displayedTokens.value = 0;
    startTime.value = null;
  }

  function getStreamingContent() {
    return streamingContent.value;
  }

  return {
    streamingContent,
    isStreaming,
    displayedTokens,
    startTime,
    startStreaming,
    appendToken,
    completeStream,
    clearStream,
    getStreamingContent,
  };
}
