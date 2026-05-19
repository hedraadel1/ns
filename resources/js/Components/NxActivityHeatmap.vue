<template>
  <div class="nx-activity-heatmap">
    <div class="heatmap-grid">
      <div
        v-for="(cell, index) in heatmap"
        :key="cell.date"
        class="heatmap-cell"
        :class="heatClass(cell.count)"
        :style="{ animationDelay: `${index * 30}ms` }"
        :title="`${cell.date}: ${cell.count} interactions`"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  contactId: {
    type: String,
    default: '',
  },
  data: {
    type: Array,
    default: () => [],
  },
})

const heatmap = computed(() => {
  const entries = props.data.slice(0, 53 * 7)
  if (entries.length) return entries
  const now = new Date()
  return Array.from({ length: 53 * 7 }, (_, index) => {
    const date = new Date(now)
    date.setDate(now.getDate() - index)
    return {
      date: date.toISOString().slice(0, 10),
      count: Math.floor(Math.random() * 10),
    }
  }).reverse()
})

function heatClass(count) {
  if (count >= 8) return 'heat-max'
  if (count >= 4) return 'heat-high'
  if (count >= 1) return 'heat-medium'
  return 'heat-empty'
}
</script>

<style scoped>
.nx-activity-heatmap {
  width: 100%;
  padding: 0.75rem;
  background: rgba(15, 23, 42, 0.88);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 1rem;
}

.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(53, 1fr);
  gap: 2px;
}

.heatmap-cell {
  width: 10px;
  height: 10px;
  border-radius: 2px;
  opacity: 0;
  animation: fade-in 600ms ease forwards;
}

.heat-empty {
  background: rgba(148, 163, 184, 0.12);
}

.heat-medium {
  background: rgba(59, 130, 246, 0.35);
}

.heat-high {
  background: rgba(59, 130, 246, 0.65);
}

.heat-max {
  background: rgba(37, 99, 235, 0.95);
}

@keyframes fade-in {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
