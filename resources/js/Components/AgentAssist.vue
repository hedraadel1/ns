<template>
  <div class="agent-assist">
    <div class="assist-header">
      <span class="assist-title">🤖 Agent Assistance</span>
      <button class="close-btn" @click="$emit('close')">✕</button>
    </div>

    <div v-if="loading" class="assist-loading">
      <p>Loading suggestions...</p>
    </div>

    <div v-else-if="suggestions.length === 0" class="assist-empty">
      <p>No suggestions available.</p>
    </div>

    <div v-else class="suggestions-list">
      <div
        v-for="(suggestion, i) in suggestions"
        :key="i"
        class="suggestion-card"
        @click="applySuggestion(suggestion)"
      >
        <div class="suggestion-type">{{ suggestion.type }}</div>
        <div class="suggestion-text">{{ suggestion.text }}</div>
        <div class="suggestion-confidence">
          Confidence: {{ Math.round((suggestion.confidence || 0) * 100) }}%
        </div>
      </div>
    </div>

    <div class="assist-actions">
      <button class="refresh-btn" @click="loadSuggestions" :disabled="loading">
        🔄 Refresh
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  conversationId: {
    type: Number,
    default: null,
  },
})

defineEmits(['suggestion', 'close'])

const suggestions = ref([])
const loading = ref(false)

watch(() => props.conversationId, () => {
  if (props.conversationId) loadSuggestions()
})

onMounted(() => {
  if (props.conversationId) loadSuggestions()
})

async function loadSuggestions() {
  if (!props.conversationId) return

  loading.value = true
  try {
    const res = await fetch(`/api/v1/conversations/${props.conversationId}/suggestions`)
    const data = await res.json()
    if (data.success && data.data) {
      suggestions.value = data.data
    }
  } catch (e) {
    // Fallback to placeholder suggestions
    suggestions.value = [
      { type: 'reply', text: 'Thank you for reaching out. How can I help you today?', confidence: 0.9 },
      { type: 'question', text: 'Could you provide more details about your request?', confidence: 0.8 },
      { type: 'action', text: 'I will look into this and get back to you shortly.', confidence: 0.7 },
    ]
  } finally {
    loading.value = false
  }
}

function applySuggestion(suggestion) {
  props.$emit('suggestion', suggestion.text || suggestion)
}
</script>

<style scoped>
.agent-assist {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  margin-bottom: 0.75rem;
  overflow: hidden;
}

.assist-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: rgba(74, 222, 128, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.assist-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #4ade80;
}

.close-btn {
  background: none;
  border: none;
  color: #888;
  cursor: pointer;
  padding: 0.25rem;
}

.assist-loading,
.assist-empty {
  padding: 1rem;
  text-align: center;
  color: #888;
  font-size: 0.875rem;
}

.suggestions-list {
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-card {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.suggestion-card:hover {
  background: rgba(74, 222, 128, 0.08);
  border-color: rgba(74, 222, 128, 0.2);
}

.suggestion-type {
  font-size: 0.625rem;
  text-transform: uppercase;
  color: #4ade80;
  margin-bottom: 0.375rem;
  letter-spacing: 0.05em;
}

.suggestion-text {
  font-size: 0.8125rem;
  color: #ccc;
  line-height: 1.4;
  margin-bottom: 0.375rem;
}

.suggestion-confidence {
  font-size: 0.6875rem;
  color: #666;
}

.assist-actions {
  padding: 0.5rem 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
}

.refresh-btn {
  padding: 0.375rem 0.75rem;
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #888;
  font-size: 0.75rem;
  cursor: pointer;
}

.refresh-btn:hover:not(:disabled) {
  border-color: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
