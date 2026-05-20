import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add request interceptor to include auth token
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Add response interceptor to handle 401 errors
axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const response = await axios.post('/api/v1/refresh-token');
        const { access_token } = response.data;

        localStorage.setItem('auth_token', access_token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
        originalRequest.headers.Authorization = `Bearer ${access_token}`;

        return axios(originalRequest);
      } catch (refreshError) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);


import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY || 'local',
  wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
  wsPort: parseInt(import.meta.env.VITE_REVERB_PORT || '8080', 10),
  wssPort: parseInt(import.meta.env.VITE_REVERB_PORT || '8080', 10),
  forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
  enabledTransports: ['ws', 'wss'],
  disableStats: true,
  authEndpoint: '/broadcasting/auth',
  auth: {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  },
});

window.EchoConnection = {
  status: 'initializing',
  lastStateChange: Date.now(),
  fallbackEnabled: false,
};

if (window.Echo?.connector?.pusher?.connection) {
  const connection = window.Echo.connector.pusher.connection;

  connection.bind('connected', () => {
    window.EchoConnection.status = 'connected';
    window.EchoConnection.lastStateChange = Date.now();
    window.EchoConnection.fallbackEnabled = false;
    console.info('[Echo] connected');
  });

  connection.bind('disconnected', () => {
    window.EchoConnection.status = 'disconnected';
    window.EchoConnection.lastStateChange = Date.now();
    console.warn('[Echo] disconnected');
  });

  connection.bind('error', (error) => {
    window.EchoConnection.status = 'error';
    window.EchoConnection.lastStateChange = Date.now();
    console.error('[Echo] error', error);
  });

  connection.bind('failed', (error) => {
    window.EchoConnection.status = 'failed';
    window.EchoConnection.lastStateChange = Date.now();
    console.error('[Echo] failed', error);
  });

  connection.bind('state_change', (states) => {
    window.EchoConnection.status = states.current || 'unknown';
    window.EchoConnection.lastStateChange = Date.now();
    console.debug('[Echo] state_change', states);
  });
}
