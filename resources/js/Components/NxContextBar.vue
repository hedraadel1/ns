<template>
  <div class="nx-context-bar">
    <div class="bar-label">
      <span>Context tokens</span>
      <span>{{ tokens }} / {{ maxTokens }}</span>
    </div>
    <div class="progress-track">
      <div class="progress-fill" :style="fillStyle"></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  percentage: { type: Number, default: 0 },
  tokens: { type: Number, default: 0 },
  maxTokens: { type: Number, default: 6000 },
});

const fillStyle = computed(() => ({ width: `${Math.min(Math.max(props.percentage, 0), 100)}%` }));
</script>

<style scoped>
.nx-context-bar {
  display: grid;
  gap: 0.45rem;
}

.bar-label {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.75);
}

.progress-track {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #6366F1, #8B5CF6);
  transition: width 0.25s ease;
}
</style>
