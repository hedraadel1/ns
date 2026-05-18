# Phase 8: Routers, Pipelines & Engines

## Overview

Phase 8 implements the intelligent routing layer, data processing pipelines, and cognitive engines that power the Nexus platform's conversation dynamics. This phase connects the AI Models Hub (Phase 7) with the Agents Hub (Phase 5) and Workflows Hub (Phase 6) through a unified orchestration engine.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    AIOrchestrationEngine                      │
│  (orchestrate: context → select → execute → deliver)        │
├─────────────┬─────────────┬─────────────┬───────────────────┤
│   Intent    │   Persona   │   Memory    │   Orchestration   │
│   Topic     │   Tone      │ Management  │   Engine          │
│   Engine    │   Engine    │   Engine    │                   │
├─────────────┴─────────────┴─────────────┴───────────────────┤
│                      Pipelines Layer                          │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐        │
│  │ Context      │ │ Memory       │ │ Response     │        │
│  │ Assembly     │ │ Extraction   │ │ Delivery     │        │
│  └──────────────┘ └──────────────┘ └──────────────┘        │
│  ┌──────────────┐ ┌──────────────┐                           │
│  │ Pipeline     │ │ Pipeline     │                           │
│  │ Error        │ │ Monitor      │                           │
│  │ Handler      │ │              │                           │
│  └──────────────┘ └──────────────┘                           │
├─────────────────────────────────────────────────────────────┤
│                      Routers Layer                            │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│  │ Message  │ │  Task    │ │  Tone    │ │ Memory   │       │
│  │ Router   │ │  Router  │ │  Router  │ │ Router   │       │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              RoutingMiddleware                        │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Completed Tasks (14/14)

### 8.1 Routers

| Task | File | Status | Description |
|------|------|--------|-------------|
| 8.1.1 | `app/Services/Routing/MessageRouter.php` | ✅ | Pattern-based message routing |
| 8.1.2 | `app/Services/Routing/TaskRouter.php` | ✅ | Task-to-agent/workflow routing |
| 8.1.3 | `app/Services/Routing/ToneRouter.php` | ✅ | 6-tone persona adaptation |
| 8.1.4 | `app/Services/Routing/MemoryRouter.php` | ✅ | Memory type-based routing |
| 8.1.5 | `app/Http/Middleware/RoutingMiddleware.php` | ✅ | Request tagging middleware |

### 8.2 Pipelines

| Task | File | Status | Description |
|------|------|--------|-------------|
| 8.2.1 | `app/Services/Pipelines/ContextAssemblyPipeline.php` | ✅ | Context + history assembly |
| 8.2.2 | `app/Services/Pipelines/MemoryExtractionPipeline.php` | ✅ | Pluggable memory extraction |
| 8.2.3 | `app/Services/Pipelines/ResponseDeliveryPipeline.php` | ✅ | Multi-format response delivery |
| 8.2.4 | `app/Services/Pipelines/PipelineErrorHandler.php` | ✅ | Error classification + fallback |
| 8.2.5 | `app/Services/Pipelines/PipelineMonitor.php` | ✅ | Redis-backed metrics + tracing |

### 8.3 Engines

| Task | File | Status | Description |
|------|------|--------|-------------|
| 8.3.1 | `app/Services/Engines/IntentTopicEngine.php` | ✅ | 10 intents, 7 topics |
| 8.3.2 | `app/Services/Engines/PersonaToneEngine.php` | ✅ | 5 personas, 6 tones |
| 8.3.3 | `app/Services/Engines/MemoryManagementEngine.php` | ✅ | CRUD + auto-limit enforcement |
| 8.3.4 | `app/Services/Engines/AIOrchestrationEngine.php` | ✅ | 5 routing modes, full pipeline |

## Routers

### MessageRouter
Routes incoming messages to the correct processing path based on content patterns, metadata, and registered route handlers.

- **Pattern types**: `intent:`, `sender:`, `direction:`, `type:`, `regex:`, plain text
- **Middleware support**: Pluggable pre/post processing callbacks
- **Default fallback**: First registered route or explicitly marked default

### TaskRouter
Routes task requests to the appropriate agent or workflow based on task type, payload hints, and registered defaults.

- **Agent routing**: By `agent_type` in payload → registered agent registry
- **Workflow routing**: By `workflow_id` or `workflow_trigger` in payload
- **Default routes**: Configurable per task type

### ToneRouter
Detects and routes tone/style requests for persona-adapted responses.

- **6 tones**: professional, casual, friendly, technical, empathetic, concise
- **Detection**: Keyword-scored tone detection from message content
- **Contact preferences**: Per-contact tone overrides
- **System prompt generation**: Auto-generated tone instructions

### MemoryRouter
Routes memory reads/writes based on memory type with handler registry.

- **Type-based routing**: Register handlers per memory type
- **Read/Write separation**: Separate route resolution for each operation
- **Required field validation**: Per-handler required field enforcement

### RoutingMiddleware
Laravel middleware that tags every request with routing metadata.

- **Metadata**: route_name, controller, method, path, timestamp
- **Response headers**: `X-Route-Name`, `X-Controller`
- **Route tagging**: Pattern-based tag assignment

## Pipelines

### ContextAssemblyPipeline
Assembles rich context before AI request execution.

- **Conversation loading**: Eager-loads contact relationship
- **Memory loading**: Last N memories for contact/user
- **History loading**: Last N messages in conversation
- **Summary building**: `[type] content` format
- **Prompt building**: Structured prompt with context, history, current message

### MemoryExtractionPipeline
Extracts structured memories from message content using pluggable extractors.

- **Pluggable extractors**: Register callbacks per extraction type
- **Filter chain**: Apply filters before storage
- **Auto-storage**: Direct-to-database with metadata tracking

### ResponseDeliveryPipeline
Formats and delivers AI responses to target channels.

- **Formats**: text, markdown, html, json + custom formatters
- **Channels**: Configurable channel registry
- **Message creation**: Creates outbound Message records

### PipelineErrorHandler
Catches and recovers from pipeline failures.

- **Error classification**: timeout, rate_limit, connection, auth, validation, not_found, unknown
- **Fallback handlers**: Per-stage fallback callbacks
- **Retry strategies**: Configurable per error type

### PipelineMonitor
Publishes pipeline metrics and trace logs with Redis-backed storage.

- **Stage metrics**: count, success/failure, avg/min/max duration, success rate
- **Pipeline metrics**: Aggregated across stages
- **Trace logging**: Debug-level structured logging

## Engines

### IntentTopicEngine
Detects user intent and topic from message content using keyword scoring.

- **10 intents**: greeting, farewell, question, request, complaint, praise, schedule, support, billing, feedback
- **7 topics**: product, service, account, order, technical, billing, general
- **Confidence scoring**: Percentage-based confidence from match counts

### PersonaToneEngine
Adjusts persona and tone selection for response generation.

- **5 personas**: default, expert, friendly, concise, empathetic
- **6 tones**: professional, casual, friendly, technical, empathetic, concise
- **Contact preferences**: Per-contact persona overrides
- **System prompt generation**: Auto-generated persona instructions

### MemoryManagementEngine
Manages memory lifecycle, storage, and retrieval with automatic limit enforcement.

- **5 memory types**: fact (priority 10), event (9), preference (8), context (7), general (5)
- **CRUD operations**: store, retrieve, search, forget
- **Auto-limit enforcement**: Deletes oldest memories when limit exceeded (100/contact)
- **Retention tracking**: 365-day retention period

### AIOrchestrationEngine
Orchestrates model selection, prompt building, execution, and response delivery.

- **5 routing modes**:
  - `auto` — Automatic model selection via ModelSelector
  - `quality` — Quality-tier routing (critical/high/standard/low)
  - `speed` — Speed-tier routing (instant/fast/normal/batch)
  - `cost` — Cost-optimized selection via CostOptimizer
  - `fallback` — Multi-provider fallback chain
- **Full pipeline**: Context assembly → Memory extraction → AI execution → Response delivery
- **Metrics recording**: PipelineMonitor integration for all executions

## Design Decisions

1. **Router pattern**: Each router is a standalone service with no framework coupling
2. **Pipeline pattern**: Each pipeline is a single-responsibility stage with pluggable extensions
3. **Engine pattern**: Engines are stateless analysis/decision services
4. **Redis for metrics**: PipelineMonitor uses Redis for distributed metric aggregation
5. **Keyword scoring**: Intent/topic detection uses simple keyword scoring (extensible to ML)
6. **Memory limit enforcement**: Automatic FIFO eviction at 100 memories/contact
7. **Orchestration modes**: 5 distinct routing strategies selectable per request

## Dependencies

- Laravel 11.x (Cache, Log facades, Validator)
- Redis (PipelineMonitor metrics storage)
- Existing models: `Message`, `Conversation`, `Contact`, `Memory`, `Task`, `Agent`, `Workflow`
- Phase 7 services: `ModelSelector`, `FallbackChainService`, `CostOptimizer`, `QualityRouter`, `SpeedRouter`
- Phase 8 pipelines: `ContextAssemblyPipeline`, `MemoryExtractionPipeline`, `ResponseDeliveryPipeline`, `PipelineMonitor`

## Next Steps

- Add ML-based intent/topic classification
- Add conversation summarization engine
- Add sentiment analysis engine
- Add entity extraction pipeline
- Add multi-turn context windowing
- Add pipeline visualization dashboard
