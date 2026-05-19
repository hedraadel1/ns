<template>
  <aside v-if="open" class="task-detail-drawer glass-strong" role="dialog" aria-modal="true" aria-label="Task detail drawer">
    <header class="drawer-header">
      <div>
        <p class="subtitle">Task detail</p>
        <h2>{{ displayTask.title || 'Task details' }}</h2>
        <p class="meta">Status: <strong>{{ displayTask.status || 'unknown' }}</strong></p>
      </div>
      <div class="header-actions">
        <NxActionButton variant="secondary" :loading="retrying" :disabled="!taskKey" @click="retryTask">
          Retry
        </NxActionButton>
        <button class="close-btn" @click="closeDrawer">Close</button>
      </div>
    </header>

    <section class="drawer-body">
      <div class="drawer-grid">
        <div class="drawer-row">
          <dt>Trace ID</dt>
          <dd>
            <div class="trace-id-row">
              <span>{{ displayTask.trace_id || displayTask.traceId || '—' }}</span>
              <button class="trace-copy" @click="copyTraceId" :disabled="!traceId">Copy</button>
            </div>
          </dd>
        </div>

        <div class="drawer-row">
          <dt>Workflow</dt>
          <dd>{{ displayTask.workflow ?? displayTask.workflow_name ?? '—' }}</dd>
        </div>

        <div class="drawer-row">
          <dt>Agent</dt>
          <dd>{{ displayTask.agent ?? displayTask.agent_name ?? '—' }}</dd>
        </div>

        <div class="drawer-row">
          <dt>Progress</dt>
          <dd>{{ displayTask.progress ?? '—' }}</dd>
        </div>

        <div class="drawer-row">
          <dt>Updated</dt>
          <dd>{{ displayTask.updated_at || displayTask.updatedAt || '—' }}</dd>
        </div>

        <div v-if="displayTask.message" class="drawer-row">
          <dt>Message</dt>
          <dd class="task-message">{{ displayTask.message }}</dd>
        </div>
      </div>

      <div class="section-header">
        <div>
          <h3>Step logs</h3>
          <p class="section-subtitle">Live task events and step history.</p>
        </div>
        <NxLiveLoader v-if="loading" :taskId="taskId || displayTask.key" status="loading" />
      </div>
      <div v-if="error" class="error-message">{{ error }}</div>

      <div class="logs-accordion">
        <template v-if="logs.length">
          <article
            v-for="log in logs"
            :key="log.key"
            class="log-entry"
          >
            <button type="button" class="log-summary" @click="toggleExpanded(log.key)">
              <span>{{ log.title || log.type || 'Step event' }}</span>
              <span>{{ log.timestampLabel }}</span>
            </button>
            <div v-if="isExpanded(log.key)" class="log-details">
              <p class="log-message">{{ log.message || log.description || 'No details available.' }}</p>
              <pre class="log-payload">{{ prettyJson(log.payload || log.meta || log.data) }}</pre>
            </div>
          </article>
        </template>

        <div v-else class="empty-state">
          <p>No logs have been loaded for this task yet.</p>
        </div>
      </div>

      <div class="raw-payload-card">
        <header class="payload-header">
          <h3>Raw payload</h3>
          <button class="payload-copy" @click="copyRawPayload" :disabled="!rawPayload">Copy JSON</button>
        </header>
        <pre class="raw-json">{{ prettyJson(rawPayload) }}</pre>
      </div>
    </section>
  </aside>
</template>

<script setup>
import { computed, defineEmits, defineProps, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import NxActionButton from './NxActionButton.vue'
import NxLiveLoader from './NxLiveLoader.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: true,
  },
  taskId: {
    type: [String, Number],
    default: null,
  },
  task: {
    type: Object,
    default: null,
  },
})
const emit = defineEmits(['close', 'retry'])

const taskDetails = ref(null)
const logs = ref([])
const expanded = ref(new Set())
const loading = ref(false)
const retrying = ref(false)
const error = ref('')
const taskChannel = ref(null)

const displayTask = computed(() => props.task ?? taskDetails.value ?? {})
const traceId = computed(() => displayTask.value.trace_id ?? displayTask.value.traceId ?? '')
const rawPayload = computed(() => displayTask.value.raw_payload ?? displayTask.value.payload ?? displayTask.value.data ?? {})

const taskKey = computed(() => props.task?.key ?? props.task?.id ?? props.taskId ?? displayTask.value.id ?? displayTask.value.key)

watch(
  () => [props.open, props.taskId, props.task],
  async ([open]) => {
    if (open) {
      await loadData()
      setupTaskListeners()
    } else {
      unsubscribeTaskListeners()
    }
  },
  { immediate: true }
)

function closeDrawer() {
  unsubscribeTaskListeners()
  emit('close')
}

function copyTraceId() {
  if (!traceId.value) return
  navigator.clipboard.writeText(traceId.value).catch(() => {})
}

function copyRawPayload() {
  if (!rawPayload.value) return
  navigator.clipboard.writeText(prettyJson(rawPayload.value)).catch(() => {})
}

function normalizeLog(log) {
  const timestamp = log.timestamp ?? log.created_at ?? log.time ?? new Date().toISOString()
  const date = new Date(timestamp)
  return {
    key: log.id ?? log.uuid ?? `${timestamp}-${Math.random().toString(36).slice(2, 6)}`,
    title: log.type ?? log.event ?? log.step ?? 'Task event',
    message: log.message ?? log.description ?? log.detail ?? '',
    payload: log.payload ?? log.data ?? log.context ?? null,
    timestampLabel: Number.isNaN(date.getTime()) ? String(timestamp) : new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(date),
  }
}

function isExpanded(key) {
  return expanded.value.has(key)
}

function toggleExpanded(key) {
  if (expanded.value.has(key)) {
    expanded.value.delete(key)
  } else {
    expanded.value.add(key)
  }
}

async function loadData() {
  loading.value = true
  error.value = ''

  try {
    if (props.task) {
      taskDetails.value = { ...props.task }
    }

    const id = props.task?.id ?? props.task?.key ?? props.taskId
    if (!id) return

    const [taskResponse, logsResponse] = await Promise.all([
      fetch(`/api/v1/tasks/${id}`, { headers: { Accept: 'application/json' } }),
      fetch(`/api/v1/tasks/${id}/logs`, { headers: { Accept: 'application/json' } }),
    ])

    if (!taskResponse.ok) {
      throw new Error(`Task detail request failed with ${taskResponse.status}`)
    }

    if (!logsResponse.ok) {
      throw new Error(`Task logs request failed with ${logsResponse.status}`)
    }

    const taskData = await taskResponse.json()
    const taskSource = taskData?.data ?? taskData
    taskDetails.value = normalizeTaskResponse(taskSource)

    const logsData = await logsResponse.json()
    const list = Array.isArray(logsData) ? logsData : logsData?.data ?? logsData?.items ?? []
    logs.value = Array.isArray(list) ? list.map(normalizeLog) : []
    await nextTick(() => {})
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load task details.'
  } finally {
    loading.value = false
  }
}

function normalizeTaskResponse(task) {
  if (!task || typeof task !== 'object') return {}
  return {
    ...task,
    trace_id: task.trace_id ?? task.traceId ?? task.traceIdValue ?? '',
    workflow: task.workflow_name ?? task.workflow?.name ?? task.workflow ?? '—',
    agent: task.agent_name ?? task.agent?.name ?? task.agent ?? '—',
    progress: task.progress ?? task.percent ?? task.completion ?? '—',
    updated_at: task.updated_at ?? task.updatedAt ?? task.last_updated_at ?? task.updatedAt ?? '—',
    message: task.message ?? task.detail ?? task.notes ?? '',
    raw_payload: task.raw_payload ?? task.payload ?? task.data ?? task,
  }
}

async function retryTask() {
  if (!taskKey.value) return
  retrying.value = true
  emit('retry', taskKey.value)

  try {
    const response = await fetch(`/api/v1/tasks/${taskKey.value}/retry`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    })
    if (!response.ok) {
      const result = await response.json().catch(() => ({}))
      throw new Error(result.message || 'Retry request failed')
    }
    await loadData()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Retry failed.'
  } finally {
    retrying.value = false
  }
}

function handleTaskEvent(event) {
  const eventKey = event.taskId ?? event.id ?? event.key
  if (!eventKey || String(eventKey) !== String(taskKey.value)) return

  if (event.status) {
    taskDetails.value = { ...taskDetails.value, status: event.status }
  }
  if (event.progress) {
    taskDetails.value = { ...taskDetails.value, progress: event.progress }
  }
  if (event.message) {
    taskDetails.value = { ...taskDetails.value, message: event.message }
  }

  if (event.log || event.detail || event.message) {
    const incoming = normalizeLog(event.log ? event.log : event)
    logs.value.push(incoming)
  }
}

function setupTaskListeners() {
  if (typeof window === 'undefined' || !window.Echo || !taskKey.value) return

  try {
    taskChannel.value = window.Echo.private('tasks')
    taskChannel.value.listen('WorkflowStepCompleted', handleTaskEvent)
    taskChannel.value.listen('JobProgressUpdated', handleTaskEvent)
    taskChannel.value.listen('TaskCheckpoint', handleTaskEvent)
  } catch (err) {
    console.warn('Unable to subscribe to task detail events', err)
  }
}

function unsubscribeTaskListeners() {
  if (!taskChannel.value || typeof window === 'undefined' || !window.Echo) return
  try {
    taskChannel.value.stopListening('WorkflowStepCompleted')
    taskChannel.value.stopListening('JobProgressUpdated')
    taskChannel.value.stopListening('TaskCheckpoint')
    window.Echo.leave('tasks')
  } catch (err) {
    console.warn('Unable to unsubscribe from task detail events', err)
  } finally {
    taskChannel.value = null
  }
}

onBeforeUnmount(() => {
  unsubscribeTaskListeners()
})
</script>

<style scoped>
.task-detail-drawer {
  position: fixed;
  right: 1rem;
  top: 1rem;
  bottom: 1rem;
  width: min(600px, calc(100% - 2rem));
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 28px;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  background: rgba(10, 14, 25, 0.96);
  backdrop-filter: blur(18px);
  box-shadow: 0 40px 120px rgba(0, 0, 0, 0.35);
  overflow: hidden;
  animation: slideIn 220ms ease-out forwards;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(32px); }
  to { opacity: 1; transform: translateX(0); }
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.subtitle {
  margin: 0 0 0.5rem;
  color: #60a5fa;
  font-size: 0.75rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.drawer-header h2 {
  margin: 0;
  font-size: 1.65rem;
  color: #f8fafc;
}

.meta {
  margin: 0.75rem 0 0;
  color: #94a3b8;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
}

.action-btn,
.close-btn,
.trace-copy,
.payload-copy {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.04);
  color: #e2e8f0;
  border-radius: 14px;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background 160ms ease, transform 160ms ease;
}

.action-btn:hover:not(:disabled),
.close-btn:hover:not(:disabled),
.trace-copy:hover:not(:disabled),
.payload-copy:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-1px);
}

.action-btn:disabled,
.trace-copy:disabled,
.payload-copy:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.drawer-body {
  display: grid;
  gap: 1.25rem;
  overflow-y: auto;
  padding-right: 0.5rem;
}

.drawer-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.drawer-row {
  display: grid;
  gap: 0.5rem;
}

.drawer-row dt {
  font-size: 0.8rem;
  color: #8b93a8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.drawer-row dd {
  margin: 0;
  color: #f8fafc;
  font-size: 0.95rem;
}

.trace-id-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.section-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #f8fafc;
}

.section-subtitle {
  margin: 0.35rem 0 0;
  color: #94a3b8;
  font-size: 0.92rem;
}

.logs-accordion {
  display: grid;
  gap: 0.85rem;
}

.log-entry {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.03);
  overflow: hidden;
}

.log-summary {
  width: 100%;
  text-align: left;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.2rem;
  color: #f8fafc;
  background: rgba(15, 23, 42, 0.9);
  cursor: pointer;
  border: none;
}

.log-details {
  padding: 1rem 1.2rem 1.2rem;
  background: rgba(11, 14, 21, 0.95);
}

.log-message {
  margin: 0 0 0.75rem;
  color: #cbd5e1;
  line-height: 1.7;
}

.log-payload,
.raw-json {
  margin: 0;
  padding: 1rem;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.04);
  color: #cbd5e1;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  font-size: 0.82rem;
  white-space: pre-wrap;
  word-break: break-word;
  max-height: 240px;
  overflow: auto;
}

.raw-payload-card {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  background: rgba(15, 23, 42, 0.88);
  padding: 1rem;
}

.payload-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.empty-state {
  padding: 1.5rem;
  border: 1px dashed rgba(148, 163, 184, 0.3);
  border-radius: 18px;
  color: #94a3b8;
  background: rgba(255, 255, 255, 0.03);
}

.task-message {
  color: #cbd5e1;
  white-space: pre-wrap;
}

@media (max-width: 900px) {
  .task-detail-drawer {
    width: 100%;
    right: 0;
    left: 0;
    top: 0;
    bottom: 0;
    border-radius: 0;
  }

  .drawer-grid {
    grid-template-columns: 1fr;
  }
}
</style>
