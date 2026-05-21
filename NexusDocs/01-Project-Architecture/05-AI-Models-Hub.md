# AI Models Hub

## Overview

The AI Models Hub provides dynamic provider management, intent-based routing, and secure API key handling for the Nexus platform. This hub orchestrates communication with external AI providers (Gemini, OpenAI, Anthropic, Groq) through a unified interface that supports fallback chains, circuit breaking, and cost optimization.

## Core Purpose

1. **Dynamic Provider Management**: Register, configure, and manage multiple AI providers
2. **Intent-Based Routing**: Route requests to optimal providers based on intent classification
3. **Secure Key Storage**: AES-256 encrypted API key management with rotation support
4. **Circuit Breaking**: Automatic failover when providers are unhealthy
5. **Cost Optimization**: Select models based on budget constraints
6. **Usage Tracking**: Monitor token consumption across providers

## Key Services

### DynamicProviderRegistry
- **Path**: `app/Services/AiModelsHub/DynamicProviderRegistry.php`
- **Purpose**: Manages provider registration, selection, and health monitoring
- **Key Methods**:
  - `registerProvider(array $config)`: Registers a new provider
  - `selectProvider(string $intent, array $criteria)`: Selects optimal provider
  - `getHealthStatus()`: Returns health status for all providers
  - `syncModels(int $providerId)`: Syncs available models from provider

### IntentRoutingEngine
- **Path**: `app/Services/AiModelsHub/IntentRoutingEngine.php`
- **Purpose**: Routes requests based on intent classification
- **Key Methods**:
  - `routeIntent(string $intent, array $context)`: Returns routing decision
  - `getRoutingMatrix()`: Returns current routing rules
  - `updateRouting(string $intent, array $rules)`: Updates routing configuration

### PayloadAdapterFactory
- **Path**: `app/Services/AiModelsHub/PayloadAdapterFactory.php`
- **Purpose**: Adapts requests to provider-specific formats
- **Key Methods**:
  - `createPayload(string $provider, array $data)`: Creates provider-specific payload
  - `adaptResponse(string $provider, mixed $response)`: Normalizes responses

### EncryptedApiKeyStorage
- **Path**: `app/Services/AiModelsHub/EncryptedApiKeyStorage.php`
- **Purpose**: Securely stores and retrieves API keys
- **Key Methods**:
  - `storeKey(int $providerId, string $key)`: Encrypts and stores key
  - `getKey(int $providerId): string`: Retrieves decrypted key
  - `rotateKey(int $providerId, string $newKey)`: Rotates API key

### CircuitBreaker
- **Path**: `app/Services/AiModelsHub/CircuitBreaker.php`
- **Purpose**: Prevents cascading failures
- **Key Methods**:
  - `canExecute(int $providerId): bool`: Checks if provider is available
  - `recordSuccess(int $providerId)`: Records successful execution
  - `recordFailure(int $providerId, Exception $e)`: Records failure

## Database Tables

### ai_providers
- `id`: UUID (primary key)
- `name`: string - Provider name
- `type`: string - Provider type (openai, anthropic, gemini, groq)
- `base_url`: string - API base URL
- `capabilities`: JSON - Supported capabilities
- `rate_limit`: integer - Requests per minute
- `is_active`: boolean - Provider status

### ai_api_keys
- `id`: UUID (primary key)
- `provider_id`: UUID (foreign key)
- `encrypted_key`: text - AES-256 encrypted key
- `key_hash`: string - Hash for lookup
- `created_at`: timestamp
- `expires_at`: timestamp (nullable)

### intent_routing
- `id`: UUID (primary key)
- `intent_pattern`: string - Regex pattern for intent
- `provider_id`: UUID (foreign key)
- `model_name`: string - Target model
- `priority`: integer - Routing priority
- `criteria`: JSON - Additional routing criteria

### usage_logs
- `id`: UUID (primary key)
- `provider_id`: UUID (foreign key)
- `model_name`: string
- `prompt_tokens`: integer
- `completion_tokens`: integer
- `total_tokens`: integer
- `cost`: decimal
- `created_at`: timestamp

## API Endpoints

### Provider Management
```
POST /api/v1/ai/providers
- Creates a new AI provider configuration

POST /api/v1/ai/providers/{id}/test
- Tests provider connection

POST /api/v1/ai/providers/{id}/sync-models
- Syncs available models from provider
```

### Intent Routing
```
GET  /api/v1/ai/intents/routing
- Returns current routing matrix

PUT  /api/v1/ai/intents/routing
- Updates routing configuration for an intent
```

### AI Request Handling
```
POST /api/v1/ai/request
- Handles AI request with intent-based routing
- Body: { intent: string, messages: array, parameters: object }
```

### Provider Operations
```
GET  /api/v1/ai/providers
- Lists available providers

GET  /api/v1/ai-models/key-pool
- Returns key rotation status

GET  /api/v1/ai-models/fallback-chain
- Returns current fallback chain configuration
```

## Provider Implementations

### GoogleGeminiProvider
- Supports: gemini-pro, gemini-pro-vision
- Features: Multimodal, streaming

### OpenAIProvider
- Supports: gpt-4, gpt-4-turbo, gpt-3.5-turbo
- Features: Function calling, embeddings

### AnthropicProvider
- Supports: claude-3-opus, claude-3-sonnet, claude-3-haiku
- Features: Vision capabilities

### GroqProvider
- Supports: llama-3, mixtral
- Features: High-speed inference

## Security Considerations

1. **API Key Encryption**: All keys encrypted with AES-256-GCM
2. **SSRF Protection**: All external requests validated against allowlists
3. **Private Channels**: All broadcasts use private channels with sanitized data
4. **Rate Limiting**: Provider-specific rate limiting enforced
5. **Key Rotation**: Automatic key rotation support

## Related Documentation
- [Database Schema](../01-Project-Architecture/04-Data-Models.md)
- [Technical Specifications](../01-Project-Architecture/03-Technical-Specifications.md)
- [AI Provider Interface](../02-Project-Code/01-Backend/03-Interfaces/AIProviderInterface.md)
- [Usage Tracking](../02-Project-Code/01-Backend/02-Core-Services/UsageTracker.md)