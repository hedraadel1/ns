<?php

namespace App\Services\AiModelsHub;

use Illuminate\Support\Facades\Log;
use App\Models\IntentRouting;
use App\Models\AIProvider;
use App\Models\AIModel;
use App\Services\AiModelsHub\CacheManager;

class IntentRoutingEngine
{
    protected $cacheManager;
    protected $cacheTTL = 1800; // 30 minutes

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Resolve intent to provider/model configuration
     */
    public function resolveIntent($intentName)
    {
        return $this->cacheManager->cacheIntentRouting(
            "intent:{$intentName}",
            function () use ($intentName) {
                return IntentRouting::with(['defaultProvider', 'defaultModel', 'fallbackProvider', 'fallbackModel'])
                    ->where('intent_name', $intentName)
                    ->first();
            },
            $this->cacheTTL
        );
    }

    /**
     * Get default model for an intent
     */
    public function getDefaultModel($intentName)
    {
        $routing = $this->resolveIntent($intentName);
        return $routing ? $routing->defaultModel : null;
    }

    /**
     * Get fallback options for an intent
     */
    public function getFallbackOptions($intentName)
    {
        $routing = $this->resolveIntent($intentName);
        if (!$routing) {
            return [];
        }

        $fallbacks = [];
        if ($routing->fallback_provider_id && $routing->fallback_model_id) {
            $fallbacks[] = [
                'provider' => $routing->fallbackProvider,
                'model' => $routing->fallbackModel
            ];
        }

        return $fallbacks;
    }

    /**
     * Create or update intent routing
     */
    public function upsertIntentRouting(array $data)
    {
        $intentRouting = IntentRouting::updateOrCreate(
            ['intent_name' => $data['intent_name']],
            [
                'id' => $data['id'] ?? \Illuminate\Support\Str::uuid(),
                'default_provider_id' => $data['default_provider_id'],
                'default_model_id' => $data['default_model_id'],
                'fallback_provider_id' => $data['fallback_provider_id'] ?? null,
                'fallback_model_id' => $data['fallback_model_id'] ?? null,
            ]
        );

        // Clear intent cache
        $this->clearIntentCache($data['intent_name']);

        return $intentRouting;
    }

    /**
     * Delete intent routing
     */
    public function deleteIntentRouting($intentName)
    {
        $intentRouting = IntentRouting::where('intent_name', $intentName)->first();
        if ($intentRouting) {
            $intentRouting->delete();
            $this->clearIntentCache($intentName);
            return true;
        }
        return false;
    }

    /**
     * Get all intent routings
     */
    public function getAllIntentRouting()
    {
        return $this->cacheManager->cacheProvider(
            'intents:all',
            function () {
                return IntentRouting::with(['defaultProvider', 'defaultModel', 'fallbackProvider', 'fallbackModel'])
                    ->get();
            },
            $this->cacheTTL
        );
    }

    /**
     * Clear intent cache
     */
    protected function clearIntentCache($intentName)
    {
        $this->cacheManager->invalidateIntentRouting($intentName);
        $this->cacheManager->invalidateAllIntentRouting();
    }
}