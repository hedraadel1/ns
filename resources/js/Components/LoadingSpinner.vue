<template>
  <div class="flex items-center justify-center rounded border border-zinc-200/10 bg-zinc-950/80 p-6 text-center text-zinc-300">
    <div class="mr-3 inline-flex h-8 w-8 animate-spin items-center justify-center rounded-full border-2 border-t-transparent border-white/20"></div>
    <div>
      <p class="font-semibold" v-if="message">{{ message }}</p>
      <p class="text-sm text-zinc-400" v-else>Loading...</p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  message: {
    type: String,
    default: '',
  },
})
</script>
