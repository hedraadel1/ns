<template>
  <section class="rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl shadow-black/20">
    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-400">Live stream</p>
          <h2 class="mt-2 text-2xl font-bold text-white">Operational log feed</h2>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
            Monitor incoming events, narrow the feed by severity or category, and inspect JSON context in-place.
          </p>
        </div>

        <div class="flex flex-wrap gap-3">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/20"
            @click="refreshStream"
          >
            <span aria-hidden="true">↻</span>
            Refresh
          </button>

          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-emerald-500/40 hover:text-emerald-200"
            @click="togglePause"
          >
            <span aria-hidden="true">{{ isPaused ? '▶' : '❚❚' }}</span>
            {{ isPaused ? 'Resume' : 'Pause' }}
          </button>
        </div>
      </div>

      <div class="mt-5 grid gap-3 md:grid-cols-3">
        <div
          v-for="card in statCards"
          :key="card.label"
          class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4"
        >
          <p class="text-xs uppercase tracking-[0.32em] text-slate-500">{{ card.label }}</p>
          <p class="mt-3 text-3xl font-semibold text-white">{{ card.value }}</p>
          <p class="mt-2 text-sm text-slate-400">{{ card.helper }}</p>
        </div>
      </div>
    </div>

    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
      <div class="grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_repeat(3,minmax(0,1fr))]">
        <label class="flex flex-col gap-2 text-sm text-slate-300">
          <span class="font-medium text-slate-200">Search</span>
          <input
            v-model.trim="searchText"
            type="search"
            placeholder="Search messages, sources, and context…"
            class="rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
            @keyup.enter="applyFilters"
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

      <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="chip in levelChips"
            :key="chip.value"
            type="button"
            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition"
            :class="chip.value === selectedLevel
              ? 'border-emerald-500/60 bg-emerald-500/15 text-emerald-300'
              : 'border-slate-700 bg-slate-950/60 text-slate-300 hover:border-emerald-500/40 hover:text-emerald-200'"
            @click="selectedLevel = chip.value"
          >
            {{ chip.label }}
          </button>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-400">
          <span>Last sync: <span class="font-medium text-slate-200">{{ lastSyncedLabel }}</span></span>
          <span class="rounded-full border border-slate-700 bg-slate-950/60 px-3 py-1">
            {{ logs.length }} visible
          </span>
          <span v-if="error" class="rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-1 text-rose-300">
            {{ error }}
          </span>
          <span v-else-if="loading" class="rounded-full border border-slate-700 bg-slate-950/60 px-3 py-1">
            Loading…
          </span>
        </div>
      </div>
    </div>

    <div ref="logContainer" class="max-h-[760px] overflow-auto">
      <div v-if="loading && logs.length === 0" class="p-8">
        <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-8 text-center text-slate-400">
          Connecting to log stream…
        </div>
      </div>

      <div v-else-if="logs.length === 0" class="p-8">
        <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-8 text-center">
          <p class="text-lg font-medium text-slate-200">No logs available</p>
          <p class="mt-2 text-sm text-slate-400">
            Try adjusting the filters or refresh the stream.
          </p>
        </div>
      </div>

      <div v-else class="divide-y divide-slate-800">
        <article
          v-for="log in logs"
          :key="logKey(log)"
          class="px-5 py-4 transition hover:bg-slate-950/50 sm:px-6"
        >
          <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0 space-y-3">
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.24em]" :class="levelBadgeClass(log.level)">
                  {{ displayLevel(log.level) }}
                </span>
                <span class="rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-300">
                  {{ displayCategory(log.category) }}
                </span>
                <span v-if="displaySource(log.source)" class="text-xs text-slate-500">
                  {{ displaySource(log.source) }}
                </span>
              </div>

              <p class="text-sm font-medium text-white">
                {{ displayMessage(log) }}
              </p>

              <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                <span>{{ displayTimestamp(log) }}</span>
                <span v-if="log.user_id ?? log.actor_id ?? log.actor" class="text-slate-400">
                  Actor: {{ log.user_id ?? log.actor_id ?? log.actor }}
                </span>
                <span v-if="contextKeyCount(log)">{{ contextKeyCount(log) }} context keys</span>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button
                type="button"
                class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-emerald-500/40 hover:text-emerald-200"
                @click="toggleExpanded(log)"
              >
                {{ isExpanded(log) ? 'Hide context' : 'Inspect' }}
              </button>
            </div>
          </div>

          <div v-if="isExpanded(log)" class="mt-4 rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
            <div class="grid gap-3 md:grid-cols-2">
              <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Message</p>
                <p class="mt-2 text-sm leading-6 text-slate-100">{{ displayMessage(log) }}</p>
              </div>
              <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Timestamp</p>
                <p class="mt-2 text-sm text-slate-100">{{ displayTimestamp(log) }}</p>
              </div>
            </div>

            <div class="mt-4">
              <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Context</p>
              <pre class="mt-2 max-h-[320px] overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-300">{{ prettyJson(log.context ?? log.meta ?? log.data ?? log) }}</pre>
            </div>
          </div>
        </article>
      </div>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <p class="text-sm text-slate-400">
        Page {{ pagination.page }} of {{ pagination.lastPage }}
        <span v-if="pagination.total !== null">· {{ formatNumber(pagination.total) }} total</span>
      </p>

      <div class="flex flex-wrap items-center gap-2">
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
        <button
          type="button"
          class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200 transition hover:border-emerald-500/50 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="loading || !pagination.hasNext"
          @click="loadMore"
        >
          Load more
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const logs = ref([])
const levels = ref([])
const categories = ref([])
const stats = ref({})
const searchText = ref('')
const selectedLevel = ref('')
const selectedCategory = ref('')
const perPage = ref(25)
const loading = ref(false)
const isPaused = ref(false)
const error = ref('')
const lastSyncedAt = ref(null)
const expandedLogs = ref(new Set())

const pagination = ref({
  page: 1,
  lastPage: 1,
  total: null,
  hasNext: false,
  hasPrev: false,
})

let pollTimer = null

const levelChips = computed(() => [
  { label: 'All', value: '' },
  ...levels.value,
])

const statCards = computed(() => [
  {
    label: 'Total',
    value: formatNumber(readNumber(stats.value, ['total', 'count', 'logs', 'entries'], logs.value.length)),
    helper: 'Records reported by the API',
  },
  {
    label: 'Today',
    value: formatNumber(readNumber(stats.value, ['today', 'today_count', 'recent', 'today_total'], 0)),
    helper: 'Events created in the current day',
  },
  {
    label: 'Errors today',
    value: formatNumber(readNumber(stats.value, ['errors_today', 'error_today', 'today_errors'], 0)),
    helper: 'High severity events needing review',
  },
])

const lastSyncedLabel = computed(() => {
  if (!lastSyncedAt.value) return 'Never'
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'medium',
  }).format(lastSyncedAt.value)
})

onMounted(async () => {
  await refreshStream()
  startPolling()
})

onBeforeUnmount(() => {
  stopPolling()
})

watch([selectedLevel, selectedCategory, perPage], () => {
  loadLogs(1)
})

function startPolling() {
  stopPolling()
  pollTimer = window.setInterval(() => {
    if (!isPaused.value) {
      refreshStream(false)
    }
  }, 5000)
}

function stopPolling() {
  if (pollTimer) {
    window.clearInterval(pollTimer)
    pollTimer = null
  }
}

async function refreshStream(reloadLogs = true) {
  await Promise.all([
    loadStats(),
    loadLevels(),
    loadCategories(),
    reloadLogs ? loadLogs(1) : Promise.resolve(),
  ])
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

async function loadLogs(page = 1, append = false) {
  loading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams()
    params.set('page', String(page))
    params.set('per_page', String(perPage.value))

    if (searchText.value) {
      params.set('search', searchText.value)
      params.set('q', searchText.value)
    }

    if (selectedLevel.value) {
      params.set('level', selectedLevel.value)
    }

    if (selectedCategory.value) {
      params.set('category', selectedCategory.value)
    }

    const response = await fetch(`/api/v1/logs?${params.toString()}`)
    const payload = await safeJson(response)
    const records = normalizeCollection(payload, ['data', 'logs', 'items', 'results', 'entries'])

    logs.value = append ? [...logs.value, ...records] : records
    pagination.value = normalizePagination(payload, page, perPage.value, records.length)
    lastSyncedAt.value = new Date()
    expandedLogs.value = new Set()
  } catch (err) {
    console.error('Failed to load logs', err)
    error.value = 'Failed to load logs'
    if (!append) {
      logs.value = []
    }
  } finally {
    loading.value = false
  }
}

async function applyFilters() {
  await loadLogs(1)
}

async function goToPage(nextPage) {
  const safePage = Math.max(1, nextPage)
  await loadLogs(safePage)
}

async function loadMore() {
  if (!pagination.value.hasNext) return
  await loadLogs(pagination.value.page + 1, true)
}

function refreshStreamOnly() {
  loadLogs(pagination.value.page)
}

function togglePause() {
  isPaused.value = !isPaused.value
}

function toggleExpanded(log) {
  const key = logKey(log)
  const next = new Set(expandedLogs.value)
  if (next.has(key)) {
    next.delete(key)
  } else {
    next.add(key)
  }
  expandedLogs.value = next
}

function isExpanded(log) {
  return expandedLogs.value.has(logKey(log))
}

function logKey(log) {
  return log?.id ?? log?.uuid ?? log?.log_id ?? log?.created_at ?? log?.timestamp ?? log?.message ?? Math.random().toString(36)
}

function displayMessage(log) {
  return log?.message ?? log?.summary ?? log?.text ?? log?.description ?? JSON.stringify(log)
}

function displayLevel(level) {
  return String(level || 'unknown').toUpperCase()
}

function displayCategory(category) {
  return String(category || 'uncategorized')
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

function formatNumber(value) {
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

  if (Array.isArray(payload?.data?.data)) return payload.data.data
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