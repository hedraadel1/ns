<template>
  <aside v-if="open" class="nx-thought-trace-drawer" role="dialog" aria-modal="true" aria-label="Agent reasoning trace drawer">
    <header class="drawer-header">
      <div>
        <p class="subtitle">Agent reasoning</p>
        <h2>Thought trace</h2>
        <p class="description">Streaming agent reasoning steps for <strong>{{ agentId || 'unknown agent' }}</strong>.</p>
      </div>
      <button type="button" class="close-button" @click="closeDrawer" aria-label="Close drawer">×</button>
    </header>

    <section class="trace-meta">
      <span class="meta-pill">Task ID: {{ taskId || 'N/A' }}</span>
      <span class="meta-pill">Steps: {{ steps.length }}</span>
      <span class="meta-pill">Status: {{ connectionStatus }}</span>
    </section>

    <div class="trace-body" ref="drawerBody">
      <transition-group name="trace-step" tag="div" class="trace-list">
        <article v-for="step in steps" :key="step.key" class="trace-step" :class="stepClass(step.type)">
          <div class="step-marker" :class="stepClass(step.type)"></div>
          <div class="step-content">
            <div class="step-header">
              <span class="step-type">{{ stepLabel(step.type) }}</span>
              <span class="step-time">{{ displayTimestamp(step.timestamp) }}</span>
            </div>
            <p class="step-message">{{ step.message }}</p>
            <pre v-if="step.payload" class="step-payload">{{ prettyJson(step.payload) }}</pre>
          </div>
        </article>
      </transition-group>

      <div v-if="!steps.length && !loading" class="empty-state">
        <p>No reasoning steps received yet.</p>
        <p class="hint">Open this drawer while the agent executes to observe live trace updates.</p>
      </div>

      <div v-if="loading" class="empty-state">
        <p>Connecting to agent trace stream...</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, defineEmits, defineProps, nextTick, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: true,
  },
  agentId: {
    type: [String, Number],
    default: null,
  },
  taskId: {
    type: [String, Number],
    default: null,
  },
})
const emit = defineEmits(['close'])

const steps = ref([])
const loading = ref(false)
const error = ref('')
const drawerBody = ref(null)
let traceChannel = null

const connectionStatus = computed(() => {
  if (error.value) return 'Disconnected'
  if (loading.value) return 'Connecting'
  return steps.value.length ? 'Live' : 'Idle'
})

watch(
  () => [props.open, props.agentId],
  ([isOpen, agentId]) => {
    if (isOpen && agentId) {
      subscribeToTrace()
    } else {
      unsubscribeFromTrace()
    }
  },
  { immediate: true }
)

function closeDrawer() {
  emit('close')
}

function stepLabel(type) {
  switch (String(type || '').toLowerCase()) {
    case 'thinking': return 'Thinking'
    case 'tool-call': return 'Tool call'
    case 'observation': return 'Observation'
    case 'response': return 'Response'
    default: return 'Step'
  }
}

function stepClass(type) {
  switch (String(type || '').toLowerCase()) {
    case 'thinking': return 'thinking'
    case 'tool-call': return 'tool-call'
    case 'observation': return 'observation'
    case 'response': return 'response'
    default: return 'default'
  }
}

function stepKey(step) {
  return step.id ?? step.uuid ?? `${step.type}-${step.timestamp ?? Date.now()}-${Math.random().toString(36).slice(2, 6)}`
}

function normalizeStep(payload) {
  const step = payload?.step ?? payload
  return {
    key: stepKey(step),
    id: step.id ?? step.uuid ?? null,
    type: step.type ?? step.phase ?? 'thinking',
    message: step.message ?? step.text ?? step.summary ?? 'No detail available.',
    payload: step.payload ?? step.data ?? step.context ?? null,
    timestamp: step.timestamp ?? step.created_at ?? new Date().toISOString(),
  }
}

function displayTimestamp(value) {
  if (!value) return 'Unknown'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? String(value) : new Intl.DateTimeFormat(undefined, { timeStyle: 'short', dateStyle: 'medium' }).format(date)
}

function prettyJson(value) {
  try {
    return JSON.stringify(value, null, 2)
  } catch {
    return String(value)
  }
}

async function appendStep(event) {
  const step = normalizeStep(event)
  steps.value.push(step)
  await nextTick(scrollToBottom)
}

function scrollToBottom() {
  if (!drawerBody.value) return
  drawerBody.value.scrollTop = drawerBody.value.scrollHeight
}

function subscribeToTrace() {
  unsubscribeFromTrace()
  if (typeof window === 'undefined' || !window.Echo || !props.agentId) return

  loading.value = true
  error.value = ''
  steps.value = []

  try {
    traceChannel = window.Echo.private(`agents.${props.agentId}`)
    traceChannel.listen('AgentStepCompleted', appendStep)
  } catch (err) {
    error.value = 'Unable to connect to agent trace channel.'
    console.warn(err)
  } finally {
    loading.value = false
  }
}

function unsubscribeFromTrace() {
  if (!traceChannel || typeof window === 'undefined' || !window.Echo) return
  try {
    traceChannel.stopListening('AgentStepCompleted')
    window.Echo.leave(`agents.${props.agentId}`)
  } catch (err) {
    console.warn('Failed to unsubscribe from trace channel', err)
  } finally {
    traceChannel = null
  }
}

onBeforeUnmount(() => {
  unsubscribeFromTrace()
})
</script>

<style scoped>
.nx-thought-trace-drawer {
  position: fixed;
  top: 1rem;
  right: 1rem;
  bottom: 1rem;
  width: min(520px, calc(100vw - 2rem));
  background: rgba(12, 15, 22, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  box-shadow: 0 35px 90px rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(18px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideInDrawer 220ms ease-out forwards;
}

@keyframes slideInDrawer {
  from { opacity: 0; transform: translateX(24px); }
  to { opacity: 1; transform: translateX(0); }
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.5rem 1.5rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.drawer-header h2 {
  margin: 0.25rem 0 0;
  font-size: 1.5rem;
}

.subtitle,
.step-type,
.meta-pill {
  color: #7dd3fc;
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.description {
  margin: 0.5rem 0 0;
  color: #cbd5e1;
  font-size: 0.95rem;
}

.close-button {
  border: 1px solid rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.04);
  color: #fff;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  cursor: pointer;
}

.trace-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
}

.meta-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.6rem 0.9rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.16);
  background: rgba(148, 163, 184, 0.08);
  color: #e2e8f0;
}

.trace-body {
  position: relative;
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem 1.25rem;
}

.trace-list {
  display: grid;
  gap: 1rem;
}

.trace-step {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 1rem;
  padding: 1rem;
  border-radius: 20px;
  background: rgba(15, 20, 34, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.step-marker {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  margin-top: 0.5rem;
}

.step-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.step-header {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: flex-start;
}

.step-time {
  color: #94a3b8;
  font-size: 0.82rem;
}

.step-message {
  margin: 0;
  color: #e2e8f0;
  line-height: 1.7;
}

.step-payload {
  margin: 0;
  padding: 0.9rem;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 16px;
  color: #cbd5e1;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  white-space: pre-wrap;
}

.empty-state {
  padding: 2rem 1rem;
  text-align: center;
  color: #94a3b8;
}

.hint {
  margin-top: 0.5rem;
  font-size: 0.92rem;
}

.trace-step.thinking .step-marker {
  background: #60a5fa;
}

.trace-step.tool-call .step-marker {
  background: #f59e0b;
}

.trace-step.observation .step-marker {
  background: #34d399;
}

.trace-step.response .step-marker {
  background: #8b5cf6;
}

.trace-step.default .step-marker {
  background: #94a3b8;
}

.trace-step-enter-active,
.trace-step-leave-active {
  transition: all 200ms ease;
}

.trace-step-enter-from {
  opacity: 0;
  transform: translateX(16px);
}

.trace-step-leave-to {
  opacity: 0;
  transform: translateX(-16px);
}
</style>
