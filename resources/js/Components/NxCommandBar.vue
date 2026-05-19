<template>
  <div v-if="visible" class="command-bar-overlay" @click.self="close">
    <div class="command-bar" role="dialog" aria-label="Command Bar">
      <div class="command-input-wrapper">
        <SearchIcon class="command-icon" />
        <input
          ref="inputRef"
          v-model="query"
          type="text"
          class="command-input"
          placeholder="Type a command or search..."
          @input="onInput"
          @keydown.down.prevent="onArrowDown"
          @keydown.up.prevent="onArrowUp"
          @keydown.enter.prevent="onEnter"
          @keydown.escape="close"
        />
        <kbd class="command-shortcut">ESC</kbd>
      </div>

      <div class="command-results">
        <div v-if="!query && recentSearches.length > 0" class="result-group">
          <div class="group-label">Recent</div>
          <div
            v-for="(item, index) in recentSearches"
            :key="'recent-' + index"
            class="result-item"
            :class="{ selected: selectedIndex === index }"
            @click="selectResult(item)"
          >
            <ClockIcon class="result-icon" />
            <span class="result-text">{{ item }}</span>
          </div>
        </div>

        <div v-if="results.length > 0" class="result-group">
          <div class="group-label">{{ query ? 'Results' : 'Suggestions' }}</div>
          <div
            v-for="(item, index) in results"
            :key="item.id"
            class="result-item"
            :class="{ selected: selectedIndex === (query ? index : recentSearches.length + index) }"
            @click="selectResult(item)"
          >
            <component :is="item.icon" class="result-icon" />
            <div class="result-content">
              <span class="result-text">{{ item.title }}</span>
              <span v-if="item.subtitle" class="result-subtitle">{{ item.subtitle }}</span>
            </div>
            <span class="result-type">{{ item.type }}</span>
          </div>
        </div>

        <div v-if="query && results.length === 0" class="empty-state">
          <SearchXIcon class="empty-icon" />
          <p>No results found for "{{ query }}"</p>
        </div>
      </div>

      <div class="command-footer">
        <div class="footer-hint">
          <kbd>↑↓</kbd> to navigate
          <kbd>↵</kbd> to select
          <kbd>esc</kbd> to close
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import {
  Search as SearchIcon,
  Clock as ClockIcon,
  SearchX as SearchXIcon,
  User as UserIcon,
  Bot as BotIcon,
  Brain as BrainIcon,
  Workflow as WorkflowIcon,
  ListTodo as ListTodoIcon,
  MessageSquare as MessageSquareIcon,
  Settings as SettingsIcon,
  Home as HomeIcon,
  FileText as LogsIcon,
  Server as AIModelsIcon,
} from 'lucide-vue-next';

const router = useRouter();
const visible = ref(false);
const query = ref('');
const results = ref([]);
const recentSearches = ref([]);
const selectedIndex = ref(0);
const inputRef = ref(null);

const routeItems = [
  { id: 'nexus', title: 'Nexus', subtitle: 'Home hub', icon: MessageSquareIcon, route: { name: 'nexus' }, type: 'Hub' },
  { id: 'agents', title: 'Agents', subtitle: 'Agent management', icon: BotIcon, route: { name: 'agents' }, type: 'Hub' },
  { id: 'memory', title: 'Memory', subtitle: 'Memory assistant', icon: BrainIcon, route: { name: 'memory' }, type: 'Hub' },
  { id: 'contacts', title: 'Contacts', subtitle: 'Contact book', icon: UserIcon, route: { name: 'contacts' }, type: 'Hub' },
  { id: 'workflows', title: 'Workflows', subtitle: 'Workflow builder', icon: WorkflowIcon, route: { name: 'workflows' }, type: 'Hub' },
  { id: 'settings', title: 'Settings', subtitle: 'System preferences', icon: SettingsIcon, route: { name: 'settings' }, type: 'Hub' },
  { id: 'dashboard', title: 'Dashboard', subtitle: 'Overview metrics', icon: HomeIcon, route: { name: 'dashboard' }, type: 'Hub' },
  { id: 'logs', title: 'Logs', subtitle: 'System logs', icon: LogsIcon, route: { name: 'logs' }, type: 'Hub' },
  { id: 'ai-models', title: 'AI Models', subtitle: 'Model registry', icon: AIModelsIcon, route: { name: 'ai-models' }, type: 'Hub' },
];

function onKeyDown(e) {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    toggle();
  }
}

function toggle() {
  visible.value = !visible.value;
  if (visible.value) {
    nextTick(() => {
      inputRef.value?.focus();
    });
  }
}

function close() {
  visible.value = false;
  query.value = '';
  results.value = [];
  selectedIndex.value = 0;
}

function onInput() {
  selectedIndex.value = 0;
  if (!query.value) {
    results.value = [];
    return;
  }

  const search = query.value.toLowerCase().trim();
  results.value = routeItems
    .filter((item) =>
      item.title.toLowerCase().includes(search) ||
      item.subtitle.toLowerCase().includes(search) ||
      item.type.toLowerCase().includes(search)
    )
    .slice(0, 8);
}

function onArrowDown() {
  const maxIndex = results.value.length - 1;
  selectedIndex.value = Math.min(selectedIndex.value + 1, maxIndex);
}

function onArrowUp() {
  selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
}

function onEnter() {
  if (!results.value.length) return;
  selectResult(results.value[selectedIndex.value]);
}

function selectResult(item) {
  if (!item) return;

  addToRecent(item.title);
  if (item.route) {
    router.push(item.route);
  }
  close();
}

function addToRecent(label) {
  recentSearches.value = [label, ...recentSearches.value.filter((item) => item !== label)].slice(0, 5);
  localStorage.setItem('nexus-recent-searches', JSON.stringify(recentSearches.value));
}

onMounted(() => {
  const stored = localStorage.getItem('nexus-recent-searches');
  if (stored) {
    try {
      recentSearches.value = JSON.parse(stored);
    } catch {
      recentSearches.value = [];
    }
  }
  document.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', onKeyDown);
});

defineExpose({ open: toggle, close });
</script>

<style scoped>
.command-bar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 15vh;
  z-index: 1000;
}

.command-bar {
  width: 100%;
  max-width: 560px;
  background: rgba(22, 27, 34, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.command-input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.command-icon {
  color: rgba(255, 255, 255, 0.4);
  flex-shrink: 0;
}

.command-input {
  flex: 1;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.9);
  font-size: 16px;
  outline: none;
}

.command-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.command-shortcut {
  padding: 4px 8px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  color: rgba(255, 255, 255, 0.5);
  font-size: 11px;
  font-family: 'JetBrains Mono', monospace;
}

.command-results {
  max-height: 400px;
  overflow-y: auto;
  padding: 8px;
}

.result-group {
  margin-bottom: 8px;
}

.group-label {
  padding: 8px 12px 4px;
  font-size: 11px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.4);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 150ms ease;
}

.result-item:hover,
.result-item.selected {
  background: rgba(255, 255, 255, 0.05);
}

.result-item.selected {
  background: rgba(0, 122, 255, 0.15);
}

.result-icon {
  color: rgba(255, 255, 255, 0.5);
  flex-shrink: 0;
}

.result-content {
  flex: 1;
  min-width: 0;
}

.result-text {
  display: block;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.9);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-subtitle {
  display: block;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.5);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-type {
  padding: 2px 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  font-size: 10px;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 32px;
  text-align: center;
  color: rgba(255, 255, 255, 0.5);
}

.empty-icon {
  margin-bottom: 12px;
  opacity: 0.5;
}

.command-footer {
  padding: 8px 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.2);
}

.footer-hint {
  display: flex;
  gap: 16px;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.4);
}

.footer-hint kbd {
  padding: 2px 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px;
}
</style>
