<template>
  <div class="nx-pull-refresh" ref="container" @touchstart="onTouchStart" @touchmove="onTouchMove" @touchend="onTouchEnd">
    <div class="refresh-indicator" :style="indicatorStyle">
      <span class="indicator-ring" :class="{ active: readyToRefresh }" />
      <span class="indicator-text">{{ indicatorText }}</span>
    </div>
    <div class="refresh-content" :style="contentStyle">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'

const props = defineProps({
  threshold: {
    type: Number,
    default: 60,
  },
  maxPull: {
    type: Number,
    default: 120,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['refresh'])
const container = ref(null)
const startY = ref(0)
const touchOffset = ref(0)
const refreshing = ref(false)
const isPulling = ref(false)
let touchActive = false

const readyToRefresh = computed(() => touchOffset.value >= props.threshold)
const indicatorText = computed(() => {
  if (refreshing.value) {
    return 'Refreshing…'
  }
  return readyToRefresh.value ? 'Release to refresh' : 'Pull to refresh'
})

const indicatorStyle = computed(() => ({
  transform: `translateY(${touchOffset.value}px)`,
}))

const contentStyle = computed(() => ({
  transform: `translateY(${touchOffset.value}px)`,
}))

function resetPull() {
  touchOffset.value = 0
  refreshing.value = false
  isPulling.value = false
  touchActive = false
}

function onTouchStart(e) {
  if (props.disabled || refreshing.value) return
  const touch = e.touches[0]
  const scrollTop = container.value?.scrollTop ?? 0
  if (scrollTop > 0) return

  startY.value = touch.clientY
  touchActive = true
  isPulling.value = true
}

function onTouchMove(e) {
  if (!touchActive || props.disabled || refreshing.value || !isPulling.value) return
  const touch = e.touches[0]
  const delta = touch.clientY - startY.value
  if (delta <= 0) return
  e.preventDefault()
  touchOffset.value = Math.min(props.maxPull, delta)
}

function onTouchEnd() {
  if (!touchActive || props.disabled || refreshing.value || !isPulling.value) {
    resetPull()
    return
  }

  if (touchOffset.value >= props.threshold) {
    refreshing.value = true
    emit('refresh')
    window.setTimeout(() => {
      resetPull()
    }, 450)
    return
  }

  resetPull()
}

onBeforeUnmount(() => {
  touchActive = false
})
</script>

<style scoped>
.nx-pull-refresh {
  position: relative;
  width: 100%;
}

.refresh-indicator {
  position: absolute;
  inset-inline: 0;
  top: -48px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  height: 48px;
  color: #cbd5e1;
  font-size: 0.85rem;
  pointer-events: none;
  transition: transform 180ms ease, opacity 180ms ease;
}

.indicator-ring {
  width: 1.1rem;
  height: 1.1rem;
  border: 2px solid rgba(148, 163, 184, 0.75);
  border-radius: 9999px;
  box-sizing: border-box;
}

.indicator-ring.active {
  border-color: #34d399;
  background: rgba(52, 211, 153, 0.15);
}

.indicator-text {
  color: #cbd5e1;
}

.refresh-content {
  transition: transform 180ms ease;
}
</style>
