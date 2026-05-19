# 🔍 Nexus UI/UX Compliance Audit Report — `uiuv.md`

**Auditor Role:** Senior UI/UX Auditor · Lead Frontend Architect · DX Specialist  
**Audit Date:** 2026-05-19  
**Specification Source:** Nexus Master UI/UX Specification Report (Final)  
**Codebase Snapshot:** `resources/js/` · `tailwind.config.js` · `routes/api.php` · `app/Events/`  
**Verdict Scale:** ✅ Compliant · ⚠️ Partial / Near-Miss · ❌ Non-Compliant / Missing

---

## 1. 🎨 Design System & CSS Audit

### 1.1 Token Deviations — `tailwind.config.js` & `resources/css/app.css`

The `tailwind.config.js` contains **zero custom color tokens**. The only theme extension is a font-family override that itself violates the spec. All design tokens are stored in CSS custom properties in `app.css`, but every single semantic color maps to the wrong hex value or is missing entirely.

#### Color Palette Violations

| Spec Token | Spec Hex | Actual Variable | Actual Hex | File & Line | Verdict |
|---|---|---|---|---|---|
| `Surface-High` (body bg) | `#0B0E14` | `--color-bg-primary` | `#0a0a0a` | `app.css:33` | ❌ Wrong — 4-shade darker |
| `Surface-Mid` (card bg) | `#161B22` | `--color-bg-secondary` | `#111111` | `app.css:34` | ❌ Wrong — 25% too dark |
| `Action-Primary` / Nexus Blue | `#007AFF` | `--color-primary` | `#4ade80` | `app.css:15` | ❌ **Entire wrong color family** — spec is blue, codebase is emerald green |
| `Action-Primary hover` | (derived blue) | `--color-primary-hover` | `#22c55e` | `app.css:16` | ❌ Wrong — green |
| `AI-Core` / Hédra Purple | `#6366F1` | **Not defined** | — | — | ❌ Missing entirely |
| `Status-Success` / Emerald | `#10B981` | `--color-success` | `#4ade80` | `app.css:27` | ❌ Wrong shade — 1.5× brighter |
| `Status-Warning` / Amber | `#F59E0B` | `--color-warning` | `#fbbf24` | `app.css:28` | ⚠️ Different Amber — off by ~1 Tailwind stop |
| `Status-Error` / Crimson | `#EF4444` | `--color-error` | `#f87171` | `app.css:29` | ❌ Wrong — `f87171` is red-300, not red-500 |
| `--color-accent-blue` | `#007AFF` (Action-Primary) | `--color-accent-blue` | `#60a5fa` | `app.css:20` | ❌ Wrong — blue-400, not Nexus Blue |
| `--color-accent-purple` | `#6366F1` (AI-Core) | `--color-accent-purple` | `#a78bfa` | `app.css:21` | ❌ Wrong — violet-400, not Hédra Purple |

**Root Cause:** The entire color palette was built around an emerald/green design language. The spec requires a dark-space blue/indigo aesthetic (`#007AFF` Nexus Blue, `#6366F1` Hédra Purple). This is a **complete palette replacement**, not a minor tweak.

#### Missing Tailwind Color Extensions

`tailwind.config.js` (lines 12–19) has no `colors` key under `theme.extend`. All seven spec-mandated semantic colors (`surface-high`, `surface-mid`, `action-primary`, `ai-core`, `status-success`, `status-warning`, `status-error`) are absent as Tailwind tokens. This means no utility classes like `bg-action-primary` or `text-ai-core` can be used—developers must reach for raw hex values or wrong CSS vars.

```js
// tailwind.config.js — CURRENT (lines 12–19)
theme: {
  extend: {
    fontFamily: {
      sans: ['Figtree', ...defaultTheme.fontFamily.sans], // ← wrong font
    },
  },
},

// REQUIRED addition:
colors: {
  'surface-high':    '#0B0E14',
  'surface-mid':     '#161B22',
  'action-primary':  '#007AFF',
  'ai-core':         '#6366F1',
  'status-success':  '#10B981',
  'status-warning':  '#F59E0B',
  'status-error':    '#EF4444',
},
```

---

### 1.2 Typography Violations

| Spec Requirement | Actual | File & Line | Verdict |
|---|---|---|---|
| System font: **`Inter`** | **`Figtree`** | `tailwind.config.js:15` | ❌ Wrong typeface |
| AI/Data font: **`JetBrains Mono`** | **Not installed, not referenced anywhere** | Entire codebase | ❌ Missing entirely |
| H1/H2 tracking: `-0.02em` | Not set globally | — | ❌ Missing |
| Body line-height: `1.6` | Not set globally | — | ❌ Missing |
| `font-variant-numeric: tabular-nums` on Mono | Not set | — | ❌ Missing |
| `<pre>` in `MemoryView.vue` (raw JSON) | No explicit font-family | `MemoryView.vue:234` | ❌ Falls back to browser mono, not JetBrains Mono |
| Config textarea in `WorkflowBuilder.vue` | `font-mono` (Tailwind = system mono) | `WorkflowBuilder.vue:227` | ❌ System mono, not JetBrains Mono |
| `setting-header h3` key display | `font-family: monospace` | `SettingsView.vue:335` | ❌ System monospace |

**Impact:** Every `trace_id`, JSON payload, confidence score, and code block in the application renders in system monospace instead of the spec-mandated JetBrains Mono. This affects `MemoryView.vue`, `WorkflowBuilder.vue`, `SettingsView.vue`, and any future log display.

---

### 1.3 Glassmorphism Quality Audit

The spec defines **Glassmorphism 2.0** with three precise values. Here is the per-rule status:

| Rule | Spec Value | Actual Value | File & Line | Verdict |
|---|---|---|---|---|
| `backdrop-filter` | `blur(12px)` | `blur(12px)` ✓ | `app.css:95` | ✅ Correct |
| `border` | `1px solid rgba(255,255,255,0.1)` | `1px solid var(--color-border)` → `rgba(255,255,255,0.1)` ✓ | `app.css:97` | ✅ Correct |
| **`background-color`** | `rgba(22, 27, 34, 0.7)` | `rgba(255, 255, 255, 0.05)` | `app.css:94` | ❌ **Critical mismatch** — spec is 70% opaque dark, actual is 5% opaque light — renders nearly invisible |

#### Per-Component Glass Consistency

| Component / File | Glass Implementation | Uses `.glass`? | Spec-Compliant? |
|---|---|---|---|
| `Card.vue` `.card-glass` | `background: var(--color-bg-glass)` = `rgba(255,255,255,0.05)` + `blur(12px)` | Partial (own CSS) | ❌ Wrong bg |
| `AgentsView.vue` cards | `bg-slate-900/70 backdrop-blur` (Tailwind inline) | ❌ No | ⚠️ Inconsistent |
| `NexusView.vue` cards | `bg-slate-900/70 shadow-lg backdrop-blur` | ❌ No | ⚠️ Inconsistent |
| `TaskMonitor.vue` wrapper | `bg-slate-900/70 shadow-lg backdrop-blur` | ❌ No | ⚠️ Inconsistent |
| `AIModelsView.vue` header | `bg-slate-900/70 shadow-lg backdrop-blur` | ❌ No | ⚠️ Inconsistent |
| `DashboardView.vue` `.kpi-card` | `background: rgba(255,255,255,0.03)` inline scoped CSS | ❌ No | ❌ No blur at all |
| `ChatInterface.vue` | `background: rgba(255,255,255,0.02)` inline scoped CSS | ❌ No | ❌ No blur at all |
| `App.vue` header | `bg-slate-900/90 backdrop-blur` | ❌ No | ⚠️ Missing border spec |
| `SettingsView.vue` `.glass` | Uses `.glass` CSS class ✓ | ✅ Yes | ❌ Wrong bg value (inherited) |

**Finding:** 8 of 9 audited surfaces either skip the `.glass` utility entirely or use ad-hoc inline Tailwind classes. Zero surfaces achieve the correct `rgba(22,27,34,0.7)` background.

---

## 2. 🧱 Component Library Implementation Status

### 2.1 Missing Nx Atomic Components

All five components defined in the Master Report **Section 2** are absent from `resources/js/Components/`:

| Spec Component | Status | Required Props | Required Behaviour |
|---|---|---|---|
| `NxAiPulse.vue` | ❌ **Does not exist** | `state: idle\|thinking\|speaking\|error` | CSS keyframe animations per state; conic-gradient rotation; crimson jitter |
| `NxGlassCard.vue` | ❌ **Does not exist** | `elevation: 1\|2\|3`, `hoverable: Boolean` | Named slots `#header` (sticky), `#body` (scroll), `#footer`; shadow spread mapping |
| `NxTokenMeter.vue` | ❌ **Does not exist** | `currentTokens: Int`, `maxTokens: Int` | SVG horizontal bar; 3-threshold colour logic (Blue → Amber → Crimson); "Trim Context" suggestion at >90% |
| `NxLiveLoader.vue` | ❌ **Does not exist** | `taskId: UUID`, `status: String` | Pulsing glass pill; expandable terminal log feed via Reverb `task_checkpoints` |
| `NxActionButton.vue` | ❌ **Does not exist** | `variant`, `loading`, `disabled`, `optimistic: Boolean` | Pseudo-success on `optimistic=true`; rollback to `error` on promise rejection |

The codebase contains five loader-related components (`GlobalLoader.vue`, `AnimatedLoader.vue`, `Loader.vue`, `LoadingSpinner.vue`, `FooterLoader.vue`) and a `Button.vue`, but **none** satisfy the Nx component contracts.

---

### 2.2 Existing Components vs. Spec Requirements

#### `Button.vue` → Should become `NxActionButton.vue`

```
resources/js/Components/Button.vue
```

| Spec Requirement | Code Reality | Line | Verdict |
|---|---|---|---|
| `optimistic` prop (Boolean) | **Absent** — no such prop defined | — | ❌ Missing |
| Pseudo-success state on `optimistic=true` | **Absent** | — | ❌ Missing |
| Rollback to `error` on promise rejection | **Absent** | — | ❌ Missing |
| `variant: primary` → Nexus Blue `#007AFF` | Renders `--color-primary` = `#4ade80` (green) | `Button.vue:72` | ❌ Wrong color |
| `variant: danger` → Crimson `#EF4444` | Renders `--color-error` = `#f87171` (wrong shade) | `Button.vue:105` | ❌ Wrong shade |
| Loading state shows spinner | `<span class="btn-spinner">` ✓ | `Button.vue:13` | ✅ Exists |
| `disabled` prop | Defined ✓ | `Button.vue:36` | ✅ Exists |

#### `Card.vue` → Should become `NxGlassCard.vue`

```
resources/js/Components/Card.vue
```

| Spec Requirement | Code Reality | Line | Verdict |
|---|---|---|---|
| `elevation` prop (1, 2, 3) | **Absent** — uses `variant: default\|elevated\|glass\|bordered` | — | ❌ Wrong prop API |
| `hoverable` prop (Boolean) | Uses `hover: Boolean` ✓ (different name) | `Card.vue:31` | ⚠️ Renamed |
| `translate-y-[-2px]` on hover | `translateY(-1px)` | `Card.vue:51` | ⚠️ Off by 1px |
| Named slot `#body` (scrollable) | Uses default slot, no `#body` | `Card.vue:12` | ❌ Wrong slot name |
| Named slot `#header` (sticky top) | `#header` slot exists ✓ | `Card.vue:3` | ✅ Correct |
| Named slot `#footer` (actions) | `#footer` slot exists ✓ | `Card.vue:14` | ✅ Correct |
| `.card-glass` background: `rgba(22,27,34,0.7)` | `var(--color-bg-glass)` = `rgba(255,255,255,0.05)` | `Card.vue:66` | ❌ Wrong background |
| Shadow spread mapping to elevation | No elevation prop → no mapping | — | ❌ Missing |

#### `MobileFooter.vue`

```
resources/js/Components/MobileFooter.vue
```

| Spec Requirement | Code Reality | Line | Verdict |
|---|---|---|---|
| Tabs: Home, Memory, Contacts, Tasks, Search | Tabs: `dashboard, chat, contacts, agents, workflows, settings` | `MobileFooter.vue:27-33` | ❌ Wrong tabs — none match spec |
| Floating Action Orb (voice dictation) | **Absent** | — | ❌ Missing |
| Height: `64px` | `height: 60px` | `MobileFooter.vue:46` | ⚠️ 4px short |
| Heavy blur effect | `background: var(--color-bg-secondary)` — no blur | `MobileFooter.vue:44-50` | ❌ Missing backdrop-filter |
| **Mounted in `App.vue`** | **Not rendered — dead component** | `App.vue` (absent) | ❌ Never used |

#### `Toast.vue`

```
resources/js/Components/Toast.vue
```

| Spec Requirement | Code Reality | Verdict |
|---|---|---|
| Pinia `useNotificationStore` integration | `window.$toast` global object (anti-pattern) | ❌ Wrong architecture |
| Mounted at app level | Defined but never imported/mounted in `App.vue` | ❌ Dead component |
| Revert failed optimistic actions | No rollback integration — standalone only | ❌ Missing |

---

## 3. 🗺️ Layout & Navigation Violations

### 3.1 The 3-Pane Architecture Test

**Spec Architecture:**
```
┌────────────┬─────────────────┬──────────────────────────────┐
│  Nav Rail  │   Hub Sidebar   │         Workspace            │
│  80-240px  │     320px       │          flex-1              │
│ (collapse) │  Entity list    │  Glass header + Breadcrumbs  │
│            │  Sticky search  │  Action Icons                │
│            │  Sort filters   │                              │
└────────────┴─────────────────┴──────────────────────────────┘
```

**Actual Layout (`App.vue`):**
```
┌───────────────────────┬──────────────────────────────────────┐
│   Navigation (320px)  │            Workspace                 │
│   Fixed, no collapse  │   (max-w-7xl cap — bad for app)      │
│   Hub list only       │   Tab bar replaces Hub Sidebar       │
│   No search/sort      │   No Action Icons in header          │
└───────────────────────┴──────────────────────────────────────┘
```

#### Specific Violations in `App.vue`

| Violation | Line(s) | Detail |
|---|---|---|
| `max-w-7xl` caps entire layout | `3` | A full-screen app must fill `100vw`. `max-w-7xl` (1280px) constrains the layout on wide monitors. |
| `Navigation` is `lg:w-80` = 320px fixed | `4-9` | Spec: Rail is `80px` collapsed / `240px` expanded. This is 320px and never collapses. |
| No collapse toggle on Nav Rail | `4-9` | Zero click handler, no `collapsed` state, no width transition logic. |
| `<TabSystem>` renders in place of Hub Sidebar | `29` | The middle pane (entity list with search + sort filters) is replaced by a flat tab bar. |
| No second pane / Hub Sidebar | — | Entirely absent. No `320px` entity list panel exists. |
| Workspace header missing Action Icons | `14-26` | Header has a label and count badge only. Spec requires action icon buttons. |
| Header lacks Glassmorphism spec | `12` | `bg-slate-900/90 backdrop-blur` — missing `border: 1px solid rgba(255,255,255,0.1)` and correct background opacity. |

#### Specific Violations in `Navigation.vue`

| Violation | Line(s) | Detail |
|---|---|---|
| No `80px` collapsed icon-only state | Entire file | Navigation always shows full labels. No icon-only mode. |
| No collapse button | Entire file | No toggle control to switch between 80px and 240px widths. |
| Uses emoji as icons | `22-24` | Spec: Lucide-Vue-Next (2px stroke width) — currently using emoji characters. |
| `indexFor()` shows numeric order badges | `36-40` | Unnecessary and not in the spec — distracts from navigation purpose. |

---

### 3.2 Universal Command Bar (Cmd+K)

**Status: 100% absent.**

A full-text search of all files in `resources/js/` finds:
- Zero occurrences of `keydown`, `Meta`, `Ctrl+K`, `CommandBar`, `commandbar`, `cmd`, `fuzzy`
- Zero overlay or modal component for global search
- No fuzzy-search logic for Contacts, Memories, or Agents
- No keyboard shortcut registration

**Missing deliverables:** `CommandBar.vue` component, keyboard event listener in `App.vue`, fuzzy-search utility, and integration with `/api/v1/contacts`, `/api/v1/memories`, `/api/v1/agents` search endpoints.

---

### 3.3 RTL Readiness

**Status: Zero RTL support.**

No `dir` attribute on the HTML root. No `[dir=rtl]` CSS rules. No logical CSS properties.

| Hardcoded Directional Class/Style | File | Line | RTL Breakage |
|---|---|---|---|
| `border-r border-slate-800` (Nav right border) | `Navigation.vue` | `2` | In RTL, nav is on the right — `border-r` creates an inner border instead of outer |
| `border-l border-slate-800/70` (Workspace left border) | `App.vue` | `11` | Same problem reversed |
| `flex-row` (no `rtl:flex-row-reverse`) | `App.vue` | `3` | Nav Rail stays on left in RTL |
| `ml-3` (badge margin-left) | `Navigation.vue` | `35` | Should be `ms-3` (margin-inline-start) |
| All `px-*` paddings | Multiple | — | ✅ Safe (symmetric) |
| All `text-left` | `Navigation.vue` | `13` | ❌ Should be `text-start` |
| `top: 1rem; right: 1rem` in Toast | `Toast.vue` | `107-108` | ❌ Toast position breaks in RTL — should be `inset-inline-end` |

---

## 4. ⚡ Real-Time & State Integrity

### 4.1 WebSocket Hooks — `window.Echo`

**Status: Completely unimplemented.**

`resources/js/bootstrap.js` (5 lines total):
```js
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// ← No Laravel Echo. No pusher-js. No window.Echo. Zero.
```

`package.json` dependencies contain no `laravel-echo` or `pusher-js` entries.

| Backend Event | Event File | Frontend Listener | Verdict |
|---|---|---|---|
| `TokenStreamed` | `app/Events/TokenStreamed.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `AgentExecuted` | `app/Events/AgentExecuted.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `WorkflowStepCompleted` | `app/Events/WorkflowStepCompleted.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `WorkflowStarted` | `app/Events/WorkflowStarted.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `WorkflowCompleted` | `app/Events/WorkflowCompleted.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `MessageCompleted` | `app/Events/MessageCompleted.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `MessageReceived` | `app/Events/MessageReceived.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `MessageSent` | `app/Events/MessageSent.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `MemoriesExtracted` | `app/Events/MemoriesExtracted.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `MemoryIndexed` | `app/Events/MemoryIndexed.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `MemoryVectorized` | `app/Events/MemoryVectorized.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `ContactCreated` | `app/Events/ContactCreated.php` ✓ | **Not listened anywhere** | ❌ Dead event |
| `JobFailedEvent` | `app/Events/JobFailedEvent.php` ✓ | **Not listened anywhere** | ❌ Dead event |

**All 13 backend real-time events are implemented server-side and silently ignored by the frontend.**

`ChatInterface.vue` sends a message via `POST /api/v1/chat/send` and polls for one synchronous response. It does **not** use `TokenStreamed` for streaming — the user sees no token-by-token output. `sendMessage()` at line 81 waits for the full HTTP response before rendering.

---

### 4.2 Pinia Store Audit

**Status: Pinia is not installed and zero stores exist.**

`package.json` has no `pinia` dependency.  
`resources/js/app.js` (5 lines): `createApp(App).mount('#app')` — no `app.use(createPinia())`.  
A ripgrep of `defineStore` across the entire codebase returns zero results.

| Spec Store | Status | Consequence |
|---|---|---|
| `useChat` | ❌ Not created | Chat session, history, and streaming state are ephemeral local `ref()` in `ChatInterface.vue` — lost on component unmount |
| `useContacts` | ❌ Not created | Contact list re-fetched from API every time `ContactsView.vue` mounts |
| `useWorkflows` | ❌ Not created | Workflow state in `WorkflowBuilder.vue` is unsynchronized with `WorkflowsView.vue` |
| `useSystem` | ❌ Not created | No global system state — no cross-hub awareness of agent status or connection health |
| `useNotificationStore` | ❌ Not created | `Toast.vue` uses `window.$toast` global hack instead |

The spec mentions `unreadCount` in a notification store. This field does not exist anywhere in the codebase. The `Toast.vue` component itself is never mounted in `App.vue` — meaning even the `window.$toast` hack is non-functional at the app level.

---

### 4.3 Optimistic UI Failures

| Action | File & Lines | Current Behaviour | Spec Requirement | Verdict |
|---|---|---|---|---|
| Send message | `ChatInterface.vue:87-130` | User message added to DOM immediately ✓ — but error path pushes an error **message** into the chat instead of **reverting** the user message | Must revert user message + show `NxToast` error with draft preserved | ❌ No rollback |
| Toggle boolean setting | `SettingsView.vue:174-210` | **Waits for `PUT /api/v1/settings/{key}`** to resolve before any DOM update. Uses `alert()` on error. | Must update toggle instantly; revert + toast if 500 | ❌ Not optimistic |
| Update text setting | `SettingsView.vue:174-210` | Same — waits for API on `@blur` | Must update instantly; revert on failure | ❌ Not optimistic |
| Save workflow | `WorkflowBuilder.vue:376-407` | `savingWorkflow=true` shows "Saving..." text; no DOM state change | Should update workflow name/status instantly | ❌ Not optimistic |
| Publish workflow | `WorkflowBuilder.vue:410-434` | Sets `currentWorkflow.value.status = 'published'` **only after** successful API response | Should flip status instantly, revert on error | ❌ Not optimistic |

Additionally, `WorkflowBuilder.vue:381-383` contains a logic bug: it checks `currentWorkflow.value.id.startsWith('workflow-temp-')` to determine POST vs PUT, but new workflows created via `createNewWorkflow()` at line 312 use the prefix `workflow-` (not `workflow-temp-`). Saves of new workflows will always attempt `PUT` to a non-existent route.

---

## 5. 📱 Mobile-First Compliance

### 5.1 Breakpoint Gaps

#### `WorkflowBuilder.vue` — Critical Mobile Overflow

The entire 3-panel layout has **zero responsive breakpoints**:

```html
<!-- WorkflowBuilder.vue:34-237 -->
<div class="flex min-h-0 flex-1 gap-4">
  <div class="w-64 flex-shrink-0 ...">     <!-- 256px fixed — never collapses -->
  <div class="flex-1 min-w-0 ...">          <!-- Canvas — compresses to nothing -->
  <div class="w-80 flex-shrink-0 ...">     <!-- 320px fixed — never collapses -->
</div>
```

On a 375px mobile screen: 256 + flex-gap + 320 = ~588px in a 375px container. The layout overflows horizontally. The spec requires a linear "Step Sequencer" fallback on mobile — **not implemented**.

#### `DashboardView.vue` — Grid Overflow

```css
/* DashboardView.vue:99-102 */
.dashboard-grid {
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
}
```

`minmax(400px, 1fr)` means each column is at minimum 400px wide. On a 375px screen, no column fits — the browser forces horizontal scroll. Fix: `minmax(min(400px, 100%), 1fr)`.

#### `MobileFooter.vue` — Never Rendered

The component is defined but never imported or rendered in `App.vue`. Even if it were, `App.vue` line `32` (`<main class="flex-1 overflow-y-auto p-4 sm:p-6">`) has no bottom padding to accommodate the footer's 60px height — content would be hidden behind it.

#### Other Missing Mobile Views

| Spec Feature | Status |
|---|---|
| Intent Routing Matrix mobile accordion | ❌ The entire Intent Matrix is not implemented |
| Consolidation Map mobile accordion | ❌ The Consolidation Map (D3/ECharts) does not exist |
| Thought-Trace "Glass Terminal" | ❌ Not implemented |
| Mobile `< Back` chevron on detail slide | ❌ No slide-over navigation on mobile |

---

### 5.2 Touch Target Audit

| Element | File | Measured Touch Area | Spec Minimum | Verdict |
|---|---|---|---|---|
| Mobile footer tabs | `MobileFooter.vue:63` | `min-height: 44px` + `padding: 0.5rem` | 44×44px | ✅ Meets minimum |
| Navigation hub buttons | `Navigation.vue:14` | `px-4 py-3` ≈ 44px height at sm font | 44×44px | ✅ Borderline acceptable |
| WorkflowBuilder "Delete" btn | `WorkflowBuilder.vue:154` | `px-2 py-0.5` ≈ 22px height | 44×44px | ❌ 50% too small |
| WorkflowBuilder "+ stepType" btns | `WorkflowBuilder.vue:182` | `py-1.5` ≈ 30px height | 44×44px | ❌ 32% too small |
| WorkflowBuilder zoom +/- btns | `WorkflowBuilder.vue:83-99` | `px-2 py-1` ≈ 28px height | 44×44px | ❌ 36% too small |
| MemoryView Prev/Next pagination | `MemoryView.vue:177-191` | `px-3 py-2` ≈ 36px height | 44×44px | ⚠️ Below minimum |
| AgentsView "Refresh" button | `AgentsView.vue:12` | `px-3 py-2` ≈ 36px height | 44×44px | ⚠️ Below minimum |

---

## 6. 🚀 Prioritized Refactoring Plan

Ordered by dependency chain. Each item is a specific, file-level action.

---

### Phase 1 — Foundation (Unblock all subsequent work)

**P1.1 — Install missing packages**  
File: `package.json`  
Add to `dependencies`:
```json
"pinia": "^2.2.6",
"laravel-echo": "^1.16.1",
"pusher-js": "^8.4.0",
"lucide-vue-next": "^0.511.0",
"vue-echarts": "^7.0.3",
"echarts": "^5.6.0",
"markdown-it": "^14.1.0",
"highlight.js": "^11.11.1"
```

**P1.2 — Fix font family**  
File: `tailwind.config.js`, line 15  
Change: `'Figtree'` → `'Inter'`  
Add: `mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono]`  
Add font import to `resources/css/app.css` via Google Fonts CDN for both Inter and JetBrains Mono.

**P1.3 — Add Nexus color tokens to Tailwind**  
File: `tailwind.config.js`, inside `theme.extend`  
Add the 7 spec colors as documented in Section 1.1, plus `surface-high` as the default body background.

**P1.4 — Remap all CSS custom properties**  
File: `resources/css/app.css`, lines 9–66  
Replace all `--color-*` variables with spec-correct hex values. Key changes:
- `--color-primary`: `#4ade80` → `#007AFF`
- `--color-bg-primary`: `#0a0a0a` → `#0B0E14`
- `--color-bg-secondary`: `#111111` → `#161B22`
- `--color-success`: `#4ade80` → `#10B981`
- `--color-error`: `#f87171` → `#EF4444`
- Add `--color-ai-core: #6366F1`

**P1.5 — Fix `.glass` background**  
File: `resources/css/app.css`, line 94  
Change: `background: var(--color-bg-glass)` → `background: rgba(22, 27, 34, 0.7)`

**P1.6 — Initialize Pinia**  
File: `resources/js/app.js`  
Add `import { createPinia } from 'pinia'` and `app.use(createPinia())`.

**P1.7 — Initialize Laravel Echo**  
File: `resources/js/bootstrap.js`  
Add Echo initialization targeting Laravel Reverb:
```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
```

**P1.8 — Configure Lucide globally**  
File: `resources/js/app.js`  
Register `lucide-vue-next` globally with `strokeWidth: 2` default.

---

### Phase 2 — Create All Five Missing Nx Components

**P2.1 — Create `NxAiPulse.vue`**  
File: `resources/js/Components/NxAiPulse.vue` (new)  
Props: `state: { type: String, validator: v => ['idle','thinking','speaking','error'].includes(v) }`  
Implement four `@keyframes`:
- `idle`: scale 1.0→1.05 + opacity 0.4→0.7 over 4s loop
- `thinking`: conic-gradient rotation at 1s linear infinite
- `speaking`: randomized scale on streaming token events
- `error`: `translateX(-2px to 2px)` shake at 100ms interval

**P2.2 — Create `NxGlassCard.vue`**  
File: `resources/js/Components/NxGlassCard.vue` (new)  
Props: `elevation: { type: Number, validator: v => [1,2,3].includes(v), default: 1 }`, `hoverable: { type: Boolean, default: false }`  
Slots: `#header` (position sticky, top-0), `#body` (overflow-y-auto, flex-1), `#footer` (border-top actions area)  
Shadow spread by elevation: `1→shadow-sm`, `2→shadow-md`, `3→shadow-lg`  
Background: `rgba(22,27,34,0.7)` + `backdrop-filter: blur(12px)` + `border: 1px solid rgba(255,255,255,0.1)`  
Hover: `:hover { transform: translateY(-2px) }` when `hoverable=true`

**P2.3 — Create `NxTokenMeter.vue`**  
File: `resources/js/Components/NxTokenMeter.vue` (new)  
Props: `currentTokens: Number`, `maxTokens: { type: Number, default: 6000 }`  
Render an SVG `<rect>` progress bar. Computed `fillColor`:
- `< 70%` → `#007AFF` (Nexus Blue)
- `70%–90%` → `#F59E0B` (Amber)
- `> 90%` → `#EF4444` (Crimson) + emit `trim-context` event

**P2.4 — Create `NxLiveLoader.vue`**  
File: `resources/js/Components/NxLiveLoader.vue` (new)  
Props: `taskId: String`, `status: String`  
Default state: pulsing glass pill (`animate-pulse`, glassmorphism background)  
Expanded state (click to toggle): terminal-style `<pre>` log feed  
Wire to Echo: `window.Echo.private('tasks').listen('TaskCheckpoint', (e) => logs.value.push(e.line))`

**P2.5 — Create `NxActionButton.vue`**  
File: `resources/js/Components/NxActionButton.vue` (new)  
Props: `variant: primary|secondary|danger|ghost`, `loading: Boolean`, `disabled: Boolean`, `optimistic: Boolean`  
When `optimistic=true`:
1. On click: immediately set internal `state = 'success'` (green checkmark, no spinner)
2. Emit the action promise via `@action` event
3. If promise rejects: set `state = 'error'` for 2s, then revert
Colors must use spec tokens: `primary→#007AFF`, `danger→#EF4444`

---

### Phase 3 — Create Pinia Stores

**P3.1 — Create `resources/js/Stores/useNotificationStore.js`**  
State: `toasts: []`, `unreadCount: 0`  
Actions: `addToast(toast)`, `dismiss(id)`, `incrementUnread()`, `resetUnread()`  
Remove `window.$toast` from `Toast.vue`; connect `Toast.vue` to this store.  
Mount `Toast.vue` in `App.vue`.

**P3.2 — Create `resources/js/Stores/useChat.js`**  
State: `messages: []`, `isStreaming: Boolean`, `currentSession: null`, `draftMessage: String`  
Wire `TokenStreamed` Echo event to append tokens to the last `agent` message.  
Include rollback action: `revertLastMessage()` for failed sends.

**P3.3 — Create `resources/js/Stores/useContacts.js`**  
State: `contacts: []`, `selectedId: null`, `loading: Boolean`  
Cache contacts — prevent re-fetch if already loaded.  
Wire `ContactCreated` Echo event to prepend new contacts.

**P3.4 — Create `resources/js/Stores/useWorkflows.js`**  
State: `workflows: []`, `currentWorkflow: null`, `selectedStep: null`  
Wire `WorkflowStarted`, `WorkflowCompleted`, `WorkflowStepCompleted` Echo events.

**P3.5 — Create `resources/js/Stores/useSystem.js`**  
State: `connectionState: 'connecting'|'connected'|'disconnected'`, `activeAgents: []`  
Wire Echo connection events to `connectionState`.  
Wire `AgentExecuted` to update `activeAgents` list in real-time.

---

### Phase 4 — Layout & Navigation Overhaul

**P4.1 — Rewrite `App.vue` to 3-pane architecture**  
Remove `max-w-7xl` constraint (line 3).  
Implement:
```html
<div class="flex h-screen overflow-hidden">
  <NavRail />                    <!-- 80px collapsed / 240px expanded -->
  <HubSidebar />                 <!-- 320px, hidden on mobile -->
  <Workspace />                  <!-- flex-1, main content -->
</div>
```
Add `collapsed` ref, toggle button in NavRail.  
Add `Cmd+K` keydown listener: `window.addEventListener('keydown', e => { if ((e.metaKey || e.ctrlKey) && e.key === 'k') showCommandBar.value = true })`

**P4.2 — Extract `NavRail.vue`**  
File: `resources/js/Components/NavRail.vue` (new)  
Width: `collapsed ? 'w-20' : 'w-60'` with `transition-all duration-300`  
Icon-only mode: show Lucide icons only when collapsed, labels appear when expanded.  
Bottom anchor: user profile avatar + settings icon.

**P4.3 — Create `CommandBar.vue`**  
File: `resources/js/Components/CommandBar.vue` (new)  
Trigger: `v-if="showCommandBar"` with `Escape` to close.  
Frosted glass overlay: `fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm`  
Fuzzy search across: `GET /api/v1/contacts?search=`, `GET /api/v1/memories?search=`, `GET /api/v1/agents?search=`  
Results: keyboard-navigable list with `ArrowUp`/`ArrowDown`/`Enter`.

**P4.4 — Fix RTL in `Navigation.vue`**  
Line 2: `border-r` → `border-e` (logical CSS via Tailwind)  
Line 35: `ml-3` → `ms-3`  
Line 13: `text-left` → `text-start`

**P4.5 — Fix RTL in `App.vue`**  
Line 11: `border-l` → `border-s`

**P4.6 — Fix `MobileFooter.vue`**  
Replace tab array (line 27–33) with: `Home (🏠)`, `Memory (🧠)`, `Contacts (👥)`, `Tasks (⚡)`, `Search (🔍)`  
Add `backdrop-filter: blur(20px)` and `background: rgba(22,27,34,0.85)` (heavy blur per spec).  
Change `height: 60px` → `height: 64px`.  
Add floating Action Orb button above the tab bar (absolute position, centered).  
Mount in `App.vue`.

**P4.7 — Fix Toast positioning for RTL**  
File: `Toast.vue`, lines 107–108  
Change `top: 1rem; right: 1rem` → `top: 1rem; inset-inline-end: 1rem`

---

### Phase 5 — View & Page Fixes

**P5.1 — `WorkflowBuilder.vue` — Add mobile breakpoints**  
Replace fixed 3-col layout with responsive layout:
- `>= 1024px`: current 3-column canvas layout
- `< 1024px`: linear "Step Sequencer" — vertical list of steps, no SVG canvas
Fix touch targets: all action buttons must be `min-h-[44px] min-w-[44px]`.  
Fix `id.startsWith('workflow-temp-')` logic — new workflows use `workflow-` prefix; change to `workflow-temp-` in `createNewWorkflow()` line 313.  
Fix syntax error: `WorkflowBuilder.vue:143` — `:key=step.id"` is missing opening quote — should be `:key="step.id"`.

**P5.2 — `DashboardView.vue` — Fix grid overflow**  
Line 99: `minmax(400px, 1fr)` → `minmax(min(400px, 100%), 1fr)`  
Replace all inline scoped CSS `.kpi-card` and `.panel` with `NxGlassCard` component.  
Replace hardcoded `#4ade80` color values (lines 93, 95 inline style) with `var(--color-status-success)`.

**P5.3 — `ChatInterface.vue` — Wire Echo streaming**  
Remove full HTTP-wait pattern. On send:
1. Add user message optimistically.
2. Add empty agent message with `isStreaming: true`.
3. Listen: `window.Echo.private('chat').listen('TokenStreamed', e => appendToken(e.token))`.
4. On `MessageCompleted` event: set `isStreaming = false`.
5. On error: call `useChat().revertLastMessage()`, preserve draft.  
Replace scoped CSS with Tailwind + `NxGlassCard` and `NxAiPulse` components.  
Replace `<div class="message.user">` background with `Action-Primary` (`#007AFF`) per spec.

**P5.4 — `MemoryView.vue` — Apply JetBrains Mono**  
Line 234: `<pre class="... text-xs ...">` → add `font-['JetBrains_Mono']` Tailwind class (available after P1.2).

**P5.5 — `SettingsView.vue` — Replace `alert()` calls**  
Lines 182 and 207: Replace `alert(...)` → `useNotificationStore().addToast({ type: 'error', message: ... })`.  
The Provider Manager section needs an async "Test Connection" button. Add to `AIModelsView.vue`.

**P5.6 — `AgentsView.vue` — Wire Echo**  
After `loadAgents()` in `onMounted`, add:
```js
window.Echo.private('agents').listen('AgentExecuted', (e) => {
  const idx = agents.value.findIndex(a => a.key === e.agent_id);
  if (idx !== -1) agents.value[idx] = normalizeAgent({ ...agents.value[idx], ...e }, idx);
});
```

**P5.7 — `TaskMonitor.vue` — Wire Echo + fix touch targets**  
Add Echo listener for `WorkflowStepCompleted` and `AgentExecuted`.  
Make `trace_id` clickable — launch sliding drawer to raw JSON (requires new `TraceDrawer.vue`).  
Increase all button padding to `min-h-[44px]`.

**P5.8 — Replace generic components with Nx variants sitewide**  
After Phase 2 completes:  
- All `<Card>` usages → `<NxGlassCard>`  
- All `<Button>` usages → `<NxActionButton>`  
- All `<div class="... backdrop-blur ...">` card patterns → `<NxGlassCard>`

---

### Compliance Score Summary

| Section | Issues Found | Critical | Moderate |
|---|---|---|---|
| Design Tokens & CSS | 13 | 8 | 5 |
| Component Library | 11 | 5 | 6 |
| Layout & Navigation | 9 | 3 | 6 |
| Real-Time & State | 18 | 13 | 5 |
| Mobile Compliance | 9 | 4 | 5 |
| **Total** | **60** | **33** | **27** |

**Current Compliance:** ~12% (foundational structure exists but almost all spec-mandated specifics are absent or incorrect)  
**Blocking Items to reach 100%:** Phases 1–5 above, in order.
