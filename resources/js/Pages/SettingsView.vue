<template>
  <div class="settings-view">
    <h1>Settings</h1>
    <p class="subtitle">Manage application configuration.</p>

    <div class="settings-controls">
      <div class="filter-bar">
        <select v-model="selectedGroup" @change="loadSettings" class="group-select">
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

    <section class="intent-panel mt-8">
      <div class="intent-panel-header">
        <h2>Intent routing matrix</h2>
        <p class="text-sm text-slate-400">Configure which providers handle which intents and cost profiles.</p>
      </div>
      <NxIntentGrid :rows="intentRows" :profiles="intentProfiles" />
    </section>

    <section class="provider-panel mt-8">
      <div class="provider-panel-header">
        <h2>Add provider</h2>
        <p class="text-sm text-slate-400">Connect a new AI provider with a guided wizard.</p>
      </div>
      <NxAddProviderForm @saved="loadSettings" />
    </section>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading settings...</p>
    </div>

    <div v-else-if="error" class="error-message">{{ error }}</div>

    <div v-else class="settings-grid">
      <div
        v-for="setting in settings"
        :key="setting.key"
        class="setting-card glass"
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
                @change="updateSetting(setting, $event)"
              />
              <span class="toggle-slider"></span>
            </label>
          </template>
          <template v-else-if="setting.type === 'json'">
            <textarea
              :value="JSON.stringify(setting.value, null, 2)"
              @blur="updateSetting(setting, $event)"
              rows="4"
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

    <div v-if="!loading && !error && settings.length === 0" class="empty-state">
      <p>No settings found.</p>
    </div>

    <!-- Add Setting Modal -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal glass-strong">
        <h2>Add Setting</h2>
        <form @submit.prevent="addSetting">
          <div class="form-group">
            <label>Key</label>
            <input v-model="newSetting.key" required class="form-input" placeholder="e.g. app.timezone" />
          </div>
          <div class="form-group">
            <label>Value</label>
            <input v-model="newSetting.value" required class="form-input" placeholder="Value" />
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
            <input v-model="newSetting.group" required class="form-input" placeholder="e.g. general" />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="newSetting.description" class="form-input" placeholder="Setting description..."></textarea>
          </div>
          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="newSetting.is_public" />
              <span>Publicly readable (via public API)</span>
            </label>
          </div>
          <div class="modal-actions">
            <button type="button" class="cancel-btn" @click="showAddModal = false">Cancel</button>
            <button type="submit" class="submit-btn">Add Setting</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '../stores/useNotificationStore'
import NxIntentGrid from '../Components/NxIntentGrid.vue'
import NxAddProviderForm from '../Components/NxAddProviderForm.vue'

const notifications = useNotificationStore()
const settings = ref([])
const groups = ref([])
const selectedGroup = ref('')
const searchQuery = ref('')
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const intentRows = ref([
  { intent: 'Search', provider: 'Auto', fallback: 'Quality' },
  { intent: 'Summarize', provider: 'Quality', fallback: 'Budget' },
  { intent: 'Chat', provider: 'Fast', fallback: 'Quality' },
])
const intentProfiles = ref(['Fast', 'Quality', 'Budget'])

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
      notifications.addToast({ type: 'error', title: 'Settings load failed', message: error.value })
    }
  } catch (e) {
    error.value = 'Network error'
    notifications.addToast({ type: 'error', title: 'Network error', message: 'Unable to load settings.' })
  } finally {
    loading.value = false
  }
}

async function updateSetting(setting, event) {
  const originalValue = setting.value
  let value

  if (setting.type === 'boolean') {
    value = event.target.checked
  } else if (setting.type === 'json') {
    try {
      value = JSON.parse(event.target.value)
    } catch {
      notifications.addToast({ type: 'error', title: 'Invalid JSON', message: 'Invalid JSON format.' })
      return
    }
  } else {
    value = event.target.value
  }

  setting.value = value

  try {
    const res = await fetch(`/api/v1/settings/${setting.key}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ value }),
    })
    const data = await res.json()
    if (data.success) {
      setting.value = data.data?.value ?? value
      notifications.addToast({ type: 'success', title: 'Updated', message: `Successfully updated ${setting.key}.` })
    } else {
      setting.value = originalValue
      notifications.addToast({ type: 'error', title: 'Update failed', message: data.message || 'Unable to update setting.' })
    }
  } catch (e) {
    setting.value = originalValue
    notifications.addToast({ type: 'error', title: 'Network error', message: 'Unable to update setting.' })
  }
}

async function addSetting() {
  try {
    const res = await fetch('/api/v1/settings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
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
      notifications.addToast({ type: 'success', title: 'Added', message: 'Successfully added setting.' })
    } else {
      notifications.addToast({ type: 'error', title: 'Add failed', message: data.message || 'Failed to add setting.' })
    }
  } catch (e) {
    notifications.addToast({ type: 'error', title: 'Network error', message: 'Unable to add setting.' })
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.settings-view {
  max-width: 1200px;
  margin: 0 auto;
}

.subtitle {
  color: var(--color-text-muted);
  margin-bottom: 2rem;
}

.settings-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.filter-bar {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.group-select,
.search-input {
  padding: 0.6rem 1rem;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  color: var(--color-text-primary);
  font-size: 0.875rem;
}

.group-select:focus,
.search-input:focus {
  border-color: var(--color-primary);
  outline: none;
}

.add-btn {
  padding: 0.6rem 1.25rem;
  background: var(--color-primary-muted);
  border: 1px solid var(--color-primary-border);
  border-radius: var(--radius-md);
  color: var(--color-primary);
  cursor: pointer;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all var(--transition-fast);
}

.add-btn:hover {
  background: rgba(74, 222, 128, 0.2);
  box-shadow: var(--shadow-glow);
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.5rem;
}

.setting-card {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.setting-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
  gap: 0.5rem;
}

.setting-header h3 {
  margin: 0;
  font-size: 1rem;
  font-family: monospace;
  word-break: break-all;
  color: var(--color-text-primary);
}

.setting-type {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  background: rgba(255, 255, 255, 0.05);
  padding: 0.2rem 0.5rem;
  border-radius: var(--radius-sm);
  text-transform: uppercase;
  font-weight: 600;
}

.setting-desc {
  color: var(--color-text-muted);
  font-size: 0.825rem;
  margin: 0 0 1.25rem 0;
  line-height: 1.4;
  flex-grow: 1;
}

.setting-value {
  margin-bottom: 1.25rem;
}

.text-input,
.json-input {
  width: 100%;
  padding: 0.6rem;
  background: var(--color-bg-tertiary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  color: var(--color-text-primary);
  font-family: inherit;
  font-size: 0.875rem;
}

.text-input:focus,
.json-input:focus {
  border-color: var(--color-primary);
  outline: none;
}

.json-input {
  font-family: monospace;
  resize: vertical;
}

.setting-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 0.75rem;
}

.setting-group {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge {
  font-size: 0.7rem;
  padding: 0.2rem 0.5rem;
  border-radius: var(--radius-sm);
  text-transform: uppercase;
  font-weight: 600;
}

.badge.public {
  background: rgba(74, 222, 128, 0.1);
  color: var(--color-primary);
}

.badge.private {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-muted);
}

/* Toggle Switch */
.toggle {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 22px;
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
  border-radius: 22px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

.toggle input:checked + .toggle-slider {
  background-color: var(--color-primary);
}

.toggle input:checked + .toggle-slider:before {
  transform: translateX(22px);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal {
  padding: 2rem;
  width: 100%;
  max-width: 500px;
  box-shadow: var(--shadow-lg);
}

.modal h2 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
  color: var(--color-text-primary);
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--color-text-muted);
  font-size: 0.875rem;
  font-weight: 500;
}

.form-input {
  width: 100%;
  padding: 0.6rem 0.875rem;
  background: var(--color-bg-tertiary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  color: var(--color-text-primary);
  font-size: 0.9rem;
}

.form-input:focus {
  border-color: var(--color-primary);
  outline: none;
}

.checkbox-group {
  margin-top: 1rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  cursor: pointer;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.cancel-btn {
  padding: 0.6rem 1.25rem;
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  color: var(--color-text-secondary);
  cursor: pointer;
  font-weight: 500;
  transition: all var(--transition-fast);
}

.cancel-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text-primary);
}

.submit-btn {
  padding: 0.6rem 1.25rem;
  background: var(--color-primary);
  border: 1px solid var(--color-primary);
  border-radius: var(--radius-md);
  color: var(--color-bg-primary);
  cursor: pointer;
  font-weight: 600;
  transition: all var(--transition-fast);
}

.submit-btn:hover {
  box-shadow: var(--shadow-glow);
  transform: translateY(-1px);
}

.loading-state,
.error-message,
.empty-state {
  text-align: center;
  padding: 3rem;
  color: var(--color-text-muted);
}

.spinner {
  border: 3px solid rgba(255, 255, 255, 0.05);
  border-top: 3px solid var(--color-primary);
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  color: var(--color-error);
  background: rgba(248, 113, 113, 0.05);
  border: 1px dashed rgba(248, 113, 113, 0.2);
  border-radius: var(--radius-md);
}
</style>
