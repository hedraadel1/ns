<template>
  <section class="settings-page">
    <h1>Settings</h1>
    <p>Manage application configuration.</p>

    <div class="settings-controls">
      <div class="filter-bar">
        <select v-model="selectedGroup" @change="loadSettings">
          <option value="">All Groups</option>
          <option v-for="g in groups" :key="g" :value="g">{{ g }}</option>
        </select>
        <input
          v-model="searchQuery"
          placeholder="Search settings..."
          @input="loadSettings"
          class="search-input"
        />
      </div>
      <button class="add-btn" @click="showAddModal = true">+ Add Setting</button>
    </div>

    <div v-if="loading" class="loading">Loading settings...</div>

    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else class="settings-grid">
      <div
        v-for="setting in settings"
        :key="setting.key"
        class="setting-card"
      >
        <div class="setting-header">
          <h3>{{ setting.key }}</h3>
          <span class="setting-type">{{ setting.type }}</span>
        </div>
        <p v-if="setting.description" class="setting-desc">
          {{ setting.description }}
        </p>
        <div class="setting-value">
          <template v-if="setting.type === 'boolean'">
            <label class="toggle">
              <input
                type="checkbox"
                :checked="setting.value"
                @change="updateSetting(setting)"
              />
              <span class="toggle-slider"></span>
            </label>
          </template>
          <template v-else-if="setting.type === 'json'">
            <textarea
              :value="JSON.stringify(setting.value, null, 2)"
              @blur="updateSetting(setting, $event)"
              rows="3"
              class="json-input"
            ></textarea>
          </template>
          <template v-else>
            <input
              type="text"
              :value="setting.value"
              @blur="updateSetting(setting, $event)"
              class="text-input"
            />
          </template>
        </div>
        <div class="setting-meta">
          <span class="setting-group">{{ setting.group }}</span>
          <span v-if="setting.is_public" class="badge public">Public</span>
          <span v-else class="badge private">Private</span>
        </div>
      </div>
    </div>

    <div v-if="!loading && !error && settings.length === 0" class="empty">
      <p>No settings found.</p>
    </div>

    <!-- Add Setting Modal -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal">
        <h2>Add Setting</h2>
        <form @submit.prevent="addSetting">
          <div class="form-group">
            <label>Key</label>
            <input v-model="newSetting.key" required class="form-input" />
          </div>
          <div class="form-group">
            <label>Value</label>
            <input v-model="newSetting.value" required class="form-input" />
          </div>
          <div class="form-group">
            <label>Type</label>
            <select v-model="newSetting.type" class="form-input">
              <option value="string">String</option>
              <option value="integer">Integer</option>
              <option value="boolean">Boolean</option>
              <option value="json">JSON</option>
              <option value="text">Text</option>
            </select>
          </div>
          <div class="form-group">
            <label>Group</label>
            <input v-model="newSetting.group" required class="form-input" />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="newSetting.description" class="form-input"></textarea>
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" v-model="newSetting.is_public" />
              Public
            </label>
          </div>
          <div class="modal-actions">
            <button type="button" @click="showAddModal = false">Cancel</button>
            <button type="submit" class="primary">Add Setting</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const settings = ref([])
const groups = ref([])
const selectedGroup = ref('')
const searchQuery = ref('')
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)

const newSetting = ref({
  key: '',
  value: '',
  type: 'string',
  group: 'general',
  description: '',
  is_public: false,
})

async function loadSettings() {
  loading.value = true
  error.value = null
  try {
    const params = new URLSearchParams()
    if (selectedGroup.value) params.append('group', selectedGroup.value)
    if (searchQuery.value) params.append('search', searchQuery.value)

    const res = await fetch(`/api/v1/settings?${params}`)
    const data = await res.json()
    if (data.success) {
      settings.value = data.data
      // Extract unique groups
      const uniqueGroups = [...new Set(settings.value.map(s => s.group))]
      groups.value = uniqueGroups.sort()
    } else {
      error.value = data.message || 'Failed to load settings'
    }
  } catch (e) {
    error.value = 'Network error'
  } finally {
    loading.value = false
  }
}

async function updateSetting(setting, event) {
  let value
  if (setting.type === 'boolean') {
    value = event.target.checked
  } else if (setting.type === 'json') {
    try {
      value = JSON.parse(event.target.value)
    } catch {
      alert('Invalid JSON')
      return
    }
  } else {
    value = event.target.value
  }

  try {
    const res = await fetch(`/api/v1/settings/${setting.key}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ value }),
    })
    const data = await res.json()
    if (data.success) {
      setting.value = data.data.value
    } else {
      alert(data.message || 'Update failed')
    }
  } catch (e) {
    alert('Network error')
  }
}

async function addSetting() {
  try {
    const res = await fetch('/api/v1/settings', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newSetting.value),
    })
    const data = await res.json()
    if (data.success) {
      showAddModal.value = false
      newSetting.value = {
        key: '',
        value: '',
        type: 'string',
        group: 'general',
        description: '',
        is_public: false,
      }
      loadSettings()
    } else {
      alert(data.message || 'Failed to add setting')
    }
  } catch (e) {
    alert('Network error')
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.settings-page {
  padding: 1rem;
}

.settings-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 1rem 0;
  flex-wrap: wrap;
  gap: 1rem;
}

.filter-bar {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.search-input {
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #fff;
}

.add-btn {
  padding: 0.5rem 1rem;
  background: rgba(74, 222, 128, 0.1);
  border: 1px solid rgba(74, 222, 128, 0.3);
  border-radius: 4px;
  color: #4ade80;
  cursor: pointer;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1rem;
}

.setting-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem;
}

.setting-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.setting-header h3 {
  margin: 0;
  font-size: 1rem;
  font-family: monospace;
}

.setting-type {
  font-size: 0.75rem;
  color: #888;
  background: rgba(255, 255, 255, 0.05);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.setting-desc {
  color: #888;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.setting-value {
  margin-bottom: 0.75rem;
}

.text-input,
.json-input {
  width: 100%;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #fff;
  font-family: inherit;
}

.json-input {
  font-family: monospace;
  font-size: 0.875rem;
}

.setting-meta {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.setting-group {
  font-size: 0.75rem;
  color: #666;
}

.badge {
  font-size: 0.7rem;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
}

.badge.public {
  background: rgba(74, 222, 128, 0.1);
  color: #4ade80;
}

.badge.private {
  background: rgba(255, 255, 255, 0.1);
  color: #888;
}

/* Toggle Switch */
.toggle {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
}

.toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.1);
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

.toggle input:checked + .toggle-slider {
  background-color: #4ade80;
}

.toggle input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #1a1a1a;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 2rem;
  width: 100%;
  max-width: 500px;
}

.modal h2 {
  margin-top: 0;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #888;
}

.form-input {
  width: 100%;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  color: #fff;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.modal-actions button {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.modal-actions .primary {
  background: rgba(74, 222, 128, 0.1);
  border: 1px solid rgba(74, 222, 128, 0.3);
  color: #4ade80;
}

.loading,
.error,
.empty {
  text-align: center;
  padding: 2rem;
  color: #888;
}

.error {
  color: #ef4444;
}
</style>
