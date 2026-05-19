<template>
  <div class="intent-grid-shell">
    <header class="intent-grid-header">
      <div>
        <p class="subtitle">Intent routing</p>
        <h2>AI intent matrix</h2>
      </div>
      <div class="header-actions">
        <button class="refresh-button" @click="loadRouting" :disabled="loading">Refresh</button>
      </div>
    </header>

    <div v-if="error" class="error-banner">{{ error }}</div>

    <div class="matrix-card">
      <div v-if="isMobile" class="accordion-list">
        <article v-for="intent in intents" :key="intent.key" class="accordion-item">
          <button class="accordion-toggle" @click="toggleIntent(intent.key)">
            <span>{{ intent.name }}</span>
            <span>{{ expandedIntent === intent.key ? '−' : '+' }}</span>
          </button>
          <div v-if="expandedIntent === intent.key" class="accordion-body">
            <div v-for="profile in profiles" :key="profile.key" class="accordion-row">
              <label>{{ profile.name }}</label>
              <select v-model="mapping[intent.key][profile.key]" @change="saveRoute(intent, profile)">
                <option v-for="model in profile.models" :key="model.id" :value="model.id">{{ model.name }}</option>
              </select>
              <span v-if="flashCell(intent.key, profile.key)" class="flash-pill">Saved</span>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="table-wrapper">
        <table class="intent-table">
          <thead>
            <tr>
              <th>Intent</th>
              <th v-for="profile in profiles" :key="profile.key">{{ profile.name }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="intent in intents" :key="intent.key">
              <td>{{ intent.name }}</td>
              <td v-for="profile in profiles" :key="profile.key">
                <div class="cell-content">
                  <select v-model="mapping[intent.key][profile.key]" @change="saveRoute(intent, profile)">
                    <option v-for="model in profile.models" :key="model.id" :value="model.id">{{ model.name }}</option>
                  </select>
                  <span v-if="flashCell(intent.key, profile.key)" class="flash-pill">Saved</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue'

const loading = ref(false)
const error = ref('')
const intents = ref([])
const profiles = ref([])
const mapping = reactive({})
const flashStates = reactive({})
const expandedIntent = ref(null)
const isMobile = ref(false)

const buildMapping = (intentsData, profilesData, routes = []) => {
  const map = {}
  intentsData.forEach((intent) => {
    map[intent.key] = {}
    profilesData.forEach((profile) => {
      const route = routes.find(
        (item) => String(item.intent) === String(intent.key) && String(item.provider) === String(profile.key),
      )
      map[intent.key][profile.key] = route?.model ?? profile.models?.[0]?.id ?? ''
    })
  })
  return map
}

const normalizeIntentRow = (item, index) => ({
  key: item.key ?? item.id ?? item.intent ?? `intent-${index}`,
  name: item.name ?? item.intent ?? `Intent ${index + 1}`,
})

const normalizeProfileRow = (item, index) => ({
  key: item.key ?? item.id ?? item.provider ?? `profile-${index}`,
  name: item.name ?? item.provider ?? `Profile ${index + 1}`,
  models: Array.isArray(item.models)
    ? item.models.map((model, modelIndex) => ({
        id: model.id ?? model.key ?? `model-${modelIndex}`,
        name: model.name ?? model.label ?? `Model ${modelIndex + 1}`,
      }))
    : [],
})

const normalizeRoute = (item) => ({
  intent: item.intent ?? item.intent_key ?? item.intentId,
  provider: item.provider ?? item.provider_key ?? item.providerId,
  model: item.model ?? item.model_id ?? item.modelId,
})

const flashCellKey = (intentKey, profileKey) => `${intentKey}::${profileKey}`

const flashCell = (intentKey, profileKey) => flashStates[flashCellKey(intentKey, profileKey)] === true

const updateFlash = (intentKey, profileKey) => {
  const key = flashCellKey(intentKey, profileKey)
  flashStates[key] = true
  setTimeout(() => {
    flashStates[key] = false
  }, 1200)
}

const setMapping = (map) => {
  Object.keys(map).forEach((intentKey) => {
    mapping[intentKey] = { ...map[intentKey] }
  })
}

const toggleIntent = (key) => {
  expandedIntent.value = expandedIntent.value === key ? null : key
}

const loadRouting = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await fetch('/api/v1/ai/intents/routing', {
      headers: { Accept: 'application/json' },
    })
    if (!response.ok) throw new Error(`Routing request failed with ${response.status}`)
    const payload = await response.json()
    const payloadData = payload?.data ?? payload
    const routes = Array.isArray(payloadData?.routes) ? payloadData.routes : payloadData?.routing ?? []
    const intentsData = Array.isArray(payloadData?.intents) ? payloadData.intents : payloadData?.rows ?? []
    const providersData = Array.isArray(payloadData?.providers) ? payloadData.providers : payloadData?.profiles ?? []

    intents.value = intentsData.map(normalizeIntentRow)
    profiles.value = providersData.map(normalizeProfileRow)
    setMapping(buildMapping(intents.value, profiles.value, routes.map(normalizeRoute)))
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load intent routing.'
    intents.value = []
    profiles.value = []
    setMapping({})
  } finally {
    loading.value = false
  }
}

const saveRoute = async (intent, profile) => {
  const selectedModel = mapping[intent.key][profile.key]
  if (!selectedModel) return

  try {
    const response = await fetch('/api/v1/ai/intents/routing', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ intent: intent.key, provider: profile.key, model: selectedModel }),
    })
    if (!response.ok) {
      const payload = await response.json().catch(() => ({}))
      throw new Error(payload.message || 'Update failed')
    }
    updateFlash(intent.key, profile.key)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to save routing update.'
  }
}

const checkMobile = () => {
  isMobile.value = window.innerWidth < 900
}

onMounted(() => {
  loadRouting()
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.intent-grid-shell {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.intent-grid-header {
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
  color: #38bdf8;
}

.intent-grid-header h2 {
  margin: 0.25rem 0 0;
  font-size: 1.4rem;
}

.refresh-button {
  border: 1px solid rgba(56, 189, 248, 0.35);
  background: rgba(56, 189, 248, 0.1);
  color: #e2e8f0;
  padding: 0.75rem 1rem;
  border-radius: 999px;
  cursor: pointer;
}

.error-banner {
  padding: 0.95rem 1rem;
  border-radius: 18px;
  background: rgba(248, 113, 113, 0.12);
  color: #fecaca;
}

.matrix-card {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  background: rgba(15, 23, 42, 0.92);
  padding: 1rem;
}

.table-wrapper {
  overflow-x: auto;
}

.intent-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 720px;
}

.intent-table th,
.intent-table td {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding: 0.9rem 1rem;
  color: #e2e8f0;
}

.intent-table th {
  color: #94a3b8;
  text-transform: uppercase;
  font-size: 0.78rem;
  letter-spacing: 0.08em;
}

.cell-content {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

select {
  width: 100%;
  appearance: none;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(255, 255, 255, 0.04);
  color: #e2e8f0;
  border-radius: 12px;
  padding: 0.75rem 0.9rem;
}

.flash-pill {
  display: inline-flex;
  margin-top: 0.5rem;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  background: rgba(34, 197, 94, 0.18);
  color: #86efac;
  font-size: 0.77rem;
}

.accordion-list {
  display: grid;
  gap: 0.85rem;
}

.accordion-item {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.03);
}

.accordion-toggle {
  width: 100%;
  background: transparent;
  border: none;
  padding: 1rem 1.2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #e2e8f0;
  cursor: pointer;
}

.accordion-body {
  padding: 1rem 1.2rem 1.2rem;
  display: grid;
  gap: 0.9rem;
}

.accordion-row {
  display: grid;
  gap: 0.5rem;
}

.accordion-row label {
  color: #94a3b8;
}

@media (max-width: 900px) {
  .intent-table {
    min-width: 100%;
  }
}
</style>
