<template>
  <div class="nx-presence-dot" :class="statusClass" :title="tooltipText"></div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  lastSeenAt: {
    type: [String, Date],
    default: null,
  },
})

const parsedDate = computed(() => {
  if (!props.lastSeenAt) return null
  const date = new Date(props.lastSeenAt)
  return Number.isNaN(date.getTime()) ? null : date
})

const statusClass = computed(() => {
  if (!parsedDate.value) return 'present-unknown'
  const now = Date.now()
  const diff = now - parsedDate.value.getTime()
  if (diff <= 86400000) return 'present-today'
  if (diff <= 604800000) return 'present-week'
  if (diff <= 2592000000) return 'present-month'
  return 'present-old'
})

const tooltipText = computed(() => {
  if (!parsedDate.value) return 'Last active: unknown'
  const seconds = Math.floor((Date.now() - parsedDate.value.getTime()) / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `Last active: ${hours}h ago`
  if (minutes < 60) return `Last active: ${minutes}m ago`
  if (seconds < 60) return 'Last active: moments ago'
  return `Last active: ${parsedDate.value.toLocaleDateString()}`
})
</script>

<style scoped>
.nx-presence-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.18);
}

.present-today {
  background: #10b981;
  animation: pulse-emerald 2s ease-in-out infinite;
}

.present-week {
  background: #f59e0b;
}

.present-month {
  background: #64748b;
}

.present-old,
.present-unknown {
  background: #475569;
}

@keyframes pulse-emerald {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.4); opacity: 0.75; }
}
</style>
