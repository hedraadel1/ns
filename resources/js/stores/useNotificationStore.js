import { defineStore } from 'pinia';

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        toasts: [],
        unreadCount: 0,
        pendingUndo: null,
    }),
    actions: {
        addToast(payload) {
            this.toasts.push({ ...payload, id: Date.now() });
            if (payload.type !== 'success') this.unreadCount++;
        },
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) this.toasts.splice(index, 1);
        },
        incrementUnread() { this.unreadCount++; },
        markAllRead() { this.unreadCount = 0; },
        setUndo(action) {
            this.pendingUndo = { ...action, expiresAt: Date.now() + 8000 };
        },
        clearUndo() { this.pendingUndo = null; },
    },
});
