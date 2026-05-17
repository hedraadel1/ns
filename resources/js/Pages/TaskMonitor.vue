<template>
  <section class="task-monitor">
    <h1>Task Monitor</h1>
    <p>Dashboard for viewing tasks in flight with real-time status.</p>
    <div class="monitor-stats">
      <div class="stat-card">
        <span class="stat-label">Queued</span>
        <span class="stat-value">{{ stats.queued ?? 0 }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Running</span>
        <span class="stat-value">{{ stats.running ?? 0 }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Completed</span>
        <span class="stat-value">{{ stats.completed ?? 0 }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Failed</span>
        <span class="stat-value">{{ stats.failed ?? 0 }}</span>
      </div>
    </div>
    <div class="active-tasks">
      <h2>Active Tasks</h2>
      <p>Active tasks will be listed here with progress indicators.</p>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const stats = ref({
  queued: 0,
  running: 0,
  completed: 0,
  failed: 0,
})

onMounted(() => {
  // Poll for task stats
  fetch('/api/v1/tasks/stats')
    .then(res => res.json())
    .then(data => {
      if (data.data) stats.value = data.data
    })
    .catch(() => {})
})
</script>

<style scoped>
.task-monitor {
  padding: 1rem;
}

.monitor-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin: 1rem 0;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
}

.stat-label {
  display: block;
  font-size: 0.875rem;
  color: #888;
}

.stat-value {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  color: #fff;
}

.active-tasks {
  margin-top: 2rem;
}
</style>