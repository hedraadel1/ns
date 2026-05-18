<template>
  <div
    ref="container"
    class="touch-controls"
    @touchstart="onTouchStart"
    @touchmove="onTouchMove"
    @touchend="onTouchEnd"
    @click="onClick"
  >
    <slot></slot>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'

const props = defineProps({
  swipeThreshold: {
    type: Number,
    default: 50,
  },
  tapThreshold: {
    type: Number,
    default: 10,
  },
})

const emit = defineEmits(['swipe-left', 'swipe-right', 'swipe-up', 'swipe-down', 'tap', 'long-press'])

const container = ref(null)
const touchStart = ref({ x: 0, y: 0, time: 0 })
const touchEnd = ref({ x: 0, y: 0, time: 0 })
const longPressTimer = ref(null)

function onTouchStart(e) {
  const touch = e.touches[0]
  touchStart.value = {
    x: touch.clientX,
    y: touch.clientY,
    time: Date.now(),
  }

  // Long press detection (500ms)
  longPressTimer.value = setTimeout(() => {
    emit('long-press', {
      x: touch.clientX,
      y: touch.clientY,
    })
  }, 500)
}

function onTouchMove(e) {
  // Cancel long press if finger moves
  if (longPressTimer.value) {
    clearTimeout(longPressTimer.value)
    longPressTimer.value = null
  }

  const touch = e.touches[0]
  touchEnd.value = {
    x: touch.clientX,
    y: touch.clientY,
    time: Date.now(),
  }
}

function onTouchEnd() {
  if (longPressTimer.value) {
    clearTimeout(longPressTimer.value)
    longPressTimer.value = null
  }

  const deltaX = touchEnd.value.x - touchStart.value.x
  const deltaY = touchEnd.value.y - touchStart.value.y
  const absDeltaX = Math.abs(deltaX)
  const absDeltaY = Math.abs(deltaY)

  // Check if it's a tap (minimal movement)
  if (absDeltaX < props.tapThreshold && absDeltaY < props.tapThreshold) {
    emit('tap', {
      x: touchStart.value.x,
      y: touchStart.value.y,
    })
    return
  }

  // Determine swipe direction
  if (absDeltaX > absDeltaY) {
    // Horizontal swipe
    if (absDeltaX > props.swipeThreshold) {
      if (deltaX > 0) {
        emit('swipe-right')
      } else {
        emit('swipe-left')
      }
    }
  } else {
    // Vertical swipe
    if (absDeltaY > props.swipeThreshold) {
      if (deltaY > 0) {
        emit('swipe-down')
      } else {
        emit('swipe-up')
      }
    }
  }
}

function onClick(e) {
  // Fallback for non-touch devices
  if (!('ontouchstart' in window)) {
    emit('tap', {
      x: e.clientX,
      y: e.clientY,
    })
  }
}
</script>

<style scoped>
.touch-controls {
  touch-action: pan-y;
  user-select: none;
  -webkit-user-select: none;
  -webkit-tap-highlight-color: transparent;
}

.touch-controls:active {
  transform: scale(0.98);
  transition: transform 0.1s ease;
}
</style>
