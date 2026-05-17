# Phase 7: AI Models Hub

## Overview

Phase 7 implements a comprehensive multi-provider AI orchestration layer for the Nexus platform. It provides a unified abstraction over Google Gemini, OpenAI, Anthropic, and Groq APIs, with intelligent model selection, fallback chains, cost optimization, quality/speed routing, and full API key lifecycle management.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    AiModelController                         │
│  (REST API: /api/v1/ai-models/*)                            │
├─────────────┬─────────────┬─────────────┬───────────────────┤
│   Model     │  Fallback   │   Cost      │   Quality /       │
│   Selector  │  Chain      │  Optimizer  │   Speed Router    │
├─────────────┴─────────────┴─────────────┴───────────────────┤
│                    ProviderInterface                         │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│  │ Gemini   │ │ OpenAI   │ │Anthropic │ │  Groq    │       │
│  │ Provider │ │ Provider │ │ Provider │ │ Provider │       │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
├─────────────────────────────────────────────────────────────┤
│              API Key Management Layer                        │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│  │ Key Pool │ │ Key      │ │ Rate     │ │ Key      │       │
│  │          │ │ Rotation │ │ Limiter  │ │ Health   │       │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
└─────────────────────────────────────────────────────────────┘
```

## Completed Tasks (14/14)

### 7.1 Provider Abstraction Layer

| Task | File | Status | Description |
|------|------|--------|-------------|
| 7.1.1 | `app/Services/AI/ProviderInterface.php` | ✅ | Interface with 10 methods |
| 7.1.2 | `app/Services/AI/GoogleGeminiProvider.php` | ✅ | Gemini adapter (3 models) |
| 7.1.3 | `app/Services/AI/OpenAIProvider.php` | ✅ | OpenAI adapter (4 models) |
| 7.1.4 | `app/Services/AI/AnthropicProvider.php` | ✅ | Anthropic adapter (3 models) |
| 7.1.5 | `app/Services/AI/GroqProvider.php` | ✅ | Groq adapter (4 models) |

### 7.2 Intelligent Routing

| Task | File | Status | Description |
|------|------|--------|-------------|
| 7.2.1 | `app/Services/AI/ModelSelector.php` | ✅ | Scoring-based model selection |
| 7.2.2 | `app/Services/AI/FallbackChainService.php` | ✅ | Multi-provider fallback with retry |
| 7.2.3 | `app/Services/AI/CostOptimizer.php` | ✅ | Budget-aware cost optimization |
| 7.2.4 | `app/Services/AI/QualityRouter.php` | ✅ | 4-tier quality routing |
| 7.2.5 | `app/Services/AI/SpeedRouter.php` | ✅ | 4-tier speed routing |

### 7.3 API Key Management

| Task | File | Status | Description |
|------|------|--------|-------------|
| 7.3.1 | `app/Services/AI/ApiKeyPool.php` | ✅ | Round-robin key pool with Redis |
| 7.3.2 | `app/Services/AI/ApiKeyRotationService.php` | ✅ | Expiry-based key rotation |
| 7.3.3 | `app/Services/AI/RateLimitService.php` | ✅ | Per-provider rate limiting |
| 7.3.4 | `app/Services/AI/ApiKeyHealthService.php` | ✅ | Health check with caching |

### 7.4 Controller & Routes

| Task | File | Status | Description |
|------|------|--------|-------------|
| 7.4.1 | `app/Http/Controllers/AiModelController.php` | ✅ | Full CRUD + 14 action endpoints |

## API Endpoints

### AI Models CRUD

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/ai-models` | List all AI models |
| POST | `/api/v1/ai-models` | Create new AI model |
| GET | `/api/v1/ai-models/{id}` | Get model details |
| PUT | `/api/v1/ai-models/{id}` | Update model |
| DELETE | `/api/v1/ai-models/{id}` | Delete model |

### Execution

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/ai-models/execute` | Execute with single provider |
| POST | `/api/v1/ai-models/execute-with-fallback` | Execute with fallback chain |
| POST | `/api/v1/ai-models/{id}/test` | Test a model |

### Routing

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/ai-models/select` | Select model by criteria |
| POST | `/api/v1/ai-models/optimize-cost` | Cost-optimized selection |
| POST | `/api/v1/ai-models/route-quality` | Route by quality tier |
| POST | `/api/v1/ai-models/route-speed` | Route by speed tier |

### Key Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/ai-models/providers` | List all providers & models |
| GET | `/api/v1/ai-models/key-pool` | Key pool status |
| GET | `/api/v1/ai-models/key-health` | Key health checks |
| GET | `/api/v1/ai-models/rate-limits` | Rate limit status |
| GET | `/api/v1/ai-models/rotation-schedule` | Key rotation schedule |
| POST | `/api/v1/ai-models/rotate-expired` | Bulk rotate expired keys |

### Monitoring

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/ai-models/fallback-chain` | Fallback chain status |
| GET | `/api/v1/ai-models/budget` | Budget status |

## Provider Models

### Google Gemini
- `gemini-1.5-pro` — 8K context, vision, $0.0035/$0.0105 per 1K
- `gemini-1.5-flash` — 8K context, vision, $0.000075/$0.0003 per 1K
- `gemini-2.0-flash` — 8K context, vision, $0.0001/$0.0004 per 1K

### OpenAI
- `gpt-4o` — 4K context, vision, $0.005/$0.015 per 1K
- `gpt-4o-mini` — 4K context, vision, $0.00015/$0.0006 per 1K
- `gpt-4-turbo` — 4K context, vision, $0.01/$0.03 per 1K
- `gpt-3.5-turbo` — 4K context, $0.0005/$0.0015 per 1K

### Anthropic
- `claude-3-5-sonnet-20241022` — 4K context, vision, $0.003/$0.015 per 1K
- `claude-3-opus-20240229` — 4K context, vision, $0.015/$0.075 per 1K
- `claude-3-haiku-20240307` — 4K context, vision, $0.00025/$0.00125 per 1K

### Groq
- `llama-3.3-70b-versatile` — 8K context, $0.00059/$0.00079 per 1K
- `llama-3.1-8b-instant` — 8K context, $0.00005/$0.00008 per 1K
- `mixtral-8x7b-32768` — 32K context, $0.00024/$0.00024 per 1K
- `gemma2-9b-it` — 8K context, $0.0001/$0.0001 per 1K

## Quality Tiers

| Tier | Min Quality | Use Case |
|------|-------------|----------|
| critical | 90 | Legal, medical, financial |
| high | 80 | Important business logic |
| standard | 65 | Regular tasks |
| low | 0 | Simple/trivial tasks |

## Speed Tiers

| Tier | Max Latency | Use Case |
|------|-------------|----------|
| instant | 300ms | Real-time chat |
| fast | 800ms | Interactive UI |
| normal | 2s | Standard tasks |
| batch | 10s | Batch processing |

## Design Decisions

- **ProviderInterface**: 10-method contract ensures all providers expose identical capabilities
- **cURL over HTTP client**: Zero external dependencies, works in any PHP environment
- **Redis-backed rate limiting**: Distributed rate limit state across workers
- **Round-robin key pool**: Even distribution of API calls across keys
- **Scoring-based selection**: Weighted scoring across cost, quality, and latency
- **Fallback with exponential backoff**: Automatic recovery from transient failures
- **Health check caching**: 5-minute TTL prevents excessive API calls
- **Budget tracking**: In-memory tracking with configurable periods

## Dependencies

- Laravel 11.x (Cache, Log facades)
- Redis (for rate limiting and key pool state)
- cURL extension (for all provider API calls)
- Existing models: `AIModel`, `ApiKey`

## Next Steps

- Add streaming support for all providers
- Add prompt caching for Anthropic
- Add function calling / tool use support
- Add usage analytics dashboard
- Add webhook notifications for key expiry
- Add provider-specific retry logic (e.g., 429 handling for OpenAI)
