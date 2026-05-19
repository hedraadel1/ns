<template>
  <div class="nx-memory-mini-graph">
    <div class="graph-canvas">
      <div
        v-for="node in displayNodes"
        :key="node.id"
        class="graph-node"
        :style="node.style"
        @click="$emit('node-click', node.id)">
        <span>{{ node.label }}</span>
      </div>
      <div v-for="edge in edges" :key="edge.id" class="graph-edge" :style="edge.style"></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  contactId: {
    type: String,
    default: '',
  },
  maxNodes: {
    type: Number,
    default: 12,
  },
})

const displayNodes = Array.from({ length: Math.min(props.maxNodes, 8) }, (_, index) => {
  const angle = (index / Math.min(props.maxNodes, 8)) * Math.PI * 2
  const radius = 70
  return {
    id: `node-${index}`,
    label: `M${index + 1}`,
    style: {
      top: `${50 + Math.sin(angle) * radius}%`,
      left: `${50 + Math.cos(angle) * radius}%`,
    },
  }
})

const edges = displayNodes.slice(1).map((node, index) => ({
  id: `edge-${index}`,
  style: {
    top: `${50 + Math.sin((index + 1) / Math.min(props.maxNodes, 8) * Math.PI * 2) * 70}%`,
    left: `${50 + Math.cos((index + 1) / Math.min(props.maxNodes, 8) * Math.PI * 2) * 70}%`,
  },
}))
</script>

<style scoped>
.nx-memory-mini-graph {
  width: 100%;
  height: 220px;
  background: rgba(15, 23, 42, 0.88);
  border: 1px solid rgba(148, 163, 184, 0.12);
  border-radius: 1rem;
  overflow: hidden;
  position: relative;
}

.graph-canvas {
  position: relative;
  width: 100%;
  height: 100%;
}

.graph-node {
  position: absolute;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  transform: translate(-50%, -50%);
  border-radius: 9999px;
  background: rgba(99, 102, 241, 0.9);
  color: #fff;
  font-size: 0.75rem;
  text-align: center;
  cursor: pointer;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}

.graph-edge {
  position: absolute;
  width: 2px;
  height: 2px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.12);
}
</style>
