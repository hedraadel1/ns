<template>
  <svg class="nx-token-meter" viewBox="0 0 100 4" preserveAspectRatio="none">
    <rect class="bg" x="0" y="0" width="100" height="4" rx="2" />
    <rect class="fill" x="0" y="0" :width="barWidth" height="4" rx="2" :fill="thresholdColor" />
  </svg>
</template>

<script setup>
defineProps({
  currentTokens: {
    type: Number,
    default: 0,
  },
  maxTokens: {
    type: Number,
    default: 6000,
  },
});

const percentage = computed(() => Math.min(props.currentTokens / props.maxTokens, 1));
const barWidth = computed(() => `${percentage.value * 100}%`);
const thresholdColor = computed(() => {
  if (percentage.value < 0.7) return '#007AFF';
  if (percentage.value < 0.9) return '#F59E0B';
  return '#EF4444';
});
</script>

<style scoped>
.nx-token-meter {
  width: 100%;
  height: 4px;
  display: block;
}

.bg {
  fill: rgba(255, 255, 255, 0.1);
}

.fill {
  transition: width 300ms ease, fill 300ms ease;
}
</style>
