# Task 02: NexusView Metrics Validation

Status: Finished

Objective:
- Replace hardcoded metrics with live API calls and ensure error handling and auto-refresh are correct.

Steps:
1. Validate `/api/v1/health` endpoint response shape.
2. Update `resources/js/Pages/NexusView.vue` to fetch metrics, handle errors, and retry/backoff.
3. Add unit/feature test for health endpoint and UI smoke test.

Notes:
- This was already patched in the UI; file marked Finished. Verify behavior in staging.
