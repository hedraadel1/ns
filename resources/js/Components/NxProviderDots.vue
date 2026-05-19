<template>
    <div class="nx-provider-dots">
        <div
            v-for="provider in providers"
            :key="provider.name"
            class="provider-dot"
            :class="`status-${provider.status}`"
            :title="`${provider.name} · ${provider.latency}ms · ${provider.status}`"
        />
    </div>
</template>

<script setup>
defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
});
</script>

<style scoped>
.nx-provider-dots {
    display: flex;
    align-items: center;
    gap: 4px;
}

.provider-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    transition: background-color 200ms ease;
}

.status-online {
    background: #10B981;
}

.status-degraded {
    background: #F59E0B;
}

.status-offline {
    background: #EF4444;
}
</style>
