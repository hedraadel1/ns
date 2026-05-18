<template>
  <div class="contact-analytics">
    <h3>Contact Analytics</h3>

    <div v-if="!contactId" class="no-contact">
      <p>Select a contact to view analytics.</p>
    </div>

    <div v-else class="analytics-content">
      <div class="stat-row">
        <div class="stat">
          <span class="stat-value">{{ stats.totalMessages || 0 }}</span>
          <span class="stat-label">Messages</span>
        </div>
        <div class="stat">
          <span class="stat-value">{{ stats.avgResponseTime || '0m' }}</span>
          <span class="stat-label">Avg Response</span>
        </div>
      </div>

      <div class="stat-row">
        <div class="stat">
          <span class="stat-value">{{ stats.sentiment || 'N/A' }}</span>
          <span class="stat-label">Sentiment</span>
        </div>
        <div class="stat">
          <span class="stat-value">{{ stats.engagement || 'N/A' }}</span>
          <span class="stat-label">Engagement</span>
        </div>
      </div>

      <div class="analytics-section">
        <h4>Message Trend</h4>
        <div class="trend-chart">
          <div
            v-for="(bar, i) in trendData"
            :key="i"
            class="trend-bar"
            :style="{ height: bar.height + '%' }"
            :title="bar.label"
          ></div>
        </div>
        <div class="trend-labels">
          <span v-for="(bar, i) in trendData" :key="i">{{ bar.label }}</span>
        </div>
      </div>

      <div class="analytics-section">
        <h4>Top Topics</h4>
        <div v-if="topics.length === 0" class="empty-topics">
          <p>No topics detected yet.</p>
        </div>
        <div v-else class="topics-list">
          <div v-for="topic in topics" :key="topic.name" class="topic-item">
            <span class="topic-name">{{ topic.name }}</span>
            <div class="topic-bar">
              <div class="topic-fill" :style="{ width: topic.percentage + '%' }"></div>
            </div>
            <span class="topic-count">{{ topic.count }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  contactId: {
    type: Number,
    default: null,
  },
})

const stats = ref({
  totalMessages: 0,
  avgResponseTime: '0m',
  sentiment: 'N/A',
  engagement: 'N/A',
})

const trendData = ref([
  { label: 'Mon', height: 30 },
  { label: 'Tue', height: 50 },
  { label: 'Wed', height: 40 },
  { label: 'Thu', height: 70 },
  { label: 'Fri', height: 60 },
  { label: 'Sat', height: 20 },
  { label: 'Sun', height: 35 },
])

const topics = ref([
  { name: 'Support', count: 12, percentage: 80 },
  { name: 'Billing', count: 5, percentage: 40 },
  { name: 'General', count: 8, percentage: 60 },
])

watch(() => props.contactId, (newId) => {
  if (newId) loadAnalytics(newId)
})

onMounted(() => {
  if (props.contactId) loadAnalytics(props.contactId)
})

async function loadAnalytics(contactId) {
  try {
    const res = await fetch(`/api/v1/contacts/${contactId}/analytics`)
    const data = await res.json()
    if (data.success && data.data) {
      stats.value = { ...stats.value, ...data.data }
    }
  } catch (e) {
    // Use placeholder data
  }
}
</script>

<style scoped>
.contact-analytics {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.02);
}

.contact-analytics h3 {
  margin: 0 0 1rem 0;
  font-size: 0.9375rem;
  color: #ccc;
}

.no-contact {
  text-align: center;
  color: #666;
  padding: 2rem 1rem;
  font-size: 0.875rem;
}

.analytics-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.stat-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.stat {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 6px;
  padding: 0.75rem;
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 1.25rem;
  font-weight: bold;
  color: #4ade80;
}

.stat-label {
  display: block;
  font-size: 0.6875rem;
  color: #888;
  margin-top: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.analytics-section {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 6px;
  padding: 0.75rem;
}

.analytics-section h4 {
  margin: 0 0 0.75rem 0;
  font-size: 0.8125rem;
  color: #ccc;
}

.trend-chart {
  display: flex;
  align-items: flex-end;
  gap: 0.375rem;
  height: 60px;
  margin-bottom: 0.375rem;
}

.trend-bar {
  flex: 1;
  background: #4ade80;
  border-radius: 2px 2px 0 0;
  min-height: 4px;
}

.trend-labels {
  display: flex;
  gap: 0.375rem;
  font-size: 0.625rem;
  color: #666;
}

.trend-labels span {
  flex: 1;
  text-align: center;
}

.empty-topics {
  text-align: center;
  color: #666;
  font-size: 0.75rem;
  padding: 0.5rem;
}

.topics-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.topic-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.topic-name {
  font-size: 0.75rem;
  color: #ccc;
  min-width: 60px;
}

.topic-bar {
  flex: 1;
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  overflow: hidden;
}

.topic-fill {
  height: 100%;
  background: #4ade80;
}

.topic-count {
  font-size: 0.6875rem;
  color: #888;
  min-width: 20px;
  text-align: right;
}
</style>
