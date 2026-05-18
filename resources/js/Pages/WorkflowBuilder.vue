<template>
  <div class="flex h-[calc(100vh-8rem)] flex-col gap-4 p-4">
    <!-- Header -->
    <section class="border border-green-500/20 bg-zinc-950/80 p-5">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
          <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-green-400/70">Workflow Builder</p>
          <h1 class="mt-2 text-3xl font-black uppercase tracking-wide text-white">Visual workflow composer</h1>
          <p class="mt-3 text-sm leading-6 text-zinc-400">
            Design and compose workflows using a visual drag-and-drop interface. Connect steps, configure triggers, and deploy automations.
          </p>
        </div>

        <div class="flex gap-2">
          <button
            @click="saveWorkflow"
            :disabled="!currentWorkflow || savingWorkflow"
            class="rounded-lg bg-green-500/20 px-4 py-2 text-sm font-semibold text-green-300 transition hover:bg-green-500/30 disabled:opacity-50"
          >
            {{ savingWorkflow ? 'Saving...' : 'Save' }}
          </button>
          <button
            @click="publishWorkflow"
            :disabled="!currentWorkflow || publishingWorkflow"
            class="rounded-lg bg-blue-500/20 px-4 py-2 text-sm font-semibold text-blue-300 transition hover:bg-blue-500/30 disabled:opacity-50"
          >
            {{ publishingWorkflow ? 'Publishing...' : 'Publish' }}
          </button>
        </div>
      </div>
    </section>

    <!-- Main Content Grid -->
    <div class="flex min-h-0 flex-1 gap-4">
      <!-- Left Panel: Workflow List -->
      <div class="w-64 flex-shrink-0 overflow-hidden rounded-lg border border-zinc-700/50 bg-zinc-950/50 flex flex-col">
        <div class="border-b border-zinc-700/50 px-4 py-3">
          <h2 class="text-sm font-semibold text-white">Workflows</h2>
          <button
            @click="createNewWorkflow"
            class="mt-2 w-full rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:bg-zinc-900/70"
          >
            + New Workflow
          </button>
        </div>

        <!-- Workflows List -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="loadingWorkflows" class="p-4 text-center text-xs text-zinc-400">
            Loading...
          </div>
          <div v-else-if="workflows.length === 0" class="p-4 text-center text-xs text-zinc-400">
            No workflows yet
          </div>
          <div v-else>
            <div
              v-for="workflow in workflows"
              :key="workflow.id"
              class="cursor-pointer border-b border-zinc-700/30 px-4 py-2.5 transition hover:bg-zinc-900/30"
              :class="{ 'bg-green-500/10': currentWorkflow?.id === workflow.id }"
              @click="selectWorkflow(workflow)"
            >
              <p class="text-xs font-semibold text-white truncate">{{ workflow.name }}</p>
              <p class="mt-0.5 text-xs text-zinc-500 truncate">{{ workflow.description }}</p>
              <div class="mt-1 flex items-center gap-1">
                <span class="inline-block rounded-full px-1.5 py-0.5 text-[10px] font-semibold" :class="getStatusClass(workflow.status)">
                  {{ workflow.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Middle Panel: Canvas -->
      <div class="flex-1 min-w-0 overflow-hidden rounded-lg border border-zinc-700/50 bg-zinc-950/50 flex flex-col">
        <!-- Canvas Toolbar -->
        <div class="border-b border-zinc-700/50 bg-zinc-900/50 px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <button
                @click="zoomIn"
                class="rounded-lg border border-zinc-700/50 bg-zinc-900 px-2 py-1 text-xs text-zinc-300 transition hover:bg-zinc-800"
                title="Zoom In"
              >
                +
              </button>
              <button
                @click="zoomOut"
                class="rounded-lg border border-zinc-700/50 bg-zinc-900 px-2 py-1 text-xs text-zinc-300 transition hover:bg-zinc-800"
                title="Zoom Out"
              >
                -
              </button>
              <button
                @click="resetZoom"
                class="rounded-lg border border-zinc-700/50 bg-zinc-900 px-2 py-1 text-xs text-zinc-300 transition hover:bg-zinc-800"
                title="Reset Zoom"
              >
                Reset
              </button>
              <p class="ml-2 text-xs text-zinc-400">{{ Math.round(zoom * 100) }}%</p>
            </div>

            <p v-if="currentWorkflow" class="text-sm font-semibold text-white">
              {{ currentWorkflow.name }}
            </p>

            <p class="text-xs text-zinc-400">
              {{ currentWorkflow?.steps?.length || 0 }} steps
            </p>
          </div>
        </div>

        <!-- Canvas Area -->
        <div class="flex-1 overflow-auto relative bg-gradient-to-br from-zinc-950 to-zinc-900/80">
          <svg
            v-if="currentWorkflow"
            class="absolute inset-0 w-full h-full"
            :style="{ transform: `scale(${zoom})`, transformOrigin: '0 0' }"
          >
            <!-- Connections -->
            <line
              v-for="(connection, idx) in getConnections"
              :key="`conn-${idx}`"
              :x1="connection.fromX"
              :y1="connection.fromY"
              :x2="connection.toX"
              :y2="connection.toY"
              stroke="#10b981"
              stroke-width="2"
              fill="none"
            />
          </svg>

          <!-- Workflow Steps -->
          <div
            v-if="currentWorkflow && currentWorkflow.steps && currentWorkflow.steps.length > 0"
            class="relative p-8"
          >
            <div
              v-for="(step, index) in currentWorkflow.steps"
              :key=step.id"
              class="absolute cursor-move rounded-lg border-2 bg-zinc-900/80 p-4 transition hover:border-green-500/50"
              :style="{ left: `${step.x || index * 200}px`, top: `${step.y || index * 100}px` }"
              :class="{ 'border-green-500 border-green-500/50': selectedStep?.id === step.id, 'border-zinc-700/50': selectedStep?.id !== step.id }"
              @click="selectStep(step)"
            >
              <p class="text-xs font-semibold text-white">{{ step.name }}</p>
              <p class="mt-1 text-xs text-zinc-400">{{ step.type }}</p>
              <div class="mt-3 flex gap-1">
                <button
                  @click.stop="deleteStep(step.id)"
                  class="rounded-md border border-red-500/20 bg-red-500/10 px-2 py-0.5 text-xs text-red-300 transition hover:bg-red-500/20"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="flex h-full items-center justify-center">
            <div class="text-center">
              <p class="text-sm text-zinc-400">Select a workflow or create a new one to start</p>
              <p class="mt-2 text-xs text-zinc-500">Drag steps from the toolbar to add them to the canvas</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel: Step Configuration -->
      <div class="w-80 flex-shrink-0 overflow-hidden rounded-lg border border-zinc-700/50 bg-zinc-950/50 flex flex-col">
        <!-- Step Types Toolbar -->
        <div class="border-b border-zinc-700/50 bg-zinc-900/50 px-4 py-3">
          <p class="text-xs font-semibold text-zinc-400">Add Step</p>
          <div class="mt-2 space-y-2">
            <button
              v-for="stepType in stepTypes"
              :key="stepType"
              @click="addStep(stepType)"
              class="w-full rounded-lg border border-zinc-700/50 bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:bg-zinc-800"
            >
              + {{ stepType }}
            </button>
          </div>
        </div>

        <!-- Configuration Panel -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="selectedStep" class="space-y-3">
            <div>
              <label class="text-xs font-semibold text-zinc-400">Step Name</label>
              <input
                v-model="selectedStep.name"
                type="text"
                class="mt-1 w-full rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white placeholder-zinc-500"
              />
            </div>

            <div>
              <label class="text-xs font-semibold text-zinc-400">Description</label>
              <textarea
                v-model="selectedStep.description"
                class="mt-1 w-full h-20 rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white placeholder-zinc-500"
              ></textarea>
            </div>

            <div>
              <label class="text-xs font-semibold text-zinc-400">Agent (if applicable)</label>
              <select
                v-model="selectedStep.agent_id"
                class="mt-1 w-full rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-3 py-2 text-sm text-white"
              >
                <option value="">None</option>
                <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                  {{ agent.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="text-xs font-semibold text-zinc-400">Step Configuration (JSON)</label>
              <textarea
                v-model="selectedStep.config"
                class="mt-1 w-full h-24 rounded-lg border border-zinc-700/50 bg-zinc-900/50 px-2 py-1.5 font-mono text-xs text-white placeholder-zinc-500"
                placeholder='{}'
              ></textarea>
            </div>
          </div>

          <div v-else class="flex items-center justify-center h-full">
            <p class="text-xs text-zinc-400">Select a step to configure</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// State
const workflows = ref([])
const currentWorkflow = ref(null)
const selectedStep = ref(null)
const agents = ref([])
const zoom = ref(1)
const stepTypes = ['Start', 'Action', 'Decision', 'End', 'Webhook', 'Wait']

// Loading states
const loadingWorkflows = ref(false)
const savingWorkflow = ref(false)
const publishingWorkflow = ref(false)

// Computed
const getConnections = computed(() => {
  if (!currentWorkflow.value?.steps || currentWorkflow.value.steps.length < 2) {
    return []
  }

  return currentWorkflow.value.steps.slice(0, -1).map((step, idx) => {
    const nextStep = currentWorkflow.value.steps[idx + 1]
    return {
      fromX: (step.x || idx * 200) + 100,
      fromY: (step.y || idx * 100) + 50,
      toX: (nextStep.x || (idx + 1) * 200) + 100,
      toY: (nextStep.y || (idx + 1) * 100) + 50,
    }
  })
})

// Load workflows
async function loadWorkflows() {
  loadingWorkflows.value = true
  try {
    const response = await fetch('/api/v1/workflows', {
      headers: { 'Accept': 'application/json' },
    })
    if (!response.ok) throw new Error('Failed to load workflows')
    const data = await response.json()
    workflows.value = data.data || data || []
  } catch (err) {
    console.error('Error loading workflows:', err)
  } finally {
    loadingWorkflows.value = false
  }
}

// Load agents
async function loadAgents() {
  try {
    const response = await fetch('/api/v1/agents', {
      headers: { 'Accept': 'application/json' },
    })
    if (!response.ok) throw new Error('Failed to load agents')
    const data = await response.json()
    agents.value = data.data || data || []
  } catch (err) {
    console.error('Error loading agents:', err)
  }
}

// Select workflow
function selectWorkflow(workflow) {
  currentWorkflow.value = workflow
  selectedStep.value = null
}

// Create new workflow
function createNewWorkflow() {
  const newWorkflow = {
    id: `workflow-${Date.now()}`,
    name: 'New Workflow',
    description: '',
    status: 'draft',
    steps: [],
  }
  workflows.value.unshift(newWorkflow)
  selectWorkflow(newWorkflow)
}

// Add step
function addStep(stepType) {
  if (!currentWorkflow.value) {
    createNewWorkflow()
  }

  const newStep = {
    id: `step-${Date.now()}`,
    name: stepType,
    type: stepType,
    description: '',
    agent_id: null,
    config: '{}',
    x: Math.random() * 500,
    y: Math.random() * 300,
  }

  if (!currentWorkflow.value.steps) {
    currentWorkflow.value.steps = []
  }

  currentWorkflow.value.steps.push(newStep)
}

// Delete step
function deleteStep(stepId) {
  if (!currentWorkflow.value) return
  currentWorkflow.value.steps = currentWorkflow.value.steps.filter(s => s.id !== stepId)
  if (selectedStep.value?.id === stepId) {
    selectedStep.value = null
  }
}

// Select step
function selectStep(step) {
  selectedStep.value = step
}

// Zoom controls
function zoomIn() {
  zoom.value = Math.min(zoom.value + 0.1, 2)
}

function zoomOut() {
  zoom.value = Math.max(zoom.value - 0.1, 0.5)
}

function resetZoom() {
  zoom.value = 1
}

// Save workflow
async function saveWorkflow() {
  if (!currentWorkflow.value) return

  savingWorkflow.value = true
  try {
    const url = currentWorkflow.value.id.startsWith('workflow-temp-') ?
      '/api/v1/workflows' :
      `/api/v1/workflows/${currentWorkflow.value.id}`

    const method = currentWorkflow.value.id.startsWith('workflow-temp-') ? 'POST' : 'PUT'

    const response = await fetch(url, {
      method,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(currentWorkflow.value),
    })

    if (!response.ok) throw new Error('Failed to save workflow')

    const data = await response.json()
    if (method === 'POST' && data.data) {
      currentWorkflow.value.id = data.data.id
    }
  } catch (err) {
    console.error('Error saving workflow:', err)
  } finally {
    savingWorkflow.value = false
  }
}

// Publish workflow
async function publishWorkflow() {
  if (!currentWorkflow.value) return

  publishingWorkflow.value = true
  try {
    // First save
    await saveWorkflow()

    // Then publish
    const response = await fetch(`/api/v1/workflows/${currentWorkflow.value.id}/publish`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) throw new Error('Failed to publish workflow')

    currentWorkflow.value.status = 'published'
  } catch (err) {
    console.error('Error publishing workflow:', err)
  } finally {
    publishingWorkflow.value = false
  }
}

// Status class
function getStatusClass(status) {
  const classes = {
    draft: 'bg-yellow-500/20 text-yellow-300',
    published: 'bg-green-500/20 text-green-300',
    archived: 'bg-gray-500/20 text-gray-300',
  }
  return classes[status] || classes.draft
}

// Load initial data
onMounted(() => {
  loadWorkflows()
  loadAgents()
})
</script>
