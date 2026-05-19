<template>
  <div class="nx-channel-status">
    <div
      v-for="channel in channels"
      :key="channel.type"
      class="channel-badge"
      :class="badgeClass(channel)"
      @click="$emit('select', channel.type)"
      role="button"
      tabindex="0"
    >
      <span class="badge-icon">{{ channelIcon(channel.type) }}</span>
      <span class="badge-label">{{ channel.type }}</span>
      <span class="status-dot" :class="statusClass(channel.status)"></span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  channels: {
    type: Array,
    default: () => [],
  },
})

function badgeClass(channel) {
  return {
    whatsapp: 'badge-whatsapp',
    sms: 'badge-sms',
    email: 'badge-email',
  }[String(channel.type || '').toLowerCase()] || 'badge-default'
}

function statusClass(status) {
  const normalized = String(status || '').toLowerCase()
  return {
    online: 'status-online',
    active: 'status-online',
    idle: 'status-idle',
    offline: 'status-offline',
  }[normalized] || 'status-offline'
}

function channelIcon(type) {
  const key = String(type || '').toLowerCase()
  if (key === 'whatsapp') return 'W'
  if (key === 'sms') return 'S'
  if (key === 'email') return '✉'
  return '•'
}
</script>

<style scoped>
.nx-channel-status {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.channel-badge {
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 0.9rem;
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.04);
  color: #f8fafc;
  transition: transform 150ms ease, background 150ms ease;
}

.channel-badge:hover {
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.08);
}

.badge-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.08);
  font-size: 0.8rem;
}

.channel-badge.badge-whatsapp {
  border-color: #25d366;
}

.channel-badge.badge-sms {
  border-color: #007aff;
}

.channel-badge.badge-email {
  border-color: #64748b;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #94a3b8;
}

.status-online {
  background: #34d399;
}

.status-idle {
  background: #f59e0b;
}

.status-offline {
  background: #64748b;
}
</style>
