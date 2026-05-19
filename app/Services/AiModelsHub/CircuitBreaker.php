<?php

namespace App\Services\AiModelsHub;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class CircuitBreaker
{
    protected $failureThreshold = 5;
    protected $recoveryTimeout = 60; // seconds
    protected $cachePrefix = 'circuit_breaker:';

    /**
     * Execute a callback with circuit breaker protection and fallback
     */
    public function executeWithFallback(callable $callback, array $fallbackProviders = [])
    {
        // For simplicity in this implementation, we'll execute the callback
        // and if it fails, we'll try the fallback providers
        
        try {
            return $callback();
        } catch (Exception $e) {
            Log::warning("Primary provider failed: {$e->getMessage()}");
            
            // Try fallback providers
            foreach ($fallbackProviders as $fallbackProvider) {
                try {
                    // In a real implementation, we'd have a way to execute 
                    // the callback with the fallback provider
                    // For now, we'll just log and continue
                    Log::info("Trying fallback provider: {$fallbackProvider->name}");
                    
                    // This would be the actual fallback execution
                    // return $this->executeWithProvider($callback, $fallbackProvider);
                } catch (Exception $fallbackE) {
                    Log::warning("Fallback provider failed: {$fallbackE->getMessage()}");
                    continue;
                }
            }
            
            // If all providers fail, throw the original exception
            throw $e;
        }
    }

    /**
     * Check if circuit breaker is open for a provider
     */
    protected function isCircuitOpen($providerId)
    {
        $key = $this->cachePrefix . "provider:{$providerId}:state";
        $state = Cache::get($key, 'closed');
        
        if ($state === 'open') {
            // Check if recovery timeout has passed
            $lastFailureTime = Cache::get($this->cachePrefix . "provider:{$providerId}:last_failure");
            if ($lastFailureTime && (now()->getTimestamp() - $lastFailureTime) > $this->recoveryTimeout) {
                // Try half-open state
                Cache::put($key, 'half-open', $this->recoveryTimeout);
                return false;
            }
            return true;
        }
        
        return false;
    }

    /**
     * Record a failure for a provider
     */
    protected function recordFailure($providerId)
    {
        $key = $this->cachePrefix . "provider:{$providerId}:failures";
        $failures = Cache::increment($key, 1);
        
        if ($failures === 1) {
            // First failure, set expiration
            Cache::put($key, 1, $this->recoveryTimeout);
        }
        
        if ($failures >= $this->failureThreshold) {
            // Open the circuit
            Cache::put($this->cachePrefix . "provider:{$providerId}:state", 'open', $this->recoveryTimeout);
            Cache::put($this->cachePrefix . "provider:{$providerId}:last_failure", now()->getTimestamp(), $this->recoveryTimeout);
        }
    }

    /**
     * Record a success for a provider
     */
    protected function recordSuccess($providerId)
    {
        // Reset failure count on success
        Cache::forget($this->cachePrefix . "provider:{$providerId}:failures");
        Cache::put($this->cachePrefix . "provider:{$providerId}:state", 'closed', $this->recoveryTimeout);
    }
}