# 🚀 UPDATE BLUEPRINT: UP-004 — Layout & Navigation Overhaul (Phase 4)

## 1. Meta & Pre-flight Analysis

- **Features & Details:**
  - Rewrite `App.vue` to implement 3-pane architecture (Navigation Rail → Hub Sidebar → Workspace)
  - Create `NxNavRail.vue` — collapsible 80px/240px navigation rail (replaces `Navigation.vue`)
  - Create `HubSidebar.vue` — middle pane entity list with sticky search and sort filters
  - Create `NxCommandBar.vue` — Cmd+K universal command bar with fuzzy search
  - Fix `MobileFooter.vue` — mount in `App.vue`, fix tabs to Home/Memory/Contacts/Tasks/Search
  - Add breadcrumb trail to workspace header
  - Implement full RTL support (logical CSS properties: `border-e/s`, `ms-3` instead of `ml-3`)
  - Remove `max-w-7xl` width cap from `App.vue`

- **Project Context & Versions:**
  - Vue 3 Composition API with Vue Router
  - Tailwind CSS v3+ with design tokens from UP-001
  - Pinia stores available (UP-003)
  - Lucide icons registered globally (UP-001)

- **Regression Check:**
  - Complete `App.vue` rewrite — all existing route views must continue to work
  - `Navigation.vue` replaced by `NxNavRail.vue` — all hub navigation links must be preserved
  - `TabSystem.vue` deprecated — hub switching now via `NxNavRail` + `HubSidebar`
  - `max-w-7xl` removal may affect centered layouts — verify all pages render full-width
  - RTL changes use logical properties — no LTR breakage expected

---

## 2. Feature Specifications (Per Feature)

### Feature 1: NxNavRail.vue — Collapsible Navigation Rail (F-NAV-01)

- **Feature Name & ID:** NxNavRail — Collapsible Navigation Rail — F-NAV-01
- **Specs & Requirements:**
  - Replaces `Navigation.vue` (file: `resources/js/Components/Navigation.vue`)
  - Collapsible: `80px` (icon-only) ↔ `240px` (expanded with labels)
  - Collapse toggle: chevron button at bottom of rail
  - State persists in `localStorage` key `nexus-nav-rail-collapsed`
  - Top section: Hub icons (Nexus, Agents, Memory, Contacts, Workflows, Settings)
  - Bottom section: User profile avatar + settings gear
  - Mobile (`< 768px`): rail completely hidden; replaced by Bottom Tab Bar

- **UI/UX Specs:**
  - Width: `80px` collapsed, `240px` expanded; `transition: width 250ms cubic-bezier(0.4, 0, 0.2, 1)`
  - Active hub: left border accent `3px solid #007AFF`; background `rgba(0,122,255,0.05)`
  - Icon-only mode: labels `opacity: 0; width: 0; overflow: hidden`
  - Expanded mode: labels `opacity: 1; width: auto; padding-left: 12px`
  - Glass: `background: rgba(22,27,34,0.7); backdrop-filter: blur(12px); border-right: 1px solid rgba(255,255,255,0.1)`
  - RTL: `border-right` → `border-left`; `padding-left` → `padding-right`

- **Logic Workflow:**
  - `collapsed` state from `localStorage` on mount
  - Toggle writes to `localStorage` and updates reactive state
  - Hub click: `router.push({ name: hubName })` + close mobile drawer if open

- **Technical Workflow:**
  - File: `resources/js/Components/NxNavRail.vue` (new)
  - Props: None
  - Emits: `hub-change`
  - State: `collapsed: Boolean` (ref), `activeHub: String` (computed from route)
  - Icons: Lucide icons per hub (MessageSquare, Bot, Brain, Users, Workflow, Settings)

- **Backend Readiness:** N/A
- **Required Libraries:** `vue-router`, `lucide-vue-next`
- **Class/Component Names:** `NxNavRail.vue`, `Navigation.vue` (deprecated)
- **Functions to Modify/Create:**
  - `toggleCollapsed()` — toggle rail width
  - `navigateToHub(hubName)` — router push

---

### Feature 2: HubSidebar.vue — Entity List Pane (F-NAV-02)

- **Feature Name & ID:** HubSidebar — Entity List Middle Pane — F-NAV-02
- **Specs & Requirements:**
  - Middle pane (`320px` fixed width) between Nav Rail and Workspace
  - Shows entity list for active hub (contacts, agents, memories, workflows, tasks)
  - Sticky search input at top with sort/filter dropdowns
  - Entity items: clickable, show primary identifier + status pill
  - Empty state: "No items found" with create CTA

- **UI/UX Specs:**
  - Width: `320px`; `min-width: 280px; max-width: 400px` (resizable in future)
  - Background: `rgba(22,27,34,0.7); backdrop-filter: blur(12px); border-right: 1px solid rgba(255,255,255,0.1)`
  - Search: `background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px`
  - RTL: `border-right` → `border-left`; `padding-left` → `padding-right`

- **Logic Workflow:**
  - Reads active hub from `route.name`
  - Fetches entities from relevant Pinia store (`useContacts`, `useWorkflows`, etc.)
  - Search filters entities client-side; sort toggles between name/date/status

- **Technical Workflow:**
  - File: `resources/js/Components/HubSidebar.vue` (new)
  - Props: `hub: String` (from route)
  - Emits: `entity-select`
  - Computed: `entities` (from active store), `filteredEntities`
  - Slots: `#entity-item` for custom entity rendering per hub

- **Backend Readiness:** N/A (uses existing API via Pinia stores)
- **Required Libraries:** `pinia`, `vue-router`
- **Class/Component Names:** `HubSidebar.vue`
- **Functions to Modify/Create:**
  - `fetchEntities()` — load from active store
  - `filterEntities(query)` — client-side filter
  - `sortEntities(field)` — sort toggle

---

### Feature 3: NxCommandBar.vue — Cmd+K Universal Search (F-NAV-03)

- **Feature Name & ID:** NxCommandBar — Universal Command Bar — F-NAV-03
- **Specs & Requirements:**
  - Trigger: `Cmd+K` (Mac) or `Ctrl+K` (Win), or clicking search icon in Hub Sidebar
  - Frosted glass overlay centered on screen
  - Fuzzy search across: `contacts.canonical_name`, `memories.snippet`, `agents.name`, `workflows.name`, system routes
  - Results grouped by type with distinct icons
  - Keyboard navigation: `↑`/`↓` navigate, `Enter` selects, `Escape` closes
  - Recent searches shown before typing (from `localStorage`)

- **UI/UX Specs:**
  - Overlay: `position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 100`
  - Panel: `width: 640px; max-width: 90vw; background: rgba(22,27,34,0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px`
  - Input: `width: 100%; background: transparent; border: none; font-size: 16px; color: white`
  - Result item: `padding: 10px 16px; display: flex; align-items: center; gap: 12px`
  - Animation: backdrop `fade-in 150ms`; panel `scale 0.95→1.0 in 200ms`

- **Logic Workflow:**
  - `onMounted`: add `keydown` listener for `Cmd+K`/`Ctrl+K`
  - `onUnmounted`: remove listener
  - Search input: debounced `300ms` API call to `GET /api/v1/search?q={query}`
  - Keyboard nav: track `selectedIndex` in results array

- **Technical Workflow:**
  - File: `resources/js/Components/NxCommandBar.vue` (new)
  - Props: None (global)
  - Emits: `select`
  - State: `open: Boolean`, `query: String`, `results: Array`, `selectedIndex: Number`, `recentSearches: Array`
  - API: `axios.get('/api/v1/search', { params: { q: query, types: 'contacts,memories,agents,workflows' } })`

- **Backend Readiness:** `GET /api/v1/search?q={query}&types=...` endpoint
- **Required Libraries:** `axios`, `pinia`
- **Class/Component Names:** `NxCommandBar.vue`
- **Functions to Modify/Create:**
  - `open()` / `close()` — toggle visibility
  - `handleKeydown(e)` — keyboard navigation
  - `search(query)` — debounced API call
  - `selectResult(result)` — navigate to result

---

### Feature 4: App.vue — 3-Pane Layout Rewrite (F-APP-01)

- **Feature Name & ID:** App.vue — 3-Pane Layout Rewrite — F-APP-01
- **Specs & Requirements:**
  - Replace current 2-pane layout with 3-pane: `NxNavRail` (80/240px) + `HubSidebar` (320px) + `Workspace` (flex)
  - Remove `max-w-7xl` width cap — workspace fills remaining space
  - Mount `NxStatusBar` (A01) below workspace header
  - Add breadcrumb trail in workspace header
  - Mount `MobileFooter.vue` for mobile (`< 768px`)
  - Add `NxTopBar` (L07) at very top of viewport
  - Add skip-to-content link for accessibility (K01)

- **UI/UX Specs:**
  - Layout: `display: flex; height: 100vh; overflow: hidden`
  - Nav Rail: `width: v-bind('navRailWidth')` (80 or 240px); `flex-shrink: 0`
  - Hub Sidebar: `width: 320px; flex-shrink: 0; border-right: 1px solid rgba(255,255,255,0.1)`
  - Workspace: `flex: 1; display: flex; flex-direction: column; overflow: hidden`
  - Workspace header: `height: 56px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid rgba(255,255,255,0.1)`
  - RTL: Nav Rail on right; Hub Sidebar on left; logical border properties

- **Logic Workflow:**
  - `navRailCollapsed` from `localStorage` (via `useSystem()`)
  - Breadcrumb computed from `route.matched` array
  - `NxStatusBar` conditionally rendered (hidden on mobile or collapsed)

- **Technical Workflow:**
  - File: `resources/js/App.vue` (rewrite)
  - Template: `<NxNavRail /> <HubSidebar /> <main id="main-content" class="workspace"> <NxTopBar /> <header class="workspace-header"> <breadcrumbs /> <action-icons /> </header> <NxStatusBar /> <router-view v-slot="{ Component }"> <transition name="page-slide"> <component :is="Component" /> </transition> </router-view> </main> <MobileFooter /> <NxCommandBar />`
  - Script: Import all new components; setup Pinia; setup Echo

- **Backend Readiness:** N/A
- **Required Libraries:** `vue-router`, `pinia`
- **Class/Component Names:** `App.vue`, `NxNavRail.vue`, `HubSidebar.vue`, `NxStatusBar.vue`, `NxTopBar.vue`, `NxCommandBar.vue`, `MobileFooter.vue`
- **Functions to Modify/Create:**
  - `navRailWidth` computed (80 or 240)
  - `breadcrumbItems` computed from route
  - `toggleNavRail()` — dispatch to `NxNavRail`

---

### Feature 5: MobileFooter.vue — Bottom Tab Bar (F-NAV-04)

- **Feature Name & ID:** MobileFooter — Bottom Tab Bar — F-NAV-04
- **Specs & Requirements:**
  - Fix existing `MobileFooter.vue` (file: `resources/js/Components/MobileFooter.vue`)
  - Tabs: Home, Memory, Contacts, Tasks, Search (5 tabs)
  - `64px` height; heavily blurred glass background
  - Floating Hédra orb (NxVoiceOrb, D02) above tab bar center
  - Mount in `App.vue` — currently never rendered
  - Only visible at `< 768px`; hidden on desktop

- **UI/UX Specs:**
  - `position: fixed; bottom: 0; left: 0; right: 0; height: 64px; z-index: 50`
  - Background: `background: rgba(22,27,34,0.85); backdrop-filter: blur(20px); border-top: 1px solid rgba(255,255,255,0.1)`
  - Tab: `flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px`
  - Active tab: `color: #007AFF`; inactive: `color: rgba(255,255,255,0.5)`
  - Touch target: `min-height: 44px` per mobile compliance rule

- **Logic Workflow:**
  - Active tab from `route.name`
  - Tab click: `router.push({ name: tabRoute })`
  - `NxVoiceOrb` click: toggle voice dictation mode

- **Technical Workflow:**
  - File: `resources/js/Components/MobileFooter.vue` (modify)
  - Props: None
  - Emits: None
  - Computed: `activeTab` from route; `tabRoutes` mapping

- **Backend Readiness:** N/A
- **Required Libraries:** `vue-router`, `lucide-vue-next`
- **Class/Component Names:** `MobileFooter.vue`
- **Functions to Modify/Create:**
  - Fix tab routes to match actual route names: `nexus`, `memory`, `contacts`, `tasks`, `search`
  - Add `NxVoiceOrb` component above tab bar

---

### Feature 6: RTL Support — Logical CSS Properties (F-RTL-01)

- **Feature Name & ID:** RTL Support — Logical CSS Properties — F-RTL-01
- **Specs & Requirements:**
  - Replace all hardcoded directional CSS with logical properties
  - `border-r` → `border-e` (end border); `border-l` → `border-s` (start border)
  - `ml-3` → `ms-3` (margin-start); `mr-3` → `me-3` (margin-end)
  - `pl-4` → `ps-4` (padding-start); `pr-4` → `pe-4` (padding-end)
  - `text-left` → `text-start`; `text-right` → `text-end`
  - Apply to: `App.vue`, `NxNavRail.vue`, `HubSidebar.vue`, `NxStatusBar.vue`, all existing components

- **UI/UX Specs:**
  - When `dir="rtl"` on `<html>`, all layouts mirror automatically
  - No visual change in LTR mode
  - Test with Egyptian Arabic locale (`ar_EG`)

- **Logic Workflow:**
  - No logic change — purely CSS property substitution
  - Tailwind v3+ supports logical properties natively

- **Technical Workflow:**
  - Grep all `.vue` files for hardcoded directional classes
  - Replace with logical equivalents
  - Files: `App.vue`, `Navigation.vue`, `MobileFooter.vue`, `Button.vue`, `Card.vue`, `Toast.vue`, all Pages/*

- **Backend Readiness:** N/A
- **Required Libraries:** N/A
- **Class/Component Names:** N/A
- **Functions to Modify/Create:** None

---

### Feature 7: Breadcrumb Trail — Workspace Header (F-NAV-05)

- **Feature Name & ID:** Breadcrumb Trail — Workspace Header — F-NAV-05
- **Specs & Requirements:**
  - Add breadcrumb to workspace header in `App.vue`
  - Format: `Hub / Entity / Detail` (e.g., `Contacts / Ahmed Ali / Profile`)
  - Each segment is clickable (navigates to that level)
  - Animation: new crumb slides in from `translateX(8px) opacity(0)` in `150ms`; old crumbs slide out left

- **UI/UX Specs:**
  - `display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--color-text-secondary)`
  - Separator: `/` character or Lucide `ChevronRight` icon
  - Active segment: `color: var(--color-text-primary); font-weight: 500`
  - Clickable segments: `cursor: pointer; hover: underline`

- **Logic Workflow:**
  - Computed from `route.matched` array
  - Each matched route has `meta.breadcrumb` label
  - Click navigates to that route level

- **Technical Workflow:**
  - File: `resources/js/Components/Breadcrumbs.vue` (modify existing) or inline in `App.vue`
  - Props: None (reads from route)
  - Emits: None
  - Computed: `items` from `useRoute().matched`

- **Backend Readiness:** N/A
- **Required Libraries:** `vue-router`
- **Class/Component Names:** `Breadcrumbs.vue`
- **Functions to Modify/Create:**
  - `breadcrumbItems` computed property
  - `navigateTo(item)` — router push

---

## 3. Testing Strategy

### Automated Testing

- **Unit Tests (Vitest):**
  - `NxNavRail.spec.ts`: Test collapse/expand state; test localStorage persistence; test hub navigation
  - `HubSidebar.spec.ts`: Test entity list rendering; test search filter; test sort toggle
  - `NxCommandBar.spec.ts`: Test open/close; test keyboard navigation; test result selection; test fuzzy search
  - `App.spec.ts`: Test 3-pane layout structure; test breadcrumb computation; test mobile footer visibility

- **Visual Regression:**
  - Capture `App.vue` at desktop (1440px) and mobile (375px) breakpoints
  - Capture `NxNavRail` in both collapsed and expanded states
  - Capture `NxCommandBar` open state

### Manual Testing Steps

1. **Desktop Layout Test (1440px):**
   - Verify 3-pane layout: Nav Rail (240px) + Hub Sidebar (320px) + Workspace (flex)
   - Click collapse chevron → verify Nav Rail shrinks to 80px, labels fade out
   - Click hub icon → verify Hub Sidebar updates entity list
   - Press `Cmd+K` → verify Command Bar opens with fuzzy search
   - Type query → verify results appear grouped by type
   - Navigate with `↑`/`↓` + `Enter` → verify navigation works

2. **Mobile Layout Test (375px):**
   - Verify Nav Rail and Hub Sidebar are hidden
   - Verify `MobileFooter` renders at bottom with 5 tabs
   - Tap each tab → verify correct hub loads
   - Verify `NxVoiceOrb` floats above tab bar center
   - Swipe right from left edge → verify swipe-back gesture (J01, Phase 4)

3. **RTL Test:**
   - Set `document.documentElement.dir = 'rtl'`
   - Verify Nav Rail moves to right side
   - Verify Hub Sidebar moves to left side
   - Verify all text aligns right
   - Verify breadcrumb separators mirror

4. **Breadcrumb Test:**
   - Navigate to Contacts → select contact → verify breadcrumb shows `Contacts / [Name]`
   - Click `Contacts` in breadcrumb → verify returns to contact list
   - Verify animation on route change

5. **Accessibility Test:**
   - Tab through Nav Rail → verify all hubs receive focus
   - Tab through Command Bar → verify keyboard navigation works
   - Verify skip-to-content link appears on `Tab` from page top
   - Verify `:focus-visible` ring is Nexus Blue `#007AFF`

6. **Regression Test:**
   - Navigate to all existing hubs → verify all pages load correctly
   - Verify `max-w-7xl` removal doesn't break any page layouts
   - Verify `TabSystem.vue` deprecation doesn't break hub switching
