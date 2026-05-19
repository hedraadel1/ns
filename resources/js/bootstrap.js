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
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabled: import.meta.env.VITE_REVERB_ENABLED !== 'false',
    authEndpoint: '/broadcasting/auth',
});

if (window.Echo?.connector?.pusher?.connection) {
    const connection = window.Echo.connector.pusher.connection;

    connection.bind('connected', () => console.info('[Echo] connected'));
    connection.bind('disconnected', () => console.warn('[Echo] disconnected'));
    connection.bind('error', (error) => console.error('[Echo] error', error));
    connection.bind('failed', (error) => console.error('[Echo] failed', error));
    connection.bind('state_change', (states) => console.debug('[Echo] state_change', states));
}
