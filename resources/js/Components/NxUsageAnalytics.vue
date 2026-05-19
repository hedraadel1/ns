<template>
  <NxGlassCard class="usage-analytics-card" elevation="2" hoverable>
    <div class="analytics-header">
      <div>
        <p class="subtitle">Usage analytics</p>
        <h2>Token, cost & intent metrics</h2>
      </div>
      <div class="range-controls">
        <button
          v-for="option in rangeOptions"
          :key="option.value"
          :class="['range-button', { active: range === option.value }]"
          @click="setRange(option.value)"
          :disabled="loading"
        >
          {{ option.label }}
        </button>
      </div>

      <div v-if="range === 'custom'" class="custom-range">
        <label>
          From
          <input type="date" v-model="customFrom" :disabled="loading" />
        </label>
        <label>
          To
          <input type="date" v-model="customTo" :disabled="loading" />
        </label>
        <button class="apply-button" @click="applyCustomRange" :disabled="loading || !customFrom || !customTo">
          Apply
        </button>
        <p v-if="customError" class="custom-error">{{ customError }}</p>
      </div>
    </div>

    <div class="analytics-grid">
      <section class="chart-card">
        <div class="chart-card-header">
          <h3>Token usage</h3>
          <span>Daily token volume</span>
        </div>
        <VChart :option="tokenOptions" autoresize class="chart-canvas" />
      </section>

      <section class="chart-card">
        <div class="chart-card-header">
          <h3>Provider calls</h3>
          <span>Calls by provider</span>
        </div>
        <VChart :option="providerOptions" autoresize class="chart-canvas" />
      </section>

      <section class="chart-card">
        <div class="chart-card-header">
          <h3>Estimated cost</h3>
          <span>Daily cost trend</span>
        </div>
        <VChart :option="costOptions" autoresize class="chart-canvas" />
      </section>

      <section class="chart-card">
        <div class="chart-card-header">
          <h3>Top intents</h3>
          <span>Most frequent AI intents</span>
        </div>
        <VChart :option="intentOptions" autoresize class="chart-canvas" />
      </section>
    </div>

    <div class="analytics-footer">
      <p class="status-text" v-if="error">{{ error }}</p>
      <p class="status-text" v-else-if="loading">Loading latest usage data…</p>
      <p class="status-text" v-else>Updated {{ lastUpdatedLabel }}</p>
    </div>
  </NxGlassCard>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import NxGlassCard from './NxGlassCard.vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart, BarChart, PieChart } from 'echarts/charts'
import { TitleComponent, TooltipComponent, GridComponent, LegendComponent, ToolboxComponent } from 'echarts/components'

use([CanvasRenderer, LineChart, BarChart, PieChart, TitleComponent, TooltipComponent, GridComponent, LegendComponent, ToolboxComponent])

const range = ref('7d')
const data = ref({})
const error = ref('')
const lastUpdatedAt = ref(null)
const refreshTimer = ref(null)
const loading = ref(false)
const customError = ref('')
const customFrom = ref(new Date(Date.now() - 6 * 86400000).toISOString().slice(0, 10))
const customTo = ref(new Date().toISOString().slice(0, 10))

const rangeOptions = [
  { label: 'Today', value: 'today' },
  { label: '7d', value: '7d' },
  { label: '30d', value: '30d' },
  { label: 'Custom', value: 'custom' },
]

const tokenOptions = computed(() => {
  const points = data.value.token_usage ?? []
  return {
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: points.map((item) => item.date) },
    yAxis: { type: 'value', name: 'Tokens' },
    series: [
      {
        type: 'line',
        smooth: true,
        data: points.map((item) => item.value),
        areaStyle: { opacity: 0.14 },
        lineStyle: { width: 3 },
        itemStyle: { color: '#60A5FA' },
      },
    ],
  }
})

const providerOptions = computed(() => {
  const list = data.value.provider_calls ?? []
  return {
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: list.map((item) => item.provider) },
    yAxis: { type: 'value', name: 'Calls' },
    series: [
      {
        type: 'bar',
        data: list.map((item) => item.count),
        itemStyle: { color: '#34D399' },
        barCategoryGap: '40%',
      },
    ],
  }
})

const costOptions = computed(() => {
  const points = data.value.cost_estimate ?? []
  return {
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: points.map((item) => item.date) },
    yAxis: { type: 'value', name: 'USD' },
    series: [
      {
        type: 'line',
        smooth: true,
        data: points.map((item) => item.amount),
        areaStyle: { opacity: 0.18 },
        itemStyle: { color: '#F59E0B' },
      },
    ],
  }
})

const intentOptions = computed(() => {
  const items = data.value.top_intents ?? []
  return {
    tooltip: { trigger: 'item' },
    legend: { orient: 'vertical', left: 'left', textStyle: { color: '#cbd5e1' } },
    series: [
      {
        type: 'pie',
        radius: ['40%', '70%'],
        label: { show: false },
        data: items.map((item) => ({ name: item.intent, value: item.count })),
      },
    ],
  }
})

const lastUpdatedLabel = computed(() => {
  if (!lastUpdatedAt.value) return 'No recent update'
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(lastUpdatedAt.value)
})

function setRange(value) {
  if (range.value === value) return
  range.value = value
  customError.value = ''
  if (value !== 'custom') {
    loadUsage()
  }
}

function applyCustomRange() {
  customError.value = ''
  if (!customFrom.value || !customTo.value) {
    customError.value = 'Please select both dates.'
    return
  }

  if (customFrom.value > customTo.value) {
    customError.value = 'Start date must be before or equal to end date.'
    return
  }

  range.value = 'custom'
  loadUsage()
}

async function loadUsage() {
  error.value = ''
  customError.value = ''
  loading.value = true

  try {
    const params = new URLSearchParams({ range: range.value })
    if (range.value === 'custom') {
      params.set('from', customFrom.value)
      params.set('to', customTo.value)
    }

    const response = await fetch(`/api/v1/stats/usage?${params.toString()}`, {
      headers: { Accept: 'application/json' },
    })

    if (!response.ok) throw new Error(`Data request failed with ${response.status}`)

    const payload = await response.json()
    const payloadData = payload?.data ?? payload

    data.value = {
      token_usage: payloadData?.token_usage ?? payloadData?.usage?.tokens ?? [],
      provider_calls: payloadData?.provider_calls ?? payloadData?.usage?.providers ?? [],
      cost_estimate: payloadData?.cost_estimate ?? payloadData?.usage?.cost ?? [],
      top_intents: payloadData?.top_intents ?? payloadData?.usage?.intents ?? [],
    }
    lastUpdatedAt.value = new Date()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load usage analytics.'
  } finally {
    loading.value = false
  }
}

function startRefresh() {
  stopRefresh()
  refreshTimer.value = window.setInterval(loadUsage, 60000)
}

function stopRefresh() {
  if (refreshTimer.value) {
    clearInterval(refreshTimer.value)
    refreshTimer.value = null
  }
}

onMounted(() => {
  loadUsage()
  startRefresh()
})

onBeforeUnmount(() => {
  stopRefresh()
})
</script>

<style scoped>
.usage-analytics-card {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.5rem;
}

.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.subtitle {
  margin: 0;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #38bdf8;
}

.analytics-header h2 {
  margin: 0.25rem 0 0;
  font-size: 1.35rem;
}

.range-controls {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.custom-range {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
  margin-top: 0.75rem;
}

.custom-range label {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  color: #cbd5e1;
  font-size: 0.85rem;
}

.custom-range input {
  min-width: 170px;
  padding: 0.55rem 0.75rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(15, 23, 42, 0.96);
  color: #f8fafc;
}

.apply-button {
  padding: 0.75rem 1rem;
  border-radius: 999px;
  border: none;
  background: #38bdf8;
  color: #0f172a;
  font-weight: 700;
  cursor: pointer;
  transition: background 150ms ease;
}

.apply-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.custom-error {
  flex-basis: 100%;
  margin: 0;
  color: #fca5a5;
  font-size: 0.85rem;
}

.range-button {
  padding: 0.65rem 1rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  background: rgba(255, 255, 255, 0.04);
  cursor: pointer;
  transition: background 150ms ease, border-color 150ms ease;
}

.range-button.active {
  border-color: rgba(56, 189, 248, 0.5);
  background: rgba(56, 189, 248, 0.18);
  color: #ffffff;
}

.analytics-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.chart-card {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  padding: 1rem;
  border-radius: 20px;
  background: rgba(15, 23, 42, 0.88);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.chart-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.5rem;
}

.chart-card-header h3 {
  margin: 0;
  font-size: 1rem;
  color: #f8fafc;
}

.chart-card-header span {
  color: #94a3b8;
  font-size: 0.85rem;
}

.chart-canvas {
  min-height: 260px;
}

.analytics-footer {
  display: flex;
  justify-content: flex-end;
}

.status-text {
  margin: 0;
  color: #94a3b8;
  font-size: 0.92rem;
}

@media (max-width: 980px) {
  .analytics-grid {
    grid-template-columns: 1fr;
  }
}
</style>
