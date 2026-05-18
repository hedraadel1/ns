<template>
  <section class="space-y-5">
    <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Agents hub</p>
          <h1 class="mt-1 text-2xl font-semibold text-white">Agent roster and availability</h1>
          <p class="mt-1 text-sm text-slate-400">Live data from /api/v1/agents.</p>
        </div>

        <button
          type="button"
          class="rounded-lg border border-emerald-500/30 px-3 py-2 text-sm font-medium text-emerald-300 transition hover:border-emerald-400/60 hover:bg-emerald-500/10"
          :disabled="loading"
          @click="loadAgents"
        >
          Refresh
        </button>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Total agents</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ agents.length }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Online</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ onlineAgents.length }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Busy</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ busyAgents.length }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Idle</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ idleAgents.length }}</p>
        </div>
      </div>

      <div class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_280px]">
        <label class="block text-sm font-medium text-slate-300">
          Search agents
          <input
            v-model="query"
            type="search"
            class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400/50"
            placeholder="Filter by name, role, model, or status"
          />
        </label>

        <label class="block text-sm font-medium text-slate-300">
          Status
          <select
            v-model="statusFilter"
            class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition focus:border-emerald-400/50"
          >
            <option value="all">All statuses</option>
            <option value="online">Online</option>
            <option value="busy">Busy</option>
            <option value="idle">Idle</option>
            <option value="offline">Offline</option>
          </select>
        </label>
      </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
      <div class="space-y-3">
        <article
          v-for="agent in filteredAgents"
          :key="agent.key"
          class="cursor-pointer rounded-2xl border p-4 transition"
          :class="selectedAgent?.key === agent.key ? 'border-emerald-400/40 bg-emerald-500/10' : 'border-white/10 bg-slate-900/70 hover:border-emerald-500/30 hover:bg-white/5'"
          @click="selectedAgent = agent"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-white">{{ agent.name }}</h2>
              <p class="mt-1 text-sm text-slate-400">{{ agent.role }}</p>
            </div>

            <span
              class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
              :class="agent.badgeClass"
            >
              {{ agent.status }}
            </span>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Model</p>
              <p class="mt-1 text-sm text-white">{{ agent.model }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Workload</p>
              <p class="mt-1 text-sm text-white">{{ agent.workload }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Tasks</p>
              <p class="mt-1 text-sm text-white">{{ agent.tasks }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Updated</p>
              <p class="mt-1 text-sm text-white">{{ agent.updatedAt }}</p>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <span
              v-for="capability in agent.capabilities"
              :key="capability"
              class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-slate-300"
            >
              {{ capability }}
            </span>
          </div>
        </article>

        <div v-if="!filteredAgents.length" class="rounded-2xl border border-dashed border-white/10 bg-slate-950/40 p-5 text-sm text-slate-400">
          No agents match the current filters.
        </div>
      </div>

      <aside class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
        <template v-if="selectedAgent">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Selected agent</p>
          <h2 class="mt-1 text-xl font-semibold text-white">{{ selectedAgent.name }}</h2>
          <p class="mt-2 text-sm text-slate-400">{{ selectedAgent.description }}</p>

          <div class="mt-5 space-y-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Type</p>
              <p class="mt-1 text-sm text-white">{{ selectedAgent.type }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Primary focus</p>
              <p class="mt-1 text-sm text-white">{{ selectedAgent.focus }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">API status</p>
              <p class="mt-1 text-sm text-white">{{ selectedAgent.apiStatus }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Last activity</p>
              <p class="mt-1 text-sm text-white">{{ selectedAgent.lastActivity }}</p>
            </div>
          </div>

          <div class="mt-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Capabilities</p>
            <ul class="mt-3 space-y-2">
              <li
                v-for="capability in selectedAgent.capabilities"
                :key="capability"
                class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200"
              >
                {{ capability }}
              </li>
            </ul>
          </div>

          <div class="mt-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            This hub is read-only until agent management mutations are added to the API contract.
          </div>
        </template>

        <div v-else class="rounded-xl border border-dashed border-white/10 bg-white/5 p-5 text-sm text-slate-400">
          Select an agent to inspect details.
        </div>
      </aside>
    </div>

    <p v-if="error" class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </p>

    <!-- Loading skeleton -->
    <div v-if="loading" class="grid gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
      <div class="space-y-3">
        <div v-for="n in 4" :key="n" class="animate-pulse rounded-2xl border border-white/10 bg-slate-900/70 p-4">
          <div class="mb-3 h-5 w-1/3 bg-slate-800"></div>
          <div class="mb-4 h-4 w-1/2 bg-slate-800"></div>
          <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <div class="mb-2 h-3 w-1/2 bg-slate-800"></div>
              <div class="h-4 w-2/3 bg-slate-800"></div>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <div class="mb-2 h-3 w-1/2 bg-slate-800"></div>
              <div class="h-4 w-2/3 bg-slate-800"></div>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <div class="mb-2 h-3 w-1/2 bg-slate-800"></div>
              <div class="h-4 w-2/3 bg-slate-800"></div>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <div class="mb-2 h-3 w-1/2 bg-slate-800"></div>
              <div class="h-4 w-2/3 bg-slate-800"></div>
            </div>
          </div>
        </div>
      </div>
      <aside class="space-y-3">
        <div class="animate-pulse rounded-2xl border border-white/10 bg-slate-900/70 p-4">
          <div class="mb-4 h-6 w-1/2 bg-slate-800"></div>
          <div class="space-y-3">
            <div class="h-4 w-3/4 bg-slate-800"></div>
            <div class="h-4 w-2/3 bg-slate-800"></div>
            <div class="h-4 w-3/4 bg-slate-800"></div>
          </div>
        </div>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'

const loading = ref(false)
const error = ref('')
const query = ref('')
const statusFilter = ref('all')
const agents = ref([])
const selectedAgent = ref(null)

const asDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString()
}

function normalizeCapabilities(agent) {
  const source = agent?.capabilities ?? agent?.skills ?? agent?.specialties ?? []
  if (Array.isArray(source)) {
    return source.map((item) => (typeof item === 'string' ? item : item?.name ?? item?.label ?? String(item)))
  }

  if (typeof source === 'string') {
    return source.split(',').map((item) => item.trim()).filter(Boolean)
  }

  return []
}

function resolveStatus(agent) {
  return String(agent?.status ?? agent?.state ?? agent?.availability ?? 'offline')
}

function normalizeAgent(agent, index) {
  const status = resolveStatus(agent)
  const normalized = status.toLowerCase()
  const badgeClass =
    normalized === 'online' || normalized === 'available'
      ? 'bg-emerald-500/15 text-emerald-300'
      : normalized === 'busy' || normalized === 'working'
        ? 'bg-sky-500/15 text-sky-300'
        : normalized === 'idle'
          ? 'bg-amber-500/15 text-amber-300'
          : 'bg-slate-500/15 text-slate-300'

  const workload = agent?.workload ?? agent?.load ?? agent?.capacity ?? '—'
  const tasks = agent?.active_tasks ?? agent?.tasks_active ?? agent?.task_count ?? agent?.tasks ?? '—'

  return {
    key: agent?.id ?? agent?.uuid ?? index,
    name: agent?.name ?? agent?.display_name ?? `Agent ${index + 1}`,
    role: agent?.role ?? agent?.title ?? agent?.type ?? 'Agent',
    type: agent?.type ?? agent?.category ?? 'General',
    status,
    badgeClass,
    workload: typeof workload === 'number' ? `${workload}%` : workload,
    tasks: typeof tasks === 'number' ? tasks : tasks ?? '—',
    model: agent?.model ?? agent?.model_name ?? agent?.llm ?? '—',
    description: agent?.description ?? agent?.summary ?? '',
    focus: agent?.focus ?? agent?.specialization ?? agent?.purpose ?? '—',
    apiStatus: agent?.api_status ?? agent?.health ?? agent?.connection_status ?? status,
    lastActivity: asDate(agent?.updated_at ?? agent?.last_seen_at ?? agent?.last_activity_at),
    capabilities: normalizeCapabilities(agent),
  }
}

const filteredAgents = computed(() => {
  const term = query.value.trim().toLowerCase()
  return agents.value.filter((agent) => {
    const matchesStatus = statusFilter.value === 'all' || agent.status.toLowerCase() === statusFilter.value
    if (!matchesStatus) return false

    if (!term) return true

    const haystack = [
      agent.name,
      agent.role,
      agent.type,
      agent.status,
      agent.model,
      agent.focus,
      agent.description,
      ...agent.capabilities,
    ]
      .join(' ')
      .toLowerCase()

    return haystack.includes(term)
  })
})

const onlineAgents = computed(() => agents.value.filter((agent) => ['online', 'available'].includes(agent.status.toLowerCase())))
const busyAgents = computed(() => agents.value.filter((agent) => ['busy', 'working', 'running'].includes(agent.status.toLowerCase())))
const idleAgents = computed(() => agents.value.filter((agent) => ['idle'].includes(agent.status.toLowerCase())))

async function loadAgents() {
  loading.value = true
  error.value = ''

  try {
    const response = await fetch('/api/v1/agents', {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`)
    }

    const data = await response.json()
    const list = data?.data ?? data?.result ?? data
    const source = Array.isArray(list) ? list : list?.items ?? list?.agents ?? []
    agents.value = Array.isArray(source) ? source.map(normalizeAgent) : []
    selectedAgent.value = agents.value[0] ?? null
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load agents.'
  } finally {
    loading.value = false
  }
}

onMounted(loadAgents)
</script>
