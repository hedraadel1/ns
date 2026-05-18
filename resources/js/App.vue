<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex min-h-screen max-w-7xl flex-col lg:flex-row">
      <Navigation
        class="lg:w-80 lg:flex-shrink-0"
        :tabs="hubTabs"
        :active-tab="activeHub"
        @select="setActiveHub"
      />

      <div class="flex min-w-0 flex-1 flex-col border-t border-slate-800/70 lg:border-l lg:border-t-0">
        <header class="border-b border-slate-800 bg-slate-900/90 px-5 py-4 backdrop-blur">
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-400">Shell navigation</p>
          <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h1 class="text-xl font-semibold text-white">{{ currentHub?.label ?? 'Hub' }}</h1>
              <p class="mt-1 max-w-2xl text-sm text-slate-400">
                {{ currentHub?.description ?? 'Select a hub to continue.' }}
              </p>
            </div>

            <div class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-300">
              {{ hubTabs.length }} hubs reachable
            </div>
          </div>
        </header>

        <div class="border-b border-slate-800 bg-slate-900/60 px-4 py-3">
          <TabSystem :tabs="hubTabs" :active-tab="activeHub" @select="setActiveHub" />
        </div>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
          <component
            v-if="currentPageComponent"
            :is="currentPageComponent"
            :key="activeHub"
          />

          <section
            v-else
            class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-6 text-amber-100"
          >
            <h2 class="text-lg font-semibold">Hub unavailable</h2>
            <p class="mt-2 text-sm leading-6 text-amber-50/90">
              The <span class="font-medium">{{ currentHub?.label ?? activeHub }}</span> hub does not have a page component yet.
              Use one of the reachable hubs below or add a matching <code class="rounded bg-black/20 px-1 py-0.5 text-[0.8em]">*View.vue</code> file.
            </p>

            <div class="mt-5 flex flex-wrap gap-2">
              <button
                v-for="tab in hubTabs"
                :key="tab.key + '-fallback'"
                type="button"
                class="rounded-full border border-emerald-500/30 bg-slate-950/40 px-3 py-1.5 text-sm text-slate-100 transition hover:border-emerald-400 hover:text-white"
                @click="setActiveHub(tab.key)"
              >
                {{ tab.label }}
              </button>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, onBeforeUnmount, onMounted, ref } from 'vue';
import Navigation from './Components/Navigation.vue';
import TabSystem from './Components/TabSystem.vue';

const pageModules = import.meta.glob('./Pages/*View.vue');
const hubQueryKey = 'hub';

const hubDefinitions = [
  {
    key: 'agents',
    label: 'Agents',
    icon: '🤖',
    description: 'Manage agent personas, routing, and assistant behaviors.',
    candidates: ['AgentsView.vue'],
  },
  {
    key: 'workflows',
    label: 'Workflows',
    icon: '⚡',
    description: 'Inspect and build automations that move work forward.',
    candidates: ['WorkflowsView.vue'],
  },
  {
    key: 'settings',
    label: 'Settings',
    icon: '⚙️',
    description: 'Tune account, system, and environment-wide preferences.',
    candidates: ['SettingsView.vue'],
  },
  {
    key: 'contacts',
    label: 'Contacts',
    icon: '👥',
    description: 'Browse customer records and relationship context.',
    candidates: ['ContactsView.vue'],
  },
  {
    key: 'conversations',
    label: 'Conversations',
    icon: '💬',
    description: 'Multi-channel communication and message management.',
    candidates: ['ConversationsView.vue'],
  },
  {
    key: 'logs',
    label: 'Logs',
    icon: '📜',
    description: 'Review event streams, traces, and recent system activity.',
    candidates: ['LogsView.vue'],
  },
  {
    key: 'memory',
    label: 'Memory',
    icon: '🧠',
    description: 'Inspect stored memories, context, and recall state.',
    candidates: ['MemoryView.vue'],
  },
  {
    key: 'nexus',
    label: 'Nexus',
    icon: '🧭',
    description: 'Open the orchestration hub and shared coordination surface.',
    candidates: ['NexusView.vue'],
  },
  {
    key: 'ai-models',
    label: 'AI Models',
    icon: '🧪',
    description: 'Compare available models and configure model defaults.',
    candidates: ['AIModelsView.vue'],
  },
];

const knownCandidateFiles = new Set(hubDefinitions.flatMap((hub) => hub.candidates));

const resolveLoader = (candidates) => {
  for (const fileName of candidates) {
    const moduleKey = `./Pages/${fileName}`;
    if (pageModules[moduleKey]) {
      return pageModules[moduleKey];
    }
  }

  return null;
};

const labelFromFileName = (fileName) =>
  fileName
    .replace(/View\.vue$/, '')
    .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
    .replace(/[-_]+/g, ' ')
    .trim()
    .replace(/\b\w/g, (match) => match.toUpperCase());

const keyFromFileName = (fileName) =>
  fileName
    .replace(/View\.vue$/, '')
    .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
    .replace(/[_\s]+/g, '-')
    .toLowerCase();

const iconFromKey = (key) => {
  if (key.includes('log')) return '📜';
  if (key.includes('memory')) return '🧠';
  if (key.includes('contact')) return '👥';
  if (key.includes('workflow')) return '⚡';
  if (key.includes('agent')) return '🤖';
  if (key.includes('setting')) return '⚙️';
  if (key.includes('model')) return '🧪';
  if (key.includes('nexus')) return '🧭';
  return '▣';
};

const availableHubs = [
  ...hubDefinitions
    .map((definition) => ({
      ...definition,
      loader: resolveLoader(definition.candidates),
    }))
    .filter((definition) => definition.loader),
  ...Object.keys(pageModules)
    .map((moduleKey) => moduleKey.split('/').pop())
    .filter((fileName) => fileName && !knownCandidateFiles.has(fileName) && /View\.vue$/.test(fileName))
    .map((fileName) => ({
      key: keyFromFileName(fileName),
      label: labelFromFileName(fileName),
      icon: iconFromKey(keyFromFileName(fileName)),
      description: `${labelFromFileName(fileName)} hub`,
      candidates: [fileName],
      loader: pageModules[`./Pages/${fileName}`],
    })),
];

const fallbackHub = availableHubs[0]?.key ?? hubDefinitions[0].key;
const activeHub = ref(fallbackHub);

const isValidHub = (value) => availableHubs.some((hub) => hub.key === value);

const getHubFromLocation = () => {
  if (typeof window === 'undefined') {
    return fallbackHub;
  }

  const url = new URL(window.location.href);
  const queryHub = url.searchParams.get(hubQueryKey)?.trim();
  if (isValidHub(queryHub)) {
    return queryHub;
  }

  const rawHash = window.location.hash.replace(/^#\/?/, '').trim();
  const hashHub = rawHash.includes('=') ? rawHash.split('=').pop() : rawHash;
  return isValidHub(hashHub) ? hashHub : fallbackHub;
};

const syncLocationToHub = (hubKey) => {
  if (typeof window === 'undefined') {
    return;
  }

  const url = new URL(window.location.href);
  url.searchParams.set(hubQueryKey, hubKey);
  url.hash = hubKey;
  window.history.replaceState({}, '', url);
};

const currentHub = computed(
  () => availableHubs.find((hub) => hub.key === activeHub.value) ?? null,
);

const currentPageComponent = computed(() => {
  const hub = currentHub.value;
  if (!hub?.loader) {
    return null;
  }

  return defineAsyncComponent(hub.loader);
});

const setActiveHub = (hubKey) => {
  const nextHub = isValidHub(hubKey) ? hubKey : fallbackHub;
  if (!nextHub || activeHub.value === nextHub) {
    return;
  }

  activeHub.value = nextHub;
  syncLocationToHub(nextHub);
};

const syncHubFromLocation = () => {
  const nextHub = getHubFromLocation();
  activeHub.value = nextHub;
  if (typeof window !== 'undefined') {
    syncLocationToHub(nextHub);
  }
};

onMounted(() => {
  syncHubFromLocation();

  if (typeof window !== 'undefined') {
    window.addEventListener('hashchange', syncHubFromLocation);
  }
});

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('hashchange', syncHubFromLocation);
  }
});

const hubTabs = computed(() =>
  availableHubs.map((hub) => ({
    key: hub.key,
    label: hub.label,
    icon: hub.icon,
    description: hub.description,
  })),
);
</script>
