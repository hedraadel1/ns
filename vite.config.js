import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separate vendor chunks
                    'vendor-vue': ['vue', 'vue-router', 'pinia'],
                    'vendor-echarts': ['echarts', 'vue-echarts'],
                    'vendor-icons': ['lucide-vue-next'],
                    'vendor-other': ['axios', 'laravel-echo', 'pusher-js', 'markdown-it', 'highlight.js'],
                    // Separate page components
                    'page-nexus': ['resources/js/Pages/NexusView.vue'],
                    'page-dashboard': ['resources/js/Pages/DashboardView.vue'],
                    'page-agents': ['resources/js/Pages/AgentsView.vue'],
                    'page-contacts': ['resources/js/Pages/ContactsView.vue'],
                    'page-memory': ['resources/js/Pages/MemoryView.vue'],
                    'page-workflows': ['resources/js/Pages/WorkflowsView.vue'],
                    'page-conversations': ['resources/js/Pages/ConversationsView.vue'],
                    'page-settings': ['resources/js/Pages/SettingsView.vue'],
                    'page-logs': ['resources/js/Pages/LogsView.vue'],
                    'page-ai': ['resources/js/Pages/AIModelsView.vue'],
                },
            },
        },
    },
});
