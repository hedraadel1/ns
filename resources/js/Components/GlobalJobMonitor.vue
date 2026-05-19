<template>
  <section class="global-job-monitor">
    <header class="monitor-header">
      <div>
        <h3>Job Monitor</h3>
        <p class="monitor-subtitle">Live queue and DLQ updates for admin users.</p>
      </div>
      <div class="monitor-stats">
        <span>Pending: {{ stats.pending }}</span>
        <span>Failed: {{ stats.failed }}</span>
        <span>Recent: {{ stats.recentFailures }}</span>
      </div>
    </header>

    <div class="log-panel">
      <div v-if="events.length === 0" class="empty-state">
        Waiting for queue updates...
      </div>
      <div v-else class="event-list">
        <div v-for="event in events" :key="event.id" class="event-item">
          <span class="event-time">{{ event.timestamp }}</span>
          <span class="event-message">{{ event.message }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useEcho } from '../composables/useEcho';

const { isAvailable, subscribePrivate, leaveChannel, withPollingFallback } = useEcho();
const stats = ref({ pending: 0, failed: 0, recentFailures: 0 });
const events = ref([]);
let channel = null;
const channelName = 'admin.dlq';

async function fetchMetrics() {
  try {
    const res = await fetch('/api/v1/monitoring/health');
    const data = await res.json();
    const queue = data.checks?.queue || {};
    const totals = queue.queues || {};
    stats.value.pending = Object.values(totals).reduce((sum, count) => sum + (Number(count) || 0), 0);
    stats.value.failed = queue.failed_jobs ?? 0;
    stats.value.recentFailures = queue.failed_jobs ?? 0;
  } catch (error) {
    events.value.unshift({
      id: Date.now(),
      timestamp: new Date().toLocaleTimeString(),
      message: 'Unable to load monitoring metrics',
    });
  }
}

function addEvent(message) {
  events.value.unshift({
    id: Date.now() + events.value.length,
    timestamp: new Date().toLocaleTimeString(),
    message,
  });
  if (events.value.length > 20) {
    events.value.splice(20);
  }
}

function subscribeAdminChannel() {
  if (!isAvailable()) {
    addEvent('WebSocket connection is not available in this browser. Falling back to polling.');
    fetchMetrics();
    return;
  }

  channel = withPollingFallback(
    () => subscribePrivate(
      channelName,
      {
        '.App\\Events\\JobFailedEvent': (payload) => {
          addEvent(`Job failed: ${payload.job_class} (${payload.queue}) - ${payload.error_message}`);
          fetchMetrics();
        },
      },
      () => {
        addEvent('Subscribed to DLQ admin channel.');
        fetchMetrics();
      }
    ),
    () => {
      addEvent('WebSocket subscription failed; using polling for DLQ metrics.');
      fetchMetrics();
      return null;
    }
  );
}

onMounted(async () => {
  await fetchMetrics();
  subscribeAdminChannel();
});

onUnmounted(() => {
  if (channel) {
    channel.stopListening('.App\\Events\\JobFailedEvent');
  }

  leaveChannel(channelName);
});
</script>

<style scoped>
.global-job-monitor {
  width: 100%;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 1rem;
  padding: 1rem;
  color: #f8fafc;
  margin-top: 1rem;
}

.monitor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.monitor-header h3 {
  margin: 0;
  font-size: 1rem;
}

.monitor-subtitle {
  margin: 0.25rem 0 0;
  color: #94a3b8;
  font-size: 0.85rem;
}

.monitor-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  font-size: 0.85rem;
}

.log-panel {
  min-height: 160px;
}

.empty-state {
  color: #94a3b8;
  padding: 1rem;
}

.event-list {
  display: grid;
  gap: 0.5rem;
}

.event-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 0.75rem;
}

.event-time {
  color: #94a3b8;
  min-width: 70px;
}

.event-message {
  flex: 1;
}
</style>
