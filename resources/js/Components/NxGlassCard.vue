<template>
  <article class="nx-glass-card" :class="[`elevation-${elevation}`, { hoverable }]">
    <div v-if="$slots.header" class="card-header">
      <slot name="header" />
    </div>
    <div class="card-body">
      <slot />
    </div>
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </article>
</template>

<script setup>
defineProps({
  elevation: {
    type: Number,
    default: 2,
    validator: (v) => v >= 1 && v <= 3,
  },
  hoverable: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped>
.nx-glass-card {
  background: rgba(22, 27, 34, 0.7);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.elevation-1 {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
}

.elevation-2 {
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.5);
}

.elevation-3 {
  box-shadow: 0 20px 25px rgba(0, 0, 0, 0.6);
}

.hoverable {
  transition: transform 150ms ease, box-shadow 150ms ease;
}

.hoverable:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 20px rgba(0, 0, 0, 0.5);
}

.card-header {
  position: sticky;
  top: 0;
  z-index: 10;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.card-body {
  flex: 1;
  overflow-y: auto;
}

.card-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}
</style>
