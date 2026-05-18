<template>
  <nav class="breadcrumbs">
    <span
      v-for="(item, i) in items"
      :key="i"
      :class="['crumb', { active: item.active }]"
    >
      <a v-if="item.href" :href="item.href">{{ item.label }}</a>
      <span v-else>{{ item.label }}</span>
    </span>
  </nav>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<style scoped>
.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.crumb {
  color: #888;
}

.crumb a {
  color: #888;
  text-decoration: none;
}

.crumb a:hover {
  color: #4ade80;
}

.crumb.active {
  color: #fff;
}

.crumb:not(:last-child)::after {
  content: '/';
  margin-left: 0.5rem;
  color: #666;
}
</style>
