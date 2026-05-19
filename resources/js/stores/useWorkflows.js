import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useWorkflows = defineStore('workflows', {
  state: () => ({
    workflows: [],
    current: null,
    selectedStep: null,
    executionProgress: 0,
  }),

  getters: {
    currentWorkflowSteps: (state) => state.current?.steps || [],
    isExecuting: (state) => state.executionProgress > 0 && state.executionProgress < 100,
  },

  actions: {
    async fetchWorkflows() {
      const { data } = await axios.get('/api/v1/workflows');
      this.workflows = data;
    },

    selectWorkflow(id) {
      this.current = this.workflows.find(w => w.id === id) || null;
      this.selectedStep = null;
      this.executionProgress = 0;
    },

    selectStep(stepId) {
      this.selectedStep = this.current?.steps.find(s => s.id === stepId) || null;
    },

    updateStepStatus(stepId, status) {
      const step = this.current?.steps.find(s => s.id === stepId);
      if (step) step.status = status;
    },

    setExecutionProgress(progress) {
      this.executionProgress = Math.min(100, Math.max(0, progress));
    },
  },
});
