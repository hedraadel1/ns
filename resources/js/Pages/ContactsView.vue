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

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
          <p class="text-sm text-slate-400">Swipe right from the left edge on mobile to go back.</p>
          <button
            type="button"
            class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-200 transition hover:bg-white/10"
            @click="openContactActionsSheet"
          >
            More actions
          </button>
        </div>
      </div>

      <div class="mt-5 flex flex-col gap-3 rounded-xl border border-green-500/10 bg-zinc-950/70 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-green-400/70">Quick add</p>
            <p class="mt-1 text-sm text-zinc-400">Add a contact optimistically while the API confirms the record.</p>
          </div>
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-md border border-green-500/20 bg-green-500/10 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.3em] text-green-200 transition hover:bg-green-500/20"
            @click="showAddForm = !showAddForm"
          >
            {{ showAddForm ? 'Hide' : 'Add Contact' }}
          </button>
        </div>

        <div v-if="showAddForm" class="space-y-3">
          <div class="grid gap-3 md:grid-cols-2">
            <label class="block">
              <span class="mb-2 block text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-400">Name</span>
              <input
                v-model="newContact.name"
                type="text"
                placeholder="Jane Doe"
                class="w-full border border-zinc-800 bg-zinc-950 px-3 py-3 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-green-500/50"
              />
            </label>
            <label class="block">
              <span class="mb-2 block text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-400">Email</span>
              <input
                v-model="newContact.email"
                type="email"
                placeholder="jane@example.com"
                class="w-full border border-zinc-800 bg-zinc-950 px-3 py-3 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-green-500/50"
              />
            </label>
            <label class="block">
              <span class="mb-2 block text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-400">Company</span>
              <input
                v-model="newContact.company"
                type="text"
                placeholder="Nexus Labs"
                class="w-full border border-zinc-800 bg-zinc-950 px-3 py-3 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-green-500/50"
              />
            </label>
            <label class="block">
              <span class="mb-2 block text-[11px] font-bold uppercase tracking-[0.3em] text-zinc-400">Title</span>
              <input
                v-model="newContact.title"
                type="text"
                placeholder="Product Lead"
                class="w-full border border-zinc-800 bg-zinc-950 px-3 py-3 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-green-500/50"
              />
            </label>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2 text-sm text-zinc-300">
              <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Status</p>
              <select
                v-model="newContact.status"
                class="w-full border border-zinc-800 bg-zinc-950 px-3 py-3 text-sm text-zinc-100 outline-none transition focus:border-green-500/50"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="archived">Archived</option>
              </select>
            </div>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-md bg-green-500 px-4 py-3 text-[11px] font-bold uppercase tracking-[0.3em] text-white transition hover:bg-green-400 disabled:opacity-50"
              :disabled="addLoading"
              @click="handleAddContact"
            >
              {{ addLoading ? 'Adding…' : 'Submit contact' }}
            </button>
          </div>

          <div v-if="addError" class="rounded-md border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-100">
            {{ addError }}
          </div>
          <div v-if="addSuccess" class="rounded-md border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-100">
            {{ addSuccess }}
          </div>
        </div>
      </div>
    </section>

    <div class="grid min-h-0 flex-1 gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
      <div class="space-y-4">
        <div v-if="multiSelectMode" class="rounded-2xl border border-emerald-500/20 bg-slate-950/80 p-4 text-sm text-emerald-200">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p>
              <span class="text-2xl font-bold text-white">{{ selectedCount }}</span>
              selected
            </p>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200 transition hover:bg-emerald-500/20"
                @click="toggleSelectAll"
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
          <p class="mt-3 text-sm text-slate-400">Use long-press or click to select contacts, then archive or delete in bulk.</p>
          <p v-if="bulkActionError" class="mt-3 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">{{ bulkActionError }}</p>
        </div>

        <NxPullRefresh @refresh="loadContacts">
          <ContactList
            class="min-h-0"
            :contacts="filteredContacts"
            :selected-id="selectedContactId"
            :bulk-mode="multiSelectMode"
            :selected-ids="selectedContactIds"
            :loading="contactsStore.loading"
            :error="contactsError"
            :search="search"
            :status-filter="statusFilter"
            :status-options="statusOptions"
            :filtered-count="filteredContacts.length"
            @refresh="loadContacts"
            @select="selectContact"
            @toggle-selection="toggleContactSelection"
            @enter-bulk-mode="enterBulkMode"
            @contextmenu="openContactContextMenu"
            @update:search="updateSearch"
            @update:status-filter="updateStatusFilter"
          />
        </NxPullRefresh>
      </div>

      <ContactDetail
        class="min-h-0"
        :contact="selectedContact"
        :loading="loadingDetail"
        :error="detailError"
      />
    </div>

    <div
      v-if="multiSelectMode"
      class="fixed bottom-6 left-1/2 z-50 w-[min(96vw,1024px)] -translate-x-1/2 rounded-3xl border border-white/10 bg-slate-950/95 p-4 shadow-2xl shadow-black/40 backdrop-blur-xl transition-transform duration-300"
    >
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-200">
          <span class="text-white font-semibold">{{ selectedCount }}</span> contacts selected
        </p>
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200 transition hover:bg-emerald-500/20"
            @click="bulkArchiveContacts"
            :disabled="bulkActionLoading"
          >
            Archive
          </button>
          <button
            type="button"
            class="rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-rose-200 transition hover:bg-rose-500/20"
            @click="bulkDeleteContacts"
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
      <p class="text-xs text-slate-400">Actions apply to all selected contacts.</p>
    </div>

    <NxBottomSheet :open="showActionsSheet" title="Contacts actions" subtitle="Quick mobile operations" @close="showActionsSheet = false">
      <div class="space-y-3">
        <button
          type="button"
          class="w-full rounded-2xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-left text-sm text-slate-100 transition hover:border-emerald-500/40 hover:bg-slate-900"
          @click="loadContacts"
        >
          Refresh contacts
        </button>
        <button
          type="button"
          class="w-full rounded-2xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-left text-sm text-slate-100 transition hover:border-emerald-500/40 hover:bg-slate-900"
          @click="showAddForm = true"
        >
          Add a new contact
        </button>
        <button
          type="button"
          class="w-full rounded-2xl border border-slate-700 bg-slate-950/80 px-4 py-3 text-left text-sm text-slate-100 transition hover:border-emerald-500/40 hover:bg-slate-900"
          @click="clearBulkMode"
        >
          Cancel bulk mode
        </button>
      </div>
    </NxBottomSheet>

    <NxContextMenu
      :visible="contextMenuVisible"
      :x="contextMenuX"
      :y="contextMenuY"
      :items="contactActions"
      @select="handleContactContextMenu"
      @close="closeContactContextMenu"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue'
import { useContacts } from '../stores/useContacts'
import ContactList from '../Components/ContactList.vue'
import NxPullRefresh from '../Components/NxPullRefresh.vue'
import NxBottomSheet from '../Components/NxBottomSheet.vue'
import NxContextMenu from '../Components/NxContextMenu.vue'
import ContactDetail from './ContactDetail.vue'

const contactsStore = useContacts()
const selectedContactId = ref(null)
const selectedContact = ref(null)
const loadingDetail = ref(false)
const contactsError = ref('')
const detailError = ref('')
const search = ref('')
const statusFilter = ref('all')
const showAddForm = ref(false)
const addLoading = ref(false)
const addError = ref('')
const addSuccess = ref('')

const newContact = ref({
  name: '',
  email: '',
  company: '',
  title: '',
  status: 'active',
})

const statusOptions = [
  { value: 'all', label: 'All statuses' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'archived', label: 'Archived' },
]

const multiSelectMode = ref(false)
const selectedContactIds = ref([])
const bulkActionLoading = ref(false)
const bulkActionError = ref('')
const showActionsSheet = ref(false)
const contextMenuVisible = ref(false)
const contextMenuX = ref(0)
const contextMenuY = ref(0)
const contextMenuTarget = ref(null)

const normalizedContacts = computed(() => contactsStore.contacts.map(normalizeContact))

const filteredContacts = computed(() => {
  const query = search.value.trim().toLowerCase()
  return normalizedContacts.value.filter((contact) => {
    const fields = [
      contact.display_name,
      contact.name,
      contact.email,
      contact.phone,
      contact.company,
      contact.organization,
      contact.title,
      contact.role,
      contact.status,
      ...(contact.tags || []),
    ]

    const matchesQuery =
      !query ||
      fields.filter(Boolean).some((field) => String(field).toLowerCase().includes(query))

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

const selectedCount = computed(() => selectedContactIds.value.length)
const visibleContactIds = computed(() => filteredContacts.value.map((contact) => contact.id))
const allVisibleSelected = computed(() =>
  visibleContactIds.value.length > 0 && visibleContactIds.value.every((id) => selectedContactIds.value.includes(id))
)

function enterBulkMode(contact) {
  if (!multiSelectMode.value) {
    multiSelectMode.value = true
    selectedContactIds.value = [contact.id]
  }
}

function toggleContactSelection(contact) {
  if (!contact) return
  const id = contact.id
  const index = selectedContactIds.value.indexOf(id)
  if (index > -1) {
    selectedContactIds.value.splice(index, 1)
  } else {
    selectedContactIds.value.push(id)
  }
}

function clearBulkMode() {
  multiSelectMode.value = false
  selectedContactIds.value = []
  bulkActionError.value = ''
}

function toggleSelectAll() {
  if (allVisibleSelected.value) {
    selectedContactIds.value = []
    return
  }
  selectedContactIds.value = Array.from(new Set([...visibleContactIds.value]))
}

function openContactActionsSheet() {
  showActionsSheet.value = true
}

function openContactContextMenu(payload) {
  contextMenuTarget.value = payload.contact
  contextMenuX.value = payload.x
  contextMenuY.value = payload.y
  contextMenuVisible.value = true
}

function closeContactContextMenu() {
  contextMenuVisible.value = false
  contextMenuTarget.value = null
}

function handleContactContextMenu(action) {
  const contact = contextMenuTarget.value
  if (!contact) {
    closeContactContextMenu()
    return
  }

  switch (action.value) {
    case 'view':
      selectContact(contact)
      break
    case 'archive':
      contactsStore.updateContact(contact.id, { status: 'archived' })
      break
    case 'delete':
      contactsStore.deleteContact(contact.id)
      break
    case 'bulk':
      enterBulkMode(contact)
      break
    default:
      break
  }

  closeContactContextMenu()
}

const contactActions = computed(() => [
  { value: 'view', label: 'View details' },
  { value: 'archive', label: 'Archive contact' },
  { value: 'delete', label: 'Delete contact' },
  { value: 'bulk', label: 'Select for bulk' },
])

async function bulkArchiveContacts() {
  if (!selectedContactIds.value.length) return
  bulkActionLoading.value = true
  bulkActionError.value = ''

  try {
    await Promise.all(
      selectedContactIds.value.map((contactId) =>
        contactsStore.updateContact(contactId, { status: 'archived' })
      )
    )
    clearBulkMode()
  } catch (error) {
    bulkActionError.value = error instanceof Error ? error.message : 'Failed to archive selected contacts.'
  } finally {
    bulkActionLoading.value = false
  }
}

async function bulkDeleteContacts() {
  if (!selectedContactIds.value.length) return
  bulkActionLoading.value = true
  bulkActionError.value = ''

  try {
    const deletedIds = [...selectedContactIds.value]
    await Promise.all(
      deletedIds.map((contactId) => contactsStore.deleteContact(contactId))
    )
    if (deletedIds.includes(selectedContactId.value)) {
      selectedContactId.value = null
      selectedContact.value = null
    }
    clearBulkMode()
  } catch (error) {
    bulkActionError.value = error instanceof Error ? error.message : 'Failed to delete selected contacts.'
  } finally {
    bulkActionLoading.value = false
  }
}


const handleFabAction = (event) => {
  const action = event.detail
  switch (action.value) {
    case 'refresh':
      loadContacts()
      break
    case 'new-contact':
      showAddForm.value = true
      break
    case 'more':
      openContactActionsSheet()
      break
    default:
      break
  }
}

onMounted(() => {
  loadContacts()
  window.addEventListener('nx-fab-action', handleFabAction)
})

onBeforeUnmount(() => {
  window.removeEventListener('nx-fab-action', handleFabAction)
})

watch(
  () => filteredContacts.value,
  (current) => {
    if (!current.length) {
      selectedContactId.value = null
      if (!loadingDetail.value) {
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
  contactsError.value = ''

  try {
    await contactsStore.fetchContacts()
  } catch (error) {
    contactsError.value = error instanceof Error ? error.message : 'Unable to load contacts.'
    contactsStore.contacts = []
    selectedContact.value = null
    selectedContactId.value = null
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

async function handleAddContact() {
  addLoading.value = true
  addError.value = ''
  addSuccess.value = ''

  try {
    const created = await contactsStore.addContact({
      name: newContact.value.name,
      email: newContact.value.email,
      company: newContact.value.company,
      title: newContact.value.title,
      status: newContact.value.status,
    })
    selectedContactId.value = created.id
    selectedContact.value = normalizeContact(created)
    addSuccess.value = 'Contact added successfully.'
    showAddForm.value = false
    newContact.value = { name: '', email: '', company: '', title: '', status: 'active' }
  } catch (error) {
    addError.value =
      error instanceof Error ? error.message : 'Unable to add contact. Please try again.'
  } finally {
    addLoading.value = false
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
