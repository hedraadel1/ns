export function useHaptic() {
  const isSupported = typeof navigator !== 'undefined' && 'vibrate' in navigator;

  const vibrate = (pattern) => {
    if (!isSupported) {
      return;
    }

    try {
      navigator.vibrate(pattern);
    } catch (_error) {
      // Ignore vibration errors on unsupported platforms.
    }
  };

  return {
    success: () => vibrate([12, 25, 12]),
    error: () => vibrate([30, 15, 30]),
    confirm: () => vibrate([16]),
    light: () => vibrate([8]),
  };
}
