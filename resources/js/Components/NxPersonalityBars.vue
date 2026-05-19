<template>
  <div class="nx-personality-bars">
    <div
      v-for="(trait, index) in traits"
      :key="trait.name"
      class="personality-row"
      @mouseenter="hovered = index"
      @mouseleave="hovered = null"
      :title="trait.description"
    >
      <div class="personality-meta">
        <span class="trait-name">{{ trait.name }}</span>
        <span class="trait-score">{{ trait.score }}%</span>
      </div>
      <div class="bar-track">
        <div class="bar-fill" :style="fillStyle(trait.score)" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  traits: {
    type: Array,
    default: () => [],
  },
})

const hovered = ref(null)

function fillStyle(score) {
  return {
    width: `${Math.min(Math.max(score, 0), 100)}%`,
    transitionDelay: '0ms',
  }
}
</script>

<style scoped>
.nx-personality-bars {
  display: grid;
  gap: 0.85rem;
}

.personality-row {
  display: grid;
  gap: 0.45rem;
}

.personality-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #f8fafc;
  font-size: 0.8rem;
}

.trait-name {
  font-weight: 600;
}

.trait-score {
  color: rgba(255,255,255,0.65);
}

.bar-track {
  width: 100%;
  height: 10px;
  background: rgba(255,255,255,0.08);
  border-radius: 9999px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  border-radius: 9999px;
  background: linear-gradient(90deg, #6366f1, #007aff);
  transition: width 0.8s ease-out;
}
</style>
