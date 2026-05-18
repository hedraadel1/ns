<template>
  <section class="flex h-full min-h-0 flex-col border border-green-500/20 bg-zinc-950/80">
    <header class="border-b border-green-500/10 bg-zinc-900/60 p-4">
      <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-green-400/70">Contact Profile</p>
      <h2 class="mt-1 text-lg font-black uppercase tracking-wide text-white">Details</h2>
    </header>

    <div class="min-h-0 flex-1 overflow-y-auto p-4">
      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse border border-zinc-800 bg-zinc-950 p-5">
          <div class="h-8 w-2/3 bg-zinc-800"></div>
          <div class="mt-4 h-3 w-1/2 bg-zinc-800"></div>
          <div class="mt-2 h-3 w-1/3 bg-zinc-800"></div>
        </div>
        <div class="animate-pulse border border-zinc-800 bg-zinc-950 p-5">
          <div class="h-4 w-1/4 bg-zinc-800"></div>
          <div class="mt-3 h-3 w-full bg-zinc-800"></div>
          <div class="mt-2 h-3 w-5/6 bg-zinc-800"></div>
        </div>
      </div>

      <div v-else-if="error" class="border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-100">
        <p class="font-bold uppercase tracking-[0.25em] text-red-300">Unable to load contact</p>
        <p class="mt-2">{{ error }}</p>
      </div>

      <div v-else-if="!contact" class="border border-dashed border-zinc-800 bg-zinc-950 p-8 text-center">
        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-500">No contact selected</p>
        <p class="mt-2 text-sm text-zinc-400">
          Choose a record from the directory to inspect identity, activity, and notes.
        </p>
      </div>

      <article v-else class="space-y-4">
        <div class="border border-zinc-800 bg-zinc-950 p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-zinc-500">Selected contact</p>
              <h3 class="mt-2 truncate text-2xl font-black uppercase tracking-wide text-white">
                {{ displayName }}
              </h3>
              <p class="mt-2 text-xs uppercase tracking-[0.3em] text-zinc-500">
                {{ contact.title || contact.role || 'Contact' }}
                <span v-if="contact.company || contact.organization" class="text-zinc-700">·</span>
                <span v-if="contact.company || contact.organization">{{ contact.company || contact.organization }}</span>
              </p>
            </div>

            <span
              class="shrink-0 border px-3 py-1 text-[10px] font-bold uppercase tracking-[0.3em]"
              :class="statusClass(contact.status)"
            >
              {{ displayStatus }}
            </span>
          </div>

          <div class="mt-5 flex flex-wrap gap-2 text-xs text-zinc-300">
            <span v-if="contact.email" class="border border-zinc-800 bg-zinc-900 px-3 py-1">{{ contact.email }}</span>
            <span v-if="contact.phone" class="border border-zinc-800 bg-zinc-900 px-3 py-1">{{ contact.phone }}</span>
            <span v-if="contact.id" class="border border-zinc-800 bg-zinc-900 px-3 py-1">ID {{ contact.id }}</span>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div class="border border-zinc-800 bg-zinc-950 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-500">Identity</p>
            <dl class="mt-4 space-y-3 text-sm">
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Name</dt>
                <dd class="text-right text-zinc-100">{{ displayName }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Company</dt>
                <dd class="text-right text-zinc-100">{{ contact.company || contact.organization || '—' }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Title</dt>
                <dd class="text-right text-zinc-100">{{ contact.title || contact.role || '—' }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Email</dt>
                <dd class="text-right text-zinc-100">{{ contact.email || '—' }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Phone</dt>
                <dd class="text-right text-zinc-100">{{ contact.phone || '—' }}</dd>
              </div>
            </dl>
          </div>

          <div class="border border-zinc-800 bg-zinc-950 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-500">Activity</p>
            <dl class="mt-4 space-y-3 text-sm">
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Last activity</dt>
                <dd class="text-right text-zinc-100">{{ formatDate(contact.last_activity_at || contact.last_interaction_at) }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Created</dt>
                <dd class="text-right text-zinc-100">{{ formatDate(contact.created_at) }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Updated</dt>
                <dd class="text-right text-zinc-100">{{ formatDate(contact.updated_at) }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-zinc-500">Status source</dt>
                <dd class="text-right text-zinc-100">{{ contact.status_source || contact.source || 'API' }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <div class="border border-zinc-800 bg-zinc-950 p-4">
          <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-500">Tags</p>
          <div v-if="tags.length" class="mt-3 flex flex-wrap gap-2">
            <span
              v-for="tag in tags"
              :key="tag"
              class="border border-green-500/20 bg-green-500/10 px-3 py-1 text-xs font-semibold text-green-200"
            >
              {{ tag }}
            </span>
          </div>
          <p v-else class="mt-3 text-sm text-zinc-500">No tags available.</p>
        </div>

        <div class="border border-zinc-800 bg-zinc-950 p-4">
          <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-500">Notes</p>
          <p v-if="notes" class="mt-3 whitespace-pre-wrap text-sm leading-6 text-zinc-200">{{ notes }}</p>
          <p v-else class="mt-3 text-sm text-zinc-500">No notes available for this contact.</p>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  contact: {
    type: Object,
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
})

const displayName = computed(() => {
  const contact = props.contact || {}
  return contact.display_name || contact.name || [contact.first_name, contact.last_name].filter(Boolean).join(' ') || `Contact ${contact.id ?? ''}`.trim()
})

const displayStatus = computed(() => {
  const status = props.contact?.status
  if (!status) return 'Unknown'
  return String(status).replace(/_/g, ' ')
})

const tags = computed(() => {
  const raw = props.contact?.tags || props.contact?.tag_list || props.contact?.labels || []
  if (Array.isArray(raw)) return raw.filter(Boolean).map((item) => String(item))
  if (typeof raw === 'string' && raw.trim()) {
    return raw.split(',').map((item) => item.trim()).filter(Boolean)
  }
  return []
})

const notes = computed(() => props.contact?.notes || props.contact?.summary || props.contact?.bio || '')

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