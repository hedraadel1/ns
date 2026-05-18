<template>
  <section class="contact-analytics p-4">
    <h1 class="text-2xl font-semibold mb-4">Contact Analytics</h1>

    <div v-if="loading">
      <LoadingSpinner message="Loading analytics..." />
    </div>
    <div v-else-if="error">
      <ErrorPanel :message="error" @retry="refresh" />
    </div>
    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-white shadow rounded">
          <div class="text-sm text-gray-500">Type</div>
          <div class="text-xl font-medium">{{ analytics.type }}</div>
        </div>
        <div class="p-4 bg-white shadow rounded">
          <div class="text-sm text-gray-500">Last Seen</div>
          <div class="text-xl font-medium">{{ analytics.last_seen_at || '—' }}</div>
        </div>
        <div class="p-4 bg-white shadow rounded">
          <div class="text-sm text-gray-500">Emotional Baseline</div>
          <div class="text-xl font-medium">{{ analytics.baseline }}</div>
        </div>
      </div>

      <div class="mb-6 bg-white shadow rounded p-4">
        <h2 class="font-semibold mb-2">Counts</h2>
        <div class="flex gap-4 items-end">
          <div class="text-center">
            <div class="text-3xl font-bold">{{ analytics.memory_count }}</div>
            <div class="text-sm text-gray-500">Memories</div>
          </div>
          <div class="text-center">
            <div class="text-3xl font-bold">{{ analytics.tag_count }}</div>
            <div class="text-sm text-gray-500">Tags</div>
          </div>
          <div class="text-center">
            <div class="text-3xl font-bold">{{ analytics.rule_count }}</div>
            <div class="text-sm text-gray-500">Rules</div>
          </div>
        </div>
      </div>

      <div class="mb-6 bg-white shadow rounded p-4">
        <h2 class="font-semibold mb-2">Simple Chart</h2>
        <svg width="100%" height="80" viewBox="0 0 300 80" preserveAspectRatio="none">
          <rect v-for="(v, i) in chartValues" :key="i" :x="(i*90)+10" :y="80 - v*3" :width="60" :height="v*3" :fill="barColor(i)" rx="4" />
        </svg>
        <div class="flex gap-6 mt-2 text-sm text-gray-600">
          <div>Memories: {{ analytics.memory_count }}</div>
          <div>Tags: {{ analytics.tag_count }}</div>
          <div>Rules: {{ analytics.rule_count }}</div>
        </div>
      </div>

      <div class="flex gap-3">
        <button @click="exportCsv" class="px-4 py-2 bg-blue-600 text-white rounded">Export CSV</button>
        <button @click="refresh" class="px-4 py-2 bg-gray-200 rounded">Refresh</button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import LoadingSpinner from '../Components/LoadingSpinner.vue';
import ErrorPanel from '../Components/ErrorPanel.vue';

const route = useRoute();
const contactId = route.params.id || route.query.id;

const analytics = ref({});
const loading = ref(false);
const error = ref(null);

const fetchAnalytics = async () => {
  if (!contactId) {
    error.value = 'Contact ID missing in route.';
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const res = await fetch(`/api/v1/contacts/${contactId}/analytics`, { credentials: 'include' });
    if (!res.ok) throw new Error('Failed to load analytics');
    const json = await res.json();
    analytics.value = json.data.analytics || json.data || {};
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

const refresh = () => fetchAnalytics();

onMounted(() => fetchAnalytics());

const chartValues = computed(() => [analytics.value.memory_count || 0, analytics.value.tag_count || 0, analytics.value.rule_count || 0]);
const barColor = (i) => ['#4F46E5', '#06B6D4', '#F59E0B'][i] || '#6B7280';

const exportCsv = () => {
  const rows = [['key','value']];
  for (const [k, v] of Object.entries(analytics.value)) {
    rows.push([k, String(v ?? '')]);
  }
  const csv = rows.map(r => r.map(c => '"' + ('' + c).replace(/"/g, '""') + '"').join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `contact_${contactId}_analytics.csv`;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
};
</script>

<style scoped>
.contact-analytics { }
</style>
