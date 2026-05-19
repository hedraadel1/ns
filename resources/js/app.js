import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

// Import Bootstrap (Echo + Reverb)
import './bootstrap';

// Create app
const app = createApp(App);

// Install plugins
const pinia = createPinia();
app.use(pinia);
app.use(router);

// Mount app
app.mount('#app');