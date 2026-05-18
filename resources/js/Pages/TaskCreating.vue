<template>
  <section class="p-4">
    <div class="max-w-xl mx-auto">
      <LoadingSpinner :message="`Creating task: ${title}`" />
      <div class="mt-4 text-sm text-gray-400">Waiting for the server to confirm creation...</div>
      <div v-if="error" class="mt-4">
        <ErrorPanel :message="error" @retry="startPolling" />
        <div class="mt-3">
          <router-link to="/tasks" class="px-3 py-2 bg-gray-200 rounded">Back to tasks</router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LoadingSpinner from '../Components/LoadingSpinner.vue'
import ErrorPanel from '../Components/ErrorPanel.vue'

const route = useRoute()
const router = useRouter()
const token = route.query.token || ''
const title = route.query.title || ''
const error = ref('')
let pollId = null
let attempts = 0

async function poll() {
  if (!title) {
    error.value = 'No task title provided for optimistic creation.'
    return
  }

  try {
    let url = ''
    if (token) {
      url = `/api/v1/tasks?metadata_token=${encodeURIComponent(token)}&per_page=10`
    } else {
      url = `/api/v1/tasks?search=${encodeURIComponent(title)}&per_page=10`
    }
    const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'include' })
    if (!res.ok) throw new Error('Search failed')
    const json = await res.json()
    const items = (json.data && json.data.data) ? json.data.data : (json.data ?? json)
    let found = null
    if (token) {
      found = (items || []).find(t => t.metadata && t.metadata.client_token === token)
    } else {
      found = (items || []).find(t => t.title === title)
    }
    if (found && found.id) {
      router.replace(`/tasks/${found.id}`)
      return
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Error searching for task'
  }

  attempts++
  if (attempts > 30) { // ~30 * 2s = 60s timeout
    error.value = 'Timeout waiting for task creation confirmation.'
    return
  }

  pollId = setTimeout(poll, 2000)
}

function startPolling() {
  error.value = ''
  attempts = 0
  if (pollId) clearTimeout(pollId)
  poll()
}

onMounted(() => startPolling())
onUnmounted(() => { if (pollId) clearTimeout(pollId) })
</script>
