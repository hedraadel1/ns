<template>
  <aside class="hub-sidebar" :class="{ collapsed }">
    <!-- Sticky Search Header -->
    <div class="sidebar-header">
      <div class="search-container">
        <Icon :name="SearchIcon" :size="16" class="search-icon" />
        <input
          v-model="searchQuery"
          type="text"
          class="search-input"
          placeholder="Search..."
          @input="onSearch"
        />
      </div>
      <div class="filter-row">
        <select v-model="sortBy" class="sort-select" @change="onSort">
          <option value="name">Name</option>
          <option value="updated">Updated</option>
          <option value="created">Created</option>
        </select>
        <button class="filter-btn" @click="showFilters = !showFilters" title="Filters">
          <Icon :name="FilterIcon" :size="14" />
        </button>
      </div>
    </div>

    <!-- Entity List -->
    <div class="entity-list">
      <div
        v-for="entity in filteredEntities"
        :key="entity.id"
        class="entity-item"
        :class="{ active: selectedEntity?.id === entity.id }"
        @click="selectEntity(entity)"
      >
        <div class="entity-icon">
          <Icon :name="entity.icon" :size="16" />
        </div>
        <div class="entity-info">
          <div class="entity-name">{{ entity.name }}</div>
          <div v-if="entity.subtitle" class="entity-subtitle">{{ entity.subtitle }}</div>
        </div>
        <div v-if="entity.status" class="entity-status" :class="entity.status">
          {{ entity.status }}
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredEntities.length === 0" class="empty-state">
        <Icon :name="SearchXIcon" :size="32" class="empty-icon" />
        <p>No items found</p>
        <button class="create-btn" @click="onCreate">
          <Icon :name="PlusIcon" :size="14" />
          Create New
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Icon } from 'lucide-vue-next';
import {
  Search as SearchIcon,
  Filter as FilterIcon,
  SearchX as SearchXIcon,
  Plus as PlusIcon,
  User as UserIcon,
  Bot as BotIcon,
  Brain as BrainIcon,
  Workflow as WorkflowIcon,
  ListTodo as ListTodoIcon,
  MessageSquare as MessageSquareIcon,
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

const props = defineProps({
  collapsed: { type: Boolean, default: false },
});

const emit = defineEmits(['select', 'create']);

const searchQuery = ref('');
const sortBy = ref('name');
const showFilters = ref(false);
const selectedEntity = ref(null);

// Hub entity definitions
const hubEntities = computed(() => {
  const hub = route.meta?.hub || 'nexus';
  const entities = {
    nexus: [
      { id: 'recent', name: 'Recent Conversations', icon: MessageSquareIcon, type: 'conversation' },
      { id: 'pinned', name: 'Pinned', icon: MessageSquareIcon, type: 'pinned' },
    ],
    agents: [
      { id: 'agent-1', name: 'Nova', icon: BotIcon, status: 'online', subtitle: 'Primary Assistant' },
      { id: 'agent-2', name: 'Atlas', icon: BotIcon, status: 'idle', subtitle: 'Research Agent' },
      { id: 'agent-3', name: 'Echo', icon: BotIcon, status: 'offline', subtitle: 'Memory Agent' },
    ],
    memory: [
      { id: 'mem-1', name: 'Project Context', icon: BrainIcon, type: 'memory', subtitle: 'Updated 2h ago' },
      { id: 'mem-2', name: 'User Preferences', icon: BrainIcon, type: 'memory', subtitle: 'Updated 1d ago' },
    ],
    contacts: [
      { id: 'contact-1', name: 'Ahmed Ali', icon: UserIcon, status: 'active', subtitle: 'ahmed@example.com' },
      { id: 'contact-2', name: 'Sarah Chen', icon: UserIcon, status: 'active', subtitle: 'sarah@example.com' },
    ],
    workflows: [
      { id: 'wf-1', name: 'Daily Report', icon: WorkflowIcon, status: 'active', subtitle: 'Runs at 9:00 AM' },
      { id: 'wf-2', name: 'Data Sync', icon: WorkflowIcon, status: 'paused', subtitle: 'Manual trigger' },
    ],
    tasks: [
      { id: 'task-1', name: 'Review PR #42', icon: ListTodoIcon, status: 'pending', subtitle: 'Due today' },
      { id: 'task-2', name: 'Update docs', icon: ListTodoIcon, status: 'completed', subtitle: 'Done' },
    ],
  };
  return entities[hub] || entities.nexus;
});

const filteredEntities = computed(() => {
  let result = [...hubEntities.value];
  
  // Filter by search
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(e => 
      e.name.toLowerCase().includes(q) ||
      e.subtitle?.toLowerCase().includes(q)
    );
  }
  
  // Sort
  if (sortBy.value === 'name') {
    result.sort((a, b) => a.name.localeCompare(b.name));
  } else if (sortBy.value === 'updated') {
    result.sort((a, b) => (b.subtitle || '').localeCompare(a.subtitle || ''));
  }
  
  return result;
});

function selectEntity(entity) {
  selectedEntity.value = entity;
  emit('select', entity);
}

function onSearch() {
  // Debounced search could be added here
}

function onSort() {
  // Sorting handled by computed
}

function onCreate() {
  emit('create');
}

// Watch route changes to update selected entity
watch(() => route.path, () => {
  const entityId = route.params.id || route.query.entity;
  if (entityId) {
    selectedEntity.value = hubEntities.value.find(e => e.id === entityId) || null;
  }
});
</script>

<style scoped>
.hub-sidebar {
  width: 320px;
  min-width: 280px;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  background: rgba(22, 27, 34, 0.7);
  backdrop-filter: blur(12px);
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.sidebar-header {
  position: sticky;
  top: 0;
  z-index: 10;
  padding: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  background: rgba(22, 27, 34, 0.9);
}

.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 10px;
  color: rgba(255, 255, 255, 0.4);
}

.search-input {
  width: 100%;
  padding: 8px 12px 8px 32px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.9);
  font-size: 13px;
  outline: none;
  transition: border-color 150ms ease;
}

.search-input:focus {
  border-color: rgba(0, 122, 255, 0.5);
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.filter-row {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.sort-select {
  flex: 1;
  padding: 6px 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 12px;
  outline: none;
}

.filter-btn {
  padding: 6px 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  transition: all 150ms ease;
}

.filter-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
}

.entity-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
}

.entity-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 150ms ease;
  margin-bottom: 2px;
}

.entity-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.entity-item.active {
  background: rgba(0, 122, 255, 0.15);
  border-left: 2px solid #007AFF;
}

.entity-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.7);
}

.entity-info {
  flex: 1;
  min-width: 0;
}

.entity-name {
  font-size: 13px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.entity-subtitle {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.5);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.entity-status {
  padding: 2px 8px;
  border-radius: 9999px;
  font-size: 10px;
  font-weight: 500;
  text-transform: uppercase;
}

.entity-status.online,
.entity-status.active {
  background: rgba(16, 185, 129, 0.2);
  color: #10B981;
}

.entity-status.idle {
  background: rgba(245, 158, 11, 0.2);
  color: #F59E0B;
}

.entity-status.offline {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.5);
}

.entity-status.pending {
  background: rgba(0, 122, 255, 0.2);
  color: #007AFF;
}

.entity-status.completed {
  background: rgba(16, 185, 129, 0.2);
  color: #10B981;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
  text-align: center;
  color: rgba(255, 255, 255, 0.5);
}

.empty-icon {
  margin-bottom: 12px;
  opacity: 0.5;
}

.empty-state p {
  margin: 0 0 16px;
  font-size: 13px;
}

.create-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #007AFF;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 150ms ease;
}

.create-btn:hover {
  opacity: 0.9;
}
</style>
