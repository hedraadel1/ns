<template>
  <section class="nx-version-history">
    <button class="accordion-toggle" type="button" @click="expanded = !expanded">
      <span>Version history</span>
      <span>{{ expanded ? 'Hide' : 'View' }}</span>
    </button>

    <div class="version-list" :class="{ expanded }">
      <div
        v-for="(version, index) in versions"
        :key="`${version.updatedAt}-${index}`"
        class="version-item"
      >
        <button class="version-summary" type="button" @click="selectVersion(version)">
          <span :class="['version-label', { superseded: Boolean(version.supersededAt) }]">
            {{ formatDate(version.updatedAt) }}
          </span>
          <span class="version-source">{{ version.source || 'System' }}</span>
        </button>
        <div v-if="selectedVersion === version" class="version-diff">
          <pre>{{ version.value }}</pre>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  fieldKey: {
    type: String,
    default: '',
  },
  versions: {
    type: Array,
    default: () => [],
  },
})

const expanded = ref(false)
const selectedVersion = ref(null)

function selectVersion(version) {
  selectedVersion.value = selectedVersion.value === version ? null : version
}

function formatDate(value) {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric', year: 'numeric' }).format(date)
}
</script>

<style scoped>
.nx-version-history {
  background: rgba(15, 23, 42, 0.88);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 1rem;
  overflow: hidden;
}

.accordion-toggle {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(30, 41, 59, 0.9);
  color: #f8fafc;
  border: none;
  cursor: pointer;
  font-weight: 700;
}

.version-list {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s ease;
}

.version-list.expanded {
  max-height: 600px;
}

.version-item {
  border-top: 1px solid rgba(148, 163, 184, 0.12);
}

.version-summary {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: transparent;
  border: none;
  color: #f8fafc;
  cursor: pointer;
}

.version-label {
  font-size: 0.85rem;
}

.version-label.superseded {
  text-decoration: line-through;
  opacity: 0.6;
}

.version-source {
  color: #94a3b8;
  font-size: 0.75rem;
}

.version-diff {
  padding: 1rem;
  background: rgba(31, 41, 55, 0.9);
  color: #cbd5e1;
}

.version-diff pre {
  white-space: pre-wrap;
  margin: 0;
}
</style>
