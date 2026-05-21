# Architecture Documentation Gaps and Updates Plan

This document identifies gaps between the current Nexus documentation in NexusDocs and the actual implementation in the codebase, along with a detailed plan to update and implement missing requirements.

## Executive Summary

Based on analysis of the codebase, the current documentation has **significant gaps** in covering:

1. **AI Models Hub Implementation** - Partially implemented with recent migrations
2. **API Endpoints Documentation** - Many endpoints not documented
3. **Database Schema Alignment** - New tables exist but not documented
4. **Service Implementations** - Extensive service layer not fully documented
5. **Real-time Features** - WebSocket/Reverb integration needs documentation
6. **Security Features** - Encrypted API keys, SSRF protection, circuit breakers

---

## Gap Analysis by Category

### 1. AI Models Hub Gap Analysis

#### Current Implementation Status: ✅ Partially Completed

**Implemented Services (found in `app/Services/AiModelsHub/`):**
- `DynamicProviderRegistry.php` - Provider registration and management
- `IntentRoutingEngine.php` - Intent-based routing logic
- `PayloadAdapterFactory.php` - Request payload adaptation
- `EncryptedApiKeyStorage.php` - AES-256 key encryption
- `CircuitBreaker.php` - Failure detection and fallback
- `CacheManager.php` - Response caching
- `UsageCalculator.php` / `UsageTracker.php` - Token usage tracking
- Provider implementations: `AnthropicProvider.php`, `GoogleGeminiProvider.php`, `GroqProvider.php`, `OpenAIProvider.php`

**Database Tables Created (2026_05 migrations):**
- `ai_providers` - Provider configurations
- `ai_api_keys` - Encrypted API keys
- `intent_routing` - Routing rules
- `usage_logs` - Usage tracking

#### Documentation Gaps:
| Gap | Priority | Missing Documentation |
|-----|----------|----------------------|
| AIProviderInterface | High | No documentation of contract or method signatures |
| DynamicProviderRegistry | High | No documentation of provider registration flow |
| IntentRoutingEngine | High | No documentation of intent matching algorithm |
| EncryptedApiKeyStorage | Critical | No documentation of encryption implementation |
| CircuitBreaker | High | No documentation of failure threshold logic |
| UsageTracker/Calculator | Medium | No documentation of token counting logic |
| Provider implementations | High | No documentation of each provider's specifics |

### 2. Database Schema Gap Analysis

#### Missing Tables in Documentation:
| Table Name | Migration | Purpose | Documentation Status |
|------------|-----------|---------|---------------------|
| `ai_providers` | 05_19_000001 | AI service provider configs | ❌ Missing |
| `ai_api_keys` | 05_19_000003 | Encrypted API keys | ❌ Missing |
| `intent_routing` | 05_19_000004 | Routing rules | ❌ Missing |
| `usage_logs` | 05_19_000005 | Token usage tracking | ❌ Missing |
| `structured_memories` | 05_17_090000 | Memory layer 2 | ❌ Missing |
| `graph_nodes` | 05_17_100000 | Memory layer 3 | ❌ Missing |
| `graph_edges` | 05_17_100000 | Memory relationships | ❌ Missing |

### 3. API Endpoints Gap Analysis

#### Undocumented API Endpoints (from routes/api.php):

**AI Models Hub (lines 171-182):**
```
POST   /api/v1/ai/providers              - Create provider
POST   /api/v1/ai/providers/{id}/test     - Test provider connection
POST   /api/v1/ai/providers/{id}/sync-models - Sync provider models
GET    /api/v1/ai/intents/routing         - Get routing matrix
PUT    /api/v1/ai/intents/routing         - Update routing
POST   /api/v1/ai/request                 - Handle AI request with intent routing
```

**Missing from Documentation:**
- All AI Models Hub endpoints
- Agent execution flow endpoints
- Memory search/indexing endpoints
- Task management extended endpoints

### 4. Service Layer Gap Analysis

#### Undocumented Services Found:
| Service | File Path | Purpose |
|---------|-----------|---------|
| AgentLifecycleService | app/Services/ | Agent state management |
| AgentRegistry | app/Services/ | Agent capability registration |
| AgentSkillLibrary | app/Services/ | Skill management |
| AgentToolExecutor | app/Services/ | Tool execution orchestration |
| AgentToolRegistry | app/Services/ | Tool registration |
| WorkflowExecutor | app/Services/ | Workflow execution |
| WorkflowValidationService | app/Services/ | Workflow validation |
| RelationshipGraphService | app/Services/ | Relationship analysis |
| MemoryManagementEngine | app/Services/Memory/ | Memory orchestration |
| AlertService | app/Services/ | System alerts |
| CircuitBreakerService | app/Services/ | Circuit breaking |
| EmotionBaselineService | app/Services/ | Emotion detection |
| IdempotencyService | app/Services/ | Duplicate request prevention |
| LogService | app/Services/ | Logging |
| TaskLogService | app/Services/ | Task logging |
| TaskQueueService | app/Services/ | Queue management |
| TaskRetryService | app/Services/ | Retry logic |
| TaskRoutingService | app/Services/ | Task routing |

### 5. Frontend Component Gap Analysis

#### Undocumented Vue Components (from AGENTS.md and frontend stack):
- `NxAiPulse.vue` - AI state indicator
- `NxGlassCard.vue` - Standard container
- `NxTokenMeter.vue` - Context window visualization
- `NxLiveLoader.vue` - Job progress indicator
- `NxActionButton.vue` - Standardized button

---

## Update Plan & Timeline

### Phase 1: Critical Architecture Updates (Week 1)

#### Task 1.1: AI Models Hub Documentation
**Files to create/update:**
- `01-Project-Architecture/05-AI-Models-Hub.md`
- Database schema documentation for new tables
- Service API documentation for each service
- Routing documentation for intent-based requests

**Implementation Steps:**
1. Document AIProviderInterface contract
2. Document DynamicProviderRegistry methods
3. Document IntentRoutingEngine algorithm
4. Document EncryptedApiKeyStorage encryption flow
5. Document CircuitBreaker thresholds and states
6. Document each provider implementation differences
7. Map API endpoints to service methods

#### Task 1.2: Security Architecture Updates
**Files to create/update:**
- `01-Project-Architecture/04-Security-Architecture.md`

**Implementation Steps:**
1. Document AES-256 key encryption implementation
2. Document SSRF protection mechanisms
3. Document private channel broadcasting rules
4. Document circuit breaker patterns
5. Document fallback chain implementation

### Phase 2: Database Schema Alignment (Week 2)

#### Task 2.1: Database Documentation
**Files to create/update:**
- `01-Project-Architecture/04-Data-Models.md` (update)
- New file: `02-Project-Code/01-Database-Schema.md`

**Implementation Steps:**
1. Document ai_providers table schema
2. Document ai_api_keys table with encryption notes
3. Document intent_routing table and columns
4. Document usage_logs table structure
5. Update memory system tables documentation
6. Create ER diagrams for new relationships

### Phase 3: API Documentation (Week 3)

#### Task 3.1: API Endpoint Documentation
**Files to create/update:**
- `01-Project-Architecture/03-Technical-Specifications.md` (update)
- New file: `02-Project-Code/API-Documentation.md`

**Implementation Steps:**
1. Document all AI Models Hub endpoints
2. Document request/response formats
3. Document authentication requirements
4. Document error codes and messages
5. Document rate limiting behavior
6. Document fallback mechanisms

### Phase 4: Service Layer Documentation (Week 4)

#### Task 4.1: Service Documentation
**Files to create/update:**
- `02-Project-Code/01-Backend/02-Core-Services/` files

**Implementation Steps:**
1. Document AgentLifecycleService
2. Document WorkflowExecutor and related services
3. Document MemoryManagementEngine
4. Document CircuitBreakerService
5. Document IdempotencyService
6. Document all service dependencies

---

## Detailed Implementation Requirements

### Required Documentation Updates

#### 1. High-Level Overhaul Needed

```markdown
# 05-AI-Models-Hub.md (NEW FILE)

## Overview
The AI Models Hub provides dynamic provider management, intent-based routing, and secure API key handling.

## Key Components
- DynamicProviderRegistry - Provider lifecycle
- IntentRoutingEngine - Request routing logic
- PayloadAdapterFactory - Request adaptation
- EncryptedApiKeyStorage - Key security
- CircuitBreaker - Failure handling

## API Endpoints
### POST /api/v1/ai/request
Handles AI requests with intent-based routing...

### POST /api/v1/ai/providers
Creates a new AI provider configuration...

## Security Considerations
- AES-256 encryption for API keys
- SSRF protection on all requests
- Private channel broadcasting only
```

#### 2. Database Updates Required

```markdown
# Updated 04-Data-Models.md

## AI Models Hub Tables

### ai_providers
| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| name | string | Provider name |
| type | string | Provider type (openai, anthropic, gemini) |
| base_url | string | API base URL |
| capabilities | JSON | Provider capabilities |
| rate_limit | integer | Requests per minute |

### ai_api_keys
| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| provider_id | UUID | Foreign key |
| encrypted_key | text | AES-256 encrypted key |
| key_hash | string | Hash for lookup |
```

#### 3. Service Documentation Template

```markdown
# DynamicProviderRegistry Service

## Purpose
Manages AI provider registration, selection, and health monitoring.

## Methods
### registerProvider(array $config): Provider
Registers a new provider with the given configuration.

### selectProvider(string $intent): ?Provider
Selects the best provider for the given intent.

### getHealthStatus(): array
Returns health status for all providers.
```

---

## Priority Matrix

| Priority | Category | Items | Estimated Effort |
|----------|----------|-------|-----------------|
| 🔴 Critical | Security | Encryption, SSRF, API keys | 2 days |
| 🔴 Critical | AI Models | Provider docs, routing | 3 days |
| 🟠 High | Database | New tables, relationships | 2 days |
| 🟠 High | API | Endpoints, formats | 2 days |
| 🟡 Medium | Services | Core service layer | 3 days |
| 🟢 Low | Frontend | Components, stores | 2 days |

---

## Immediate Action Items

### Week 1 (Next 7 days):
1. ✅ Create this Gaps-updates-plan.md document
2. ⬜ Create 05-AI-Models-Hub.md with comprehensive documentation
3. ⬜ Update 04-Data-Models.md with new database tables
4. ⬜ Create security architecture documentation
5. ⬜ Document encryption implementation details

### Week 2:
1. ⬜ Document all AI Models Hub services
2. ⬜ Create API endpoint documentation
3. ⬜ Update database schema diagrams
4. ⬜ Document intent routing algorithm

### Week 3:
1. ⬜ Document service layer comprehensively
2. ⬜ Create workflow execution documentation
3. ⬜ Update technical specifications
4. ⬜ Align documentation with actual routes

### Week 4:
1. ⬜ Review and validate all documentation
2. ⬜ Create cross-reference links
3. ⬜ Update AI context documents
4. ⬜ Final consistency check

---

## Success Criteria

- [ ] All AI Models Hub services documented with method signatures
- [ ] All database tables documented with schema
- [ ] All API endpoints documented with examples
- [ ] Security implementation documented
- [ ] Documentation matches actual code implementation
- [ ] Cross-hub references are consistent
- [ ] Guide.md files updated for new structures

---

## Related Documentation

- [Project Architecture Guide](./Guide.md)
- [Code Hub](../02-Project-Code/Guide.md)
- [Governance Hub](../05-Governance-Hub/Guide.md)
- [Existing AGENTS.md](../../AGENTS.md)
- [Existing Architecture Details](../../Docs/ARCHITECTURE_DETAILS.md)