<template>
  <div :class="['skeleton-loader', `skeleton-${variant}`]">
    <div v-if="variant === 'card'" class="skeleton-card">
      <div class="skeleton-image"></div>
      <div class="skeleton-content">
        <div class="skeleton-title"></div>
        <div class="skeleton-text"></div>
        <div class="skeleton-text short"></div>
      </div>
    </div>
    <div v-else-if="variant === 'list'" class="skeleton-list">
      <div v-for="i in rows" :key="i" class="skeleton-list-item">
        <div class="skeleton-avatar"></div>
        <div class="skeleton-content">
          <div class="skeleton-title"></div>
          <div class="skeleton-text"></div>
        </div>
      </div>
    </div>
    <div v-else-if="variant === 'table'" class="skeleton-table">
      <div class="skeleton-table-header">
        <div v-for="i in columns" :key="i" class="skeleton-table-cell"></div>
      </div>
      <div v-for="i in rows" :key="i" class="skeleton-table-row">
        <div v-for="j in columns" :key="j" class="skeleton-table-cell"></div>
      </div>
    </div>
    <div v-else class="skeleton-text-block">
      <div v-for="i in rows" :key="i" :class="['skeleton-line', { short: i === rows }]"></div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  variant: {
    type: String,
    default: 'text',
    validator: (v) => ['text', 'card', 'list', 'table'].includes(v),
  },
  rows: {
    type: Number,
    default: 3,
  },
  columns: {
    type: Number,
    default: 4,
  },
})
</script>

<style scoped>
.skeleton-loader {
  width: 100%;
}

/* Shimmer Animation */
@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.skeleton-card {
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.skeleton-image {
  height: 160px;
  background: linear-gradient(
    90deg,
    var(--color-bg-tertiary) 25%,
    var(--color-bg-elevated) 50%,
    var(--color-bg-tertiary) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  padding: 1rem;
}

.skeleton-title {
  height: 20px;
  width: 60%;
  background: linear-gradient(
    90deg,
    var(--color-bg-tertiary) 25%,
    var(--color-bg-elevated) 50%,
    var(--color-bg-tertiary) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-sm);
  margin-bottom: 0.75rem;
}

.skeleton-text {
  height: 14px;
  width: 100%;
  background: linear-gradient(
    90deg,
    var(--color-bg-tertiary) 25%,
    var(--color-bg-elevated) 50%,
    var(--color-bg-tertiary) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-sm);
  margin-bottom: 0.5rem;
}

.skeleton-text.short {
  width: 80%;
}

/* List Skeleton */
.skeleton-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.skeleton-list-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
}

.skeleton-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(
    90deg,
    var(--color-bg-tertiary) 25%,
    var(--color-bg-elevated) 50%,
    var(--color-bg-tertiary) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  flex-shrink: 0;
}

/* Table Skeleton */
.skeleton-table {
  width: 100%;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.skeleton-table-header {
  display: grid;
  grid-template-columns: repeat(var(--columns, 4), 1fr);
  gap: 1px;
  background: var(--color-border);
}

.skeleton-table-row {
  display: grid;
  grid-template-columns: repeat(var(--columns, 4), 1fr);
  gap: 1px;
  background: var(--color-border);
}

.skeleton-table-cell {
  height: 40px;
  background: var(--color-bg-secondary);
}

.skeleton-table-header .skeleton-table-cell {
  background: var(--color-bg-tertiary);
}

/* Text Block Skeleton */
.skeleton-text-block {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-line {
  height: 14px;
  width: 100%;
  background: linear-gradient(
    90deg,
    var(--color-bg-tertiary) 25%,
    var(--color-bg-elevated) 50%,
    var(--color-bg-tertiary) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-sm);
}

.skeleton-line.short {
  width: 60%;
}
</style>
