<template>
  <div v-if="active" class="nx-celebration-overlay" ref="overlay" aria-hidden="true">
    <canvas ref="canvas" class="celebration-canvas"></canvas>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useHaptic } from '../composables/useHaptic';

const props = defineProps({
  trigger: { type: Number, default: 0 },
  intensity: { type: Number, default: 0.75 },
});

const emit = defineEmits(['finished']);
const active = ref(false);
const canvas = ref(null);
const context = ref(null);
const animationFrame = ref(null);
const particles = ref([]);
const startTime = ref(0);
const duration = 1500;

const { success } = useHaptic();

function createParticle(width, height) {
  const angle = Math.random() * Math.PI * 2;
  const speed = 0.7 + Math.random() * 2.1;
  const hue = 40 + Math.random() * 320;
  return {
    x: width / 2,
    y: height * 0.7,
    vx: Math.cos(angle) * speed * 2,
    vy: Math.sin(angle) * speed * 2,
    radius: 2 + Math.random() * 3,
    hue,
    alpha: 1,
    rotation: Math.random() * Math.PI * 2,
    spin: (Math.random() - 0.5) * 0.2,
    gravity: 0.03 + Math.random() * 0.04,
  };
}

function resizeCanvas() {
  if (!canvas.value) {
    return;
  }
  const element = canvas.value;
  element.width = element.clientWidth * window.devicePixelRatio;
  element.height = element.clientHeight * window.devicePixelRatio;
  context.value = element.getContext('2d');
  if (context.value) {
    context.value.scale(window.devicePixelRatio, window.devicePixelRatio);
  }
}

function drawFrame(timestamp) {
  if (!active.value || !context.value || !canvas.value) {
    return;
  }

  const elapsed = timestamp - startTime.value;
  const ctx = context.value;
  const width = canvas.value.clientWidth;
  const height = canvas.value.clientHeight;

  ctx.clearRect(0, 0, width, height);

  particles.value.forEach((particle) => {
    particle.x += particle.vx;
    particle.y += particle.vy;
    particle.vy += particle.gravity;
    particle.rotation += particle.spin;
    particle.alpha -= 0.008;

    ctx.save();
    ctx.globalAlpha = Math.max(particle.alpha, 0);
    ctx.translate(particle.x, particle.y);
    ctx.rotate(particle.rotation);
    ctx.fillStyle = `hsla(${particle.hue}, 95%, 55%, ${particle.alpha})`;
    ctx.fillRect(-particle.radius, -particle.radius, particle.radius * 2, particle.radius * 2);
    ctx.restore();
  });

  particles.value = particles.value.filter((particle) => particle.alpha > 0);

  if (elapsed < duration) {
    animationFrame.value = requestAnimationFrame(drawFrame);
  } else {
    active.value = false;
    particles.value = [];
    emit('finished');
  }
}

function startCelebration() {
  const element = canvas.value;
  if (!element) {
    return;
  }

  const count = Math.max(12, Math.min(48, Math.round(18 + props.intensity * 40)));
  resizeCanvas();
  particles.value = Array.from({ length: count }, () => createParticle(element.clientWidth, element.clientHeight));
  startTime.value = performance.now();
  active.value = true;
  success();
  animationFrame.value = requestAnimationFrame(drawFrame);
}

watch(
  () => props.trigger,
  (value) => {
    if (value > 0) {
      if (animationFrame.value) {
        cancelAnimationFrame(animationFrame.value);
      }
      startCelebration();
    }
  }
);

onMounted(() => {
  resizeCanvas();
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', resizeCanvas);
  }
});

onBeforeUnmount(() => {
  if (animationFrame.value) {
    cancelAnimationFrame(animationFrame.value);
  }
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', resizeCanvas);
  }
});
</script>

<style scoped>
.nx-celebration-overlay {
  position: fixed;
  inset: 0;
  z-index: 110;
  pointer-events: none;
  background: radial-gradient(circle at center, rgba(255, 255, 255, 0.05), transparent 45%);
}

.celebration-canvas {
  width: 100%;
  height: 100%;
  display: block;
}
</style>
