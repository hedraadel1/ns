<template>
  <section class="p-4">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4 gap-4">
      <div>
        <h1 class="text-2xl font-semibold">Task detail</h1>
        <p class="text-sm text-gray-500">View and edit task properties or manage state.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button v-if="!isEditing" @click="startEditing" class="px-3 py-2 bg-indigo-600 text-white rounded">Edit</button>
        <button v-if="isEditing" @click="saveTask" class="px-3 py-2 bg-blue-600 text-white rounded">Save</button>
        <button v-if="isEditing" @click="cancelEdit" class="px-3 py-2 bg-gray-200 rounded">Cancel</button>
        <button v-if="!isEditing" @click="pause" class="px-3 py-2 bg-yellow-500 text-white rounded">Pause</button>
        <button v-if="!isEditing" @click="resume" class="px-3 py-2 bg-green-600 text-white rounded">Resume</button>
        <button v-if="!isEditing" @click="cancelAction" class="px-3 py-2 bg-rose-600 text-white rounded">Cancel Task</button>
      </div>
    </div>

    <div v-if="loading">
      <LoadingSpinner message="Loading task..." />
    </div>
    <div v-else-if="error">
      <ErrorPanel :message="error" @retry="fetchTask" />
    </div>

    <div v-else>
      <div class="bg-white shadow rounded p-4 mb-4">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="block text-xs uppercase tracking-wide text-gray-500">Title</label>
            <div v-if="!isEditing" class="mt-1 text-lg font-semibold">{{ task.title }}</div>
            <input v-else v-model="editTask.title" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wide text-gray-500">Status</label>
            <div class="mt-1 font-medium">{{ task.status }}</div>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wide text-gray-500">Progress</label>
            <div class="mt-1 font-medium">{{ task.progress }}%</div>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wide text-gray-500">Updated</label>
            <div class="mt-1 font-medium">{{ task.updated_at }}</div>
          </div>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="bg-white shadow rounded p-4">
          <label class="block text-xs uppercase tracking-wide text-gray-500">Description</label>
          <div v-if="!isEditing" class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ task.description || 'No description provided.' }}</div>
          <textarea v-else v-model="editTask.description" rows="4" class="mt-1 block w-full rounded border-gray-300 shadow-sm"></textarea>
        </div>

        <div class="bg-white shadow rounded p-4">
          <label class="block text-xs uppercase tracking-wide text-gray-500">Priority</label>
          <div v-if="!isEditing" class="mt-1 text-sm font-medium">{{ task.priority }}</div>
          <input v-else v-model.number="editTask.priority" type="number" min="0" max="10" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />

          <label class="block text-xs uppercase tracking-wide text-gray-500 mt-4">Due</label>
          <div v-if="!isEditing" class="mt-1 text-sm">{{ task.due_at || 'None' }}</div>
          <input v-else v-model="editTask.due_at" type="datetime-local" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
        </div>
      </div>

      <div class="mt-4 bg-white shadow rounded p-4">
        <h3 class="text-sm font-semibold mb-2">Metadata</h3>
        <pre class="text-sm text-gray-700 bg-gray-50 p-3 rounded break-words">{{ JSON.stringify(task.metadata || {}, null, 2) }}</pre>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import LoadingSpinner from '../Components/LoadingSpinner.vue'
import ErrorPanel from '../Components/ErrorPanel.vue'

const route = useRoute()
const id = route.params.id
const isCreating = id === 'creating'
const creatingToken = route.query.token || ''
const creatingTitle = route.query.title || ''
const loading = ref(false)
const error = ref('')
const task = ref({})
const isEditing = ref(false)
const editTask = ref({ title: '', description: '', priority: 0, due_at: '' })
let pollInterval = null

async function fetchTask() {
  loading.value = true
  error.value = ''

  try {
    if (isCreating) {
      // Poll index searching for the created task by token (preferred) or title
      if (!creatingToken && !creatingTitle) {
        throw new Error('No token or title provided for optimistic creation')
      }

      let url = ''
      if (creatingToken) {
        url = `/api/v1/tasks?metadata_token=${encodeURIComponent(creatingToken)}&per_page=10`
      } else {
        url = `/api/v1/tasks?search=${encodeURIComponent(creatingTitle)}&per_page=10`
      }

      const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'include' })
      if (!res.ok) throw new Error('Failed to search tasks')
      const json = await res.json()
      const items = (json.data && json.data.data) ? json.data.data : (json.data ?? json)
      let found = null
      if (creatingToken) {
        found = (items || []).find(t => t.metadata && t.metadata.client_token === creatingToken)
      } else {
        found = (items || []).find(t => t.title === creatingTitle)
      }
      if (found && found.id) {
        // Replace creating route with real task
        window.history.replaceState({}, '', `/tasks/${found.id}`)
        // load real task
        const r2 = await fetch(`/api/v1/tasks/${found.id}`, { headers: { Accept: 'application/json' }, credentials: 'include' })
        const j2 = await r2.json()
        task.value = j2.data || j2
      } else {
        // nothing yet; keep empty task and let polling continue
        task.value = {}
      }
    } else {
      const res = await fetch(`/api/v1/tasks/${id}`, { headers: { Accept: 'application/json' }, credentials: 'include' })
      if (!res.ok) throw new Error('Failed to load task')
      const json = await res.json()
      task.value = json.data || json
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load task'
  } finally {
    loading.value = false
  }
}

function startEditing() {
  isEditing.value = true
  editTask.value = {
    title: task.value.title || '',
    description: task.value.description || '',
    priority: task.value.priority ?? 0,
    due_at: task.value.due_at ? task.value.due_at.replace(' ', 'T') : '',
  }
}

function cancelEdit() {
  isEditing.value = false
  error.value = ''
}

async function saveTask() {
  if (!editTask.value.title.trim()) {
    error.value = 'Title is required.'
    return
  }

  try {
    const payload = {
      title: editTask.value.title,
      description: editTask.value.description,
      priority: editTask.value.priority,
      due_at: editTask.value.due_at || null,
    }

    const res = await fetch(`/api/v1/tasks/${id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      credentials: 'include',
      body: JSON.stringify(payload),
    })

    if (!res.ok) {
      const json = await res.json().catch(() => ({}))
      throw new Error(json.message || 'Failed to save task')
    }

    await fetchTask()
    isEditing.value = false
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to save task'
  }
}

async function postAction(action) {
  try {
    const res = await fetch(`/api/v1/tasks/${id}/${action}`, { method: 'POST', headers: { Accept: 'application/json' }, credentials: 'include' })
    if (!res.ok) throw new Error(`${action} failed`)
    await fetchTask()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Action failed'
  }
}

function pause() { postAction('pause') }
function resume() { postAction('resume') }
function cancelAction() { postAction('cancel') }

onMounted(() => fetchTask())
// Poll task status when running/pending
onMounted(() => {
  pollInterval = setInterval(() => {
    if (task.value && ['pending', 'running'].includes(task.value.status)) {
      fetchTask()
    }
  }, 5000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>
