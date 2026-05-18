<template>
  <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">{{ title }}</p>
        <h3 class="mt-1 text-lg font-semibold text-white">
          <span v-if="workflowTitle">{{ workflowTitle }}</span>
          <span v-else>Workflow progress</span>
        </h3>
        <p class="mt-1 text-sm text-slate-400">
          <span v-if="workflowId">Live progress from /api/v1/workflows/{{ workflowId }}/progress.</span>
          <span v-else>Select a workflow to inspect its progress.</span>
        </p>
      </div>

      <button
        v-if="workflowId"
        type="button"
        class="rounded-lg border border-emerald-500/30 px-3 py-2 text-sm font-medium text-emerald-300 transition hover:border-emerald-400/60 hover:bg-emerald-500/10"
        :disabled="loading"
        @click="loadProgress"
      >
        Refresh
      </button>
    </div>

    <div v-if="workflowId" class="mt-4">
      <div class="flex items-center justify-between text-sm text-slate-300">
        <span>{{ statusLabel }}</span>
        <span>{{ progressLabel }}</span>
      </div>

      <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-800">
        <div
          class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-lime-300 transition-all duration-500"
          :style="{ width: `${progressPercent}%` }"
        />
      </div>

      <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
          <p class="text-xs uppercase tracking-wide text-slate-500">Current step</p>
          <p class="mt-1 text-sm font-semibold text-white">{{ currentStep }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
          <p class="text-xs uppercase tracking-wide text-slate-500">Completed</p>
          <p class="mt-1 text-sm font-semibold text-white">{{ completedStepsLabel }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
          <p class="text-xs uppercase tracking-wide text-slate-500">Remaining</p>
          <p class="mt-1 text-sm font-semibold text-white">{{ remainingStepsLabel }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
          <p class="text-xs uppercase tracking-wide text-slate-500">Updated</p>
          <p class="mt-1 text-sm font-semibold text-white">{{ updatedAtLabel }}</p>
        </div>
      </div>

      <div v-if="steps.length" class="mt-5">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Step breakdown</p>
        <div class="mt-3 space-y-2">
          <div
            v-for="step in steps"
            :key="step.key"
            class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2"
          >
            <div>
              <p class="text-sm font-medium text-white">{{ step.label }}</p>
              <p class="text-xs text-slate-500">{{ step.hint }}</p>
            </div>
            <span
              class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
              :class="step.badgeClass"
            >
              {{ step.status }}
            </span>
          </div>
        </div>
      </div>

      <p v-if="message" class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-200">
        {{ message }}
      </p>
    </div>

    <div v-else class="mt-4 rounded-xl border border-dashed border-white/10 bg-slate-950/40 p-5 text-sm text-slate-400">
      No workflow selected.
    </div>

    <div v-if="loading" class="mt-4 text-sm text-slate-400">Loading progress…</div>
    <p v-if="error" class="mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </p>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
  workflowId: {
    type: [String, Number],
    default: null,
  },
  workflowTitle: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: 'Workflow telemetry',
  },
  autoRefresh: {
    type: Boolean,
    default: true,
  },
  refreshMs: {
    type: Number,
    default: 15000,
  },
})

const loading = ref(false)
const error = ref('')
const payload = ref(null)
const message = ref('')
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

const statusLabel = computed(() => {
  return payload.value?.status ?? payload.value?.state ?? 'unknown'
})

const completedSteps = computed(() => {
  return normalizeNumber(payload.value?.completed_steps ?? payload.value?.completed ?? payload.value?.finished_steps)
})

const totalSteps = computed(() => {
  return normalizeNumber(payload.value?.total_steps ?? payload.value?.total ?? payload.value?.steps_total)
})

const progressPercent = computed(() => {
  const explicit = payload.value?.progress ?? payload.value?.percent ?? payload.value?.completion
  if (explicit !== undefined && explicit !== null) {
    return Math.max(0, Math.min(100, normalizeNumber(explicit)))
  }

  if (!totalSteps.value) return 0
  return Math.max(0, Math.min(100, Math.round((completedSteps.value / totalSteps.value) * 100)))
})

const progressLabel = computed(() => {
  return `${progressPercent.value}%`
})

const currentStep = computed(() => {
  return (
    payload.value?.current_step?.name ??
    payload.value?.current_step ??
    payload.value?.active_step?.name ??
    payload.value?.active_step ??
    payload.value?.stage ??
    'Not started'
  )
})

const remainingStepsLabel = computed(() => {
  if (!totalSteps.value) return '—'
  return `${Math.max(totalSteps.value - completedSteps.value, 0)} steps`
})

const updatedAtLabel = computed(() => {
  return asDate(payload.value?.updated_at ?? payload.value?.last_updated_at ?? payload.value?.timestamp)
})

const steps = computed(() => {
  const source = payload.value?.steps ?? payload.value?.step_progress ?? payload.value?.step_results ?? []
  if (!Array.isArray(source)) return []

  return source.map((step, index) => {
    const status = String(step?.status ?? step?.state ?? 'pending')
    const normalizedStatus = status.toLowerCase()
    const badgeClass =
      normalizedStatus === 'done' || normalizedStatus === 'completed' || normalizedStatus === 'success'
        ? 'bg-emerald-500/15 text-emerald-300'
        : normalizedStatus === 'running' || normalizedStatus === 'in_progress' || normalizedStatus === 'active'
          ? 'bg-sky-500/15 text-sky-300'
          : normalizedStatus === 'failed' || normalizedStatus === 'error'
            ? 'bg-rose-500/15 text-rose-300'
            : 'bg-slate-500/15 text-slate-300'

    return {
      key: step?.id ?? step?.key ?? index,
      label: step?.name ?? step?.label ?? `Step ${index + 1}`,
      hint: step?.description ?? step?.detail ?? '',
      status,
      badgeClass,
    }
  })
})

async function loadProgress() {
  if (!props.workflowId) {
    payload.value = null
    return
  }

  loading.value = true
  error.value = ''
  message.value = ''

  try {
    const response = await fetch(`/api/v1/workflows/${props.workflowId}/progress`, {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`)
    }

    const data = await response.json()
    const result = data?.data ?? data?.result ?? data
    payload.value = result
    message.value = result?.message ?? result?.summary ?? ''
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load workflow progress.'
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
  if (!props.autoRefresh || !props.workflowId) return
  timer = setInterval(() => {
    loadProgress()
  }, props.refreshMs)
}

watch(
  () => props.workflowId,
  async () => {
    await loadProgress()
    startTimer()
  },
  { immediate: true },
)

watch(
  () => props.autoRefresh,
  () => {
    startTimer()
  },
)

onBeforeUnmount(() => {
  stopTimer()
})
</script>