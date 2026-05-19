<template>
  <Teleport to="body">
    <div v-if="open" class="provider-health-overlay" @click.self="close">
      <div class="provider-health-panel">
        <header class="panel-header">
          <h3>Connection status</h3>
          <button type="button" class="close-btn" @click="close">×</button>
        </header>

        <div class="status-block">
          <p class="status-label">{{ statusLabel }}</p>
          <p class="status-description">{{ description }}</p>
        </div>

        <div v-if="details" class="details-block">
          <h4>Details</h4>
          <pre>{{ prettyJson(details) }}</pre>
        </div>

        <div class="footer-actions">
          <button type="button" class="close-action" @click="close">Close</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { Teleport, defineEmits, defineProps } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  status: { type: String, default: 'pending' },
  description: { type: String, default: '' },
  details: { type: [Object, String], default: null },
})
const emit = defineEmits(['close'])

const statusLabel = props.status === 'success' ? 'Connected' : props.status === 'error' ? 'Connection failed' : 'Pending verification'

function prettyJson(value) {
  try {
    return typeof value === 'string' ? value : JSON.stringify(value, null, 2)
  } catch {
    return String(value)
  }
}

function close() {
  emit('close')
}
</script>

<style scoped>
.provider-health-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.72);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1400;
}

.provider-health-panel {
  width: min(520px, 100%);
  border-radius: 24px;
  background: rgba(10, 14, 24, 0.98);
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 45px 130px rgba(0, 0, 0, 0.35);
  padding: 1.5rem;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.panel-header h3 {
  margin: 0;
  color: #f8fafc;
}

.close-btn,
.close-action {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.04);
  color: #fff;
  border-radius: 14px;
  padding: 0.75rem 1rem;
  cursor: pointer;
}

.status-block {
  border-radius: 20px;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.92);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.status-label {
  margin: 0;
  font-size: 1rem;
  color: #38bdf8;
}

.status-description {
  margin: 0.5rem 0 0;
  color: #cbd5e1;
}

.details-block {
  margin-top: 1rem;
}

.details-block h4 {
  margin: 0 0 0.75rem;
  color: #f8fafc;
}

.details-block pre {
  margin: 0;
  padding: 1rem;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.04);
  color: #cbd5e1;
  max-height: 260px;
  overflow: auto;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}

.footer-actions {
  margin-top: 1.5rem;
  display: flex;
  justify-content: flex-end;
}
</style>
