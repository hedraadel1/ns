<template>
  <div class="settings-view">
    <h1>Settings</h1>
    <p class="subtitle">Manage application configuration.</p>
    <div class="placeholder-panel">
      <p>Settings hub is available at <a href="/settings" class="link">/settings</a> or via the Settings page in the sidebar.</p>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.settings-view {
  max-width: 1200px;
}

.subtitle {
  color: #888;
  margin-bottom: 2rem;
}

.placeholder-panel {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  color: #888;
}

.link {
  color: #4ade80;
  text-decoration: none;
}

.link:hover {
  text-decoration: underline;
}
</style>
