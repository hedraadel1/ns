<template>
  <NxGlassCard class="ai-summary-card" elevation="1">
    <header class="summary-header" @click="toggleExpanded">
      <div>
        <p class="subtitle">AI insights</p>
        <h2>Hub summary</h2>
      </div>
      <button class="toggle-btn" type="button" :disabled="loading">{{ expanded ? '−' : '+' }}</button>
    </header>

    <div v-if="expanded" class="summary-body">
      <div v-if="loading" class="loading-state">
        <p>Generating summary...</p>
        <div class="loader"></div>
      </div>
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button type="button" @click="fetchSummary" class="retry-btn">Retry</button>
      </div>
      <div v-else class="summary-text">
        <p>{{ displayedText }}<span v-if="isTyping" class="typing-cursor">▌</span></p>
      </div>
    </div>
  </NxGlassCard>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import NxGlassCard from './NxGlassCard.vue'

const props = defineProps({
  hub: {
    type: String,
    default: 'dashboard'
  }
})

const expanded = ref(false)
const loading = ref(false)
const error = ref('')
const summaryText = ref('')
const displayedText = ref('')
const isTyping = ref(false)
let typingTimer = null

const toggleExpanded = () => {
  expanded.value = !expanded.value
  if (expanded.value && !summaryText.value && !loading.value && !error.value) {
    fetchSummary()
  }
}

const fetchSummary = async () => {
  loading.value = true
  error.value = ''
  displayedText.value = ''

  try {
    const response = await fetch('/api/v1/ai/summarize', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        scope: props.hub
      })
    })

    if (!response.ok) {
      const payload = await response.json().catch(() => ({}))
      throw new Error(payload.message || 'Failed to fetch summary')
    }

    const payload = await response.json()
    summaryText.value = payload.data?.summary ?? payload.summary ?? 'No summary available.'

    // Start typing animation
    startTypingAnimation()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to generate summary.'
  } finally {
    loading.value = false
  }
}

const startTypingAnimation = () => {
  isTyping.value = true
  displayedText.value = ''
  let charIndex = 0

  const typeNextChar = () => {
    if (charIndex < summaryText.value.length) {
      displayedText.value += summaryText.value[charIndex]
      charIndex++
      typingTimer = setTimeout(typeNextChar, 30) // 30ms per character for smooth typing
    } else {
      isTyping.value = false
    }
  }

  typeNextChar()
}

// Watch for expanded state changes
watch(() => expanded.value, (newValue) => {
  if (!newValue && typingTimer) {
    clearTimeout(typingTimer)
    isTyping.value = false
  }
})

// Cleanup on unmount
onBeforeUnmount(() => {
  if (typingTimer) {
    clearTimeout(typingTimer)
  }
})
</script>

<style scoped>
.ai-summary-card {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.5rem;
}

.subtitle {
  margin: 0;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #38bdf8;
}

.ai-summary-card h2 {
  margin: 0.25rem 0 0;
  font-size: 1.35rem;
}

.summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  user-select: none;
}

.toggle-btn {
  background: rgba(56, 189, 248, 0.1);
  border: 1px solid rgba(56, 189, 248, 0.35);
  color: #38bdf8;
  padding: 0.65rem 1rem;
  border-radius: 999px;
  cursor: pointer;
  font-size: 1.2rem;
  line-height: 1;
  transition: background 150ms ease;
  min-width: 44px;
  text-align: center;
}

.toggle-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.toggle-btn:not(:disabled):hover {
  background: rgba(56, 189, 248, 0.18);
}

.summary-body {
  padding: 1.25rem;
  border-radius: 18px;
  background: rgba(15, 23, 42, 0.88);
  border: 1px solid rgba(255, 255, 255, 0.08);
  min-height: 80px;
  display: flex;
  align-items: center;
}

.summary-text {
  width: 100%;
}

.summary-text p {
  margin: 0;
  color: #cbd5e1;
  line-height: 1.6;
  font-size: 0.95rem;
}

.typing-cursor {
  margin-left: 2px;
  animation: blink 0.8s infinite;
}

@keyframes blink {
  0%, 49% {
    opacity: 1;
  }
  50%, 100% {
    opacity: 0;
  }
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  width: 100%;
}

.loading-state p {
  margin: 0;
  color: #94a3b8;
}

.loader {
  width: 24px;
  height: 24px;
  border: 2px solid rgba(56, 189, 248, 0.2);
  border-top-color: #38bdf8;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-state {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

.error-state p {
  margin: 0;
  color: #fecaca;
}

.retry-btn {
  align-self: flex-start;
  padding: 0.65rem 1rem;
  border-radius: 999px;
  border: 1px solid rgba(254, 202, 202, 0.4);
  background: rgba(248, 113, 113, 0.1);
  color: #fecaca;
  cursor: pointer;
  transition: background 150ms ease;
}

.retry-btn:hover {
  background: rgba(248, 113, 113, 0.18);
}
</style>
