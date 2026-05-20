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

              <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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

                <div class="flex flex-col gap-3 rounded-2xl border border-slate-800 bg-slate-950/80 p-4">
                  <div class="flex items-center justify-between gap-3 text-sm text-slate-400">
                    <span>Decay filter</span>
                    <span>{{ Math.round(decayFilter * 100) }}% minimum opacity</span>
                  </div>
                  <input
                    v-model.number="decayFilter"
                    type="range"
                    min="0.3"
                    max="1"
                    step="0.05"
                    class="h-2 w-full cursor-pointer appearance-none rounded-full bg-slate-800 accent-emerald-400"
                  />
                </div>
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

          <NxPullRefresh @refresh="refreshAll">
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
                  <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <p class="text-sm text-slate-400">
                      Page {{ pagination.page }} of {{ pagination.lastPage }}
                    </p>
                    <button
                      type="button"
                      class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-200 transition hover:bg-white/10"
                      @click="openMemoryActionsSheet"
                    >
                      More actions
                    </button>
                  </div>
                </div>
                <div v-if="multiSelectMode.value" class="mt-4 rounded-2xl border border-emerald-500/20 bg-slate-950/80 p-3 text-sm text-emerald-200">
                <div class="flex flex-wrap items-center justify-between gap-3">
                  <p><span class="text-white font-semibold">{{ selectedCount }}</span> selected</p>
                  <div class="flex flex-wrap gap-2">
                    <button
                      type="button"
                      class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200 transition hover:bg-emerald-500/20"
                      @click="toggleSelectAllMemories"
                    >
                      {{ allVisibleSelected ? 'Deselect all' : 'Select all' }}
                    </button>
                    <button
                      type="button"
                      class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-300 transition hover:bg-white/10"
                      @click="clearBulkMode"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
                <p class="mt-2 text-xs text-slate-400">Use long-press to enter multi-select mode and choose multiple memories.</p>
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
                  v-for="memory in filteredMemories"
                  :key="memoryKey(memory)"
                  type="button"
                  class="w-full px-5 py-4 text-left transition duration-300 hover:bg-slate-950/60"
                  :class="[
                    multiSelectMode.value && selectedMemoryKeys.value.includes(memoryKey(memory)) ? 'bg-emerald-500/10' : '',
                    !multiSelectMode.value && memoryKey(memory) === selectedMemoryKey ? 'bg-emerald-500/10' : '',
                  ]"
                  :style="{ opacity: memoryOpacity(memory) }"
                  @pointerdown.prevent="beginMemoryPress(memory)"
                  @pointerup.prevent="endMemoryPress(memory)"
                  @pointerleave="cancelMemoryPress"
                  @pointercancel="cancelMemoryPress"
                  @contextmenu.prevent="openMemoryContextMenu(memory, $event)"
                  @keydown.enter.prevent="endMemoryPress(memory)"
                >
                  <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex gap-3">
                      <div v-if="multiSelectMode.value" class="flex items-center">
                        <label class="relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded-sm border border-white/20 bg-slate-950 transition focus-within:ring-2 focus-within:ring-emerald-400/40">
                          <input
                            type="checkbox"
                            class="peer absolute h-full w-full opacity-0"
                            :checked="selectedMemoryKeys.value.includes(memoryKey(memory))"
                            @change.stop.prevent="toggleMemorySelection(memory)"
                          />
                          <span
                            class="pointer-events-none h-3.5 w-3.5 rounded-sm border border-white/20 bg-transparent transition peer-checked:bg-emerald-400 peer-checked:border-emerald-400"
                          />
                        </label>
                      </div>

                      <div class="min-w-0 space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                          <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-300">
                            {{ memoryType(memory) }}
                          </span>
                          <span v-if="memoryScope(memory)" class="rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-300">
                            {{ memoryScope(memory) }}
                          </span>
                          <span
                            v-if="memoryConfidence(memory) !== null"
                            :class="['rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.24em]', memoryConfidenceClass(memory)]"
                          >
                            {{ memoryConfidenceLabel(memory) }}
                          </span>
                        </div>

                        <p class="truncate text-sm font-medium text-white">
                          {{ memoryTitle(memory) }}
                        </p>
                        <p class="line-clamp-2 text-sm leading-6 text-slate-400">
                          {{ memorySummary(memory) }}
                        </p>
                      </div>
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
        </NxPullRefresh>

        <NxBottomSheet :open="showSheet" title="Memory actions" subtitle="Quick hub tools" @close="showSheet = false">
          <div class="space-y-3">
            <button
              type="button"
              class="w-full rounded-2xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-left text-sm text-slate-100 transition hover:border-emerald-500/40 hover:bg-slate-900"
              @click="refreshAll"
            >
              Refresh memory list
            </button>
            <button
              type="button"
              class="w-full rounded-2xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-left text-sm text-slate-100 transition hover:border-emerald-500/40 hover:bg-slate-900"
              @click="query = ''"
            >
              Clear search filters
            </button>
            <button
              type="button"
              class="w-full rounded-2xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-left text-sm text-slate-100 transition hover:border-emerald-500/40 hover:bg-slate-900"
              @click="showSheet = false"
            >
              Close actions
            </button>
          </div>
        </NxBottomSheet>

        <NxContextMenu
          :visible="contextMenuVisible"
          :x="contextMenuX"
          :y="contextMenuY"
          :items="memoryActions"
          @select="handleMemoryContextMenu"
          @close="closeMemoryContextMenu"
        />

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
      </div>
      </section>

      <div
        v-if="multiSelectMode.value"
        class="fixed bottom-6 left-1/2 z-50 w-[min(96vw,1024px)] -translate-x-1/2 rounded-3xl border border-white/10 bg-slate-950/95 p-4 shadow-2xl shadow-black/40 backdrop-blur-xl"
      >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <p class="text-sm text-slate-200">
            <span class="text-white font-semibold">{{ selectedCount }}</span> memories selected
          </p>
          <div class="flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="rounded-full border border-sky-500/30 bg-sky-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-sky-200 transition hover:bg-sky-500/20"
              @click="bulkExportMemories"
              :disabled="bulkActionLoading"
            >
              Export
            </button>
            <button
              type="button"
              class="rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-rose-200 transition hover:bg-rose-500/20"
              @click="bulkDeleteMemories"
              :disabled="bulkActionLoading"
            >
              Delete
            </button>
            <button
              type="button"
              class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-300 transition hover:bg-white/10"
              @click="clearBulkMode"
              :disabled="bulkActionLoading"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, onUnmounted, ref, watch } from 'vue'
import MemoryViewer from '../Components/MemoryViewer.vue'
import NxPullRefresh from '../Components/NxPullRefresh.vue'
import NxBottomSheet from '../Components/NxBottomSheet.vue'
import NxContextMenu from '../Components/NxContextMenu.vue'

const memories = ref([])
const stats = ref({})
const selectedMemory = ref(null)
const loading = ref(false)
const error = ref('')
const query = ref('')
const activeMode = ref('browse')
const page = ref(1)
const perPage = ref(25)
const decayFilter = ref(0.3)
const multiSelectMode = ref(false)
const selectedMemoryKeys = ref([])
const bulkActionLoading = ref(false)
const bulkActionError = ref('')
const showSheet = ref(false)
const contextMenuVisible = ref(false)
const contextMenuX = ref(0)
const contextMenuY = ref(0)
const contextMenuTarget = ref(null)
const longPressSuppressed = ref(false)
const pressTimer = ref(null)
const echoChannel = ref(null)
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
const selectedCount = computed(() => selectedMemoryKeys.value.length)
const visibleMemoryKeys = computed(() => filteredMemories.value.map((memory) => memoryKey(memory)))
const allVisibleSelected = computed(() =>
  visibleMemoryKeys.value.length > 0 && visibleMemoryKeys.value.every((key) => selectedMemoryKeys.value.includes(key))
)

function openMemoryActionsSheet() {
  showSheet.value = true
}

function openMemoryContextMenu(memory, event) {
  contextMenuTarget.value = memory
  contextMenuX.value = event.clientX
  contextMenuY.value = event.clientY
  contextMenuVisible.value = true
}

function closeMemoryContextMenu() {
  contextMenuVisible.value = false
  contextMenuTarget.value = null
}

function handleMemoryContextMenu(action) {
  const memory = contextMenuTarget.value
  if (!memory) {
    closeMemoryContextMenu()
    return
  }

  switch (action.value) {
    case 'inspect':
      selectedMemory.value = memory
      break
    case 'export':
      const blob = new Blob([JSON.stringify(memory, null, 2)], { type: 'application/json' })
      const link = document.createElement('a')
      link.href = URL.createObjectURL(blob)
      link.download = `memory-${memoryKey(memory)}.json`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      break
    case 'delete':
      memories.value = memories.value.filter((item) => memoryKey(item) !== memoryKey(memory))
      if (selectedMemory.value && memoryKey(selectedMemory.value) === memoryKey(memory)) {
        selectedMemory.value = null
      }
      break
    default:
      break
  }

  closeMemoryContextMenu()
}

const memoryActions = computed(() => [
  { value: 'inspect', label: 'Inspect memory' },
  { value: 'export', label: 'Export as JSON' },
  { value: 'delete', label: 'Remove memory' },
])

const filteredMemories = computed(() => {
  const term = query.value.trim().toLowerCase()
  return memories.value
    .filter((memory) => {
      const matchesQuery = !term || [
        memoryTitle(memory),
        memorySummary(memory),
        memoryType(memory),
        memoryScope(memory),
      ]
        .join(' ')
        .toLowerCase()
        .includes(term)

      return matchesQuery && memoryOpacity(memory) >= decayFilter.value
    })
    .sort((a, b) => (memoryTimestamp(a) < memoryTimestamp(b) ? 1 : -1))
})

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

const handleFabAction = (event) => {
  const action = event.detail
  switch (action.value) {
    case 'refresh':
      refreshAll()
      break
    case 'browse':
      query.value = ''
      activeMode.value = 'browse'
      page.value = 1
      loadMemories()
      break
    case 'filters':
      openMemoryActionsSheet()
      break
    default:
      break
  }
}

onMounted(() => {
  refreshAll()
  setupMemoryEchoListeners()
  window.addEventListener('nx-fab-action', handleFabAction)
})

onBeforeUnmount(() => {
  window.removeEventListener('nx-fab-action', handleFabAction)
})

onUnmounted(() => {
  if (typeof window !== 'undefined' && window.Echo && echoChannel.value) {
    echoChannel.value.stopListening('*')
    window.Echo.leaveChannel('memories')
  }
})

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

function memoryTimestampRaw(memory) {
  const raw = memory?.updated_at ?? memory?.created_at ?? memory?.timestamp ?? memory?.createdAt
  return raw ? new Date(raw) : null
}

function memoryDecayWeight(memory) {
  const value = memory?.decay_weight ?? memory?.decayWeight ?? memory?.decay ?? memory?.decayFactor
  const normalized = Number(value)
  if (Number.isNaN(normalized)) return 0
  return Math.min(Math.max(normalized, 0), 1)
}

function memoryOpacity(memory) {
  const decayWeight = memoryDecayWeight(memory)
  if (decayWeight > 0) {
    return Math.max(0.3, 1 - decayWeight * 0.7)
  }

  const timestamp = memoryTimestampRaw(memory)
  if (!timestamp) return 1

  const ageMinutes = (Date.now() - timestamp.getTime()) / 60000
  if (ageMinutes < 60 * 24) return 1
  if (ageMinutes < 60 * 24 * 3) return 0.92
  if (ageMinutes < 60 * 24 * 7) return 0.82
  if (ageMinutes < 60 * 24 * 14) return 0.72
  return 0.55
}

function memoryConfidence(memory) {
  const value = memory?.confidence ?? memory?.confidence_score ?? memory?.confidenceScore
  const confidence = Number(value)
  return Number.isNaN(confidence) ? null : Math.min(Math.max(confidence, 0), 1)
}

function memoryConfidenceLabel(memory) {
  const confidence = memoryConfidence(memory)
  if (confidence === null) return 'Unknown'
  if (confidence >= 0.8) return 'High'
  if (confidence >= 0.6) return 'Medium'
  return 'Low'
}

function memoryConfidenceClass(memory) {
  const confidence = memoryConfidence(memory)
  if (confidence === null) return 'border-slate-500 bg-slate-950/70 text-slate-300'
  if (confidence >= 0.8) return 'border-emerald-500 bg-emerald-500/10 text-emerald-200'
  if (confidence >= 0.6) return 'border-amber-500 bg-amber-500/10 text-amber-200'
  return 'border-rose-500 bg-rose-500/10 text-rose-200'
}

function normalizeMemory(memory) {
  return memory || {}
}

function handleMemoryEvent(event) {
  const memory = normalizeMemory(event.memory ?? event.data ?? event.payload ?? {})
  if (!memoryKey(memory)) return

  const existingIndex = memories.value.findIndex((item) => memoryKey(item) === memoryKey(memory))
  if (existingIndex >= 0) {
    memories.value.splice(existingIndex, 1, {
      ...memories.value[existingIndex],
      ...memory,
    })
    if (selectedMemory.value && memoryKey(selectedMemory.value) === memoryKey(memory)) {
      selectedMemory.value = { ...selectedMemory.value, ...memory }
    }
  } else {
    memories.value.unshift(memory)
  }
}

function beginMemoryPress(memory) {
  if (pressTimer.value) {
    clearTimeout(pressTimer.value)
  }
  longPressSuppressed.value = false
  pressTimer.value = window.setTimeout(() => {
    longPressSuppressed.value = true
    if (!multiSelectMode.value) {
      multiSelectMode.value = true
      selectedMemoryKeys.value = [memoryKey(memory)]
    }
    pressTimer.value = null
  }, 500)
}

function endMemoryPress(memory) {
  if (pressTimer.value) {
    clearTimeout(pressTimer.value)
    pressTimer.value = null
  }
  if (longPressSuppressed.value) {
    longPressSuppressed.value = false
    return
  }
  if (multiSelectMode.value) {
    toggleMemorySelection(memory)
    return
  }
  selectedMemory.value = memory
}

function cancelMemoryPress() {
  if (pressTimer.value) {
    clearTimeout(pressTimer.value)
    pressTimer.value = null
  }
}

function toggleMemorySelection(memory) {
  const key = memoryKey(memory)
  if (!key) return
  const index = selectedMemoryKeys.value.indexOf(key)
  if (index > -1) {
    selectedMemoryKeys.value.splice(index, 1)
  } else {
    selectedMemoryKeys.value.push(key)
  }
}

function clearBulkMode() {
  multiSelectMode.value = false
  selectedMemoryKeys.value = []
  bulkActionError.value = ''
}

function toggleSelectAllMemories() {
  if (allVisibleSelected.value) {
    selectedMemoryKeys.value = []
    return
  }
  selectedMemoryKeys.value = Array.from(new Set([...visibleMemoryKeys.value]))
}

async function bulkDeleteMemories() {
  if (!selectedMemoryKeys.value.length) return
  bulkActionLoading.value = true
  bulkActionError.value = ''

  try {
    await Promise.all(
      selectedMemoryKeys.value.map((key) =>
        fetch(`/api/v1/memories/${encodeURIComponent(key)}`, {
          method: 'DELETE',
          headers: { Accept: 'application/json' },
        })
      )
    )
    memories.value = memories.value.filter((memory) => !selectedMemoryKeys.value.includes(memoryKey(memory)))
    if (selectedMemory.value && selectedMemoryKeys.value.includes(selectedMemoryKey.value)) {
      selectedMemory.value = null
    }
    clearBulkMode()
  } catch (error) {
    bulkActionError.value = error instanceof Error ? error.message : 'Failed to delete selected memories.'
  } finally {
    bulkActionLoading.value = false
  }
}

function bulkExportMemories() {
  if (!selectedMemoryKeys.value.length) return
  const selected = memories.value.filter((memory) => selectedMemoryKeys.value.includes(memoryKey(memory)))
  const blob = new Blob([JSON.stringify(selected, null, 2)], { type: 'application/json' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `memories-export-${new Date().toISOString().slice(0,10)}.json`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function setupMemoryEchoListeners() {
  if (typeof window === 'undefined' || !window.Echo) return

  echoChannel.value = window.Echo.private('memories')
  echoChannel.value.listen('MemoriesExtracted', handleMemoryEvent)
  echoChannel.value.listen('MemoryIndexed', handleMemoryEvent)
  echoChannel.value.listen('MemoryVectorized', handleMemoryEvent)
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
