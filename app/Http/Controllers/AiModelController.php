<?php

namespace App\Http\Controllers;

use App\Models\AIModel;
use App\Models\ApiKey;
use App\Services\AI\ProviderInterface;
use App\Services\AI\ModelSelector;
use App\Services\AI\FallbackChainService;
use App\Services\AI\CostOptimizer;
use App\Services\AI\QualityRouter;
use App\Services\AI\SpeedRouter;
use App\Services\AI\ApiKeyPool;
use App\Services\AI\ApiKeyRotationService;
use App\Services\AI\RateLimitService;
use App\Services\AI\ApiKeyHealthService;
use App\Services\AI\GoogleGeminiProvider;
use App\Services\AI\OpenAIProvider;
use App\Services\AI\AnthropicProvider;
use App\Services\AI\GroqProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AiModelController extends Controller
{
    public function __construct(
        protected ModelSelector $modelSelector,
        protected FallbackChainService $fallbackChain,
        protected CostOptimizer $costOptimizer,
        protected QualityRouter $qualityRouter,
        protected SpeedRouter $speedRouter,
        protected ApiKeyPool $keyPool,
        protected ApiKeyRotationService $keyRotation,
        protected RateLimitService $rateLimiter,
        protected ApiKeyHealthService $keyHealth,
    ) {}

    public function index(Request $request)
    {
        $query = AIModel::query();

        if ($request->has('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $models = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json($models);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:google_gemini,openai,anthropic,groq',
            'external_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'capabilities' => 'nullable|array',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|in:active,inactive,deprecated',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $model = AIModel::create($validator->validated());

        return response()->json([
            'message' => 'AI model created successfully',
            'data' => $model,
        ], 201);
    }

    public function show($id)
    {
        $model = AIModel::with(['provider'])->findOrFail($id);
        return response()->json($model);
    }

    public function update(Request $request, $id)
    {
        $model = AIModel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'capabilities' => 'nullable|array',
            'metadata' => 'nullable|array',
            'status' => 'nullable|string|in:active,inactive,deprecated',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $model->update($validator->validated());

        return response()->json([
            'message' => 'AI model updated successfully',
            'data' => $model,
        ]);
    }

    public function destroy($id)
    {
        $model = AIModel::findOrFail($id);
        $model->delete();

        return response()->json(['message' => 'AI model deleted successfully']);
    }

    public function providers(Request $request)
    {
        $providers = [
            'google_gemini' => [
                'name' => 'Google Gemini',
                'models' => (new GoogleGeminiProvider(''))->getAvailableModels(),
                'default_model' => (new GoogleGeminiProvider(''))->getDefaultModel(),
            ],
            'openai' => [
                'name' => 'OpenAI',
                'models' => (new OpenAIProvider(''))->getAvailableModels(),
                'default_model' => (new OpenAIProvider(''))->getDefaultModel(),
            ],
            'anthropic' => [
                'name' => 'Anthropic',
                'models' => (new AnthropicProvider(''))->getAvailableModels(),
                'default_model' => (new AnthropicProvider(''))->getDefaultModel(),
            ],
            'groq' => [
                'name' => 'Groq',
                'models' => (new GroqProvider(''))->getAvailableModels(),
                'default_model' => (new GroqProvider(''))->getDefaultModel(),
            ],
        ];

        return response()->json($providers);
    }

    public function test(Request $request, $id)
    {
        $model = AIModel::findOrFail($id);
        $provider = $model->provider;

        $apiKey = ApiKey::where('provider', $provider)
            ->where('type', 'ai_provider')
            ->where('is_active', true)
            ->first()?->key;

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => "No active API key found for provider: {$provider}",
            ], 400);
        }

        $providerInstance = $this->resolveProvider($provider, $apiKey);
        $externalId = $model->external_id ?? $model->name;

        $testRequest = [
            'model' => $externalId,
            'prompt' => $request->prompt ?? 'Hello, please respond with a single word.',
            'options' => [
                'max_tokens' => 50,
                'temperature' => 0.7,
            ],
        ];

        $startTime = microtime(true);
        $result = $providerInstance->execute($testRequest);
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $result['test_duration_ms'] = $durationMs;
        $result['model_id'] = $id;
        $result['model_name'] = $model->name;

        return response()->json($result);
    }

    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:google_gemini,openai,anthropic,groq',
            'model' => 'required|string',
            'prompt' => 'required_without:messages|string',
            'messages' => 'required_without:prompt|array',
            'options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $provider = $request->provider;
        $apiKey = $this->keyPool->getKeyForProvider($provider);

        if (!$apiKey) {
            $apiKey = ApiKey::where('provider', $provider)
                ->where('type', 'ai_provider')
                ->where('is_active', true)
                ->first()?->key;
        }

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => "No API key available for provider: {$provider}",
            ], 400);
        }

        $rateLimit = $this->rateLimiter->check($provider, $apiKey);
        if (!$rateLimit['allowed']) {
            return response()->json([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'rate_limit' => $rateLimit,
            ], 429);
        }

        $providerInstance = $this->resolveProvider($provider, $apiKey);
        $execRequest = [
            'model' => $request->model,
            'prompt' => $request->prompt,
            'messages' => $request->messages,
            'options' => $request->options ?? [],
        ];

        $startTime = microtime(true);
        $result = $providerInstance->execute($execRequest);
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        if ($result['success']) {
            $this->rateLimiter->recordSuccess($provider, $apiKey);
            $this->keyPool->getKeyForProvider($provider);
        } else {
            $this->rateLimiter->recordFailure($provider, $apiKey);
        }

        $result['request_duration_ms'] = $durationMs;
        $result['rate_limit'] = $this->rateLimiter->getStatus($provider, $apiKey);

        return response()->json($result);
    }

    public function executeWithFallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'providers' => 'required|array|min:1',
            'model' => 'required|string',
            'prompt' => 'required_without:messages|string',
            'messages' => 'required_without:prompt|array',
            'options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chain = [];
        foreach ($request->providers as $index => $providerName) {
            $apiKey = $this->keyPool->getKeyForProvider($providerName);
            if (!$apiKey) {
                $apiKey = ApiKey::where('provider', $providerName)
                    ->where('type', 'ai_provider')
                    ->where('is_active', true)
                    ->first()?->key;
            }
            if ($apiKey) {
                $chain[] = [
                    'provider' => $this->resolveProvider($providerName, $apiKey),
                    'priority' => $index,
                ];
            }
        }

        if (empty($chain)) {
            return response()->json([
                'success' => false,
                'error' => 'No available providers in fallback chain',
            ], 400);
        }

        $this->fallbackChain->setChain($chain);

        $execRequest = [
            'model' => $request->model,
            'prompt' => $request->prompt,
            'messages' => $request->messages,
            'options' => $request->options ?? [],
        ];

        $result = $this->fallbackChain->executeWithFallback($execRequest);

        return response()->json($result);
    }

    public function selectModel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'criteria' => 'nullable|array',
            'criteria.provider' => 'nullable|string',
            'criteria.model' => 'nullable|string',
            'criteria.max_cost_per_1k' => 'nullable|numeric|min:0',
            'criteria.min_quality' => 'nullable|integer|min:0|max:100',
            'criteria.max_latency_ms' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $criteria = $request->criteria ?? [];
        $selection = $this->modelSelector->select($criteria);

        if (!$selection) {
            return response()->json([
                'success' => false,
                'error' => 'No model found matching criteria',
                'criteria' => $criteria,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'provider' => $selection['provider']->getProviderName(),
            'model' => $selection['model'],
            'score' => $selection['score'],
        ]);
    }

    public function optimizeCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model' => 'nullable|string',
            'estimated_input_tokens' => 'nullable|integer|min:0',
            'estimated_output_tokens' => 'nullable|integer|min:0',
            'max_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $optimization = $this->costOptimizer->optimize($validator->validated(), $request->max_cost);

        return response()->json($optimization);
    }

    public function routeByQuality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tier' => 'required|string|in:critical,high,standard,low',
            'prompt' => 'required_without:messages|string',
            'messages' => 'required_without:prompt|array',
            'model' => 'nullable|string',
            'provider' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $execRequest = $validator->validated();
        unset($execRequest['tier']);

        $result = $this->qualityRouter->route($request->tier, $execRequest);

        return response()->json($result);
    }

    public function routeBySpeed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tier' => 'required|string|in:instant,fast,normal,batch',
            'prompt' => 'required_without:messages|string',
            'messages' => 'required_without:prompt|array',
            'model' => 'nullable|string',
            'provider' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $execRequest = $validator->validated();
        unset($execRequest['tier']);

        $result = $this->speedRouter->route($request->tier, $execRequest);

        return response()->json($result);
    }

    public function keyPoolStatus()
    {
        $statuses = $this->keyPool->getAllPoolStatuses();
        return response()->json($statuses);
    }

    public function keyHealth(Request $request)
    {
        $provider = $request->query('provider');
        $key = $request->query('key');

        if ($provider && $key) {
            $health = $this->keyHealth->checkKey($provider, $key);
            return response()->json($health);
        }

        $keyMap = [];
        $keys = ApiKey::where('type', 'ai_provider')
            ->where('is_active', true)
            ->get();

        foreach ($keys as $k) {
            $keyMap[$k->provider] = $k->key;
        }

        if (empty($keyMap)) {
            return response()->json([
                'message' => 'No active AI provider keys found',
                'health' => [],
            ]);
        }

        $health = $this->keyHealth->checkAllKeys($keyMap);
        return response()->json($health);
    }

    public function rateLimitStatus()
    {
        $statuses = $this->rateLimiter->getAllStatuses();
        return response()->json($statuses);
    }

    public function rotationSchedule()
    {
        $schedule = $this->keyRotation->getRotationSchedule();
        return response()->json($schedule);
    }

    public function rotateExpired()
    {
        $results = $this->keyRotation->bulkRotateExpired();
        return response()->json($results);
    }

    public function fallbackChainStatus()
    {
        $status = $this->fallbackChain->getChainStatus();
        return response()->json([
            'chain_length' => $this->fallbackChain->getChainLength(),
            'providers' => $status,
        ]);
    }

    public function budgetStatus()
    {
        $status = $this->costOptimizer->getBudgetStatus();
        return response()->json($status);
    }

    protected function resolveProvider(string $provider, string $apiKey): ProviderInterface
    {
        return match ($provider) {
            'google_gemini' => new GoogleGeminiProvider($apiKey),
            'openai' => new OpenAIProvider($apiKey),
            'anthropic' => new AnthropicProvider($apiKey),
            'groq' => new GroqProvider($apiKey),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
        };
    }
}
