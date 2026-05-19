<template>
  <div class="provider-form-shell">
    <header class="form-header">
      <div>
        <p class="subtitle">Add AI provider</p>
        <h2>Provider onboarding wizard</h2>
      </div>
      <div class="step-pill">Step {{ step }} of 4</div>
    </header>

    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: `${(step / 4) * 100}%` }" />
    </div>

    <section class="step-card">
      <div v-if="step === 1" class="step-panel">
        <h3>Basic info</h3>
        <label>
          Provider name
          <input v-model="form.name" placeholder="e.g. OpenAI" />
        </label>
        <label>
          Provider type
          <input v-model="form.type" placeholder="e.g. OpenAI, Gemini" />
        </label>
      </div>

      <div v-if="step === 2" class="step-panel">
        <h3>Authentication</h3>
        <label>
          API key
          <input v-model="form.api_key" placeholder="Enter API key" />
        </label>
        <label>
          Endpoint URL
          <input v-model="form.endpoint" placeholder="https://api.provider.com" />
        </label>
      </div>

      <div v-if="step === 3" class="step-panel">
        <h3>Test connection</h3>
        <p class="step-help">Validate provider authentication before syncing models.</p>
        <div class="action-group">
          <NxActionButton variant="primary" :loading="testing" @click="runTest">Test connection</NxActionButton>
          <button type="button" class="secondary-button" @click="openHealthModal = true" :disabled="!healthDetails">View result</button>
        </div>
        <p v-if="testResult" class="test-status">{{ testResult }}</p>
      </div>

      <div v-if="step === 4" class="step-panel">
        <h3>Sync models</h3>
        <p class="step-help">Sync available provider models and select the ones you want enabled.</p>
        <div class="model-list">
          <label v-for="model in availableModels" :key="model.id" class="model-item">
            <input type="checkbox" v-model="form.selectedModels" :value="model.id" />
            <span>{{ model.name }}</span>
          </label>
        </div>
        <NxActionButton variant="secondary" :loading="syncing" @click="syncModels">Sync models</NxActionButton>
      </div>
    </section>

    <div class="wizard-actions">
      <button type="button" class="ghost-button" @click="previousStep" :disabled="step === 1">Back</button>
      <NxActionButton variant="primary" :loading="saving" @click="nextStep">
        {{ step === 4 ? 'Finish' : 'Next' }}
      </NxActionButton>
    </div>

    <NxProviderHealthModal
      :open="openHealthModal"
      :status="healthStatus"
      :description="healthDescription"
      :details="healthDetails"
      @close="openHealthModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import NxActionButton from './NxActionButton.vue'
import NxProviderHealthModal from './NxProviderHealthModal.vue'

const emit = defineEmits(['complete'])

const step = ref(1)
const form = ref({
  name: '',
  type: '',
  api_key: '',
  endpoint: '',
  selectedModels: [],
})
const availableModels = ref([])
const providerId = ref(null)
const testing = ref(false)
const syncing = ref(false)
const saving = ref(false)
const testResult = ref('')
const healthStatus = ref('pending')
const healthDescription = ref('Awaiting validation')
const healthDetails = ref(null)
const openHealthModal = ref(false)

const hasBasicInfo = computed(() => form.value.name.trim() && form.value.type.trim())
const hasAuthInfo = computed(() => form.value.api_key.trim() && form.value.endpoint.trim())

async function createProvider() {
  if (!hasAuthInfo.value) {
    throw new Error('Please fill in the provider authentication details.')
  }

  saving.value = true
  try {
    const response = await fetch('/api/v1/ai/providers', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        name: form.value.name,
        provider_type: form.value.type,
        api_key: form.value.api_key,
        endpoint: form.value.endpoint,
      }),
    })
    if (!response.ok) {
      const payload = await response.json().catch(() => ({}))
      throw new Error(payload.message || 'Unable to create provider.')
    }
    const payload = await response.json()
    providerId.value = payload?.data?.id ?? payload?.id
    if (!providerId.value) {
      throw new Error('Provider ID was not returned.')
    }
  } finally {
    saving.value = false
  }
}

async function runTest() {
  if (!providerId.value) {
    await createProvider()
  }

  testing.value = true
  testResult.value = ''
  healthStatus.value = 'pending'
  healthDescription.value = 'Testing provider connection...'
  healthDetails.value = null

  try {
    const response = await fetch(`/api/v1/ai/providers/${providerId.value}/test`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    })
    const payload = await response.json().catch(() => ({}))
    if (!response.ok || payload.success === false) {
      throw new Error(payload.message || 'Connection test failed.')
    }
    testResult.value = 'Connection succeeded'
    healthStatus.value = 'success'
    healthDescription.value = payload.message ?? 'Provider responded successfully.'
    healthDetails.value = payload
  } catch (err) {
    testResult.value = err instanceof Error ? err.message : 'Connection test failed.'
    healthStatus.value = 'error'
    healthDescription.value = testResult.value
    healthDetails.value = { error: testResult.value }
  } finally {
    testing.value = false
    openHealthModal.value = true
  }
}

async function syncModels() {
  if (!providerId.value) return
  syncing.value = true
  try {
    const response = await fetch(`/api/v1/ai/providers/${providerId.value}/sync-models`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    })
    const payload = await response.json().catch(() => ({}))
    if (!response.ok || payload.success === false) {
      throw new Error(payload.message || 'Sync failed.')
    }
    availableModels.value = Array.isArray(payload?.data?.models)
      ? payload.data.models
      : Array.isArray(payload?.models)
        ? payload.models
        : []
  } catch (err) {
    healthStatus.value = 'error'
    healthDescription.value = err instanceof Error ? err.message : 'Unable to sync models.'
    healthDetails.value = err instanceof Error ? { message: err.message } : { error: String(err) }
    openHealthModal.value = true
  } finally {
    syncing.value = false
  }
}

async function nextStep() {
  try {
    if (step.value === 1) {
      if (!hasBasicInfo.value) throw new Error('Please provide provider name and type.')
      step.value = 2
      return
    }

    if (step.value === 2) {
      if (!hasAuthInfo.value) throw new Error('Please enter the API key and endpoint.')
      await createProvider()
      step.value = 3
      return
    }

    if (step.value === 3) {
      await runTest()
      step.value = 4
      return
    }

    if (step.value === 4) {
      if (!availableModels.value.length) {
        throw new Error('Please sync models before finishing.')
      }
      emit('complete', { id: providerId.value, ...form.value, models: availableModels.value })
      resetForm()
    }
  } catch (err) {
    healthStatus.value = 'error'
    healthDescription.value = err instanceof Error ? err.message : 'Unable to continue.'
    healthDetails.value = err instanceof Error ? { message: err.message } : { error: String(err) }
    openHealthModal.value = true
  }
}

function previousStep() {
  if (step.value > 1) {
    step.value -= 1
  }
}

function resetForm() {
  step.value = 1
  form.value = { name: '', type: '', api_key: '', endpoint: '', selectedModels: [] }
  availableModels.value = []
  providerId.value = null
  testResult.value = ''
  healthStatus.value = 'pending'
  healthDescription.value = 'Awaiting validation'
  healthDetails.value = null
}
</script>

<style scoped>
.provider-form-shell {
  width: 100%;
  padding: 1.5rem;
  border-radius: 24px;
  background: rgba(15, 23, 42, 0.94);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.subtitle {
  margin: 0;
  font-size: 0.75rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #60a5fa;
}

.form-header h2 {
  margin: 0.25rem 0 0;
  font-size: 1.45rem;
}

.step-pill {
  padding: 0.65rem 1rem;
  border-radius: 999px;
  background: rgba(56, 189, 248, 0.1);
  color: #c7d2fe;
  font-size: 0.9rem;
}

.progress-bar {
  height: 8px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #38bdf8, #818cf8);
  transition: width 250ms ease;
}

.step-card {
  padding: 1.25rem;
  border-radius: 22px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.04);
}

.step-panel h3 {
  margin: 0 0 0.75rem;
  color: #f8fafc;
}

label {
  display: grid;
  gap: 0.5rem;
  color: #cbd5e1;
}

input {
  width: 100%;
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 14px;
  padding: 0.95rem 1rem;
  background: rgba(15, 23, 42, 0.95);
  color: #f8fafc;
}

input::placeholder {
  color: rgba(203, 213, 225, 0.55);
}

.step-help {
  margin: 0;
  color: #94a3b8;
}

.action-group {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
}

.secondary-button,
.ghost-button {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.04);
  color: #e2e8f0;
  padding: 0.85rem 1.15rem;
  border-radius: 14px;
  cursor: pointer;
}

.secondary-button:hover,
.ghost-button:hover {
  background: rgba(255, 255, 255, 0.08);
}

.test-status {
  margin: 0;
  color: #cbd5e1;
}

.model-list {
  display: grid;
  gap: 0.85rem;
}

.model-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.95rem 1rem;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  color: #e2e8f0;
}

.wizard-actions {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.ghost-button {
  min-width: 120px;
}

@media (max-width: 700px) {
  .wizard-actions {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
