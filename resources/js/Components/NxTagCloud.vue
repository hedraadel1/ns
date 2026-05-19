<template>
  <div class="nx-tag-cloud">
    <div class="tag-row">
      <button
        v-for="(tag, index) in tags"
        :key="`${tag.label}-${index}`"
        class="tag-chip"
        :class="chipClass(tag.category)"
      >
        <span>{{ tag.label }}</span>
        <button
          v-if="editable"
          type="button"
          class="remove-chip"
          @click.prevent="$emit('remove', tag)">
          ×
        </button>
      </button>
      <button v-if="editable" type="button" class="add-chip" @click="$emit('add')">+ Add</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  tags: {
    type: Array,
    default: () => [],
  },
  editable: {
    type: Boolean,
    default: false,
  },
})

function chipClass(category) {
  const key = String(category || '').toLowerCase()
  return {
    personality: 'chip-personality',
    preference: 'chip-preference',
    topic: 'chip-topic',
    flag: 'chip-flag',
  }[key] || 'chip-default'
}
</script>

<style scoped>
.nx-tag-cloud {
  width: 100%;
}

.tag-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag-chip,
.add-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 0.9rem;
  border-radius: 9999px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: #f8fafc;
  font-size: 0.75rem;
  cursor: default;
  transition: transform 150ms ease, background 150ms ease;
}

.tag-chip:hover,
.add-chip:hover {
  transform: translateY(-1px);
  background: rgba(255,255,255,0.08);
}

.remove-chip {
  background: none;
  border: none;
  color: rgba(255,255,255,0.7);
  cursor: pointer;
  font-size: 0.85rem;
}

.chip-personality {
  background: rgba(99, 102, 241, 0.18);
  border-color: rgba(99, 102, 241, 0.35);
}

.chip-preference {
  background: rgba(59, 130, 246, 0.18);
  border-color: rgba(59, 130, 246, 0.35);
}

.chip-topic {
  background: rgba(16, 185, 129, 0.18);
  border-color: rgba(16, 185, 129, 0.35);
}

.chip-flag {
  background: rgba(245, 158, 11, 0.18);
  border-color: rgba(245, 158, 11, 0.35);
}

.chip-default {
  background: rgba(255,255,255,0.08);
  border-color: rgba(255,255,255,0.18);
}
</style>
