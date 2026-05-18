<template>
  <Transition
    :name="transitionName"
    :mode="mode"
    :appear="appear"
    @before-enter="onBeforeEnter"
    @enter="onEnter"
    @after-enter="onAfterEnter"
    @before-leave="onBeforeLeave"
    @leave="onLeave"
    @after-leave="onAfterLeave"
  >
    <slot></slot>
  </Transition>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

defineProps({
  transitionName: {
    type: String,
    default: 'fade',
  },
  mode: {
    type: String,
    default: 'in-out',
  },
  appear: {
    type: Boolean,
    default: true,
  },
})

defineEmits([
  'before-enter',
  'enter',
  'after-enter',
  'before-leave',
  'leave',
  'after-leave',
])

function onBeforeEnter(el) {
  el.style.opacity = '0'
  el.style.transform = 'translateY(10px)'
}

function onEnter(el, done) {
  el.style.transition = 'opacity 0.3s ease, transform 0.3s ease'
  el.style.opacity = '1'
  el.style.transform = 'translateY(0)'
  done()
}

function onAfterEnter(el) {
  el.style.transition = ''
}

function onBeforeLeave(el) {
  el.style.opacity = '1'
  el.style.transform = 'translateY(0)'
}

function onLeave(el, done) {
  el.style.transition = 'opacity 0.2s ease, transform 0.2s ease'
  el.style.opacity = '0'
  el.style.transform = 'translateY(-10px)'
  done()
}

function onAfterLeave(el) {
  el.style.transition = ''
}
</script>

<style scoped>
/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Slide transition */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

/* Scale transition */
.scale-enter-active,
.scale-leave-active {
  transition: all 0.3s ease;
}

.scale-enter-from,
.scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Slide up transition */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
