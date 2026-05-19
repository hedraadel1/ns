<template>
  <div class="nx-engagement-ring">
    <svg viewBox="0 0 120 120" class="ring-svg">
      <circle class="ring-bg" cx="60" cy="60" r="54" />
      <circle
        class="ring-fill"
        cx="60"
        cy="60"
        r="54"
        :stroke-dashoffset="dashOffset"
        :style="{ color: ringColor }"
      />
    </svg>
    <div class="ring-labels">
      <div class="score">{{ animatedScore }}</div>
      <div class="trend">{{ trendText }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'

const props = defineProps({
  score: {
    type: Number,
    default: 0,
  },
  trend: {
    type: String,
    default: 'stable',
  },
})

const animatedScore = ref(0)
const circumference = 2 * Math.PI * 54
const fillAmount = computed(() => Math.min(Math.max(props.score, 0), 100))
const dashOffset = computed(() => circumference * (1 - fillAmount.value / 100))
const ringColor = computed(() => {
  if (props.score < 40) return '#ef4444'
  if (props.score < 70) return '#f59e0b'
  return '#10b981'
})

const trendText = computed(() => {
  if (props.trend === 'up') return 'Up'
  if (props.trend === 'down') return 'Down'
  return 'Stable'
})

watch(
  () => props.score,
  (next) => {
    const start = animatedScore.value
    const end = Math.round(Math.min(Math.max(next, 0), 100))
    const duration = 1200
    const startTime = performance.now()

    function animate(time) {
      const elapsed = time - startTime
      const progress = Math.min(elapsed / duration, 1)
      animatedScore.value = Math.round(start + (end - start) * progress)
      if (progress < 1) requestAnimationFrame(animate)
    }

    requestAnimationFrame(animate)
  },
  { immediate: true }
)

onMounted(() => {
  animatedScore.value = Math.round(fillAmount.value)
})
</script>

<style scoped>
.nx-engagement-ring {
  position: relative;
  width: 120px;
  height: 120px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.ring-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.ring-bg {
  fill: transparent;
  stroke: rgba(255,255,255,0.08);
  stroke-width: 8;
}

.ring-fill {
  fill: transparent;
  stroke: currentColor;
  stroke-width: 8;
  stroke-linecap: round;
  stroke-dasharray: 339.292;
  transition: stroke-dashoffset 1.2s ease-out;
  color: var(--ring-color);
}

.ring-labels {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #f8fafc;
}

.score {
  font-size: 1.75rem;
  font-weight: 800;
}

.trend {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: rgba(255,255,255,0.68);
}

:root {
  --ring-color: #10b981;
}

.nx-engagement-ring .ring-fill {
  color: #10b981;
}

.nx-engagement-ring[data-score="low"] .ring-fill {
  color: #ef4444;
}

.nx-engagement-ring[data-score="mid"] .ring-fill {
  color: #f59e0b;
}
</style>
