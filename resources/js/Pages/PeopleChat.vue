<template>
  <div class="people-chat">
    <div class="chat-layout">
      <!-- Conversation List Sidebar -->
      <ConversationList
        :model-value="activeConversationId"
        @update:model-value="onConversationSelect"
        @new-conversation="startNewConversation"
      />

      <!-- Chat Area -->
      <div class="chat-main">
        <div v-if="!activeConversationId" class="no-selection">
          <p>Select a conversation or start a new one.</p>
        </div>

        <div v-else class="chat-container">
          <div class="chat-header">
            <div class="chat-contact">
              <div class="contact-avatar">
                {{ contactInitial }}
              </div>
              <div class="contact-info">
                <span class="contact-name">{{ contactName }}</span>
                <span class="contact-status">{{ contactStatus }}</span>
              </div>
            </div>
            <div class="chat-actions">
              <button class="action-btn" @click="showAgentAssist = !showAgentAssist">
                🤖 Assist
              </button>
            </div>
          </div>

          <div class="messages-area" ref="messagesArea">
            <div
              v-for="msg in messages"
              :key="msg.id"
              :class="['message-bubble', msg.direction]"
            >
              <div class="message-content">{{ msg.content }}</div>
              <div class="message-meta">
                <span class="message-time">{{ formatTime(msg.created_at) }}</span>
                <span v-if="msg.direction === 'outbound'" class="message-status">
                  {{ msg.status || 'sent' }}
                </span>
              </div>
            </div>

            <div v-if="isLoading" class="typing-indicator">
              <span></span><span></span><span></span>
            </div>
          </div>

          <div class="message-input-area">
            <AgentAssist
              v-if="showAgentAssist"
              :conversation-id="activeConversationId"
              @suggestion="applySuggestion"
            />
            <form @submit.prevent="sendMessage" class="input-form">
              <input
                v-model="newMessage"
                placeholder="Type a message..."
                class="message-input"
                :disabled="isLoading"
              />
              <button type="submit" class="send-btn" :disabled="!newMessage.trim() || isLoading">
                Send
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Analytics Sidebar -->
      <div v-if="activeConversationId && showAnalytics" class="analytics-sidebar">
        <ContactAnalytics :contact-id="contactId" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import ConversationList from './ConversationList.vue'
import AgentAssist from './AgentAssist.vue'
import ContactAnalytics from './ContactAnalytics.vue'

const props = defineProps({
  conversationId: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['message-sent'])

const activeConversationId = ref(props.conversationId)
const messages = ref([])
const newMessage = ref('')
const isLoading = ref(false)
const showAgentAssist = ref(false)
const showAnalytics = ref(true)
const contactId = ref(null)
const contactName = ref('')
const contactStatus = ref('')
const messagesArea = ref(null)

const contactInitial = computed(() => {
  return (contactName.value || 'C')[0].toUpperCase()
})

watch(activeConversationId, (newId) => {
  if (newId) {
    loadMessages(newId)
    loadContactInfo(newId)
  } else {
    messages.value = []
    contactName.value = ''
  }
})

onMounted(() => {
  if (props.conversationId) {
    activeConversationId.value = props.conversationId
    loadMessages(props.conversationId)
    loadContactInfo(props.conversationId)
  }
})

async function loadMessages(conversationId) {
  isLoading.value = true
  try {
    const res = await fetch(`/api/v1/conversations/${conversationId}/messages`)
    const data = await res.json()
    if (data.success) {
      messages.value = data.data || []
      scrollToBottom()
    }
  } catch (e) {
    console.error('Failed to load messages:', e)
  } finally {
    isLoading.value = false
  }
}

async function loadContactInfo(conversationId) {
  try {
    const res = await fetch(`/api/v1/conversations/${conversationId}`)
    const data = await res.json()
    if (data.success && data.data) {
      contactId.value = data.data.contact_id
      contactName.value = data.data.contact?.name || 'Unknown'
      contactStatus.value = data.data.contact?.status || 'offline'
    }
  } catch (e) {}
}

async function sendMessage() {
  if (!newMessage.value.trim() || isLoading.value) return

  const content = newMessage.value.trim()
  newMessage.value = ''

  // Optimistically add message
  const tempId = Date.now()
  messages.value.push({
    id: tempId,
    direction: 'outbound',
    content,
    created_at: new Date().toISOString(),
    status: 'sending',
  })
  scrollToBottom()

  try {
    const res = await fetch(`/api/v1/conversations/${activeConversationId.value}/send-message`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ content }),
    })
    const data = await res.json()
    if (data.success) {
      // Update temp message status
      const msg = messages.value.find(m => m.id === tempId)
      if (msg) msg.status = 'sent'
      emit('message-sent', data.data)
    } else {
      const msg = messages.value.find(m => m.id === tempId)
      if (msg) msg.status = 'failed'
    }
  } catch (e) {
    const msg = messages.value.find(m => m.id === tempId)
    if (msg) msg.status = 'failed'
  }
}

function onConversationSelect(id) {
  activeConversationId.value = id
}

function startNewConversation() {
  // Placeholder: open new conversation modal
  alert('New conversation feature coming soon')
}

function applySuggestion(suggestion) {
  newMessage.value = suggestion
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesArea.value) {
      messagesArea.value.scrollTop = messagesArea.value.scrollHeight
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
.people-chat {
  height: calc(100vh - 140px);
  max-height: 800px;
  display: flex;
  flex-direction: column;
}

.chat-layout {
  display: flex;
  flex: 1;
  min-height: 0;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  overflow: hidden;
}

.no-selection {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
}

.chat-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.chat-contact {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.contact-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(74, 222, 128, 0.15);
  color: #4ade80;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
}

.contact-info {
  display: flex;
  flex-direction: column;
}

.contact-name {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #fff;
}

.contact-status {
  font-size: 0.75rem;
  color: #888;
}

.chat-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.375rem 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #888;
  font-size: 0.75rem;
  cursor: pointer;
}

.action-btn:hover {
  background: rgba(74, 222, 128, 0.1);
  border-color: rgba(74, 222, 128, 0.3);
  color: #4ade80;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.message-bubble {
  max-width: 70%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
}

.message-bubble.inbound {
  align-self: flex-start;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-bottom-left-radius: 4px;
}

.message-bubble.outbound {
  align-self: flex-end;
  background: rgba(74, 222, 128, 0.15);
  border: 1px solid rgba(74, 222, 128, 0.3);
  border-bottom-right-radius: 4px;
}

.message-content {
  font-size: 0.9375rem;
  line-height: 1.5;
  word-break: break-word;
}

.message-meta {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.375rem;
  font-size: 0.625rem;
  color: #666;
}

.message-status {
  text-transform: capitalize;
}

.message-status.sending { color: #fbbf24; }
.message-status.sent { color: #4ade80; }
.message-status.failed { color: #f87171; }

.typing-indicator {
  display: flex;
  gap: 0.25rem;
  padding: 0.75rem 1rem;
  align-self: flex-start;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  background: #888;
  border-radius: 50%;
  animation: typing 1.4s infinite ease-in-out;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30% { transform: translateY(-4px); opacity: 1; }
}

.message-input-area {
  padding: 0.75rem 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.02);
}

.input-form {
  display: flex;
  gap: 0.5rem;
}

.message-input {
  flex: 1;
  padding: 0.625rem 0.875rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  color: #fff;
  font-size: 0.9375rem;
}

.message-input:focus {
  outline: none;
  border-color: #4ade80;
}

.send-btn {
  padding: 0.625rem 1.25rem;
  background: rgba(74, 222, 128, 0.1);
  border: 1px solid rgba(74, 222, 128, 0.3);
  border-radius: 20px;
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

.analytics-sidebar {
  width: 280px;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  overflow-y: auto;
}

@media (max-width: 1024px) {
  .analytics-sidebar {
    display: none;
  }
}

@media (max-width: 768px) {
  .people-chat {
    height: calc(100vh - 160px);
  }

  .message-bubble {
    max-width: 85%;
  }
}
</style>
