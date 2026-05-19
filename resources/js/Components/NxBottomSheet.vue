<template>
  <div v-if="open" class="nx-bottom-sheet">
    <div class="sheet-backdrop" @click="closeSheet" />
    <section
      ref="sheet"
      class="sheet-panel"
      :style="panelStyle"
      @pointerdown.prevent="onDragStart"
      @pointermove.prevent="onDragMove"
      @pointerup.prevent="onDragEnd"
      @pointercancel.prevent="onDragEnd"
      @pointerleave.prevent="onDragEnd"
    >
      <div class="sheet-handle" />
      <div class="sheet-header">
        <div>
          <p class="sheet-title">{{ title }}</p>
          <p class="sheet-subtitle" v-if="subtitle">{{ subtitle }}</p>
        </div>
        <button type="button" class="sheet-close" @click="closeSheet">Close</button>
      </div>
      <div class="sheet-body">
        <slot />
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Actions',
  },
  subtitle: {
    type: String,
    default: '',
  },
  snapPoints: {
    type: Array,
    default: () => [0.55, 0.85],
  },
})

const emit = defineEmits(['close'])
const sheet = ref(null)
const windowHeight = ref(typeof window !== 'undefined' ? window.innerHeight : 0)
const currentY = ref(windowHeight.value)
const dragStartY = ref(0)
const dragActive = ref(false)
const targetY = ref(windowHeight.value)

const panelStyle = computed(() => ({
  transform: `translateY(${currentY.value}px)`,
}))

function updateHeight() {
  windowHeight.value = window.innerHeight
  if (!props.open) {
    currentY.value = windowHeight.value
    targetY.value = windowHeight.value
    return
  }
  targetY.value = Math.round(windowHeight.value * (1 - props.snapPoints[0]))
  currentY.value = targetY.value
}

function closeSheet() {
  emit('close')
}

function nearestPoint(y) {
  const points = props.snapPoints.map((point) => windowHeight.value * (1 - point))
  points.push(windowHeight.value)
  let closest = points[0]
  for (const point of points) {
    if (Math.abs(point - y) < Math.abs(closest - y)) {
      closest = point
    }
  }
  return closest
}

function onDragStart(event) {
  if (event.pointerType !== 'touch' && event.pointerType !== 'pen') return
  dragStartY.value = event.clientY
  dragActive.value = true
}

function onDragMove(event) {
  if (!dragActive.value) return
  const delta = event.clientY - dragStartY.value
  const nextY = Math.min(Math.max(targetY.value + delta, 0), windowHeight.value)
  currentY.value = nextY
}

function onDragEnd() {
  if (!dragActive.value) return
  dragActive.value = false
  const clamped = Math.min(Math.max(currentY.value, 0), windowHeight.value)
  const snapY = nearestPoint(clamped)
  if (clamped > windowHeight.value * 0.75) {
    closeSheet()
    return
  }
  currentY.value = snapY
  targetY.value = snapY
}

watch(
  () => props.open,
  (open) => {
    if (open) {
      updateHeight()
      window.setTimeout(() => {
        currentY.value = Math.round(windowHeight.value * (1 - props.snapPoints[0]))
        targetY.value = currentY.value
      }, 0)
      return
    }
    currentY.value = windowHeight.value
    targetY.value = windowHeight.value
  },
  { immediate: true }
)

onMounted(() => {
  updateHeight()
  window.addEventListener('resize', updateHeight)
})
</script>

<style scoped>
.nx-bottom-sheet {
  position: fixed;
  inset: 0;
  z-index: 60;
}

.sheet-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(6px);
}

.sheet-panel {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  min-height: 220px;
  max-height: 95vh;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  background: rgba(15, 23, 42, 0.96);
  box-shadow: 0 -18px 64px rgba(0, 0, 0, 0.35);
  padding: 1rem;
  transition: transform 200ms ease;
  touch-action: none;
  display: flex;
  flex-direction: column;
}

.sheet-handle {
  width: 42px;
  height: 6px;
  border-radius: 9999px;
  background: rgba(148, 163, 184, 0.25);
  margin: 0 auto 0.85rem;
}

.sheet-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.sheet-title {
  font-size: 1rem;
  font-weight: 700;
  color: #f8fafc;
}

.sheet-subtitle {
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #94a3b8;
}

.sheet-close {
  border: none;
  background: rgba(255, 255, 255, 0.08);
  color: #f8fafc;
  padding: 0.75rem 1rem;
  border-radius: 9999px;
  cursor: pointer;
}

.sheet-body {
  flex: 1;
  overflow-y: auto;
  padding-right: 0.25rem;
}
</style>
