<template>
  <button type="button" class="nx-voice-orb" @click="$emit('click')" aria-label="Activate voice mode">
    <span class="orb-ring"></span>
    <span class="orb-core"></span>
  </button>
</template>

<script setup>
</script>

<style scoped>
.nx-voice-orb {
  position: relative;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  transition: transform 0.2s ease, background 0.2s ease;
}

.nx-voice-orb:hover {
  transform: scale(1.05);
  background: rgba(255, 255, 255, 0.14);
}

.orb-ring,
.orb-core {
  position: absolute;
  border-radius: 50%;
}

.orb-ring {
  width: 100%;
  height: 100%;
  border: 1px solid rgba(99, 102, 241, 0.55);
}

.orb-core {
  width: 16px;
  height: 16px;
  background: linear-gradient(135deg, #818CF8, #A78BFA);
  box-shadow: 0 0 10px rgba(99, 102, 241, 0.35);
}
</style>
