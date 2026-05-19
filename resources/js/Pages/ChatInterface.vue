<template>
  <div class="chat-interface">
    <div class="chat-header">
      <div>
        <p class="eyebrow">Live AI conversation</p>
        <h2>HedraSouly Chat</h2>
        <p class="subhead">Start a session, choose an agent, and watch real-time responses stream in.</p>
      </div>

      <div class="chat-controls">
        <NxContextBar :percentage="chatStore.tokenPercentage" :tokens="chatStore.contextTokens" :maxTokens="chatStore.maxTokens" />
        <NxVoiceOrb @click="activateVoiceMode" />
        <select v-model="selectedAgent" class="agent-select">
          <option value="">Select Agent...</option>
          <option v-for="agent in agents" :key="agent.id" :value="agent.id">
            {{ agent.name }}
          </option>
        </select>
      </div>
    </div>

    <NxAiStatusRow :step="currentStep" />

    <div class="chat-messages" ref="messagesContainer">
      <div v-if="visibleMessages.length === 0" class="empty-state">
        <p>Begin typing or tap a quick action to send your first message.</p>
      </div>

      <div v-for="msg in visibleMessages" :key="msg.id" :class="['message', msg.role]">
        <div class="message-header">
          <span class="message-role">{{ msg.role === 'user' ? 'You' : 'Agent' }}</span>
          <span class="message-time">{{ formatTime(msg.created_at || msg.timestamp) }}</span>
        </div>

        <template v-if="msg.role === 'agent'">
          <NxAiBubble :content="msg.content" :streaming="msg.streaming" />
        </template>

        <template v-else>
          <div class="message-content">{{ msg.content }}</div>
        </template>
      </div>
    </div>

    <div class="chat-input-area">
      <QuickActions @action="handleQuickAction" />
      <form @submit.prevent="sendMessage" class="chat-form">
        <input
          v-model="chatStore.draft"
          placeholder="Type your message..."
          class="chat-input"
          :disabled="isLoading"
        />
        <button type="submit" class="send-btn" :disabled="!chatStore.draft.trim() || isLoading">
          {{ isLoading ? 'Sending...' : 'Send' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue'
import axios from 'axios'
import { useChat } from '../stores/useChat'
import QuickActions from '../Components/QuickActions.vue'
import NxAiBubble from '../Components/NxAiBubble.vue'
import NxAiStatusRow from '../Components/NxAiStatusRow.vue'
import NxContextBar from '../Components/NxContextBar.vue'
import NxVoiceOrb from '../Components/NxVoiceOrb.vue'

const chatStore = useChat()
const selectedAgent = ref('')
const agents = ref([])
const isLoading = ref(false)
const messagesContainer = ref(null)
const currentStep = ref('Ready')
const sessionId = ref(`session-${Date.now()}`)
let echoChannel = null

const visibleMessages = computed(() => chatStore.messages.filter((message) => message.sessionId === chatStore.sessionId))

function activateVoiceMode() {
  currentStep.value = 'Listening'
}

onMounted(() => {
  chatStore.setSession(sessionId.value)
  chatStore.clearDraft()
  setupEcho()
  loadAgents()
})

onUnmounted(() => {
  if (echoChannel && typeof window !== 'undefined' && window.Echo) {
    echoChannel.stopListening('*')
    window.Echo.leaveChannel(`private-chat.${sessionId.value}`)
  }
})

watch(
  () => chatStore.messages.length,
  () => {
    scrollToBottom()
  },
)

async function setupEcho() {
  if (typeof window === 'undefined' || !window.Echo) return

  echoChannel = window.Echo.private(`chat.${sessionId.value}`)

  echoChannel.listen('TokenStreamed', (event) => {
    chatStore.streamToken(event.token)
    currentStep.value = 'Streaming response'
  })

  echoChannel.listen('MessageCompleted', () => {
    chatStore.finalizeMessage()
    currentStep.value = 'Completed'
  })

  echoChannel.listen('MessageReceived', (event) => {
    if (event.message) {
      chatStore.addMessage({ ...event.message, sessionId: sessionId.value })
    }
  })
}

async function loadAgents() {
  try {
    const res = await fetch('/api/v1/agents')
    const data = await res.json()
    if (data.success) {
      agents.value = data.data
    }
  } catch (error) {
    console.warn('Unable to load agents', error)
  }
}

async function sendMessage() {
  if (!chatStore.draft.trim() || isLoading.value) return

  const content = chatStore.draft.trim()
  chatStore.sendMessage(content)
  chatStore.setDraft('')
  isLoading.value = true
  currentStep.value = 'Sending'

  const placeholderId = Date.now() + 1
  chatStore.addMessage({
    id: placeholderId,
    role: 'agent',
    content: '',
    sessionId: sessionId.value,
    created_at: new Date().toISOString(),
    streaming: true,
  })

  try {
    const response = await axios.post('/api/v1/chat/send', {
      message: content,
      agent_id: selectedAgent.value || null,
      session_id: sessionId.value,
    })

    const payload = response.data?.data ?? response.data
    if (payload) {
      const existing = chatStore.messages.find((msg) => msg.id === placeholderId)
      if (existing) {
        existing.content = payload.content || payload.message || existing.content
        existing.streaming = false
      }
    }

    currentStep.value = 'Waiting for response'
  } catch (error) {
    const existing = chatStore.messages.find((msg) => msg.id === placeholderId)
    if (existing) {
      existing.content = 'Network error. Please try again.'
      existing.streaming = false
    }
    currentStep.value = 'Error'
  } finally {
    isLoading.value = false
  }
}

function handleQuickAction(action) {
  chatStore.setDraft(action)
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
  max-height: 820px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 18px;
  overflow: hidden;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.eyebrow {
  margin: 0 0 0.35rem;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #a5f3fc;
}

h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #f8fafc;
}

.subhead {
  margin: 0.5rem 0 0;
  color: rgba(255, 255, 255, 0.72);
  font-size: 0.95rem;
}

.chat-controls {
  display: grid;
  grid-template-columns: minmax(160px, 1fr) auto auto;
  gap: 0.85rem;
  width: min(100%, 560px);
}

.agent-select {
  width: 100%;
  min-width: 0;
  padding: 0.9rem 1rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.05);
  color: #f8fafc;
  appearance: none;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.empty-state {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  padding: 3rem 1rem;
}

.message {
  width: fit-content;
  max-width: 82%;
  border-radius: 18px;
}

.message.user {
  align-self: flex-end;
}

.message.agent {
  align-self: flex-start;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.65rem;
  font-size: 0.78rem;
  color: rgba(255, 255, 255, 0.72);
}

.message-role {
  font-weight: 600;
  color: #a5f3fc;
}

.message-time {
  white-space: nowrap;
}

.chat-input-area {
  padding: 1.25rem;
  background: rgba(255, 255, 255, 0.03);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.chat-form {
  display: flex;
  gap: 0.85rem;
  align-items: center;
}

.chat-input {
  flex: 1;
  min-width: 0;
  padding: 1rem 1.1rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #f8fafc;
  outline: none;
}

.send-btn {
  min-width: 110px;
  padding: 1rem 1.3rem;
  border: none;
  border-radius: 999px;
  background: linear-gradient(135deg, #6366F1, #8B5CF6);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.send-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.send-btn:not(:disabled):hover {
  transform: translateY(-1px);
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
