# 🎯 TASK: UP-009 - Task 06: NxOfflineBanner Component (F-ACC-06)
- **Status:** 🔴 PENDING
- **Dependencies:** None

## 1. Objective
Create `NxOfflineBanner.vue` — an offline indicator that shows when the browser is disconnected and replays queued mutations on reconnect.

## 2. Files to Create/Modify
- `resources/js/Components/NxOfflineBanner.vue` (new)
- `resources/js/app.js` or a global service for queued mutation handling

## 3. Implementation Steps
1. Build a fixed amber banner that slides down when offline.
2. Listen for `offline` and `online` events.
3. Queue offline mutations in `localStorage` and replay them on reconnect.
4. Hide the banner when connectivity returns.

## ✅ Final Verification
- [ ] Banner appears on network disconnect
- [ ] Queued mutations are stored in localStorage
- [ ] Mutations replay after reconnect
- [ ] Banner hides on reconnect
