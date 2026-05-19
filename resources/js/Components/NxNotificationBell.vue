<template>
    <button class="nx-notification-bell" @click="$emit('open-drawer')">
        <BellIcon :size="20" />
        <span v-if="unreadCount > 0" class="badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
    </button>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { Bell } from 'lucide-vue-next';
import { useNotificationStore } from '../stores/useNotificationStore';

const BellIcon = Bell;

const emit = defineEmits(['open-drawer']);

const store = useNotificationStore();
const unreadCount = ref(store.unreadCount);
const bellRef = ref(null);

watch(() => store.unreadCount, (newVal, oldVal) => {
    if (newVal > oldVal) {
        unreadCount.value = newVal;
        // Trigger shake animation
        if (bellRef.value) {
            bellRef.value.style.animation = 'none';
            bellRef.value.offsetHeight; // Trigger reflow
            bellRef.value.style.animation = 'bell-shake 400ms ease';
        }
    }
});

onMounted(() => {
    unreadCount.value = store.unreadCount;
});
</script>

<style scoped>
.nx-notification-bell {
    position: relative;
    background: none;
    border: none;
    color: var(--color-text-secondary);
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.nx-notification-bell:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--color-text-primary);
}

.badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #EF4444;
    color: white;
    border-radius: 9999px;
    min-width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    font-family: 'JetBrains Mono', monospace;
    font-variant-numeric: tabular-nums;
}

@keyframes bell-shake {
    0%, 100% { transform: rotate(0); }
    25% { transform: rotate(-15deg); }
    75% { transform: rotate(15deg); }
}
</style>
