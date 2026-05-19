<template>
  <section class="nx-relation-timeline">
    <div class="timeline-header">
      <p class="timeline-title">Relationship timeline</p>
      <p class="timeline-subtitle">Recent milestones and engagement points</p>
    </div>
    <div class="timeline-track">
      <div class="timeline-line"></div>
      <div v-for="(event, index) in events" :key="`${event.label}-${index}`" class="timeline-event" :class="{ milestone: index === 0 }">
        <div class="event-dot"></div>
        <div class="event-card">
          <p class="event-label">{{ event.label }}</p>
          <p class="event-date">{{ event.date }}</p>
          <p class="event-detail">{{ event.detail }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  events: {
    type: Array,
    default: () => [],
  },
})
</script>

<style scoped>
.nx-relation-timeline {
  background: rgba(15, 23, 42, 0.86);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 1rem;
  padding: 1rem;
}

.timeline-header {
  margin-bottom: 1rem;
}

.timeline-title {
  color: #f8fafc;
  font-size: 0.95rem;
  font-weight: 700;
}

.timeline-subtitle {
  color: #94a3b8;
  font-size: 0.75rem;
}

.timeline-track {
  position: relative;
  padding-left: 1.5rem;
}

.timeline-line {
  background: rgba(148, 163, 184, 0.2);
  left: 6px;
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  animation: pulse-line 4s ease-in-out infinite;
}

@media (max-width: 800px) {
  .timeline-track {
    padding-left: 0;
    display: grid;
    gap: 1rem;
  }

  .timeline-line {
    display: none;
  }

  .timeline-event {
    flex-direction: column;
    padding-left: 0;
  }
}

.timeline-event {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
  position: relative;
  padding-left: 1rem;
}

.timeline-event:last-child {
  margin-bottom: 0;
}

.event-dot {
  background: #2563eb;
  border: 2px solid rgba(15, 23, 42, 0.9);
  border-radius: 9999px;
  height: 14px;
  width: 14px;
}

.timeline-event.milestone .event-dot {
  background: #f59e0b;
  box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.12);
  height: 18px;
  width: 18px;
}

.event-card {
  background: rgba(51, 65, 85, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 1rem;
  padding: 0.85rem 1rem;
  width: 100%;
}

.event-label {
  color: #f8fafc;
  font-size: 0.85rem;
  font-weight: 700;
}

.event-date {
  color: #94a3b8;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.event-detail {
  color: #cbd5e1;
  font-size: 0.8rem;
  margin-top: 0.5rem;
}

@keyframes pulse-line {
  0%, 100% { opacity: 0.35; }
  50% { opacity: 1; }
}
</style>
