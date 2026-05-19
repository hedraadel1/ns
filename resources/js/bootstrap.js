import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

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
