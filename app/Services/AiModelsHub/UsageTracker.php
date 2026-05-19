<?php

namespace App\Services\AiModelsHub;

use Illuminate\Support\Facades\Log;
use App\Models\AIProvider;
use App\Models\AIModel;
use App\Models\UsageLog;

class UsageTracker
{
    /**
     * Track usage for cost calculation
     */
    public function trackUsage($providerId, $modelId, $inputTokens, $outputTokens)
    {
        try {
            // Get provider and model for cost calculation
            $provider = AIProvider::find($providerId);
            $model = AIModel::find($modelId);

            if (!$provider || !$model) {
                Log::warning("Provider or model not found for usage tracking");
                return;
            }

            // Calculate costs
            $inputCost = ($inputTokens / 1000000) * $model->input_cost_per_m;
            $outputCost = ($outputTokens / 1000000) * $model->output_cost_per_m;
            $totalCost = $inputCost + $outputCost;

            // Create usage log entry
            UsageLog::create([
                'provider_id' => $providerId,
                'model_id' => $modelId,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'input_cost' => $inputCost,
                'output_cost' => $outputCost,
                'total_cost' => $totalCost,
                'timestamp' => now(),
            ]);

            // Optionally, you could update aggregated statistics here
            // For example, update daily/monthly totals in a separate table
        } catch (\Exception $e) {
            Log::error("Error tracking usage: {$e->getMessage()}");
            // Don't throw exception - usage tracking shouldn't break the main flow
        }
    }

    /**
     * Get usage statistics for a provider
     */
    public function getProviderUsage($providerId, $startDate = null, $endDate = null)
    {
        $query = UsageLog::where('provider_id', $providerId);

        if ($startDate) {
            $query->where('timestamp', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('timestamp', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get usage statistics for a model
     */
    public function getModelUsage($modelId, $startDate = null, $endDate = null)
    {
        $query = UsageLog::where('model_id', $modelId);

        if ($startDate) {
            $query->where('timestamp', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('timestamp', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get total cost for a provider
     */
    public function getProviderTotalCost($providerId, $startDate = null, $endDate = null)
    {
        $query = UsageLog::where('provider_id', $providerId);

        if ($startDate) {
            $query->where('timestamp', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('timestamp', '<=', $endDate);
        }

        return $query->sum('total_cost');
    }

    /**
     * Get total cost for a model
     */
    public function getModelTotalCost($modelId, $startDate = null, $endDate = null)
    {
        $query = UsageLog::where('model_id', $modelId);

        if ($startDate) {
            $query->where('timestamp', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('timestamp', '<=', $endDate);
        }

        return $query->sum('total_cost');
    }
}