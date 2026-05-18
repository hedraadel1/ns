# Task 05: Tasks CRUD

Status: In Progress

Objective:
- Implement full task lifecycle (create, read, update, delete, assign, state transitions) and UI pages.

Progress:
- Backend `TaskController` already implements index/show/store/update/destroy and state actions (`cancel`, `pause`, `resume`).
- Added frontend pages: `resources/js/Pages/TasksView.vue` and `resources/js/Pages/TaskDetail.vue`.
- `TaskMonitor.vue` exists and polls `/api/v1/tasks/stats` and `/api/v1/tasks/active`.

Next steps:
1. Implement Task create/edit UI and form validation.
2. Add real-time updates via WebSocket or polling for task state changes.
3. Add feature tests for task transitions and permission checks.

Recent frontend updates:
- Integrated shared `LoadingSpinner` and `ErrorPanel` components into `TasksView.vue` and `TaskDetail.vue` for consistent loading/error UX.
- Wired SPA navigation in task list to use `router-link`.

Files changed/created:
- app/Http/Controllers/TaskController.php (existing)
- resources/js/Pages/TaskMonitor.vue (existing)
- resources/js/Pages/TasksView.vue (new)
- resources/js/Pages/TaskDetail.vue (new)

Estimate remaining: 1.5 days
