<template>
  <div class="conversation-list">
    <div class="list-header">
      <h3>Conversations</h3>
      <button class="new-btn" @click="startNew">+ New</button>
    </div>

    <div v-if="conversations.length === 0" class="empty-state">
      <p>No conversations yet.</p>
    </div>

    <div v-else class="conversation-items">
      <div
        v-for="conv in conversations"
        :key="conv.id"
        :class="['conversation-item', { active: activeId === conv.id }]"
        @click="selectConversation(conv.id)"
      >
        <div class="conv-avatar">
          {{ (conv.contact_name || 'C')[0].toUpperCase() }}
        </div>
        <div class="conv-details">
          <span class="conv-name">{{ conv.contact_name || 'Unknown' }}</span>
          <span class="conv-preview">{{ conv.last_message || 'No messages' }}</span>
        </div>
        <div class="conv-meta">
          <span class="conv-time">{{ formatTime(conv.updated_at) }}</span>
          <span v-if="conv.unread_count" class="unread-badge">
            {{ conv.unread_count }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'new-conversation'])

const conversations = ref([])
const activeId = ref(props.modelValue)

watch(
  () => props.modelValue,
  (newValue) => {
    activeId.value = newValue
  }
)

onMounted(() => {
  loadConversations()
})

async function loadConversations() {
  try {
    const res = await fetch('/api/v1/conversations')
    const data = await res.json()
    if (data.success) conversations.value = data.data
  } catch (e) {}
}

function selectConversation(id) {
  activeId.value = id
  emit('update:modelValue', id)
}

function startNew() {
  emit('new-conversation')
}

function formatTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.conversation-list {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: rgba(255, 255, 255, 0.02);
  border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.list-header h3 {
  margin: 0;
  font-size: 0.9375rem;
}

.new-btn {
  padding: 0.375rem 0.75rem;
  background: rgba(74, 222, 128, 0.1);
  border: 1px solid rgba(74, 222, 128, 0.3);
  border-radius: 4px;
  color: #4ade80;
  font-size: 0.75rem;
  cursor: pointer;
}

.empty-state {
  text-align: center;
  color: #666;
  padding: 2rem;
  font-size: 0.875rem;
}

.conversation-items {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: background 0.2s;
}

.conversation-item:hover {
  background: rgba(255, 255, 255, 0.03);
}

.conversation-item.active {
  background: rgba(74, 222, 128, 0.08);
  border-left: 2px solid #4ade80;
}

.conv-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(74, 222, 128, 0.15);
  color: #4ade80;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.conv-details {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.conv-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: #fff;
}

.conv-preview {
  font-size: 0.75rem;
  color: #888;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.conv-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
  flex-shrink: 0;
}

.conv-time {
  font-size: 0.625rem;
  color: #666;
}

.unread-badge {
  background: #4ade80;
  color: #000;
  font-size: 0.625rem;
  font-weight: bold;
  padding: 0.125rem 0.375rem;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}
</style>
