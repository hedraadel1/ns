<template>
  <Teleport to="body">
    <transition name="nx-queue-modal">
      <div v-if="open" class="nx-queue-overlay" @click.self="closeModal">
        <section class="nx-queue-panel" role="dialog" aria-modal="true" aria-label="Queue manager">
          <header class="queue-header">
            <div>
              <p class="subtitle">Task queue</p>
              <h2>Queued & running jobs</h2>
              <p class="description">Monitor active work items, sort by priority, and cancel or retry jobs instantly.</p>
            </div>
            <button type="button" class="close-action" @click="closeModal" aria-label="Close queue manager">×</button>
          </header>

          <div class="queue-toolbar">
            <button type="button" class="toolbar-button" @click="refreshQueue">Refresh</button>
            <button type="button" class="toolbar-button" :class="{ active: sortField === 'status' }" @click="sortBy('status')">Sort status</button>
            <button type="button" class="toolbar-button" :class="{ active: sortField === 'priority' }" @click="sortBy('priority')">Sort priority</button>
            <button type="button" class="toolbar-button" :class="{ active: sortField === 'created_at' }" @click="sortBy('created_at')">Sort newest</button>
          </div>

          <div class="queue-body">
            <div class="queue-overview">
              <span class="overview-pill">{{ tasks.length }} jobs</span>
              <span class="overview-pill">Sorted by {{ sortFieldLabel }}</span>
              <span class="overview-pill">{{ connectionStatus }}</span>
            </div>

            <div v-if="error" class="queue-error">{{ error }}</div>

            <div class="queue-table-wrapper">
              <table class="queue-table">
                <thead>
                  <tr>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Started</th>
                    <th class="actions-column">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="task in sortedTasks" :key="taskKey(task)" :class="rowHighlight(task.key)">
                    <td>
                      <p class="job-title">{{ task.title || task.name || 'Unnamed job' }}</p>
                      <p class="job-meta">{{ task.description || task.subtitle || 'No description available' }}</p>
                    </td>
                    <td>
                      <span :class="['status-pill', statusBadgeClass(task.status)]">{{ displayStatus(task.status) }}</span>
                      <p class="job-progress" v-if="task.progress">{{ task.progress }}</p>
                    </td>
                    <td>{{ task.priority ?? '—' }}</td>
                    <td>{{ displayTimestamp(task.created_at) }}</td>
                    <td class="actions-column">
                      <NxActionButton
                        variant="secondary"
                        :loading="loadingTask === task.key"
                        :disabled="task.status === 'completed' || task.status === 'cancelled' || task.status === 'failed'
                          || loadingTask === task.key || retryingTask === task.key"
                        @click="cancelJob(task)"
                      >
                        Cancel
                      </NxActionButton>

                      <NxActionButton
                        variant="ghost"
                        :loading="retryingTask === task.key"
                        @click="retryJob(task)"
                      >
                        Retry
                      </NxActionButton>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { Teleport, computed, defineEmits, defineProps, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import NxActionButton from './NxActionButton.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
})
const emit = defineEmits(['close'])

const tasks = ref([])
const loading = ref(false)
const error = ref('')
const sortField = ref('status')
const sortDirection = ref('asc')
const loadingTask = ref(null)
const retryingTask = ref(null)
const updatedKeys = ref(new Set())
let taskChannel = null

const connectionStatus = computed(() => (taskChannel ? 'Live' : 'Idle'))
const sortFieldLabel = computed(() => {
  switch (sortField.value) {
    case 'priority': return 'priority'
    case 'created_at': return 'start time'
    default: return 'status'
  }
})

const sortedTasks = computed(() => {
  return [...tasks.value].sort((a, b) => {
    const primary = compareField(a, b, sortField.value)
    if (primary !== 0) return sortDirection.value === 'asc' ? primary : -primary
    return compareField(a, b, 'created_at')
  })
})

watch(
  () => props.open,
  (opened) => {
    if (opened) {
      loadQueue()
      subscribeToTaskStream()
    } else {
      unsubscribeFromTaskStream()
    }
  },
  { immediate: true }
)

function closeModal() {
  emit('close')
}

function taskKey(task) {
  return task.key ?? task.id ?? task.uuid ?? `${task.title}-${task.created_at}-${Math.random().toString(36).slice(2, 6)}`
}

function displayStatus(status) {
  return String(status || 'pending').replace(/_/g, ' ').toUpperCase()
}

function statusBadgeClass(status) {
  const normalized = String(status || '').toLowerCase()
  if (['running', 'in_progress', 'active'].includes(normalized)) return 'running'
  if (['queued', 'pending'].includes(normalized)) return 'queued'
  if (['completed', 'success'].includes(normalized)) return 'completed'
  if (['failed', 'error', 'cancelled'].includes(normalized)) return 'failed'
  return 'queued'
}

function compareField(a, b, field) {
  if (field === 'status') {
    return statusRank(a.status) - statusRank(b.status)
  }

  const first = a[field] ?? ''
  const second = b[field] ?? ''
  if (first < second) return -1
  if (first > second) return 1
  return 0
}

function statusRank(value) {
  const map = {
    queued: 0,
    pending: 0,
    running: 1,
    in_progress: 1,
    active: 1,
    completed: 2,
    success: 2,
    failed: 3,
    error: 3,
    cancelled: 3,
  }
  return map[String(value || '').toLowerCase()] ?? 4
}

function sortBy(field) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = field === 'created_at' ? 'desc' : 'asc'
  }
}

function transformTask(task) {
  return {
    ...task,
    key: taskKey(task),
    title: task.title ?? task.name ?? 'Unnamed job',
    description: task.description ?? task.summary ?? '',
    priority: task.priority ?? task.priority_level ?? 0,
    created_at: task.created_at ?? task.createdAt ?? task.createdAt ?? task.started_at ?? task.created ?? '',
    status: task.status ?? task.state ?? 'queued',
    progress: task.progress ?? task.percent ?? task.completion ?? '',
  }
}

async function loadQueue() {
  loading.value = true
  error.value = ''

  try {
    const response = await fetch('/api/v1/tasks?status=queued,running', {
      headers: { Accept: 'application/json' },
    })
    if (!response.ok) {
      throw new Error(`Queue request failed with status ${response.status}`)
    }

    const data = await response.json()
    const list = Array.isArray(data) ? data : data?.data ?? data?.items ?? []
    tasks.value = list.map(transformTask)
    await nextTick(() => {})
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load queue'
    tasks.value = []
  } finally {
    loading.value = false
  }
}

function highlightRow(key) {
  updatedKeys.value.add(key)
  setTimeout(() => {
    updatedKeys.value.delete(key)
  }, 900)
}

function rowHighlight(key) {
  return updatedKeys.value.has(key) ? 'row-updated' : ''
}

function displayTimestamp(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(date)
}

async function cancelJob(task) {
  if (!task?.key) return
  loadingTask.value = task.key
  const originalStatus = task.status
  updateTask(task.key, { status: 'cancelled' })

  try {
    const response = await fetch(`/api/v1/tasks/${task.key}/cancel`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    })
    if (!response.ok) {
      throw new Error('Cancel failed')
    }
  } catch (err) {
    updateTask(task.key, { status: originalStatus })
    error.value = err instanceof Error ? err.message : 'Cancel failed.'
  } finally {
    loadingTask.value = null
    highlightRow(task.key)
  }
}

async function retryJob(task) {
  if (!task?.key) return
  retryingTask.value = task.key
  const originalStatus = task.status
  updateTask(task.key, { status: 'queued', progress: 'Retrying...' })

  try {
    const response = await fetch(`/api/v1/tasks/${task.key}/retry`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    })
    if (!response.ok) {
      throw new Error('Retry failed')
    }
  } catch (err) {
    updateTask(task.key, { status: originalStatus })
    error.value = err instanceof Error ? err.message : 'Retry failed.'
  } finally {
    retryingTask.value = null
    highlightRow(task.key)
  }
}

function updateTask(key, patch) {
  const index = tasks.value.findIndex((item) => item.key === key)
  if (index < 0) return
  tasks.value.splice(index, 1, { ...tasks.value[index], ...patch })
}

function handleTaskEvent(event) {
  const key = event.taskId ?? event.id ?? event.key
  if (!key) return
  const updates = {
    status: event.status,
    progress: event.progress ?? event.percent ?? event.completion,
    description: event.message ?? tasks.value.find((item) => item.key === key)?.description,
  }

  if (tasks.value.some((item) => item.key === key)) {
    updateTask(key, updates)
    highlightRow(key)
  } else {
    tasks.value.push(transformTask({ ...event, id: key }))
    highlightRow(key)
  }
}

function setupTaskStream() {
  if (typeof window === 'undefined' || !window.Echo) return
  taskChannel = window.Echo.private('tasks')
  taskChannel.listen('WorkflowStepCompleted', handleTaskEvent)
  taskChannel.listen('JobProgressUpdated', handleTaskEvent)
}

function unsubscribeFromTaskStream() {
  if (!taskChannel || typeof window === 'undefined' || !window.Echo) return
  try {
    taskChannel.stopListening('WorkflowStepCompleted')
    taskChannel.stopListening('JobProgressUpdated')
    window.Echo.leave('tasks')
  } catch (err) {
    console.warn('Unable to leave tasks channel', err)
  } finally {
    taskChannel = null
  }
}

function subscribeToTaskStream() {
  unsubscribeFromTaskStream()
  setupTaskStream()
}

onBeforeUnmount(() => {
  unsubscribeFromTaskStream()
})
</script>

<style scoped>
.nx-queue-overlay {
  position: fixed;
  inset: 0;
  z-index: 1300;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.nx-queue-panel {
  width: min(1120px, 100%);
  max-height: min(92vh, 100%);
  overflow: hidden;
  background: rgba(10, 14, 24, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 28px;
  box-shadow: 0 40px 120px rgba(0, 0, 0, 0.35);
  display: flex;
  flex-direction: column;
}

.queue-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem 2rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.subtitle {
  margin: 0;
  color: #38bdf8;
  font-size: 0.78rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.queue-header h2 {
  margin: 0.5rem 0 0;
  font-size: 1.65rem;
  color: #f8fafc;
}

.description {
  margin: 0.75rem 0 0;
  color: #cbd5e1;
  max-width: 44rem;
}

.close-action {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
  font-size: 1.2rem;
  cursor: pointer;
}

.queue-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  padding: 1rem 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.toolbar-button {
  padding: 0.9rem 1.2rem;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(255, 255, 255, 0.04);
  color: #e2e8f0;
  cursor: pointer;
  transition: background 180ms ease, border-color 180ms ease;
}

.toolbar-button.active {
  background: rgba(56, 189, 248, 0.16);
  border-color: rgba(56, 189, 248, 0.3);
}

.queue-body {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: 0 2rem 2rem;
}

.queue-overview {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin: 1rem 0;
}

.overview-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.65rem 0.95rem;
  border-radius: 999px;
  background: rgba(148, 163, 184, 0.08);
  border: 1px solid rgba(148, 163, 184, 0.16);
  color: #cbd5e1;
  font-size: 0.85rem;
}

.queue-error {
  margin-bottom: 1rem;
  padding: 1rem;
  border-radius: 18px;
  background: rgba(248, 113, 113, 0.1);
  color: #fecaca;
}

.queue-table-wrapper {
  flex: 1;
  overflow: auto;
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.06);
  background: rgba(15, 23, 42, 0.92);
}

.queue-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 780px;
}

.queue-table th,
.queue-table td {
  padding: 1rem 1.15rem;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  color: #e2e8f0;
}

.queue-table th {
  color: #94a3b8;
  font-size: 0.82rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.actions-column {
  width: 220px;
}

.job-title {
  margin: 0;
  font-weight: 600;
  color: #f8fafc;
}

.job-meta {
  margin: 0.35rem 0 0;
  font-size: 0.92rem;
  color: #94a3b8;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
}

.status-pill.queued {
  background: rgba(79, 70, 229, 0.12);
  color: #c7d2fe;
}

.status-pill.running {
  background: rgba(34, 197, 94, 0.12);
  color: #86efac;
}

.status-pill.completed {
  background: rgba(16, 185, 129, 0.14);
  color: #a7f3d0;
}

.status-pill.failed {
  background: rgba(248, 113, 113, 0.14);
  color: #fecaca;
}

.job-progress {
  margin: 0.5rem 0 0;
  font-size: 0.84rem;
  color: #94a3b8;
}

.row-updated {
  animation: rowPulse 0.9s ease;
}

@keyframes rowPulse {
  0% { background: rgba(56, 189, 248, 0.14); }
  100% { background: transparent; }
}

.nx-queue-modal-enter-active,
.nx-queue-modal-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.nx-queue-modal-enter-from,
.nx-queue-modal-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

@media (max-width: 900px) {
  .nx-queue-panel {
    width: 100%;
    max-height: 100%;
    border-radius: 16px;
  }

  .queue-table {
    min-width: 100%;
  }
}
</style>
