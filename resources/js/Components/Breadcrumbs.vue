<template>
  <nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
      <li v-for="(item, index) in breadcrumbItems" :key="index" class="breadcrumb-item">
        <button
          v-if="index < breadcrumbItems.length - 1"
          type="button"
          class="breadcrumb-link"
          @click="navigateTo(item)"
        >
          {{ item.label }}
        </button>
        <span v-else class="breadcrumb-current">
          {{ item.label }}
        </span>
        <span v-if="index < breadcrumbItems.length - 1" class="breadcrumb-separator">/</span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const breadcrumbItems = computed(() => {
  const items = route.matched
    .filter((r) => r.meta?.breadcrumb)
    .map((r) => ({ label: r.meta.breadcrumb, path: r.path }));

  if (items.length === 0) {
    items.push({ label: route.meta?.breadcrumb || 'Home', path: route.path });
  }

  return items;
});

const navigateTo = (item) => {
  router.push(item.path);
};
</script>

<style scoped>
.breadcrumbs {
  display: flex;
  align-items: center;
}

.breadcrumb-list {
  display: flex;
  align-items: center;
  gap: 4px;
  margin: 0;
  padding: 0;
  list-style: none;
  font-size: 13px;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.breadcrumb-link,
.breadcrumb-current {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.9);
  font: inherit;
  cursor: pointer;
  padding: 0;
}

.breadcrumb-link {
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  transition: color 150ms ease, transform 150ms ease;
}

.breadcrumb-link:hover {
  color: rgba(255, 255, 255, 0.9);
  transform: translateX(2px);
}

.breadcrumb-current {
  font-weight: 500;
}

.breadcrumb-separator {
  color: rgba(255, 255, 255, 0.3);
}
</style>
