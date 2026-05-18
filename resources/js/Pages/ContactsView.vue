<template>
  <div class="flex min-h-[calc(100vh-8rem)] flex-col gap-4 p-4 text-zinc-100 md:p-6">
    <section class="border border-green-500/20 bg-zinc-950/80 p-5">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
          <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-green-400/70">Contacts Hub</p>
          <h1 class="mt-2 text-3xl font-black uppercase tracking-wide text-white">People, accounts, and records</h1>
          <p class="mt-3 text-sm leading-6 text-zinc-400">
            Search, filter, and inspect contacts from the live API. The list stays lightweight while the
            details panel loads a full record on demand.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-3 text-center text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-400 sm:grid-cols-4 lg:w-auto">
          <div class="border border-zinc-800 bg-zinc-950 px-4 py-3">
            <div class="text-xl text-white">{{ metrics.total }}</div>
            <div class="mt-1">Total</div>
          </div>
          <div class="border border-zinc-800 bg-zinc-950 px-4 py-3">
            <div class="text-xl text-green-300">{{ metrics.active }}</div>
            <div class="mt-1">Active</div>
          </div>
          <div class="border border-zinc-800 bg-zinc-950 px-4 py-3">
            <div class="text-xl text-amber-300">{{ metrics.other }}</div>
            <div class="mt-1">Other</div>
          </div>
          <div class="border border-zinc-800 bg-zinc-950 px-4 py-3">
            <div class="text-xl text-white">{{ metrics.visible }}</div>
            <div class="mt-1">Visible</div>
          </div>
        </div>
      </div>
    </section>

    <div class="grid min-h-0 flex-1 gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
      <ContactList
        class="min-h-0"
        :contacts="filteredContacts"
        :selected-id="selectedContactId"
        :loading="loadingContacts"
        :error="contactsError"
        :search="search"
        :status-filter="statusFilter"
        :status-options="statusOptions"
        :filtered-count="filteredContacts.length"
        @refresh="loadContacts"
        @select="selectContact"
        @update:search="updateSearch"
        @update:status-filter="updateStatusFilter"
      />

      <ContactDetail
        class="min-h-0"
        :contact="selectedContact"
        :loading="loadingDetail"
        :error="detailError"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import ContactList from '../Components/ContactList.vue'
import ContactDetail from './ContactDetail.vue'

const contacts = ref([])
const selectedContactId = ref(null)
const selectedContact = ref(null)
const loadingContacts = ref(false)
const loadingDetail = ref(false)
const contactsError = ref('')
const detailError = ref('')
const search = ref('')
const statusFilter = ref('all')

const statusOptions = [
  { value: 'all', label: 'All statuses' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'archived', label: 'Archived' },
]

const normalizedContacts = computed(() => contacts.value.map(normalizeContact))

const filteredContacts = computed(() => {
  const query = search.value.trim().toLowerCase()
  return normalizedContacts.value.filter((contact) => {
    const matchesQuery =
      !query ||
      [contact.display_name, contact.name, contact.email, contact.phone, contact.company, contact.organization, contact.title, contact.role, contact.status, ...(contact.tags || [])]
        .filter(Boolean)
        .some((field) => String(field).toLowerCase().includes(query))

    const matchesStatus =
      statusFilter.value === 'all' ||
      String(contact.status || '').toLowerCase() === String(statusFilter.value).toLowerCase()

    return matchesQuery && matchesStatus
  })
})

const metrics = computed(() => {
  const total = normalizedContacts.value.length
  const active = normalizedContacts.value.filter((contact) => String(contact.status || '').toLowerCase() === 'active').length
  return {
    total,
    active,
    other: Math.max(total - active, 0),
    visible: filteredContacts.value.length,
  }
})

onMounted(() => {
  loadContacts()
})

watch(
  () => filteredContacts.value,
  (current) => {
    if (!current.length) {
      selectedContactId.value = null
      if (!loadingContacts.value) {
        selectedContact.value = null
      }
      return
    }

    const stillVisible = current.some((contact) => String(contact.id) === String(selectedContactId.value))
    if (!selectedContactId.value || !stillVisible) {
      selectContact(current[0])
    }
  },
  { immediate: true }
)

async function loadContacts() {
  loadingContacts.value = true
  contactsError.value = ''

  try {
    const response = await fetch('/api/v1/contacts', {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`)
    }

    const payload = await response.json()
    contacts.value = normalizeCollection(payload)
  } catch (error) {
    contactsError.value = error instanceof Error ? error.message : 'Unable to load contacts.'
    contacts.value = []
    selectedContact.value = null
    selectedContactId.value = null
  } finally {
    loadingContacts.value = false
  }
}

async function selectContact(contact) {
  if (!contact) return

  selectedContactId.value = contact.id
  detailError.value = ''
  loadingDetail.value = true

  const fallback = normalizeContact(contact)
  selectedContact.value = fallback

  try {
    const response = await fetch(`/api/v1/contacts/${contact.id}`, {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`)
    }

    const payload = await response.json()
    selectedContact.value = normalizeContact(normalizeSingle(payload) || fallback)
  } catch (error) {
    detailError.value = error instanceof Error ? error.message : 'Unable to load contact details.'
    selectedContact.value = fallback
  } finally {
    loadingDetail.value = false
  }
}

function updateSearch(value) {
  search.value = value
}

function updateStatusFilter(value) {
  statusFilter.value = value
}

function normalizeCollection(payload) {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.contacts)) return payload.contacts
  if (Array.isArray(payload?.results)) return payload.results
  if (Array.isArray(payload?.items)) return payload.items
  return []
}

function normalizeSingle(payload) {
  if (!payload) return null
  if (payload.data && !Array.isArray(payload.data)) return payload.data
  if (payload.contact) return payload.contact
  if (payload.result) return payload.result
  return payload
}

function normalizeContact(contact) {
  const firstName = contact?.first_name || contact?.firstName || ''
  const lastName = contact?.last_name || contact?.lastName || ''
  const displayName =
    contact?.display_name ||
    contact?.displayName ||
    contact?.name ||
    [firstName, lastName].filter(Boolean).join(' ') ||
    `Contact ${contact?.id ?? ''}`.trim()

  return {
    ...contact,
    display_name: displayName,
    name: contact?.name || displayName,
    company: contact?.company || contact?.organization || '',
    organization: contact?.organization || contact?.company || '',
    title: contact?.title || contact?.role || '',
    role: contact?.role || contact?.title || '',
    status: contact?.status || contact?.state || contact?.lifecycle_status || '',
    tags: normalizeTags(contact?.tags || contact?.tag_list || contact?.labels),
  }
}

function normalizeTags(raw) {
  if (Array.isArray(raw)) return raw.filter(Boolean).map((value) => String(value))
  if (typeof raw === 'string' && raw.trim()) {
    return raw.split(',').map((value) => value.trim()).filter(Boolean)
  }
  return []
}
</script>