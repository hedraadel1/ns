<template>
  <section class="nx-emotion-radar">
    <div class="radar-header">
      <p class="radar-title">Emotional baseline</p>
      <p class="radar-subtitle">6-axis sentiment profile</p>
    </div>
    <div class="radar-grid">
      <div v-for="axis in axisLabels" :key="axis" class="radar-row">
        <span class="axis-label">{{ axis }}</span>
        <div class="axis-track">
          <div class="axis-fill" :style="{ width: `${Math.round(values[axis] * 100)}%` }"></div>
        </div>
        <span class="axis-value">{{ Math.round(values[axis] * 100) }}%</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  baseline: {
    type: Object,
    default: () => ({}),
  },
})

const axisLabels = ['Joy', 'Trust', 'Anticipation', 'Surprise', 'Sadness', 'Anger']

const values = computed(() => {
  const result = {}
  axisLabels.forEach((axis) => {
    const raw = props.baseline?.[axis.toLowerCase()] ?? props.baseline?.[axis] ?? 0
    result[axis] = Math.min(1, Math.max(0, Number(raw) || 0))
  })
  return result
})
</script>

<style scoped>
.nx-emotion-radar {
  background: rgba(15, 23, 42, 0.86);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 1rem;
  padding: 1rem;
}

.radar-header {
  margin-bottom: 1rem;
}

.radar-title {
  color: #f8fafc;
  font-size: 0.95rem;
  font-weight: 700;
}

.radar-subtitle {
  color: #94a3b8;
  font-size: 0.75rem;
}

.radar-grid {
  display: grid;
  gap: 0.75rem;
}

.radar-row {
  align-items: center;
  display: grid;
  grid-template-columns: 1fr 1.75fr auto;
  gap: 0.75rem;
}

.axis-label {
  color: #cbd5e1;
  font-size: 0.8rem;
  text-transform: uppercase;
}

.axis-track {
  background: rgba(148, 163, 184, 0.12);
  border-radius: 9999px;
  height: 10px;
  overflow: hidden;
}

.axis-fill {
  background: linear-gradient(90deg, #6366f1, #0ea5e9);
  height: 100%;
  transition: width 0.5s ease;
}

.axis-value {
  color: #f8fafc;
  font-size: 0.75rem;
  min-width: 38px;
  text-align: right;
}
</style>
