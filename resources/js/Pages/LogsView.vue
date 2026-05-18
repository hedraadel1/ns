<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
      <section class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-2xl shadow-black/20">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-400">Logs hub</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Operational log stream</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
              Review system events, filter by severity or category, and inspect the most recent log details.
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
            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-slate-300">
              <input v-model="autoRefresh" type="checkbox" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500" />
              Auto refresh
            </label>
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

      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.9fr)]">
        <div class="space-y-6">
          <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl shadow-black/10">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="flex flex-col gap-2 text-sm text-slate-300">
                <span class="font-medium text-slate-200">Search</span>
                <input
                  v-model.trim="query"
                  type="search"
                  placeholder="Search logs…"
                  class="rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                  @keyup.enter="loadLogs"
                />
              </label>

              <label class="flex flex-col gap-2 text-sm text-slate-300">
                <span class="font-medium text-slate-200">Level</span>
                <select
                  v-model="selectedLevel"
                  class="rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-slate-100 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                >
                  <option value="">All levels</option>
                  <option v-for="level in levels" :key="level.value" :value="level.value">
                    {{ level.label }}
                  </option>
                </select>
              </label>

              <label class="flex flex-col gap-2 text-sm text-slate-300">
                <span class="font-medium text-slate-200">Category</span>
                <select
                  v-model="selectedCategory"
                  class="rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-slate-100 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                >
                  <option value="">All categories</option>
                  <option v-for="category in categories" :key="category.value" :value="category.value">
                    {{ category.label }}
                  </option>
                </select>
              </label>

              <label class="flex flex-col gap-2 text-sm text-slate-300">
                <span class="font-medium text-slate-200">Per page</span>
                <select
                  v-model.number="perPage"
                  class="rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-slate-100 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                >
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
              </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="level in quickLevelChips"
                  :key="level.value"
                  type="button"
                  class="rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                  :class="level.value === selectedLevel
                    ? 'border-emerald-500/60 bg-emerald-500/15 text-emerald-300'
                    : 'border-slate-700 bg-slate-950/60 text-slate-300 hover:border-emerald-500/40 hover:text-emerald-200'"
                  @click="selectedLevel = level.value"
                >
                  {{ level.label }}
                </button>
              </div>

              <div class="flex items-center gap-2 text-sm text-slate-400">
                <span>Last sync:</span>
                <span class="font-medium text-slate-200">{{ lastSyncedLabel }}</span>
              </div>
            </div>
          </div>

          <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl shadow-black/10">
            <div class="border-b border-slate-800 px-5 py-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <h2 class="text-lg font-semibold text-white">Log entries</h2>
                  <p class="text-sm text-slate-400">
                    {{ logs.length }} entries loaded
                    <span v-if="pagination.total !== null">· {{ pagination.total }} total</span>
                  </p>
                </div>
                <div class="text-sm text-slate-400">
                  <span v-if="loading">Loading…</span>
                  <span v-else-if="error" class="text-rose-300">{{ error }}</span>
                  <span v-else>Ready</span>
                </div>
              </div>
            </div>

            <div v-if="loading && !logs.length" class="p-8">
              <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-8 text-center text-slate-400">
                Loading logs…
              </div>
            </div>

            <div v-else-if="!logs.length" class="p-8">
              <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-8 text-center">
                <p class="text-lg font-medium text-slate-200">No logs found</p>
                <p class="mt-2 text-sm text-slate-400">
                  Try adjusting the filters or refresh the stream.
                </p>
              </div>
            </div>

            <div v-else class="divide-y divide-slate-800">
              <button
                v-for="log in logs"
                :key="log.id ?? log.uuid ?? `${log.timestamp ?? log.created_at ?? log.message}-${log.level ?? 'unknown'}`"
                type="button"
                class="w-full px-5 py-4 text-left transition hover:bg-slate-950/60"
                :class="selectedLogId(log) === selectedLogKey ? 'bg-emerald-500/10' : ''"
                @click="selectedLog = log"
              >
                <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                  <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                      <span
                        class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.24em]"
                        :class="levelBadgeClass(log.level)"
                      >
                        {{ displayLevel(log.level) }}
                      </span>
                      <span class="rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-300">
                        {{ displayCategory(log.category) }}
                      </span>
                      <span v-if="displaySource(log.source)" class="text-xs text-slate-500">{{ displaySource(log.source) }}</span>
                    </div>

                    <p class="truncate text-sm font-medium text-white">
                      {{ displayMessage(log) }}
                    </p>

                    <p class="text-xs text-slate-400">
                      {{ displayTimestamp(log) }}
                    </p>
                  </div>

                  <div class="flex items-center gap-4">
                    <div class="text-right">
                      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Context keys</p>
                      <p class="mt-1 text-sm font-semibold text-slate-200">{{ contextKeyCount(log) }}</p>
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
                Page {{ pagination.page }} of {{ pagination.lastPage }}
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
                <h2 class="text-lg font-semibold text-white">Selected log</h2>
                <p class="text-sm text-slate-400">Detailed payload and context</p>
              </div>
              <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                Live
              </span>
            </div>

            <div v-if="selectedLog" class="mt-5 space-y-4">
              <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Message</p>
                <p class="mt-2 text-sm leading-6 text-slate-100">{{ displayMessage(selectedLog) }}</p>
              </div>

              <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                  <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Level</p>
                  <p class="mt-2 text-sm font-semibold text-slate-100">{{ displayLevel(selectedLog.level) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                  <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Category</p>
                  <p class="mt-2 text-sm font-semibold text-slate-100">{{ displayCategory(selectedLog.category) }}</p>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Timestamp</p>
                <p class="mt-2 text-sm font-semibold text-slate-100">{{ displayTimestamp(selectedLog) }}</p>
              </div>

              <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Raw payload</p>
                <pre class="mt-3 max-h-[360px] overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-300">{{ prettyJson(selectedLog) }}</pre>
              </div>
            </div>

            <div v-else class="mt-5 rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-6 text-center text-sm text-slate-400">
              Select a log entry to inspect its details.
            </div>
          </div>

          <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl shadow-black/10">
            <div class="flex items-center justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold text-white">Live stream</h2>
                <p class="text-sm text-slate-400">Component-driven feed</p>
              </div>
            </div>
            <div class="mt-5 rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
              <LogStream />
            </div>
          </div>
        </aside>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import LogStream from '../Components/LogStream.vue'

const logs = ref([])
const stats = ref({})
const levels = ref([])
const categories = ref([])
const selectedLog = ref(null)
const loading = ref(false)
const error = ref('')
const query = ref('')
const selectedLevel = ref('')
const selectedCategory = ref('')
const perPage = ref(25)
const page = ref(1)
const autoRefresh = ref(false)
const lastSyncedAt = ref(null)
let autoRefreshTimer = null

const pagination = ref({
  page: 1,
  lastPage: 1,
  total: null,
  hasNext: false,
  hasPrev: false,
})

const quickLevelChips = computed(() => [
  { label: 'All', value: '' },
  ...levels.value.map((level) => ({
    label: level.label,
    value: level.value,
  })),
])

const statsCards = computed(() => {
  const total = readNumber(stats.value, ['total', 'count', 'logs', 'entries'], logs.value.length)
  const errors = readNumber(stats.value, ['errors', 'error_count', 'critical', 'failed'], 0)
  const warnings = readNumber(stats.value, ['warnings', 'warning_count'], 0)

  return [
    {
      label: 'Total entries',
      value: formatCount(total),
      helper: 'All logs available from the API',
    },
    {
      label: 'Warnings',
      value: formatCount(warnings),
      helper: 'High attention events in the current window',
    },
    {
      label: 'Errors',
      value: formatCount(errors),
      helper: 'Failures and critical events',
    },
  ]
})

const selectedLogKey = computed(() => selectedLogId(selectedLog.value))

const lastSyncedLabel = computed(() => {
  if (!lastSyncedAt.value) return 'Never'
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'medium',
  }).format(lastSyncedAt.value)
})

watch([selectedLevel, selectedCategory, perPage], () => {
  page.value = 1
  loadLogs()
})

watch(query, () => {
  page.value = 1
})

watch(autoRefresh, (enabled) => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer)
    autoRefreshTimer = null
  }

  if (enabled) {
    autoRefreshTimer = setInterval(() => {
      refreshAll(false)
    }, 30000)
  }
})

onMounted(() => {
  refreshAll()
})

onBeforeUnmount(() => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer)
  }
})

async function refreshAll(reloadLogs = true) {
  await Promise.all([loadStats(), loadLevels(), loadCategories(), reloadLogs ? loadLogs() : Promise.resolve()])
}

async function loadStats() {
  try {
    const response = await fetch('/api/v1/logs/stats')
    stats.value = await safeJson(response)
  } catch (err) {
    console.warn('Unable to load log stats', err)
  }
}

async function loadLevels() {
  try {
    const response = await fetch('/api/v1/logs/levels')
    levels.value = normalizeOptionList(await safeJson(response), [
      'debug',
      'info',
      'notice',
      'warning',
      'error',
      'critical',
      'alert',
      'emergency',
    ])
  } catch (err) {
    console.warn('Unable to load log levels', err)
    levels.value = normalizeOptionList([], [
      'debug',
      'info',
      'notice',
      'warning',
      'error',
      'critical',
      'alert',
      'emergency',
    ])
  }
}

async function loadCategories() {
  try {
    const response = await fetch('/api/v1/logs/categories')
    categories.value = normalizeOptionList(await safeJson(response))
  } catch (err) {
    console.warn('Unable to load log categories', err)
    categories.value = []
  }
}

async function loadLogs() {
  loading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams()
    params.set('page', String(page.value))
    params.set('per_page', String(perPage.value))

    if (query.value) params.set('search', query.value)
    if (selectedLevel.value) params.set('level', selectedLevel.value)
    if (selectedCategory.value) params.set('category', selectedCategory.value)

    const response = await fetch(`/api/v1/logs?${params.toString()}`)
    const payload = await safeJson(response)
    const records = normalizeCollection(payload, ['data', 'logs', 'items', 'results', 'entries'])

    logs.value = records
    selectedLog.value = records[0] ?? null
    pagination.value = normalizePagination(payload, page.value, perPage.value, records.length)
    lastSyncedAt.value = new Date()
  } catch (err) {
    error.value = 'Failed to load logs'
    logs.value = []
    selectedLog.value = null
    console.error(err)
  } finally {
    loading.value = false
  }
}

function goToPage(nextPage) {
  page.value = Math.max(1, nextPage)
  loadLogs()
}

function selectedLogId(log) {
  if (!log) return null
  return log.id ?? log.uuid ?? log.log_id ?? log._id ?? null
}

function displayMessage(log) {
  if (!log) return '—'
  return log.message ?? log.summary ?? log.text ?? log.description ?? JSON.stringify(log)
}

function displayLevel(level) {
  if (!level) return 'UNKNOWN'
  return String(level).toUpperCase()
}

function displayCategory(category) {
  return category ? String(category) : 'uncategorized'
}

function displaySource(source) {
  if (!source) return ''
  return String(source)
}

function displayTimestamp(log) {
  const raw = log?.timestamp ?? log?.created_at ?? log?.createdAt ?? log?.time ?? log?.date
  if (!raw) return 'Unknown time'
  const date = new Date(raw)
  if (Number.isNaN(date.getTime())) return String(raw)
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'medium',
  }).format(date)
}

function contextKeyCount(log) {
  const context = log?.context ?? log?.meta ?? log?.data ?? {}
  if (!context || typeof context !== 'object') return 0
  return Object.keys(context).length
}

function levelBadgeClass(level) {
  const normalized = String(level || '').toLowerCase()

  if (['error', 'critical', 'alert', 'emergency', 'failed'].includes(normalized)) {
    return 'border-rose-500/30 bg-rose-500/10 text-rose-300'
  }

  if (['warning', 'warn'].includes(normalized)) {
    return 'border-amber-500/30 bg-amber-500/10 text-amber-300'
  }

  if (['info', 'notice'].includes(normalized)) {
    return 'border-sky-500/30 bg-sky-500/10 text-sky-300'
  }

  return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
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
  if (Array.isArray(payload?.data?.[0])) return payload.data[0]
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

function normalizeOptionList(payload, fallback = []) {
  const raw = Array.isArray(payload) ? payload : normalizeCollection(payload, ['data', 'items', 'results', 'entries'])
  if (!raw.length && fallback.length) {
    return fallback.map((value) => ({ label: formatOptionLabel(value), value }))
  }

  return raw
    .map((item) => {
      if (typeof item === 'string' || typeof item === 'number') {
        return { label: formatOptionLabel(item), value: String(item) }
      }

      const value = item.value ?? item.key ?? item.id ?? item.slug ?? item.name ?? item.label
      const label = item.label ?? item.name ?? item.title ?? item.value ?? item.key ?? value
      if (value === undefined || value === null) return null
      return { label: String(label), value: String(value) }
    })
    .filter(Boolean)
}

function formatOptionLabel(value) {
  return String(value)
    .replace(/[_-]+/g, ' ')
    .replace(/\b\w/g, (match) => match.toUpperCase())
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