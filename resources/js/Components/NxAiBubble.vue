<template>
  <div class="nx-ai-bubble" :class="{ streaming }">
    <div v-html="renderedContent" class="bubble-content"></div>
    <span v-if="streaming" class="cursor">|</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import MarkdownIt from 'markdown-it';

const props = defineProps({
  content: { type: String, default: '' },
  streaming: { type: Boolean, default: false },
});

const md = new MarkdownIt({ html: false, linkify: true, typographer: true });

const renderedContent = computed(() => {
  return md.render(props.content || '');
});
</script>

<style scoped>
.nx-ai-bubble {
  position: relative;
  font-size: 0.95rem;
  line-height: 1.65;
  color: rgba(255, 255, 255, 0.94);
  background: rgba(15, 23, 42, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 16px;
  overflow-wrap: anywhere;
}

.nx-ai-bubble.streaming {
  background: rgba(15, 23, 42, 1);
}

.bubble-content p {
  margin: 0 0 0.75rem;
}

.bubble-content p:last-child {
  margin-bottom: 0;
}

.cursor {
  display: inline-block;
  margin-left: 2px;
  color: rgba(255, 255, 255, 0.7);
  animation: blink 1s steps(2, start) infinite;
}

@keyframes blink {
  to { visibility: hidden; }
}
</style>
