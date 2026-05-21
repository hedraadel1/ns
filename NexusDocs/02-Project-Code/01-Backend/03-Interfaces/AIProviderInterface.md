# AIProviderInterface

## Overview

The `AIProviderInterface` defines the contract for all AI provider implementations in the Nexus platform. This interface ensures consistent behavior across different AI service providers (OpenAI, Anthropic, Gemini, Groq, etc.).

## Namespace
- **File**: `app/Services/AiModelsHub/AiProviderInterface.php`
- **Namespace**: `App\Services\AiModelsHub`

## Interface Definition

```php
interface AIProviderInterface
{
    /**
     * Get the provider name/identifier
     */
    public function getName(): string;

    /**
     * Get the provider type (openai, anthropic, gemini, groq)
     */
    public function getType(): string;

    /**
     * Validate that the provider is properly configured
     */
    public function validate(): bool;

    /**
     * Execute a chat completion request
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Generate embeddings for text
     */
    public function embeddings(string $text, array $options = []): array;

    /**
     * Get available models from this provider
     */
    public function getAvailableModels(): array;

    /**
     * Calculate token count for messages
     */
    public function countTokens(array $messages): int;

    /**
     * Check if the provider supports a specific capability
     */
    public function supports(string $capability): bool;

    /**
     * Get the maximum context length for this provider
     */
    public function getMaxContextLength(): int;

    /**
     * Get rate limit information
     */
    public function getRateLimit(): array;
}
```

## Method Documentation

### getName(): string
Returns the human-readable name of the provider.

**Returns**: Provider name (e.g., "OpenAI", "Anthropic", "Google Gemini")

**Example**:
```php
$provider->getName(); // Returns "OpenAI"
```

### getType(): string
Returns the provider type identifier used for routing and configuration.

**Returns**: Provider type string (e.g., "openai", "anthropic", "gemini", "groq")

**Example**:
```php
$provider->getType(); // Returns "openai"
```

### validate(): bool
Validates that the provider is properly configured and can be used.

**Returns**: `true` if the provider is valid and ready, `false` otherwise

**Throws**: `ProviderConfigurationException` if critical configuration is missing

### chat(array $messages, array $options = []): array
Executes a chat completion request with the given messages.

**Parameters**:
- `messages` (array): Array of message objects with `role` and `content` keys
- `options` (array): Optional parameters including:
  - `model` (string): Model to use (overrides default)
  - `temperature` (float): Sampling temperature (0-1)
  - `max_tokens` (int): Maximum tokens in response
  - `top_p` (float): Nucleus sampling probability
  - `stream` (bool): Whether to stream the response

**Returns**: Array containing:
- `id` (string): Request identifier
- `content` (string): Generated response content
- `usage` (array): Token usage information
- `model` (string): Model used

**Example**:
```php
$response = $provider->chat([
    ['role' => 'user', 'content' => 'Hello!']
], [
    'temperature' => 0.7,
    'max_tokens' => 100
]);
```

### embeddings(string $text, array $options = []): array
Generates embeddings for the given text.

**Parameters**:
- `text` (string): Text to embed
- `options` (array): Optional parameters

**Returns**: Array containing:
- `embedding` (array): Vector embedding
- `usage` (array): Token usage

### getAvailableModels(): array
Gets list of models available from this provider.

**Returns**: Array of model objects with:
- `id` (string): Model identifier
- `name` (string): Human-readable name
- `capabilities` (array): Supported features
- `max_tokens` (int): Context length

### countTokens(array $messages): int
Counts tokens in the given messages.

**Parameters**:
- `messages` (array): Array of messages to count

**Returns**: Number of tokens

### supports(string $capability): bool
Checks if the provider supports a specific capability.

**Parameters**:
- `capability` (string): Capability to check (e.g., "chat", "embeddings", "vision")

**Returns**: `true` if supported, `false` otherwise

**Common Capabilities**:
- `chat` - Chat completion
- `embeddings` - Text embeddings
- `vision` - Image understanding
- `function_calling` - Function/tool calling
- `streaming` - Response streaming

### getMaxContextLength(): int
Gets the maximum context length (in tokens) for this provider.

**Returns**: Maximum context length

### getRateLimit(): array
Gets rate limit information for this provider.

**Returns**: Array with:
- `requests_per_minute` (int): RPM limit
- `tokens_per_minute` (int): TPM limit
- `remaining_requests` (int): Requests remaining (if known)

## Implementation Examples

### OpenAIProvider Implementation
```php
class OpenAIProvider implements AIProviderInterface
{
    public function getName(): string
    {
        return 'OpenAI';
    }

    public function getType(): string
    {
        return 'openai';
    }

    public function supports(string $capability): bool
    {
        $capabilities = ['chat', 'embeddings', 'function_calling', 'streaming'];
        return in_array($capability, $capabilities);
    }

    public function getMaxContextLength(): int
    {
        return 128000; // GPT-4 Turbo context
    }
}
```

## Related Documentation
- [AI Models Hub](./05-AI-Models-Hub.md)
- [DynamicProviderRegistry](./02-Core-Services/DynamicProviderRegistry.md)
- [PayloadAdapterFactory](./03-Interfaces/PayloadAdapterFactory.md)
- [CircuitBreaker](./02-Core-Services/CircuitBreaker.md)