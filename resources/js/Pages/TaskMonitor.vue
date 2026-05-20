<template>
  <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Task monitor</p>
        <h2 class="mt-1 text-xl font-semibold text-white">Active operations</h2>
        <p class="mt-1 text-sm text-slate-400">Live task metrics from /api/v1/tasks/stats and /api/v1/tasks/active.</p>
      </div>

      <button
        type="button"
        class="rounded-lg border border-emerald-500/30 px-3 py-2 text-sm font-medium text-emerald-300 transition hover:border-emerald-400/60 hover:bg-emerald-500/10"
        :disabled="loading"
        @click="loadMonitor"
      >
        Refresh
      </button>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="stat in statsCards"
        :key="stat.label"
        class="rounded-xl border border-white/10 bg-white/5 p-4"
      >
        <p class="text-xs uppercase tracking-wide text-slate-500">{{ stat.label }}</p>
        <p class="mt-2 text-2xl font-semibold text-white">{{ stat.value }}</p>
        <p class="mt-1 text-sm text-slate-400">{{ stat.hint }}</p>
      </div>
    </div>

    <NxPullRefresh @refresh="loadMonitor">
      <div class="mt-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-sm font-medium text-white">Active tasks</p>
          <p class="text-xs text-slate-400">{{ tasks.length }} tasks currently in flight</p>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-400">
          <span class="h-2 w-2 rounded-full bg-emerald-400" />
          Polled data
        </div>
      </div>

      <div v-if="multiSelectMode.value" class="mt-3 rounded-2xl border border-emerald-500/20 bg-slate-950/80 p-4 text-sm text-emerald-200">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <p><span class="text-white font-semibold">{{ selectedTaskKeys.value.length }}</span> selected</p>
          <button
            type="button"
            class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-300 transition hover:bg-white/10"
            @click="clearBulkMode"
          >
            Cancel
          </button>
        </div>
        <p class="mt-2 text-xs text-slate-400">Long-press a task to start multi-select and choose bulk actions.</p>
      </div>

      <div v-if="tasks.length" class="mt-3 space-y-3">
        <article
          v-for="task in tasks"
          :key="task.key"
          class="group relative rounded-2xl border border-white/10 bg-slate-950/60 p-4 transition hover:border-emerald-400/30 hover:bg-slate-900/80"
          @pointerdown.prevent="beginTaskPress(task)"
          @pointerup.prevent="endTaskPress(task)"
          @pointerleave="cancelTaskPress"
          @pointercancel="cancelTaskPress"
          @contextmenu.prevent="openTaskContextMenu(task, $event)"
          @keydown.enter.prevent="endTaskPress(task)"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <label v-if="multiSelectMode.value" class="relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded-sm border border-white/20 bg-slate-950 transition focus-within:ring-2 focus-within:ring-emerald-400/40">
                <input
                  type="checkbox"
                  class="peer absolute h-full w-full opacity-0"
                  :checked="selectedTaskKeys.value.includes(task.key)"
                  @change.stop.prevent="toggleTaskSelection(task)"
                />
                <span class="pointer-events-none h-3.5 w-3.5 rounded-sm border border-white/20 bg-transparent transition peer-checked:bg-emerald-400 peer-checked:border-emerald-400" />
              </label>
              <div>
                <h3 class="text-base font-semibold text-white">{{ task.title }}</h3>
                <p class="mt-1 text-sm text-slate-400">{{ task.subtitle }}</p>
              </div>
            </div>

            <span
              class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
              :class="task.badgeClass"
            >
              {{ task.status }}
            </span>
            <NxLiveLoader v-if="isRunningTask(task)" :taskId="task.key" status="loading" />
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Workflow</p>
              <p class="mt-1 text-sm text-white">{{ task.workflow }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Agent</p>
              <p class="mt-1 text-sm text-white">{{ task.agent }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Progress</p>
              <p class="mt-1 text-sm text-white">{{ task.progress }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Actions</p>
              <div class="mt-2 flex flex-wrap gap-2">
                <NxActionButton
                  variant="secondary"
                  optimistic
                  :optimisticState="optimisticStates[task.key] || null"
                  @click.stop="retryTask(task)"
                >
                  Retry
                </NxActionButton>
              </div>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Updated</p>
              <p class="mt-1 text-sm text-white">{{ task.updatedAt }}</p>
            </div>
          </div>

          <p v-if="task.message" class="mt-3 text-sm text-slate-300">
            {{ task.message }}
          </p>
        </article>
      </div>

      <div v-else class="mt-4 rounded-xl border border-dashed border-white/10 bg-slate-950/40 p-5 text-sm text-slate-400">
        No active tasks are currently running.
      </div>
    </div>
  </NxPullRefresh>

    <NxContextMenu
      :visible="contextMenuVisible"
      :x="contextMenuX"
      :y="contextMenuY"
      :items="taskActions"
      @select="handleTaskContextMenu"
      @close="closeTaskContextMenu"
    />

    <p v-if="error" class="mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </p>

    <NxTaskDetailDrawer v-if="drawerOpen && selectedTask" :task="selectedTask" @close="drawerOpen = false" />

    <div v-if="multiSelectMode.value" class="fixed bottom-6 left-1/2 z-50 w-[min(96vw,960px)] -translate-x-1/2 rounded-3xl border border-white/10 bg-slate-950/95 p-4 shadow-2xl shadow-black/40 backdrop-blur-xl">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-200">
          <span class="text-white font-semibold">{{ selectedTaskKeys.value.length }}</span> tasks selected
        </p>
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            class="rounded-full border border-sky-500/30 bg-sky-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-sky-200 transition hover:bg-sky-500/20"
            @click="bulkRetryTasks"
            :disabled="bulkActionLoading"
          >
            Retry
          </button>
          <button
            type="button"
            class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-amber-200 transition hover:bg-amber-500/20"
            @click="bulkPauseTasks"
            :disabled="bulkActionLoading"
          >
            Pause
          </button>
          <button
            type="button"
            class="rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-rose-200 transition hover:bg-rose-500/20"
            @click="bulkCancelTasks"
            :disabled="bulkActionLoading"
          >
            Cancel
          </button>
          <button
            type="button"
            class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-300 transition hover:bg-white/10"
            @click="clearBulkMode"
            :disabled="bulkActionLoading"
          >
            Close
          </button>
        </div>
      </div>
      <p class="text-xs text-slate-400">Bulk actions apply to selected tasks only.</p>
      <p v-if="bulkActionError" class="mt-2 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-xs text-rose-200">{{ bulkActionError }}</p>
    </div>

    <div v-if="loading" class="mt-4 text-sm text-slate-400">Loading task monitor…</div>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import NxActionButton from '../Components/NxActionButton.vue'
import NxContextMenu from '../Components/NxContextMenu.vue'
import NxLiveLoader from '../Components/NxLiveLoader.vue'
import NxPullRefresh from '../Components/NxPullRefresh.vue'
import NxTaskDetailDrawer from '../Components/NxTaskDetailDrawer.vue'

const props = defineProps({
  autoRefresh: {
    type: Boolean,
    default: true,
  },
  refreshMs: {
    type: Number,
    default: 12000,
  },
})

const loading = ref(false)
const error = ref('')
const stats = ref({})
const tasks = ref([])
const selectedTask = ref(null)
const drawerOpen = ref(false)
const optimisticStates = ref({})
const multiSelectMode = ref(false)
const selectedTaskKeys = ref([])
const bulkActionLoading = ref(false)
const bulkActionError = ref('')
const contextMenuVisible = ref(false)
const contextMenuX = ref(0)
const contextMenuY = ref(0)
const contextMenuTarget = ref(null)
const taskPressTimer = ref(null)
const taskLongPressSuppressed = ref(false)
let timer = null
let taskChannel = null

const normalizeNumber = (value, fallback = 0) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

const asDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString()
}

const statsCards = computed(() => [
  {
    label: 'Active',
    value: normalizeNumber(stats.value?.active ?? stats.value?.running ?? tasks.value.length),
    hint: 'Tasks currently executing',
  },
  {
    label: 'Queued',
    value: normalizeNumber(stats.value?.queued ?? stats.value?.pending),
    hint: 'Waiting for execution',
  },
  {
    label: 'Completed',
    value: normalizeNumber(stats.value?.completed ?? stats.value?.done),
    hint: 'Finished recently',
  },
  {
    label: 'Failed',
    value: normalizeNumber(stats.value?.failed ?? stats.value?.errors),
    hint: 'Require attention',
  },
])

function normalizeTask(task, index) {
  const status = String(task?.status ?? task?.state ?? 'unknown')
  const normalized = status.toLowerCase()
  const badgeClass =
    normalized === 'running' || normalized === 'active' || normalized === 'in_progress'
      ? 'bg-sky-500/15 text-sky-300'
      : normalized === 'completed' || normalized === 'done' || normalized === 'success'
        ? 'bg-emerald-500/15 text-emerald-300'
        : normalized === 'failed' || normalized === 'error'
          ? 'bg-rose-500/15 text-rose-300'
          : 'bg-slate-500/15 text-slate-300'

  const progress = task?.progress ?? task?.percent ?? task?.completion
  const progressLabel =
    progress !== undefined && progress !== null
      ? `${normalizeNumber(progress)}%`
      : task?.steps_completed && task?.steps_total
        ? `${task.steps_completed}/${task.steps_total}`
        : '—'

  return {
    key: task?.id ?? task?.uuid ?? index,
    title: task?.name ?? task?.title ?? `Task ${index + 1}`,
    subtitle: task?.description ?? task?.summary ?? task?.workflow_name ?? '',
    status,
    badgeClass,
    workflow: task?.workflow_name ?? task?.workflow?.name ?? task?.workflow_id ?? '—',
    agent: task?.agent_name ?? task?.agent?.name ?? task?.agent_id ?? '—',
    progress: progressLabel,
    updatedAt: asDate(task?.updated_at ?? task?.last_updated_at ?? task?.started_at),
    message: task?.message ?? task?.detail ?? task?.notes ?? '',
  }
}

function isRunningTask(task) {
  return ['running', 'active', 'in_progress'].includes(String(task.status).toLowerCase())
}

function statusClass(status) {
  const normalized = String(status ?? 'unknown').toLowerCase()
  if (['running', 'active', 'in_progress'].includes(normalized)) {
    return 'bg-sky-500/15 text-sky-300'
  }
  if (['completed', 'done', 'success'].includes(normalized)) {
    return 'bg-emerald-500/15 text-emerald-300'
  }
  if (['failed', 'error'].includes(normalized)) {
    return 'bg-rose-500/15 text-rose-300'
  }
  return 'bg-amber-500/15 text-amber-300'
}

function openTask(task) {
  if (multiSelectMode.value) {
    toggleTaskSelection(task)
    return
  }
  selectedTask.value = task
  drawerOpen.value = true
}

function beginTaskPress(task) {
  if (taskPressTimer.value) {
    clearTimeout(taskPressTimer.value)
  }
  taskLongPressSuppressed.value = false
  taskPressTimer.value = window.setTimeout(() => {
    taskLongPressSuppressed.value = true
    if (!multiSelectMode.value) {
      multiSelectMode.value = true
      selectedTaskKeys.value = [task.key]
    }
    taskPressTimer.value = null
  }, 500)
}

function endTaskPress(task) {
  if (taskPressTimer.value) {
    clearTimeout(taskPressTimer.value)
    taskPressTimer.value = null
  }
  if (taskLongPressSuppressed.value) {
    taskLongPressSuppressed.value = false
    return
  }
  if (multiSelectMode.value) {
    toggleTaskSelection(task)
    return
  }
  selectedTask.value = task
  drawerOpen.value = true
}

function cancelTaskPress() {
  if (taskPressTimer.value) {
    clearTimeout(taskPressTimer.value)
    taskPressTimer.value = null
  }
}

function toggleTaskSelection(task) {
  const key = task.key
  const index = selectedTaskKeys.value.indexOf(key)
  if (index > -1) {
    selectedTaskKeys.value.splice(index, 1)
  } else {
    selectedTaskKeys.value.push(key)
  }
}

function clearBulkMode() {
  multiSelectMode.value = false
  selectedTaskKeys.value = []
  bulkActionError.value = ''
}

function updateTaskByKey(taskKey, updates = {}) {
  const index = tasks.value.findIndex((task) => task.key === taskKey)
  if (index < 0) return
  tasks.value[index] = {
    ...tasks.value[index],
    ...updates,
    badgeClass: updates.status ? statusClass(updates.status) : tasks.value[index].badgeClass,
  }
  if (selectedTask.value?.key === taskKey) {
    selectedTask.value = { ...selectedTask.value, ...tasks.value[index] }
  }
}

function handleWorkflowStepCompleted(event) {
  const taskKey = event.taskId ?? event.id
  if (!taskKey) return
  updateTaskByKey(taskKey, {
    status: event.status ?? 'completed',
    progress: event.progress !== undefined ? `${event.progress}%` : '100%',
    message: event.message ?? tasks.value.find((task) => task.key === taskKey)?.message,
  })
}

function handleJobProgressUpdated(event) {
  const taskKey = event.taskId ?? event.id
  if (!taskKey) return
  const progress = event.progress ?? event.percent ?? event.completion
  updateTaskByKey(taskKey, {
    progress: progress !== undefined ? `${progress}%` : tasks.value.find((task) => task.key === taskKey)?.progress,
    message: event.message ?? tasks.value.find((task) => task.key === taskKey)?.message,
  })
}

function setupTaskEchoListeners() {
  if (typeof window === 'undefined' || !window.Echo) return
  taskChannel = window.Echo.private('tasks')
  taskChannel.listen('WorkflowStepCompleted', handleWorkflowStepCompleted)
  taskChannel.listen('JobProgressUpdated', handleJobProgressUpdated)
}

async function retryTask(task) {
  if (!task) return
  optimisticStates.value = { ...optimisticStates.value, [task.key]: 'pending' }
  const originalStatus = task.status
  const originalProgress = task.progress
  updateTaskByKey(task.key, { status: 'queued', progress: 'Retrying...' })

  try {
    const response = await fetch(`/api/v1/tasks/${task.key}/retry`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    })
    const result = await response.json()
    if (!response.ok || result.success === false) {
      throw new Error(result.message || 'Retry failed')
    }
    optimisticStates.value = { ...optimisticStates.value, [task.key]: 'success' }
  } catch (err) {
    updateTaskByKey(task.key, { status: originalStatus, progress: originalProgress })
    optimisticStates.value = { ...optimisticStates.value, [task.key]: 'error' }
  } finally {
    setTimeout(() => {
      optimisticStates.value = { ...optimisticStates.value, [task.key]: null }
    }, 1200)
  }
}

async function sendBulkTaskAction(action, statusUpdate, tasksToProcess) {
  if (tasksToProcess.length === 0) return
  bulkActionLoading.value = true
  bulkActionError.value = ''

  try {
    await Promise.all(
      tasksToProcess.map(async (taskKey) => {
        if (statusUpdate) {
          updateTaskByKey(taskKey, statusUpdate)
        }
        const response = await fetch(`/api/v1/tasks/${encodeURIComponent(taskKey)}/${action}`, {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        })
        if (!response.ok) {
          const result = await response.json().catch(() => ({}))
          throw new Error(result.message || `Failed to ${action}`)
        }
      })
    )
    if (action === 'cancel') {
      tasks.value = tasks.value.map((task) =>
        selectedTaskKeys.value.includes(task.key)
          ? { ...task, status: 'cancelled', badgeClass: statusClass('cancelled') }
          : task
      )
    }
    if (action === 'pause') {
      tasks.value = tasks.value.map((task) =>
        selectedTaskKeys.value.includes(task.key)
          ? { ...task, status: 'paused', badgeClass: statusClass('paused') }
          : task
      )
    }
    if (action === 'retry') {
      tasks.value = tasks.value.map((task) =>
        selectedTaskKeys.value.includes(task.key)
          ? { ...task, status: 'queued', badgeClass: statusClass('queued') }
          : task
      )
    }
    clearBulkMode()
  } catch (error) {
    bulkActionError.value = error instanceof Error ? error.message : `Failed to ${action} selected tasks.`
  } finally {
    bulkActionLoading.value = false
  }
}

function bulkPauseTasks() {
  sendBulkTaskAction('pause', { status: 'paused', progress: 'Paused' }, selectedTaskKeys.value)
}

function bulkCancelTasks() {
  sendBulkTaskAction('cancel', { status: 'cancelled', progress: 'Cancelled' }, selectedTaskKeys.value)
}

function bulkRetryTasks() {
  sendBulkTaskAction('retry', { status: 'queued', progress: 'Retrying...' }, selectedTaskKeys.value)
}

async function loadMonitor() {
  loading.value = true
  error.value = ''

  try {
    const [statsResponse, tasksResponse] = await Promise.all([
      fetch('/api/v1/tasks/stats', {
        headers: { Accept: 'application/json' },
      }),
      fetch('/api/v1/tasks/active', {
        headers: { Accept: 'application/json' },
      }),
    ])

    if (!statsResponse.ok) {
      throw new Error(`Stats request failed with status ${statsResponse.status}`)
    }

    if (!tasksResponse.ok) {
      throw new Error(`Active tasks request failed with status ${tasksResponse.status}`)
    }

    const statsData = await statsResponse.json()
    const tasksData = await tasksResponse.json()

    stats.value = statsData?.data ?? statsData?.result ?? statsData ?? {}
    const list = tasksData?.data ?? tasksData?.result ?? tasksData
    const source = Array.isArray(list) ? list : list?.items ?? list?.tasks ?? []
    tasks.value = Array.isArray(source) ? source.map(normalizeTask) : []
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load task monitor.'
  } finally {
    loading.value = false
  }
}

function stopTimer() {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

function startTimer() {
  stopTimer()
  if (!props.autoRefresh) return
  timer = setInterval(() => {
    loadMonitor()
  }, props.refreshMs)
}

function openTaskContextMenu(task, event) {
  contextMenuTarget.value = task
  contextMenuX.value = event.clientX
  contextMenuY.value = event.clientY
  contextMenuVisible.value = true
}

function closeTaskContextMenu() {
  contextMenuVisible.value = false
  contextMenuTarget.value = null
}

function handleTaskContextMenu(action) {
  const task = contextMenuTarget.value
  if (!task) {
    closeTaskContextMenu()
    return
  }

  switch (action.value) {
    case 'retry':
      retryTask(task)
      break
    case 'pause':
      pauseTask(task)
      break
    case 'cancel':
      cancelTask(task)
      break
    case 'detail':
      selectedTask.value = task
      drawerOpen.value = true
      break
    default:
      break
  }

  closeTaskContextMenu()
}

const taskActions = computed(() => [
  { value: 'detail', label: 'View details' },
  { value: 'retry', label: 'Retry task' },
  { value: 'pause', label: 'Pause task' },
  { value: 'cancel', label: 'Cancel task' },
])

const handleFabAction = (event) => {
  const action = event.detail
  switch (action.value) {
    case 'refresh':
      loadMonitor()
      break
    case 'retry':
      tasks.value.slice(0, 3).forEach(retryTask)
      break
    default:
      break
  }
}

watch(
  () => props.autoRefresh,
  () => {
    loadMonitor()
    startTimer()
  },
  { immediate: true },
)

onMounted(() => {
  setupTaskEchoListeners()
  window.addEventListener('nx-fab-action', handleFabAction)
})

onBeforeUnmount(() => {
  window.removeEventListener('nx-fab-action', handleFabAction)
  stopTimer()
  if (taskChannel) {
    taskChannel.stopListening('*')
    window.Echo.leaveChannel('tasks')
  }
})
</script>
