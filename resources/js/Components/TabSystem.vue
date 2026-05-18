<template>
  <div class="rounded-2xl border border-slate-800 bg-slate-950/80 p-2">
    <div class="overflow-x-auto">
      <div class="inline-flex min-w-full gap-2">
        <button
          v-for="tab in displayTabs"
          :key="tab.key ?? tab.id"
          type="button"
          class="whitespace-nowrap rounded-full border px-4 py-2 text-sm font-medium transition"
          :class="resolveTabKey(tab) === currentTab
            ? 'border-emerald-500/40 bg-emerald-500/15 text-emerald-300'
            : 'border-slate-800 bg-slate-900/40 text-slate-300 hover:border-slate-700 hover:bg-slate-900 hover:text-white'"
          @click="activate(resolveTabKey(tab))"
        >
          <span v-if="tab.icon" class="mr-2">{{ tab.icon }}</span>
          {{ tab.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  tabs: {
    type: Array,
    default: () => [],
  },
  items: {
    type: Array,
    default: () => [],
  },
  activeTab: {
    type: String,
    default: '',
  },
  modelValue: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['select', 'change', 'update:activeTab', 'update:modelValue']);

const displayTabs = computed(() => (props.tabs.length ? props.tabs : props.items));
const resolveTabKey = (tab) => tab?.key ?? tab?.id ?? '';
const currentTab = computed(() => props.activeTab || props.modelValue || resolveTabKey(displayTabs.value[0] || {}));

const activate = (tabKey) => {
  if (!tabKey) {
    return;
  }

  emit('select', tabKey);
  emit('change', tabKey);
  emit('update:activeTab', tabKey);
  emit('update:modelValue', tabKey);

  if (typeof window !== 'undefined') {
    window.location.hash = tabKey;
  }
};
</script>