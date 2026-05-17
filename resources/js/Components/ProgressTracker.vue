<template>
  <div class="progress-tracker">
    <div class="progress-header">
      <h3>{{ title }}</h3>
      <span class="progress-percent">{{ progress }}%</span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: progress + '%' }"></div>
    </div>
    <div class="progress-steps">
      <div
        v-for="(step, index) in steps"
        :key="index"
        class="step"
        :class="{
          completed: step.status === 'completed',
          running: step.status === 'running',
          failed: step.status === 'failed',
          pending: step.status === 'pending'
        }"
      >
        <span class="step-indicator">{{ index + 1 }}</span>
        <span class="step-name">{{ step.name || step.title || 'Step ' + (index + 1) }}</span>
        <span v-if="step.duration_ms" class="step-duration">{{ step.duration_ms }}ms</span>
      </div>
    </div>
    <div v-if="error" class="progress-error">
      <span class="error-icon">!</span>
      <span class="error-message">{{ error }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Workflow Progress'
  },
  workflowId: {
    type: Number,
    default: null
  },
  pollInterval: {
    type: Number,
    default: 5000
  }
})

const progress = ref(0)
const steps = ref([])
const error = ref(null)
let pollTimer = null

function fetchProgress() {
  if (!props.workflowId) return

  fetch(`/api/v1/workflows/${props.workflowId}/progress`)
    .then(res => res.json())
    .then(data => {
      if (data.data) {
        progress.value = data.data.progress || 0
        steps.value = data.data.step_results || []
        error.value = null
      }
    })
    .catch(err => {
      error.value = err.message
    })
}

onMounted(() => {
  fetchProgress()
  if (props.workflowId && props.pollInterval > 0) {
    pollTimer = setInterval(fetchProgress, props.pollInterval)
  }
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<style scoped>
.progress-tracker {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.progress-header h3 {
  margin: 0;
  font-size: 1rem;
}

.progress-percent {
  font-weight: bold;
  color: #4ade80;
}

.progress-bar {
  height: 8px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4ade80, #22c55e);
  transition: width 0.3s ease;
}

.progress-steps {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.step {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.02);
}

.step.completed .step-indicator {
  background: #4ade80;
  color: #000;
}

.step.running .step-indicator {
  background: #3b82f6;
  animation: pulse 1.5s infinite;
}

.step.failed .step-indicator {
  background: #ef4444;
}

.step.pending .step-indicator {
  background: #6b7280;
}

.step-indicator {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: bold;
  color: #fff;
}

.step-name {
  flex: 1;
  font-size: 0.875rem;
}

.step-duration {
  font-size: 0.75rem;
  color: #888;
}

.progress-error {
  margin-top: 1rem;
  padding: 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.error-icon {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #ef4444;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: bold;
}

.error-message {
  color: #fca5a5;
  font-size: 0.875rem;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>