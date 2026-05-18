<template>
  <section class="rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl shadow-black/20">
    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
      <div class="flex flex-col gap-3">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-400">Memory viewer</p>
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 class="text-2xl font-bold text-white">Structured memory detail</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
              Inspect the stored payload, metadata, and derived fields for the selected memory record.
            </p>
          </div>
          <span class="rounded-full border border-slate-700 bg-slate-950/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">
            {{ memory ? 'Selected' : 'No selection' }}
          </span>
        </div>
      </div>
    </div>

    <div v-if="memory" class="space-y-6 p-5 sm:p-6">
      <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="min-w-0 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-300">
                {{ memoryType }}
              </span>
              <span v-if="memoryScope" class="rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-300">
                {{ memoryScope }}
              </span>
              <span v-if="memorySource" class="text-xs text-slate-500">{{ memorySource }}</span>
            </div>

            <h3 class="truncate text-xl font-semibold text-white">
              {{ memoryTitle }}
            </h3>

            <p class="max-w-4xl text-sm leading-6 text-slate-300">
              {{ memorySummary }}
            </p>

            <div class="flex flex-wrap gap-2">
              <span
                v-for="chip in tags"
                :key="chip"
                class="rounded-full border border-slate-700 bg-slate-950/60 px-3 py-1 text-xs text-slate-300"
              >
                {{ chip }}
              </span>
            </div>
          </div>

          <div class="grid min-w-[240px] gap-3 sm:grid-cols-2 lg:grid-cols-1">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-4">
              <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Updated</p>
              <p class="mt-2 text-sm font-semibold text-slate-100">{{ displayTimestamp(memory.updated_at ?? memory.updatedAt ?? memory.timestamp ?? memory.created_at) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-4">
              <p class="text-xs uppercase tracking-[0.3em] text-slate-500">ID</p>
              <p class="mt-2 break-all text-sm font-semibold text-slate-100">{{ memoryId }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Type</p>
          <p class="mt-2 text-sm font-semibold text-slate-100">{{ memoryType }}</p>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Scope</p>
          <p class="mt-2 text-sm font-semibold text-slate-100">{{ memoryScope || '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Source</p>
          <p class="mt-2 text-sm font-semibold text-slate-100">{{ memorySource || '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Confidence</p>
          <p class="mt-2 text-sm font-semibold text-slate-100">{{ memoryConfidence }}</p>
        </div>
      </div>

      <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Highlights</p>
          <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div v-for="item in highlightFields" :key="item.label" class="rounded-xl border border-slate-800 bg-slate-900/70 p-3">
              <dt class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ item.label }}</dt>
              <dd class="mt-2 break-words text-sm font-medium text-slate-100">{{ item.value }}</dd>
            </div>
          </dl>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Metadata</p>
          <div class="mt-4 space-y-3">
            <div
              v-for="entry in metadataEntries"
              :key="entry.label"
              class="rounded-xl border border-slate-800 bg-slate-900/70 p-3"
            >
              <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ entry.label }}</p>
              <p class="mt-2 break-words text-sm leading-6 text-slate-200">{{ entry.value }}</p>
            </div>
            <div v-if="metadataEntries.length === 0" class="rounded-xl border border-dashed border-slate-700 bg-slate-950/60 p-4 text-sm text-slate-400">
              No metadata fields were detected on this record.
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Raw payload</p>
          <button
            type="button"
            class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-emerald-500/40 hover:text-emerald-200"
            @click="copyJson"
          >
            Copy JSON
          </button>
        </div>
        <pre class="mt-3 max-h-[420px] overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-300">{{ prettyJson(memory) }}</pre>
      </div>
    </div>

    <div v-else class="p-6">
      <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-8 text-center">
        <p class="text-lg font-medium text-slate-200">Select a memory to inspect</p>
        <p class="mt-2 text-sm text-slate-400">
          The viewer will render the selected record's content, metadata, and raw payload here.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  memory: {
    type: Object,
    default: null,
  },
})

const memoryId = computed(() => props.memory?.id ?? props.memory?.uuid ?? props.memory?.memory_id ?? '—')
const memoryType = computed(() => String(props.memory?.type ?? props.memory?.category ?? props.memory?.kind ?? props.memory?.memory_type ?? 'memory'))
const memoryScope = computed(() => stringifyField(props.memory?.scope ?? props.memory?.namespace ?? props.memory?.context ?? props.memory?.source ?? ''))
const memorySource = computed(() => stringifyField(props.memory?.source ?? props.memory?.origin ?? props.memory?.channel ?? ''))
const memoryTitle = computed(() => {
  const memory = props.memory ?? {}
  return (
    memory.title ??
    memory.name ??
    memory.subject ??
    memory.key ??
    memory.label ??
    memory.summary ??
    'Untitled memory'
  )
})

const memorySummary = computed(() => {
  const memory = props.memory ?? {}
  return (
    memory.summary ??
    memory.content ??
    memory.body ??
    memory.description ??
    memory.text ??
    memory.note ??
    'No summary available for this memory record.'
  )
})

const memoryConfidence = computed(() => {
  const memory = props.memory ?? {}
  const value = memory.confidence ?? memory.score ?? memory.relevance ?? memory.similarity
  if (value === undefined || value === null || value === '') return '—'
  return typeof value === 'number' ? value.toFixed(2) : String(value)
})

const tags = computed(() => {
  const memory = props.memory ?? {}
  const sources = [
    memory.tags,
    memory.labels,
    memory.keywords,
    memory.entities,
  ]

  const values = []
  for (const source of sources) {
    const parsed = normalizeList(source)
    for (const item of parsed) {
      if (item && !values.includes(item)) values.push(item)
    }
  }
  return values.slice(0, 12)
})

const highlightFields = computed(() => {
  const memory = props.memory ?? {}
  return [
    { label: 'Created', value: displayTimestamp(memory.created_at ?? memory.createdAt ?? memory.timestamp ?? memory.updated_at) },
    { label: 'Updated', value: displayTimestamp(memory.updated_at ?? memory.updatedAt ?? memory.timestamp ?? memory.created_at) },
    { label: 'Author', value: stringifyField(memory.author ?? memory.owner ?? memory.user ?? memory.actor ?? '—') },
    { label: 'Context', value: stringifyField(memory.context ?? memory.namespace ?? memory.scope ?? '—') },
  ]
})

const metadataEntries = computed(() => {
  const memory = props.memory ?? {}
  return flattenRecord([
    ['Description', memory.description],
    ['Details', memory.details],
    ['Reason', memory.reason],
    ['Source', memory.source],
    ['Origin', memory.origin],
    ['Notes', memory.notes],
    ['Metadata', memory.metadata],
    ['Attributes', memory.attributes],
  ])
})

function normalizeList(value) {
  if (!value) return []

  if (Array.isArray(value)) {
    return value.map((item) => stringifyField(item)).filter(Boolean)
  }

  if (typeof value === 'string') {
    return value
      .split(',')
      .map((part) => part.trim())
      .filter(Boolean)
  }

  if (typeof value === 'object') {
    return Object.values(value).map((item) => stringifyField(item)).filter(Boolean)
  }

  return [stringifyField(value)].filter(Boolean)
}

function flattenRecord(entries) {
  return entries
    .map(([label, value]) => {
      if (value === undefined || value === null || value === '') return null
      return {
        label,
        value: stringifyField(value),
      }
    })
    .filter(Boolean)
}

function stringifyField(value) {
  if (value === undefined || value === null || value === '') return ''
  if (typeof value === 'string') return value
  if (typeof value === 'number' || typeof value === 'boolean') return String(value)

  try {
    return JSON.stringify(value, null, 2)
  } catch {
    return String(value)
  }
}

function prettyJson(value) {
  return JSON.stringify(value ?? {}, null, 2)
}

function displayTimestamp(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'medium',
  }).format(date)
}

async function copyJson() {
  try {
    await navigator.clipboard.writeText(prettyJson(props.memory))
  } catch {
    // Clipboard access is optional; ignore failures.
  }
}
</script>