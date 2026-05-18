<template>
  <div class="task-panel">
    <div class="panel-header">
      <h3>Active Tasks</h3>
      <span class="task-count">{{ activeTasks.length }} running</span>
    </div>

    <div v-if="error" class="error-state">
      <p>{{ error }}</p>
    </div>

    <div v-else-if="activeTasks.length === 0" class="empty-state">
      <p>No active tasks.</p>
    </div>

    <div v-else class="task-list">
      <div
        v-for="task in activeTasks"
        :key="task.id"
        class="task-item"
      >
        <div class="task-info">
          <span class="task-name">{{ task.name || 'Task #' + task.id }}</span>
          <span class="task-agent">{{ task.agent_name || 'Unassigned' }}</span>
        </div>
        <div class="task-progress">
          <div class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: task.progress + '%' }"
            ></div>
          </div>
          <span class="progress-text">{{ task.progress }}%</span>
        </div>
        <div class="task-meta">
          <span class="task-status" :class="task.status">{{ task.status }}</span>
          <span class="task-time">{{ formatTime(task.started_at) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const activeTasks = ref([])
const error = ref('')
let pollInterval = null

onMounted(() => {
  loadTasks()
  pollInterval = setInterval(loadTasks, 5000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})

async function loadTasks() {
  try {
    const res = await fetch('/api/v1/tasks/active')
    const data = await res.json()

    if (data.success) {
      activeTasks.value = Array.isArray(data.data) ? data.data : []
      error.value = ''
      return
    }

    activeTasks.value = []
    error.value = data.message || 'Failed to load active tasks.'
    if (window.$toast) {
      window.$toast.error(error.value, 'Task Loading Error')
    }
  } catch (e) {
    activeTasks.value = []
    error.value = 'Unable to load active tasks.'
    if (window.$toast) {
      window.$toast.error(error.value, 'Task Loading Error')
    }
  }
}

function formatTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.task-panel {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.panel-header h3 {
  margin: 0;
  font-size: 0.9375rem;
}

.task-count {
  font-size: 0.75rem;
  color: #888;
}

.empty-state,
.error-state {
  text-align: center;
  padding: 1rem;
  font-size: 0.875rem;
}

.empty-state {
  color: #666;
}

.error-state {
  color: #f87171;
  background: rgba(248, 113, 113, 0.08);
  border: 1px solid rgba(248, 113, 113, 0.2);
  border-radius: 6px;
}

.task-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.task-item {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 6px;
  padding: 0.75rem;
}

.task-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.task-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: #fff;
}

.task-agent {
  font-size: 0.75rem;
  color: #888;
}

.task-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #4ade80;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.75rem;
  color: #888;
  min-width: 35px;
  text-align: right;
}

.task-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
}

.task-status {
  padding: 0.125rem 0.5rem;
  border-radius: 4px;
  text-transform: capitalize;
}

.task-status.running {
  background: rgba(74, 222, 128, 0.1);
  color: #4ade80;
}

.task-status.pending {
  background: rgba(251, 191, 36, 0.1);
  color: #fbbf24;
}

.task-status.completed {
  background: rgba(96, 165, 250, 0.1);
  color: #60a5fa;
}

.task-status.failed {
  background: rgba(239, 68, 68, 0.1);
  color: #f87171;
}

.task-time {
  color: #666;
}
</style>
