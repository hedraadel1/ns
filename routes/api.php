<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

/**
 * API Routes for Nexus Platform
 * All routes are prefixed with /api/v1
 */

// Public routes (no authentication required)
Route::group(['prefix' => 'v1', 'middleware' => ['api']], function () {

    // Health check endpoint
    Route::get('/health', function (Request $request) {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'app' => config('app.name'),
        ]);
    });

    // WAHA WhatsApp webhook endpoint
    Route::post('/webhooks/waha', [\App\Http\Controllers\WebhookController::class, 'handleWahaWebhook'])
        ->name('webhooks.waha');

    // Sanctum authentication routes
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])
        ->name('login');

    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register'])
        ->name('register');

    Route::post('/verify-token', [\App\Http\Controllers\AuthController::class, 'verifyToken'])
        ->name('verify-token');
});

// Protected routes (authentication required via Sanctum)
Route::group(['prefix' => 'v1', 'middleware' => ['api', EnsureFrontendRequestsAreStateful::class, 'auth:sanctum']], function () {

    /**
     * Contacts Hub Routes
     */
    Route::post('/contacts/import', [\App\Http\Controllers\ContactController::class, 'import'])
        ->name('contacts.import');
    Route::get('/contacts/export', [\App\Http\Controllers\ContactController::class, 'export'])
        ->name('contacts.export');
    Route::get('/contacts/{id}/memory', [\App\Http\Controllers\ContactController::class, 'getMemory'])
        ->name('contacts.memory');
    Route::get('/contacts/{id}/rules', [\App\Http\Controllers\ContactController::class, 'getRules'])
        ->name('contacts.rules');
    Route::get('/contacts/{id}/analytics', [\App\Http\Controllers\ContactController::class, 'getAnalytics'])
        ->name('contacts.analytics');
    Route::resource('contacts', \App\Http\Controllers\ContactController::class);

    /**
     * Conversations Routes
     */
    Route::resource('conversations', \App\Http\Controllers\ConversationController::class);
    Route::get('/conversations/{id}/messages', [\App\Http\Controllers\ConversationController::class, 'getMessages'])
        ->name('conversations.messages');
    Route::post('/conversations/{id}/send-message', [\App\Http\Controllers\ConversationController::class, 'sendMessage'])
        ->name('conversations.send-message');

    /**
     * Agents Hub Routes
     */
    Route::resource('agents', \App\Http\Controllers\AgentController::class);
    Route::post('/agents/{id}/execute', [\App\Http\Controllers\AgentController::class, 'execute'])
        ->name('agents.execute');
    Route::get('/agents/{id}/status', [\App\Http\Controllers\AgentController::class, 'getStatus'])
        ->name('agents.status');

    /**
       * Workflows Hub Routes
       */
    Route::resource('workflows', \App\Http\Controllers\WorkflowController::class);
    Route::post('/workflows/{id}/execute', [\App\Http\Controllers\WorkflowController::class, 'execute'])
       ->name('workflows.execute');
    Route::get('/workflows/{id}/progress', [\App\Http\Controllers\WorkflowController::class, 'getProgress'])
       ->name('workflows.progress');
    Route::get('/workflows/templates', [\App\Http\Controllers\WorkflowController::class, 'getTemplates'])
       ->name('workflows.templates');

    /**
       * Tasks Hub Routes
       */
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);
    Route::post('/tasks/{id}/cancel', [\App\Http\Controllers\TaskController::class, 'cancel'])
       ->name('tasks.cancel');
    Route::post('/tasks/{id}/pause', [\App\Http\Controllers\TaskController::class, 'pause'])
       ->name('tasks.pause');
    Route::post('/tasks/{id}/resume', [\App\Http\Controllers\TaskController::class, 'resume'])
       ->name('tasks.resume');
    Route::get('/tasks/stats', [\App\Http\Controllers\TaskController::class, 'getStats'])
       ->name('tasks.stats');
    Route::get('/tasks/active', [\App\Http\Controllers\TaskController::class, 'getActive'])
       ->name('tasks.active');
    Route::get('/tasks/queue-stats', [\App\Http\Controllers\TaskController::class, 'getQueueStats'])
       ->name('tasks.queue-stats');
    Route::get('/tasks/routing-stats', [\App\Http\Controllers\TaskController::class, 'getRoutingStats'])
       ->name('tasks.routing-stats');

    /**
     * Memory Hub Routes
     */
    Route::resource('memories', \App\Http\Controllers\MemoryController::class);
    Route::get('/memories/search', [\App\Http\Controllers\MemoryController::class, 'search'])
        ->name('memories.search');
    Route::post('/memories/{id}/index', [\App\Http\Controllers\MemoryController::class, 'indexMemory'])
        ->name('memories.index');

    /**
      * AI Models Hub Routes
      */
    Route::resource('ai-models', \App\Http\Controllers\AiModelController::class);
    Route::post('/ai-models/{id}/test', [\App\Http\Controllers\AiModelController::class, 'test'])
        ->name('ai-models.test');
    Route::post('/ai-models/execute', [\App\Http\Controllers\AiModelController::class, 'execute'])
        ->name('ai-models.execute');
    Route::post('/ai-models/execute-with-fallback', [\App\Http\Controllers\AiModelController::class, 'executeWithFallback'])
        ->name('ai-models.execute-with-fallback');
    Route::post('/ai-models/select', [\App\Http\Controllers\AiModelController::class, 'selectModel'])
        ->name('ai-models.select');
    Route::post('/ai-models/optimize-cost', [\App\Http\Controllers\AiModelController::class, 'optimizeCost'])
        ->name('ai-models.optimize-cost');
    Route::post('/ai-models/route-quality', [\App\Http\Controllers\AiModelController::class, 'routeByQuality'])
        ->name('ai-models.route-quality');
    Route::post('/ai-models/route-speed', [\App\Http\Controllers\AiModelController::class, 'routeBySpeed'])
        ->name('ai-models.route-speed');
    Route::get('/ai-models/providers', [\App\Http\Controllers\AiModelController::class, 'providers'])
        ->name('ai-models.providers');
    Route::get('/ai-models/key-pool', [\App\Http\Controllers\AiModelController::class, 'keyPoolStatus'])
        ->name('ai-models.key-pool');
    Route::get('/ai-models/key-health', [\App\Http\Controllers\AiModelController::class, 'keyHealth'])
        ->name('ai-models.key-health');
    Route::get('/ai-models/rate-limits', [\App\Http\Controllers\AiModelController::class, 'rateLimitStatus'])
        ->name('ai-models.rate-limits');
    Route::get('/ai-models/rotation-schedule', [\App\Http\Controllers\AiModelController::class, 'rotationSchedule'])
        ->name('ai-models.rotation-schedule');
    Route::post('/ai-models/rotate-expired', [\App\Http\Controllers\AiModelController::class, 'rotateExpired'])
        ->name('ai-models.rotate-expired');
    Route::get('/ai-models/fallback-chain', [\App\Http\Controllers\AiModelController::class, 'fallbackChainStatus'])
        ->name('ai-models.fallback-chain');
    Route::get('/ai-models/budget', [\App\Http\Controllers\AiModelController::class, 'budgetStatus'])
        ->name('ai-models.budget');

    /**
     * Settings Hub Routes
     */
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [\App\Http\Controllers\SettingController::class, 'index'])
            ->name('settings.index');
        Route::put('/', [\App\Http\Controllers\SettingController::class, 'update'])
            ->name('settings.update');
        Route::post('/reset', [\App\Http\Controllers\SettingController::class, 'reset'])
            ->name('settings.reset');
    });

    /**
     * Logs Hub Routes
     */
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', [\App\Http\Controllers\LogController::class, 'index'])
            ->name('logs.index');
        Route::get('/{id}', [\App\Http\Controllers\LogController::class, 'show'])
            ->name('logs.show');
        Route::delete('/{id}', [\App\Http\Controllers\LogController::class, 'destroy'])
            ->name('logs.destroy');
        Route::post('/clear', [\App\Http\Controllers\LogController::class, 'clear'])
            ->name('logs.clear');
    });

    /**
     * User Profile Routes
     */
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'show'])
            ->name('profile.show');
        Route::put('/', [\App\Http\Controllers\ProfileController::class, 'update'])
            ->name('profile.update');
        Route::post('/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])
            ->name('profile.avatar');
    });

    /**
     * Authentication Routes
     */
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])
        ->name('logout');

    Route::post('/refresh-token', [\App\Http\Controllers\AuthController::class, 'refreshToken'])
        ->name('refresh-token');
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'error' => 'Not Found',
        'message' => 'The requested resource was not found',
    ], 404);
});
