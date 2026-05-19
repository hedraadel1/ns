# Nexus Platform - AI Agent Instructions

This file provides guidance for AI coding agents working on the Nexus platform, a cognitive digital twin platform built on Laravel 11 and Vue 3.

## Quick Start

### Essential Commands

```bash
# Install & setup
composer install && npm install && php artisan key:generate

# Start development (all services: PHP, queue, Vite, logs)
composer run dev

# Run tests (PHPUnit)
php artisan test

# Listen to queue jobs
php artisan queue:listen --tries=1

# Run migrations
php artisan migrate --seed
```

### Key Directories

- **Backend Logic**: `app/Services/`, `app/Http/Controllers/`, `app/Jobs/`
- **Data Models**: `app/Models/`
- **Frontend**: `resources/js/`, `resources/views/`
- **Configuration**: `config/`, `database/`
- **Tests**: `tests/Unit/`, `tests/Feature/`
- **Documentation**: `Docs/`, `DETAILED_EXECUTION_PLAN/`

## Project Architecture

### Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend** | Laravel 11, PHP 8.2 | API & business logic |
| **Database** | MySQL 8.0+ | Primary persistence |
| **Cache/Queue** | Redis 7.0+ | Caching, sessions, job queues |
| **Frontend** | Vue 3, Vite 6 | Reactive UI components |
| **Styling** | Tailwind CSS 3 | Utility-first CSS |
| **Auth** | Laravel Sanctum | Token-based API auth |
| **Real-time** | Laravel Reverb | WebSocket messaging |
| **AI Providers** | Gemini, OpenAI, Anthropic | Language models |
| **Vector Store** | Pinecone | Semantic memory storage |
| **Testing** | PHPUnit 11 | Unit & feature tests |

### 8 Core Hubs (Modules)

Each hub is a self-contained module with models, services, and events. They communicate via events and APIs.

| Hub | Purpose | Key Models | Key Services |
|-----|---------|-----------|--------------|
| **Agents Hub** | AI agent management | Agent, AgentSkill, AgentTask, AgentTool | AgentLifecycleService, AgentRegistry |
| **Workflows Hub** | Multi-step orchestration | Workflow, TaskStep | WorkflowExecutor, WorkflowValidationService |
| **Contacts Hub** | Contact intelligence | Contact, Conversation, Message, ContactNote, ContactTag | ContactHubService, RelationshipGraphService |
| **Memory Hub** | Five-layer memory system | Memory, StructuredMemory, GraphNode | MemoryManagementEngine, Memory*Services |
| **AI Models Hub** | AI provider orchestration | AIModel, ApiKey | AIOrchestrationEngine, ModelRouter |
| **Settings Hub** | Configuration management | Setting | SettingCacheService |
| **Logs Hub** | Audit & monitoring | Log, SystemLog | LogService, AlertService |
| **Nexus Hub** | Main dashboard | - | Dashboard aggregation |

See [ARCHITECTURE_DETAILS.md](Docs/ARCHITECTURE_DETAILS.md) for detailed architecture documentation.

### Layered Architecture

Nexus follows strict 5-layer clean architecture:

```
Presentation Layer (Vue 3 UI)
         ↓ (depends on)
API Layer (Controllers, Middleware, Requests)
         ↓ (depends on)
Service Layer (Business logic, orchestration)
         ↓ (depends on)
Domain Layer (Models, Events, Policies)
         ↓ (depends on)
Data Layer (Repositories, Database, Cache)
```

**Critical Rules**:
- ✅ Upper layers depend on lower layers
- ❌ Lower layers CANNOT depend on upper layers
- ❌ NO circular dependencies allowed
- ✅ Horizontal dependencies within same layer OK

## Development Conventions

### Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| **Database Tables** | Plural, snake_case | `contact_notes`, `conversations` |
| **Models** | Singular, PascalCase | `Contact`, `ConversationSession` |
| **Controllers** | `{Model}Controller` | `ContactController` |
| **Services** | `{Domain}Service` | `ContactHubService` |
| **Jobs** | `{Action}{Subject}Job` | `ExecuteAiModelJob`, `ExtractMemoryJob` |
| **Events** | `{Action}{Subject}` | `MessageSent`, `WorkflowCompleted` |
| **Migrations** | `{timestamp}_{action}_{table}` | `2024_03_10_create_contacts_table` |
| **Routes** | RESTful, kebab-case | `POST /api/contacts` |

### File Organization

```
app/
├── Agents/                  # 5 agent type implementations
├── Events/                  # Domain events (always use ShouldBroadcast for real-time)
├── Http/
│   ├── Controllers/         # API endpoints (thin, delegate to services)
│   ├── Middleware/          # Request/response middleware
│   └── Requests/            # Form request validation
├── Jobs/                    # Async jobs (queue:listen processes these)
├── Listeners/               # Event handlers
├── Models/                  # Eloquent models (1 per domain concept)
├── Policies/                # Authorization logic
├── Providers/               # Service container registration
├── Repositories/            # Data access abstraction
├── Services/
│   ├── Engines/             # Core processors (AI, Memory, etc.)
│   ├── Memory/              # Memory type implementations
│   ├── Routing/             # Message/task/tone routers
│   ├── Pipelines/           # Sequential operation chains
│   ├── AI/                  # AI provider integration
│   ├── AiModelsHub/         # AI model management
│   ├── Integrations/        # External APIs (Mem0, MCP, etc.)
│   └── Other services       # Registry, Executor, Lifecycle, etc.
└── Hubs/                    # Hub coordination classes
```

### Code Style & Patterns

**Service Layer**: Business logic lives in services, not models
```php
// ✅ CORRECT: Logic in service
class ContactHubService {
    public function enrichContact(Contact $contact) { /* logic */ }
}

// ❌ WRONG: Logic in model
class Contact extends Model {
    public function enrich() { /* logic */ }
}
```

**Controllers**: Thin, delegate to services
```php
// ✅ CORRECT: Minimal controller
public function store(StoreContactRequest $request) {
    $contact = $this->contactService->create($request->validated());
    return new ContactResource($contact);
}

// ❌ WRONG: Business logic in controller
public function store(StoreContactRequest $request) {
    $contact = Contact::create($request->validated());
    // ... complex business logic here
}
```

**Events & Broadcasting**: All real-time events must use `ShouldBroadcastNow`
```php
// ✅ CORRECT: Broadcasts immediately with sanitized data
class MessageSent implements ShouldBroadcastNow {
    public function broadcastOn(): Channel {
        return new PrivateChannel("conversation.{$this->conversationId}");
    }
    public function broadcastWith(): array {
        return ['id' => $this->messageId, 'content' => $this->content];
    }
}
```

**Jobs**: For long operations, always use async jobs
```php
// ✅ CORRECT: Long operation as background job
dispatch(new ExecuteAiModelJob($model, $input));

// ❌ WRONG: Long operation blocking request
$result = $this->aiService->execute($model, $input);
```

## Common Development Tasks

### Adding a New Hub Feature

1. **Create Model** in `app/Models/`
   - Use standard Eloquent with relationships
   - Document relationships in comments

2. **Create Service** in `app/Services/{HubName}/`
   - Implement business logic
   - Use dependency injection in constructor
   - Delegate to repositories for data access

3. **Create Controller** in `app/Http/Controllers/{HubName}/`
   - Keep controller thin
   - Use form request validation
   - Return resource classes

4. **Define Route** in `routes/api.php`
   - Follow RESTful conventions
   - Group by hub: `Route::prefix('contacts')->group(...)`

5. **Add Event** (if real-time needed) in `app/Events/`
   - Implement `ShouldBroadcastNow`
   - Use private channels for security
   - Sanitize data in `broadcastWith()`

6. **Add Test** in `tests/Feature/` or `tests/Unit/`
   - Test service logic in unit tests
   - Test API endpoints in feature tests
   - Use database transactions for cleanup

See [Docs/ARCHITECTURE_DETAILS.md](Docs/ARCHITECTURE_DETAILS.md) for detailed patterns.

### Making Database Changes

1. **Create migration**: `php artisan make:migration {description}`
2. **Update model**: Add relationships and fillable properties
3. **Add repository method**: For complex queries
4. **Update service**: To use new data
5. **Create test**: For new functionality
6. **Run**: `php artisan migrate:refresh --seed` (dev only)

### Adding an AI Provider Integration

1. Check `app/Services/AiModelsHub/` for existing patterns
2. Create provider service implementing `AIProviderContract`
3. Register in `app/Providers/AiServiceProvider`
4. Add configuration to `config/services.php`
5. Create tests in `tests/Feature/AiModelsHub/`

See [Docs/NEW_FEATURES_SPEC.md](Docs/NEW_FEATURES_SPEC.md) for integration examples.

## Active Development Areas

### Current Branch: `NUX2`

### 🔴 Critical Issues & Gaps

#### AI Models Hub (HIGH PRIORITY)
**Status**: ❌ NOT IMPLEMENTED - Major gaps despite claims
- Missing database tables: `ai_providers`, `ai_api_keys`, `intent_routing`
- Existing `ai_models` table schema mismatches specification
- Missing API endpoints for provider management and intent-based routing
- Missing services: `DynamicProviderRegistry`, `IntentRoutingEngine`, `PayloadAdapterFactory`
- Current AI services hardcoded instead of dynamic provider-driven
- No encrypted API key storage (keys in plaintext env)
- No SSRF protection, circuit breakers, or fallback chains
- See [AiModelHubReport.md](AiModelHubReport.md) for detailed audit

**Action Items**:
1. Create missing migrations for `ai_providers`, `ai_api_keys`, `intent_routing`
2. Implement DynamicProviderRegistry, IntentRoutingEngine, PayloadAdapterFactory services
3. Create API endpoints: `/api/v1/ai/providers`, `/api/v1/ai/providers/{id}/sync-models`, `/api/v1/ai/intents/routing`, `/api/v1/ai/request`
4. Implement AES-256 key encryption and SSRF protection
5. Update workflows/agents to use intent-based requests instead of direct provider calls

#### LogsHub (COMPLETED - Minor Fixes Remain)
**Status**: ⚠️ PARTIALLY FIXED
- ✅ Schema mismatch fixed (now uses `channel` instead of `category`)
- ✅ Polymorphic relations implemented
- ✅ Universal logging integrated across all 7 hubs
- ❌ Real-time WebSocket streaming still missing (uses polling instead)
- ❌ Alert notification dispatch not implemented
- ❌ Log retention policies not implemented
- ❌ Audit trail features incomplete

See [Finished_UP-001_LogsHub_Fix.md](AI_Workflow/Updates_Docs/Finished_UP-001_LogsHub_Fix.md) and [CODE_REVIEW_GAP_ANALYSIS_REPORT.md](CODE_REVIEW_GAP_ANALYSIS_REPORT.md)

### 🟡 In Progress

#### UP-003: AsyncEngine Refactoring (PHASE 2 OF 3)
**Status**: 60% complete
- ✅ Task 1: Horizon Configuration & Base Job Infrastructure (Done)
- ✅ Task 2: Core Job Classes Implementation (Done)
- ✅ Task 3: Event Broadcasting Infrastructure (Done)
- 🟡 Task 4: Event Refactoring & Broadcasting Implementation (In Progress)
- ⏳ Task 5: Laravel Echo & Reverb Frontend Integration (Pending)
- ⏳ Task 6: Real-time Components Development (Pending)
- ⏳ Task 7: Controller Integration & Job Dispatching (Pending)
- ⏳ Task 8: DLQ Management & Resilience Monitoring (Pending)
- ⏳ Task 9: Testing, Validation & Documentation (Pending)

**Focus Areas**:
- Event broadcasting with secure payload sanitization
- Background job system with queue separation
- Real-time WebSocket streaming for token generation
- Dead-Letter Queue management

See [AI_Workflow/Updates_Docs/UP-003_AsyncEngine_Refactoring.md](AI_Workflow/Updates_Docs/UP-003_AsyncEngine_Refactoring.md) for detailed task breakdown

### 🟢 Recently Completed

#### UP-002: AI Model Hub Implementation (Documentation Only)
- Specification completed; implementation pending
- See [Finished_UP-002_AiModelHub_Implementation.md](AI_Workflow/Updates_Docs/Finished_UP-002_AiModelHub_Implementation.md)

#### UP-001: Logs Hub Fix
- ✅ Database schema corrected
- ✅ Universal logging integrated
- Remaining: WebSocket streaming, alert dispatch

### 📋 Known Bugs & Issues

From [CODE_REVIEW_GAP_ANALYSIS_REPORT.md](CODE_REVIEW_GAP_ANALYSIS_REPORT.md) and [updatespoints.md](updatespoints.md):

**Backend**:
- Memory indexing race condition: New memories not indexed to Pinecone immediately
- Agent status transitions insufficiently validated (can reach inconsistent states)
- Pinecone integration uses stubs instead of real calls
- Contact avatar_url and last_seen_at fields sometimes missing in API responses

**Frontend**:
- ContactsView: No virtual scrolling (performance issues with large lists)
- LogsView: Date range validation missing (allows start > end)
- TaskMonitor: No live updates (needs WebSocket/polling implementation)
- Workflow builder: Phase-1 skeleton only; drag-drop validation incomplete
- Contact analytics: Placeholder only; charts not implemented
- Agent team UI: Incomplete

**Updates Tracking**:
See [updatespoints.md](updatespoints.md) for comprehensive gap analysis and [Final Master Specification Report.md](Final%20Master%20Specification%Report.md) for complete UI/UX specification

### 📚 Recent Updates & Context

**UI/UX Polish** (UP-010 - Final Phase):
- Page transitions and motion design
- Loading states standardization
- Hover effects and micro-interactions
- Accessibility enhancements
- Mobile/touch optimization
- See [_AI_Workflow/Updates_Docs/UP-010_Final_Polish_Deployment.md](_AI_Workflow/Updates_Docs/UP-010_Final_Polish_Deployment.md)

### Documentation

- [ARCHITECTURE_DETAILS.md](Docs/ARCHITECTURE_DETAILS.md) - Full architecture overview
- [DB_SCHEMA.md](Docs/DB_SCHEMA.md) - Database tables & relationships
- [NEW_FEATURES_SPEC.md](Docs/NEW_FEATURES_SPEC.md) - Feature specifications
- [DEPLOYMENT_GUIDE.md](Docs/DEPLOYMENT_GUIDE.md) - Production deployment
- [USER_MANUAL.md](Docs/USER_MANUAL.md) - End-user documentation
- [Final Master Specification Report.md](Final%20Master%20Specification%Report.md) - Complete UI/UX spec with design system
- [DETAILED_EXECUTION_PLAN/](DETAILED_EXECUTION_PLAN/) - Phase-by-phase development plan

## Frontend/UI Implementation Status

### Design System (Glassmorphism 2.0)
The UI uses a sophisticated glassmorphism design language reflecting the "Digital Mirror" philosophy:
- **Blur Effect**: `backdrop-filter: blur(12px)`
- **Borders**: `1px solid rgba(255, 255, 255, 0.1)` for frosted-edge effect
- **Color Palette**:
  - Surface-High: #0B0E14 (Deep Space)
  - Surface-Mid: #161B22 (Midnight Glass)
  - Action-Primary: #007AFF (Nexus Blue)
  - AI-Core: #6366F1 (Hédra Purple)
  - Status colors: Emerald (success), Amber (warning), Crimson (error)
- **Typography**: Inter (system UI), JetBrains Mono (data/metadata)

### Core UI Components (Implemented/In Progress)
- ✅ `NxAiPulse.vue` - AI state indicator with breathing/thinking animations
- ✅ `NxGlassCard.vue` - Standard container with elevation and hover effects
- ✅ `NxTokenMeter.vue` - Context window visualization
- ✅ `NxLiveLoader.vue` - Job progress indicator with log feed
- ✅ `NxActionButton.vue` - Standardized button with optimistic UI
- 🟡 `LiveChatStream.vue` - Token-by-token streaming (pending Echo integration)
- 🟡 `GlobalJobMonitor.vue` - Queue monitoring dashboard (pending WebSocket)

### Frontend Implementation Phases
- ✅ **UP-001**: Design System Foundation
- ✅ **UP-002**: Core Nx Components
- ✅ **UP-003**: Pinia Stores & Real-Time State Management
- ✅ **UP-004**: Layout & Navigation Overhaul (3-pane desktop, stack-and-slide mobile)
- ✅ **UP-005**: View Fixes & Optimistic UI
- ✅ **UP-006**: Contact Profile 3D Experience
- ✅ **UP-007**: Advanced Features (Modals, Charts)
- ✅ **UP-008**: Mobile & Touch Enhancements
- ✅ **UP-009**: Accessibility Polish
- 🟡 **UP-010**: Final Polish & Deployment (Page transitions, loading states, hover effects, motion design)

### Missing Frontend Features
- ContactAnalytics: Placeholder only; charts not implemented
- Workflow builder: Phase-1 skeleton; drag-drop validation incomplete
- Memory advanced UI: Vector search, graph visualization incomplete
- Logs hierarchical UI: Backend supports; UI shows flat list only
- Agent team UI: Composition and collaboration surfaces incomplete
- Webhook management: Create/manage/test endpoints missing
- PWA offline support: Placeholders exist; runtime validation incomplete

### Frontend Stack & Tools
- **Framework**: Vue 3 with Composition API + `<script setup>`
- **State**: Pinia (stores in `resources/js/stores/`)
- **Build**: Vite 6.0+
- **Styling**: Tailwind CSS 3.4+
- **Real-time**: Laravel Echo + Reverb WebSockets (integration in UP-003 pending)
- **Routing**: Vue Router with lazy-loaded route components
- **HTTP**: axios for REST API calls

## Important Constraints & Gotchas

### ⚠️ Critical Rules

1. **Queue First**: All operations > 2 seconds must be async jobs
   - Don't make users wait for AI model calls, vectorization, or external APIs
   - Dispatch jobs, emit events, update UI via WebSocket

2. **Private Channels Only**: All broadcasted data uses private channels
   - Don't broadcast sensitive data (API keys, full models, private content)
   - Sanitize in `broadcastWith()` method
   - Test channel authorization in tests

3. **Model Relationships**: Load eagerly to avoid N+1 queries
   - Use `with()` in services before returning models
   - Use query optimization in repositories

4. **Environment Variables**: All external API keys in `.env`
   - Never hardcode credentials
   - Never commit `.env` file
   - Document required vars in `README.md`

5. **Test Database**: Uses SQLite in-memory
   - Set `DB_CONNECTION=sqlite` in `phpunit.xml`
   - Migrations run before each test suite
   - No real data or API calls in tests (use mocks)

6. **AI Models Hub (Current Gap)**: DO NOT bypass the hub
   - All AI requests must go through `/api/v1/ai/request` endpoint (when implemented)
   - Never call provider services directly (OpenAI, Gemini, etc.)
   - Always use intent-based routing (future implementation)
   - Never store API keys outside encrypted storage (when implemented)

### 🔴 Anti-Patterns to Avoid

```php
// ❌ Blocking AI calls in controller
public function generate(Request $request) {
    $result = $this->aiService->complete($request->prompt); // Blocks!
    return $result;
}

// ✅ Dispatch async job
public function generate(Request $request) {
    dispatch(new GenerateResponseJob($request->prompt));
    return response()->json(['status' => 'generating']);
}

// ❌ Unoptimized queries
$contacts = Contact::all();
foreach ($contacts as $contact) {
    echo $contact->conversations->count(); // N+1 query!
}

// ✅ Eager loading
$contacts = Contact::with('conversations')->get();
foreach ($contacts as $contact) {
    echo $contact->conversations->count(); // No extra queries
}

// ❌ Broadcasting full models
event(new MessageSent($message)); // Broadcasts entire model!

// ✅ Broadcasting sanitized data
event(new MessageSent($message->id, $message->content));
```

## Testing Strategy

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test tests/Feature/Contacts
php artisan test tests/Unit/Services
```

### Test Configuration
- **Database**: SQLite in-memory (`phpunit.xml`)
- **Queue**: Sync mode (jobs execute immediately)
- **Cache**: Array driver (no Redis needed)
- **Broadcasting**: Fake (no real WebSocket connection)

### Testing Best Practices

1. **Unit Tests**: Test services in isolation with mocks
2. **Feature Tests**: Test API endpoints with real database transactions
3. **Use Factories**: Create test data with `Contact::factory()->create()`
4. **Mock External APIs**: Use `Http::fake()` for AI provider calls
5. **Clean Up**: Use `refershDatabase()` trait or transactions

## Environment Variables

Key `.env` variables (copy from `.env.example`):

```
APP_NAME=Nexus
APP_ENV=local
APP_KEY=               # Generated by: php artisan key:generate
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=root
DB_PASSWORD=

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=localhost
REDIS_PORT=6379

# AI Providers
GEMINI_API_KEY=        # Primary AI provider
OPENAI_API_KEY=        # Fallback
ANTHROPIC_API_KEY=     # Fallback

# Vector Store
PINECONE_API_KEY=
PINECONE_INDEX=

# External APIs
WAHA_URL=              # WhatsApp HTTP API
WAHA_API_TOKEN=

# Broadcasting
BROADCAST_DRIVER=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
```

## Getting Help

1. **Architecture Questions**: Read [Docs/ARCHITECTURE_DETAILS.md](Docs/ARCHITECTURE_DETAILS.md)
2. **Database Questions**: Check [Docs/DB_SCHEMA.md](Docs/DB_SCHEMA.md)
3. **Feature Specifications**: See [Docs/NEW_FEATURES_SPEC.md](Docs/NEW_FEATURES_SPEC.md)
4. **UI/UX Specifications**: Review [Final Master Specification Report.md](Final%20Master%20Specification%Report.md)
5. **Deployment Questions**: Review [Docs/DEPLOYMENT_GUIDE.md](Docs/DEPLOYMENT_GUIDE.md)
6. **Current Tasks**: Check `AI_Workflow/Tasks_Docs/` for active development items
7. **Project Status**: See `AI_Workflow/Updates_Docs/` for latest progress reports
8. **Code Examples**: Existing hubs in `app/Services/` show proven patterns

## Summary

The Nexus platform is a sophisticated Laravel application using clean architecture, event-driven design, and asynchronous job processing. Key principles:

- **API-First**: All features accessible via REST before UI
- **Background-First**: Long operations as async jobs
- **Event-Driven**: Components communicate via events with real-time broadcasting
- **Modular**: 8 self-contained hubs with clear boundaries
- **Tested**: All code covered by unit and feature tests

When implementing features:
1. Use services for business logic
2. Dispatch jobs for long operations
3. Broadcast sanitized data for real-time UI
4. Write tests before/with code
5. Follow naming conventions strictly
6. Check existing code for patterns
