<template>
  <div class="dashboard-view">
    <h1>Dashboard</h1>
    <p class="subtitle">Platform overview and key metrics.</p>

    <div class="kpi-grid">
      <NxGlassCard class="kpi-card">
        <span class="kpi-label">Total Contacts</span>
        <span class="kpi-value">{{ stats.contacts ?? 0 }}</span>
      </NxGlassCard>
      <NxGlassCard class="kpi-card">
        <span class="kpi-label">Active Agents</span>
        <span class="kpi-value">{{ stats.agents ?? 0 }}</span>
      </NxGlassCard>
      <NxGlassCard class="kpi-card">
        <span class="kpi-label">Running Workflows</span>
        <span class="kpi-value">{{ stats.workflows ?? 0 }}</span>
      </NxGlassCard>
      <NxGlassCard class="kpi-card">
        <span class="kpi-label">Tasks Today</span>
        <span class="kpi-value">{{ stats.tasks ?? 0 }}</span>
      </NxGlassCard>
    </div>

    <div class="dashboard-grid">
      <NxGlassCard class="panel">
        <h2>Recent Activity</h2>
        <p class="placeholder-text">Activity feed coming soon.</p>
      </NxGlassCard>
      <NxGlassCard class="panel">
        <h2>System Health</h2>
        <p class="placeholder-text">Health metrics coming soon.</p>
      </NxGlassCard>
      <NxUsageAnalytics class="panel" />
      <NxAiSummary class="panel" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import NxGlassCard from '../Components/NxGlassCard.vue'
import NxUsageAnalytics from '../Components/NxUsageAnalytics.vue'
import NxAiSummary from '../Components/NxAiSummary.vue'

const stats = ref({
  contacts: 0,
  agents: 0,
  workflows: 0,
  tasks: 0,
})

onMounted(() => {
  // Placeholder: fetch real stats from API
  fetch('/api/v1/stats/dashboard')
    .then(res => res.json())
    .then(data => {
      if (data.success && data.data) stats.value = data.data
    })
    .catch(() => {})
})
</script>

<style scoped>
.dashboard-view {
  max-width: 1200px;
}

.subtitle {
  color: #888;
  margin-bottom: 2rem;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1.25rem;
  text-align: center;
}

.kpi-label {
  display: block;
  font-size: 0.875rem;
  color: #888;
  margin-bottom: 0.5rem;
}

.kpi-value {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  color: #4ade80;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(400px, 100%), 1fr));
  gap: 1rem;
}

.panel {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1.25rem;
}

.panel h2 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: #ccc;
}

.placeholder-text {
  color: #666;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}
</style>
