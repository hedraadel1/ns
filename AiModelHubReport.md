# 📊 AiModels Hub - Codebase Audit & Gap Analysis Report

## 1. ❌ Missing Implementations

- Database table `ai_providers` is completely missing
- Database table `ai_api_keys` is completely missing
- Database table `intent_routing` is completely missing
- API endpoint `POST /api/v1/ai/providers` does not exist
- API endpoint `POST /api/v1/ai/providers/{id}/sync-models` does not exist
- API endpoint `PUT /api/v1/ai/intents/routing` does not exist
- API endpoint `POST /api/v1/ai/request` does not exist
- Service `DynamicProviderRegistry` does not exist
- Service `IntentRoutingEngine` does not exist
- Service `PayloadAdapterFactory` does not exist

## 2. ⚠️ Incomplete & Partial Implementations

- The `ai_models` table exists but does not match the Blueprint v2.0 specification:
  * Missing `provider_id` (FK to ai_providers)
  * Missing `context_window`, `input_cost_per_m`, `output_cost_per_m`, `last_synced_at` columns
  * Uses `bigint` auto-increment primary key instead of UUID
  * Contains `provider` (string) column instead of foreign key reference
  * Contains `external_id`, `description`, `capabilities`, `metadata`, `status` columns not specified in Blueprint
- Existing AI provider services (OpenAIProvider, GroqProvider, AnthropicProvider, GoogleGeminiProvider) are hardcoded implementations rather than dynamic providers configured via the database
- The `ModelSelector` service exists but does not implement intent-based routing from the `intent_routing` table
- No evidence of payload adaptation logic for different provider formats (OpenAI standard, Anthropic standard, custom)
- No implementation of automated model synchronization via `syncModels()` method hitting provider's `models_fetch_endpoint`
- Missing API key lifecycle management (secure storage, encryption, multiple keys per provider)
- No SSRF protection for dynamic base URLs
- No real-time token & cost meter implementation
- Missing Redis cache invalidation mechanism when SettingsHub updates intent routing

## 3. 🐛 Bugs & Schema Deviations

- The `ai_models` table schema deviates significantly from Blueprint:
  * Primary key type mismatch (bigint auto-increment vs UUID)
  * Missing foreign key relationship to providers
  * Missing cost tracking fields (input_cost_per_m, output_cost_per_m)
  * Missing context window and last synced timestamp
  * Using `provider` as a string field instead of foreign key
- No `ai_api_keys` table for encrypted API key storage (Blueprint requires AES-256 encryption at rest)
- No `intent_routing` table for intent-based deterministic routing
- Current AI services store API keys in plaintext configuration/env rather than encrypted database storage
- Missing fallback chain implementation for rate limits (429), server errors (5xx), and timeouts
- No validation of dynamic base URLs to prevent SSRF attacks
- No circuit breaker implementation for provider failovers

## 4. 🏗️ Architectural Violations

- Workflows and agents are likely calling specific provider services directly (e.g., OpenAIProvider) instead of passing intent strings to AiModelsHub for dynamic resolution
- The system uses hardcoded provider selection logic rather than dynamic database-driven configuration
- Missing separation of concerns: AI services contain both provider-specific logic and routing logic instead of delegating to IntentRoutingEngine
- No abstraction layer for payload formatting; each provider service likely handles formatting independently
- Missing centralized usage tracking and cost optimization across all AI requests
- No dynamic extensibility: adding a new provider requires code changes (new service class) rather than pure database configuration
- AI request flow does not follow the sequence: request → IntentRoutingEngine → DynamicProviderRegistry → PayloadAdapterFactory → HTTP call → response

## 5. 🚀 Continuo (Next Steps & Execution Plan)

1. **Database Schema Updates**
   - Create `ai_providers` table with UUID PK, name, base_url, models_fetch_endpoint, generate_endpoint, auth_header_format, payload_format, is_active
   - Update `ai_models` table: change id to UUID, add provider_id FK, add context_window, input_cost_per_m, output_cost_per_m, last_synced_at; remove provider, external_id, description, capabilities, metadata, status columns
   - Create `ai_api_keys` table with UUID PK, provider_id FK, key_hash (encrypted), and appropriate indexes
   - Create `intent_routing` table with UUID PK, intent_name (unique), default_provider_id FK, default_model_id FK, fallback_provider_id FK (nullable), fallback_model_id FK (nullable)

2. **API Endpoint Implementation**
   - Implement POST /api/v1/ai/providers for provider CRUD operations
   - Implement POST /api/v1/ai/providers/{id}/sync-models for model synchronization
   - Implement PUT /api/v1/ai/intents/routing for intent routing updates
   - Implement POST /api/v1/ai/request for unified AI request handling

3. **Core Services Development**
   - Create DynamicProviderRegistry service for provider CRUD and syncModels() execution
   - Create IntentRoutingEngine service for intent-based provider/model resolution
   - Create PayloadAdapterFactory service for formatting requests to provider-specific payloads
   - Implement Redis caching for intent routing and provider configurations with invalidation mechanism

4. **Security & Resilience Features**
   - Implement AES-256 encryption for API keys at rest
   - Add SSRF protection for dynamic base URLs (block localhost/internal IPs)
   - Implement fallback chain with circuit breakers for 429/5xx/timeout scenarios
   - Add usage tracking and cost meter functionality

5. **Integration & Refactoring**
   - Refactor existing AI provider services to implement a common interface and be dynamically loadable
   - Update workflows, agents, MemoryHub, and LogsHub to use intent-based requests instead of direct provider calls
   - Implement real-time token usage and cost calculation
   - Add notification alerts for model deprecation and fallback events

6. **Testing & Validation**
   - Create unit tests for all new services and API endpoints
   - Test dynamic provider addition without code deployment
   - Verify intent-based routing works correctly
   - Validate fallback chain functionality
   - Confirm SSRF protection effectiveness