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

      <div v-if="tasks.length" class="mt-3 space-y-3">
        <article
          v-for="task in tasks"
          :key="task.key"
          class="rounded-2xl border border-white/10 bg-slate-950/60 p-4"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h3 class="text-base font-semibold text-white">{{ task.title }}</h3>
              <p class="mt-1 text-sm text-slate-400">{{ task.subtitle }}</p>
            </div>

            <span
              class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
              :class="task.badgeClass"
            >
              {{ task.status }}
            </span>
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

    <p v-if="error" class="mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </p>

    <div v-if="loading" class="mt-4 text-sm text-slate-400">Loading task monitor…</div>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'

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
let timer = null

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

watch(
  () => props.autoRefresh,
  () => {
    loadMonitor()
    startTimer()
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  stopTimer()
})
</script>