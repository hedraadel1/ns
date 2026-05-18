# Phase 9: Settings & Logs Hub — Implementation Plan

**Phase:** 9 — Settings & Logs Hub
**Status:** ✅ COMPLETE
**Date Completed:** 2026-05-17
**Total Tasks:** 10/10

---

## Executive Summary

Phase 9 delivers the Settings Hub and Logs Hub — two foundational administrative pillars for the Nexus platform. The Settings Hub provides a key-value configuration system with type casting, grouping, caching, and a full CRUD API with Vue UI. The Logs Hub implements structured application logging with PSR-3 levels, 10 categories, search/filter capabilities, real-time streaming UI, and an alert trigger system for failures and security events.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Settings Hub                              │
├─────────────────────────────────────────────────────────────┤
│  Setting Model (key-value, typed, grouped)                  │
│  SettingController (CRUD + grouped + bulk)                  │
│  SettingRequest (validation with unique key rules)          │
│  SettingCacheService (Redis cache, TTL, invalidation)       │
│  SettingsPage.vue (Vue UI with toggle, JSON, text editors)  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    Logs Hub                                  │
├─────────────────────────────────────────────────────────────┤
│  Log Model (PSR-3 levels, 10 categories, context JSON)      │
│  LogService (8 log methods + database persistence)           │
│  LogController (index, show, errors, stats, levels, cats)   │
│  LogStream.vue (real-time polling, filter, pause, clear)    │
│  AlertService (4 default rules, evaluate, cache triggers)   │
└─────────────────────────────────────────────────────────────┘
```

---

## Files Created

### 9.1 Settings Hub (5 files)

| File | Lines | Description |
|------|-------|-------------|
| `app/Models/Setting.php` | 172 | Key-value model with 5 types, 6 groups, typed value accessors |
| `app/Http/Controllers/SettingController.php` | 251 | Full CRUD + grouped, public, bulk update endpoints |
| `app/Http/Requests/SettingRequest.php` | 73 | Form request with unique key validation, boolean normalization |
| `app/Services/SettingCacheService.php` | 113 | Redis cache with TTL, per-key and bulk invalidation |
| `resources/js/Pages/SettingsPage.vue` | 280 | Vue page with card grid, toggle switches, JSON editor, add modal |

### 9.2 Logs Hub (5 files)

| File | Lines | Description |
|------|-------|-------------|
| `app/Models/Log.php` | 210 | PSR-3 levels (8), 10 categories, scopes, accessors |
| `app/Services/LogService.php` | 148 | 8 log methods (debug through emergency) + database persistence |
| `app/Http/Controllers/LogController.php` | 241 | index, show, errors, stats, levels, categories, destroy, clear |
| `resources/js/Components/LogStream.vue` | 230 | Real-time polling (5s), level/category filters, pause, load more |
| `app/Services/AlertService.php` | 198 | 4 default rules, evaluateAll, add/update/delete rules |

---

## Settings Hub API Reference

### Endpoints (all under `/api/v1/settings`, auth required)

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/` | `index` | List settings (filter by group, type, is_public, search) |
| POST | `/` | `store` | Create new setting |
| GET | `/{key}` | `show` | Get single setting by key |
| PUT | `/{key}` | `update` | Update setting value |
| DELETE | `/{key}` | `destroy` | Delete setting |
| GET | `/grouped` | `grouped` | Get all settings grouped by group |
| GET | `/public` | `publicSettings` | Get all public settings |
| PUT | `/bulk` | `bulkUpdate` | Bulk update multiple settings |

### Setting Types

| Type | Cast | Description |
|------|------|-------------|
| `string` | string | Plain text |
| `integer` | int | Numeric value |
| `boolean` | bool | True/false toggle |
| `json` | array | JSON object/array |
| `text` | string | Long-form text |

### Setting Groups

| Group | Label |
|-------|-------|
| `general` | General |
| `security` | Security |
| `ai` | AI Configuration |
| `notifications` | Notifications |
| `integrations` | Integrations |
| `ui` | User Interface |

---

## Logs Hub API Reference

### Endpoints (all under `/api/v1/logs`, auth required)

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/` | `index` | List logs (filter by level, category, source, user, date, search) |
| GET | `/{id}` | `show` | Get single log entry |
| DELETE | `/{id}` | `destroy` | Delete a log entry |
| POST | `/clear` | `clear` | Truncate all logs |
| GET | `/stats` | `stats` | Get log statistics (total, by_level, by_category, today, errors_today) |
| GET | `/levels` | `levels` | Get available log levels |
| GET | `/categories` | `categories` | Get available log categories |
| GET | `/errors` | `errors` | Get error-level logs only |

### Log Levels (PSR-3 compatible)

| Level | Color | Description |
|-------|-------|-------------|
| `debug` | gray | Detailed debug information |
| `info` | blue | Interesting events |
| `notice` | cyan | Normal but significant events |
| `warning` | yellow | Exceptional occurrences that are not errors |
| `error` | red | Runtime errors |
| `critical` | red | Critical conditions |
| `alert` | red | Action must be taken immediately |
| `emergency` | red | System is unusable |

### Log Categories

| Category | Description |
|----------|-------------|
| `auth` | Authentication events |
| `security` | Security events |
| `api` | API requests |
| `workflow` | Workflow execution |
| `agent` | Agent activity |
| `ai` | AI provider calls |
| `system` | System events |
| `database` | Database queries |
| `cache` | Cache operations |
| `queue` | Queue jobs |

---

## Alert Service

### Default Alert Rules

| Rule ID | Name | Condition | Threshold | Window |
|---------|------|-----------|-----------|-------|
| `high_error_rate` | High Error Rate | error_rate | 10% | 5 min |
| `security_failure` | Security Failure | category_level (security) | 1 occurrence | 1 min |
| `ai_provider_down` | AI Provider Down | category_level (ai) | 1 occurrence | 3 min |
| `workflow_failure` | Workflow Failure | category_level (workflow) | 1 occurrence | 5 min |

### AlertService Methods

| Method | Description |
|--------|-------------|
| `evaluateAll()` | Evaluate all enabled rules against recent logs |
| `getRules()` | Get all alert rules (from config or defaults) |
| `getRule($id)` | Get a specific rule by ID |
| `addRule($rule)` | Add a new alert rule |
| `updateRule($id, $updates)` | Update an existing rule |
| `deleteRule($id)` | Delete a rule |
| `getRecentAlerts($limit)` | Get recently triggered alerts from cache |

---

## Settings Cache Service

### Cache Strategy

| Key Pattern | TTL | Invalidation |
|-------------|-----|-------------|
| `setting.{key}` | 1 hour | On setting update/delete |
| `settings.all` | 1 hour | On any setting change |
| `settings.public` | 1 hour | On any setting change |
| `settings.group.{group}` | 1 hour | On any setting change |

### Methods

| Method | Description |
|--------|-------------|
| `get($key, $default)` | Get a setting by key with cache |
| `getAll($group)` | Get all settings, optionally filtered by group |
| `getPublic()` | Get all public settings |
| `set($key, $value)` | Set a setting value and update cache |
| `forget($key)` | Forget a cached setting |
| `clear()` | Clear all settings cache |
| `has($key)` | Check if a setting exists |

---

## Vue UI Components

### SettingsPage.vue

- **Card grid layout** — Settings displayed as cards with key, type, value, group, visibility
- **Type-aware editors** — Toggle switch for booleans, text input for strings/integers, JSON textarea for JSON type
- **Add modal** — Form to create new settings with all fields
- **Group filter** — Dropdown to filter by setting group
- **Search** — Real-time search by key name
- **Inline editing** — Blur-to-save on value inputs

### LogStream.vue

- **Real-time polling** — Fetches logs every 5 seconds (pauseable)
- **Level filter** — Dropdown to filter by PSR-3 log level
- **Category filter** — Dropdown to filter by log category
- **Stats bar** — Total, today, errors today counters
- **Color-coded badges** — Each log level has a distinct color
- **Context display** — JSON context shown in expandable pre block
- **Load more** — Pagination support for large log volumes
- **Pause/Resume** — Stop polling without losing current view
- **Clear** — Clear the local log view

---

## Dependencies

- Laravel 11.x (Cache, Log, Validator facades, Sanctum auth)
- Redis (SettingCacheService, AlertService cache)
- Existing models: Contact, Conversation, Message, Memory, Agent, Workflow, Task
- Vue 3 + Vite (SettingsPage.vue, LogStream.vue)

---

## Phase 9 Complete ✅

All 10 tasks completed. The Settings & Logs Hub provides comprehensive application configuration management and observability infrastructure, enabling administrators to manage platform settings with low-latency cached access and monitor application health through structured logging, real-time streaming, and automated alerting.
