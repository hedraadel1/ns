<template>
  <Teleport to="body">
    <transition name="nx-log-viewer-modal">
      <div v-if="open" class="nx-log-viewer-overlay" @click.self="closeModal">
        <section class="nx-log-viewer-panel" role="dialog" aria-modal="true" aria-label="Log viewer">
          <header class="nx-log-viewer-header">
            <div>
              <p class="subtitle">Operational logs</p>
              <h2>Live log viewer</h2>
              <p class="description">Stream events in real time, filter severity and category, and export logs.</p>
            </div>

            <div class="header-actions">
              <button type="button" class="action-button" @click="exportLogs">Export JSON</button>
              <button type="button" class="close-button" @click="closeModal" aria-label="Close log viewer">✕</button>
            </div>
          </header>

          <div class="nx-log-viewer-controls">
            <label class="field-group">
              <span>Search</span>
              <input
                v-model="searchText"
                type="search"
                placeholder="Search logs..."
                @keyup.enter="applyFilters"
              />
            </label>

            <label class="field-group">
              <span>Level</span>
              <select v-model="selectedLevel">
                <option value="">All levels</option>
                <option v-for="level in levels" :key="level.value" :value="level.value">{{ level.label }}</option>
              </select>
            </label>

            <label class="field-group">
              <span>Category</span>
              <select v-model="selectedCategory">
                <option value="">All categories</option>
                <option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option>
              </select>
            </label>

            <button type="button" class="toggle-button" @click="togglePaused">
              {{ paused ? 'Resume stream' : 'Pause stream' }}
            </button>
          </div>

          <div class="nx-log-viewer-status">
            <span class="status-pill">{{ filteredLogs.length }} visible</span>
            <span class="status-pill">{{ paused ? 'Paused' : 'Live' }}</span>
            <span class="status-pill">{{ lastSyncedLabel }}</span>
            <span v-if="error" class="status-pill status-error">{{ error }}</span>
          </div>

          <div class="nx-log-viewer-body">
            <div class="nx-log-header-row">
              <span class="detail-label">Timestamp</span>
              <span class="detail-label">Level</span>
              <span class="detail-label">Category</span>
              <span class="detail-label">Message</span>
            </div>

            <div ref="logContainer" class="nx-log-list">
              <div v-if="loading && !logs.length" class="empty-state">
                <p>Connecting to log stream…</p>
              </div>

              <div v-else-if="!filteredLogs.length" class="empty-state">
                <p>No log entries match the current filters.</p>
              </div>

              <article
                v-for="log in filteredLogs"
                :key="logKey(log)"
                class="nx-log-row"
              >
                <div class="log-row-meta">
                  <span class="mono">{{ displayTimestamp(log) }}</span>
                  <span :class="['level-badge', levelBadgeClass(log.level)]">{{ displayLevel(log.level) }}</span>
                  <span class="category-pill">{{ displayCategory(log.category) }}</span>
                </div>

                <div class="log-row-message">
                  <p>{{ displayMessage(log) }}</p>
                  <button type="button" class="inspect-button" @click="toggleExpanded(log)">
                    {{ isExpanded(log) ? 'Hide context' : 'Inspect' }}
                  </button>
                </div>

                <div v-if="isExpanded(log)" class="log-row-context">
                  <pre>{{ prettyJson(log.context ?? log.meta ?? log.data ?? log) }}</pre>
                </div>
              </article>
            </div>
          </div>
        </section>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { Teleport, computed, defineEmits, defineProps, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
})
const emit = defineEmits(['close'])

const logs = ref([])
const loading = ref(false)
const error = ref('')
const paused = ref(false)
const searchText = ref('')
const selectedLevel = ref('')
const selectedCategory = ref('')
const categories = ref([])
const levels = ref([
  { label: 'Debug', value: 'debug' },
  { label: 'Info', value: 'info' },
  { label: 'Notice', value: 'notice' },
  { label: 'Warning', value: 'warning' },
  { label: 'Error', value: 'error' },
  { label: 'Critical', value: 'critical' },
  { label: 'Alert', value: 'alert' },
  { label: 'Emergency', value: 'emergency' },
])
const expandedLogIds = ref(new Set())
const logContainer = ref(null)
const lastSyncedAt = ref(null)
let eventChannel = null

const filteredLogs = computed(() => {
  return logs.value.filter((log) => {
    const levelMatches = !selectedLevel.value || String(log.level || '').toLowerCase() === selectedLevel.value.toLowerCase()
    const categoryMatches = !selectedCategory.value || String(log.category || log.type || '').toLowerCase() === selectedCategory.value.toLowerCase()
    const query = String(searchText.value || '').trim().toLowerCase()
    const searchMatches = !query || [displayMessage(log), displayLevel(log), displayCategory(log), String(log.source ?? ''), String(log.actor ?? '')]
      .some((value) => String(value).toLowerCase().includes(query))

    return levelMatches && categoryMatches && searchMatches
  })
})

const lastSyncedLabel = computed(() => {
  if (!lastSyncedAt.value) return 'Never'
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(lastSyncedAt.value)
})

watch(
  () => props.open,
  async (isOpen) => {
    if (isOpen) {
      await loadCategories()
      await loadLogs()
      subscribeToLogs()
    } else {
      unsubscribeFromLogs()
    }
  },
  { immediate: true }
)

function closeModal() {
  emit('close')
}

function togglePaused() {
  paused.value = !paused.value
}

function applyFilters() {
  lastSyncedAt.value = new Date()
}

async function loadCategories() {
  try {
    const response = await axios.get('/api/v1/logs/categories')
    const payload = response.data
    categories.value = normalizeOptionList(payload)
  } catch (err) {
    categories.value = []
  }
}

async function loadLogs() {
  loading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams()
    params.set('per_page', '100')
    if (selectedLevel.value) params.set('level', selectedLevel.value)
    if (selectedCategory.value) params.set('category', selectedCategory.value)
    if (searchText.value) params.set('search', searchText.value)

    const response = await axios.get(`/api/v1/logs?${params.toString()}`)
    logs.value = Array.isArray(response.data) ? response.data : response.data?.data ?? response.data?.logs ?? response.data?.items ?? []
    lastSyncedAt.value = new Date()
    await nextTick(scrollToBottom)
  } catch (err) {
    error.value = 'Unable to load logs'
    logs.value = []
    console.error(err)
  } finally {
    loading.value = false
  }
}

function normalizeOptionList(payload) {
  const data = Array.isArray(payload)
    ? payload
    : Array.isArray(payload?.data)
      ? payload.data
      : payload?.data?.items ?? payload?.items ?? []

  return (Array.isArray(data) ? data : []).map((item) => {
    if (typeof item === 'string' || typeof item === 'number') {
      return { label: String(item), value: String(item) }
    }
    const value = item.value ?? item.key ?? item.id ?? item.slug ?? item.name
    const label = item.label ?? item.name ?? value
    return { label: String(label), value: String(value) }
  })
}

function scrollToBottom() {
  if (!logContainer.value) return
  logContainer.value.scrollTop = logContainer.value.scrollHeight
}

function logKey(log) {
  return log.id ?? log.uuid ?? log.log_id ?? `${String(log.timestamp ?? log.created_at ?? log.time ?? '')}-${String(log.level ?? '')}-${Math.random()}`
}

function displayTimestamp(log) {
  const raw = log?.timestamp ?? log?.created_at ?? log?.createdAt ?? log?.time ?? log?.date
  if (!raw) return 'Unknown time'
  const date = new Date(raw)
  if (Number.isNaN(date.getTime())) return String(raw)
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(date)
}

function displayLevel(level) {
  if (!level) return 'UNKNOWN'
  return String(level).toUpperCase()
}

function displayCategory(category) {
  if (!category) return 'uncategorized'
  return String(category)
}

function displayMessage(log) {
  if (!log) return '—'
  return String(log.message ?? log.summary ?? log.text ?? log.description ?? JSON.stringify(log))
}

function levelBadgeClass(level) {
  const normalized = String(level || '').toLowerCase()
  if (['error', 'critical', 'alert', 'emergency', 'failed'].includes(normalized)) {
    return 'error'
  }
  if (['warning', 'warn'].includes(normalized)) {
    return 'warning'
  }
  if (['info', 'notice'].includes(normalized)) {
    return 'info'
  }
  return 'success'
}

function prettyJson(value) {
  try {
    return JSON.stringify(value ?? {}, null, 2)
  } catch {
    return String(value)
  }
}

function isExpanded(log) {
  return expandedLogIds.value.has(logKey(log))
}

function toggleExpanded(log) {
  const key = logKey(log)
  if (expandedLogIds.value.has(key)) {
    expandedLogIds.value.delete(key)
  } else {
    expandedLogIds.value.add(key)
  }
}

function exportLogs() {
  const payload = JSON.stringify(filteredLogs.value, null, 2)
  const blob = new Blob([payload], { type: 'application/json' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `log-viewer-${new Date().toISOString()}.json`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function transformEventPayload(payload) {
  return payload?.log ?? payload?.data ?? payload
}

function eventLogCreated(event) {
  const incoming = transformEventPayload(event)
  const key = logKey(incoming)
  if (logs.value.some((entry) => logKey(entry) === key)) return
  logs.value.push(incoming)
  lastSyncedAt.value = new Date()
  if (!paused.value) {
    nextTick(scrollToBottom)
  }
}

function subscribeToLogs() {
  if (!props.open || !window.Echo || eventChannel) return
  try {
    eventChannel = window.Echo.private('logs')
    eventChannel.listen('LogCreated', eventLogCreated)
  } catch (err) {
    console.warn('Unable to subscribe to log channel', err)
  }
}

function unsubscribeFromLogs() {
  if (!window.Echo || !eventChannel) return
  try {
    eventChannel.stopListening('LogCreated')
    window.Echo.leave('logs')
  } catch (err) {
    console.warn('Unable to unsubscribe from log channel', err)
  } finally {
    eventChannel = null
  }
}

onBeforeUnmount(() => {
  unsubscribeFromLogs()
})
</script>

<style scoped>
.nx-log-viewer-overlay {
  position: fixed;
  inset: 0;
  z-index: 1200;
  background: rgba(0, 0, 0, 0.68);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.nx-log-viewer-panel {
  width: min(1200px, 100%);
  max-height: min(95vh, 100%);
  background: rgba(12, 15, 22, 0.96);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 24px;
  box-shadow: 0 35px 120px rgba(0, 0, 0, 0.45);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.nx-log-viewer-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.subtitle {
  margin: 0;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #10b981;
}

h2 {
  margin: 0.5rem 0 0;
  font-size: 1.75rem;
  color: #ffffff;
}

.description {
  margin: 0.75rem 0 0;
  color: #cbd5e1;
  max-width: 34rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.action-button,
.toggle-button,
.close-button,
.inspect-button {
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.04);
  color: #e2e8f0;
  padding: 0.8rem 1rem;
  border-radius: 999px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: transform 150ms ease, background-color 150ms ease, border-color 150ms ease;
}

.action-button:hover,
.toggle-button:hover,
.inspect-button:hover,
.close-button:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-1px);
}

.close-button {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.nx-log-viewer-controls {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
  padding: 20px 24px;
  background: rgba(15, 20, 34, 0.95);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  color: #cbd5e1;
}

.field-group span {
  font-size: 0.82rem;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: #94a3b8;
}

.field-group input,
.field-group select {
  width: 100%;
  min-height: 44px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: rgba(15, 20, 34, 0.9);
  color: #e2e8f0;
  border-radius: 14px;
  padding: 0.95rem 1rem;
  outline: none;
}

.field-group input:focus,
.field-group select:focus {
  border-color: rgba(16, 185, 129, 0.65);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
}

.nx-log-viewer-status {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  padding: 18px 24px;
  background: rgba(15, 20, 34, 0.96);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.status-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.65rem 0.95rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(148, 163, 184, 0.08);
  color: #e2e8f0;
  font-size: 0.82rem;
}

.status-error {
  border-color: rgba(248, 113, 113, 0.32);
  background: rgba(248, 113, 113, 0.12);
  color: #fda4af;
}

.nx-log-viewer-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.nx-log-header-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 2.5fr;
  gap: 1rem;
  padding: 18px 24px 12px;
  color: #94a3b8;
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.nx-log-list {
  flex: 1;
  overflow-y: auto;
  padding: 0 24px 24px;
}

.nx-log-row {
  padding: 1rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.log-row-meta {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 1rem;
  align-items: center;
  color: #cbd5e1;
  margin-bottom: 0.85rem;
}

.mono {
  font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  font-size: 0.82rem;
  color: #94a3b8;
}

.level-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 86px;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  text-transform: uppercase;
  font-size: 0.72rem;
  letter-spacing: 0.08em;
  font-weight: 700;
}

.level-badge.success {
  background: rgba(16, 185, 129, 0.14);
  color: #a7f3d0;
}

.level-badge.info {
  background: rgba(56, 189, 248, 0.14);
  color: #bfdbfe;
}

.level-badge.warning {
  background: rgba(245, 158, 11, 0.14);
  color: #fde68a;
}

.level-badge.error {
  background: rgba(248, 113, 113, 0.16);
  color: #fecaca;
}

.category-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(148, 163, 184, 0.08);
  color: #cbd5e1;
  font-size: 0.8rem;
}

.log-row-message {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: center;
}

.log-row-message p {
  margin: 0;
  color: #e2e8f0;
  line-height: 1.6;
  flex: 1 1 60%;
}

.log-row-context {
  margin-top: 1rem;
  padding: 1rem;
  background: rgba(15, 20, 34, 0.92);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
}

.log-row-context pre {
  margin: 0;
  overflow-x: auto;
  font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  font-size: 0.82rem;
  line-height: 1.5;
  color: #cbd5e1;
  white-space: pre-wrap;
  word-break: break-word;
}

@media (max-width: 900px) {
  .nx-log-viewer-controls {
    grid-template-columns: 1fr;
  }

  .nx-log-header-row,
  .log-row-meta {
    grid-template-columns: 1fr;
  }
}

.nx-log-viewer-modal-enter-active,
.nx-log-viewer-modal-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.nx-log-viewer-modal-enter-from,
.nx-log-viewer-modal-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.98);
}
</style>
