# Nexus Platform - UI/UX Implementation Plan
**Generated:** May 18, 2026  
**Estimated Duration:** 5-8 weeks

---

## PHASE 1: CRITICAL FIXES (Week 1-2)

### Task 1.1: Fix NexusView Hardcoded Metrics
**File:** `resources/js/Pages/NexusView.vue`  
**Severity:** 🔴 CRITICAL  
**Effort:** 1-2 hours  
**Steps:**
1. Add `<script setup>` imports for API calls
2. Create reactive `metrics` using `ref()`
3. Create `loadMetrics()` function with fetch to `/api/v1/dashboard/metrics`
4. Call `loadMetrics()` on component mount
5. Add error handling with try-catch
6. Add loading state while fetching

**Expected Outcome:** NexusView displays live system metrics instead of hardcoded values

---

### Task 1.2: Implement Conversations Hub Page
**New File:** `resources/js/Pages/ConversationsView.vue`  
**Severity:** 🔴 CRITICAL  
**Effort:** 4-6 hours  
**Requirements:**
- List conversations with pagination
- Filter by channel (WhatsApp, Email, SMS, etc.)
- Display latest message preview
- Show conversation metadata (last updated, participant count)
- Search conversations
- Click to view conversation detail
- Create new conversation button

**API Endpoints to Use:**
- GET `/api/v1/conversations` - List conversations
- POST `/api/v1/conversations` - Create conversation
- GET `/api/v1/conversations/{id}/messages` - Get messages

**Template Structure:**
```vue
<template>
  <div class="flex flex-col gap-4 p-4">
    <!-- Header -->
    <!-- Filters & Search -->
    <!-- Conversation List -->
    <!-- Detail Panel -->
  </div>
</template>
```

**Expected Outcome:** Users can browse, search, and manage conversations

---

### Task 1.3: Implement WorkflowBuilder - Phase 1 (Skeleton)
**File:** `resources/js/Pages/WorkflowBuilder.vue`  
**Severity:** 🔴 CRITICAL  
**Effort:** 8-12 hours  
**Requirements - Phase 1:**
- Basic canvas layout
- Step node creation UI
- Connection drawing (visual only, no validation yet)
- Properties sidebar
- Save/load workflow
- Load existing workflows

**Phase 1 Components:**
- Canvas area with zoom/pan controls
- Toolbar with step types (Start, Action, Decision, End)
- Properties panel
- Step connection editor
- Load/Save buttons

**Expected Outcome:** Basic workflow creation UI ready for testing

---

## PHASE 2: HIGH PRIORITY FEATURES (Week 3-5)

### Task 2.1: Fix ContactsView - Add Missing Fields
**File:** `resources/js/Pages/ContactsView.vue`  
**Severity:** 🟠 HIGH  
**Effort:** 2-3 hours  
**Steps:**
1. Update ContactList component to display:
   - `avatar_url` (with fallback placeholder)
   - `last_seen_at` (formatted date)
   - Contact type indicator
   - Status indicator
2. Add avatar image loading with fallback
3. Format timestamps to readable format
4. Add tooltip for full metadata

**Expected Outcome:** Contact cards show complete information from API

---

### Task 2.2: Implement ContactAnalytics Page
**File:** `resources/js/Pages/ContactAnalytics.vue`  
**Severity:** 🟠 HIGH  
**Effort:** 5-7 hours  
**Requirements:**
- Timeline of all interactions with a contact
- Sentiment analysis chart
- Message frequency chart
- Interaction type breakdown (email, WhatsApp, etc.)
- Date range filter
- Export data button

**API Endpoint:**
- GET `/api/v1/contacts/{id}/analytics`

**Components Needed:**
- Timeline component (interaction history)
- Chart component (sentiment, frequency)
- Date range picker

**Expected Outcome:** Users see comprehensive contact analytics dashboard

---

### Task 2.3: Implement Tasks CRUD Pages
**New Files:**
- `resources/js/Pages/TasksView.vue` - List view
- `resources/js/Pages/TaskDetail.vue` - Detail/edit view  
- `resources/js/Components/TaskForm.vue` - Reusable form

**Severity:** 🟠 HIGH  
**Effort:** 6-8 hours  
**Features:**
- List tasks with filtering (status, priority, assigned agent)
- Create new task form
- Edit task details
- Delete task
- Execute task (trigger agent)
- View execution history

**API Endpoints:**
- GET `/api/v1/tasks` - List tasks
- POST `/api/v1/tasks` - Create
- GET `/api/v1/tasks/{id}` - Get detail
- PUT `/api/v1/tasks/{id}` - Update
- DELETE `/api/v1/tasks/{id}` - Delete
- POST `/api/v1/tasks/{id}/execute` - Execute

**Expected Outcome:** Full task management interface

---

### Task 2.4: Fix LogsView - Add Hierarchical Display
**File:** `resources/js/Pages/LogsView.vue`  
**Severity:** 🟠 HIGH  
**Effort:** 3-4 hours  
**Steps:**
1. Detect parent-child relationships in logs (via parent_log_id field)
2. Group child logs under parent
3. Add expand/collapse controls
4. Style parent logs differently
5. Show child count badge

**Expected Outcome:** Logs organized hierarchically, easy to trace related events

---

### Task 2.5: Add Workflow Step Visualizer
**New Component:** `resources/js/Components/WorkflowDiagram.vue`  
**File Usage:** `WorkflowsView.vue`  
**Severity:** 🟠 HIGH  
**Effort:** 4-5 hours  
**Requirements:**
- Render workflow steps as visual nodes
- Show connections between steps
- Display step status (pending, running, completed, failed)
- Step details on hover
- Clickable nodes to view step details

**Expected Outcome:** Users can visualize workflow structure and progress

---

### Task 2.6: Add AIModelsView Execution Features
**File:** `resources/js/Pages/AIModelsView.vue`  
**Severity:** 🟠 HIGH  
**Effort:** 3-4 hours  
**Features:**
- Test model endpoint (input form, output display)
- Model execution with parameters
- Cost display for execution
- Fallback chain visualization
- Compare models performance

**API Endpoints:**
- POST `/api/v1/ai-models/{id}/test` - Test model
- POST `/api/v1/ai-models/execute` - Execute model

**Expected Outcome:** Users can test and execute AI models directly from UI

---

## PHASE 3: BUG FIXES & IMPROVEMENTS (Week 6-7)

### Task 3.1: Standardize Error Handling
**Files:** All Page components  
**Severity:** 🟡 MEDIUM  
**Effort:** 3-4 hours  
**Steps:**
1. Add error state refs to all pages
2. Wrap API calls in try-catch
3. Use error display component (Toast or Alert)
4. Add retry button for failed requests
5. Log errors to console in dev mode

**Pattern to Use:**
```javascript
const error = ref(null);

async function loadData() {
  try {
    const response = await fetch('/api/...');
    if (!response.ok) throw new Error('API error');
    data.value = await response.json();
    error.value = null;
  } catch (err) {
    error.value = err.message;
  }
}
```

**Expected Outcome:** Consistent error handling across application

---

### Task 3.2: Standardize Loading States
**Files:** All Page components  
**Severity:** 🟡 MEDIUM  
**Effort:** 2-3 hours  
**Steps:**
1. Replace mixed loading components with consistent pattern
2. Use SkeletonLoader for anticipated data
3. Use GlobalLoader for full-page loads
4. Add loading prop to child components
5. Show loading message during fetch

**Expected Outcome:** Consistent, predictable loading feedback

---

### Task 3.3: Fix Memory Vector Index Update
**File:** `resources/js/Pages/MemoryView.vue`  
**Severity:** 🟠 HIGH  
**Effort:** 2 hours  
**Steps:**
1. After POST `/api/v1/memories`, trigger index update
2. Call POST `/api/v1/memories/{id}/index`
3. Show notification when indexing completes
4. Refresh search results

**Expected Outcome:** New memories immediately searchable

---

### Task 3.4: Add Live Updates via WebSocket
**Files:** TaskMonitor.vue, WorkflowsView.vue, LogsView.vue  
**Severity:** 🟡 MEDIUM  
**Effort:** 4-5 hours  
**Steps:**
1. Create WebSocket service
2. Subscribe to live update channels
3. Update component state on events
4. Add connection status indicator
5. Auto-reconnect on disconnect

**Expected Outcome:** Real-time updates without manual refresh

---

### Task 3.5: Fix Date Filtering Validation
**File:** `resources/js/Pages/LogsView.vue`  
**Severity:** 🟡 MEDIUM  
**Effort:** 1 hour  
**Steps:**
1. Validate start_date < end_date
2. Show error if invalid
3. Disable search button if invalid
4. Format dates to ISO string for API

**Expected Outcome:** Date filters work correctly

---

### Task 3.6: Add Virtual Scrolling to ContactsView
**File:** `resources/js/Pages/ContactsView.vue`  
**Severity:** 🟡 MEDIUM  
**Effort:** 2-3 hours  
**Steps:**
1. Install Vue virtual scroll library (vue-virtual-scroller)
2. Wrap contact list in virtual scroll component
3. Set item height
4. Optimize performance

**Expected Outcome:** Smooth scrolling with 1000+ contacts

---

### Task 3.7: Add Search Debouncing
**File:** `resources/js/Pages/MemoryView.vue`  
**Severity:** 🟡 MEDIUM  
**Effort:** 1 hour  
**Steps:**
1. Add debounce utility or use lodash
2. Debounce search input (300ms)
3. Reduce API calls
4. Show "searching..." state

**Expected Outcome:** Reduced server load, better performance

---

## PHASE 4: POLISH & QA (Week 8)

### Task 4.1: Mobile Responsiveness Review
**Files:** All components  
**Effort:** 3-4 hours  
**Steps:**
1. Test all pages on mobile devices
2. Fix layout issues
3. Optimize touch interactions
4. Adjust font sizes for mobile
5. Test forms on mobile

**Expected Outcome:** All pages work well on mobile

---

### Task 4.2: Button & Component Consistency
**Files:** All components  
**Effort:** 2-3 hours  
**Steps:**
1. Replace all custom button styles with Button component
2. Standardize form input usage
3. Consistent card styling
4. Consistent modal usage

**Expected Outcome:** Uniform look and feel

---

### Task 4.3: Testing & QA
**Effort:** 4-5 hours  
**Steps:**
1. Manual test all fixed pages
2. Test error scenarios
3. Test API failures
4. Test loading states
5. Test mobile responsiveness
6. Document any issues

**Expected Outcome:** All features working, no obvious bugs

---

## Implementation Checklist

### Phase 1 - Critical
- [ ] 1.1: NexusView metrics fixed
- [ ] 1.2: Conversations Hub implemented
- [ ] 1.3: WorkflowBuilder basic UI

### Phase 2 - High Priority
- [ ] 2.1: ContactsView fields added
- [ ] 2.2: ContactAnalytics implemented
- [ ] 2.3: Tasks CRUD implemented
- [ ] 2.4: LogsView hierarchy added
- [ ] 2.5: Workflow visualizer added
- [ ] 2.6: AIModelsView execution features added

### Phase 3 - Bug Fixes
- [ ] 3.1: Error handling standardized
- [ ] 3.2: Loading states standardized
- [ ] 3.3: Memory index update fixed
- [ ] 3.4: Live updates via WebSocket
- [ ] 3.5: Date filtering validation
- [ ] 3.6: Virtual scrolling added
- [ ] 3.7: Search debouncing added

### Phase 4 - Polish
- [ ] 4.1: Mobile responsiveness verified
- [ ] 4.2: Component consistency reviewed
- [ ] 4.3: Full QA completed

---

## Success Criteria

- [ ] All stub pages fully implemented
- [ ] All API integrations working
- [ ] All documented features present
- [ ] Error handling consistent
- [ ] Mobile responsive
- [ ] No console errors
- [ ] Performance optimized (< 3s load time)
- [ ] All tests passing

