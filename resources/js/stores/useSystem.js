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
        pageLoading: false,
        pageLoadingProgress: 0,
        theme: typeof window !== 'undefined' ? window.localStorage.getItem('nexus-theme') || 'dark' : 'dark',
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
        setPageLoading(loading) { this.pageLoading = loading; },
        setPageLoadingProgress(progress) { this.pageLoadingProgress = Math.min(progress, 100); },
        startPageLoading() { this.pageLoading = true; this.pageLoadingProgress = 0; },
        completePageLoading() { this.pageLoading = false; this.pageLoadingProgress = 100; },
        setTheme(theme) {
            this.theme = theme;
            if (typeof window !== 'undefined' && window.document?.documentElement) {
                window.document.documentElement.dataset.theme = theme;
                window.localStorage.setItem('nexus-theme', theme);
            }
        },
    },
});
