<template>
  <section class="space-y-5">
    <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20 backdrop-blur">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">AI Models hub</p>
          <h1 class="mt-1 text-2xl font-semibold text-white">Model availability and defaults</h1>
          <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-400">
            Compare available models, review recommended use cases, and keep the shell entry reachable while the
            model-management API contract evolves.
          </p>
        </div>

        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
          Reachable from shell navigation
        </div>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Providers</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ stats.providers }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Active model</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ stats.active }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Fallbacks</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ stats.fallbacks }}</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
          <p class="text-xs uppercase tracking-wide text-slate-500">Latency target</p>
          <p class="mt-2 text-2xl font-semibold text-white">{{ stats.latency }}</p>
        </div>
      </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
      <div class="space-y-3">
        <article
          v-for="model in models"
          :key="model.key"
          class="rounded-2xl border p-4 transition"
          :class="selectedModel?.key === model.key ? 'border-emerald-400/40 bg-emerald-500/10' : 'border-white/10 bg-slate-900/70 hover:border-emerald-500/30 hover:bg-white/5'"
          @click="selectedModel = model"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-white">{{ model.name }}</h2>
              <p class="mt-1 text-sm text-slate-400">{{ model.description }}</p>
            </div>

            <span class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide" :class="model.badgeClass">
              {{ model.status }}
            </span>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Context</p>
              <p class="mt-1 text-sm text-white">{{ model.context }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Modalities</p>
              <p class="mt-1 text-sm text-white">{{ model.modalities }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Cost tier</p>
              <p class="mt-1 text-sm text-white">{{ model.costTier }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-[11px] uppercase tracking-wide text-slate-500">Updated</p>
              <p class="mt-1 text-sm text-white">{{ model.updatedAt }}</p>
            </div>
          </div>
        </article>
      </div>

      <aside class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
        <template v-if="selectedModel">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400/80">Selected model</p>
          <h2 class="mt-1 text-xl font-semibold text-white">{{ selectedModel.name }}</h2>
          <p class="mt-2 text-sm text-slate-400">{{ selectedModel.description }}</p>

          <div class="mt-5 space-y-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Provider</p>
              <p class="mt-1 text-sm text-white">{{ selectedModel.provider }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Best for</p>
              <p class="mt-1 text-sm text-white">{{ selectedModel.bestFor }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3">
              <p class="text-xs uppercase tracking-wide text-slate-500">Strengths</p>
              <ul class="mt-2 space-y-1 text-sm text-slate-200">
                <li v-for="item in selectedModel.strengths" :key="item">• {{ item }}</li>
              </ul>
            </div>
          </div>
        </template>

        <div v-else class="rounded-xl border border-dashed border-white/10 bg-white/5 p-5 text-sm text-slate-400">
          Select a model to inspect details.
        </div>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const stats = {
  providers: '3',
  active: 'Auto',
  fallbacks: '2',
  latency: '< 1s',
}

const models = [
  {
    key: 'gpt-4.1',
    name: 'GPT-4.1',
    provider: 'OpenAI',
    status: 'recommended',
    description: 'Balanced general-purpose model for assistant tasks, routing, and structured generation.',
    context: 'Large',
    modalities: 'Text',
    costTier: 'Standard',
    updatedAt: 'Current',
    bestFor: 'Complex reasoning and high-quality responses',
    strengths: ['Reliable instruction following', 'Strong tool use', 'Good for orchestration'],
    badgeClass: 'bg-emerald-500/15 text-emerald-300',
  },
  {
    key: 'claude-sonnet',
    name: 'Claude Sonnet',
    provider: 'Anthropic',
    status: 'fallback',
    description: 'Good alternative for long-form writing, summarization, and nuanced conversation.',
    context: 'Large',
    modalities: 'Text',
    costTier: 'Balanced',
    updatedAt: 'Current',
    bestFor: 'Long-context analysis and copy refinement',
    strengths: ['Readable output', 'Stable long-context behavior', 'Concise summaries'],
    badgeClass: 'bg-sky-500/15 text-sky-300',
  },
  {
    key: 'local-mini',
    name: 'Local Mini',
    provider: 'Self-hosted',
    status: 'offline',
    description: 'Compact local option for low-latency or private workflows.',
    context: 'Medium',
    modalities: 'Text',
    costTier: 'Low',
    updatedAt: 'Configured',
    bestFor: 'Private or offline-first use cases',
    strengths: ['Low latency', 'Local deployment', 'Reduced external dependency'],
    badgeClass: 'bg-slate-500/15 text-slate-300',
  },
]

const selectedModel = ref(models[0])
</script>