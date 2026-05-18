# Task 04: Contact Analytics

Status: Not Started

Objective:
- Implement ContactAnalytics view, backfill analytics endpoints, and enable CSV export.

Steps:
1. Implement backend analytics endpoints (contacts/analytics) that return time-series data and aggregates.
2. Implement `resources/js/Pages/ContactAnalytics.vue` with charts and export button.
3. Add caching for expensive queries.
4. Add feature tests and UI smoke tests.

Files to change:
- app/Http/Controllers/ContactController.php
- resources/js/Pages/ContactAnalytics.vue
- routes/api.php (add analytics route)

Estimate: 1.5 days
