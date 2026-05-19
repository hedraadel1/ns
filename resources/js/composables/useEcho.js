import { useEchoStore } from '../stores/useEchoStore';

export function useEcho() {
  const echoStore = useEchoStore();
  let currentChannel = null;

  function isAvailable() {
    return typeof window !== 'undefined' && !!window.Echo;
  }

  function leaveChannel(channelName) {
    if (!isAvailable() || !channelName) {
      return;
    }

    try {
      window.Echo.leaveChannel(channelName);
      currentChannel = null;
      echoStore.reset();
    } catch (error) {
      console.warn('Failed to leave Echo channel', error);
    }
  }

  function subscribePrivate(channelName, listeners = {}, onSubscribed = null) {
    if (!isAvailable() || !channelName) {
      echoStore.setError('WebSocket unavailable');
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
        echoStore.setConnected();
        onSubscribed();
      });
    }

    return currentChannel;
  }

  function subscribePresence(channelName, listeners = {}, onSubscribed = null) {
    if (!isAvailable() || !channelName) {
      echoStore.setError('WebSocket unavailable');
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
        echoStore.setConnected();
        onSubscribed();
      });
    }

    return currentChannel;
  }

  function syncMissedEvents(fetchFn) {
    if (typeof fetchFn !== 'function') {
      return Promise.resolve([]);
    }

    return fetchFn()
      .then((response) => response.json())
      .then((data) => data || [])
      .catch((error) => {
        echoStore.setError(error.message || 'Failed to sync missed events');
        return [];
      });
  }

  function withPollingFallback(wsCallback, pollCallback) {
    if (echoStore.error || !isAvailable()) {
      return pollCallback();
    }

    try {
      return wsCallback();
    } catch (error) {
      echoStore.setError(error.message || 'WebSocket callback failed');
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
