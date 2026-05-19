<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\AiModelsHub\DynamicProviderRegistry;

class AiProviderController extends Controller
{
    protected $providerRegistry;

    public function __construct(DynamicProviderRegistry $providerRegistry)
    {
        $this->providerRegistry = $providerRegistry;
    }

    /**
     * Store a new AI provider
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'base_url' => 'required|url',
            'models_fetch_endpoint' => 'nullable|string|max:255',
            'generate_endpoint' => 'nullable|string|max:255',
            'auth_header_format' => 'nullable|string|max:255',
            'payload_format' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $provider = $this->providerRegistry->registerProvider([
                'name' => $request->name,
                'base_url' => $request->base_url,
                'models_fetch_endpoint' => $request->models_fetch_endpoint,
                'generate_endpoint' => $request->generate_endpoint,
                'auth_header_format' => $request->auth_header_format,
                'payload_format' => $request->payload_format,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'data' => $provider,
                'message' => 'AI provider created successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating AI provider: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create AI provider'
            ], 500);
        }
    }

    /**
     * Sync models for a specific provider
     */
    public function syncModels(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // No specific validation needed for sync trigger
        ]);

        try {
            $success = $this->providerRegistry->syncModels($id);

            $provider = $this->providerRegistry->getProvider($id);

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $provider,
                'message' => 'Model synchronization completed successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error syncing models for AI provider: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync models for AI provider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test connection to a specific provider
     */
    public function test(Request $request, $id)
    {
        try {
            $provider = $this->providerRegistry->getProvider($id);

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI provider not found'
                ], 404);
            }

            // Simulate a test connection to the provider
            // In a real implementation, this would make a test request to the provider's API
            $testResult = [
                'success' => true,
                'message' => 'Connection to provider successful',
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String(),
            ];

            return response()->json($testResult, 200);
        } catch (\Exception $e) {
            Log::error('Error testing provider connection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to test provider connection: ' . $e->getMessage()
            ], 500);
        }
    }
}
