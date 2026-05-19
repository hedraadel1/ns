import { createRouter, createWebHistory } from 'vue-router';

// Lazy load views
const NexusView = () => import('../Pages/NexusView.vue');
const AgentsView = () => import('../Pages/AgentsView.vue');
const MemoryView = () => import('../Pages/MemoryView.vue');
const ContactsView = () => import('../Pages/ContactsView.vue');
const WorkflowsView = () => import('../Pages/WorkflowsView.vue');
const SettingsView = () => import('../Pages/SettingsView.vue');
const DashboardView = () => import('../Pages/DashboardView.vue');
const ConversationsView = () => import('../Pages/ConversationsView.vue');
const LogsView = () => import('../Pages/LogsView.vue');
const AIModelsView = () => import('../Pages/AIModelsView.vue');

const routes = [
  {
    path: '/',
    name: 'nexus',
    component: NexusView,
    meta: { breadcrumb: 'Nexus' },
  },
  {
    path: '/agents',
    name: 'agents',
    component: AgentsView,
    meta: { breadcrumb: 'Agents' },
  },
  {
    path: '/memory',
    name: 'memory',
    component: MemoryView,
    meta: { breadcrumb: 'Memory' },
  },
  {
    path: '/contacts',
    name: 'contacts',
    component: ContactsView,
    meta: { breadcrumb: 'Contacts' },
  },
  {
    path: '/workflows',
    name: 'workflows',
    component: WorkflowsView,
    meta: { breadcrumb: 'Workflows' },
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsView,
    meta: { breadcrumb: 'Settings' },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { breadcrumb: 'Dashboard' },
  },
  {
    path: '/conversations',
    name: 'conversations',
    component: ConversationsView,
    meta: { breadcrumb: 'Conversations' },
  },
  {
    path: '/logs',
    name: 'logs',
    component: LogsView,
    meta: { breadcrumb: 'Logs' },
  },
  {
    path: '/ai-models',
    name: 'ai-models',
    component: AIModelsView,
    meta: { breadcrumb: 'AI Models' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

export default router;