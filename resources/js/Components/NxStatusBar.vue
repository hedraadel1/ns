<template>
    <header class="nx-status-bar">
        <div class="zone zone-left">
            <slot name="left">
                <NxConnectionDot :state="connectionState" />
                <NxProviderDots :providers="providers" />
            </slot>
        </div>
        <div class="zone zone-center">
            <slot name="center">
                <NxJobRail :progress="jobProgress" :active="hasActiveJobs" />
            </slot>
        </div>
        <div class="zone zone-right">
            <slot name="right">
                <NxTokenBudget :used="tokenUsed" :budget="tokenBudget" />
                <NxQueuePill :count="queueDepth" :has-failures="hasQueueFailures" />
                <NxNotificationBell />
            </slot>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { useSystem } from '../stores/useSystem';
import NxConnectionDot from './NxConnectionDot.vue';
import NxProviderDots from './NxProviderDots.vue';
import NxJobRail from './NxJobRail.vue';
import NxTokenBudget from './NxTokenBudget.vue';
import NxQueuePill from './NxQueuePill.vue';
import NxNotificationBell from './NxNotificationBell.vue';

const system = useSystem();

const connectionState = computed(() => system.connectionState);
const jobProgress = computed(() => system.jobProgress);
const hasActiveJobs = computed(() => system.jobProgress > 0 && system.jobProgress < 100);
const queueDepth = computed(() => system.queueDepth);
const hasQueueFailures = computed(() => system.hasQueueFailures);
const tokenUsed = computed(() => system.tokenUsed);
const tokenBudget = computed(() => system.tokenBudget);
const providers = computed(() => system.providers);
</script>

<style scoped>
.nx-status-bar {
    height: 40px;
    background: rgba(22, 27, 34, 0.7);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 16px;
    z-index: 40;
    animation: slideDown 200ms ease-out;
}

.zone {
    display: flex;
    align-items: center;
    gap: 12px;
}

.zone-left {
    flex: 1;
    justify-content: flex-start;
}

.zone-center {
    flex: 2;
    justify-content: center;
}

.zone-right {
    flex: 1;
    justify-content: flex-end;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
    }
    to {
        transform: translateY(0);
    }
}
</style>
