<template>
  <section class="log-stream">
    <div class="stream-header">
      <h1>Live Log Stream</h1>
      <div class="stream-controls">
        <select v-model="selectedLevel" @change="filterLogs">
          <option value="">All Levels</option>
          <option v-for="l in levels" :key="l.value" :value="l.value">
            {{ l.label }}
          </option>
        </select>
        <select v-model="selectedCategory" @change="filterLogs">
          <option value="">All Categories</option>
          <option v-for="c in categories" :key="c.value" :value="c.value">
            {{ c.label }}
          </option>
        </select>
        <button @click="togglePause" class="pause-btn">
          {{ isPaused ? 'Resume' : 'Pause' }}
        </button>
        <button @click="clearLogs" class="clear-btn">Clear</button>
      </div>
    </div>

    <div class="stream-stats">
      <span class="stat">Total: {{ stats.total ?? 0 }}</span>
      <span class="stat">Today: {{ stats.today ?? 0 }}</span>
      <span class="stat error-stat">Errors Today: {{ stats.errors_today ?? 0 }}</span>
    </div>

    <div class="log-container" ref="logContainer">
      <div v-if="loading && logs.length === 0" class="loading">
        Connecting to log stream...
      </div>

      <div v-else-if="logs.length === 0" class="empty">
        No logs to display.
      </div>

      <div
        v-for="log in logs"
        :key="log.id"
        :class="['log-entry', log.level]"
      >
        <div class="log-header">
          <span :class="['level-badge', log.level]">{{ log.level }}</span>
          <span class="log-category">{{ log.category }}</span>
          <span class="log-time">{{ formatTime(log.created_at) }}</span>
        </div>
        <div class="log-message">{{ log.message }}</div>
        <div v-if="log.context && Object.keys(log.context).length" class="log-context">
          <pre>{{ JSON.stringify(log.context, null, 2) }}</pre>
        </div>
      </div>
    </div>

    <div class="stream-footer">
      <span>Showing {{ logs.length }} of {{ stats.total ?? 0 }} logs</span>
      <button @click="loadMore" v-if="hasMore" class="load-more-btn">
        Load More
      </button>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'

const logs = ref([])
const levels = ref([])
const categories = ref([])
const stats = ref({})
const selectedLevel = ref('')
const selectedCategory = ref('')
const isPaused = ref(false)
const loading = ref(false)
const hasMore = ref(false)
const logContainer = ref(null)
let eventSource = null
let pollInterval = null

async function fetchLevels() {
  try {
    const res = await fetch('/api/v1/logs/levels')
    const data = await res.json()
    if (data.success) levels.value = data.data
  } catch (e) {}
}

async function fetchCategories() {
  try {
    const res = await fetch('/api/v1/logs/categories')
    const data = await res.json()
    if (data.success) categories.value = data.data
  } catch (e) {}
}

async function fetchStats() {
  try {
    const res = await fetch('/api/v1/logs/stats')
    const data = await res.json()
    if (data.success) stats.value = data.data
  } catch (e) {}
}

async function fetchLogs(append = false) {
  if (isPaused.value && append) return

  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('per_page', '50')
    if (selectedLevel.value) params.append('level', selectedLevel.value)
    if (selectedCategory.value) params.append('category', selectedCategory.value)
    if (append) params.append('page', '2')

    const res = await fetch(`/api/v1/logs?${params}`)
    const data = await res.json()
    if (data.success) {
      if (append) {
        logs.value = [...logs.value, ...data.data]
      } else {
        logs.value = data.data
      }
      hasMore.value = data.pagination?.current_page < data.pagination?.last_page
    }
  } catch (e) {
    console.error('Failed to fetch logs:', e)
  } finally {
    loading.value = false
  }
}

function filterLogs() {
  fetchLogs(false)
}

function togglePause() {
  isPaused.value = !isPaused.value
}

function clearLogs() {
  logs.value = []
}

function loadMore() {
  fetchLogs(true)
}

function formatTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleTimeString()
}

function scrollToBottom() {
  nextTick(() => {
    if (logContainer.value) {
      logContainer.value.scrollTop = logContainer.value.scrollHeight
    }
  })
}

function startPolling() {
  pollInterval = setInterval(() => {
    if (!isPaused.value) {
      fetchLogs()
      fetchStats()
    }
  }, 5000)
}

function stopPolling() {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

onMounted(() => {
  fetchLevels()
  fetchCategories()
  fetchStats()
  fetchLogs()
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})

watch(logs, () => {
  scrollToBottom()
}, { deep: true })
</script>

<style scoped>
.log-stream {
  padding: 1rem;
}

.stream-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.stream-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.stream-controls select {
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #fff;
}

.pause-btn,
.clear-btn,
.load-more-btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: transparent;
  color: #888;
}

.pause-btn:hover,
.clear-btn:hover,
.load-more-btn:hover {
  border-color: rgba(255, 255, 255, 0.4);
  color: #fff;
}

.stream-stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
}

.stat {
  font-size: 0.875rem;
  color: #888;
}

.error-stat {
  color: #ef4444;
}

.log-container {
  max-height: 600px;
  overflow-y: auto;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.3);
}

.log-entry {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.log-entry:last-child {
  border-bottom: none;
}

.log-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.level-badge {
  font-size: 0.7rem;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
  font-weight: bold;
}

.level-badge.debug { background: rgba(107, 114, 128, 0.3); color: #9ca3af; }
.level-badge.info { background: rgba(59, 130, 246, 0.3); color: #60a5fa; }
.level-badge.notice { background: rgba(6, 182, 212, 0.3); color: #22d3ee; }
.level-badge.warning { background: rgba(234, 179, 8, 0.3); color: #facc15; }
.level-badge.error { background: rgba(239, 68, 68, 0.3); color: #f87171; }
.level-badge.critical { background: rgba(239, 68, 68, 0.5); color: #ef4444; }
.level-badge.alert { background: rgba(239, 68, 68, 0.5); color: #ef4444; }
.level-badge.emergency { background: rgba(239, 68, 68, 0.7); color: #fff; }

.log-category {
  font-size: 0.75rem;
  color: #888;
  text-transform: capitalize;
}

.log-time {
  font-size: 0.75rem;
  color: #666;
  margin-left: auto;
}

.log-message {
  font-size: 0.875rem;
  color: #ccc;
  margin-bottom: 0.5rem;
  word-break: break-word;
}

.log-context {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
  padding: 0.5rem;
  margin-top: 0.5rem;
}

.log-context pre {
  margin: 0;
  font-size: 0.75rem;
  color: #888;
  white-space: pre-wrap;
  word-break: break-word;
}

.loading,
.empty {
  text-align: center;
  padding: 2rem;
  color: #888;
}

.stream-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: #888;
  font-size: 0.875rem;
}
</style>
