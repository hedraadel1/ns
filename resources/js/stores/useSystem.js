import { defineStore } from 'pinia';

export const useSystem = defineStore('system', {
    state: () => ({
        connectionState: 'connecting',
        jobProgress: 0,
        queueDepth: 0,
        hasQueueFailures: false,
        activeAgentCount: 0,
        tokenUsed: 0,
        tokenBudget: 6000,
        providers: [],
        rateLimitInfo: null,
    }),
    actions: {
        setConnectionState(state) { this.connectionState = state; },
        updateJobProgress(progress) { this.jobProgress = progress; },
        updateQueueDepth(depth) { this.queueDepth = depth; },
        setQueueFailures(hasFailures) { this.hasQueueFailures = hasFailures; },
        setActiveAgentCount(count) { this.activeAgentCount = count; },
        setTokenUsage(used, budget) { this.tokenUsed = used; this.tokenBudget = budget; },
        setProviders(providers) { this.providers = providers; },
        setRateLimit(info) { this.rateLimitInfo = info; },
        clearRateLimit() { this.rateLimitInfo = null; },
    },
});
