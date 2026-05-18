<template>
  <section class="p-4">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4 gap-4">
      <div>
        <h1 class="text-2xl font-semibold">Tasks</h1>
        <p class="text-sm text-gray-500">Create, browse, and manage active tasks.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="refresh" class="px-3 py-2 bg-blue-600 text-white rounded">Refresh</button>
        <button @click="openCreate" class="px-3 py-2 bg-green-600 text-white rounded">Create Task</button>
      </div>
    </div>

    <div v-if="showCreate" class="mb-6 p-4 bg-white shadow rounded">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-lg font-semibold">New Task</h2>
          <p class="text-sm text-gray-500">Quick task creation with title, priority, and optional owner.</p>
        </div>
        <button @click="closeCreate" class="text-gray-500 hover:text-gray-700">Close</button>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">Title</label>
          <input v-model="newTask.title" type="text" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
          <p v-if="validationErrors.title" class="text-rose-600 text-sm mt-1">{{ validationErrors.title }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Priority</label>
          <input v-model.number="newTask.priority" type="number" min="0" max="10" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
          <p v-if="validationErrors.priority" class="text-rose-600 text-sm mt-1">{{ validationErrors.priority }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Agent ID</label>
          <input v-model="newTask.agent_id" type="text" class="mt-1 block w-full rounded border-gray-300 shadow-sm" placeholder="Optional agent id" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Workflow ID</label>
          <input v-model="newTask.workflow_id" type="text" class="mt-1 block w-full rounded border-gray-300 shadow-sm" placeholder="Optional workflow id" />
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea v-model="newTask.description" rows="3" class="mt-1 block w-full rounded border-gray-300 shadow-sm"></textarea>
        </div>
        <div class="md:col-span-2 flex items-center gap-3">
          <button @click="createTask" :disabled="creating || Object.keys(validationErrors).length" class="px-4 py-2 bg-blue-600 text-white rounded">Create Task</button>
          <button @click="closeCreate" type="button" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
          <span class="text-sm text-rose-600" v-if="createError">{{ createError }}</span>
        </div>
      </div>
    </div>

    <div v-if="loading">
      <LoadingSpinner message="Loading tasks..." />
    </div>
    <div v-else-if="error">
      <ErrorPanel :message="error" @retry="refresh" />
    </div>

    <div v-else>
      <table class="w-full text-left border-collapse bg-white shadow rounded overflow-hidden">
        <thead>
          <tr class="text-sm text-gray-500 bg-gray-50 border-b">
            <th class="p-3">Title</th>
            <th class="p-3">Agent</th>
            <th class="p-3">Workflow</th>
            <th class="p-3">Status</th>
            <th class="p-3">Priority</th>
            <th class="p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="task in tasks" :key="task.id" class="border-b hover:bg-gray-50">
            <td class="p-3">{{ task.title }}</td>
            <td class="p-3">{{ task.agent?.name ?? task.agent_name ?? '—' }}</td>
            <td class="p-3">{{ task.workflow?.name ?? task.workflow_name ?? '—' }}</td>
            <td class="p-3">{{ task.status }}</td>
            <td class="p-3">{{ task.priority }}</td>
            <td class="p-3">
              <router-link :to="`/tasks/${task.id}`" class="text-blue-500 hover:underline mr-3">View</router-link>
              <button @click="cancel(task.id)" class="text-rose-600 hover:text-rose-800">Cancel</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <button :disabled="!pagination.prev" @click="loadPage(pagination.prev)" class="px-3 py-2 bg-gray-200 rounded">Prev</button>
          <button :disabled="!pagination.next" @click="loadPage(pagination.next)" class="px-3 py-2 bg-gray-200 rounded">Next</button>
        </div>
        <div class="text-sm text-gray-500">Page {{ pagination.current }} of {{ pagination.last }}</div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import LoadingSpinner from '../Components/LoadingSpinner.vue'
import ErrorPanel from '../Components/ErrorPanel.vue'

const loading = ref(false)
const error = ref('')
const tasks = ref([])
const pagination = ref({ current: 1, last: 1, next: null, prev: null })
const router = useRouter()
const showCreate = ref(false)
const creating = ref(false)
const createError = ref('')
const validationErrors = ref({})
const newTask = ref({ title: '', description: '', agent_id: '', workflow_id: '', priority: 0, due_at: '' })
let pollInterval = null

async function loadPage(url = '/api/v1/tasks') {
  loading.value = true
  error.value = ''

  try {
    const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'include' })
    if (!res.ok) throw new Error('Failed to fetch tasks')
    const json = await res.json()
    const data = json.data ?? json
    tasks.value = data.data ?? data.items ?? data

    pagination.value.current = data.current_page ?? data.meta?.current_page ?? 1
    pagination.value.last = data.last_page ?? data.meta?.last_page ?? 1
    pagination.value.next = data.next_page_url ?? data.links?.next ?? null
    pagination.value.prev = data.prev_page_url ?? data.links?.prev ?? null
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load tasks'
  } finally {
    loading.value = false
  }
}

function refresh() {
  loadPage('/api/v1/tasks')
}

function openCreate() {
  showCreate.value = true
  createError.value = ''
  validationErrors.value = {}
  newTask.value = { title: '', description: '', agent_id: '', workflow_id: '', priority: 0, due_at: '' }
}

function closeCreate() {
  showCreate.value = false
}

async function createTask() {
    // Client-side validation
  validationErrors.value = {}
  if (!newTask.value.title || !newTask.value.title.trim()) {
    validationErrors.value.title = 'Title is required.'
  }
  if (newTask.value.priority == null || newTask.value.priority < 0 || newTask.value.priority > 10) {
    validationErrors.value.priority = 'Priority must be between 0 and 10.'
  }
  if (Object.keys(validationErrors.value).length) {
    createError.value = 'Please fix validation errors.'
    return
  }

  creating.value = true
  createError.value = ''

  try {
    // Generate a client token to correlate optimistic navigation
    const token = (typeof crypto !== 'undefined' && crypto.randomUUID) ? crypto.randomUUID() : 'tok_' + Math.random().toString(36).slice(2, 10)
    // Optimistic navigation: show creating UI while backend confirms
    router.push({ path: '/tasks/creating', query: { token } })

    const payload = {
      title: newTask.value.title,
      description: newTask.value.description,
      agent_id: newTask.value.agent_id || null,
      workflow_id: newTask.value.workflow_id || null,
      priority: newTask.value.priority,
      due_at: newTask.value.due_at || null,
      metadata: Object.assign({}, newTask.value.metadata || {}, { client_token: token }),
    }

    const res = await fetch('/api/v1/tasks', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      credentials: 'include',
      body: JSON.stringify(payload),
    })

    if (!res.ok) {
      const json = await res.json().catch(() => ({}))
      throw new Error(json.message || json.errors ? Object.values(json.errors || {}).flat().join(', ') : 'Create task failed')
    }

    const json = await res.json().catch(() => ({}))
    const created = json.data || json
    // Optimistic UX: redirect to a creating page immediately if not already
    // (we might have navigated before the request; ensure we land on created resource if backend returns id)
    if (created && created.id) {
      router.replace(`/tasks/${created.id}`)
      return
    }

    await refresh()
    closeCreate()
  } catch (err) {
    createError.value = err instanceof Error ? err.message : 'Create task failed'
  } finally {
    creating.value = false
  }
}

async function cancel(id) {
  try {
    const res = await fetch(`/api/v1/tasks/${id}/cancel`, { method: 'POST', headers: { Accept: 'application/json' }, credentials: 'include' })
    if (!res.ok) throw new Error('Cancel failed')
    await refresh()
  } catch (err) {
    alert(err instanceof Error ? err.message : 'Cancel failed')
  }
}

onMounted(() => loadPage())
// poll for updates every 10s
onMounted(() => {
  pollInterval = setInterval(() => {
    loadPage(`/api/v1/tasks`)
  }, 10000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

