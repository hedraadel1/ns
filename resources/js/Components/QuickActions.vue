<template>
  <div class="quick-actions">
    <button
      v-for="action in actions"
      :key="action.id"
      class="action-btn"
      @click="$emit('action', action.prompt)"
      :title="action.label"
    >
      <span class="action-icon">{{ action.icon }}</span>
      <span class="action-label">{{ action.label }}</span>
    </button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

defineEmits(['action'])

const actions = [
  { id: 'summarize', label: 'Summarize', icon: '📝', prompt: 'Summarize the conversation' },
  { id: 'analyze', label: 'Analyze', icon: '🔍', prompt: 'Analyze this conversation' },
  { id: 'draft', label: 'Draft Reply', icon: '✍️', prompt: 'Draft a reply' },
  { id: 'translate', label: 'Translate', icon: '🌐', prompt: 'Translate this message' },
  { id: 'sentiment', label: 'Sentiment', icon: '😊', prompt: 'What is the sentiment?' },
  { id: 'action-items', label: 'Action Items', icon: '✅', prompt: 'Extract action items' },
]
</script>

<style scoped>
.quick-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  color: #888;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  background: rgba(74, 222, 128, 0.1);
  border-color: rgba(74, 222, 128, 0.3);
  color: #4ade80;
}

.action-icon {
  font-size: 0.875rem;
}

.action-label {
  white-space: nowrap;
}
</style>
