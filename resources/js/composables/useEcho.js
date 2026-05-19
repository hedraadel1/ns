import { useEchoStore } from '../stores/useEchoStore';

export function useEcho() {
  const echoStore = useEchoStore();
  let currentChannel = null;
  let fallbackTimer = null;
  let listenersAttached = false;

  function isAvailable() {
    return typeof window !== 'undefined' && !!window.Echo;
  }

  function clearFallbackTimer() {
    if (fallbackTimer) {
      window.clearTimeout(fallbackTimer);
      fallbackTimer = null;
    }
  }

  function startFallbackTimer() {
    clearFallbackTimer();
    fallbackTimer = window.setTimeout(() => {
      echoStore.enableFallback();
      console.warn('[Echo] switching to polling fallback after connection timeout');
    }, 30000);
  }

  function ensureConnectionListeners() {
    if (!isAvailable() || listenersAttached) {
      return;
    }

    const connection = window.Echo?.connector?.pusher?.connection;
    if (!connection) {
      return;
    }

    listenersAttached = true;

    connection.bind('connected', () => {
      echoStore.setConnectionStatus('connected');
      echoStore.disableFallback();
      clearFallbackTimer();
    });

    connection.bind('disconnected', () => {
      echoStore.setConnectionStatus('disconnected');
      startFallbackTimer();
    });

    connection.bind('error', (error) => {
      echoStore.setConnectionStatus('error', error?.message || 'WebSocket error');
      startFallbackTimer();
    });

    connection.bind('failed', (error) => {
      echoStore.setConnectionStatus('error', error?.message || 'WebSocket failed');
      startFallbackTimer();
    });

    connection.bind('state_change', (states) => {
      if (states.current === 'connecting') {
        echoStore.setConnectionStatus('reconnecting');
      } else if (states.current === 'connected') {
        echoStore.setConnectionStatus('connected');
        echoStore.disableFallback();
        clearFallbackTimer();
      } else if (states.current === 'disconnected' || states.current === 'failed') {
        echoStore.setConnectionStatus('disconnected');
        startFallbackTimer();
      }
    });
  }

  function leaveChannel(channelName) {
    if (!isAvailable() || !channelName) {
      return;
    }

    try {
      window.Echo.leaveChannel(channelName);
      currentChannel = null;
      echoStore.setChannel(null);
    } catch (error) {
      console.warn('Failed to leave Echo channel', error);
    }
  }

  function subscribePrivate(channelName, listeners = {}, onSubscribed = null) {
    ensureConnectionListeners();

    if (!isAvailable() || !channelName || echoStore.shouldUsePolling) {
      echoStore.setError('WebSocket unavailable or polling fallback active');
      return null;
    }

    leaveChannel(channelName);
    currentChannel = window.Echo.private(channelName);
    echoStore.setChannel(channelName);

    Object.entries(listeners).forEach(([event, callback]) => {
      currentChannel.listen(event, callback);
    });

    if (typeof onSubscribed === 'function') {
      currentChannel.subscribed(() => {
        echoStore.setConnectionStatus('connected');
        onSubscribed();
      });
    }

    return currentChannel;
  }

  function subscribePresence(channelName, listeners = {}, onSubscribed = null) {
    ensureConnectionListeners();

    if (!isAvailable() || !channelName || echoStore.shouldUsePolling) {
      echoStore.setError('WebSocket unavailable or polling fallback active');
      return null;
    }

    leaveChannel(channelName);
    currentChannel = window.Echo.join(channelName);
    echoStore.setChannel(channelName);

    Object.entries(listeners).forEach(([event, callback]) => {
      currentChannel.here((users) => {
        if (event === 'here') callback(users);
      });
      currentChannel.joining((user) => {
        if (event === 'joining') callback(user);
      });
      currentChannel.leaving((user) => {
        if (event === 'leaving') callback(user);
      });
      currentChannel.listen(event, callback);
    });

    if (typeof onSubscribed === 'function') {
      currentChannel.subscribed(() => {
        echoStore.setConnectionStatus('connected');
        onSubscribed();
      });
    }

    return currentChannel;
  }

  function parseResponse(response) {
    if (!response) {
      return Promise.resolve([]);
    }
    if (typeof response.json === 'function') {
      return response.json();
    }
    if (response.data !== undefined) {
      return Promise.resolve(response.data);
    }
    return Promise.resolve(response);
  }

  function syncMissedEvents(eventIds, fetchFn) {
    if (typeof fetchFn !== 'function') {
      return Promise.resolve([]);
    }

    return Promise.resolve(fetchFn(eventIds))
      .then(parseResponse)
      .then((data) => data || [])
      .catch((error) => {
        echoStore.setError(error?.message || 'Failed to sync missed events');
        return [];
      });
  }

  function withPollingFallback(wsCallback, pollCallback) {
    if (!isAvailable() || echoStore.shouldUsePolling) {
      return pollCallback();
    }

    try {
      const result = wsCallback();
      if (result && typeof result.then === 'function') {
        return result.catch((error) => {
          echoStore.setError(error?.message || 'WebSocket callback failed');
          return pollCallback();
        });
      }
      return result;
    } catch (error) {
      echoStore.setError(error?.message || 'WebSocket callback failed');
      return pollCallback();
    }
  }

  return {
    isAvailable,
    leaveChannel,
    subscribePrivate,
    subscribePresence,
    syncMissedEvents,
    withPollingFallback,
  };
}
