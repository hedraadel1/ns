import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useChat = defineStore('chat', {
  state: () => ({
    messages: [],
    streaming: false,
    draft: '',
    sessionId: null,
    contextTokens: 0,
    maxTokens: 6000,
  }),

  getters: {
    currentSessionMessages: (state) => state.messages.filter(m => m.sessionId === state.sessionId),
    tokenPercentage: (state) => (state.contextTokens / state.maxTokens) * 100,
  },

  actions: {
    setSession(id) {
      this.sessionId = id;
    },

    setDraft(text) {
      this.draft = text;
    },

    clearDraft() {
      this.draft = '';
    },

    addMessage(message) {
      this.messages.push(message);
    },

    sendMessage(content) {
      const userMsg = {
        id: Date.now(),
        role: 'user',
        content,
        sessionId: this.sessionId,
        timestamp: new Date(),
      };
      this.messages.push(userMsg);
      this.draft = '';
      return userMsg;
    },

    streamToken(token) {
      if (this.messages.length > 0) {
        const lastMsg = this.messages[this.messages.length - 1];
        if (lastMsg.role === 'agent') {
          lastMsg.content += token;
          this.contextTokens++;
        }
      }
    },

    finalizeMessage() {
      this.streaming = false;
    },

    revertLastMessage() {
      if (this.messages.length > 0) {
        const removed = this.messages.pop();
        if (removed?.role === 'user') this.draft = removed.content;
      }
    },
  },
});
