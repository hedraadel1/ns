<template>
  <section class="flex h-full min-h-0 flex-col border border-green-500/20 bg-zinc-950/80">
    <header class="border-b border-green-500/10 bg-zinc-900/60 p-4">
      <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-green-400/70">Contacts</p>
            <h2 class="text-lg font-black uppercase tracking-wide text-white">Directory</h2>
          </div>
          <button
            type="button"
            class="border border-green-500/30 bg-green-500/10 px-3 py-2 text-[11px] font-bold uppercase tracking-[0.25em] text-green-300 transition hover:bg-green-500/20 hover:text-green-200"
            @click="emit('refresh')"
          >
            Refresh
          </button>
        </div>

        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px]">
          <label class="block">
            <span class="mb-2 block text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-400">Search</span>
            <input
              :value="searchInput"
              type="search"
              placeholder="Name, email, phone, company..."
              class="w-full border border-zinc-800 bg-zinc-950 px-3 py-3 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-green-500/50"
              @input="onSearchInput"
            />
          </label>

          <label class="block">
            <span class="mb-2 block text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-400">Status</span>
            <select
              :value="statusFilter"
              class="w-full border border-zinc-800 bg-zinc-950 px-3 py-3 text-sm text-zinc-100 outline-none transition focus:border-green-500/50"
              @change="emit('update:statusFilter', $event.target.value)"
            >
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </label>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.25em] text-zinc-500">
          <span class="border border-zinc-800 bg-zinc-950 px-2 py-1 text-zinc-300">{{ contacts.length }} loaded</span>
          <span class="border border-zinc-800 bg-zinc-950 px-2 py-1 text-zinc-300">{{ filteredCount }} visible</span>
          <span v-if="activeFilters" class="border border-green-500/20 bg-green-500/10 px-2 py-1 text-green-300">{{ activeFilters }}</span>
        </div>
      </div>
    </header>

    <div class="min-h-0 flex-1 overflow-y-auto">
      <div v-if="loading" class="space-y-3 p-4">
        <div
          v-for="n in 6"
          :key="n"
          class="animate-pulse border border-zinc-800 bg-zinc-950 p-4"
        >
          <div class="mb-3 h-4 w-1/3 bg-zinc-800"></div>
          <div class="mb-2 h-3 w-1/2 bg-zinc-800"></div>
          <div class="h-3 w-2/3 bg-zinc-800"></div>
        </div>
      </div>

      <div v-else-if="error" class="p-4">
        <div class="border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">
          <p class="font-bold uppercase tracking-[0.25em] text-red-300">Failed to load contacts</p>
          <p class="mt-2 text-red-100/80">{{ error }}</p>
          <button
            type="button"
            class="mt-4 border border-red-500/30 bg-red-500/10 px-3 py-2 text-[11px] font-bold uppercase tracking-[0.25em] text-red-200 transition hover:bg-red-500/20"
            @click="emit('refresh')"
          >
            Retry
          </button>
        </div>
      </div>

      <div v-else-if="filteredContacts.length === 0" class="p-6 text-sm text-zinc-400">
        <div class="border border-dashed border-zinc-800 bg-zinc-950 p-6 text-center">
          <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-500">No contacts found</p>
          <p class="mt-2">
            Try a different search term or status filter.
          </p>
        </div>
      </div>

      <ul v-else class="divide-y divide-zinc-900">
        <li v-for="contact in filteredContacts" :key="contact.id">
          <button
            type="button"
            class="w-full border-l-4 px-4 py-4 text-left transition hover:bg-zinc-900/70"
            :class="contact.id === selectedId ? 'border-l-green-400 bg-zinc-900/80' : 'border-l-transparent bg-transparent'"
            @click="emit('select', contact)"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <p class="truncate text-sm font-black uppercase tracking-wide text-white">
                    {{ displayName(contact) }}
                  </p>
                  <span
                    class="shrink-0 border px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.25em]"
                    :class="statusClass(contact.status)"
                  >
                    {{ displayStatus(contact.status) }}
                  </span>
                </div>
                <p class="mt-1 truncate text-xs uppercase tracking-[0.25em] text-zinc-500">
                  {{ contact.title || contact.role || 'Contact' }}
                  <span v-if="contact.company || contact.organization" class="text-zinc-600">·</span>
                  <span v-if="contact.company || contact.organization">{{ contact.company || contact.organization }}</span>
                </p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs text-zinc-400">
                  <span v-if="contact.email" class="border border-zinc-800 bg-zinc-950 px-2 py-1">{{ contact.email }}</span>
                  <span v-if="contact.phone" class="border border-zinc-800 bg-zinc-950 px-2 py-1">{{ contact.phone }}</span>
                </div>
              </div>

              <div class="text-right text-[11px] uppercase tracking-[0.25em] text-zinc-600">
                <p>{{ contact.last_activity_at ? formatDate(contact.last_activity_at) : '—' }}</p>
                <p class="mt-1">ID {{ contact.id }}</p>
              </div>
            </div>
          </button>
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
  contacts: {
    type: Array,
    default: () => [],
  },
  selectedId: {
    type: [String, Number, null],
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  search: {
    type: String,
    default: '',
  },
  statusFilter: {
    type: String,
    default: 'all',
  },
  statusOptions: {
    type: Array,
    default: () => [
      { value: 'all', label: 'All statuses' },
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'archived', label: 'Archived' },
    ],
  },
  filteredCount: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['refresh', 'select', 'update:search', 'update:statusFilter'])
const searchInput = ref(props.search || '')
let searchDebounce = null

watch(
  () => props.search,
  (value) => {
    searchInput.value = value || ''
  }
)

onBeforeUnmount(() => {
  if (searchDebounce) {
    clearTimeout(searchDebounce)
  }
})

function onSearchInput(event) {
  searchInput.value = event.target.value
  if (searchDebounce) {
    clearTimeout(searchDebounce)
  }
  searchDebounce = setTimeout(() => {
    emit('update:search', searchInput.value)
  }, 250)
}

const activeFilters = computed(() => {
  const parts = []
  if (props.search?.trim()) parts.push(`Search: "${props.search.trim()}"`)
  if (props.statusFilter && props.statusFilter !== 'all') parts.push(`Status: ${props.statusFilter}`)
  return parts.join(' · ')
})

function displayName(contact) {
  return contact?.display_name || contact?.name || [contact?.first_name, contact?.last_name].filter(Boolean).join(' ') || `Contact ${contact?.id ?? ''}`.trim()
}

function displayStatus(status) {
  if (!status) return 'Unknown'
  return String(status).replace(/_/g, ' ')
}

function statusClass(status) {
  const value = String(status || '').toLowerCase()
  if (value === 'active' || value === 'open' || value === 'online') {
    return 'border-green-500/30 bg-green-500/10 text-green-300'
  }
  if (value === 'archived' || value === 'inactive' || value === 'closed') {
    return 'border-zinc-700 bg-zinc-900 text-zinc-300'
  }
  return 'border-amber-500/30 bg-amber-500/10 text-amber-300'
}

function formatDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return new Intl.DateTimeFormat('en', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  }).format(date)
}
</script>
