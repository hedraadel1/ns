<template>
  <section class="space-y-5">
    <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Workflows hub</p>
          <h1 class="mt-1 text-2xl font-semibold text-white">Definitions, execution, and templates</h1>
          <p class="mt-1 text-sm text-slate-400">
            Live data from /api/v1/workflows, /api/v1/tasks/stats, /api/v1/tasks/active, and /api/v1/workflows/templates.
          </p>
        </div>

        <button
          type="button"
          class="rounded-lg border border-emerald-500/30 px-3 py-2 text-sm font-medium text-emerald-300 transition hover:border-emerald-400/60 hover:bg-emerald-500/10"
          :disabled="loading"
          @click="loadWorkflows"
        >
          Refresh
        </button>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Workflows</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ workflows.length }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Active</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ activeWorkflows.length }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Templates</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ templateCount }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Selected progress</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ selectedWorkflow ? `${selectedWorkflow.progress}%` : '—' }}</p>
        </div>
      </div>

      <div class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_280px]">
        <label class="block text-sm font-medium text-slate-300">
          Search workflows
          <input
            v-model="query"
            type="search"
            class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400/50"
            placeholder="Filter by name, owner, tag, or status"
          />
        </label>

        <label class="block text-sm font-medium text-slate-300">
          Status
          <select
            v-model="statusFilter"
            class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition focus:border-emerald-400/50"
          >
            <option value="all">All statuses</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="paused">Paused</option>
            <option value="archived">Archived</option>
          </select>
        </label>
      </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)]">
      <div class="space-y-3">
        <article
          v-for="workflow in filteredWorkflows"
          :key="workflow.key"
          class="cursor-pointer rounded-2xl border p-4 transition"
          :class="selectedWorkflow?.key === workflow.key ? 'border-emerald-400/40 bg-emerald-500/10' : 'border-white/10 bg-slate-900/70 hover:border-emerald-500/30 hover:bg-white/5'"
          @click="selectedWorkflow = workflow"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-white">{{ workflow.name }}</h2>
              <p class="mt-1 text-sm text-slate-400">{{ workflow.description }}</p>
            </div>

            <span
              class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
              :class="workflow.badgeClass"
            >
              {{ workflow.status }}
            </span>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Owner</p>
              <p class="mt-1 text-sm text-white">{{ workflow.owner }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Type</p>
              <p class="mt-1 text-sm text-white">{{ workflow.type }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Steps</p>
              <p class="mt-1 text-sm text-white">{{ workflow.stepsCount }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Updated</p>
              <p class="mt-1 text-sm text-white">{{ workflow.updatedAt }}</p>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <span
              v-for="tag in workflow.tags"
              :key="tag"
              class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-slate-300"
            >
              {{ tag }}
            </span>
          </div>
        </article>

        <div
          v-if="!filteredWorkflows.length"
          class="rounded-2xl border border-dashed border-white/10 bg-slate-950/40 p-5 text-sm text-slate-400"
        >
          No workflows match the current filters.
        </div>
      </div>

      <aside class="space-y-5">
        <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
          <template v-if="selectedWorkflow">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Selected workflow</p>
            <h2 class="mt-1 text-xl font-semibold text-white">{{ selectedWorkflow.name }}</h2>
            <p class="mt-2 text-sm text-slate-400">{{ selectedWorkflow.description }}</p>

            <div class="mt-5 space-y-3">
              <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Category</p>
                <p class="mt-1 text-sm text-white">{{ selectedWorkflow.category }}</p>
              </div>
              <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Trigger</p>
                <p class="mt-1 text-sm text-white">{{ selectedWorkflow.trigger }}</p>
              </div>
              <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Progress</p>
                <p class="mt-1 text-sm text-white">{{ selectedWorkflow.progress }}%</p>
              </div>
              <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Last run</p>
                <p class="mt-1 text-sm text-white">{{ selectedWorkflow.lastRun }}</p>
              </div>
            </div>

            <div class="mt-5">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Tags</p>
              <div class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="tag in selectedWorkflow.tags"
                  :key="`${selectedWorkflow.key}-${tag}`"
                  class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-slate-300"
                >
                  {{ tag }}
                </span>
              </div>
            </div>
          </template>

          <div v-else class="rounded-xl border border-dashed border-white/10 bg-white/5 p-5 text-sm text-slate-400">
            Select a workflow to inspect details.
          </div>
        </section>

        <ProgressTracker
          v-if="selectedWorkflow"
          :workflow-id="selectedWorkflow.id"
          :workflow-title="selectedWorkflow.name"
          title="Execution progress"
        />
      </aside>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
      <TaskMonitor />
      <TemplateLibrary />
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
          <div class="space-y-2">
            <div class="h-3 w-full bg-slate-800"></div>
            <div class="h-3 w-5/6 bg-slate-800"></div>
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
import ProgressTracker from '../Components/ProgressTracker.vue'
import TaskMonitor from './TaskMonitor.vue'
import TemplateLibrary from './TemplateLibrary.vue'

const loading = ref(false)
const error = ref('')
const query = ref('')
const statusFilter = ref('all')
const workflows = ref([])
const selectedWorkflow = ref(null)
const templateCount = ref(0)

const asDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString()
}

function normalizeTags(workflow) {
  const source = workflow?.tags ?? workflow?.labels ?? workflow?.categories ?? []
  if (Array.isArray(source)) {
    return source.map((item) => (typeof item === 'string' ? item : item?.name ?? item?.label ?? String(item)))
  }

  if (typeof source === 'string') {
    return source.split(',').map((item) => item.trim()).filter(Boolean)
  }

  return []
}

function resolveStatus(workflow) {
  return String(workflow?.status ?? workflow?.state ?? workflow?.phase ?? 'draft')
}

function normalizeWorkflow(workflow, index) {
  const status = resolveStatus(workflow)
  const normalized = status.toLowerCase()
  const badgeClass =
    normalized === 'active' || normalized === 'running'
      ? 'bg-emerald-500/15 text-emerald-300'
      : normalized === 'paused'
        ? 'bg-amber-500/15 text-amber-300'
        : normalized === 'archived'
          ? 'bg-slate-500/15 text-slate-300'
          : 'bg-sky-500/15 text-sky-300'

  const steps = workflow?.steps ?? workflow?.step_count ?? workflow?.nodes ?? []
  const stepsCount = Array.isArray(steps) ? steps.length : Number(steps) || 0

  return {
    key: workflow?.id ?? workflow?.uuid ?? index,
    id: workflow?.id ?? workflow?.uuid ?? index,
    name: workflow?.name ?? workflow?.title ?? `Workflow ${index + 1}`,
    description: workflow?.description ?? workflow?.summary ?? '',
    status,
    badgeClass,
    owner: workflow?.owner ?? workflow?.assigned_to ?? workflow?.creator ?? '—',
    type: workflow?.type ?? workflow?.category ?? 'Standard',
    category: workflow?.category ?? workflow?.group ?? 'General',
    trigger: workflow?.trigger ?? workflow?.trigger_type ?? workflow?.schedule ?? 'Manual',
    progress: Number(workflow?.progress ?? workflow?.completion ?? workflow?.percent ?? 0) || 0,
    stepsCount,
    updatedAt: asDate(workflow?.updated_at ?? workflow?.last_updated_at),
    lastRun: asDate(workflow?.last_run_at ?? workflow?.executed_at ?? workflow?.updated_at),
    tags: normalizeTags(workflow),
  }
}

const filteredWorkflows = computed(() => {
  const term = query.value.trim().toLowerCase()

  return workflows.value.filter((workflow) => {
    const matchesStatus = statusFilter.value === 'all' || workflow.status.toLowerCase() === statusFilter.value
    if (!matchesStatus) return false

    if (!term) return true

    const haystack = [
      workflow.name,
      workflow.description,
      workflow.owner,
      workflow.type,
      workflow.category,
      workflow.status,
      workflow.trigger,
      ...workflow.tags,
    ]
      .join(' ')
      .toLowerCase()

    return haystack.includes(term)
  })
})

const activeWorkflows = computed(() => workflows.value.filter((workflow) => ['active', 'running'].includes(workflow.status.toLowerCase())))

async function loadWorkflows() {
  loading.value = true
  error.value = ''

  try {
    const [workflowResponse, templateResponse] = await Promise.all([
      fetch('/api/v1/workflows', { headers: { Accept: 'application/json' } }),
      fetch('/api/v1/workflows/templates', { headers: { Accept: 'application/json' } }),
    ])

    if (!workflowResponse.ok) {
      throw new Error(`Workflows request failed with status ${workflowResponse.status}`)
    }

    if (!templateResponse.ok) {
      throw new Error(`Templates request failed with status ${templateResponse.status}`)
    }

    const workflowData = await workflowResponse.json()
    const workflowList = workflowData?.data ?? workflowData?.result ?? workflowData
    const workflowSource = Array.isArray(workflowList) ? workflowList : workflowList?.items ?? workflowList?.workflows ?? []
    workflows.value = Array.isArray(workflowSource) ? workflowSource.map(normalizeWorkflow) : []
    selectedWorkflow.value = workflows.value[0] ?? null

    const templateData = await templateResponse.json()
    const templateList = templateData?.data ?? templateData?.result ?? templateData
    const templateSource = Array.isArray(templateList) ? templateList : templateList?.items ?? templateList?.templates ?? []
    templateCount.value = Array.isArray(templateSource) ? templateSource.length : 0
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load workflows.'
  } finally {
    loading.value = false
  }
}

onMounted(loadWorkflows)
</script>
