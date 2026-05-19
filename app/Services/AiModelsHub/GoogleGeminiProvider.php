<?php

namespace App\Services\AiModelsHub;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\AIProvider;
use App\Models\AIModel;

class GoogleGeminiProvider implements AiProviderInterface
{
    protected $provider;
    protected $apiKey;
    protected $baseUrl;
    protected $models = [];

    public function __construct(string $providerId, EncryptedApiKeyStorage $encryptedKeyStorage)
    {
        $this->provider = AIProvider::find($providerId);
        
        if (!$this->provider) {
            throw new \Exception("Provider not found: {$providerId}");
        }
        
        $this->apiKey = $encryptedKeyStorage->getDecryptedKey($providerId);
        $this->baseUrl = rtrim($this->provider->base_url, '/');
        
        // Load models from database
        $this->loadModelsFromDatabase();
    }

    protected function loadModelsFromDatabase()
    {
        $this->models = [];
        $dbModels = AIModel::where('provider_id', $this->provider->id)->get();
        
        foreach ($dbModels as $model) {
            $this->models[$model->id] = [
                'name' => $model->name,
                'max_tokens' => $model->context_window ?? 4096,
                'cost_per_1k_input' => $model->input_cost_per_m / 1000,
                'cost_per_1k_output' => $model->output_cost_per_m / 1000,
            ];
        }
        
        // If no models in database, fallback to some defaults
        if (empty($this->models)) {
            $this->models = [
                'gemini-1.5-pro' => [
                    'name' => 'Gemini 1.5 Pro',
                    'max_tokens' => 32768,
                    'cost_per_1k_input' => 0.0035,
                    'cost_per_1k_output' => 0.0105,
                ],
                'gemini-1.5-flash' => [
                    'name' => 'Gemini 1.5 Flash',
                    'max_tokens' => 32768,
                    'cost_per_1k_input' => 0.000075,
                    'cost_per_1k_output' => 0.0003,
                ],
                'gemini-pro' => [
                    'name' => 'Gemini Pro',
                    'max_tokens' => 8192,
                    'cost_per_1k_input' => 0.0005,
                    'cost_per_1k_output' => 0.0015,
                ],
            ];
        }
    }

    public function getProviderName(): string
    {
        return $this->provider->name;
    }

    public function getAvailableModels(): array
    {
        return array_keys($this->models);
    }

    public function getDefaultModel(): string
    {
        // Return first available model or fallback
        $models = $this->getAvailableModels();
        return $models[0] ?? 'gemini-1.5-pro';
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $validation = $this->validateRequest([
            'prompt' => $prompt,
            'model' => $options['model'] ?? $this->getDefaultModel(),
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? null,
        ]);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => implode(', ', $validation['errors']),
                'provider' => $this->getProviderName(),
            ];
        }

        $model = $options['model'] ?? $this->getDefaultModel();
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? null;

        try {
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                ]
            ];
            
            if ($maxTokens !== null) {
                $payload['generationConfig']['maxOutputTokens'] = $maxTokens;
            }

            $headers = [
                'Content-Type' => 'application/json',
            ];

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($this->baseUrl . '/v1beta/models/' . $model . ':generateContent?key=' . $this->apiKey, $payload);

            if (!$response->successful()) {
                throw new \Exception("Gemini API error: HTTP {$response->status()} - {$response->body()}");
            }

            $responseData = $response->json();

            $content = '';
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $content = $responseData['candidates'][0]['content']['parts'][0]['text'];
            }

            $usage = [
                'input_tokens' => $responseData['usageMetadata']['promptTokenCount'] ?? 0,
                'output_tokens' => $responseData['usageMetadata']['candidatesTokenCount'] ?? 0,
                'total_tokens' => $responseData['usageMetadata']['totalTokenCount'] ?? 0,
            ];

            return [
                'success' => true,
                'provider' => $this->getProviderName(),
                'model' => $model,
                'content' => $content,
                'usage' => $usage,
            ];
        } catch (\Throwable $e) {
            Log::error("Gemini API error: " . $e->getMessage());

            return [
                'success' => false,
                'provider' => $this->getProviderName(),
                'model' => $model,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function generateEmbeddings(string $text, array $options = []): array
    {
        // Gemini embeddings would use a different endpoint
        // For now, return a placeholder
        return [
            'success' => false,
            'provider' => $this->getProviderName(),
            'error' => 'Embeddings not implemented for Gemini provider',
        ];
    }

    public function validateRequest(array $request): array
    {
        $errors = [];

        if (empty($request['prompt'])) {
            $errors[] = 'Prompt is required';
        }

        if (isset($request['model']) && !isset($this->models[$request['model']])) {
            $errors[] = "Unknown model: {$request['model']}";
        }

        if (isset($request['temperature']) && ($request['temperature'] < 0 || $request['temperature'] > 2)) {
            $errors[] = 'Temperature must be between 0 and 2';
        }

        if (isset($request['max_tokens']) && $request['max_tokens'] <= 0) {
            $errors[] = 'Max tokens must be positive';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function estimateCost(string $model, int $inputTokens, int $outputTokens = 0): float
    {
        $modelConfig = $this->models[$model] ?? null;
        if (!$modelConfig) return 0.0;

        $inputCost = ($inputTokens / 1000) * $modelConfig['cost_per_1k_input'];
        $outputCost = ($outputTokens / 1000) * $modelConfig['cost_per_1k_output'];

        return round($inputCost + $outputCost, 6);
    }

    public function getHealthStatus(): array
    {
        try {
            $response = Http::get($this->baseUrl . '/v1beta/models?key=' . $this->apiKey);

            if ($response->successful()) {
                return [
                    'provider' => $this->getProviderName(),
                    'status' => 'healthy',
                    'model' => $this->getDefaultModel(),
                ];
            } else {
                return [
                    'provider' => $this->getProviderName(),
                    'status' => 'unhealthy',
                    'error' => "HTTP {$response->status()}",
                ];
            }
        } catch (\Throwable $e) {
            return [
                'provider' => $this->getProviderName(),
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getRateLimitStatus(): array
    {
        // This would typically come from API headers
        return [
            'provider' => $this->getProviderName(),
            'limit' => 60,
            'remaining' => 60, // Would be from headers in real implementation
            'reset_at' => now()->addMinute()->toISOString(),
        ];
    }
}