<template>
  <component
    v-if="iconComponent"
    :is="iconComponent"
    :size="size"
    :stroke-width="strokeWidth"
    :class="componentClass"
  />
</template>

<script setup>
import * as LucideIcons from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
  icon: {
    type: [String, Object, Function],
    required: true,
  },
  size: {
    type: [Number, String],
    default: 16,
  },
  strokeWidth: {
    type: [Number, String],
    default: 2,
  },
  class: {
    type: [String, Array, Object],
    default: '',
  },
});

const iconComponent = computed(() => {
  if (!props.icon) {
    return null;
  }

  if (typeof props.icon === 'function' || typeof props.icon === 'object') {
    return props.icon;
  }

  const normalized = props.icon
    .toString()
    .replace(/[-_ ]+/g, ' ')
    .split(' ')
    .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
    .join('') + 'Icon';

  return LucideIcons[normalized] || null;
});

const componentClass = computed(() => props.class);
</script>
