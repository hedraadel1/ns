import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/useAuthStore';

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
const LoginPage = () => import('../Pages/Auth/LoginPage.vue');
const RegisterPage = () => import('../Pages/Auth/RegisterPage.vue');

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: { requiresAuth: false, breadcrumb: 'Login' },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
    meta: { requiresAuth: false, breadcrumb: 'Register' },
  },
  {
    path: '/',
    name: 'nexus',
    component: NexusView,
    meta: { requiresAuth: true, breadcrumb: 'Nexus' },
  },
  {
    path: '/agents',
    name: 'agents',
    component: AgentsView,
    meta: { requiresAuth: true, breadcrumb: 'Agents' },
  },
  {
    path: '/memory',
    name: 'memory',
    component: MemoryView,
    meta: { requiresAuth: true, breadcrumb: 'Memory' },
  },
  {
    path: '/contacts',
    name: 'contacts',
    component: ContactsView,
    meta: { requiresAuth: true, breadcrumb: 'Contacts' },
  },
  {
    path: '/workflows',
    name: 'workflows',
    component: WorkflowsView,
    meta: { requiresAuth: true, breadcrumb: 'Workflows' },
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsView,
    meta: { requiresAuth: true, breadcrumb: 'Settings' },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true, breadcrumb: 'Dashboard' },
  },
  {
    path: '/conversations',
    name: 'conversations',
    component: ConversationsView,
    meta: { requiresAuth: true, breadcrumb: 'Conversations' },
  },
  {
    path: '/logs',
    name: 'logs',
    component: LogsView,
    meta: { requiresAuth: true, breadcrumb: 'Logs' },
  },
  {
    path: '/ai-models',
    name: 'ai-models',
    component: AIModelsView,
    meta: { requiresAuth: true, breadcrumb: 'AI Models' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

// Navigation guard to check authentication
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const requiresAuth = to.meta.requiresAuth !== false;

  if (requiresAuth && !authStore.isAuthenticated) {
    // Redirect to login if auth is required and user is not authenticated
    next('/login');
  } else if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
    // Redirect authenticated users away from login/register pages
    next('/');
  } else {
    next();
  }
});

export default router;