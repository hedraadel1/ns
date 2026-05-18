<template>
  <section class="space-y-5">
    <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Nexus hub</p>
          <h1 class="mt-1 text-2xl font-semibold text-white">Orchestration and coordination</h1>
          <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-400">
            This hub is the shared coordination surface for cross-system operations, routing, and status at a glance.
          </p>
        </div>

        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
          Live shell surface
        </div>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Connected hubs</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ metrics.connected }}</p>
          <p v-if="loadingMetrics" class="mt-1 text-xs text-slate-400">Loading...</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Queued actions</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ metrics.queued }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Open alerts</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ metrics.alerts }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">State</p>
          <p class="mt-2 text-2xl font-semibold text-white" :class="{ 'text-red-400': metrics.state === 'Unavailable' }">
            {{ metrics.state }}
          </p>
        </div>
      </div>

      <div v-if="metricsError" class="mt-3 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">
        <strong>Error loading metrics:</strong> {{ metricsError }}
        <button @click="loadMetrics" class="ml-3 underline hover:text-red-200">Retry</button>
      </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
      <div class="space-y-3">
        <article
          v-for="item in items"
          :key="item.key"
          class="rounded-2xl border p-4 transition"
          :class="selectedItem?.key === item.key ? 'border-emerald-400/40 bg-emerald-500/10' : 'border-white/10 bg-slate-900/70 hover:border-emerald-500/30 hover:bg-white/5'"
          @click="selectedItem = item"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-white">{{ item.title }}</h2>
              <p class="mt-1 text-sm text-slate-400">{{ item.description }}</p>
            </div>

            <span class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide" :class="item.badgeClass">
              {{ item.status }}
            </span>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Owner</p>
              <p class="mt-1 text-sm text-white">{{ item.owner }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Updated</p>
              <p class="mt-1 text-sm text-white">{{ item.updatedAt }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Priority</p>
              <p class="mt-1 text-sm text-white">{{ item.priority }}</p>
            </div>
          </div>
        </article>
      </div>

      <aside class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
        <template v-if="selectedItem">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Selected item</p>
          <h2 class="mt-1 text-xl font-semibold text-white">{{ selectedItem.title }}</h2>
          <p class="mt-2 text-sm text-slate-400">{{ selectedItem.description }}</p>

          <div class="mt-5 space-y-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Summary</p>
              <p class="mt-1 text-sm text-white">{{ selectedItem.summary }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Responsibility</p>
              <p class="mt-1 text-sm text-white">{{ selectedItem.responsibility }}</p>
            </div>
          </div>
        </template>

        <div v-else class="rounded-xl border border-dashed border-white/10 bg-white/5 p-5 text-sm text-slate-400">
          Select a nexus item to inspect details.
        </div>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

// Live metrics from API
const metrics = ref({
  connected: '0',
  queued: '0',
  alerts: '0',
  state: 'Loading...',
})

const loadingMetrics = ref(false)
const metricsError = ref(null)
const retryAttempts = ref(0)
const maxRetries = 3
const retryDelayMs = 1200
let metricsInterval = null

// Load metrics from API
async function loadMetrics() {
  loadingMetrics.value = true
  metricsError.value = null

  try {
    const response = await fetch('/api/v1/health', {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    metrics.value = {
      connected: data.connected_hubs?.toString() || '0',
      queued: data.queued_tasks?.toString() || '0',
      alerts: data.open_alerts?.toString() || '0',
      state: data.system_state || data.status || 'Unknown',
    }
    retryAttempts.value = 0
  } catch (err) {
    console.warn('Failed to load metrics:', err)
    metricsError.value = err instanceof Error ? err.message : 'Failed to fetch metrics.'
    metrics.value = {
      connected: '--',
      queued: '--',
      alerts: '--',
      state: 'Unavailable',
    }

    if (retryAttempts.value < maxRetries) {
      retryAttempts.value += 1
      setTimeout(loadMetrics, retryAttempts.value * retryDelayMs)
    }
  } finally {
    loadingMetrics.value = false
  }
}

// Refresh metrics every 30 seconds
onMounted(() => {
  loadMetrics()
  metricsInterval = setInterval(loadMetrics, 30000)
})

onBeforeUnmount(() => {
  if (metricsInterval) {
    clearInterval(metricsInterval)
  }
})

const items = [
  {
    key: 'routing',
    title: 'Routing matrix',
    description: 'Hub-to-hub traffic and escalation paths.',
    status: 'active',
    owner: 'Platform',
    updatedAt: 'Current',
    priority: 'High',
    summary: 'Controls the movement of work between agents, workflows, and support surfaces.',
    responsibility: 'Ensure each hub stays reachable and discoverable.',
    badgeClass: 'bg-emerald-500/15 text-emerald-300',
  },
  {
    key: 'ops',
    title: 'Operations view',
    description: 'Current system posture and coordination state.',
    status: 'monitoring',
    owner: 'Ops',
    updatedAt: 'Current',
    priority: 'Medium',
    summary: 'Displays live operational context for reviewers and administrators.',
    responsibility: 'Surface noteworthy events without blocking the shell.',
    badgeClass: 'bg-sky-500/15 text-sky-300',
  },
  {
    key: 'release',
    title: 'Release lane',
    description: 'Cross-cutting deployment and rollout coordination.',
    status: 'queued',
    owner: 'Engineering',
    updatedAt: 'Pending',
    priority: 'Low',
    summary: 'Tracks release-adjacent tasks that may affect multiple hubs.',
    responsibility: 'Provide a consistent place for rollout visibility.',
    badgeClass: 'bg-amber-500/15 text-amber-300',
  },
]

const selectedItem = ref(items[0])
</script>
