<template>
  <div class="chat-interface">
    <div class="chat-header">
      <h2>HedraSouly Chat</h2>
      <div class="chat-controls">
        <select v-model="selectedAgent" class="agent-select">
          <option value="">Select Agent...</option>
          <option v-for="agent in agents" :key="agent.id" :value="agent.id">
            {{ agent.name }}
          </option>
        </select>
      </div>
    </div>

    <div class="chat-messages" ref="messagesContainer">
      <div v-if="messages.length === 0" class="empty-state">
        <p>Start a conversation with an agent.</p>
      </div>

      <div
        v-for="msg in messages"
        :key="msg.id"
        :class="['message', msg.role]"
      >
        <div class="message-header">
          <span class="message-role">{{ msg.role === 'user' ? 'You' : 'Agent' }}</span>
          <span class="message-time">{{ formatTime(msg.created_at) }}</span>
        </div>
        <div class="message-content">{{ msg.content }}</div>
      </div>

      <div v-if="isLoading" class="message agent">
        <div class="message-content typing">Thinking...</div>
      </div>
    </div>

    <div class="chat-input-area">
      <QuickActions @action="handleQuickAction" />
      <form @submit.prevent="sendMessage" class="chat-form">
        <input
          v-model="newMessage"
          placeholder="Type your message..."
          class="chat-input"
          :disabled="isLoading"
        />
        <button type="submit" class="send-btn" :disabled="!newMessage.trim() || isLoading">
          Send
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import QuickActions from '../Components/QuickActions.vue'

const messages = ref([])
const newMessage = ref('')
const selectedAgent = ref('')
const agents = ref([])
const isLoading = ref(false)
const messagesContainer = ref(null)

onMounted(() => {
  loadAgents()
})

watch(messages, () => {
  scrollToBottom()
}, { deep: true })

async function loadAgents() {
  try {
    const res = await fetch('/api/v1/agents')
    const data = await res.json()
    if (data.success) agents.value = data.data
  } catch (e) {}
}

async function sendMessage() {
  if (!newMessage.value.trim() || isLoading.value) return

  const content = newMessage.value.trim()
  newMessage.value = ''

  // Add user message
  messages.value.push({
    id: Date.now(),
    role: 'user',
    content,
    created_at: new Date().toISOString(),
  })

  isLoading.value = true

  try {
    const res = await fetch('/api/v1/chat/send', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message: content,
        agent_id: selectedAgent.value || null,
      }),
    })
    const data = await res.json()
    if (data.success && data.data) {
      messages.value.push({
        id: data.data.id || Date.now() + 1,
        role: 'agent',
        content: data.data.content || data.data.message || 'Response received',
        created_at: new Date().toISOString(),
      })
    } else {
      messages.value.push({
        id: Date.now() + 1,
        role: 'agent',
        content: 'Error: ' + (data.message || 'Failed to get response'),
        created_at: new Date().toISOString(),
      })
    }
  } catch (e) {
    messages.value.push({
      id: Date.now() + 1,
      role: 'agent',
      content: 'Network error. Please try again.',
      created_at: new Date().toISOString(),
    })
  } finally {
    isLoading.value = false
  }
}

function handleQuickAction(action) {
  newMessage.value = action
  sendMessage()
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

function formatTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.chat-interface {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 140px);
  max-height: 800px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  overflow: hidden;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.chat-header h2 {
  margin: 0;
  font-size: 1rem;
}

.chat-controls {
  display: flex;
  gap: 0.5rem;
}

.agent-select {
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #fff;
  font-size: 0.875rem;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.empty-state {
  text-align: center;
  color: #666;
  padding: 3rem;
}

.message {
  max-width: 80%;
  padding: 0.75rem 1rem;
  border-radius: 8px;
}

.message.user {
  align-self: flex-end;
  background: rgba(74, 222, 128, 0.15);
  border: 1px solid rgba(74, 222, 128, 0.3);
}

.message.agent {
  align-self: flex-start;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.75rem;
}

.message-role {
  font-weight: 500;
  color: #4ade80;
}

.message.agent .message-role {
  color: #888;
}

.message-time {
  color: #666;
}

.message-content {
  font-size: 0.9375rem;
  line-height: 1.5;
  word-break: break-word;
}

.message-content.typing {
  color: #888;
  font-style: italic;
}

.chat-input-area {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.02);
}

.chat-form {
  display: flex;
  gap: 0.5rem;
}

.chat-input {
  flex: 1;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #fff;
  font-size: 0.9375rem;
}

.chat-input:focus {
  outline: none;
  border-color: #4ade80;
}

.send-btn {
  padding: 0.75rem 1.5rem;
  background: rgba(74, 222, 128, 0.1);
  border: 1px solid rgba(74, 222, 128, 0.3);
  border-radius: 4px;
  color: #4ade80;
  cursor: pointer;
  font-weight: 500;
}

.send-btn:hover:not(:disabled) {
  background: rgba(74, 222, 128, 0.2);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .chat-interface {
    height: calc(100vh - 160px);
  }

  .message {
    max-width: 90%;
  }
}
</style>
