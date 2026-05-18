<template>
  <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Template library</p>
        <h2 class="mt-1 text-xl font-semibold text-white">Workflow starting points</h2>
        <p class="mt-1 text-sm text-slate-400">Browse templates from /api/v1/workflows/templates.</p>
      </div>

      <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-right">
        <p class="text-xs uppercase tracking-wide text-slate-500">Templates</p>
        <p class="text-2xl font-semibold text-white">{{ templates.length }}</p>
      </div>
    </div>

    <div class="mt-5">
      <label class="block text-sm font-medium text-slate-300">
        Search templates
        <input
          v-model="query"
          type="search"
          class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400/50"
          placeholder="Filter by name, category, or tag"
        />
      </label>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
      <div class="space-y-3">
        <article
          v-for="template in filteredTemplates"
          :key="template.key"
          class="cursor-pointer rounded-2xl border p-4 transition"
          :class="selectedTemplate?.key === template.key ? 'border-emerald-400/40 bg-emerald-500/10' : 'border-white/10 bg-slate-950/60 hover:border-emerald-500/30 hover:bg-white/5'"
          @click="selectedTemplate = template"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h3 class="text-base font-semibold text-white">{{ template.name }}</h3>
              <p class="mt-1 text-sm text-slate-400">{{ template.description }}</p>
            </div>

            <span class="rounded-full bg-white/5 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-300">
              {{ template.category }}
            </span>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <span
              v-for="tag in template.tags"
              :key="tag"
              class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-slate-300"
            >
              {{ tag }}
            </span>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Steps</p>
              <p class="mt-1 text-sm text-white">{{ template.steps.length }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Difficulty</p>
              <p class="mt-1 text-sm text-white">{{ template.difficulty }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Updated</p>
              <p class="mt-1 text-sm text-white">{{ template.updatedAt }}</p>
            </div>
          </div>
        </article>

        <div v-if="!filteredTemplates.length" class="rounded-2xl border border-dashed border-white/10 bg-slate-950/40 p-5 text-sm text-slate-400">
          No templates match your search.
        </div>
      </div>

      <aside class="rounded-2xl border border-white/10 bg-slate-950/60 p-5">
        <template v-if="selectedTemplate">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Selected template</p>
          <h3 class="mt-1 text-lg font-semibold text-white">{{ selectedTemplate.name }}</h3>
          <p class="mt-2 text-sm text-slate-400">{{ selectedTemplate.description }}</p>

          <div class="mt-4 space-y-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Category</p>
              <p class="mt-1 text-sm text-white">{{ selectedTemplate.category }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Use case</p>
              <p class="mt-1 text-sm text-white">{{ selectedTemplate.useCase }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Recommended agents</p>
              <p class="mt-1 text-sm text-white">{{ selectedTemplate.agents }}</p>
            </div>
          </div>

          <div class="mt-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Template steps</p>
            <ol class="mt-3 space-y-2">
              <li
                v-for="(step, index) in selectedTemplate.steps"
                :key="`${selectedTemplate.key}-${index}`"
                class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200"
              >
                <span class="mr-2 text-emerald-300">{{ index + 1 }}.</span>
                {{ step }}
              </li>
            </ol>
          </div>

          <div class="mt-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            Templates are read from the live API. Editing and publishing are not exposed by the documented contracts yet.
          </div>
        </template>

        <div v-else class="rounded-xl border border-dashed border-white/10 bg-white/5 p-5 text-sm text-slate-400">
          Select a template to inspect its details.
        </div>
      </aside>
    </div>

    <p v-if="error" class="mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </p>

    <div v-if="loading" class="mt-4 text-sm text-slate-400">Loading templates…</div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'

const loading = ref(false)
const error = ref('')
const query = ref('')
const templates = ref([])
const selectedTemplate = ref(null)

const asDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleDateString()
}

function normalizeTemplate(template, index) {
  return {
    key: template?.id ?? template?.uuid ?? index,
    name: template?.name ?? template?.title ?? `Template ${index + 1}`,
    description: template?.description ?? template?.summary ?? '',
    category: template?.category ?? template?.type ?? 'General',
    difficulty: template?.difficulty ?? template?.level ?? 'Standard',
    updatedAt: asDate(template?.updated_at ?? template?.last_updated_at),
    tags: Array.isArray(template?.tags) ? template.tags : [],
    steps: Array.isArray(template?.steps)
      ? template.steps.map((step) => (typeof step === 'string' ? step : step?.name ?? step?.label ?? step?.title ?? 'Step'))
      : [],
    useCase: template?.use_case ?? template?.useCase ?? template?.purpose ?? '—',
    agents: Array.isArray(template?.agents)
      ? template.agents.map((agent) => (typeof agent === 'string' ? agent : agent?.name ?? agent?.role ?? 'Agent')).join(', ')
      : template?.recommended_agents ?? template?.agents ?? '—',
  }
}

const filteredTemplates = computed(() => {
  const term = query.value.trim().toLowerCase()
  if (!term) return templates.value

  return templates.value.filter((template) => {
    const haystack = [
      template.name,
      template.description,
      template.category,
      template.difficulty,
      template.useCase,
      template.agents,
      ...template.tags,
      ...template.steps,
    ]
      .join(' ')
      .toLowerCase()

    return haystack.includes(term)
  })
})

async function loadTemplates() {
  loading.value = true
  error.value = ''

  try {
    const response = await fetch('/api/v1/workflows/templates', {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`)
    }

    const data = await response.json()
    const list = data?.data ?? data?.result ?? data
    const source = Array.isArray(list) ? list : list?.items ?? list?.templates ?? []
    templates.value = Array.isArray(source) ? source.map(normalizeTemplate) : []
    selectedTemplate.value = templates.value[0] ?? null
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load workflow templates.'
  } finally {
    loading.value = false
  }
}

onMounted(loadTemplates)
</script>