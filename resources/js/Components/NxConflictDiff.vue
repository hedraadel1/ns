<template>
  <section class="nx-conflict-diff" :class="{ conflicted: hasConflict }">
    <div class="diff-header">
      <div>
        <p class="diff-title">Conflict diff</p>
        <p class="diff-subtitle">AI reconciliation preview</p>
      </div>
      <button type="button" class="expand-toggle" @click="open = !open">
        {{ open ? 'Hide details' : 'Review conflict' }}
      </button>
    </div>

    <div class="diff-grid" :class="{ open }">
      <div
        v-for="(conflict, index) in conflicts"
        :key="`conflict-${index}`"
        class="diff-card"
      >
        <div class="diff-section">
          <p class="diff-label">Before</p>
          <p class="diff-text">{{ conflict.before }}</p>
        </div>
        <div class="diff-section">
          <p class="diff-label">After</p>
          <p class="diff-text">{{ conflict.after }}</p>
        </div>
        <div class="diff-actions" v-if="open">
          <button type="button" class="diff-action button-keep" @click="$emit('resolve', { choice: 'keep-this', conflict })">
            Keep This
          </button>
          <button type="button" class="diff-action button-other" @click="$emit('resolve', { choice: 'keep-other', conflict })">
            Keep Other
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  conflicts: {
    type: Array,
    default: () => [],
  },
  conflictId: {
    type: String,
    default: '',
  },
})

const open = ref(false)
const hasConflict = computed(() => props.conflicts && props.conflicts.length > 0)
</script>

<style scoped>
.nx-conflict-diff {
  background: rgba(15, 23, 42, 0.86);
  border: 1px solid rgba(248, 250, 252, 0.08);
  border-radius: 1rem;
  padding: 1rem;
}

.diff-header {
  margin-bottom: 1rem;
}

.diff-title {
  color: #f8fafc;
  font-size: 0.95rem;
  font-weight: 700;
}

.diff-subtitle {
  color: #94a3b8;
  font-size: 0.75rem;
}

.diff-grid {
  display: grid;
  gap: 0.75rem;
}

.diff-card {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(248, 250, 252, 0.08);
  border-radius: 1rem;
  padding: 0.85rem;
}

.diff-label {
  color: #7dd3fc;
  font-size: 0.7rem;
  letter-spacing: 0.12em;
  margin-bottom: 0.35rem;
  text-transform: uppercase;
}

.diff-text {
  color: #cbd5e1;
  font-size: 0.82rem;
  line-height: 1.5;
}

.diff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.expand-toggle {
  background: rgba(99, 102, 241, 0.12);
  color: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 9999px;
  padding: 0.65rem 1rem;
  cursor: pointer;
}

.diff-grid {
  display: grid;
  gap: 0.75rem;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease;
}

.diff-grid.open {
  max-height: 1000px;
}

.diff-card {
  transition: transform 200ms ease, box-shadow 200ms ease;
}

.nx-conflict-diff.conflicted .diff-card {
  box-shadow: 0 0 0 1px rgba(248, 113, 113, 0.25), 0 18px 60px rgba(248, 113, 113, 0.12);
}

.diff-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

.diff-action {
  border: none;
  border-radius: 9999px;
  padding: 0.55rem 0.9rem;
  font-size: 0.8rem;
  cursor: pointer;
}

.button-keep {
  background: rgba(34, 197, 94, 0.14);
  color: #d1fae5;
}

.button-other {
  background: rgba(59, 130, 246, 0.14);
  color: #bfdbfe;
}
</style>
