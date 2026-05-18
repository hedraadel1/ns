<template>
  <aside class="flex h-full flex-col border-r border-slate-800 bg-slate-950/95">
    <div class="border-b border-slate-800 px-5 py-5">
      <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-400">Navigation</p>
      <h2 class="mt-2 text-lg font-semibold text-white">Hubs</h2>
      <p class="mt-1 text-sm text-slate-400">Jump directly to any reachable workspace.</p>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
      <ul class="space-y-2">
        <li v-for="tab in displayTabs" :key="tab.key ?? tab.id">
          <button
            type="button"
            class="flex w-full items-start justify-between rounded-xl border px-4 py-3 text-left transition"
            :class="resolveTabKey(tab) === currentTab
              ? 'border-emerald-500/40 bg-emerald-500/10 text-white shadow-[0_0_0_1px_rgba(16,185,129,0.12)]'
              : 'border-slate-800 bg-slate-900/50 text-slate-300 hover:border-slate-700 hover:bg-slate-900 hover:text-white'"
            @click="activate(resolveTabKey(tab))"
          >
            <span class="flex min-w-0 items-start gap-3">
              <span
                v-if="tab.icon"
                class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-800 text-sm"
              >
                {{ tab.icon }}
              </span>
              <span class="min-w-0">
                <span class="block text-sm font-semibold">{{ tab.label }}</span>
                <span v-if="tab.description" class="mt-1 block text-xs leading-5 text-slate-400">
                  {{ tab.description }}
                </span>
              </span>
            </span>
            <span
              class="ml-3 mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-semibold"
              :class="resolveTabKey(tab) === currentTab
                ? 'bg-emerald-500/20 text-emerald-300'
                : 'bg-slate-800 text-slate-400'"
            >
              {{ indexFor(resolveTabKey(tab)) }}
            </span>
          </button>
        </li>
      </ul>
    </nav>
  </aside>
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
const currentTab = computed(() => props.activeTab || props.modelValue || resolveTabKey(displayTabs.value[0] || {}));

const resolveTabKey = (tab) => tab?.key ?? tab?.id ?? '';

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

const indexFor = (tabKey) => {
  const position = displayTabs.value.findIndex((tab) => resolveTabKey(tab) === tabKey);
  return position >= 0 ? String(position + 1).padStart(2, '0') : '--';
};
</script>