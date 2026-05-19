# UP-004_Task-04: App.vue — 3-Pane Layout Rewrite

## Task Overview
Rewrite `App.vue` to implement 3-pane architecture (Navigation Rail → Hub Sidebar → Workspace).

## Feature Specification
- **Feature ID:** F-APP-01
- **File:** `resources/js/App.vue` (rewrite)

## Requirements
1. Replace current 2-pane layout with 3-pane: NxNavRail (80/240px) + HubSidebar (320px) + Workspace (flex)
2. Remove max-w-7xl width cap — workspace fills remaining space
3. Mount NxStatusBar (A01) below workspace header
4. Add breadcrumb trail in workspace header
5. Mount MobileFooter.vue for mobile (< 768px)
6. Add NxTopBar (L07) at very top of viewport
7. Add skip-to-content link for accessibility (K01)

## Implementation Details
- Layout: display: flex; height: 100vh; overflow: hidden
- Nav Rail: width: v-bind('navRailWidth') (80 or 240px); flex-shrink: 0
- Hub Sidebar: width: 320px; flex-shrink: 0; border-right: 1px solid rgba(255,255,255,0.1)
- Workspace: flex: 1; display: flex; flex-direction: column; overflow: hidden
- Workspace header: height: 56px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid rgba(255,255,255,0.1)

## Verification
- `npm run build` passes
- 3-pane layout renders correctly
- Breadcrumb computed from route.matched
- Mobile footer visible on < 768px
