import { computed, onBeforeUnmount, ref } from 'vue';
import axios from 'axios';

const STORAGE_KEY = 'nexus-offline-queue-v1';
let initialized = false;

function loadQueue() {
  if (typeof window === 'undefined') {
    return [];
  }

  try {
    const stored = window.localStorage.getItem(STORAGE_KEY);
    return stored ? JSON.parse(stored) : [];
  } catch (_error) {
    return [];
  }
}

function persistQueue(queue) {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
}

function buildRequestPayload(config) {
  return {
    url: config.url,
    method: config.method?.toUpperCase() || 'POST',
    headers: { ...config.headers },
    credentials: config.withCredentials ? 'include' : 'same-origin',
    body: config.data ? JSON.stringify(config.data) : null,
    timestamp: Date.now(),
  };
}

function buildFetchPayload(url, init) {
  return {
    url: typeof url === 'string' ? url : url.href,
    method: (init?.method || 'GET').toUpperCase(),
    headers: init?.headers ? { ...init.headers } : {},
    body: init?.body ?? null,
    credentials: init?.credentials || 'same-origin',
    timestamp: Date.now(),
  };
}

function patchAxios(queue, online) {
  const originalPost = axios.post.bind(axios);
  const originalPut = axios.put.bind(axios);
  const originalPatch = axios.patch.bind(axios);
  const originalDelete = axios.delete.bind(axios);

  const queuedResponse = () => Promise.resolve({
    data: { success: true, queued: true, message: 'Offline queued' },
    status: 202,
    statusText: 'Accepted',
    headers: {},
    config: {},
  });

  axios.post = async (url, data, config = {}) => {
    if (!online.value) {
      queue.value.push(buildRequestPayload({ url, method: 'POST', headers: config.headers || {}, data, withCredentials: config.withCredentials }));
      persistQueue(queue.value);
      return queuedResponse();
    }

    return originalPost(url, data, config);
  };

  axios.put = async (url, data, config = {}) => {
    if (!online.value) {
      queue.value.push(buildRequestPayload({ url, method: 'PUT', headers: config.headers || {}, data, withCredentials: config.withCredentials }));
      persistQueue(queue.value);
      return queuedResponse();
    }

    return originalPut(url, data, config);
  };

  axios.patch = async (url, data, config = {}) => {
    if (!online.value) {
      queue.value.push(buildRequestPayload({ url, method: 'PATCH', headers: config.headers || {}, data, withCredentials: config.withCredentials }));
      persistQueue(queue.value);
      return queuedResponse();
    }

    return originalPatch(url, data, config);
  };

  axios.delete = async (url, config = {}) => {
    if (!online.value) {
      queue.value.push(buildRequestPayload({ url, method: 'DELETE', headers: config.headers || {}, data: null, withCredentials: config.withCredentials }));
      persistQueue(queue.value);
      return queuedResponse();
    }

    return originalDelete(url, config);
  };
}

function patchFetch(queue, online) {
  if (typeof window === 'undefined' || !window.fetch) {
    return;
  }

  const originalFetch = window.fetch.bind(window);

  window.fetch = async (resource, init = {}) => {
    const method = (init.method || 'GET').toUpperCase();
    if (!online.value && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
      queue.value.push(buildFetchPayload(resource, init));
      persistQueue(queue.value);
      return new Response(JSON.stringify({ success: true, queued: true, message: 'Offline queued' }), {
        status: 202,
        headers: { 'Content-Type': 'application/json' },
      });
    }

    return originalFetch(resource, init);
  };
}

export function useOfflineQueue() {
  const queue = ref(loadQueue());
  const online = ref(typeof window !== 'undefined' ? window.navigator.onLine : true);
  const status = ref('idle');
  const replaying = ref(false);

  const queueCount = computed(() => queue.value.length);
  const isOffline = computed(() => !online.value);

  async function replayQueue() {
    if (!online.value || replaying.value || queue.value.length === 0) {
      return;
    }

    status.value = 'replaying';
    replaying.value = true;

    while (queue.value.length > 0) {
      const request = queue.value[0];
      try {
        const response = await fetch(request.url, {
          method: request.method,
          headers: request.headers,
          body: request.body,
          credentials: request.credentials,
        });

        if (!response.ok) {
          throw new Error(`Replay failed with ${response.status}`);
        }

        queue.value.shift();
        persistQueue(queue.value);
      } catch (error) {
        status.value = 'error';
        replaying.value = false;
        return;
      }
    }

    status.value = 'synced';
    replaying.value = false;
  }

  function handleOnline() {
    online.value = true;
    status.value = 'online';
    void replayQueue();
  }

  function handleOffline() {
    online.value = false;
    status.value = 'offline';
  }

  function initialize() {
    if (initialized) {
      return;
    }

    initialized = true;
    handleOnline();
    patchFetch(queue, online);
    patchAxios(queue, online);

    if (typeof window !== 'undefined') {
      window.addEventListener('online', handleOnline);
      window.addEventListener('offline', handleOffline);
    }
  }

  initialize();

  onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    }
  });

  return {
    online,
    queueCount,
    status,
    isOffline,
    replayQueue,
  };
}
