<template>
  <section class="nx-contact-card-3d" @mousemove="handleMouseMove" @mouseleave="resetTilt" @click="toggleFlip">
    <div class="card-shell" :style="containerStyle">
      <div class="card-inner" :class="{ flipped: internalFlipped }" :style="innerStyle">
        <div class="card-face card-front">
          <div class="card-front-content">
            <div class="avatar-ring" :class="{ 'ring-active': isActiveToday }">
              <img :src="avatarUrl" alt="avatar" class="avatar-image" loading="lazy" />
              <span class="presence-dot" :class="statusClass"></span>
            </div>
            <div class="contact-summary">
              <p class="contact-status">{{ statusLabel }}</p>
              <h3 class="contact-name">{{ displayName }}</h3>
              <p class="contact-title">{{ contact.title || contact.role || 'Relationship profile' }}</p>
            </div>
          </div>

          <div class="front-stats">
            <div class="stat-card">
              <span class="stat-label">Last Active</span>
              <strong>{{ lastActive }}</strong>
            </div>
            <div class="stat-card">
              <span class="stat-label">Company</span>
              <strong>{{ contact.company || contact.organization || 'N/A' }}</strong>
            </div>
            <div class="stat-card">
              <span class="stat-label">Email</span>
              <strong>{{ contact.email || '—' }}</strong>
            </div>
          </div>

          <div class="card-footer">
            <button type="button" class="flip-button">Flip for insights</button>
          </div>
        </div>

        <div class="card-face card-back">
          <div class="card-back-header">
            <h3>Relationship intelligence</h3>
            <p>{{ relationshipSummary }}</p>
          </div>

          <div class="back-widgets">
            <NxEmotionRadar :baseline="contact.emotional_baseline" />
            <NxRelationTimeline :events="timelineEvents" />
          </div>

          <div class="back-actions">
            <div class="traits-panel">
              <h4>Top traits</h4>
              <ul>
                <li v-for="trait in personalityTraits" :key="trait">{{ trait }}</li>
              </ul>
            </div>
            <NxConflictDiff :conflicts="conflictPairs" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import NxEmotionRadar from './NxEmotionRadar.vue'
import NxRelationTimeline from './NxRelationTimeline.vue'
import NxConflictDiff from './NxConflictDiff.vue'

const props = defineProps({
  contact: {
    type: Object,
    default: () => ({}),
  },
  flipped: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:flipped', 'action'])
const internalFlipped = ref(props.flipped)
const tiltStyle = ref({ transform: 'rotateX(0deg) rotateY(0deg)' })

watch(
  () => props.flipped,
  (value) => {
    internalFlipped.value = value
  }
)

const displayName = computed(() => {
  const contact = props.contact || {}
  return contact.display_name || contact.name || [contact.first_name, contact.last_name].filter(Boolean).join(' ') || `Contact ${contact.id ?? ''}`.trim()
})

const avatarUrl = computed(() => props.contact?.avatar_url || props.contact?.avatar || `https://api.dicebear.com/6.x/initials/svg?seed=${encodeURIComponent(displayName.value)}`)

const statusLabel = computed(() => {
  const status = String(props.contact?.status || '').toLowerCase()
  if (!status) return 'Unknown'
  return status.replace(/_/g, ' ')
})

const isActiveToday = computed(() => {
  const lastSeen = props.contact?.last_activity_at || props.contact?.last_seen_at || props.contact?.updated_at
  if (!lastSeen) return false
  const date = new Date(lastSeen)
  const diff = Date.now() - date.getTime()
  return diff >= 0 && diff <= 86400000
})

const statusClass = computed(() => {
  const status = String(props.contact?.status || '').toLowerCase()
  return {
    active: 'status-online',
    online: 'status-online',
    inactive: 'status-offline',
    archived: 'status-muted',
  }[status] || 'status-muted'
})

const lastActive = computed(() => {
  const value = props.contact?.last_activity_at || props.contact?.last_seen_at || props.contact?.updated_at
  if (!value) return 'Unknown'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return new Intl.DateTimeFormat('en', { month: 'short', day: '2-digit' }).format(date)
})

const relationshipSummary = computed(() => {
  return props.contact?.relationship_summary || 'AI summary unavailable. Flip the card for a quick intelligence snapshot.'
})

const personalityTraits = computed(() => {
  if (Array.isArray(props.contact?.personality_traits) && props.contact.personality_traits.length) {
    return props.contact.personality_traits.slice(0, 3)
  }
  return ['Analytical', 'Dependable', 'Curious']
})

const timelineEvents = computed(() => {
  if (Array.isArray(props.contact?.relation_events) && props.contact.relation_events.length) {
    return props.contact.relation_events
  }

  return [
    { label: 'First contact', date: props.contact?.created_at || 'Unknown', detail: 'Initial introduction captured by CRM.' },
    { label: 'Recent interaction', date: props.contact?.last_activity_at || 'Unknown', detail: 'Most recent touchpoint from the platform.' },
    { label: 'Relationship pulse', date: props.contact?.updated_at || 'Unknown', detail: 'AI sentiment and engagement score updated.' },
  ]
})

const conflictPairs = computed(() => {
  if (Array.isArray(props.contact?.conflicts) && props.contact.conflicts.length) {
    return props.contact.conflicts.slice(0, 2)
  }

  return [
    { before: 'Estimated lead score: moderate', after: 'True lead confidence: high' },
    { before: 'Activity trend: flat', after: 'Activity trend: accelerating' },
  ]
})

const containerStyle = computed(() => ({ transformStyle: 'preserve-3d', perspective: '1000px' }))
const innerStyle = computed(() => ({ transform: tiltStyle.value.transform }))

function toggleFlip() {
  internalFlipped.value = !internalFlipped.value
  emit('update:flipped', internalFlipped.value)
}

function handleMouseMove(event) {
  const rect = event.currentTarget.getBoundingClientRect()
  const x = event.clientX - rect.left
  const y = event.clientY - rect.top
  const rotateY = ((x / rect.width) - 0.5) * 8
  const rotateX = ((y / rect.height) - 0.5) * -8
  tiltStyle.value = { transform: `rotateX(${rotateX}deg) rotateY(${rotateY}deg)` }
}

function resetTilt() {
  tiltStyle.value = { transform: 'rotateX(0deg) rotateY(0deg)' }
}
</script>

<style scoped>
.nx-contact-card-3d {
  cursor: pointer;
}

.card-shell {
  position: relative;
  min-height: 240px;
  width: 100%;
  transform-style: preserve-3d;
}

.card-inner {
  position: relative;
  width: 100%;
  min-height: 240px;
  transform-style: preserve-3d;
  transition: transform 0.8s cubic-bezier(0.23, 1, 0.32, 1);
}

.card-inner.flipped {
  transform: rotateY(180deg);
}

.card-face {
  position: absolute;
  inset: 0;
  backface-visibility: hidden;
  border: 1px solid rgba(148,163,184,0.12);
  border-radius: 1rem;
  background: rgba(15, 23, 42, 0.92);
  box-shadow: 0 24px 60px rgba(0,0,0,0.35);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 1.25rem;
}

.card-front {
  z-index: 2;
}

.card-back {
  transform: rotateY(180deg);
}

.avatar-ring {
  align-items: center;
  background: conic-gradient(from 0deg, #007AFF, #6366F1, #007AFF);
  border-radius: 9999px;
  display: inline-flex;
  height: 96px;
  justify-content: center;
  padding: 4px;
  width: 96px;
}

.avatar-ring.ring-active {
  animation: ring-rotate 4s linear infinite;
}

.avatar-image {
  border-radius: 9999px;
  height: 80px;
  width: 80px;
  object-fit: cover;
}

.presence-dot {
  border: 2px solid rgba(15,23,42,0.9);
  bottom: 0.25rem;
  height: 14px;
  position: absolute;
  right: 0.25rem;
  width: 14px;
}

.status-online {
  background: #34D399;
}

.status-offline,
.status-muted {
  background: #94A3B8;
}

.contact-summary {
  margin-top: 1.25rem;
}

.contact-status {
  color: #7DD3FC;
  font-size: 0.75rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
}

.contact-name {
  font-size: 1.75rem;
  line-height: 1.05;
  margin-top: 0.5rem;
}

.contact-title {
  color: #cbd5e1;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.front-stats {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  margin-top: 1.5rem;
}

.stat-card {
  background: rgba(51, 65, 85, 0.7);
  border: 1px solid rgba(148,163,184,0.12);
  border-radius: 0.85rem;
  padding: 1rem;
}

.stat-label {
  color: #94A3B8;
  display: block;
  font-size: 0.7rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.card-footer {
  margin-top: 1.5rem;
}

.flip-button {
  background: rgba(59,130,246,0.14);
  border: 1px solid rgba(59,130,246,0.25);
  border-radius: 9999px;
  color: #dbeafe;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  padding: 0.85rem 1rem;
  text-transform: uppercase;
}

.card-back-header {
  margin-bottom: 1rem;
}

.card-back-header h3 {
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
}

.card-back-header p {
  color: #cbd5e1;
  line-height: 1.6;
}

.back-widgets {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1.3fr 0.7fr;
}

.back-actions {
  margin-top: 1rem;
  display: grid;
  gap: 1rem;
}

.traits-panel {
  background: rgba(51,65,85,0.65);
  border: 1px solid rgba(148,163,184,0.12);
  border-radius: 1rem;
  padding: 1rem;
}

.traits-panel h4 {
  color: #fff;
  font-size: 0.9rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.traits-panel ul {
  list-style: none;
  margin: 0.75rem 0 0;
  padding: 0;
}

.traits-panel li {
  color: #cbd5e1;
  margin-top: 0.5rem;
}

@keyframes ring-rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
