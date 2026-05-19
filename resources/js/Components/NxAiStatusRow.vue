<template>
  <div class="nx-ai-status-row">
    <div class="status-label">Chat status</div>
    <div class="status-pill">{{ statusLabel }}</div>
  </div>
</template>

<script setup>
const props = defineProps({
  step: { type: String, default: 'Ready' },
});

const statusLabel = computed(() => props.step || 'Ready');
</script>

<style scoped>
.nx-ai-status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.9rem 1rem;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 1rem;
  color: rgba(255, 255, 255, 0.8);
}

.status-label {
  font-size: 0.9rem;
  letter-spacing: 0.02em;
}

.status-pill {
  font-size: 0.82rem;
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  background: rgba(99, 102, 241, 0.16);
  color: #e0e7ff;
}
</style>
