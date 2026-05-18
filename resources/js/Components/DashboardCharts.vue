<template>
  <div class="dashboard-charts">
    <div class="chart-grid">
      <div class="chart-card">
        <h3>Message Volume</h3>
        <div class="chart-placeholder">
          <div class="bar-chart">
            <div
              v-for="(bar, i) in messageVolumeData"
              :key="i"
              class="bar"
              :style="{ height: bar.height + '%' }"
              :title="bar.label"
            ></div>
          </div>
          <div class="chart-labels">
            <span v-for="(bar, i) in messageVolumeData" :key="i">{{ bar.label }}</span>
          </div>
        </div>
      </div>

      <div class="chart-card">
        <h3>Agent Performance</h3>
        <div class="chart-placeholder">
          <div class="donut-chart">
            <svg viewBox="0 0 36 36" class="donut">
              <path
                v-for="(segment, i) in agentPerformanceData"
                :key="i"
                :d="segment.path"
                :fill="segment.color"
              />
            </svg>
            <div class="donut-center">{{ totalTasks }}</div>
          </div>
          <div class="chart-legend">
            <div v-for="(segment, i) in agentPerformanceData" :key="i" class="legend-item">
              <span class="legend-color" :style="{ background: segment.color }"></span>
              <span class="legend-label">{{ segment.label }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="chart-card">
        <h3>Response Times</h3>
        <div class="chart-placeholder">
          <div class="line-chart">
            <svg viewBox="0 0 100 50" preserveAspectRatio="none" class="line-svg">
              <polyline
                :points="responseTimePoints"
                fill="none"
                stroke="#4ade80"
                stroke-width="2"
              />
            </svg>
          </div>
          <div class="chart-labels">
            <span>Last 7 days</span>
          </div>
        </div>
      </div>

      <div class="chart-card">
        <h3>Workflow Status</h3>
        <div class="status-grid">
          <div class="status-item">
            <span class="status-value">{{ workflowStats.running || 0 }}</span>
            <span class="status-label">Running</span>
          </div>
          <div class="status-item">
            <span class="status-value">{{ workflowStats.completed || 0 }}</span>
            <span class="status-label">Completed</span>
          </div>
          <div class="status-item">
            <span class="status-value">{{ workflowStats.failed || 0 }}</span>
            <span class="status-label">Failed</span>
          </div>
          <div class="status-item">
            <span class="status-value">{{ workflowStats.pending || 0 }}</span>
            <span class="status-label">Pending</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const workflowStats = ref({
  running: 0,
  completed: 0,
  failed: 0,
  pending: 0,
})

const messageVolumeData = ref([
  { label: 'Mon', height: 40 },
  { label: 'Tue', height: 65 },
  { label: 'Wed', height: 55 },
  { label: 'Thu', height: 80 },
  { label: 'Fri', height: 70 },
  { label: 'Sat', height: 30 },
  { label: 'Sun', height: 45 },
])

const agentPerformanceData = ref([
  { label: 'Success', value: 75, color: '#4ade80' },
  { label: 'Warning', value: 15, color: '#fbbf24' },
  { label: 'Error', value: 10, color: '#f87171' },
])

const responseTimePoints = computed(() => {
  const points = []
  for (let i = 0; i <= 10; i++) {
    const x = i * 10
    const y = 50 - Math.random() * 30 - 10
    points.push(`${x},${y}`)
  }
  return points.join(' ')
})

const totalTasks = computed(() => {
  return agentPerformanceData.value.reduce((sum, s) => sum + s.value, 0)
})

onMounted(() => {
  // Placeholder: fetch real stats from API
  fetch('/api/v1/stats/workflows')
    .then(res => res.json())
    .then(data => {
      if (data.success && data.data) workflowStats.value = data.data
    })
    .catch(() => {})
})
</script>

<style scoped>
.dashboard-charts {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.chart-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.chart-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1.25rem;
}

.chart-card h3 {
  margin: 0 0 1rem 0;
  font-size: 0.9375rem;
  color: #ccc;
}

.chart-placeholder {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.bar-chart {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  height: 120px;
  padding: 0 0.5rem;
}

.bar {
  flex: 1;
  background: #4ade80;
  border-radius: 4px 4px 0 0;
  min-height: 4px;
  transition: height 0.3s ease;
}

.chart-labels {
  display: flex;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #666;
  text-align: center;
}

.chart-labels span {
  flex: 1;
}

.donut-chart {
  position: relative;
  width: 100px;
  height: 100px;
  margin: 0 auto;
}

.donut {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.donut path {
  stroke-width: 3;
}

.donut-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 1.25rem;
  font-weight: bold;
  color: #fff;
}

.chart-legend {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  margin-top: 0.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #888;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.line-chart {
  height: 100px;
  position: relative;
}

.line-svg {
  width: 100%;
  height: 100%;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.status-item {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 6px;
  padding: 0.75rem;
  text-align: center;
}

.status-value {
  display: block;
  font-size: 1.5rem;
  font-weight: bold;
  color: #4ade80;
}

.status-label {
  display: block;
  font-size: 0.75rem;
  color: #888;
  margin-top: 0.25rem;
}

@media (max-width: 768px) {
  .chart-grid {
    grid-template-columns: 1fr;
  }
}
</style>
