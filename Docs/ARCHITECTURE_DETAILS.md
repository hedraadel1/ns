# Nexus Platform - Architecture Details

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Layered Architecture](#2-layered-architecture)
3. [Hub Architecture](#3-hub-architecture)
4. [Service Layer](#4-service-layer)
5. [Data Layer](#5-data-layer)
6. [External Integrations](#6-external-integrations)
7. [Security Architecture](#7-security-architecture)
8. [Deployment Architecture](#8-deployment-architecture)

---

## 1. System Overview

Nexus is a **cognitive digital twin platform** built on Laravel 11 and Vue 3. It provides 8 core hubs for managing agents, workflows, contacts, memory, and AI model orchestration.

### 1.1 Core Principles

| Principle | Description |
|-----------|-------------|
| **API-First** | All features accessible via REST API before UI |
| **Background-First** | Long-running operations execute as background jobs |
| **Event-Driven** | Components communicate through events |
| **Five-Layer Memory** | Working, Episodic, Semantic, Structured, Graph |
| **Modular Hubs** | Self-contained modules with clear boundaries |

### 1.2 Technology Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| **Backend** | Laravel 11.x | PHP framework |
| **Database** | MySQL 8.0+ | Primary data storage |
| **Cache/Queue** | Redis 7.0+ | Caching, sessions, queues |
| **Frontend** | Vue.js 3 + Vite | Reactive UI |
| **Styling** | Tailwind CSS | Utility-first CSS |
| **AI Primary** | Google Gemini | Main AI provider |
| **AI Alternative** | OpenAI, Anthropic | Fallback providers |
| **Vector DB** | Pinecone | Semantic memory storage |
| **Messaging** | WAHA | WhatsApp HTTP API |

---

## 2. Layered Architecture

Nexus follows a strict layered architecture with clear separation of concerns.

### 2.1 Layer Responsibilities

| Layer | Responsibility | Dependencies |
|-------|----------------|-------------|
| **Presentation** | UI rendering, user interaction | API Layer |
| **API** | Request handling, validation, response | Service Layer |
| **Service** | Business logic, orchestration | Domain & Data Layer |
| **Domain** | Business entities, events | Data Layer |
| **Data** | Persistence, caching, external APIs | Infrastructure |

### 2.2 Dependency Rules

- **Upper layers** can depend on **lower layers**
- **Lower layers** cannot depend on **upper layers**
- **Horizontal dependencies** are allowed within the same layer
- **Circular dependencies** are strictly prohibited

---

## 3. Hub Architecture

Each hub is a self-contained module following the same structural pattern.

### 3.1 Hub Structure

```
┌─────────────────────────────────────────────────────────────────┐
│                        Hub: Contacts                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │   Models     │  │   Services   │  │  Controllers │          │
│  │              │  │              │  │              │          │
│  │ • Contact    │  │ • Contact    │  │ • Contact    │          │
│  │ • Conversation│ │ • Contact    │  │   Controller │          │
│  │ • Message    │  │   Hub        │  │              │          │
│  │ • ContactRule│  │              │  │              │          │
│  │ • ContactNote│  │              │  │              │          │
│  │ • ContactTag │  │              │  │              │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │    Actions   │  │    Events    │  │   Listeners  │          │
│  │              │  │              │  │              │          │
│  │ • Contact    │  │ • Contact    │  │ • Send       │          │
│  │   Created    │  │   Created    │  │   Welcome    │          │
│  │ • Contact    │  │ • Contact    │  │   Message    │          │
│  │   Updated    │  │   Updated    │  │ • Log        │          │
│  │ • Message    │  │ • Message    │  │   Activity   │          │
│  │   Received   │  │   Received   │  │              │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 The 8 Hubs

| Hub | Purpose | Key Models | Key Services |
|-----|---------|------------|--------------|
| **AgentsHub** | AI agent management | Agent, AgentTool, AgentSkill, AgentTask | AgentLifecycleService, AgentRegistry |
| **WorkflowsHub** | Multi-step orchestration | Workflow, TaskStep | WorkflowExecutor, WorkflowValidationService |
| **SettingsHub** | Configuration management | Setting | SettingCacheService |
| **ContactsHub** | Contact intelligence | Contact, Conversation, Message, ContactRule, ContactNote, ContactTag | ContactHubService, RelationshipGraphService |
| **LogsHub** | Audit & monitoring | Log, SystemLog | LogService, AlertService |
| **MemoryHub** | Five-layer memory | Memory, StructuredMemory, GraphNode, GraphEdge | MemoryManagementEngine, Mem0Integration |
| **NexusHub** | Main dashboard | - | - |
| **AIModelsHub** | AI provider orchestration | AIModel, ApiKey | AIOrchestrationEngine, ModelRouter |

---

## 4. Service Layer

The service layer contains the core business logic organized into four categories.

### 4.1 Routers

Routers determine execution paths based on context.

| Router | Purpose | Location |
|--------|---------|----------|
| **MessageRouter** | Routes messages to handlers | `app/Services/Routers/MessageRouter.php` |
| **TaskRouter** | Routes tasks to agents/workflows | `app/Services/Routers/TaskRouter.php` |
| **ToneRouter** | Determines communication tone | `app/Services/Routers/ToneRouter.php` |
| **MemoryRouter** | Routes memory to storage | `app/Services/Routers/MemoryRouter.php` |

### 4.2 Pipelines

Pipelines are ordered sequences of operations.

| Pipeline | Purpose | Location |
|----------|---------|----------|
| **ContextAssemblyPipeline** | Builds AI context | `app/Services/Pipelines/ContextAssemblyPipeline.php` |
| **MemoryExtractionPipeline** | Extracts insights | `app/Services/Pipelines/MemoryExtractionPipeline.php` |
| **ResponseDeliveryPipeline** | Prepares responses | `app/Services/Pipelines/ResponseDeliveryPipeline.php` |

### 4.3 Engines

Engines are specialized processing units.

| Engine | Purpose | Location |
|--------|---------|----------|
| **IntentTopicEngine** | Understands intentions | `app/Services/Engines/IntentTopicEngine.php` |
| **PersonaToneEngine** | Communication style | `app/Services/Engines/PersonaToneEngine.php` |
| **MemoryManagementEngine** | Memory operations | `app/Services/Engines/MemoryManagementEngine.php` |
| **AIOrchestrationEngine** | AI model management | `app/Services/Engines/AIOrchestrationEngine.php` |

### 4.4 Builders

Builders construct complex objects.

| Builder | Purpose | Location |
|---------|---------|----------|
| **PromptBuilder** | Constructs AI prompts | `app/Services/Builders/PromptBuilder.php` |
| **ProfileAssemblerBuilder** | Builds contact profiles | `app/Services/Builders/ProfileAssemblerBuilder.php` |
| **ResponseBuilder** | Constructs responses | `app/Services/Builders/ResponseBuilder.php` |
| **ContextBuilder** | Builds execution context | `app/Services/Builders/ContextBuilder.php` |

### 4.5 Core Services

| Service | Purpose | Location |
|---------|---------|----------|
| **AgentLifecycleService** | Agent state management | `app/Services/AgentLifecycleService.php` |
| **AgentRegistry** | Agent discovery | `app/Services/AgentRegistry.php` |
| **AgentSkillLibrary** | Skill management | `app/Services/AgentSkillLibrary.php` |
| **AgentToolExecutor** | Tool execution | `app/Services/AgentToolExecutor.php` |
| **AgentToolRegistry** | Tool registry | `app/Services/AgentToolRegistry.php` |
| **WorkflowExecutor** | Workflow execution | `app/Services/WorkflowExecutor.php` |
| **WorkflowValidationService** | Workflow validation | `app/Services/WorkflowValidationService.php` |
| **WorkflowErrorHandler** | Error handling | `app/Services/WorkflowErrorHandler.php` |
| **ContactHubService** | Contact operations | `app/Services/ContactHubService.php` |
| **RelationshipGraphService** | Relationship mapping | `app/Services/RelationshipGraphService.php` |
| **PreferenceExtractionService** | Preference detection | `app/Services/PreferenceExtractionService.php` |
| **EmotionBaselineService** | Emotion tracking | `app/Services/EmotionBaselineService.php` |
| **TaskQueueService** | Task queuing | `app/Services/TaskQueueService.php` |
| **TaskRoutingService** | Task routing | `app/Services/TaskRoutingService.php` |
| **TaskRetryService** | Task retry logic | `app/Services/TaskRetryService.php` |
| **TaskLogService** | Task logging | `app/Services/TaskLogService.php` |
| **LogService** | Log management | `app/Services/LogService.php` |
| **AlertService** | Alert management | `app/Services/AlertService.php` |
| **SettingCacheService** | Settings caching | `app/Services/SettingCacheService.php` |
| **MCPIntegrationService** | MCP server integration | `app/Services/MCPIntegrationService.php` |

---

## 5. Data Layer

### 5.1 Database Schema

#### Core Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| **users** | User accounts | id, name, email, password |
| **contacts** | Contact profiles | id, uuid, user_id, phone, name, email, type |
| **conversations** | Conversation threads | id, contact_id, topic_id, status |
| **conversation_sessions** | Session tracking | id, conversation_id, started_at, ended_at |
| **messages** | Message history | id, conversation_id, sender_type, content |
| **contact_rules** | Automation rules | id, contact_id, rule, priority |
| **contact_notes** | Contact notes | id, contact_id, note, is_pinned |
| **contact_tags** | Contact tags | id, contact_id, name, color |
| **contact_custom_fields** | Custom fields | id, contact_id, field_key, value |

#### Agent Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| **agents** | AI agents | id, name, key, type, provider, status |
| **agent_tools** | Agent tools | id, agent_id, name, type |
| **agent_skills** | Agent skills | id, agent_id, name, level |
| **agent_tasks** | Agent tasks | id, agent_id, workflow_id, title, status |
| **task_steps** | Task steps | id, agent_task_id, step_order, status |

#### Memory Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| **memories** | General memories | id, contact_id, source, type, content |
| **structured_memories** | Fact storage | id, contact_id, fact_type, data |
| **graph_nodes** | Graph memory nodes | id, label, type, related_id |
| **graph_edges** | Graph memory edges | id, from_node, to_node, label |

#### System Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| **settings** | App configuration | id, key, value, type, group |
| **logs** | Application logs | id, level, category, message, context |
| **ai_models** | AI model config | id, name, provider, external_id |
| **api_keys** | API key management | id, name, key, type, permissions |
| **workflows** | Workflow definitions | id, name, key, steps, trigger_type |

### 5.2 Redis Data Structures

| Key Pattern | Type | Purpose | TTL |
|-------------|------|---------|-----|
| `session:{id}` | Hash | User sessions | 30 min |
| `cache:{key}` | String | General caching | Varies |
| `queue:{name}` | List | Job queues | Persistent |
| `rate:{user}:{endpoint}` | String | Rate limiting | 1 min |
| `channel:{name}` | Pub/Sub | Real-time events | Real-time |
| `working:{contact_id}` | Hash | Working memory | Session |

### 5.3 Five-Layer Memory Architecture

| Layer | Storage | Purpose | Retention |
|-------|---------|---------|-----------|
| **Working** | Redis | Real-time context | Session-based |
| **Episodic** | MySQL | Events & conversations | Permanent |
| **Semantic** | Pinecone | Facts & knowledge | Permanent |
| **Structured** | MySQL | Database entities | Permanent |
| **Graph** | MySQL | Relationship networks | Permanent |

---

## 6. External Integrations

### 6.1 WAHA (WhatsApp HTTP API)

- **Endpoint**: `POST /api/v1/webhooks/waha`
- **Purpose**: Receive incoming WhatsApp messages
- **Processing**: Parse → Validate → Store → Route

### 6.2 AI Providers

| Provider | Purpose | Fallback Priority |
|----------|---------|-------------------|
| **Google Gemini** | Primary AI | 1 |
| **OpenAI** | Alternative | 2 |
| **Anthropic** | Complex reasoning | 3 |
| **Cohere** | Embeddings | 4 |

### 6.3 Vector Database (Pinecone)

- **Purpose**: Semantic memory storage
- **Operations**: Upsert, Query, Delete
- **Integration**: Via `app/Integrations/Mem0Integration.php`

---

## 7. Security Architecture

### 7.1 Authentication Methods

| Method | Use Case | Implementation |
|--------|----------|----------------|
| **Sanctum Tokens** | API authentication | `Authorization: Bearer {token}` |
| **Session** | Web UI | Laravel session |
| **API Keys** | Service-to-service | `X-API-Key` header |

### 7.2 Authorization Layers

1. **Authentication**: Verify user identity
2. **Authorization**: Check permissions via gates/policies
3. **Rate Limiting**: Prevent abuse
4. **Input Validation**: Sanitize all input
5. **Output Encoding**: Prevent XSS

---

## 8. Deployment Architecture

### 8.1 Infrastructure Components

```
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   Web        │  │   Queue      │  │   Cache      │
│   Server     │  │   Worker     │  │   Server     │
│              │  │              │  │              │
│ • Nginx      │  │ • Horizon    │  │ • Redis      │
│ • PHP-FPM    │  │ • Workers    │  │ • Cluster    │
│ • Laravel    │  │ • Supervisor │  │ • Sentinel   │
└──────────────┘  └──────────────┘  └──────────────┘
       │                │                │
       └────────────────┴────────────────┘
                            │
                            ▼
                    ┌─────────────┐
                    │   Database  │
                    │   MySQL     │
                    │   Primary   │
                    └─────────────┘
                            │
                            ▼
                    ┌─────────────┐
                    │   Database  │
                    │   Replica   │
                    └─────────────┘
```

### 8.2 Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **CPU** | 2 cores | 4+ cores |
| **RAM** | 4GB | 8GB+ |
| **Storage** | 50GB SSD | 100GB+ SSD |
| **PHP** | 8.2 | 8.3 |
| **MySQL** | 8.0 | 8.0+ |
| **Redis** | 7.0 | 7.2+ |
| **Node.js** | 18 | 20+ |

### 8.3 Scaling Strategy

| Layer | Scaling Method | Trigger |
|-------|----------------|---------|
| **Web** | Horizontal (add servers) | CPU > 70% |
| **Queue** | Horizontal (add workers) | Queue backlog > 1000 |
| **Database** | Read replicas | Connections > 100 |
| **Cache** | Redis cluster | Memory > 80% |
| **CDN** | Static assets | Large file delivery |

---

## 9. Directory Structure

```
nexus-platform/
├── app/
│   ├── Agents/              # Agent-related classes
│   ├── Events/              # Domain events
│   ├── Http/
│   │   ├── Controllers/     # API controllers
│   │   ├── Middleware/      # HTTP middleware
│   │   └── Requests/        # Form requests
│   ├── Integrations/        # External integrations
│   ├── Jobs/                # Queue jobs
│   ├── Listeners/           # Event listeners
│   ├── Models/              # Eloquent models
│   ├── Providers/           # Service providers
│   ├── Repositories/        # Data repositories
│   └── Services/            # Business logic services
│       ├── AI/              # AI-related services
│       ├── Engines/         # Processing engines
│       ├── Memory/          # Memory services
│       ├── Pipelines/       # Processing pipelines
│       └── Routing/         # Routing services
├── bootstrap/               # Framework bootstrap
├── config/                  # Configuration files
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── docs/                    # Documentation
├── public/                  # Public assets
├── resources/
│   ├── js/                  # Vue.js frontend
│   └── css/                 # Stylesheets
├── routes/
│   ├── api.php              # API routes
│   └── web.php              # Web routes
├── storage/                 # Application storage
├── tests/                   # Test suite
└── vendor/                  # Composer dependencies
```

---

## 10. Coding Standards

### 10.1 PHP Standards

- Follow **PSR-12** coding style
- Use **strict types** declaration
- Type hint all method parameters and return types
- Use **meaningful variable names**
- Maximum line length: **120 characters**

### 10.2 Laravel Conventions

| Convention | Rule |
|------------|------|
| **Models** | Singular, PascalCase |
| **Tables** | Plural, snake_case |
| **Controllers** | PascalCase + Controller suffix |
| **Routes** | snake_case, RESTful |
| **Services** | PascalCase + Service suffix |
| **Events** | PascalCase + Event suffix |
| **Listeners** | PascalCase + Listener suffix |

### 10.3 API Conventions

- All endpoints prefixed with `/api/v1`
- RESTful resource routes where possible
- Consistent JSON response format
- Proper HTTP status codes
- Comprehensive error messages

---

*Last Updated: May 2026*
