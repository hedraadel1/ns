<template>
  <section class="workflow-builder">
    <h1>Workflow Builder</h1>
    <p>Drag-and-drop interface for composing and configuring workflows.</p>
    <div class="builder-placeholder">
      <p>Workflow steps will be rendered here with drag-and-drop support.</p>
    </div>
  </section>
</template>

<script setup>
</script>

<style scoped>
.workflow-builder {
  padding: 1rem;
}

.builder-placeholder {
  margin-top: 1rem;
  padding: 2rem;
  border: 2px dashed #444;
  border-radius: 8px;
  text-align: center;
  color: #888;
}
</style>