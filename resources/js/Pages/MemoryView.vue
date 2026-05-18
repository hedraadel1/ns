<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
      <section class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-2xl shadow-black/20">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-400">Memory hub</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Persistent memory browser</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
              Search stored memories, browse recent records, and inspect the full structured payload of any item.
            </p>
          </div>

          <div class="flex flex-wrap gap-3">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/20"
              @click="refreshAll"
            >
              <span class="text-base">↻</span>
              Refresh
            </button>
          </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
          <div
            v-for="card in statsCards"
            :key="card.label"
            class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4"
          >
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ card.label }}</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ card.value }}</p>
            <p class="mt-2 text-sm text-slate-400">{{ card.helper }}</p>
          </div>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
        <div class="space-y-6">
          <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl shadow-black/10">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
              <label class="flex flex-1 flex-col gap-2 text-sm text-slate-300">
                <span class="font-medium text-slate-200">Search memories</span>
                <input
                  v-model.trim="query"
                  type="search"
                  placeholder="Search by keyword, tag, or entity…"
                  class="rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                  @keyup.enter="executeSearch"
                />
              </label>

              <div class="flex gap-3">
                <button
                  type="button"
                  class="rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-sm font-semibold text-slate-200 transition hover:border-emerald-500/40 hover:text-emerald-200"
                  @click="executeSearch"
                >
                  Search
                </button>
                <button
                  type="button"
                  class="rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-sm font-semibold text-slate-200 transition hover:border-emerald-500/40 hover:text-emerald-200"
                  @click="loadMemories"
                >
                  Browse all
                </button>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="chip in modeChips"
                  :key="chip.value"
                  type="button"
                  class="rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                  :class="chip.value === activeMode
                    ? 'border-emerald-500/60 bg-emerald-500/15 text-emerald-300'
                    : 'border-slate-700 bg-slate-950/60 text-slate-300 hover:border-emerald-500/40 hover:text-emerald-200'"
                  @click="setMode(chip.value)"
                >
                  {{ chip.label }}
                </button>
              </div>

              <div class="text-sm text-slate-400">
                <span v-if="loading">Loading…</span>
                <span v-else-if="error" class="text-rose-300">{{ error }}</span>
                <span v-else>{{ memories.length }} records</span>
              </div>
            </div>
          </div>

          <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl shadow-black/10">
            <div class="border-b border-slate-800 px-5 py-4">
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-white">
                    {{ activeMode === 'search' ? 'Search results' : 'Memory records' }}
                  </h2>
                  <p class="text-sm text-slate-400">
                    Click any item to inspect the full memory record.
                  </p>
                </div>
                <p class="text-sm text-slate-400">
                  Page {{ pagination.page }} of {{ pagination.lastPage }}
                </p>
              </div>
            </div>

            <div v-if="loading && !memories.length" class="space-y-3 p-8">
              <div v-for="n in 5" :key="n" class="animate-pulse rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                <div class="mb-2 h-4 w-1/3 bg-slate-800"></div>
                <div class="mb-3 h-3 w-full bg-slate-800"></div>
                <div class="h-3 w-2/3 bg-slate-800"></div>
              </div>
            </div>

            <div v-else-if="!memories.length" class="p-8">
              <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-8 text-center">
                <p class="text-lg font-medium text-slate-200">No memories found</p>
                <p class="mt-2 text-sm text-slate-400">
                  Try a different search term or browse the full memory collection.
                </p>
              </div>
            </div>

            <div v-else class="divide-y divide-slate-800">
              <button
                v-for="memory in memories"
                :key="memoryKey(memory)"
                type="button"
                class="w-full px-5 py-4 text-left transition hover:bg-slate-950/60"
                :class="memoryKey(memory) === selectedMemoryKey ? 'bg-emerald-500/10' : ''"
                @click="selectedMemory = memory"
              >
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                  <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-300">
                        {{ memoryType(memory) }}
                      </span>
                      <span v-if="memoryScope(memory)" class="rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-300">
                        {{ memoryScope(memory) }}
                      </span>
                    </div>

                    <p class="truncate text-sm font-medium text-white">
                      {{ memoryTitle(memory) }}
                    </p>
                    <p class="line-clamp-2 text-sm leading-6 text-slate-400">
                      {{ memorySummary(memory) }}
                    </p>
                  </div>

                  <div class="flex items-center gap-4">
                    <div class="text-right">
                      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Updated</p>
                      <p class="mt-1 text-sm font-semibold text-slate-200">{{ memoryTimestamp(memory) }}</p>
                    </div>
                    <span class="rounded-full border border-slate-700 bg-slate-950/60 px-3 py-1 text-xs text-slate-400">
                      Inspect
                    </span>
                  </div>
                </div>
              </button>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
              <p class="text-sm text-slate-400">
                {{ pagination.total !== null ? `${pagination.total} total` : 'Total unknown' }}
              </p>

              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200 transition hover:border-emerald-500/50 disabled:cursor-not-allowed disabled:opacity-40"
                  :disabled="!pagination.hasPrev || loading"
                  @click="goToPage(pagination.page - 1)"
                >
                  Prev
                </button>
                <button
                  type="button"
                  class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200 transition hover:border-emerald-500/50 disabled:cursor-not-allowed disabled:opacity-40"
                  :disabled="!pagination.hasNext || loading"
                  @click="goToPage(pagination.page + 1)"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>

        <aside class="space-y-6">
          <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl shadow-black/10">
            <div class="flex items-center justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold text-white">Selected memory</h2>
                <p class="text-sm text-slate-400">Detail view and structured payload</p>
              </div>
              <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                Memory viewer
              </span>
            </div>

            <div v-if="selectedMemory" class="mt-5 space-y-4">
              <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Title</p>
                <p class="mt-2 text-sm font-semibold text-slate-100">{{ memoryTitle(selectedMemory) }}</p>
              </div>

              <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Summary</p>
                <p class="mt-2 text-sm leading-6 text-slate-200">{{ memorySummary(selectedMemory) }}</p>
              </div>

              <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                  <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Type</p>
                  <p class="mt-2 text-sm font-semibold text-slate-100">{{ memoryType(selectedMemory) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                  <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Scope</p>
                  <p class="mt-2 text-sm font-semibold text-slate-100">{{ memoryScope(selectedMemory) || '—' }}</p>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Raw memory</p>
                <pre class="mt-3 max-h-[340px] overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-300">{{ prettyJson(selectedMemory) }}</pre>
              </div>
            </div>

            <div v-else class="mt-5 rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-6 text-center text-sm text-slate-400">
              Choose a memory item to view the full record.
            </div>
          </div>

          <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl shadow-black/10">
            <div class="flex items-center justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold text-white">Memory viewer</h2>
                <p class="text-sm text-slate-400">Component-driven inspection pattern</p>
              </div>
            </div>
            <div class="mt-5 rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
              <MemoryViewer />
            </div>
          </div>
        </aside>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import MemoryViewer from '../Components/MemoryViewer.vue'

const memories = ref([])
const stats = ref({})
const selectedMemory = ref(null)
const loading = ref(false)
const error = ref('')
const query = ref('')
const activeMode = ref('browse')
const page = ref(1)
const perPage = ref(25)
const pagination = ref({
  page: 1,
  lastPage: 1,
  total: null,
  hasNext: false,
  hasPrev: false,
})

const modeChips = computed(() => [
  { label: 'Browse', value: 'browse' },
  { label: 'Search', value: 'search' },
])

const statsCards = computed(() => {
  const total = readNumber(stats.value, ['total', 'count', 'memories', 'entries'], memories.value.length)
  const searchable = readNumber(stats.value, ['searchable', 'indexed', 'available'], total)
  const recent = readNumber(stats.value, ['recent', 'recent_count', 'fresh'], 0)

  return [
    {
      label: 'Stored memories',
      value: formatCount(total),
      helper: 'Persisted records available from the API',
    },
    {
      label: 'Searchable',
      value: formatCount(searchable),
      helper: 'Indexed or matched memories',
    },
    {
      label: 'Recent',
      value: formatCount(recent),
      helper: 'Recently updated items',
    },
  ]
})

const selectedMemoryKey = computed(() => memoryKey(selectedMemory.value))

watch([page, perPage], () => {
  if (activeMode.value === 'browse') {
    loadMemories()
  }
})

watch(query, () => {
  if (!query.value) {
    activeMode.value = 'browse'
    page.value = 1
    loadMemories()
  }
})

onMounted(() => {
  refreshAll()
})

async function refreshAll() {
  await Promise.all([loadStats(), activeMode.value === 'search' && query.value ? executeSearch() : loadMemories()])
}

async function loadStats() {
  try {
    const response = await fetch('/api/v1/memories')
    const payload = await safeJson(response)
    stats.value = payload?.meta ?? payload?.stats ?? payload ?? {}
  } catch (err) {
    console.warn('Unable to load memory stats', err)
  }
}

async function loadMemories() {
  activeMode.value = 'browse'
  loading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams()
    params.set('page', String(page.value))
    params.set('per_page', String(perPage.value))

    const response = await fetch(`/api/v1/memories?${params.toString()}`)
    const payload = await safeJson(response)
    const records = normalizeCollection(payload, ['data', 'memories', 'items', 'results', 'entries'])

    memories.value = records
    selectedMemory.value = records[0] ?? null
    pagination.value = normalizePagination(payload, page.value, perPage.value, records.length)
  } catch (err) {
    error.value = 'Failed to load memories'
    memories.value = []
    selectedMemory.value = null
    console.error(err)
  } finally {
    loading.value = false
  }
}

async function executeSearch() {
  if (!query.value) {
    activeMode.value = 'browse'
    await loadMemories()
    return
  }

  activeMode.value = 'search'
  loading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams()
    params.set('query', query.value)
    params.set('search', query.value)
    params.set('q', query.value)
    params.set('page', String(page.value))
    params.set('per_page', String(perPage.value))

    const response = await fetch(`/api/v1/memories/search?${params.toString()}`)
    const payload = await safeJson(response)
    const records = normalizeCollection(payload, ['data', 'memories', 'items', 'results', 'entries'])

    memories.value = records
    selectedMemory.value = records[0] ?? null
    pagination.value = normalizePagination(payload, page.value, perPage.value, records.length)
  } catch (err) {
    error.value = 'Failed to search memories'
    memories.value = []
    selectedMemory.value = null
    console.error(err)
  } finally {
    loading.value = false
  }
}

function setMode(mode) {
  activeMode.value = mode
  if (mode === 'browse') {
    query.value = ''
    page.value = 1
    loadMemories()
  } else if (query.value) {
    executeSearch()
  }
}

function goToPage(nextPage) {
  page.value = Math.max(1, nextPage)
  if (activeMode.value === 'search' && query.value) {
    executeSearch()
  } else {
    loadMemories()
  }
}

function memoryKey(memory) {
  if (!memory) return null
  return memory.id ?? memory.uuid ?? memory.memory_id ?? memory.key ?? null
}

function memoryType(memory) {
  return memory?.type ?? memory?.category ?? memory?.kind ?? memory?.memory_type ?? 'memory'
}

function memoryScope(memory) {
  return memory?.scope ?? memory?.context ?? memory?.source ?? memory?.namespace ?? ''
}

function memoryTitle(memory) {
  return memory?.title ?? memory?.name ?? memory?.subject ?? memory?.key ?? memory?.summary ?? 'Untitled memory'
}

function memorySummary(memory) {
  return memory?.summary ?? memory?.content ?? memory?.body ?? memory?.description ?? memory?.text ?? JSON.stringify(memory)
}

function memoryTimestamp(memory) {
  const raw = memory?.updated_at ?? memory?.created_at ?? memory?.timestamp ?? memory?.createdAt
  if (!raw) return 'Unknown'
  const date = new Date(raw)
  if (Number.isNaN(date.getTime())) return String(raw)
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(date)
}

function formatCount(value) {
  const number = Number(value)
  if (Number.isNaN(number)) return '0'
  return new Intl.NumberFormat().format(number)
}

function prettyJson(value) {
  return JSON.stringify(value ?? {}, null, 2)
}

function readNumber(source, keys, fallback = 0) {
  for (const key of keys) {
    const value = source?.[key]
    if (typeof value === 'number') return value
    if (typeof value === 'string' && value.trim() !== '' && !Number.isNaN(Number(value))) {
      return Number(value)
    }
  }
  return fallback
}

function normalizeCollection(payload, candidates) {
  if (Array.isArray(payload)) return payload
  for (const key of candidates) {
    if (Array.isArray(payload?.[key])) return payload[key]
  }
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

function normalizePagination(payload, currentPage, currentPerPage, itemCount) {
  const meta = payload?.meta ?? payload?.pagination ?? payload
  const pageValue = Number(meta?.current_page ?? meta?.page ?? currentPage) || currentPage
  const lastPage = Number(meta?.last_page ?? meta?.total_pages ?? meta?.pages ?? pageValue) || pageValue
  const total = meta?.total ?? meta?.count ?? null

  return {
    page: pageValue,
    lastPage,
    total: total !== null ? Number(total) : null,
    hasNext:
      typeof meta?.next_page_url === 'string'
        ? Boolean(meta.next_page_url)
        : pageValue < lastPage || itemCount === currentPerPage,
    hasPrev: pageValue > 1,
  }
}

async function safeJson(response) {
  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`)
  }

  const text = await response.text()
  if (!text) return {}
  try {
    return JSON.parse(text)
  } catch {
    return { data: text }
  }
}
</script>
