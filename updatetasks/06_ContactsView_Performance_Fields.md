# Task 06: ContactsView — Performance & Fields

Status: Not Started

Objective:
- Add virtual scrolling, ensure required contact fields present, and fix missing avatar/last_seen display.

Steps:
1. Update Contacts API to support cursor pagination or offset-based with efficient indices.
2. Add virtual scrolling in `resources/js/Pages/ContactsView.vue` and lazy-load avatars.
3. Ensure backend returns `avatar_url`, `last_seen_at`, `metadata` for each contact.
4. Add tests and manual performance validation with large dataset.

Estimate: 1.5 days
