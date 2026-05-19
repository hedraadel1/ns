<template>
  <div class="flex min-h-[calc(100vh-8rem)] flex-col gap-4 p-4 text-zinc-100 md:p-6">
    <!-- Header Section -->
    <section class="border border-green-500/20 bg-zinc-950/80 p-5">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
          <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-green-400/70">Conversations Hub</p>
          <h1 class="mt-2 text-3xl font-black uppercase tracking-wide text-white">Multi-channel communication</h1>
          <p class="mt-3 text-sm leading-6 text-zinc-400">
            Browse conversations across all channels, search and filter by contact, and manage multi-channel communication in one place.
          </p>
        </div>

        <div class="flex flex-col items-end gap-3">
          <ConnectionStatus />
          <button
            @click="isCreateModalOpen = true"
            class="flex items-center gap-2 rounded-lg bg-green-500/20 px-4 py-2 text-sm font-semibold text-green-300 transition hover:bg-green-500/30"
          >
            <span>+ New Conversation</span>
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="mt-5 flex flex-wrap gap-3">
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Search conversations..."
          class="flex-1 rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white placeholder-zinc-500 focus:border-green-500/50 focus:outline-none"
        />

        <select
          v-model="selectedChannel"
          class="rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white focus:border-green-500/50 focus:outline-none"
        >
          <option value="">All Channels</option>
          <option value="whatsapp">WhatsApp</option>
          <option value="email">Email</option>
          <option value="sms">SMS</option>
          <option value="internal">Internal</option>
        </select>

        <select
          v-model="selectedStatus"
          class="rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white focus:border-green-500/50 focus:outline-none"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="archived">Archived</option>
          <option value="closed">Closed</option>
        </select>
      </div>
    </section>

    <!-- Conversations List and Detail Panel -->
    <div class="grid min-h-0 flex-1 gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
      <!-- Conversations List -->
      <div class="flex flex-col gap-3 overflow-hidden rounded-lg border border-zinc-700/50 bg-zinc-950/50">
        <!-- List Header -->
        <div class="border-b border-zinc-700/50 px-4 py-3">
          <h2 class="text-sm font-semibold text-white">{{ filteredConversations.length }} Conversations</h2>
        </div>

        <!-- Loading State -->
        <div v-if="loadingConversations" class="flex flex-1 items-center justify-center">
          <div class="text-sm text-zinc-400">Loading conversations...</div>
        </div>

        <!-- Error State -->
        <div v-else-if="conversationsError" class="border-l-4 border-red-500 bg-red-500/10 p-4 text-sm text-red-300">
          <strong>Error:</strong> {{ conversationsError }}
          <button @click="loadConversations" class="ml-2 underline hover:text-red-200">Retry</button>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredConversations.length === 0" class="flex flex-1 items-center justify-center">
          <div class="text-center text-zinc-400">
            <p class="text-sm">No conversations found</p>
          </div>
        </div>

        <!-- Conversations List -->
        <div v-else class="flex-1 overflow-y-auto">
          <div
            v-for="conversation in filteredConversations"
            :key="conversation.id"
            class="cursor-pointer border-b border-zinc-700/30 px-4 py-3 transition hover:bg-zinc-900/30"
            :class="{ 'bg-green-500/10 border-green-500/30': selectedConversation?.id === conversation.id }"
            @click="selectConversation(conversation)"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <h3 class="text-sm font-semibold text-white truncate">{{ conversation.contact_name || conversation.channel }}</h3>
                  <span class="flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold" :class="getChannelBadgeClass(conversation.channel)">
                    {{ conversation.channel }}
                  </span>
                </div>
                <p class="mt-1 text-xs text-zinc-400 truncate">{{ conversation.last_message || 'No messages' }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ formatDate(conversation.updated_at) }}</p>
              </div>
              <div class="flex-shrink-0 text-right">
                <p v-if="conversation.unread_count" class="text-xs font-semibold text-green-400">{{ conversation.unread_count }} new</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Conversation Detail Panel -->
      <div class="flex flex-col gap-3 overflow-hidden rounded-lg border border-zinc-700/50 bg-zinc-950/50">
        <div v-if="selectedConversation">
          <!-- Header -->
          <div class="border-b border-zinc-700/50 px-4 py-3">
            <h2 class="text-sm font-semibold text-white">{{ selectedConversation.contact_name }}</h2>
            <p class="mt-1 text-xs text-zinc-400">{{ selectedConversation.channel }}</p>
          </div>

          <!-- Messages Area -->
          <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
            <div
              v-for="message in selectedConversation.messages || []"
              :key="message.id"
              class="flex gap-2"
              :class="{ 'justify-end': message.is_outgoing }"
            >
              <div
                class="max-w-xs rounded-lg px-3 py-2 text-sm"
                :class="message.is_outgoing ? 'bg-green-500/20 text-green-100' : 'bg-zinc-800/50 text-zinc-100'"
              >
                {{ message.content }}
                <p class="mt-1 text-xs opacity-70">{{ formatTime(message.created_at) }}</p>
              </div>
            </div>

            <!-- Loading Messages -->
            <div v-if="loadingMessages" class="text-center text-xs text-zinc-400">Loading messages...</div>
          </div>

          <!-- Message Input -->
          <div class="border-t border-zinc-700/50 p-3">
            <div class="flex gap-2">
              <input
                v-model="messageInput"
                type="text"
                placeholder="Type a message..."
                class="flex-1 rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white placeholder-zinc-500 focus:border-green-500/50 focus:outline-none"
                @keyup.enter="sendMessage"
              />
              <button
                @click="sendMessage"
                :disabled="!messageInput || sendingMessage"
                class="rounded-lg bg-green-500/20 px-4 py-2 text-sm font-semibold text-green-300 transition hover:bg-green-500/30 disabled:opacity-50"
              >
                Send
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-1 items-center justify-center">
          <div class="text-center text-zinc-400">
            <p class="text-sm">Select a conversation to view messages</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Conversation Modal -->
    <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="rounded-lg border border-zinc-700/50 bg-zinc-950 p-6 max-w-md w-full">
        <h3 class="text-lg font-semibold text-white">Create Conversation</h3>

        <div class="mt-4 space-y-3">
          <div>
            <label class="text-sm font-medium text-zinc-400">Select Contact</label>
            <select
              v-model="newConversation.contact_id"
              class="mt-1 w-full rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white"
            >
              <option value="">Choose a contact...</option>
              <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                {{ contact.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="text-sm font-medium text-zinc-400">Channel</label>
            <select
              v-model="newConversation.channel"
              class="mt-1 w-full rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white"
            >
              <option value="whatsapp">WhatsApp</option>
              <option value="email">Email</option>
              <option value="sms">SMS</option>
              <option value="internal">Internal</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-medium text-zinc-400">Subject</label>
            <input
              v-model="newConversation.subject"
              type="text"
              placeholder="Conversation subject..."
              class="mt-1 w-full rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white placeholder-zinc-500"
            />
          </div>
        </div>

        <div class="mt-6 flex gap-3">
          <button
            @click="isCreateModalOpen = false"
            class="flex-1 rounded-lg border border-zinc-700/50 px-3 py-2 text-sm font-semibold text-zinc-300 transition hover:bg-zinc-900/30"
          >
            Cancel
          </button>
          <button
            @click="createConversation"
            :disabled="!newConversation.contact_id || !newConversation.channel"
            class="flex-1 rounded-lg bg-green-500/20 px-3 py-2 text-sm font-semibold text-green-300 transition hover:bg-green-500/30 disabled:opacity-50"
          >
            Create
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useEcho } from '../composables/useEcho';
import ConnectionStatus from '../Components/ConnectionStatus.vue';

// State
const conversations = ref([])
const selectedConversation = ref(null)
const contacts = ref([])
const searchTerm = ref('')
const selectedChannel = ref('')
const selectedStatus = ref('')
const messageInput = ref('')
const isCreateModalOpen = ref(false)

// Loading states
const loadingConversations = ref(false)
const loadingMessages = ref(false)
const sendingMessage = ref(false)

// Error state
const conversationsError = ref(null)

// New conversation form
const newConversation = ref({
  contact_id: '',
  channel: 'whatsapp',
  subject: '',
})

const { isAvailable, subscribePrivate, leaveChannel, withPollingFallback } = useEcho();
let echoChannel = null

function leaveConversationChannel() {
  if (echoChannel) {
    echoChannel.stopListening('.App\\Events\\MessageSent')
    echoChannel.stopListening('.App\\Events\\TokenStreamed')
    echoChannel.stopListening('.App\\Events\\MessageCompleted')
  }

  leaveChannel(`conversation.${selectedConversation.value?.id}`)
  echoChannel = null
}

function subscribeConversationChannel(conversationId) {
  if (!conversationId) {
    return
  }

  leaveConversationChannel()

  const channelName = `conversation.${conversationId}`

  echoChannel = withPollingFallback(
    () => subscribePrivate(
      channelName,
      {
        '.App\\Events\\MessageSent': (event) => {
          if (!selectedConversation.value || selectedConversation.value.id !== conversationId) {
            return
          }

          selectedConversation.value.messages = selectedConversation.value.messages || []
          selectedConversation.value.messages.push({
            id: event.id,
            role: event.sender === 'user' ? 'user' : 'agent',
            content: event.content,
            created_at: event.timestamp,
          })
        },
        '.App\\Events\\TokenStreamed': (event) => {
          if (!selectedConversation.value || selectedConversation.value.id !== conversationId) {
            return
          }

          const messages = selectedConversation.value.messages || []
          const lastMessage = messages[messages.length - 1]
          if (lastMessage && lastMessage.role === 'agent' && lastMessage.id === event.message_id) {
            lastMessage.content += event.token
          }
        },
        '.App\\Events\\MessageCompleted': (event) => {
          if (!selectedConversation.value || selectedConversation.value.id !== conversationId) {
            return
          }

          const messages = selectedConversation.value.messages || []
          const lastMessage = messages[messages.length - 1]
          if (lastMessage && lastMessage.id === event.message_id) {
            lastMessage.content = event.final_message
          }
        },
      },
      () => {
        loadMessages(conversationId)
        return null
      }
    ),
    () => {
      loadMessages(conversationId)
      return null
    }
  )
}

watch(selectedConversation, (conversation) => {
  if (conversation) {
    subscribeConversationChannel(conversation.id)
  } else {
    leaveConversationChannel()
  }
})

onUnmounted(() => {
  leaveConversationChannel()
})

// Filtered conversations
const filteredConversations = computed(() => {
  return conversations.value.filter(conv => {
    const matchesSearch = !searchTerm.value ||
      conv.contact_name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      conv.last_message?.toLowerCase().includes(searchTerm.value.toLowerCase())

    const matchesChannel = !selectedChannel.value || conv.channel === selectedChannel.value
    const matchesStatus = !selectedStatus.value || conv.status === selectedStatus.value

    return matchesSearch && matchesChannel && matchesStatus
  })
})

// Load conversations from API
async function loadConversations() {
  loadingConversations.value = true
  conversationsError.value = null

  try {
    const response = await fetch('/api/v1/conversations', {
      headers: { 'Accept': 'application/json' },
    })

    if (!response.ok) throw new Error('Failed to load conversations')

    const data = await response.json()
    conversations.value = data.data || data || []
  } catch (err) {
    console.error('Error loading conversations:', err)
    conversationsError.value = err.message
  } finally {
    loadingConversations.value = false
  }
}

// Load contacts for modal
async function loadContacts() {
  try {
    const response = await fetch('/api/v1/contacts', {
      headers: { 'Accept': 'application/json' },
    })

    if (!response.ok) throw new Error('Failed to load contacts')

    const data = await response.json()
    contacts.value = data.data || data || []
  } catch (err) {
    console.error('Error loading contacts:', err)
  }
}

// Select conversation
async function selectConversation(conversation) {
  selectedConversation.value = conversation
  await loadMessages(conversation.id)
}

// Load messages for conversation
async function loadMessages(conversationId) {
  loadingMessages.value = true

  try {
    const response = await fetch(`/api/v1/conversations/${conversationId}/messages`, {
      headers: { 'Accept': 'application/json' },
    })

    if (!response.ok) throw new Error('Failed to load messages')

    const data = await response.json()
    if (selectedConversation.value) {
      selectedConversation.value.messages = data.data || data || []
    }
  } catch (err) {
    console.error('Error loading messages:', err)
  } finally {
    loadingMessages.value = false
  }
}

// Send message
async function sendMessage() {
  if (!messageInput.value || !selectedConversation.value) return

  sendingMessage.value = true

  try {
    const response = await fetch(`/api/v1/conversations/${selectedConversation.value.id}/send-message`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        content: messageInput.value,
        type: 'text',
      }),
    })

    if (!response.ok) throw new Error('Failed to send message')

    messageInput.value = ''
    await loadMessages(selectedConversation.value.id)
  } catch (err) {
    console.error('Error sending message:', err)
  } finally {
    sendingMessage.value = false
  }
}

// Create conversation
async function createConversation() {
  if (!newConversation.value.contact_id) return

  try {
    const response = await fetch('/api/v1/conversations', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(newConversation.value),
    })

    if (!response.ok) throw new Error('Failed to create conversation')

    isCreateModalOpen.value = false
    newConversation.value = { contact_id: '', channel: 'whatsapp', subject: '' }
    await loadConversations()
  } catch (err) {
    console.error('Error creating conversation:', err)
  }
}

// Helper functions
function getChannelBadgeClass(channel) {
  const classes = {
    whatsapp: 'bg-green-500/20 text-green-300',
    email: 'bg-blue-500/20 text-blue-300',
    sms: 'bg-purple-500/20 text-purple-300',
    internal: 'bg-gray-500/20 text-gray-300',
  }
  return classes[channel] || classes.internal
}

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diffMs = now - d
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`

  return d.toLocaleDateString()
}

function formatTime(date) {
  if (!date) return ''
  return new Date(date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

// Load initial data
onMounted(() => {
  loadConversations()
  loadContacts()
})
</script>
