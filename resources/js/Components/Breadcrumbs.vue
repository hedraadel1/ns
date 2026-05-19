<template>
  <nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
      <li v-for="(item, index) in breadcrumbItems" :key="index" class="breadcrumb-item">
        <span
          v-if="index < breadcrumbItems.length - 1"
          class="breadcrumb-link"
          @click="navigateTo(item)"
        >
          {{ item.label }}
        </span>
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
  const items = [];
  const matched = route.matched.filter(r => r.meta?.breadcrumb);

  matched.forEach((r, i) => {
    items.push({
      label: r.meta.breadcrumb,
      path: r.path,
    });
  });

  // If no matched routes, use current route
  if (items.length === 0) {
    items.push({
      label: route.meta?.breadcrumb || 'Home',
      path: route.path,
    });
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

.breadcrumb-link {
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  transition: color 150ms ease;
}

.breadcrumb-link:hover {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: underline;
}

.breadcrumb-current {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.breadcrumb-separator {
  color: rgba(255, 255, 255, 0.3);
  margin: 0 2px;
}
</style>
