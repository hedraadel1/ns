<template>
  <div class="rounded border border-red-500/20 bg-red-500/10 p-4 text-red-100">
    <p class="font-semibold uppercase tracking-[0.2em] text-red-300">Error</p>
    <p class="mt-2 text-sm text-red-100">{{ message }}</p>
    <button
      type="button"
      class="mt-4 inline-flex items-center justify-center rounded border border-red-500/30 bg-red-600/10 px-3 py-2 text-sm font-bold uppercase tracking-[0.2em] text-red-100 transition hover:bg-red-600/20"
      @click="$emit('retry')"
    >
      Retry
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  message: {
    type: String,
    default: 'An unexpected error occurred.',
  },
})
</script>
