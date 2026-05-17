<template>
  <section class="template-library">
    <h1>Workflow Templates</h1>
    <p>Browse and apply pre-built workflow templates.</p>

    <div class="template-filters">
      <button
        v-for="cat in categories"
        :key="cat"
        :class="['filter-btn', { active: selectedCategory === cat }]"
        @click="selectedCategory = cat"
      >
        {{ cat }}
      </button>
    </div>

    <div class="template-grid">
      <div
        v-for="template in filteredTemplates"
        :key="template.id"
        class="template-card"
      >
        <h3>{{ template.name }}</h3>
        <p>{{ template.description }}</p>
        <div class="template-meta">
          <span class="template-category">{{ template.category }}</span>
          <span class="template-steps">{{ template.steps?.length || 0 }} steps</span>
        </div>
        <button class="apply-btn" @click="applyTemplate(template)">
          Apply Template
        </button>
      </div>
    </div>

    <div v-if="filteredTemplates.length === 0" class="no-templates">
      <p>No templates found for this category.</p>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const templates = ref([])
const selectedCategory = ref('all')
const categories = ref(['all', 'contacts', 'reporting', 'maintenance', 'analytics'])

const filteredTemplates = computed(() => {
  if (selectedCategory.value === 'all') return templates.value
  return templates.value.filter(t => t.category === selectedCategory.value)
})

function applyTemplate(template) {
  alert(`Applying template: ${template.name}`)
}

onMounted(() => {
  fetch('/api/v1/workflows/templates')
    .then(res => res.json())
    .then(data => {
      if (data.data) templates.value = data.data
    })
    .catch(() => {})
})
</script>

<style scoped>
.template-library {
  padding: 1rem;
}

.template-filters {
  display: flex;
  gap: 0.5rem;
  margin: 1rem 0;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 0.5rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  background: transparent;
  color: #888;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-btn:hover {
  border-color: rgba(255, 255, 255, 0.4);
  color: #fff;
}

.filter-btn.active {
  background: rgba(74, 222, 128, 0.2);
  border-color: #4ade80;
  color: #4ade80;
}

.template-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.template-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem;
  transition: border-color 0.2s;
}

.template-card:hover {
  border-color: rgba(74, 222, 128, 0.3);
}

.template-card h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
}

.template-card p {
  margin: 0 0 1rem 0;
  color: #888;
  font-size: 0.875rem;
  line-height: 1.5;
}

.template-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  font-size: 0.75rem;
  color: #666;
}

.template-category {
  text-transform: capitalize;
}

.apply-btn {
  width: 100%;
  padding: 0.5rem;
  background: rgba(74, 222, 128, 0.1);
  border: 1px solid rgba(74, 222, 128, 0.3);
  border-radius: 4px;
  color: #4ade80;
  cursor: pointer;
  transition: all 0.2s;
}

.apply-btn:hover {
  background: rgba(74, 222, 128, 0.2);
}

.no-templates {
  text-align: center;
  padding: 2rem;
  color: #888;
}
</style>