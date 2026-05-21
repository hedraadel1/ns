# High-Level System Architecture

This document provides an overview of the Nexus platform's architecture, technology stack, and core design principles.

## System Overview

The Nexus platform is a cognitive digital twin platform built as a modular system with 8 self-contained hubs that communicate via events and APIs. It follows a strict 5-layer clean architecture where upper layers depend on lower layers, but not vice versa.

## Technology Stack

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

## 8 Core Hubs (Modules)

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

## Layered Architecture

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

## Design Principles

1. **API-First**: All features accessible via REST before UI
2. **Background-First**: Long operations as async jobs
3. **Event-Driven**: Components communicate via events with real-time broadcasting
4. **Modular**: 8 self-contained hubs with clear boundaries
5. **Tested**: All code covered by unit and feature tests

## Related Documentation

- [System Requirements](./02-System-Requirements.md)
- [Technical Specifications](./03-Technical-Specifications.md)
- [Data Models](./04-Data-Models.md)
- [Code Hub](../02-Project-Code/) - Detailed code documentation
- [Workflow Hub](../03-Workflow-Hub/) - Business processes and execution traces
